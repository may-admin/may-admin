<?php
/**
 * @Description: (ajax提交返回状态信息)
 * @param int $code 状态值[0:成功,1:失败]
 * @param string $message 提示内容
 * @param string $url 返回链接
 * @param string|array $data 返回数据
 * @return @json
 * @author 子青时节 <654108442@qq.com>
 */
function ajax_return($code, $message='', $url='', $data = []){
    if($code == '0'){
        $message = !empty($message) ? $message : lang('action_success');
    }else{
        $message = !empty($message) ? $message : lang('action_fail');
    }
    $url = !empty($url) && is_object($url) ? $url->__toString() : $url;
    $result = [
        'code' => $code,
        'message' => $message,
        'url' => $url,
        'data' => $data,
    ];
    return json($result);
}

/**
 * @Description: (获取真实ip)
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function get_real_ip(){
    $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'HTTP_CF_CONNECTING_IP', // Cloudflare支持
        'HTTP_ALI_CDN_REAL_IP',  // 阿里云CDN
        'HTTP_CDN_SRC_IP'        // 腾讯云CDN
    ];
    foreach($headers as $header){
        if(!empty($_SERVER[$header])){
            $ipChain = explode(',', $_SERVER[$header]);
            // 逆向安全扫描
            foreach($ipChain as $v){
                $ip = trim($v);
                if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)){
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * @Description: (分页额外参数)
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function page_param($is_cache = true){
    $param = request()->param();
    $res = [];
    if (isset($param['_pjax'])){
        unset($param['_pjax']);
    }
    if (isset($param['may_cms_dirs'])){
        unset($param['may_cms_dirs']);
    }
    if(isset($param['page'])){
        $res['page'] = $param['page'];
    }
    if(isset($param['list_rows'])){
        $res['list_rows'] = $param['list_rows'];
        if($is_cache === true){
            if(defined('ADMINID')){
                cache('list_rows_'.ADMINID, $param['list_rows']);
            }else{
                cache('list_rows', $param['list_rows']);
            }
        }
    }else{
        if(defined('ADMINID') && cache('list_rows_'.ADMINID)){
            $res['list_rows'] = cache('list_rows_'.ADMINID);
        }elseif(cache('list_rows')){
            $res['list_rows'] = cache('list_rows');
        }
    }
    $res['query'] = $param;
    return $res;
}

/**
 * @Description: (时间戳转时间格式)
 * @param int|string $time 原始时间
 * @param string $format 时间格式
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function time_turn($time, $format='Y-m-d H:i:s'){
    if (empty($time) || $time == '' || $time == 0) {
        return '';
    }
    if (!is_numeric($time)){
        $time = strtotime($time);
    }
    return date($format, $time);
}

/**
 * @Description: (selectlist配置转换)
 * @param string $select 配置项
 * @param string $please_select 请选择
 * @param string $value 配置值
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function selectlist_select($select = 'whether', $please_select = true, $value = ''){
    $option = $please_select ? ['' => lang('please_select')] : [];
    $arr = config('selectlist.'.$select)['data'];
    $res = $option + $arr;
    if($value != ''){
        $value_arr = explode(',', $value);
        $res_arr = [];
        foreach($value_arr as $v){
            $res_arr[] = isset($res[$v]) ? $res[$v] : '';
        }
        $res = implode(',', $res_arr);
    }
    return $res;
}

/**
 * @Description: (selectlist配置转换)
 * @param string $data 转换值
 * @param string $select 配置项
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function selectlist_turn($data, $select){
    $arr = config('selectlist.'.$select)['data'];
    return $arr[$data];
}

/**
 * @Description: (字符串转挂件选项)
 * @param string $str 数组
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function option_arr($str){
    $res = [];
    if(!empty($str)){
        $option_str = str_replace("\r\n", "\n", $str);
        $option_arr = del_arr_empty(explode("\n", $option_str));
        foreach($option_arr as $v){
            $arr = explode(":", $v);
            if(count($arr) == 2){
                $res[$arr[0]] = $arr[1];
            }
        }
    }
    return $res;
}

/**
 * @Description: (随机数量字符)
 * @param int $num 整数
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function rand_str($num = 6){
    $res = '';
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    while(strlen($res)<$num) {
        $res .= $str[rand(0, strlen($str)-1)];
    }
    return $res;
}

/**
 * @Description: (token生成)
 * @param string $str 整数
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function token_str($str = ''){
    if(!empty($str)){
        $res = sha1(md5(uniqid(md5(microtime(true)),true)).$str);
    }else{
        $res = sha1(md5(uniqid(md5(microtime(true)),true)).rand_str(6));
    }
    return $res;
}

/**
 * @Description: (缓存配置文件方法)
 */
function redis_token($data){
    return $data;
}

/**
 * @Description: (是否百分数)
 * @param string $str 百分数
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function is_percentage($str) {
    return preg_match('/^\d+(\.\d+)?%$/', $str) === 1;
}

/**
 * @Description: (价格转文本显示)
 * @param int $price 价格
 * @param string $unit 单位
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function price_label($price, $unit='￥'){
    if($price > 0){
        $res = '<span class="text-danger">'.$unit.$price.'</span>';
    }elseif($price == '-'){
        $res = $price;
    }else{
        $res = '<span class="text-success">免费</span>';
    }
    return $res;
}

/**
 * @Description: (删除二维数组中的空值，保留0和false)
 * @param array $arr 数组
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function del_arr_empty($arr){
    foreach ($arr as $key => $value){
        if ($value == ''){
            unset($arr[$key]);
        }
    }
    return $arr;
}

/**
 * @Description: (字节转换单位)
 * @param int $size 大小
 * @param int $precision 小数位数
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function file_size_unit($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . $units[$i];
}

/**
 * @Description: (文件格式图标)
 * @param string $mime 文件格式
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function file_mime_icon($mime) {
    if(in_array($mime, ['zip', 'x-7z-compressed'])){
        return '<i class="mime fa-regular fa-file-zipper"></i>';
    }elseif(in_array($mime, ['msword', 'x-empty', 'vnd.ms-powerpoint', 'octet-stream', 'vnd.ms-excel', 'octet-stream'])){
        return '<i class="mime fa-regular fa-file-word"></i>';
    }elseif(in_array($mime, ['mpeg', 'x-m4a', 'x-ape', 'flac', 'x-wav'])){
        return '<i class="mime fa-regular fa-file-audio"></i>';
    }elseif(in_array($mime, ['mp4'])){
        return '<i class="mime fa-regular fa-file-video"></i>';
    }else{
        return '<i class="mime fa-regular fa-file"></i>';
    }
}

/**
 * @Description: (删除文件夹)
 * @param string $dirname 文件夹目录
 * @param boolean $withself 是否删除自身文件夹目录
 * @return boolean
 * @author 子青时节 <654108442@qq.com>
 */
function deldir($dirname, $withself = true){
    if (!is_dir($dirname)) {
        return false;
    }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    if ($withself) {
        @rmdir($dirname);
    }
    return true;
}

/**
 * @Description: (复制文件夹目录)
 * @param string $source 源文件夹目录
 * @param string $dest 目标文件夹目录
 * @return boolean
 * @author 子青时节 <654108442@qq.com>
 */
function copydirs($source, $dest){
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
        if ($item->isDir()) {
            $sontDir = $dest . $iterator->getSubPathName() . DIRECTORY_SEPARATOR;
            if (!is_dir($sontDir)) {
                mkdir($sontDir, 0755, true);
            }
        } else {
            copy($item, $dest . $iterator->getSubPathName());
        }
    }
    return true;
}

/**
 * @Description: (复制多文件)
 * @param array $files 文件数组[非绝对路径]
 * @param string $source 源文件夹目录
 * @param string $dest 目标文件夹目录
 * @return boolean
 * @author 子青时节 <654108442@qq.com>
 */
function copyfiles($files, $source, $dest){
    foreach($files as $v){
        copy($source.$v, $dest.$v);
    }
    return true;
}

/**
 * @Description: (移除空目录)
 * @param string $dir 源文件夹目录
 * @author 子青时节 <654108442@qq.com>
 */
function remove_empty_folder($dir){
    try {
        $isDirEmpty = !(new \FilesystemIterator($dir))->valid();
        if ($isDirEmpty) {
            @rmdir($dir);
            remove_empty_folder(dirname($dir));
        }
    } catch (\Exception $e) {
    }
}

/**
 * @Description: (判断文件或文件夹是否可写)
 * @param string $file 文件或目录
 * @author 子青时节 <654108442@qq.com>
 */
function is_really_writable($file){
    if (DIRECTORY_SEPARATOR === '/') {
        return is_writable($file);
    }
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/' . md5(mt_rand());
        if (($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        @chmod($file, 0777);
        @unlink($file);

        return true;
    } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
        return false;
    }
    fclose($fp);
    return true;
}
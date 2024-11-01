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
 * @Description: (分页额外参数)
 * @return array
 * @author 子青时节 <654108442@qq.com>
 */
function page_param(){
    $param = request()->param();
    $res = [];
    if (isset($param['_pjax'])){
        unset($param['_pjax']);
    }
    if(isset($param['page'])){
        $res['page'] = $param['page'];
    }
    if(isset($param['list_rows'])){
        $res['list_rows'] = $param['list_rows'];
        cache('list_rows', $param['list_rows']);
    }else{
        if(cache('list_rows')){
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
 * @Description: (价格转文本显示)
 * @param int $price 价格
 * @param string $unit 单位
 * @return string
 * @author 子青时节 <654108442@qq.com>
 */
function price_label($price, $unit='￥'){
    if($price > 0){
        $res = '<span class="text-danger">'.$unit.$price.'</span>';
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
    if($mime == 'zip'){
        return '<i class="fa-regular fa-file-zipper"></i>';
    }elseif($mime == 'msword'){
        return '<i class="fa-solid fa-file-word"></i>';
    }else{
        return '<i class="fa-solid fa-file"></i>';
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
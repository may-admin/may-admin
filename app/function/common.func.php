<?php
/**
 * @Description: todo(ajax提交返回状态信息)
 * @param int $code
 * @param string $message
 * @param string $url
 * @param string|array $data
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
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
 * @Description: todo(分页额外参数)
 * @return array
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
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
 * @Description: todo(时间戳转换为时间格式 Y-m-d H:i:s)
 * @param int $time
 * @param string $format
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
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
 * @Description: todo(删除二维数组中的空值，保留0和false)
 * @param array $arr
 * @return array
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
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
 * @Description: todo(文件大小转换单位)
 * @param string $filesize
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2024年10月10日
 * @throws
 */
function file_size_unit($filesize) {
    if ($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . 'GB';
    }elseif($filesize >= 1048576){
        $filesize = round($filesize / 1048576 * 100) / 100 . 'MB';
    }elseif($filesize >= 1024){
        $filesize = round($filesize / 1024 * 100) / 100 . 'KB';
    }else{
        $filesize = $filesize . 'B';
    }
    return $filesize;
}

/**
 * @Description: todo(文件格式图标)
 * @param string $mime
 * @return string
 * @author 苏晓信 <654108442@qq.com>
 * @date 2024年10月10日
 * @throws
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
 * 删除文件夹
 * @param string $dirname  目录
 * @param bool   $withself 是否删除自身
 * @return boolean
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
 * 复制文件夹
 * @param string $source 源文件夹
 * @param string $dest   目标文件夹
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
    
}
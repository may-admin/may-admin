<?php
/**
 * @Description: todo(ajax提交返回状态信息)
 * @param int $status
 * @param string $info
 * @param string $url
 * @param string|array $data
 * @author 苏晓信 <654108442@qq.com>
 * @date 2018年10月9日
 * @throws
 */
function ajax_return($status, $info='', $url='', $data = []){
    if($status == '0'){
        $info = !empty($info) ? $info : lang('action_success');
    }else{
        $info = !empty($info) ? $info : lang('action_fail');
    }
    $url = !empty($url) && is_object($url) ? $url->__toString() : $url;
    $result = [
        'status' => $status,
        'info' => $info,
        'url' => $url,
        'data' => $data,
    ];
    return json($result);
}
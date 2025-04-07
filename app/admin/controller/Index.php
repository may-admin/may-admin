<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\model\Admin as Admins;
use think\facade\View;
use think\facade\Cache;

class Index extends Admin
{
    public function index()
    {
        $data = [];
        $adminModel = new Admins();
        $data['admin_total'] = $adminModel->count();
        
        $data['member_total'] = 0;
        
        $data['archive_total'] = 0;
        
        $data['comment_total'] = 0;
        
        $data['system_config'] = [
            '操作系统' => PHP_OS,
            '服务器时间' => date("Y-n-j H:i:s"),
            'PHP版本号' => PHP_VERSION,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time').'秒',
        ];
        View::assign('data', $data);
        return View::fetch();
    }
    
    public function cleanCache()
    {
        Cache::clear();
        return ajax_return(0, lang('action_success'), '');
    }
}
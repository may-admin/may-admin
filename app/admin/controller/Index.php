<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\model\UploadFile;
use app\facade\AddonService;
use think\facade\View;
use think\facade\Cache;
use think\facade\App;
use think\facade\Db;

class Index extends Admin
{
    public function index()
    {
        $mysqlinfo = Db::query("SELECT VERSION() as version");
        
        $data = [];
        $data['handbook'] = config('addon.doc_url');
        $data['code'] = config('addon.code_url');
        
        $uploadFileModel = new UploadFile();
        $data['files_num'] = $uploadFileModel->count();
        $data['files_size'] = file_size_unit($uploadFileModel->sum('filesize'));
        
        $data['addon'] = !empty(config('addon.api_url')) ? url('Addons/index') : '';
        $data['addon_num'] = count(AddonService::getListAddonConfigIni());
        
        $data['system_config'] = [
            '系统版本' => config('dbconfig.sys.version'),
            'ThinkPHP' => App::version(),
            'PHP版本号' => PHP_VERSION,
            '数据库版本' => $mysqlinfo[0]['version'],
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            '服务器时间' => date("Y-n-j H:i:s"),
            'PHP运行方式' => php_sapi_name(),
            '上传附件限制' => ini_get('upload_max_filesize'),
            '可用容量' => file_size_unit(@disk_free_space(".")),
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
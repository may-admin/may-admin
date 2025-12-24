<?php
namespace app\install\controller;

use PDO;
use app\BaseController;
use app\facade\Install;
use think\facade\View;
use think\facade\Config;
use think\facade\Db;
use think\Exception;
use think\exception\ValidateException;

class Index extends BaseController
{
    public function index()
    {
        $lock_file = base_path().'admin'.DIRECTORY_SEPARATOR.'command'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'install.lock';
        if (is_file($lock_file)) {
            echo '安装成功，快去使用吧！';
            exit;
        }
        
        if (request()->isPost()){
            $data = input('post.', '', 'htmlspecialchars');
            
            if(isset($data['hostname']) && !empty($data['hostname'])){
                $hostname_arr = explode(':', $data['hostname']);
                if(count($hostname_arr) > 1){
                    $data['hostname'] = $hostname_arr[0];
                    $data['hostport'] = $hostname_arr[1];
                }
            }
            try {
                Install::install($data['hostname'], $data['database'], $data['prefix'], $data['username'], $data['password'], $data['hostport'], $data['admin_username'], $data['admin_password'], $data['admin_repassword']);
            } catch (\PDOException $e) {
                return ajax_return(1, $e->getMessage());
            } catch (\Exception $e) {
                $msg_arr = json_decode($e->getMessage(), true);
                if(is_array($msg_arr)){
                    return ajax_return(2, $msg_arr);
                }else{
                    return ajax_return(1, $e->getMessage());
                }
            }
            return ajax_return(0, '安装成功，跳转后台中！', '/admin');
        }else{
            $data = '';
            try {
                Install::checkenv();
            } catch (\Exception $e) {
                $data = $e->getMessage();
            }
            View::assign('data', $data);
            return View::fetch('common@install/index');
        }
    }
    
    public function install($hostname, $database, $prefix, $username, $password, $hostport, $admin_username, $admin_password, $admin_repassword)
    {
        Install::checkenv();
        
        $validate_rule = [
            'hostname' => 'require',
            'database' => 'require',
            'prefix' => 'require',
            'username' => 'require',
            'password' => 'require',
            'hostport' => 'require',
            'admin_username' => 'require|alphaDash|length:5,50',
            'admin_password' => 'require|/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/|length:6,32',
            'admin_repassword' => 'require|confirm:admin_password',
        ];
        $validate_message = [
            'hostname' => '数据库地址不能为空',
            'database' => '数据库名称不能为空',
            'prefix' => '数据表前缀不能为空',
            'username' => '数据库账号不能为空',
            'password' => '数据库密码不能为空',
            'hostport' => '数据库端口不能为空',
            'admin_username' => '管理员账号长度5-50（只允许字母、数字、_和-）',
            'admin_password' => '管理员密码长度6-32（必须包含大写字母，小写字母和数字）',
            'admin_repassword' => '确认密码错误',
        ];
        $data = [
            'hostname' => $hostname,
            'database' => $database,
            'prefix' => $prefix,
            'username' => $username,
            'password' => $password,
            'hostport' => $hostport,
            'admin_username' => $admin_username,
            'admin_password' => $admin_password,
            'admin_repassword' => $admin_repassword,
        ];
        try {
            validate($validate_rule)->message($validate_message)->check($data);
        } catch (ValidateException $e) {
            throw new Exception(json_encode(['name' => $e->getKey(), 'info' => $e->getError()]));
        }
        
        $install_path = base_path().'admin'.DIRECTORY_SEPARATOR.'command'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR;
        
        $sql = file_get_contents($install_path . 'may_admin.sql');
        $sql = str_replace("`may_", "`{$prefix}", $sql);
        
        // 先尝试能否自动创建数据库
        $db_config = config('database');
        try {
            $pdo = new PDO("{$db_config['connections']['mysql']['type']}:host={$hostname}" . ($hostport ? ";port={$hostport}" : ''), $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            
            $db_config = config('database');
            $db_config['connections']['mysql'] = array_merge($db_config['connections']['mysql'], [
                'hostname' => "{$hostname}",
                'hostport' => "{$hostport}",
                'database' => "{$database}",
                'username' => "{$username}",
                'password' => "{$password}",
                'prefix'   => "{$prefix}",
            ]);
            
            // 连接install命令中指定的数据库
            Config::set(['connections' => $db_config['connections']], 'database');
            $connect = Db::connect('mysql');
            
            // 查询一次SQL,判断连接是否正常
            $connect->execute("SELECT 1");
            
            // 调用原生PDO对象进行批量查询
            $connect->getPdo()->exec($sql);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
        
        // 写入.env文件
        $env_file = $install_path.'.example.env';
        $env_file_content = @file_get_contents($env_file);
        if ($env_file_content) {
            $search  = ['database_hostname', 'database_database', 'database_root', 'database_password', 'database_hostport', 'database_prefix'];
            $replace = [$hostname, $database, $username, $password, $hostport, $prefix];
            $env_file_content = str_replace($search, $replace, $env_file_content);
            $result = @file_put_contents( root_path().'.env', $env_file_content);
            if (!$result) {
                throw new Exception('当前权限不足，无法写入文件.env');
            }
        }
        
        // 重置默认管理员密码
        Db::name('admin')->where('id', 42)->update(['username' => $admin_username, 'password' => md5($admin_password)]);
        
        $installLockFile = $install_path."install.lock";
        //检测能否成功写入lock文件
        $result = @file_put_contents($installLockFile, 1);
        if (!$result) {
            throw new Exception('当前权限不足，无法写入文件 install.lock');
        }
        try {
            // 删除安装脚本
            // @unlink(public_path() . 'install.php');
        } catch (\Exception $e) {
        }
    }
    
    public function checkenv()
    {
        if(version_compare(PHP_VERSION, '8.2.0', '<')){
            throw new Exception("当前版本" . PHP_VERSION . "过低，请使用PHP8.2.0以上版本");
        }
        if(!extension_loaded("PDO")){
            throw new Exception("当前未开启PDO，无法进行安装");
        }
        if(!is_really_writable(root_path())){
            throw new Exception("当前站点目录权限不足，无法写入");
        }
        $install_lock_path = base_path().'admin'.DIRECTORY_SEPARATOR.'command'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR;
        if(!is_really_writable($install_lock_path)){
            throw new Exception("当前站点安装锁定目录权限不足，无法写入<br />".$install_lock_path);
        }
        return true;
    }
}
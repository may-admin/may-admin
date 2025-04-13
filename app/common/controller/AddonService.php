<?php
namespace app\common\controller;

use app\BaseController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use think\facade\Db;
use think\Exception;

class AddonService extends BaseController
{
    // 文件上传备份目录[/public/uploads/***]
    private $backup_dir = 'addons';
    
    // 根目录下插件目录
    private $addon_dir = 'addon';
    
    // 允许检索插件复制目录
    private $allow_copy_dir = ['app', 'config', 'extend', 'public', 'route', 'view'];
    
    /**
     * @Description: (远程下载插件)
     * @param string $name 插件名称
     * @param array $extend 扩展参数
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function download($name, $extend = [])
    {
        $addonsBackupDir = $this->getAddonsBackupDir() . $this->backup_dir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        if(!is_dir($addonsBackupDir)){
            mkdir($addonsBackupDir, 0755, true);
        }
        $tmpFile = $addonsBackupDir.$name.'.'.$extend['version'].".zip";
        try {
            $client = $this->getClient();
            $response = $client->get('/addon/Addon/download', ['query' => array_merge(['name' => $name], $extend)]);
            $body = $response->getBody();
            $content = $body->getContents();
            if (substr($content, 0, 1) === '{') {
                $res = json_decode($content, true);
                if($res['code'] == '0' && isset($res['data']['url'])){   //如果传回的是一个下载链接,则再次下载
                    $response = $client->get($res['data']['url']);
                    $body = $response->getBody();
                    $content = $body->getContents();
                }else{
                    throw new Exception($res['message']);   //下载返回错误，抛出异常
                }
            }
        } catch (TransferException $e) {
            throw new Exception("Addon package download failed");
        }
        
        $info = [];
        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            
            $addonDir = $this->getAddonDir($name);
            if (is_dir($addonDir)) {   //存在目录
                if (file_exists($addonDir.'config.ini')) {   //存在配置文件
                    // 必须先禁用插件
                    $ini = $this->getAddonConfigIni($name);
                    if(isset($ini['status']) && $ini['status'] == '1'){
                        throw new Exception('Addon '.$name.' must be disable');
                    }
                }
                
                // 备份插件
                $this->backup($name);
                
                // 删除插件目录
                deldir($this->getAddonDir($name));
            }
            $info = $this->install($name, $extend, $tmpFile);
        }else{
            throw new Exception("No permission to write temporary files");
        }
        return $info;
    }
    
    /**
     * @Description: (离线安装)
     * @param object $file 插件zip压缩包
     * @param array $extend 扩展参数
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function localInstall($file, $extend = [])
    {
        try {
            validate(['file' => ['fileSize' => intval(10 * 1048576), 'fileExt' => 'zip,rar']])->check(['file' => $file]);
            $savename = \think\facade\Filesystem::putFile($this->backup_dir, $file);
        } catch (\think\exception\ValidateException $e) {
            throw new Exception($e->getMessage());
        }
        $tmpFile = $this->getAddonsBackupDir() . $savename;   //上传插件压缩包绝对完整路径
        
        $info = [];
        $zip = new ZipFile();
        try {
            // 打开插件压缩包
            try {
                $zip->openFile($tmpFile);
            } catch (ZipException $e) {
                @unlink($tmpFile);
                throw new Exception('Unable to open the zip file');
            }
            $config = $this->getZipConfigIni($zip);
            
            // 判断插件标识
            $name = $config['name'] ?? '';
            if (empty($name)) {
                throw new Exception('Addon info file data incorrect');
            }
            
            $info = $this->install($name, $extend, $tmpFile);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {
            $zip->close();
            is_file($tmpFile) && unlink($tmpFile);
        }
        return $info;
    }
    
    /**
     * @Description: (安装插件)
     * @param string $name 插件名称
     * @param array $extend 扩展参数
     * @param string $tmpFile 本地文件
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function install($name, $extend = [], $tmpFile = '')
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || is_dir($addonDir) ) {
            throw new Exception('Addon already exists');
        }
        try {
            // 解压插件压缩包到插件目录
            $this->unzip($name, $tmpFile);
            
            // 检查插件是否完整
            $this->checkAddon($name);
            
            // 检查插件是否存在文件重名冲突
            $this->filesConflict($name);
        } catch (Exception $e) {
            @deldir($addonDir);
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }
        
        // 导入数据库
        $this->runSql($name);
        
        // 启用插件
        $this->enable($name);
        
        $ini = $this->getAddonConfigIni($name);
        return $ini;
    }
    
    /**
     * @Description: (卸载插件)
     * @param string $name 插件名称
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function uninstall($name)
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || !is_dir($addonDir) ) {
            throw new Exception('Addon not exists');
        }
        
        // 必须先禁用插件
        $ini = $this->getAddonConfigIni($name);
        if(isset($ini['status']) && $ini['status'] == '1'){
            throw new Exception('Addon must be disable');
        }
        
        // 检查插件是否存在文件重名冲突
        $this->filesConflict($name);
        
        // 存在版本文件差异
        if($ini['version_diff'] == '1'){
            $backup_file_path = $this->backup($name);
        }
        
        // 卸载数据库
        $this->runSql($name, 'uninstall.sql');
        
        // 删除插件目录
        deldir($addonDir);
        if($ini['version_diff'] == '1'){
            throw new Exception('code2,/'.str_replace(public_path(), '', $backup_file_path));
        }else{
            return true;
        }
    }
    
    /**
     * @Description: (启用插件)
     * @param string $name 插件名称
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function enable($name)
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || !is_dir($addonDir)) {
            throw new Exception('Addon not exists');
        }
        
        // 检查插件是否存在文件重名冲突
        $this->filesConflict($name);
        
        // 允许复制目录至根目录
        foreach ($this->allow_copy_dir as $dir) {
            if (is_dir($addonDir . $dir . DIRECTORY_SEPARATOR)) {
                copydirs($addonDir.$dir.DIRECTORY_SEPARATOR, root_path().$dir.DIRECTORY_SEPARATOR);
            }
        }
        
        $ini = $this->getAddonConfigIni($name);
        $ini['status'] = 1;
        $this->setAddonConfigIni($name, $ini);
        
        return true;
    }
    
    /**
     * @Description: (禁用插件)
     * @param string $name 插件名称
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function disable($name)
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || !is_dir($addonDir)) {
            throw new Exception('Addon not exists');
        }
        
        $root_path = root_path();
        $version_diff = 0;
        // 扫描与插件冲突的文件[升级或二开]
        $list = $this->getFilesDiff($name, 'diff');
        if(!empty($list)){
            copyfiles($list, $root_path, $addonDir);
            $version_diff = 1;   // 存在冲突的文件[升级或二开]
        }
        
        // 扫描出插件中文件在根目录对应位置文件并删除
        $list = $this->getFilesDiff($name, 'same');
        if(!empty($list)){
            foreach($list as $v){
                unlink($root_path.$v);
                $dir = pathinfo($root_path.$v, PATHINFO_DIRNAME);
                remove_empty_folder($dir);
            }
        }
        
        $ini = $this->getAddonConfigIni($name);
        $ini['status'] = 0;
        $ini['version_diff'] = $ini['version_diff'] == '1' ? $ini['version_diff'] : $version_diff;
        $this->setAddonConfigIni($name, $ini);
        
        return true;
    }
    
    /**
     * @Description: (备份插件)
     * @param string $name 插件名称
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function backup($name)
    {
        $addonsBackupDir = $this->getAddonsBackupDir() . $this->backup_dir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        if(!is_dir($addonsBackupDir)){
            mkdir($addonsBackupDir, 0755, true);
        }
        $backup_file_path = $addonsBackupDir . $name . "-backup-" . date("Y_m_d_H_i_s") . ".zip";
        $zipFile = new ZipFile();
        try {
            $zipFile
            ->addDirRecursive($this->getAddonDir($name))
            ->saveAsFile($backup_file_path)
            ->close();
        } catch (ZipException $e) {
        } finally {
            $zipFile->close();
        }
        return $backup_file_path;
    }
    
    /**
     * @Description: (解压插件)
     * @param string $name 插件名称
     * @param string $file 文件路径
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function unzip($name, $file = '')
    {
        if (empty($name)) {
            throw new Exception('Invalid parameters');
        }
        
        // 打开插件压缩包
        $zip = new ZipFile();
        try {
            $zip->openFile($file);
        } catch (ZipException $e) {
            $zip->close();
            throw new Exception('Unable to open the zip file');
        }
        
        $dir = $this->getAddonDir($name);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // 解压插件压缩包
        try {
            $zip->extractTo($dir);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file!');
        } finally {
            $zip->close();
        }
        return $dir;
    }
    
    /**
     * @Description: (检测插件是否完整[目录是否存在以及目录下config.ini文件是否存在])
     * @param string $name 插件名称
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function checkAddon($name)
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || !is_dir($addonDir)) {
            throw new Exception('Addon not exists');
        }
        if (!file_exists($addonDir.'config.ini')) {
            throw new Exception('Addon config.ini does not exist');
        }
        return true;
    }
    
    /**
     * @Description: (检查插件是否存在文件重名或冲突)
     * @param string $name 插件名称
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function filesConflict($name)
    {
        // 检测冲突文件
        $list = $this->getFilesDiff($name, 'same');
        if (!empty($list)) {
            //发现冲突文件，抛出异常
            //throw new Exception(json_encode($list));
            $str = 'file conflict:'.implode('<br />', $list);
            throw new Exception($str);
        }
        return true;
    }
    
    /**
     * @Description: (获取插件在全局的文件)
     * @param string $name 插件名称
     * @param string $type 类型['same':'相同文件名文件','diff':'文件内容不一致',为空则插件所有文件]
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function getFilesDiff($name, $type = '')
    {
        $list = [];
        $addonDir = $this->getAddonDir($name);
        $checkDirList = $this->allow_copy_dir;
        // 扫描插件目录是否有重名的文件
        foreach ($checkDirList as $dirName) {
            //检测目录是否存在
            if (!is_dir($addonDir . $dirName)) {
                continue;
            }
            //匹配出所有的文件
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($addonDir . $dirName, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $filePath = $fileinfo->getPathName();
                    $path = str_replace($addonDir, '', $filePath);
                    if (!empty($type)) {
                        $destPath = root_path() . $path;
                        if (is_file($destPath)) {
                            if($type == 'diff'){
                                if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                    $list[] = $path;
                                }
                            }else{
                                $list[] = $path;
                            }
                        }
                    } else {
                        $list[] = $path;
                    }
                }
            }
        }
        $list = array_filter(array_unique($list));
        return $list;
    }
    
    /**
     * @Description: (运行sql文件)
     * @param string $name 插件名称
     * @param string $sql_name sql文件名称
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function runSql($name, $sql_name = '')
    {
        $sql_name = empty($sql_name) ? 'install.sql' : $sql_name;
        $sql_file = $this->getAddonDir($name) . $sql_name;
        if (is_file($sql_file)) {
            $line_arr = file($sql_file);
            $sql_str = '';
            foreach ($line_arr as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }
                $sql_str .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    try {
                        Db::execute($sql_str);
                    } catch (\Exception $e) {
                        //throw new Exception($e->getMessage());
                    }
                    $sql_str = '';
                }
            }
        }
        return true;
    }
    
    /**
     * @Description: (获取插件备份目录[/public/uploads/])
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function getAddonsBackupDir()
    {
        $dir = public_path() . config('dbconfig.up.upload_path') . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }
    
    /**
     * @Description: (匹配压缩包配置文件中info信息)
     * @param @ZipFile $zip
     * @return array|false
     * @author 子青时节 <654108442@qq.com>
     */
    public function getZipConfigIni($zip)
    {
        $config = [];
        try {
            $info = $zip->getEntryContents('config.ini');   //获取压缩包根目录的info.ini
            $config = parse_ini_string($info);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        }
        return $config;
    }
    
    /**
     * @Description: (获取指定插件的目录)
     * @param string $name 插件名称
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function getAddonDir($name)
    {
        $dir = root_path() . $this->addon_dir . DIRECTORY_SEPARATOR . $name .DIRECTORY_SEPARATOR;
        return $dir;
    }
    
    /**
     * @Description: (获取插件配置文件config.ini信息)
     * @param string $name 插件名称
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function getAddonConfigIni($name)
    {
        $addonDir = $this->getAddonDir($name);
        return parse_ini_file($addonDir . 'config.ini');
    }
    
    /**
     * @Description: (获取所有安装插件配置文件config.ini信息)
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function getListAddonConfigIni()
    {
        $ini_arr = glob(root_path().$this->addon_dir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'config.ini');
        $res = [];
        foreach ($ini_arr as $v){
            $inis = parse_ini_file($v);
            $res[$inis['name']] = $inis;
        }
        return $res;
    }
    
    /**
     * @Description: (设置插件配置文件config.ini信息)
     * @param string $name 插件名称
     * @param array $ini 配置数组
     * @return boolean
     * @author 子青时节 <654108442@qq.com>
     */
    public function setAddonConfigIni($name, $ini)
    {
        $addonDir = $this->getAddonDir($name);
        $res = array();
        foreach ($ini as $key => $val) {
            $res[] = "$key = " . (is_numeric($val) ? $val : $val);
        }
        if (file_put_contents($addonDir . 'config.ini', implode("\n", $res) . "\n", LOCK_EX)) {
            
        } else {
            throw new Exception($name." config.ini file does not have write permission");
        }
        return true;
    }
    
    /**
     * @Description: (发送请求)
     * @param string $url 请求链接
     * @param array $params 请求参数
     * @param string $method 请求方法
     * @return mixed
     * @author 子青时节 <654108442@qq.com>
     */
    public function sendRequest($url, $params = [], $method = 'POST')
    {
        $json = [];
        try {
            $client = $this->getClient();
            $options = strtoupper($method) == 'POST' ? ['form_params' => $params] : ['query' => $params];
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $content = $body->getContents();
            $json = json_decode($content, true);
        } catch (TransferException $e) {
            throw new Exception('Network error');
        }
        return $json;
    }
    
    /**
     * @Description: (获取请求对象)
     * @author 子青时节 <654108442@qq.com>
     */
    public function getClient()
    {
        $options = [
            'base_uri'        => config('addon.api_url'),
            'timeout'         => 30,   //请求超时的秒数
            'connect_timeout' => 30,   //等待服务器响应超时的最大值
            'verify'          => false,   //请求时验证SSL证书行为
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'MayAdmin',
            ]
        ];
        static $client;
        if (empty($client)) {
            $client = new Client($options);
        }
        return $client;
    }
}
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
    // 文件上传备份目录
    private $backup_dir = 'addons';
    
    // 根目录下插件目录
    private $addon_dir = 'addon';
    
    // 允许检索插件复制目录【应该修改到110行】
    private $allow_copy_dir = ['app', 'config', 'extend', 'public', 'route', 'view'];
    
    /**
     * 安装插件
     *
     * @param string  $name    插件名称
     * @param boolean $force   是否覆盖
     * @param array   $extend  扩展参数
     * @param array   $tmpFile 本地文件
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public function install($name, $force = false, $extend = [], $tmpFile = '')
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || (is_dir($addonDir) && $force == false)) {
            throw new Exception('Addon already exists');
        }
        
        $extend['domain'] = request()->host(true);
        
        // 远程下载插件
        $tmpFile = $tmpFile ?: $this->download($name, $extend);
        
        try {
            // 解压插件压缩包到插件目录
            $this->unzip($name, $tmpFile);
            
            // 检查插件是否完整
            $this->check($name);
            
            // 检查插件是否存在重名冲突文件
            if ($force == false) {
                $this->noconflict($name);
            }
        } catch (Exception $e) {
            @deldir($addonDir);
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }
        
        // 导入数据库
        // $this->importSql($name);
        
        // 启用插件
        $this->enable($name, true);

return ;

        $info['config'] = get_addon_config($name) ? 1 : 0;
        $info['bootstrap'] = is_file(Service::getBootstrapFile($name));
        $info['testdata'] = is_file(Service::getTestdataFile($name));
        return $info;
    }
    
    /**
     * 启用
     * @param string  $name  插件名称
     * @param boolean $force 是否强制覆盖
     * @return  boolean
     */
    public function enable($name, $force = false)
    {
        $addonDir = $this->getAddonDir($name);
        
        if (empty($name) || !is_dir($addonDir)) {
            throw new Exception('Addon not exists');
        }
        
        if ($force == false) {
            $this->noconflict($name);
        }
        //备份冲突文件
        $conflictFiles = $this->getGlobalFiles($name, false);
        
        if ($conflictFiles) {
            $zip = new ZipFile();
            try {
                foreach ($conflictFiles as $v) {
                    if(is_file(root_path() . $v)){
                        $zip->addFile(root_path() . $v, $v);
                    }
                }
                $addonsBackupDir =  $this->getAddonsBackupDir() . $this->backup_dir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
                if(!is_dir($addonsBackupDir)){
                    mkdir($addonsBackupDir, 0755, true);
                }
                $zip->saveAsFile($addonsBackupDir . $name . "-conflict-enable-" . date("Y_m_d_H_i_s") . ".zip");
            } catch (Exception $e) {
                
            } finally {
                $zip->close();
            }
        }
        
        // 复制application和public到全局
        foreach ($this->allow_copy_dir as $dir) {
            if (is_dir($addonDir . $dir . DIRECTORY_SEPARATOR)) {
                copydirs($addonDir . $dir . DIRECTORY_SEPARATOR, root_path() . $dir . DIRECTORY_SEPARATOR);
            }
        }
        
        // $info = get_addon_info($name);
        // $info['state'] = 1;
        // unset($info['url']);
        // set_addon_info($name, $info);

        return true;
    }
    
    /**
     * 导入SQL
     *
     * @param string $name     插件名称
     * @param string $sql_name SQL文件名称
     * @return  boolean
     */
    public function importSql($name, $sql_name = '')
    {
        $sql_name = empty($fileName) ? 'install.sql' : $sql_name;
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
     * 解压插件
     *
     * @param string $name 插件名称
     * @param string $file 文件路径
     * @return  string
     * @throws  Exception
     */
    public function unzip($name, $file = '')
    {
        if (empty($name)) {
            throw new Exception('Invalid parameters');
        }
        $addonsBackupDir = $this->getAddonsBackupDir();
        $file = $file ?: $addonsBackupDir . $this->backup_dir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name. '.zip';   //原备份目录还需要改
        
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
     * 检测插件是否完整
     *
     * @param string $name 插件名称
     * @return  boolean
     * @throws  Exception
     */
    public function check($name)
    {
        $addonDir = $this->getAddonDir($name);
        if (empty($name) || !is_dir($addonDir)) {
            throw new Exception('Addon not exists');
        }
        if (!file_exists($addonDir.'config.ini')) {
            throw new Exception('Addon config.ini does not exist');
        }
        // $addonClass = get_addon_class($name);
        // if (!$addonClass) {
        //     throw new Exception("The addon file does not exist");
        // }
        // $addon = new $addonClass();
        // if (!$addon->checkInfo()) {
        //     throw new Exception("The configuration file content is incorrect");
        // }
        return true;
    }
    
    /**
     * 是否有冲突
     *
     * @param string $name 插件名称
     * @return  boolean
     * @throws  AddonException
     */
    public function noconflict($name)
    {
        // 检测冲突文件
        $list = $this->getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            throw new Exception($list);
        }
        return true;
    }
    
    /**
     * 获取插件在全局的文件
     *
     * @param string  $name         插件名称
     * @param boolean $onlyconflict 是否只返回冲突文件
     * @return  array
     */
    public function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $addonDir = $this->getAddonDir($name);
        $checkDirList = $this->allow_copy_dir;
        
        // 扫描插件目录是否有覆盖的文件
        foreach ($checkDirList as $dirName) {
            //检测目录是否存在
            if (!is_dir($addonDir . $dirName)) {
                continue;
            }
            //匹配出所有的文件
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($addonDir . $dirName, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $filePath = $fileinfo->getPathName();
                    $path = str_replace($addonDir, '', $filePath);
                    if ($onlyconflict) {
                        $destPath = root_path() . $path;
                        if (is_file($destPath)) {
                            if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
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
     * 远程下载插件
     *
     * @param string $name   插件名称
     * @param array  $extend 扩展参数
     * @return  string
     */
    public static function download($name, $extend = [])
    {
        echo "download";
        return;
        /*
        $addonsTempDir = self::getAddonsBackupDir();
        $tmpFile = $addonsTempDir . $name . ".zip";
        try {
            $client = self::getClient();
            $response = $client->get('/addon/download', ['query' => array_merge(['name' => $name], $extend)]);
            $body = $response->getBody();
            $content = $body->getContents();
            if (substr($content, 0, 1) === '{') {
                $json = (array)json_decode($content, true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    $response = $client->get($json['data']['url']);
                    $body = $response->getBody();
                    $content = $body->getContents();
                } else {
                    //下载返回错误，抛出异常
                    throw new AddonException($json['msg'], $json['code'], $json['data']);
                }
            }
        } catch (TransferException $e) {
            throw new Exception("Addon package download failed");
        }

        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("No permission to write temporary files");
        */
    }
    
    /**
     * 离线安装
     * @param string $file   插件压缩包
     * @param array  $extend 扩展参数
     * @param string $force  是否覆盖
     */
    public function localInstall($file, $extend = [], $force = false)
    {
        try {
            validate(['file' => ['fileSize' => intval(10 * 1048576), 'fileExt' => 'zip,rar']])->check(['file' => $file]);
            $savename = \think\facade\Filesystem::putFile($this->backup_dir, $file);
        } catch (\think\exception\ValidateException $e) {
            throw new Exception($e->getMessage());
        }
        $tmpFile = $this->getAddonsBackupDir() . $savename;   //绝对完整路径
        
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
            
            // 判断插件名称
            if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
                throw new Exception('Addon name incorrect');
            }
            
            // 判断新插件是否存在
            $newAddonDir = $this->getAddonDir($name);
            
            if ($force == false && is_dir($newAddonDir)) {
                throw new Exception('Addon already exists,title:' . $config['title'] . ',name:'.$name);
            }
            
            // 读取旧版本号
            $oldversion = '';
            if (is_dir($newAddonDir)) {
                $oldConfig = parse_ini_file($newAddonDir . 'config.ini');
                $oldversion = $oldConfig['version'] ?? '';
            }
            
            $extend['oldversion'] = $oldversion;
            $extend['version'] = $config['version'];
            
            // 追加MD5和Data数据
            $extend['md5'] = md5_file($tmpFile);
            $extend['data'] = $zip->getArchiveComment();
            $extend['unknownsources'] = config('app_debug') && config('fastadmin.unknownsources');
            $extend['faversion'] = config('fastadmin.version');
            
            $params = array_merge($config, $extend);
            // 压缩包验证、版本依赖判断，应用插件需要授权使用，移除或绕过授权验证，保留追究法律责任的权利
            //$this->valid($params);
            
            if (!$oldversion) {
                // 新装模式
                $info = $this->install($name, $force, $extend, $tmpFile);
            } else {
                // 升级模式
                echo "升级模式";
                //$info = $this->upgrade($name, $extend, $tmpFile);
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {
            $zip->close();
            is_file($tmpFile) && unlink($tmpFile);
        }
        
        
        
        // $info['config'] = get_addon_config($name) ? 1 : 0;
        // $info['bootstrap'] = is_file(Service::getBootstrapFile($name));
        // $info['testdata'] = is_file(Service::getTestdataFile($name));
        // return $info;
    }
    
    /**
     * 获取插件备份目录
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
     * 获取指定插件的目录
     */
    public function getAddonDir($name)
    {
        $dir = root_path() . $this->addon_dir . DIRECTORY_SEPARATOR . $name .DIRECTORY_SEPARATOR;
        return $dir;
    }
    
    /**
     * 匹配配置文件中info信息
     * @param ZipFile $zip
     * @return array|false
     * @throws Exception
     */
    protected function getZipConfigIni($zip)
    {
        $config = [];
        // 读取插件信息
        try {
            $info = $zip->getEntryContents('config.ini');   //获取压缩包根目录的info.ini
            $config = parse_ini_string($info);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        }
        return $config;
    }
    
    /**
     * 验证压缩包、依赖验证
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function valid($params = [])
    {
        $json = $this->sendRequest('/addon/valid', $params, 'POST');
        if ($json && isset($json['code'])) {
            if ($json['code']) {
                return true;
            } else {
                throw new Exception($json['msg'] ?? "Invalid addon package");
            }
        } else {
            throw new Exception("Unknown data format");
        }
    }
    
    /**
     * 发送请求
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * 获取请求对象
     * @return Client
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
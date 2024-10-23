<?php
namespace app\common\controller;

use app\BaseController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

use think\Exception;

class AddonService extends BaseController
{
    
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
    public static function install($name, $force = false, $extend = [], $tmpFile = '')
    {
        echo $name;
        return ;
        
        if (!$name || (is_dir(ADDON_PATH . $name) && !$force)) {
            throw new Exception('Addon already exists');
        }

        $extend['domain'] = request()->host(true);

        // 远程下载插件
        $tmpFile = $tmpFile ?: Service::download($name, $extend);

        $addonDir = self::getAddonDir($name);

        try {
            // 解压插件压缩包到插件目录
            Service::unzip($name, $tmpFile);

            // 检查插件是否完整
            Service::check($name);

            if (!$force) {
                Service::noconflict($name);
            }
        } catch (AddonException $e) {
            @rmdirs($addonDir);
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            @rmdirs($addonDir);
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }

        // 默认启用该插件
        $info = get_addon_info($name);

        Db::startTrans();
        try {
            if (!$info['state']) {
                $info['state'] = 1;
                set_addon_info($name, $info);
            }

            // 执行安装脚本
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install();
            }
            Db::commit();
        } catch (Exception $e) {
            @rmdirs($addonDir);
            Db::rollback();
            throw new Exception($e->getMessage());
        }

        // 导入
        Service::importsql($name);

        // 启用插件
        Service::enable($name, true);

        $info['config'] = get_addon_config($name) ? 1 : 0;
        $info['bootstrap'] = is_file(Service::getBootstrapFile($name));
        $info['testdata'] = is_file(Service::getTestdataFile($name));
        return $info;
    }
    
    
    
    
    
    
    
    
    
    /**
     * 发送请求
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendRequest($url, $params = [], $method = 'POST')
    {
        $json = [];
        try {
            $client = self::getClient();
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
    public static function getClient()
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
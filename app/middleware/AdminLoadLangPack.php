<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\App;
use think\Config;
use think\Cookie;
use think\Lang;
use think\Request;
use think\Response;

/**
 * 多语言加载
 */
class AdminLoadLangPack
{
    protected $config;

    public function __construct(protected App $app, protected Lang $lang, Config $config)
    {
        $this->config = $lang->getConfig();
    }

    /**
     * 路由初始化（路由规则注册）
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 自动侦测当前语言
        $langset = $this->detect($request);

        if ($this->lang->defaultLangSet() != $langset) {
            $this->lang->switchLangSet($langset);
        }

        //加载目录下多语言包【后台只加载中文包】
        $this->lang->load([
            app()->getBasePath().'lang'.DIRECTORY_SEPARATOR.'zh-cn.php',
            app()->getBasePath().'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR.request()->controller().'.php',
        ]);

        $this->saveToCookie($this->app->cookie, $langset);

        return $next($request);
    }

    /**
     * 自动侦测设置获取语言选择
     * @access protected
     * @param Request $request
     * @return string
     */
    protected function detect(Request $request): string
    {
        // 自动侦测设置获取语言选择
        $langSet = '';

        if ($request->get($this->config['detect_var'])) {
            // url中设置了语言变量
            $langSet = $request->get($this->config['detect_var']);
        } elseif ($request->header($this->config['header_var'])) {
            // Header中设置了语言变量
            $langSet = $request->header($this->config['header_var']);
        } elseif ($request->cookie($this->config['cookie_var'])) {
            // Cookie中设置了语言变量
            $langSet = $request->cookie($this->config['cookie_var']);
        } elseif ($request->server('HTTP_ACCEPT_LANGUAGE')) {
            // 自动侦测浏览器语言
            $langSet = $request->server('HTTP_ACCEPT_LANGUAGE');
        }

        if (preg_match('/^([a-z\d\-]+)/i', $langSet, $matches)) {
            $langSet = strtolower($matches[1]);
            if (isset($this->config['accept_language'][$langSet])) {
                $langSet = $this->config['accept_language'][$langSet];
            }
        } else {
            $langSet = $this->lang->getLangSet();
        }

        if (empty($this->config['allow_lang_list']) || in_array($langSet, $this->config['allow_lang_list'])) {
            // 合法的语言
            $this->lang->setLangSet($langSet);
        } else {
            $langSet = $this->lang->getLangSet();
        }

        return $langSet;
    }

    /**
     * 保存当前语言到Cookie
     * @access protected
     * @param Cookie $cookie Cookie对象
     * @param string $langSet 语言
     * @return void
     */
    protected function saveToCookie(Cookie $cookie, string $langSet): void
    {
        if ($this->config['use_cookie']) {
            $cookie->set($this->config['cookie_var'], $langSet);
        }
    }
}

<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\model\Admin;
use think\facade\View;
use think\captcha\facade\Captcha;

class Login extends BaseController
{
    protected $middleware = ['admin_load_lang_pack'];
    
    private $cModel;
    
    public function initialize()
    {
        $this->cModel = new Admin();
    }
    
    /**
     * @Title: index
     * @Description: todo(后台登录页)
     * @return string
     * @author 苏晓信 <654108442@qq.com>
     * @date 2020年10月6日
     * @throws
     */
    public function index()
    {
        $admin_id = session('admin_id');
        if (!empty($admin_id)){
            return redirect((string) url('index/index'));
        }else{
            return View::fetch();
        }
    }
    
    /**
     * @Title: checkLogin
     * @Description: todo(验证登录)
     * @return string
     * @author 苏晓信 <654108442@qq.com>
     * @date 2020年10月6日
     * @throws
     */
    public function checkLogin()
    {
        if(request()->isPost()){
            $data = input('post.');
            if (cookie('admin_username')){
                $data['username'] = cookie('admin_username');
            }
            if(empty($data['username']) || empty($data['password'])){
                return ajax_return(1, lang('admin_require'));
            }
            if(empty($data['captcha'])){
                return ajax_return(1, lang('captcha_require'));
            }
            if(!captcha_check($data['captcha'])){
                return ajax_return(2, lang('captcha_error'));
            };
            $where = [['username', '=', $data['username'] ]];
            $adminData = $this->cModel->where($where)->find();
            if(!empty($adminData)){
                if($adminData['status'] != '1'){
                    return ajax_return(1, lang('admin_stop'));
                }elseif($adminData['password'] != md5($data['password'])){
                    return ajax_return(1, lang('password_error'));
                }else{
                    //更新登录信息
                    $adminData->logins = $adminData['logins']+1;
                    $adminData->last_time = time();
                    $adminData->last_ip = request()->ip();
                    $adminData->save();
                    
                    //设置session,cookie
                    session('admin_id', $adminData['id']);
                    if (!empty($adminData['name'])){
                        cookie('admin_name', $adminData['name'], 86400);
                    }else{
                        cookie('admin_name', $adminData['username'], 86400);
                    }
                    cookie('admin_username', $adminData['username'], 86400);
                    cookie('admin_avatar', $adminData['avatar'], 86400);
                    
                    return ajax_return(0, lang('login_success'), url('index/index'));
                }
            }else{
                return ajax_return(1, lang('admin_empty'));
            }
        }
    }
    
    /**
     * @Title: loginOut
     * @Description: todo(退出登录)
     * @return string
     * @author 苏晓信 <654108442@qq.com>
     * @date 2020年10月6日
     * @throws
     */
    public function loginOut()
    {
        session('admin_id', null);
        session('addon_member_info', null);
        cookie('admin_name', null);
        cookie('admin_username', null);
        cookie('admin_avatar', null);
        return redirect((string) url('login/index'));
    }
    
    /**
     * @Title: loginLock
     * @Description: todo(锁定登录)
     * @return string
     * @author 苏晓信 <654108442@qq.com>
     * @date 2020年10月6日
     * @throws
     */
    public function loginLock()
    {
        session('admin_id', null);
        session('addon_member_info', null);
        return redirect((string) url('login/index'));
    }
    
    /**
     * @Title: verify
     * @Description: todo(后台登录验证码)
     * @return @img
     * @author 苏晓信 <654108442@qq.com>
     * @date 2020年11月6日
     * @throws
     */
    public function verify()
    {
        return Captcha::create();
    }
}
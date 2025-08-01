<?php
declare (strict_types = 1);

namespace app\middleware;

use app\common\model\AuthRule;
use expand\Auth;
use think\facade\View;

class AdminAuth
{
    static $activeMenu;
    
    public function handle($request, \Closure $next)
    {
        $treeMenu = $this->treeMenu();
        View::assign('treeMenu', $treeMenu);
        
        if( CONTROLLER_NAME != 'Addons' ){
            $authRuleModel = new AuthRule();
            $rule = CONTROLLER_NAME.'/'.ACTION_NAME;
            $data = $authRuleModel->where('name', $rule)->find();
            $activeMenus = $this->activeMneu($data, $data['pid']);
            View::assign('activeMenus', $activeMenus);
        }else{
            View::assign('activeMenus', ['addons']);
        }
        
        $auth = new Auth();
        if( CONTROLLER_NAME != 'Addons' && !$auth->check(CONTROLLER_NAME.'/'.ACTION_NAME, ADMINID) ){
            return redirect((string) url('login/loginOut'));
        }
        
        return $next($request);
    }
    
    
    private function treeMenu()
    {
        $treeMenu = cache('DB_ADMIN_TREE_MENU_'.ADMINID);
        if(!$treeMenu){
            $where = [
                ['module' ,'=', 'admin'],
                ['ismenu' ,'=', 1],
                ['status' ,'=', 1],
            ];
            $authRuleModel = new AuthRule();
            $lists = $authRuleModel->where($where)->order('sorts desc,id desc')->select()->toArray();
            //判断导航tree用户使用权限
            $auth = new Auth();
            foreach($lists as $k=>$val){
                $res = $auth->check($val['name'], ADMINID);
                if( $res === false ){
                    unset($lists[$k]);
                }
            }
            $treeClass = new \expand\Tree();
            $treeMenu = $treeClass->treeMenu($lists);
            cache('DB_ADMIN_TREE_MENU_'.ADMINID, $treeMenu);
        }
        $addon_api_url = config('addon.api_url');
        if(!empty($addon_api_url)){
            $treeMenu[] = [
                'id' => 'addons',
                'name' => 'Addons/index',
                'title' => lang('addons_list'),
                'icon' => 'fa-solid fa-puzzle-piece',
                '_child' => [],
            ];
        }
        return $treeMenu;
    }
    
    private function activeMneu($data, $pid)
    {
        self::$activeMenu[] = $data['id'];
        if (isset($data['pid']) && $data['pid'] != '0'){
            $authRuleModel = new AuthRule();
            $data = $authRuleModel->where('id', $pid)->find();
            self::activeMneu($data, $data['pid']);
        }
        return self::$activeMenu;
    }
}

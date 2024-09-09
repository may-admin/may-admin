<?php
namespace app\common\model;

use think\Model;

class Admin extends Model
{
    protected $readonly = ['username'];
    
    public function setPasswordAttr($value)
    {
        return md5($value);
    }
    public function setAvatarAttr()
    {
        return config('custom.default_avatar');
    }
    public function setLoginsAttr()
    {
        return '0';
    }
    public function setRegIpAttr()
    {
        return request()->ip();
    }
    public function setLastTimeAttr()
    {
        return time();
    }
    public function setLastIpAttr()
    {
        return request()->ip();
    }
    
    public function getSexTextAttr($value, $data)
    {
        $arr = config('selectlist.sex')['data'];
        return $arr[$data['sex']];
    }
}
<?php
namespace app\common\model;

class Admin extends BaseModel
{
    protected $readonly = ['username'];
    
    public function setPasswordAttr($value)
    {
        return !empty($value) ? md5($value) : 0;
    }
    public function setAvatarAttr($value)
    {
        return !empty($value) ? $value : config('custom.default_avatar');
    }
    public function setLoginsAttr($value)
    {
        return !empty($value) ? $value : 0;
    }
    public function setRegIpAttr($value)
    {
        return !empty($value) ? $value : get_real_ip();
    }
    public function setLastTimeAttr($value)
    {
        return !empty($value) ? $value : time();
    }
    public function setLastIpAttr($value)
    {
        return !empty($value) ? $value : get_real_ip();
    }
    
    public function getSexTextAttr($value, $data)
    {
        $arr = config('selectlist.sex')['data'];
        return $arr[$data['sex']];
    }
}
<?php
namespace app\common\model;

use think\Model;

class AuthGroup extends Model
{
    public function setRulesAttr($value)
    {
        return $value = implode(',', array_filter($value));
    }
}
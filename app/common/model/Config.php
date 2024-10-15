<?php
namespace app\common\model;

use think\Model;

class Config extends Model
{
    public function getVTextAttr($value, $data)
    {
        if(!empty($data['v'])){
            return '<div class="truncates text-truncate" data-bs-toggle="tooltip" data-bs-title="'.$data['v'].'">'.$data['v'].'</div>';
        }else{
            return $data['v'];
        }
    }
}
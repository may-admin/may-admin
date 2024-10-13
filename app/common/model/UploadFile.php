<?php
namespace app\common\model;

use think\Model;

class UploadFile extends Model
{
    public function getUrlTextAttr($value, $data)
    {
        if($data['format'] == 'image'){
            return '<a href="'.$data['url'].'" class="table-image" target="_blank"><img src="'.$data['url'].'" /></a>';
        }else{
            return file_mime_icon($data['mime']);
        }
    }
}
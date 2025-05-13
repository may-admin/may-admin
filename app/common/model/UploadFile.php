<?php
namespace app\common\model;

class UploadFile extends BaseModel
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
<?php
namespace app\common\model;

class AuthRule extends BaseModel
{
    public function treeList($module = '', $status = '')
    {
        $list = cache('DB_TREE_AUTHRULE_'.$module.'_'.$status);
        if(!$list){
            $where = [];
            if ($module != ''){
                $where[] = ['module', '=', $module];
            }
            if ($status != ''){
                $where[] = ['status', '=', $status];
            }
            $list = $this->where($where)->order('sorts desc,id desc')->select()->toArray();
            $treeClass = new \expand\Tree();
            $treeClass::$treeList = [];
            $list = $treeClass->create($list);
            cache('DB_TREE_AUTHRULE_'.$module.'_'.$status, $list);
        }
        return $list;
    }
}
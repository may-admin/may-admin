<?php
namespace app\common\model;

use think\Model;

class AuthRule extends Model
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
            $list = $this->where($where)->order('sorts ASC,id ASC')->select()->toArray();
            $treeClass = new \expand\Tree();
            $treeClass::$treeList = [];
            $list = $treeClass->create($list);
            cache('DB_TREE_AUTHRULE_'.$module.'_'.$status, $list);
        }
        return $list;
    }
}
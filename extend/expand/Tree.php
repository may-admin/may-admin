<?php
namespace expand;

class Tree
{
    static public $treeList = [];   //存放无限极分类结果
    
    public function create($data, $pid=0, $h_layer=1, $parent_id = 'pid')
    {
        foreach($data as $key => $value){
            if($value[$parent_id] == $pid){
                $value['h_layer'] = $h_layer;
                self::$treeList[] = $value;
                self::create($data, $value['id'], $h_layer+1);
            }
        }
        return self::$treeList;
    }
    
    public function treeMenu($source, $pid=0)
    {
        $treeMenu = [];
        foreach ($source as $key => $item) {
            if( $item['pid'] == $pid ) {
                $item['_child'] = self::treeMenu($source, $item['id']);
                $treeMenu[] = $item;
            }
        }
        return $treeMenu;
    }
    
    /**
     * @Description: (栏目内容栏目树)
     * @param array $source 栏目数组
     * @param int $pid 上级ID
     * @return array
     * @author 子青时节 <654108442@qq.com>
     */
    public function categoryTree($source, $pid=0)
    {
        $treeMenu = [];
        foreach ($source as $key => $item) {
            if( $item['pid'] == $pid ) {
                $nodes = self::categoryTree($source, $item['id']);
                if(!empty($nodes)){
                    $item['nodes'] = $nodes;
                }
                $treeMenu[] = $item;
            }
        }
        return $treeMenu;
    }
}
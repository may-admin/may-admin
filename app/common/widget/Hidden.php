<?php
namespace app\common\widget;

use think\facade\View;

class Hidden
{
    /**
     * @Title: index
     * @Description: todo(普通Hidden挂件)
     * @param array $data       【编辑操作时旧数据集合】
     * @param array $wconfig    【配置项】
     * <pre>
     *      name                组件标签name属性值，对应数据库字段【必须】
     * </pre>
     * @return string
     * @author 苏晓信 <654108442@qq.com>
     * @date 2018年10月9日
     * @throws
     */
    public function index($data, $wconfig)
    {
        if (isset($data[$wconfig['name']])){
            $wconfig['widget_val'] = $data[$wconfig['name']];
        }else{
            $wconfig['widget_val'] = '';
        }
        
        View::assign('wconfig', $wconfig);
        return View::fetch('common@widget/hidden');
    }
}
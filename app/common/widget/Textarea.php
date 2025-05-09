<?php
namespace app\common\widget;

use think\facade\View;

class Textarea
{
    /**
     * @Title: index
     * @Description: todo(普通Textarea挂件)
     * @param array $data       【编辑操作时旧数据集合】
     * @param array $wconfig    【配置项】
     * <pre>
     *      name                组件标签name属性值，对应数据库字段【必须】
     *      title               组件标题【必须】
     *      default_val         默认值，新增无数据时默认值【非必须】
     *      placeholder         提示内容【非必须】
     *      title_col           标题占比（默认2）【非必须】
     *      content_col         内容占比（默认7）【非必须】
     *      validate            提示信息【非必须】
     *      validate_col        提示信息占比（默认4）【非必须】
     *      readonly            只读【非必须】
     *      disabled            禁用【非必须】
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
        
        /* 默认值 */
        if ($wconfig['widget_val'] == '' && isset($wconfig['default_val']) && $wconfig['default_val'] != ''){
            $wconfig['widget_val'] = $wconfig['default_val'];
        }
        
        /* 是否只读 */
        if (isset($wconfig['readonly']) && $wconfig['readonly'] === true){
            $wconfig['readonly'] = 'readonly="readonly"';
        }else{
            $wconfig['readonly'] = '';
        }
        
        /* 是否禁用 */
        if (isset($wconfig['disabled']) && $wconfig['disabled'] === true){
            $wconfig['disabled'] = 'disabled="disabled"';
        }else{
            $wconfig['disabled'] = '';
        }
        
        $wconfig['title_col'] = isset($wconfig['title_col']) ? $wconfig ['title_col'] : '2';
        $wconfig['content_col'] = isset($wconfig['content_col']) ? $wconfig ['content_col'] : '6';
        $wconfig['validate_col'] = isset($wconfig['validate_col']) ? $wconfig ['validate_col'] : '4';
        $wconfig['validate'] = isset($wconfig['validate']) ? $wconfig ['validate'] : '';
        $wconfig['placeholder'] = isset($wconfig['placeholder']) ? $wconfig ['placeholder'] : '';
        
        View::assign('wconfig', $wconfig);
        return View::fetch('common@widget/textarea');
    }
}
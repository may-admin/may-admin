<?php
namespace app\common\widget;

use think\facade\View;

class DateTime
{
    /**
     * @Title: index
     * @Description: todo(普通DateTime挂件)
     * @param array $data       【编辑操作时旧数据集合】
     * @param array $wconfig    【配置项】
     * <pre>
     *      name                组件标签name属性值，对应数据库字段【必须】
     *      title               组件标题【必须】
     *      placeholder         提示内容【非必须】
     *      format              时间格式【非必须，默认：Y-m-d H:i:S】
     *      now_time            是否使用当前时间，优先级低于默认值【非必须,true or false】
     *      title_col           标题占比（默认2）【非必须】
     *      content_col         内容占比（默认6）【非必须】
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
        
        $wconfig['format'] = isset($wconfig['format']) ? $wconfig ['format'] : 'Y-m-d H:i:S';
        
        if (!empty($wconfig['widget_val'])){
            $wconfig['widget_val'] = is_numeric($wconfig['widget_val']) ? date($wconfig['format'], $wconfig['widget_val']) : $wconfig['widget_val'];
        }elseif (isset($wconfig['now_time']) && $wconfig['now_time'] != ''){
            $wconfig['widget_val'] = date($wconfig['format'], strtotime($wconfig['now_time'].' day'));
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
        return View::fetch('common@widget/datetime');
    }
}
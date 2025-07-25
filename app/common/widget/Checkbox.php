<?php
namespace app\common\widget;

use think\facade\View;

class Checkbox
{
    /**
     * @Title: index
     * @Description: todo(普通Checkbox挂件)
     * @param array $data       【编辑操作时旧数据集合】
     * @param array $wconfig    【配置项】
     * <pre>
     *      name                组件标签name属性值，对应数据库字段【必须】
     *      title               组件标题【必须】
     *      default_val         默认值，新增无数据时默认值【非必须】
     *      from                select选项来源【必须其中一项：selectlist,function】
     *      fromcfg             数据来源配置【必填，配合from使用】
     *          selectlist      来自selectlist配置【如sex,status】
     *          function_name   来自函数数据【['函数名', '参数，如果没有可不传']】
     *      title_col           标题占比（默认2）【非必须】
     *      content_col         内容占比（默认6）【非必须】
     *      validate            提示信息【非必须】
     *      validate_col        提示信息占比（默认4）【非必须】
     *      disabled            禁用【非必须】
     * </pre>
     * @return string
     * @author 苏晓信 <654108442@qq.com>
     * @date 2018年10月9日
     * @throws
     */
    public function index($data, $wconfig)
    {
        if ( isset($data[$wconfig['name']]) ){
            $wconfig['widget_val'] = $data[$wconfig['name']];
        }else{
            $wconfig['widget_val'] = '';
        }
        
        $wconfig['title_col'] = isset($wconfig['title_col']) ? $wconfig ['title_col'] : '2';
        $wconfig['content_col'] = isset($wconfig['content_col']) ? $wconfig ['content_col'] : '6';
        $wconfig['validate_col'] = isset($wconfig['validate_col']) ? $wconfig ['validate_col'] : '4';
        $wconfig['validate'] = isset($wconfig['validate']) ? $wconfig ['validate'] : '';
        
        /* 默认值 */
        if ($wconfig['widget_val'] == '' && isset($wconfig['default_val']) && $wconfig['default_val'] != ''){
            $wconfig['widget_val'] = $wconfig['default_val'];
        }
        
        if ( !isset($wconfig['from']) || ($wconfig['from'] != 'selectlist' && $wconfig['from'] != 'function') ){
            return '<div class="form-group"><label class="col-sm-'.$wconfig['title_col'].' control-label">'.$wconfig['title'].'</label><div class="col-sm-'.$wconfig['content_col'].'"><span class="help-block">checkbox来源 from 必须是 selectlist 或 function</span></div></div>';
        }
        if ( !isset($wconfig['fromcfg']) || empty($wconfig['fromcfg'])){
            return '<div class="form-group"><label class="col-sm-'.$wconfig['title_col'].' control-label">'.$wconfig['title'].'</label><div class="col-sm-'.$wconfig['content_col'].'"><span class="help-block">checkbox数据来源 fromcfg 不能为空</span></div></div>';
        }
        
        $optionListData = [];
        if ( $wconfig['from'] == 'selectlist' ){
            if ( config('selectlist.'.$wconfig['fromcfg']) ){
                $selectlist = config('selectlist.'.$wconfig['fromcfg']);
                $optionListData = $selectlist['data'];
            }else{
                return '<div class="form-group"><label class="col-sm-'.$wconfig['title_col'].' control-label">'.$wconfig['title'].'</label><div class="col-sm-'.$wconfig['content_col'].'"><span class="help-block">配置文件selectlist配置项：'.$wconfig['fromcfg'].' 不存在</span></div></div>';
            }
        }elseif( $wconfig['from'] == 'function' ){
            if ( function_exists($wconfig['fromcfg'][0]) ){
                $param = [];
                foreach ($wconfig['fromcfg'] as $k => $v){
                    if($k > 0){
                        $param[] = $v;
                    }
                }
                $optionListData = call_user_func_array($wconfig['fromcfg'][0], $param);
            }else{
                return '<div class="form-group"><label class="col-sm-'.$wconfig['title_col'].' control-label">'.$wconfig['title'].'</label><div class="col-sm-'.$wconfig['content_col'].'"><span class="help-block">配置function函数：'.$wconfig['fromcfg'][0].' 不存在</span></div></div>';
            }
        }
        $optionList = [];
        foreach ($optionListData as $k => $v){
            $optionList[$k]['value'] = $k;
            $optionList[$k]['html'] = $v;
            $widget_val_arr = del_arr_empty(explode(',', $wconfig['widget_val']));
            if ( in_array($k, $widget_val_arr) ){
                $optionList[$k]['checked'] = 'checked="checked"';
            }else{
                $optionList[$k]['checked'] = '';
            }
        }
        
        /* 是否禁用 */
        if (isset($wconfig['disabled']) && $wconfig['disabled'] === true){
            $wconfig['disabled'] = 'disabled="disabled"';
        }else{
            $wconfig['disabled'] = '';
        }
        
        View::assign('optionList', $optionList);
        View::assign('wconfig', $wconfig);
        return View::fetch('common@widget/checkbox');
    }
}
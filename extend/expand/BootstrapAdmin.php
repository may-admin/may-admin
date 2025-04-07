<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace expand;

use think\Paginator;

class BootstrapAdmin extends Paginator
{

    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton(string $text = "&laquo;"): string
    {

        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url(
            $this->currentPage() - 1
        );

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton(string $text = '&raquo;'): string
    {
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->currentPage() + 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    //统计信息
    protected function info(){
        return "<li class='page-item'><span class='page-link'>".$this->total.lang('page_total')."</span></li>";
    }

    //分页选择
    protected function select()
    {
        $options = ceil($this->total/$this->listRows);
        $str = "<select>";
        for ($i=1; $i<=$options; $i++){
            if ( $i == $this->currentPage){
                $str .= "<option value=\"".$i."\" data-href=\"".$this->url($i)."\" selected='selected' >".$i."</option>";
            }else{
                $str .= "<option value=\"".$i."\" data-href=\"".$this->url($i)."\" >".$i."</option>";
            }
        }
        $str .= "</select>";
        return $str;
    }

    //设置每页数量
    protected function setListRows()
    {
        $list_rows = config('selectlist.list_rows')['data'];
        $url = $this->url(1);
        $param = parse_url($url)['query'];
        $param_arr = explode('&', $param);
        $query_str = '';
        foreach($param_arr as $k => $v){
            if($k == '0'){ $query_str .= '?'; }
            $arr = explode('=', $v);
            if($arr[0] != 'list_rows'){ $query_str .= $v.'&'; }
        }
        $url_str = request()->baseUrl(true).$query_str.'list_rows=';
        
        $str = "<li class='page-item'><span class='page-link'>".lang('list_rows')."</span></li>";
        $str .= "<select>";
        foreach($list_rows as $k => $v){
            if($k == $this->listRows){
                $str .= "<option value=\"".$k."\" data-href=\"".$url_str.$v."\" selected='selected' >".$v."</option>";
            }else{
                $str .= "<option value=\"".$k."\" data-href=\"".$url_str.$v."\" >".$v."</option>";
            }
        }
        $str .= "</select>";
        return $str;
        
        
    }

    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks(): string
    {
        if ($this->simple) {
            return '';
        }

        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null,
        ];

        $side   = 3;
        $window = $side * 2;

        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + 2), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        // dd($this->options['modal_ajax']);
        if ($this->hasPages()) {
            if ($this->simple) {
                return sprintf(
                    '<ul class="pagination">%s %s</ul>',
                    $this->getPreviousButton(),
                    $this->getNextButton()
                );
            }elseif(isset($this->options['modal_ajax']) && $this->options['modal_ajax'] === true){
                return sprintf(
                    '<ul class="pagination">%s %s %s %s</ul>',
                    $this->info(),
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton()
                );
            }else {
                return sprintf(
                    '<ul class="pagination">%s %s %s %s %s %s</ul>',
                    $this->info(),
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton(),
                    $this->select(),
                    $this->setListRows()
                );
            }
        }
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  string $page
     * @return string
     */
    protected function getAvailablePageWrapper(string $url, string $page): string
    {
        if(isset($this->options['modal_ajax']) && $this->options['modal_ajax'] === true){
            return '<li class="page-item"><a class="page-link modal-page-ajax" data-href="' . htmlentities($url) . '">' . $page . '</a></li>';
        }else{
            return '<li class="page-item"><a class="page-link" href="' . htmlentities($url) . '">' . $page . '</a></li>';
        }
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper(string $text): string
    {
        return '<li class="page-item disabled"><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper(string $text): string
    {
        return '<li class="page-item active"><span class="page-link">' . $text . '</span></li>';
    }

    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots(): string
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls): string
    {
        $html = '';

        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }

        return $html;
    }

    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  string    $page
     * @return string
     */
    protected function getPageLinkWrapper(string $url, string $page): string
    {
        if ($this->currentPage() == $page) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }
}

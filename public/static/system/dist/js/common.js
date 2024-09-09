$(function(){
    $.pjax.defaults.timeout = 5000;
    $.pjax.defaults.maxCacheLength = 0;
    
    $(document).pjax('a:not(a[target="_blank"])', {container:'#pjax-container', fragment:'#pjax-container'});
    
    $(document).on('submit', 'form[pjax-search]', function(event) {
        $.pjax.submit(event, {container:'#pjax-container', fragment:'#pjax-container'})
    })
    
    $(document).on('pjax:send', function() { NProgress.start(); });
    $(document).on('pjax:complete', function() { NProgress.done(); });
    
    //分页select选择页数
    $('body').off('change', '.pagination select');
    $('body').on('change', '.pagination select', function(event){
        var _href = $(this).find('option:selected').data('href');
        $.pjax({url:_href, container: '#pjax-container', fragment:'#pjax-container'})
    });
    
    //分页input选择页数
//    $('body').off('keypress', '.pagination input');
//    $('body').on('keypress', '.pagination input', function(event){
//        if(event.keyCode == '13'){
//            var _href = $(this).data('href')+'&list_rows='+$(this).val();
//           $.pjax({url:_href, container: '#pjax-container', fragment:'#pjax-container'})
//        }
//    });
    
    //下拉选择搜索
    $('body').off('click', '.search-ul li a');
    $('body').on('click', '.search-ul li a', function(event){
        var _this = $(this);
        var _field = _this.data('field');
        var _html = _this.html();
        var _box = _this.closest('.input-group-btn');
        _box.find('.search_field').val(_field);
        _box.find('.dropdown-toggle span').html(_html);
        _box.next('input').attr('placeholder', _html);
    });
    
    //提交
    $('body').off('click', '.submits');
    $('body').on('click', '.submits', function(event){
        var _this = $(this);
        _this.button('loading');
        var form = _this.closest('form');
        if(form.length){
            var ajax_option={
                dataType:'json',
                success:function(data){
                    if(data.status == '0'){
                        layer.msg(data.info, {icon: 1});
                        if(data.url != ''){
                            $.pjax({url: data.url, container: '#pjax-container', fragment:'#pjax-container'});
                        }
                        _this.button('reset');
                    }else{
                        _this.button('reset');
                        layer.msg(data.info, {icon: 2});
                    }
                }
            }
            form.ajaxSubmit(ajax_option);
        }
    });
    
    //状态status列表修改（只能进行0和1值的切换）
    $('body').off('click', 'td a.editimg');
    $('body').on('click', 'td a.editimg', function(event){
        var addclass;
        var removeclass;
        var pvalue;   //提交值
        var _this = $(this);
        var field = _this.data('field');
        var id = _this.data('id');
        var value = _this.data('value');
        var url = _this.data('url');
        if ( value == 1){
            pvalue = 0;
            addclass = 'fa-check-circle text-green';
            removeclass = 'fa-times-circle text-red';
        }else{
            pvalue = 1;
            addclass = 'fa-times-circle text-red';
            removeclass = 'fa-check-circle text-green';
        }
        var dataStr = jQuery.parseJSON( '{"id":"'+id+'","'+field+'":"'+pvalue+'"}' );   //字符串转json
        $.ajax({
            type : 'post',
            url : url,
            dataType : 'json',
            data : dataStr,
            success : function(data) {
                if(data.status == '0'){
                    _this.data('value', pvalue);
                    _this.removeClass(addclass);
                    _this.addClass(removeclass);
                    layer.msg(data.info, {icon: 1});
                }else{
                    layer.msg(data.info, {icon: 2});
                }
            }
        });
    });
    
    //列表编辑文本
    $('body').off('click', '.list-write');
    $('body').on('click', '.list-write', function(event){
        var _this = $(this);
        var _html = _this.html();
        _this.hide().after('<input class="list-write-input" type="text" value="'+_html+'" onblur="list_write(this);" />');
        $('.list-write-input').focus();
    });
    
    //单条删除-批量删除
    $('body').off('click', '.btn-confirm');
    $('body').on('click', '.btn-confirm', function(event){
        event.preventDefault();
        var _this = $(this);
        var title = _this.data('title')?_this.data('title'):'删除';
        var url_del = _this.data('url')||'';
        var message = _this.data('message')?_this.data('message'):'确认操作？';
        var id = _this.data('id')||'';
        if(id == ''){       //批量删除
            var str = '';
            var table_box = _this.closest('.box-header').next('.box-body').find('.table tr td input[name="id[]"]');
            $(table_box).each(function(){
                if(true == $(this).is(':checked')){
                    str += $(this).val() + ',';
                }
            });
            if(str.substr(str.length-1)== ','){
                id = str.substr(0, str.length-1);
            }
        }
        if(id && url_del){
            layer.confirm(message, {
                title : title,
                btn: ['确定', '取消'] //按钮
            }, function(){
                $.ajax({
                    type : 'post',
                    url : url_del,
                    dataType : 'json',
                    data : { id:id, },
                    success : function(data) {
                        if(data.status == '0'){
                            layer.msg(data.info, {icon: 1});
                            if(data.url != ''){
                                $.pjax({url: data.url, container: '#pjax-container', fragment:'#pjax-container'})
                            }
                        }else{
                            layer.msg(data.info, {icon: 2});
                        }
                    }
                });
            });
        }
    });
    
    //菜单样式
    $('body').off('click', '.sidebar-menu li.treeview li');
    $('body').on('click', '.sidebar-menu li.treeview li', function(event){
        var _this = $(this);
        $('.sidebar-menu li').removeClass('active');
        _this.addClass('active');
        _this.closest('li.treeview').addClass('active');
    });
    $('body').off('click', '.sidebar-menu > li:eq(0)');
    $('body').on('click', '.sidebar-menu > li:eq(0)', function(event){
        $('.sidebar-menu li').removeClass('active');
        var _this = $(this).addClass('active');
    });
})

function index_list_init(){
    /*ajax页面加载icheck，有该控件的页面才需要*/
    $('.icheck').iCheck({ checkboxClass:'icheckbox_flat-green', radioClass: 'iradio_flat-green'});
    
    /*全选-反选*/
    $('.checkbox-toggle').on('ifChecked', function(event){
        $(this).closest('.table').find('tr td input[type="checkbox"]').iCheck('check');
    }).on('ifUnchecked', function(event){
        $(this).closest('.table').find('tr td input[type="checkbox"]').iCheck('uncheck');
    });
}

function list_write(input){
    var _input = $(input);
    var _prev = _input.prev();
    
    if(_input.val() != _prev.html()){
        var params = {};
        params['id'] = _prev.data('id');
        params[_prev.data('field')] = _input.val();
        $.ajax({
            type : 'post',
            url : _prev.data('url'),
            dataType : 'json',
            data : params,
            success : function(data) {
                if(data.status == '0'){
                    layer.msg(data.info, {icon: 1});
                    _prev.html(_input.val());
                }else{
                    layer.msg(data.info, {icon: 2});
                }
            }
        });
    }
    _input.remove();
    _prev.show();
}
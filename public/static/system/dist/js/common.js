$(function(){
    $.pjax.defaults.timeout = 5000;
    $.pjax.defaults.maxCacheLength = 0;
    
    $(document).pjax('a:not(a[target="_blank"])', {container:'#pjax-container', fragment:'#pjax-container'});
    
    $(document).on('submit', 'form[pjax-search]', function(event) {
        $.pjax.submit(event, {container:'#pjax-container', fragment:'#pjax-container'})
    })
    
    $(document).on('pjax:send', function() { NProgress.start(); });
    $(document).on('pjax:complete', function() { NProgress.done(); });
    
    //正常分页select选择页数
    $('body').off('change', '.pagination select');
    $('body').on('change', '.pagination select', function(event){
        var _href = $(this).find('option:selected').data('href');
        $.pjax({url:_href, container: '#pjax-container', fragment:'#pjax-container'})
    });
    
    //modal弹框分页按钮
    $('body').off('click', '.pagination .modal-page-ajax');
    $('body').on('click', '.pagination .modal-page-ajax', function(event){
        var _this = $(this);
        var _href = _this.data('href');
        $.ajax({
            type : 'get',
            url : _href,
            dataType : 'json',
            success : function(html) {
                _this.closest('.modal-content').html(html);
            }
        });
    });
    
    //上传挂件【上传】按钮
    $('body').off('change', '.widget-upload-btn');
    $('body').on('change', '.widget-upload-btn', function(event){
        var _this = $(this);
        var _url = _this.data('url');
        var _dir = _this.data('dir');
        var _tag = _this.data('tag');
        
        var formData = new FormData();
        formData.append('dir', _dir);
        formData.append('tag', _tag);
        formData.append('imgFile', _this[0].files[0]);
        
        var _button_html = _this.next('.btn').html();
        
        $.ajax({
            type: 'post',
            url: _url,
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function(event) {
                        if(event.lengthComputable){
                            var percentComplete = event.loaded / event.total;
                            percentComplete = parseInt(percentComplete * 100) + '%';
                            _this.next('.btn').html(percentComplete)
                        }
                    }, false);
                }
                return xhr;
            },
            success: function(res) {
                if(res.code == '0'){
                    if(_tag == 'image'){   //单图上传
                        _this.closest('.up-box').prev('.widget-upload-input').val(res.link);
                        _this.closest('.up-box').find('.show-image-box').attr('href', res.link);
                        _this.closest('.up-box').find('.show-image-box img').attr('src', res.link);
                    }else if(_tag == 'images'){   //图组上传
                        if(_this.closest('.up-box').prev('.widget-upload-input').val() == ''){
                            _this.closest('.up-box').prev('.widget-upload-input').val(res.link);   //输入框添加数据
                        }else{
                            _this.closest('.up-box').prev('.widget-upload-input').val(_this.closest('.up-box').prev('.widget-upload-input').val()+','+res.link);
                        }
                        var _html = '<div class="items"><div class="actions"><span class="move"><i class="fa fa-arrows"></i></span><span class="sortables-upload-del"><i class="fa fa-trash"></i></span></div>';
                        _html += '<a class="img-box" href="'+res.link+'" target="_blank"><img src="'+res.link+'" /></a></div>';
                        _this.closest('.input-group').next('.sortables').append(_html);
                    }
                    layer.msg(res.message, {icon: 1});
                }else{
                    layer.msg(res.message, {icon: 2});
                }
                _this.next('.btn').html(_button_html);
                _this.val('');
            }
        });
    });
    
    //上传挂件【浏览】按钮
    $('body').off('click', '.widget-upload-manage');
    $('body').on('click', '.widget-upload-manage', function(event){
        var _this = $(this);
        var _url = _this.data('url');
        var _tag = _this.data('tag');
        
        if($('.widgetUploadModal').length == 0){
            $('body').append('<div class="modal fade widgetUploadModal"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"></div></div></div>');
        }
        $('.widgetUploadModal').modal('show');
        $.ajax({
            type: 'post',
            url: _url,
            dataType: 'html',
            data: {format: 'image', back: _this.data('back-btn'), tag: _tag},
            success: function(html) {
                $('.widgetUploadModal .modal-content').html(html);
            }
        });
    });
    
    //上传挂件【浏览-选择】按钮
    $('body').off('click', '.widget-upload-select');
    $('body').on('click', '.widget-upload-select', function(event){
        var _this = $(this);
        var _url = _this.data('url');
        var _back = _this.data('back-btn');
        var _tag = _this.data('tag');
        
        var _back_btn = $('.widget-upload-manage[data-back-btn="'+_back+'"]');
        if(_tag == 'image'){   //单图选择
            _back_btn.closest('.up-box').prev('.widget-upload-input').val(_url);
            _back_btn.closest('.up-box').find('.show-image-box').attr('href', _url);
            _back_btn.closest('.up-box').find('.show-image-box img').attr('src', _url);
        }else if(_tag == 'images'){   //图组选择
            if(_back_btn.closest('.up-box').prev('.widget-upload-input').val() == ''){
                _back_btn.closest('.up-box').prev('.widget-upload-input').val(_url);   //输入框添加数据
            }else{
                _back_btn.closest('.up-box').prev('.widget-upload-input').val(_back_btn.closest('.up-box').prev('.widget-upload-input').val()+','+_url);
            }
            var _html = '<div class="items"><div class="actions"><span class="move"><i class="fa fa-arrows"></i></span><span class="sortables-upload-del"><i class="fa fa-trash"></i></span></div>';
            _html += '<a class="img-box" href="'+_url+'" target="_blank"><img src="'+_url+'" /></a></div>';
            _back_btn.closest('.input-group').next('.sortables').append(_html);
        }
        $('.widgetUploadModal').modal('hide');
    });
    
    //上传挂件【图组-删除】按钮
    $('body').off('click', '.sortables-upload-del');
    $('body').on('click', '.sortables-upload-del', function(event){
        var _this = $(this);
        var _val = '';
        var _sortables =_this.closest('.sortables');
        
        _this.closest('.items').remove();
        _sortables.find('.img-box img').each(function(i, e){
            var _this = $(this);
            _val += _this.attr('src')+',';
        })
        _val = (_val.substring(_val.length - 1) == ',') ? _val.substring(0, _val.length - 1) : _val;
        _sortables.prev('.input-group').find('.widget-upload-input').val(_val);
    });
    
    //全选-反选
    $('body').off('click', '.table-check');
    $('body').on('click', '.table-check', function(event){
        var _this = $(this);
        var table_check = _this.closest('.table').find(".table-check");
        var check_num = table_check.length;
        table_check.each(function() {
            if($(this).prop('checked') === false){
                check_num--;
            }
        });
        var _check_toggle = _this.closest('.table').find(".table-check-toggle");
        if(check_num == table_check.length){
            _check_toggle.prop('checked', true);
            _check_toggle[0].indeterminate = false;
            _check_toggle.removeClass('indeterminate');
        }else if(check_num == 0){
            _check_toggle.prop('checked', false);
            _check_toggle[0].indeterminate = false;
            _check_toggle.removeClass('indeterminate');
        }else{
            _check_toggle.prop('checked', false);
            _check_toggle[0].indeterminate = true;
            _check_toggle.addClass('indeterminate');
        }
    });
    $('body').off('click', '.table-check-toggle');
    $('body').on('click', '.table-check-toggle', function(event){
        var _this = $(this);
        if(_this.hasClass('indeterminate')){
            var table_check = _this.closest('.table').find(".table-check").prop('checked', true);
            _this[0].indeterminate = false;
            _this.removeClass('indeterminate');
        }else if($(this).prop('checked') === true){
            var table_check = _this.closest('.table').find(".table-check").prop('checked', true);
        }else if($(this).prop('checked') === false){
            var table_check = _this.closest('.table').find(".table-check").prop('checked', false);
        }
    });
    
    //提交
    $('body').off('click', '.submits');
    $('body').on('click', '.submits', function(event){
        var _this = $(this);
        _this.addClass('disabled').prop("disabled", true);
        _this.data('loading-html', _this.html());
        _this.html(_this.data('loading-text'));
        var form = _this.closest('form');
        if(form.length){
            var ajax_option={
                dataType:'json',
                success:function(res){
                    if(res.code == '0'){
                        layer.msg(res.message, {icon: 1});
                        if(res.url != ''){
                            $.pjax({url: res.url, container: '#pjax-container', fragment:'#pjax-container'});
                        }
                    }else{
                        layer.msg(res.message, {icon: 2});
                    }
                    _this.removeClass('disabled').prop("disabled", false);
                    _this.html(_this.data('loading-html'));
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
            success : function(res) {
                if(res.code == '0'){
                    _this.data('value', pvalue);
                    _this.removeClass(addclass);
                    _this.addClass(removeclass);
                    layer.msg(res.message, {icon: 1});
                }else{
                    layer.msg(res.message, {icon: 2});
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
                    success : function(res) {
                        if(res.code == '0'){
                            layer.msg(res.message, {icon: 1});
                            if(res.url != ''){
                                $.pjax({url: res.url, container: '#pjax-container', fragment:'#pjax-container'})
                            }
                        }else{
                            layer.msg(res.message, {icon: 2});
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
    $('body').off('click', '.sidebar-menu > li:eq(0),.sidebar-menu > li:eq(-1)');
    $('body').on('click', '.sidebar-menu > li:eq(0),.sidebar-menu > li:eq(-1)', function(event){
        $('.sidebar-menu li').removeClass('active');
        var _this = $(this).addClass('active');
    });
})

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
            success : function(res) {
                if(res.code == '0'){
                    layer.msg(res.message, {icon: 1});
                    _prev.html(_input.val());
                }else{
                    layer.msg(res.message, {icon: 2});
                }
            }
        });
    }
    _input.remove();
    _prev.show();
}
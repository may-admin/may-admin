$(function () {
    $('body').off('click', '.login');
    $('body').on("click", '.login', function(event){
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
                        window.location.href = res.url; 
                    }else{
                        layer.msg(res.message, {icon: 2});
                        if(res.code == '2'){
                            $('#captcha').click();
                        }
                        _this.removeClass('disabled').prop("disabled", false);
                        _this.html(_this.data('loading-html'));
                    }
                }
            }
            form.ajaxSubmit(ajax_option);
        }
    });
});
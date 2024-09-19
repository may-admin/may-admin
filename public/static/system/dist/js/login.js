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
                success:function(data){
                    if(data.status == '0'){
                        layer.msg(data.info, {icon: 1});
                        window.location.href = data.url; 
                    }else{
                        layer.msg(data.info, {icon: 2});
                        if(data.status == '2'){
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
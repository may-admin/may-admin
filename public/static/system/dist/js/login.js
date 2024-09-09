$(function () {
    $('body').off('click', '.login');
    $('body').on("click", '.login', function(event){
        var _this = $(this);
        _this.button('loading');
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
                        $('#captcha').click();
                        _this.button('reset');
                    }
                }
            }
            form.ajaxSubmit(ajax_option);
        }
    });
});
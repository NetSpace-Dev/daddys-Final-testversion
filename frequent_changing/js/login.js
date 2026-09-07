$(function () {
    "use strict";
    $(document).on('click', '.set_credentials', function(){
       let username = $(this).attr("data-username");
       let password = $(this).attr("data-password");
       $("#email_address").val(username);
       $("#password").val(password);
    });

    $('.toggle').on('click', function() {
        $('.container').stop().addClass('active');
    });

    $('.close').on('click', function() {
        $('.container').stop().removeClass('active');
    });

    // ---- Single pincode input -----
    var loginpin = $('#loginpin').pinlogin({
        fields : 4,
        complete : function(pin){
            $("#login_pin").val(pin);
            $(".submit_login").click();
            loginpin.disable();
        },
    });

    function show_hide(btn_type) {
        if(btn_type==1){
            $(".div_1").show(200);
            $(".div_2").hide(200);
            $("#loginpin_pinlogin_0").focus();
        }else{
            $(".div_1").hide(200);
            $(".div_2").show(200);
        }
    }

   let login_type_hidden =  $("#login_type_hidden").val();
    show_hide(login_type_hidden);
    //generate random code button
    $('body').on('click', '.login_type', function (e) {
        e.preventDefault();

        $(".login_type").removeClass('active_login_btn');
        $(this).addClass('active_login_btn');

        let btn_type = Number($(this).attr('data-id'));
        let login_title = $(this).text();
        $(".login_title").text(login_title);
        $("#login_type_hidden").val(btn_type);
        show_hide(btn_type);
    });


});
$(document).ready(function () {   
    $("a.image_gallery").fancybox({'overlayColor'          : 'black'});
    
    $("a.video_gallery").click(function() {
		$.fancybox({
			'padding'		: 0,
                        'overlayColor'          : 'black',
			'autoScale'		: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'title'			: this.title,
			'width'			: 640,
			'height'		: 385,
			'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type'			: 'swf',
			'swf'			: {
			'wmode'				: 'transparent',
			'allowfullscreen'	: 'true'
			}
		});

		return false;
    });
        
    $("#logInButton").click(function(event){
        event.preventDefault();
        $('#login_manager').slideToggle();
    });
    
    $('.close_log_button').click(function(event){
        event.preventDefault();
        $('#login_manager').slideUp();
    });
    
    $('#usernameInput, #passwordInput').click(function(){
        $(this).val('');
    });
    
    $('.delivery_toogle').click(function(){
        $("#delivery_adress").slideToggle();
    });
    
    $('.orders .orders_row_odd').click(function(){
        window.location = $(this).find('.fst_column').find('a').attr('href');
    });
    
    $(".eshop_menu").mouseenter(function(event){
       if($(this).find("div.eshop_submenu").length > 0) {
           $(this).find("div.eshop_submenu").slideDown(100);
       }
    });
    
    $(".eshop_menu").mouseleave(function(event){
       if($(this).find("div.eshop_submenu").length > 0) {
           $(this).find("div.eshop_submenu").slideUp(100);
       }
    });
    
});
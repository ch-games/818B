jQuery(".banner").slide({mainCell:".bd ul",autoPlay:!0}),
jQuery(".txtScroll-top").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:!0,effect:"top",autoPlay:!0,vis:1}),
$(".percent span").css({width:function(){return $(this).attr("data-per")}});
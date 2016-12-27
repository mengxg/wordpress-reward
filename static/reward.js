jQuery(function ($) {
    $(".reward-button").mouseover(function(){
		var code_left=-(($('.reward-code').outerWidth()-$('.reward-button').outerWidth())/2);
		var code_top=-($('.reward-code').outerHeight()+20);
		$(".reward-code").css('left',code_left+'px');
		$(".reward-code").css('top',code_top+'px');
        $(".reward-code").css('display','block');
    }).mouseout(function(){
        $(".reward-code").css('display','none');
    })
})
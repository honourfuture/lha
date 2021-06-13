/**
 * 
 */
$().ready(function(){
    var h = $(".main_right").outerHeight();
    $(".main_left").css("min-height",h);
    $(".main_left li p").click(function(){
    	$(this).toggleClass("down");
    	$(this).next(".son_menu").slideToggle();
    });
    $(".main_left a").click(function(){
    	$(this).addClass("on");
    	$(this).siblings().removeClass("on");
    });
    //退出
    $(".act").click(function(){
        $(this).find("img").toggleClass("down");
        $(".logout").slideToggle();
    });
    //全选
    $("#selectAll").click(function(e){
        $("input:checkbox[name='ck[]']").prop("checked",this.checked);
    });
});
function showDiv(div){
	document.getElementById(div).style.display="block";
    //$(this).show();
    $("body").height($(window).height()).css({"overflow-y":"hidden"});
}
function closeDiv(div){
	document.getElementById(div).style.display="none";
    //$(this).hide();
    $("body").height($(window).height()).css({"overflow-y":"auto"});
}
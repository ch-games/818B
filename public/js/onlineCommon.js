function showDetailInfo(showType) {
	$( "#onlinedialog" ).dialog({
		autoOpen: false,
		width:1024,
		height:520,
        buttons: {
            "关闭": function() {
                $( this ).dialog( "close" );
            }
        }
	});
	if(showType==0){
		$( "#onlinedialog" ).html("<iframe width=\"100%\" style=\"overflow-y:auto\" height=\"100%\" frameborder=\"0\" src="+ctx+"/agent/showOnlineDetail?op=1&type=hy&showType="+showType+"></iframe>");
	}else{
		$( "#onlinedialog" ).html("<iframe width=\"100%\" style=\"overflow-y:auto\" height=\"100%\" frameborder=\"0\" src="+ctx+"/agent/showOnlineDetail?op=1&type=allOnline&showType="+showType+"></iframe>");
	}
	$( "#onlinedialog" ).dialog( "open" );
} 
function showDepositDetailInfo() {
	$("body").append("<div id='depositAlterBox' style='display:none'><iframe width=\"100%\" style=\"overflow-y:auto\" height=\"100%\" frameborder=\"0\" src=\""+ctx+"/agent/DrawCheckQueryServlet?type=checkControl&abnormalStatus=1&clearStatus=0&isclear=1\"></iframe></div>");
	$( "#depositAlterBox" ).dialog({
		autoOpen: false,
		width:$(window).width()-20,
		height:$(window).height()-20,
		close:function(){
			$("#depositAlterBox").remove();
		},
        buttons: {
            "关闭": function() {
                $( this ).dialog( "close" );
            }
        }
	});
	$( "#depositAlterBox" ).dialog( "open" );
}
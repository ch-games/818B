(function($) {
	$.member = {
		initnav:function(type){
			var _this = this;
			if(type == 1){
				_this.navName='Hyzx-nav';
				_this.navBody='Hyzx-body';
			}else if(type == 2){
				_this.navName='nav2';
				_this.navBody='Hyzx-content';
			}else{
				_this.navName='Hyzx-conNav';
				_this.navBody='Hyzx-table-content';
			}
		},
		/*导航切换*/
		changeNav:function(file,parameter,t,is_carried){
			if(!arguments[3]) is_carried = 0;
			var _this = this;
			_this.initnav(t);
			$('.'+_this.navName).children().removeClass('active');
			$('.'+_this.navName).children("a[data='"+parameter+"']").addClass('active');
			$('.'+_this.navName).children("span").html($('.'+_this.navName).children("a[data='"+parameter+"']").html());
			if(is_carried == 0){
				_this.getPage(file,parameter,_this.navBody);
			}
		},
		/*框架自动适配高度*/
		atHeight:function(){
			var height = $('#Hyzx-pageBody').height();
            var height = Number(height)+10;
            var ifr = window.parent.document.getElementById("memiframe");
            ifr.style.height = height+'px';
		},
		/*加载遮罩层*/
		maskLayer:function(){
			$('body').prepend('<div id="HyZzc"><img src="/public/images/dzi/ajax-loader-white.gif" id="HyZzc1" height="150"/></div>');
			$('#HyZzc').css({
				padding:        0,
				margin:         0,
				width:          '100%',
				height:         '100%',
				top:            '0',
				left:           '0',
				textAlign:      'center',
				color:          '#000',
				border:         'none',
				"position":     "absolute",
				"z-index":      1000,
				"opacity":      0.7,
				"background-color": "#000000"
			});
			var zzHeight = Number($('body').height());
			if (zzHeight <= 300) {zc1H = 120;}
			else{zc1H = 150;}
			var topH = Math.ceil((zzHeight - zc1H) / 2);
			$('#HyZzc1').css({
				'height': zc1H+'px',
				'margin-top': topH+"px"
			});
		},
		/*跳转页面*/
		getPage:function(file,parameter,cs){
			var _this = this;
			$.ajax({
				type: "POST",
				url: "/index.php/member/new/"+file+"/"+parameter,
				beforeSend: function(){
					_this.maskLayer();
				},
				success: function(msg){
					$('#HyZzc').remove();
					$('.'+cs).html(msg);
		            _this.atHeight();
				}
			});
		},
		/*提示语*/
		alertpop:function(a,b){
			var _this = this;
			$('.Hyzx-tk').show();
			$('#messenger-message-inner').html(b);
			if(a == 'success'){
				$('#messenger-message-inner').attr('class','messenger-message-inner');
				_this.atClose(2); //成功2秒关闭
			}else if(a == 'prompt'){
				$('#messenger-message-inner').attr('class','messenger-success2');
				_this.atClose(3); //警告3秒关闭
			}else{
				$('#messenger-message-inner').attr('class','messenger-success3');
				_this.atClose(5); //失败5秒关闭
			}
			$('.messenger-close').click(function(){
		    	$('.Hyzx-tk').hide();
		    });
		},
		atClose:function(t) {
			setTimeout(function(){
		    	$('.Hyzx-tk').hide();
		    },1000*t);
		},
		JumpHYPage:function(ctr,fun,level){
			var _this = this;
			var f = fun.split('-');
			var l = level.split('-');
			var p = [1,0];
			$.each(f,function(i, ele) {
				_this.changeNav(ctr,ele,l[i],p[i]);
			});
		}
	}
})(jQuery);

$(function(){
	//$.member.alertpop('success','这个是成功');
	//$.member.alertpop('prompt','这个是感叹号');
	//$.member.alertpop('','这个是失败！');
	var url = window.location.href;
	id = url.split("=");//兼容老版本
	if(id[1] == 4){
		$.member.changeNav('Account','account_mem_index',1);
	}else if(id[1] == 1){
		$.member.changeNav('Bank','bank_onlinein_index',1);
	}else if(id[1] == 2){
		$.member.changeNav('Bank','bank_onlineout_index',1);
	}else if(id[1] == 3){
		$.member.changeNav('Bank','bank_transf_index',1);
	}else if(id[1] == 5){
		$.member.changeNav('Transaction','transaction_bet_index',1);
	}else if(id[1] == 6){
		$.member.changeNav('Report','report_statist_index',1);
	}else if(id[1] == 7){
		$.member.changeNav('Latestnews','latestnews_new_index',1);
	}else if(id[1] == 8){
		$.member.changeNav('Memnews','memnews_personal_index',1);
	}else if(id[1] == 9){
		$.member.changeNav('Transaction','transaction_bet_index',1);
		$.member.changeNav('Transaction','transaction_contacts_index',2);
	}
    
})
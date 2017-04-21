function hiddenDiv(){
	$('#annoument_win').hide();
}
(function($) {
$.fn.announment = function(opts) {
	return this.each(function() {
		if (opts.type == 0) {
			if ($('#annoument_win2').length > 0) {
				var div = $('#annoument_win');
				div.html(opts.content);
			} else {
				container.addClass(opts.glAnoCls);
//				container.css("position", "absolute");
//				container.css("left", opts.left);
//				container.css("top", opts.top);
				
//				var sp = $('<div style="height:80px;"></div>');
//				container.append(sp);
				var container =  $("<div id='annoument_win2' class='wet-pbet'><div id='ann_title' class='titlewei4'><div style='float:left; line-height:25px; padding-left:10px; font-size:12px; color:#FFFFFF'><strong id='an_title'></strong></div><div class='STYLE1' style='float:right;line-height:20px; padding-right:10px; font-size:20px; color:#FFFFFF' onclick='hiddenDiv()'>x</div></div></div>");
				
				var sp = $("<div id='ann_container' class='condng'><div style='clear:both'></div></div>");
				sp.html(opts.content + "<div style='clear:both'></div>");
				container.append(sp);
				container.append($("<div style='clear:both'></div>"));
				
				$(this).append(container);
				$("#an_title").html(opts.title);
				
				$('#annoument_win2').show("slow");
				
				setTimeout(function() {
					$('#annoument_win2').hide("slow");
				},1000*15);
			}
		} else {
			if ($('#annoument_win').length > 0) {
				var title = $('#annoument_win').find('#ann_title');
				title.html(opts.title);
				var content = $('#annoument_win').find('#ann_container');
				content.html(opts.content);		
			} else {
//				var container = createContainDiv(this, opts);
//				createAnoTitleDiv(container, opts);
//				createAnoContentDiv(container, opts);
//				createAnoBottomDiv(container, opts);
				var container =  $("<div id='annoument_win' class='wet-pbet'><div id='ann_title' class='titlewei4'><div style='float:left; line-height:25px; padding-left:10px; font-size:12px; color:#FFFFFF'><strong id='an_title'></strong></div><div class='STYLE1' style='float:right;line-height:20px; padding-right:10px; font-size:20px; color:#FFFFFF' onclick='hiddenDiv()'>x</div></div></div>");
				var sp = $("<div id='ann_container' class='condng'></div>");
				sp.html(opts.content + "<div style='clear:both'></div>");
				container.append(sp);
				container.append($("<div style='clear:both'></div>"));
				$(this).append(container);
				$("#an_title").html(opts.title);
			}
			
			$('#annoument_win').show(600);
			
			setTimeout(function() {
				$('#annoument_win').hide(600);
			},1000*5);
		}
	});
};
function createContainDiv(target, opts) {
	var container = $('<div id="annoument_win"></div>');
	if (opts.hasOwnProperty("containCls")) {
		container.addClass(opts.containCls);
	} 
	
	container.css("width", opts.width);
	container.css("height", opts.height);
	container.css("position", "absolute");
	container.css("left", opts.left);
	container.css("top", opts.top);
	container.hide();
	
	$(target).append(container);
	return container;
}

function createAnoTitleDiv(target, opts) {
	var container = $('<div nowrap></div>');
	
	if (opts.hasOwnProperty("titleCls")) {
		container.addClass(opts.titleCls);
	} 
	container.css("width", target.width());
		
	var lt = $('<span id="ann_title"></span>');
	lt.css("width", target.width()-20);
	lt.css("color", "red");
	lt.html(opts.title);
	
	var btn = $('<span></span>');
	btn.css("marginTop", '3');
	btn.css("cursor", 'pointer');
	btn.css("float", "right");
	btn.html("&nbsp;X&nbsp;");
	btn.click(function() {
		$('#annoument_win').hide(600);
	});
	
	
	container.append(lt);
	container.append(btn);
	target.append(container);
};

function createAnoContentDiv(target, opts) {
	var container = $('<div id="ann_container"></div>');
	container.css("width", target.width());
	container.css("borderWidth", '2');
	container.css("height", 50);
	container.addClass(opts.contextCls);
	container.html(opts.content);

	target.append(container);
};


function createAnoBottomDiv(target, opts) {
	var bottom = $('<div></div>');
	bottom.addClass(opts.titleCls);
	bottom.css("width", target.width());
	bottom.css("height", '10');
	
	target.append(bottom);
}

})(jQuery);

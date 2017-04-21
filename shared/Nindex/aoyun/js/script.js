var stage=document.getElementById("stage");
		var SHEEP_WIDTH=220,TRAN_SPEED=10,SPRIPT_SPEED=100;
		var screenWidth=window.innerWidth;
		window.onresize = function(){
			screenWidth=window.innerWidth;
		}
                createSheep();
		var create=setInterval(createSheep,1500);
		//创建人物
		function createSheep(){
			//限制最多9只人物
			sheeps=stage.childNodes;
			if(sheeps.length>0){
				return false;
			}
			var sheep=new Sheep();
			setMove(sheep);
		}
		//初始化人物
		function Sheep(){
			this.tran_y=0;
			this.sprit_left=0;
			this.spriptSpeed=SPRIPT_SPEED+20*Math.random();
			this.tranSpeed=TRAN_SPEED+TRAN_SPEED*Math.random();
			this.tran_x=SHEEP_WIDTH+400*Math.random();
			this.sheepDom=document.createElement("div");
			this.sheepDom.className="sheep";
			this.sheepDom.style.transform='translate3d('+SHEEP_WIDTH+'px, 0, 0)';
			stage.appendChild(this.sheepDom);
			this.spritTop=0;
			this.catch=false;
			this.x=0;
			this.y=0;
		}
		//移动的人物
		function setMove(sheep){
			var sprit=setInterval(spritMove,sheep.spriptSpeed);
			var walkl=setInterval(walkMove,sheep.spriptSpeed);
			//改变背景图片位置
			function spritMove(){
				if(sheep.sprit_left<(SHEEP_WIDTH*6)){
					sheep.sprit_left=sheep.sprit_left+SHEEP_WIDTH;
				}else{
					sheep.sprit_left=0;
				}
				sheep.sheepDom.style.backgroundPosition=-sheep.sprit_left+'px '+sheep.spritTop+'px';
			}
			//人物移动
			function walkMove(){
				sheep.sheepDom.style.transform='translate3d('+sheep.tran_x+'px, '+sheep.tran_y+'px, 0)';
				if(sheep.tran_x>-(screenWidth+SHEEP_WIDTH+100)){
					sheep.tran_x=sheep.tran_x-sheep.tranSpeed;
				}else{
					clearInterval(walkl);
					clearInterval(sprit);
					stage.removeChild(sheep.sheepDom);
					sheep=null;
				}
			}
			//鼠标按下
			sheep.sheepDom.addEventListener('mousedown',function(ev){
				sheep.spritTop=0;
				sheep.tranSpeed=0;
				sheep.catch=true;
				var oEvent = event || ev;
				sheep.x=oEvent.pageX;
				sheep.y=oEvent.pageY;
			},false);
			//鼠标移开
			sheep.sheepDom.addEventListener('mouseup',function(){
				if(sheep.catch){
					sheep.spritTop=0;
					sheep.tranSpeed=TRAN_SPEED+TRAN_SPEED*Math.random();
					sheep.catch=false;
					sheep.tran_y=0;
				}
			},false);
			//鼠标离开
			sheep.sheepDom.addEventListener('mouseout',function(ev){
				if(sheep.catch){
					sheep.spritTop=0;
					sheep.tranSpeed=TRAN_SPEED+TRAN_SPEED*Math.random();
					sheep.catch=false;
					sheep.tran_y=0;
				}

			},false);
			//鼠标移动，鼠标移动是有问题的，不应该存在鼠标从小羊身上移开的情况，因为此时小羊应该随着鼠标移动
			sheep.sheepDom.addEventListener('mousemove',function(ev){
				if(sheep.catch){
					var oEvent = event || ev;
					sheep.tran_x=sheep.tran_x-(sheep.x-oEvent.pageX);
					sheep.tran_y=sheep.tran_y-(sheep.y-oEvent.pageY);
					sheep.sheepDom.style.transform='translate3d('+sheep.tran_x+'px, '+sheep.tran_y+'px, 0)';
					sheep.x=oEvent.pageX;
					sheep.y=oEvent.pageY;
				}
			},false);
		}
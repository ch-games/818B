/**
 * App函数
 * @type {{init}}
 */




var App = function () {


    var handleFixInputPlaceholderForIE = function () {
        $('input[placeholder]').placeholder();
    }


    var GUNDONGCHAJIAN = function () {
        //浮动框插件���߿ͷ��������
        (function (e) {
            e.fn.fix = function (t) {
                var n = {"float": "left", minStatue: !1, skin: "gray", durationTime: 1e3}, t = e.extend(n, t);
                this.each(function () {
                    var n = e(this), r = n.find(".close_btn"), i = n.find(".show_btn"), s = n.find(".side_content"), o = n.find(".side_list").width(), u = n.find(".side_list"), a = n.offset().top;
                    n.css(t.float, 0), t.minStatue && (e(".show_btn").css("float", t.float), s.css("width", 0), i.css("width", 25)), t.skin && n.addClass("side_" + t.skin), e(window).bind("scroll", function () {
                        var r = a + e(window).scrollTop() + "px";
                        n.animate({top: r}, {duration: t.durationTime, queue: !1})
                    }), r.bind("click", function () {
                        s.animate({width: "0"}, "fast"), i.stop(!0, !0).delay(300).animate({width: "33px"}, "fast").css("float", "right")
                    }), i.click(function () {
                        e(this).animate({width: "0px"}, "fast"), n.width(o), s.stop(!0, !0).delay(200).animate({width: "167px"}, "fast")
                    })
                })
            }
        })(jQuery)

        $(function () {
            $(".adR").fix({float: 'right', minStatue: false, skin: 'green', durationTime: 800})
            $(".adL").fix({float: 'left', minStatue: false, skin: 'green', durationTime: 800})
        });
    }

    var TOPCHAJIAN = function () {
        //滚动置顶插件
        if ('undefined' != typeof($)) {
            $(function () {
                var btnNum = $('#ele-float-top').children().length,
                    wrap = $('#ele-float-top-wrap'),
                    wrapHeight = (btnNum - 1) * (40 + 2),
                    gotop = $('#ele-float-top-up'),
                    speedSet = 300,
                    thebox = $('.ele-float-box-wrap'),
                    boxwrap = '';

                /*if(navigator.userAgent.indexOf("MSIE") != -1) {}*/
                wrap.height(wrapHeight);
                if (wrap.height() == wrapHeight) {
                    $('#ele-float-top').show();
                }

                $('.ele-float-top-code').hover(function () {
                    $(this).children(thebox).stop(true, true).fadeIn(speedSet);
                }, function () {
                    $(this).children(thebox).stop(true, true).fadeOut(speedSet);
                });

                $("#ele-float-top-up").click(function () {
                    $('html,body').animate({scrollTop: 0}, 1000, 'easeOutExpo');
                });
                $(window).scroll(function () {
                    if (navigator.userAgent.indexOf("MSIE") != -1) {
                        var fadeSec = 200;
                    } else {
                        var fadeSec = 300;
                    }
                    if ($(this).scrollTop() > 300) {
                        $('#ele-float-top-up').fadeIn(fadeSec);
                    } else {
                        $('#ele-float-top-up').stop().fadeOut(fadeSec);
                    }
                });
            });
        }
    }


    var Head = function () {
        //// 主選單效果，因載入速度問題

        (function () {
            var $mainnav = $('#main-Menual'),
                $moveline = $('.move'),
                nowTurn = ($mainnav.find('.current').length === 0 ) ? $mainnav.find('#li0001') : $mainnav.find('.current').parent(),
                vout;

            if(typeof $mainnav !='undefined' && $mainnav.length>=1) {
                var followTopNav = function (s) {
                    $moveline.stop().animate({
                        left: nowTurn.position().left,
                        width: nowTurn.outerWidth()
                    }, s);
                }
                followTopNav(1);

                $mainnav.find('li').hover(
                    function () {
                        clearTimeout(vout);
                        $moveline.stop().animate({
                            left: $(this).position().left,
                            width: $(this).outerWidth()
                        }, 'normal');

                        //$(this).find('.subnav').slideDown("slow", "swing");
                    },
                    function () {
                        vout = setTimeout(followTopNav, 100);
                        //$(this).find('.subnav').slideUp("slow", "swing");
                    }
                );
            }
        })();
    }


    var Slot = function () {
        $(document).ready(function () {
            $("ul#gamenav li ").mouseover(function () {
                $(this).find('.nav_sub').stop().css('display', "block");
            }).mouseout(function () {
                $(this).find('.nav_sub').stop().css('display', "none");
            });

        });
    }


    var Transfer = function () {
        $(document).ready(function () {
            $(".ng-hide2").stop().css('display', "none");
            $(".ng-hide3").stop().css('display', "none");
            $(".ng-hide4").stop().css('display', "none");

            $(".row .btn-submit1").click(function () {
                $(".ng-hide1").stop().css('display', "none");
                $(".ng-hide2").stop().css('display', "block");
            });
            $(".row .btn-submit2").click(function () {
                $(".ng-hide1").stop().css('display', "block");
                $(".ng-hide2").stop().css('display', "none");
            });
            $(".row .btn-submit3").click(function () {
                $(".ng-hide2").stop().css('display', "none");
                $(".ng-hide3").stop().css('display', "block");
            });
            $(".btn-submit4").click(function () {
                $(".ng-hide2").stop().css('display', "block");
                $(".ng-hide3").stop().css('display', "none");
            });
            $(".btn-submit5").click(function () {
                $(".ng-hide3").stop().css('display', "none");
                $(".ng-hide4").stop().css('display', "block");
            });
            $(".btn-submit6").click(function () {
                alert(":");
            });
        });
    }

    // var Time2 = function () {
    //     $('#time2').appear();
    //     window._isCountShow = false;
    //     $(document.body).on('appear', '#time2', function (event, $all_appeared_elements) {
    //         if (window._isCountShow) {
    //             return;
    //         }
    //         window._isCountShow = true;
    //         var options1 = {useEasing: true};
    //         var options2 = {useEasing: true, useGrouping: true, separator: '’'};
    //         var demo1 = new CountUp("time1", 0, 18, 0, 1.5, options1);
    //         var demo2 = new CountUp("time2", 0, 218, 0, 1, 5, options2);
    //         demo1.start();
    //         demo2.start();
    //         $('#bar1').width('50%');
    //         $('#bar2').width('50%');
    //     });
    // }

    /*显示当前年份，时间，天*/
    var Time1 = function () {
        var show = document.getElementById("show");
        setInterval(function () {
            var day = "";
            var time = new Date();
            // 程序计时的月从0开始取值后+1
            var t = time.getFullYear() + "/";
            var numOfWeek = time.getDay();

            switch (numOfWeek) {
                case 0:
                    day = "（星期日）";
                    break;
                case 1:
                    day = "（星期一）";
                    break;
                case 2:
                    day = "（星期二）";
                    break;
                case 3:
                    day = "（星期三）";
                    break;
                case 4:
                    day = "（星期四）";
                    break;
                case 5:
                    day = "（星期五）";
                    break;
                case 6:
                    day = "（星期六）";
                    break;
            }

            var m = time.getMonth() + 1;
            if (m < 10) {
                t = t + "0" + m + "/";
            }
            else {
                t = t + m + "/";
            }
            if (time.getDate() < 10) {
                t = t + "0" + time.getDate() + " " + day + " ";
            }
            else {
                t = t + time.getDate() + " " + day + " ";
            }
            if (time.getHours() < 10) {
                t = t + "0" + time.getHours() + ":";
            }
            else {
                t = t + time.getHours() + ":";
            }
            if (time.getMinutes() < 10) {
                t = t + "0" + time.getMinutes() + ":";
            }
            else {
                t = t + time.getMinutes() + ":";
            }
            if (time.getSeconds() < 10) {
                t = t + "0" + time.getSeconds();
            }
            else {
                t = t + time.getSeconds();
            }
            show.innerHTML = t;
        }, 1000);
    }


    var Union = function () {
        $(document).ready(function() {

            $("#Union1").stop().css('display', "block");
            $("#Union2").stop().css('display', "none");
            $("#Union4").stop().css('display', "none");

            $("#Union  #mtab_menual .change01").click(function() {
                $("#Union  #mtab_menual .change01").css("border-bottom","2px solid #9bacd4");
                $("#Union  #mtab_menual .change02").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change04").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change03").css("border-bottom","2px solid #666");
                $("#Union1").stop().css('display', "block");
                $("#Union2").stop().css('display', "none");
                $("#Union4").stop().css('display', "none");
            });


            $("#Union  #mtab_menual .change02").click(function() {
                $("#Union  #mtab_menual .change02").css("border-bottom","2px solid #9bacd4");
                $("#Union  #mtab_menual .change01").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change03").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change04").css("border-bottom","2px solid #666");
                $("#Union2").stop().css('display', "block");
                $("#Union1").stop().css('display', "none");
                $("#Union4").stop().css('display', "none");
            });

            $("#Union #mtab_menual .change04").click(function() {
                $("#Union  #mtab_menual .change04").css("border-bottom","2px solid #9bacd4");
                $("#Union  #mtab_menual .change02").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change03").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change01").css("border-bottom","2px solid #666");
                $("#Union4").css('display', "block");
                $("#Union1").css('display', "none");
                $("#Union2").css('display', "none");
            });
            $("#Union #mtab_menual .change03").click(function() {
                $("#Union  #mtab_menual .change03").css("border-bottom","2px solid #9bacd4");
                $("#Union  #mtab_menual .change04").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change02").css("border-bottom","2px solid #666");
                $("#Union  #mtab_menual .change01").css("border-bottom","2px solid #666");
            });
        });
    }


    var Mtab = function () {
        /**
         * 頁籤切換
         * @return {null}
         */
        $.fn.mtab2 = function(posType) {
            var area = this, bgTop = '', bgBottom = '';
            var posType = (typeof posType !== 'undefined'? posType: 'l');
            switch(posType) {
                case 'c':
                    bgTop = 'top center';
                    bgBottom = 'bottom center';
                    break;
                case 'r':
                    bgTop = 'top right';
                    bgBottom = 'bottom right';
                    break;
                default:
                    bgTop = 'top left';
                    bgBottom = 'bottom left'
            }
            $.each(area.find('li[id^=#]'), function(i) {
                if(i != 0) {
                    area.find(this.id)[0].style.display = 'none';
                }
            });
            area.find('li[id^=#]').click(function() {
                var self = this;
                $.each(area.find('li[id^=#]'), function(i) {
                    if(self.id != this.id) {
                        area.find(this.id)[0].style.display = 'none';
                        $(this)[0].style.backgroundPosition = bgTop;
                        $(this).removeClass('mtab');
                    } else {
                        area.find(this.id)[0].style.display = 'block';
                        $(this)[0].style.backgroundPosition = bgBottom;
                        $(this).addClass('mtab');
                    }
                });
            });
        };


        //优惠活动 TAB切换
        $('.MemberExclusive .memExclusive a').click(function () {
            $(this).parent().parent().next().toggleClass('dn');
        });

        $(".mtab-menual a").each(function(){
            $(this).click(function () {
                $(this).addClass("mtab").siblings().removeClass("mtab");
            })
        });

    }

    var TOPCHAJIAN = function () {
        //滚动置顶插件
        if ('undefined' != typeof($)) {
            $(function () {
                var btnNum = $('#ele-float-top').children().length,
                    wrap = $('#ele-float-top-wrap'),
                    wrapHeight = (btnNum - 1) * (40 + 2),
                    speedSet = 300,
                    thebox = $('.ele-float-box-wrap');

                wrap.height(wrapHeight);
                if (wrap.height() == wrapHeight) {
                    $('#ele-float-top').show();
                }

                $('.ele-float-top-code').hover(function () {
                    $(this).children(thebox).stop(true, true).fadeIn(speedSet);
                }, function () {
                    $(this).children(thebox).stop(true, true).fadeOut(speedSet);
                });

                $("#ele-float-top-up").click(function () {
                    $('html,body').animate({scrollTop: 0}, 1000, 'easeOutExpo');
                });
                $(window).scroll(function () {
                    if (navigator.userAgent.indexOf("MSIE") != -1) {
                        var fadeSec = 200;
                    } else {
                        var fadeSec = 300;
                    }
                    if ($(this).scrollTop() > 300) {
                        $('#ele-float-top-up').fadeIn(fadeSec);
                    } else {
                        $('#ele-float-top-up').stop().fadeOut(fadeSec);
                    }
                });
            });
        }
    }

    //常见问题
    var handleAskFun = function () {
        $('#ask a').click(function () {
            $(this).parent().next().toggleClass('dn');
        });
    }

    //闪动
    var shangDong = function () {
        setInterval(function(){
            $('.into_game').toggleClass("into_game_hover");
        },800)
    }

    var wenziShangDong = function () {
        /**
         * 文字閃爍
         * @param id   jquery selecor
         * @param arr  ['#FFFFFF','#FF0000']
         * @param s    milliseconds
         */
        function toggleColor(id, arr, s) {
            var self = this;
            self._i = 0;
            self._timer = null;

            self.run = function() {
                if(arr[self._i]) {
                    $(id).css('color', arr[self._i]);
                }
                self._i == 0 ? self._i++ : self._i = 0;
                self._timer = setTimeout(function() {
                    self.run(id, arr, s);
                }, s);
            }
            self.run();
        }


        //讀取文案連結  data-color
        $(function() {
            $('a.js-article-color').each(function() {
                var color_arr = $(this).data('color');

                if ('undefined' ==  typeof color_arr) return;

                color_arr = color_arr.split('|');

                // 確認顏色數量  2=>閃爍   1=>單一色  0=>跳過
                if(color_arr.length == 2) {
                    new toggleColor(this, [color_arr[0], color_arr[1]], 500 );
                }else if(color_arr.length == 1 && color_arr[0] != ''){
                    $(this).css('color', color_arr[0]);
                }
            });
        });
    }


    /*浮动框插件*/
    var FloatPlug_ins = function () {
        (function (e) {
            e.fn.fix = function (t) {
                var n = {"float": "left", minStatue: !1, skin: "gray", durationTime: 1e3}, t = e.extend(n, t);
                this.each(function () {
                    var n = e(this), r = n.find(".close_btn"), i = n.find(".show_btn"), s = n.find(".side_content"), o = n.find(".side_list").width(), u = n.find(".side_list"), a = n.offset().top;
                    n.css(t.float, 0), t.minStatue && (e(".show_btn").css("float", t.float), s.css("width", 0), i.css("width", 25)), t.skin && n.addClass("side_" + t.skin), e(window).bind("scroll", function () {
                        var r = a + e(window).scrollTop() + "px";
                        n.animate({top: r}, {duration: t.durationTime, queue: !1})
                    }), r.bind("click", function () {
                        s.animate({width: "0"}, "fast"), i.stop(!0, !0).delay(300).animate({width: "33px"}, "fast").css("float", "right")
                    }), i.click(function () {
                        e(this).animate({width: "0px"}, "fast"), n.width(o), s.stop(!0, !0).delay(200).animate({width: "167px"}, "fast")
                    })
                })
            }
        })(jQuery)

        $(function () {
            $(".fixAdR").fix({float: 'right:10px', minStatue: false, skin: 'green', durationTime: 800})
            $(".fixAdL").fix({float: 'left:10px', minStatue: false, skin: 'green', durationTime: 800})
        });

        var isFirefox =navigator.userAgent.indexOf("Firefox");
        var isIE = navigator.userAgent.indexOf("MSIE");
        var isSarifi = navigator.userAgent.indexOf("Safari");
        var windowHeight = $(window).height();
        var topNum = 0;
        if ($(window).width() <= 1366) {
            if(isFirefox > 0) {
                topNum = windowHeight - 200;
            }
            else if (isIE > 0)
            {
                topNum = windowHeight - 330;
            }
            else if (isSarifi > 0) {
                topNum = windowHeight - 30;
            }
            else
            {
                topNum = windowHeight - 30;
            }
        }
        else
        {
            topNum = windowHeight - 300;
        }
        var topStr = topNum + 'px';
        $(".advBottom").css({top:topStr});

    }

    /*关闭浮动框*/
    var HandleCloseAd = function () {
        if ($('.fixAdR').length != 0) {
            $('.fixAdR .x').click(function () {
                $('.fixAdR').hide();
            });
        }
        if ($('.fixAdL').length != 0) {
            $('.fixAdL .x').click(function () {
                $('.fixAdL').hide();
            });
        }
    }

    var hoverShow = function () {
        $(".ye,.zhyepop").hover(
            function () {
                $(".zhyepop").css("display","block");
            },
            function () {
                $(".zhyepop").css("display","none");
            }
        );
    }

    return {
        init: function () {
            handleFixInputPlaceholderForIE();
            GUNDONGCHAJIAN();
            TOPCHAJIAN();
            Head();
            Slot();
            Transfer();
            // Time2();
            Time1();
            Union();
            Mtab();
            TOPCHAJIAN();
            handleAskFun();
            shangDong();
            wenziShangDong();
            FloatPlug_ins();
            HandleCloseAd();
            hoverShow();
        }
    };
}();












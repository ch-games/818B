// JavaScript Document
$(function(){
    var $Num1 = $("#Num-1"),
        $Num2 = $("#Num-2"),
        $Num3 = $("#Num-3"),
        $Num4 = $("#Num-4");
    var $Num1Rel = [(new Date().getTime() - new Date("2015/1/1 00:00").getTime()) / 1000] / 60;
        $Num1Rel = $Num1Rel / 60;
    var $Num1Rel2 = $Num1Rel > 24 ? Math.round($Num1Rel / 24) : 1,
        $Num1Rel2 = Math.round($Num1Rel2 * (Math.random()*50+1) + 2000),
        $Num1Rel3 = Math.round($Num1Rel2 * (Math.random()*50+10) + 80000),
        $Num1Rel4 = Math.round($Num1Rel2 * (Math.random()*2+1) + 3000),
        $Num1Rel5 = Math.round($Num1Rel2 * (Math.random()*2000+500) + 5000000);
    $Num1.attr("rel",$Num1Rel2);
    $Num2.attr("rel",$Num1Rel3);
    $Num3.attr("rel",$Num1Rel4);
    $Num4.attr("rel",$Num1Rel5);

    var ActivityCountsPercentage = '60' + "%";
    var BetCountsPercentage = '40' + "%";
    var FinancialPercentage = '80' + "%";
    var DividendAmountPercentage = '70' + "%";
    var EEividendAmountPercentage = '40' + "%";
    var FFividendAmountPercentage = '60' + "%";
    HoursInfo(ActivityCountsPercentage, BetCountsPercentage, FinancialPercentage, DividendAmountPercentage, EEividendAmountPercentage, FFividendAmountPercentage, '天', '时', '分', '秒');


    function HoursInfo(ActivityCountsPercentage, BetCountsPercentage, FinancialPercentage, DividendAmountPercentage, EEividendAmountPercentage, FFividendAmountPercentage, TitleResourceDay, TitleResourceHour, TitleResourceMinute, TitleResourceSecond) {
        $("#bd1").animate({
            width: ActivityCountsPercentage
        }, 3500);

        $("#bd2").animate({
            width: BetCountsPercentage
        }, 3500);

        $("#bd3").animate({
            width: FinancialPercentage
        }, 3500);

        $("#bd4").animate({
            width: DividendAmountPercentage
        }, 3500);

        $("#bd5").animate({
            width: EEividendAmountPercentage
        }, 3500);

        $("#bd6").animate({
            width: FFividendAmountPercentage
        }, 3500);


        $('span[isnum=true]').attr('now', '0');
        $('span[isnum=true]').each(function (index, element) {
            var thisrel = parseInt($(this).attr('rel'));
            var thistime = parseInt(thisrel / 300);
            if ($(this).attr('isdate') == 'true') {
                thistime = thisrel / 200;
            }
            $(this).attr('time', thistime + 1);
        }).each(function (index, element) {
            time($(this), $(this).attr("type"), TitleResourceDay, TitleResourceHour, TitleResourceMinute, TitleResourceSecond);
        });
    }


    function time(obj, type, TitleResourceDay, TitleResourceHour, TitleResourceMinute, TitleResourceSecond) {
        var thisrel = parseFloat(obj.attr('rel'));
        var thisnow = parseInt(obj.attr('now'));
        thisnow += parseInt(obj.attr('time'));
        if (thisnow >= thisrel) {
            thisnow = thisrel;
        } else {
            setTimeout(function (obj) {
                return function () {
                    time(obj, type, TitleResourceDay, TitleResourceHour, TitleResourceMinute, TitleResourceSecond);
                }
            }(obj), 50);
        }
        obj.attr('now', thisnow);
        if (obj.attr('isdate') == 'true') {
            var tstr = '';
            if (thisnow >= 60 * 60 * 24) {
                var day = parseInt(thisnow / (60 * 60 * 24));
                tstr += day + TitleResourceDay;
                var hour = thisnow % (60 * 60 * 24);
                var mint = parseInt(hour / (60 * 60));
                if (mint > 0)
                    tstr += mint + TitleResourceHour;
                var sec = hour % (60 * 60);
                if (sec > 0)
                    tstr += sec + TitleResourceMinute;
                var h = parseInt(sec / (60));
                if (h > 0)
                    tstr += h + TitleResourceSecond;
            }
            else if (thisnow >= 60 * 60) {
                var mint = parseInt(thisnow / (60 * 60));
                if (mint > 0)
                    tstr += mint + TitleResourceHour;
                var sec = hour % (60 * 60);
                if (sec > 0)
                    tstr += sec + TitleResourceMinute;
                var h = parseInt(sec / (60));
                if (h > 0)
                    tstr += h + TitleResourceSecond;
            }
            else if (thisnow >= 60) {
                var sec = parseInt(thisnow / (60));
                if (sec > 0)
                    tstr += sec + TitleResourceMinute;
                var h = thisnow % (60)
                if (h > 0)
                    tstr += h + TitleResourceSecond;
            } else {
                tstr = thisnow + TitleResourceSecond;
            }
            obj.html(tstr);
        }
        else {
            obj.html(fmoney(thisnow, 2, type));
        }
    }

    function fmoney(s, n, type) {
        if (typeof type != "undefined") {
            if (type == "currency") {
                n = 2;
                s = numberWithCommas(parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "", n);
            } else if (type == "integer") {
                n = 0;
                s = numberWithCommas(parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "", n);
            }
        }
        else {
            n = 0;
            s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
        }
        return s;
    }

    function numberWithCommas(x, fix) {
        var value = parseFloat(x).toFixed(fix);
        if (isNaN(value)) return x;
        var tmp = value.toString().split(".");
        value = tmp[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        if (tmp.length > 1) {
            if (fix > 0) {
                tmp[1] = tmp[1].substring(0, fix);
            }
            value += "." + tmp[1];
        }
        else if (fix > 0) {
            value += ".";
            for (var i = 0; i < fix; i++) {
                value += "0";
            }
        }
        return value;
    }
})
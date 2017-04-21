// IE版本检测
$.isIE = function(ver) {
    var b = document.createElement('b');
    b.innerHTML = '<!--[if IE ' + ver + ']><i></i><![endif]-->';
    return b.getElementsByTagName('i').length === 1;
};

/*------------------------------- loading提示 begin-------------------------------*/
// 打开loading提示
$.showLoading = function () {
    layer.load('处理中…');
};

// 关闭loading提示
$.hideLoading = function() {
    layer.closeAll();
};

// 消息提示框
$.showMsg = function (msg, time, callback) {
    layer.msg(msg, time, -1, callback);
};
/*------------------------------- loading提示 end-------------------------------*/


// 请忽删除
// 设置来源网站
if ($.cookie("Source") == null) {
    $.cookie("Source", document.referrer);
}

/*------------------------------- loading提示 end-------------------------------*/

$.scrollTo = function(objId) {
    var obj = $("#" + objId);
    if (obj.length == 0) return;
    var offsettop = obj.offset();
    window.scrollTo(0, offsettop.top - 100);
};

//--------------------------实用方法----------------------

/********************设置剪切板********************/
$.setClipboard = function (id) {
    var $Id = $("#" + id);
    if (window.clipboardData) {
        window.clipboardData.setData("Text", $Id.val());
        alert("复制成功");
    } else {
        $Id.focus();
        $Id.select();
        alert("你使用的是非IE核心浏览器,请按下Ctrl+C复制到剪切板");
        //$("<div class='comm_dialog_cont'><p class='f12 fblod mt8'>你使用的是非IE核心浏览器,请按下Ctrl+C复制代码到剪切板</p><textarea id='selectersecond' class='mt8' style='width:340px; height:60px; padding:5px; font-size:12px;'>" + val + "</textarea>").dialog({ title: " 温馨提示", modal: true, width: 380, height: 180 });
        //$("#selectersecond").select();
    }
};
/********************编辑URL参数并返回********************/

/*******************************************************/
/**
 * 判断一个对象是否是DOM
 * @param o
 * @returns
 */
Object.isElement = function (o) {
    return (typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
      o && typeof o === "object" && o.nodeType === 1 && typeof o.nodeName === "string"
  );
};
/**
 * 字符前三位正常显示，后面都加*号
 */
String.prototype.maskText = function () {
    if (this.length > 4) {
        var a = this.substr(0, 3);
        var b = this.substr(3);
        var c = '';
        for (var i = 0; i < b.length; i++) {
            c += '*';
        }
        return a + c;
    }
    else if (this.length == 2) {
        return this.substr(0, 1) + '*';
    }
    else if (this.length == 3) {
        return this.substr(0, 2) + '*';
    }
    else if (this.length == 4) {
        return this.substr(0, 2) + '**';
    }
    else if(this.length == 1){
        return '*';
    }
    else {
        return '';
    }
};
/**
 * 第一个参数为格式字符串，其它参数为参数   格式化字符串{0} 占位符
 */
String.format = function () {
    if (arguments.length == 0)
        return null;
    var str = arguments[0];
    for (var i = 1; i < arguments.length; i++) {
        var re = new RegExp('\\{' + (i - 1) + '\\}', 'gm');
        str = str.replace(re, arguments[i]);
    }
    return str;
};
/**
 * 字符串格式化 
 */
String.prototype.format = function () {
    var args = arguments;
    return this.replace(/\{(\d+)\}/g,
        function (m, i) {
            return args[i];
        });
};
/**
 * 去除字符串首尾空格
 * @returns
 */
String.prototype.trim = function () {
    return this.replace(/(^\s*)|(\s*$)/g, "");
};
/**
 * 去除字符串首空格
 * @returns
 */
String.prototype.ltrim = function (c) {
    if (c == null || c == "") {
        c = "\\s";
    }
    var regex = new RegExp("(^" + c + "*)");
    return this.replace(regex, "");
};
/**
 * 去除字符串尾空格
 * @returns
 */
String.prototype.rtrim = function (c) {
    if (c == null || c == "") {
        c = "\\s";
    }
    var regex = new RegExp("(" + c + "*$)");
    return this.replace(regex, "");
};
/**
 * 判断字串前缀是否是指定字符
 */
String.prototype.startsWith = function (prefix) {
    if (prefix == null || prefix.length == 0 || this.length < prefix.length)
        return false;
    return this.substr(0, prefix.length) == prefix;
};
/**
 * 判断字符串后缀是否是指定字符
 * @param suffix
 */
String.prototype.endsWith = function (suffix) {
    if (suffix == null || suffix.length == 0 || this.length < suffix.length)
        return false;
    return this.substr(this.length - suffix.length) == suffix;
};

/**
 * 判断指定的字串是否是空或者0长度
 * @param str
 */
String.isNullOrEmpty = function (str) {
    return str == null || (typeof str == "string" && str.length == 0);
};
/**
 * 判断指定的字串是否是空或者空字串
 * @param str
 * @returns {Boolean}
 */
String.isNullOrWhiteSpace = function (str) {
    return str == null || (typeof str == "string" && str.trim().length == 0);
};
/**加减秒*/
Date.prototype.addSeconds = function (s) { var lTime = this.getTime(); lTime += s * 1000; var dtDate = new Date(lTime); return dtDate; };
/**加减分钟*/
Date.prototype.addMinutes = function (m) { return this.addSeconds(m * 60); };
/**加减小时**/
Date.prototype.addHours = function (h) { return this.addMinutes(h * 60); };
/**加减天*/
Date.prototype.addDays = function (d) { return this.addHours(d * 24); };
/**加减月*/
Date.prototype.addMonths = function (m) { var dtDate = new Date(this.getTime()); dtDate.setMonth(dtDate.getMonth() + m); return dtDate; };
/**加减年*/
Date.prototype.addYears = function (y) { return this.addMonths(y * 12); };
/**
* Json日期转换成日期对象 //Date(1278903921551+0800)//
*/
Date.fromJson = function (jsondate) {
    jsondate = (jsondate + "").replace("/Date(", "").replace(")/", "");
    var iCharIndex = jsondate.indexOf("+");
    if (iCharIndex < 0)
        iCharIndex = jsondate.indexOf("-");
    if (iCharIndex >= 0)
        jsondate = jsondate.substr(jsondate, iCharIndex);
    var date = new Date(parseInt(jsondate, 10));
  
    var d = new Date(); //创建一个Date对象
    var localOffset = d.getTimezoneOffset(); //获得当地时间偏移的分钟数
    var localServerOffset = localOffset + WEBSERVERTIMEZONE - (ISDAYLIGHTSAVINGTIME ? -60 : 0);//客户端时间与服务器时间偏差
    date = date.addMinutes(localServerOffset);
    
    return date;
};
/**
 * 格式化日期转为日期类型
 * @param formatDate 支持2010-01-02 2010-1-2 2012/01/02 2012/1/2  
 * @returns {Date}
 */
Date.fromFormat = function (formatDate) {
    if (formatDate == null || formatDate == "undefined") {
        return null;
    }
    return new Date(Date.parse(formatDate.replace(/-/g, "/")));
};
/**
 * 得到当前日期 格式化成xxxx-xx-xx
 */
Date.getNowFormatDate = function () {
    var dateNow = new Date();
    var iMonth = dateNow.getMonth() + 1;
    var iDay = dateNow.getDate();
    var strDate = dateNow.getFullYear() + "-" + (iMonth < 10 ? "0" : "") + iMonth + "-" + (iDay < 10 ? "0" : "") + iDay;
    return strDate;
};
/**
 * 日期格式化
 * @param pattern
 * @returns
 */
Date.prototype.format = function (pattern) {
    var year4 = this.getFullYear();
    var year2 = year4.toString().substring(2);
    pattern = pattern.replace(/yyyy/, year4);
    pattern = pattern.replace(/yy/, year2);
    var month = this.getMonth() + 1;
    var month2 = month;
    var monthLength = month.toString().length;
    if (monthLength == 1) {
        month2 = "0" + month2;
    }
    pattern = pattern.replace(/MM/, month2);
    pattern = pattern.replace(/M/, month);
    var dayOfMonth = this.getDate();
    var dayOfMonth2 = dayOfMonth;
    var dayOfMonthLength = dayOfMonth.toString().length;
    if (dayOfMonthLength == 1) {
        dayOfMonth2 = "0" + dayOfMonth;
    }
    pattern = pattern.replace(/dd/, dayOfMonth2);
    pattern = pattern.replace(/d/, dayOfMonth);
    var hours = this.getHours();
    var hours2 = hours;
    var hoursLength = hours.toString().length;
    if (hoursLength == 1) {
        hours2 = "0" + hours;
    }
    pattern = pattern.replace(/HH/, hours2);
    pattern = pattern.replace(/H/, hours);
    var minutes = this.getMinutes();
    var minutes2 = minutes;
    var minutesLength = minutes.toString().length;
    if (minutesLength == 1) {
        minutes2 = "0" + minutes;
    }
    pattern = pattern.replace(/mm/, minutes2);
    pattern = pattern.replace(/m/, minutes);
    var seconds = this.getSeconds();
    var seconds2 = seconds;
    var secondsLength = seconds.toString().length;
    if (secondsLength == 1) {
        seconds2 = "0" + seconds;
    }
    pattern = pattern.replace(/ss/, seconds2);
    pattern = pattern.replace(/s/, seconds);
    return pattern;
};
/**
 * 解决小数进位toFixed在ie中不正常的BUG
 * @param fractionDigits
 * @returns {Number}
 */
Number.prototype.toFixed = function (fractionDigits) {
    //with (Math) {
    //    return round(this * pow(10, fractionDigits)) / pow(10, fractionDigits);
    //}
    var b = 1;
    if (isNaN(this)) return this;
    if (this < 0) b = -1;
    var multiplier = Math.pow(10, fractionDigits);
    return Math.round(Math.abs(this) * multiplier) / multiplier * b;
};
/**
 * 格式化数值为指定格式的字符串
 * @param pattern
 * @returns {String}
 */
Number.prototype.format = function (pattern) {
    //没有格式直接返回
    if (pattern == null || pattern.length == 0) {
        return this + "";
    }
    var decValue = new Number(this);
    var isPercent = pattern.endsWith("%");//是否是百分比
    if (isPercent) {
        decValue *= 100;
        pattern = pattern.substr(0, pattern.length - 1);
    }
    decValue = decValue.toString();
    var intFormat = pattern, decFormat = "", intPart = decValue, decPart = "", dotIndex, len;
    dotIndex = pattern.indexOf(".");
    if (dotIndex >= 0) {
        intFormat = pattern.substr(0, dotIndex);
        decFormat = pattern.substr(dotIndex + 1);
    }
    dotIndex = decValue.indexOf(".");
    if (dotIndex >= 0) {
        intPart = decValue.substr(0, dotIndex);
        decPart = decValue.substr(dotIndex + 1);
    }
    var intReturn = "", decReturn = "";
    //先处理整数部份
    ////var hasZero = intFormat.indexOf("0") >= 0;//是否有0,修改（注释掉，否则0.95会变成.95）
    var hasComma = intFormat.indexOf(",") >= 0;//是否有逗号分割符
    ////var hasMinus = intPart.indexOf("-") >= 0;//整数部分是否有负号，注意不是对pattern内容判断
    ////if (!hasZero && new Number(intPart) == 0 && !hasMinus) {
    ////	intPart = "";
    ////}
    var i;
    if (!hasComma) {
        //没有分割符，直接返回整数部份
        intReturn = intPart;
    } else {
        //有分割符，处理分割符
        len = intPart.length;
        var intLen = 0;
        for (i = len - 1; i >= 0; i--) {
            var ch = intPart.substr(i, 1); // 增加对负号的处理
            if (intLen > 0 && (intLen % 3) == 0 && ch != '-') {
                intReturn = "," + intReturn;
            }
            intLen++;
            intReturn = ch + intReturn;
        }
    }
    //再处理小数部份
    len = decFormat.length;
    for (i = 0; i < len; i++) {
        var charFormat = decFormat.substr(i, 1);
        var charNum = decPart.length > i ? decPart.substr(i, 1) : "0";
        if (charFormat == "0") {
            decReturn += charNum;
        } else {
            decReturn += (charNum == "0") ? "#" : charNum;
        }
    }
    decReturn = decReturn.rtrim("#");//去除#号
    decReturn = decReturn.replace(new RegExp("#"), "0");
    var strReturn = intReturn;
    if (decReturn.length > 0) {
        strReturn += "." + decReturn;
    }
    if (isPercent) {
        strReturn += "%";
    }
    return strReturn;
};
/**
 * Html编码
 * @param input
 * @returns
 */
$.HTMLEncode = function (input) {
    var objDiv = $("<div></div>");
    objDiv.text(input);
    var output = objDiv.html();
    objDiv.remove();
    return output;
};
/**
 * Html解码
 * @param input
 * @returns
 */
$.HTMLDecode = function (input) {
    var objDiv = $("<div></div>");
    objDiv.html(input);
    var output = objDiv.text();
    objDiv.remove();
    return output;
};
/*************************************************************************/
function formatMoney(s, type) {
    if (/[^0-9\.]/.test(s)) return "0";
    if (s == null || s == "") return "0";
    s = s.toString().replace(/^(\d*)$/, "$1.");
    s = (s + "00").replace(/(\d*\.\d\d)\d*/, "$1");
    s = s.replace(".", ",");
    var re = /(\d)(\d{3},)/;
    while (re.test(s))
        s = s.replace(re, "$1,$2");
    s = s.replace(/,(\d\d)$/, ".$1");
    if (type == 0) {// 不带小数位(默认是有小数位)
        var a = s.split(".");
        if (a[1] == "00") {
            s = a[0];
        }
    }
    return s;
}

String.prototype.formatMoney = function () {
    if (/[^0-9\.]/.test(this)) return "0";
    if (this == "") return "0";
    var s = this.toString().replace(/^(\d*)$/, "$1.");
    s = (s + "00").replace(/(\d*\.\d\d)\d*/, "$1");
    s = s.replace(".", ",");
    var re = /(\d)(\d{3},)/;
    while (re.test(s))
        s = s.replace(re, "$1,$2");
    s = s.replace(/,(\d\d)$/, ".$1");
    return s;
};

/***获取URL参数****************************/
$.getQueryString = function (url, name) {
    var result = url.match(new RegExp("[\?\&]" + name + "=([^\&#]+)", "i"));
    if (result == null || result.length < 1) {
        return "";
    }
    var strValue = result[1];
    strValue = decodeURIComponent(strValue);
    return strValue;
};

/*------------------------------- 适应jquery 1.9以上版本的toggle方法-------------------------------*/
$.fn.toggleClick = function () {
    var functions = arguments;

    return this.click(function () {

        var iteration = $(this).data('iteration') || 0;
        functions[iteration].apply(this, arguments);
        iteration = (iteration + 1) % functions.length;
        $(this).data('iteration', iteration);
    });
};
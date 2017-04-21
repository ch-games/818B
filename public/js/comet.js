$(function () {
    // 获取的近两天内的全站公告
    doGetTwoDaysBroadcast();

    // 初始化推送公告
    doInitComet();

    // 推送公告
    function doInitComet() {
        try {
            // 初始化推送方法
            var connection = $.hubConnection(_cometUrl + 'signalr', { useDefaultPath: false });
            var cometHubProxyNotice = connection.createHubProxy('noticeHub');
            var cometHubProxyUser = connection.createHubProxy('frontUserHub');
            //var cometHubProxyLucky = connection.createHubProxy('luckyHub');
            connection.qs = { 'ClientToken': 'EB9279982226A42AFDF2860DBDC29B45', 'ClientUN': $.cookie('CUN') };

            //cometHubProxy.on("exceptionHandler", function (errorMessage) {
            //    alert(errorMessage);
            //});

            // 公告
            cometHubProxyNotice.on("addNoticeToPage", function (noticeJson) {
                // 显示公告
                var id = noticeJson.Id;
                var title = noticeJson.Title;
                var content = noticeJson.Content;
                // 展示
                createNotice(id, title, content);
            });

            //// 抽奖
            //cometHubProxyLucky.on("addLuckyToPage", function (luckyJson) {
            //    // 显示公告
            //    var luckyNo = luckyJson.LuckyNo;
            //    var title = '幸运抽奖';
            //    var content = '您已获得3D彩票抽奖机会，大奖在向您招手';

            //    var luckyCookieKey = '_LUCKYNO';
            //    var existLuckyNo = $.cookie(luckyCookieKey);
            //    var noes = [];
            //    if (existLuckyNo != undefined && existLuckyNo != '')
            //    {
            //        noes = existLuckyNo.split(',');
            //    }
                
            //    if (noes.length == 0 || $.inArray(luckyNo.toString(), noes) == -1) {
            //        // 展示
            //        createLucky(luckyNo, title, content);
            //        noes.push(luckyNo);
            //        // 同一个抽奖不提示
            //        $.cookie(luckyCookieKey, noes.join(','));
            //    }
            //});

            // 这里是为了触发OnConnected
            cometHubProxyUser.on('showOnlineUsers', function () { });
            // 强制退出提示
            cometHubProxyUser.on('kickOut', function (msg) {
                $.cookie('CUN', null);
                alert(msg);
                location.href = '/';
            });

            // Start the connection.{ transport: 'longPolling' }
            //.fail(function (error) {
            //alert(error.message);
            //})
            connection.start();
        }
        catch (ex) {
        }
    };

    // news/gettwodaysbroadcast.html
    function doGetTwoDaysBroadcast() {
        $.post('/news/gettwodaysbroadcast.html', {pageSize:2}, function (data) {
            if (data.success != undefined && data.success == false) {
                return;
            }
            var cookieKey = 'BCN2';

            // 获取cookie，判断哪条记录不需要显示
            var existId = $.cookie(cookieKey);
            var ids = [];
            if (existId != undefined && existId != '') {
                ids = existId.split(',');
            }

            $.each(data, function (index, item) {
                var id = item.Id;
                if (ids.length > 0 && $.inArray(id.toString(), ids) > -1) {
                    // 如果已存在，则不显示
                    return;
                }
                var title = item.Title;
                var content = item.Content;
                // 展示
                createNotice(id, title, content);

                ids.push(id);
            });

            // 写入cookie，下次打开网站的时候，同一个ID号不显示
            if (ids.length > 0) {
                $.cookie(cookieKey, ids.join(','), { expires: 3 });
            }
        });
    };
});

function createNotice(id, title, content) {
    var html = [];
    html.push('<span style="line-height:20px;font-weight:bold;color:black;">', title, '</span>');
    html.push('<hr style="border: none;border-top: 1px solid #ddd;"/>');
    html.push(content);
    //html.push('&nbsp;<a style="color:blue;line-height:20px;" href="/notices.html?id=', id, '"  title="网站公告">查看详情</a>');
    // 右下角展示，多个则往上层叠
    $.sticky(html.join(''), { autoclose: false, position: 'bottom-right' });
};
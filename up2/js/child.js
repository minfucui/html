/**
 * Created by Yuri2 on 2017/7/31.
 */
//此处代码适合在子页面使用
window.Win10_parent = parent.Win10; //获取父级Win10对象的句柄
window.Win10_child = {
    _ajax: function(aurl, apar, aasync) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                type: "post",
                url: aurl,
                data: JSON.stringify(apar),
                contentType: "application/json",
                dataType: "json",
                success: function(d) {
                    if (d.code == '0') {
                        resolve(d.data);
                    } else {
                        reject(new Error(d.message));
                    }
                },
                error: function(e) {
                    Win10_child.close()
                    Win10_child.toLogin(e)
                }
            });
        });
    },
    close: function() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        Win10_parent._closeWin(index);
    },
    newMsg: function(title, content, handle_click) {
        Win10_parent.newMsg(title, content, handle_click)
    },
    openUrl: function(url, title, max) {
        var click_lock_name = Math.random();
        Win10_parent._iframe_click_lock_children[click_lock_name] = true;
        var index = Win10_parent.openUrl(url, title, max);
        setTimeout(function() {
            delete Win10_parent._iframe_click_lock_children[click_lock_name];
        }, 1000);
        return index;
    },
    toLogin: function(e) {
        Win10_parent.toLogin(e)
    },
    toReload: function() {
        Win10_parent.toReload();
    },
    getIndex: function() {
        Win10_parent.getIndex();
    },
    deletePtoject: function(params) {
        Win10_parent.deletePtoject(params);
    },
    childLayer: function(zn, en) {
        Win10_parent.childLayer(zn, en);
    },
    open_new_windows: function(par, bool) {
        Win10_parent._open_new_windows(par, bool);
    },
    timestampToTime: function(timestamp) {
        function timeStr(number) {
            return number < 10 ? '0' + number : number;
        }

        var date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
        var Y = date.getFullYear() + '-';
        var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
        var D = timeStr(date.getDate()) + ' ';
        var h = timeStr(date.getHours()) + ':';
        var m = timeStr(date.getMinutes()) + ':';
        var s = timeStr(date.getSeconds());
        return Y + M + D + h + m + s;
    },
    GetQueryString: function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    },
        //新增
    DateDifference :function (usedTime) {
        var days = Math.floor(usedTime / (24 * 3600 * 1000));
        var leave1 = usedTime % (24 * 3600 * 1000);  
        var hours = Math.floor(leave1 / (3600 * 1000));
        var leave2 = leave1 % (3600 * 1000);        
        var minutes = Math.floor(leave2 / (60 * 1000));
        var time = days + "天" + hours + "时" + minutes + "分";
        return time;
   }
};

/*
 * Copyright 2020 Skycloud Software
 *
 * by William Lu
 */

function vncurlConvert(url)
{
    var ret;
    if (!url.includes('php/vnc.php'))
        return url;
    $.ajax({
        type: "post",
        url: url,
        data: JSON.stringify({}),
        contentType: "application/json",
        dataType: "json",
        async: false,
        success: function(d) {
            if (d.code == '0') {
                ret = d.data;
            } else {
                ret = 'error:' + d.message;
            }
        },
        error: function(e) {
            ret = 'error: calling php/vnc.php';
        }
    });
    return ret;
}

function checkBalance()
{
    var ret;
    $.ajax({
        type: "post",
        url: 'php/checkbalance.php',
        data: JSON.stringify({}),
        contentType: "application/json",
        dataType: "json",
        async: false,
        success: function(d) {
            if (d.code == '0')
                ret = true;
            else
                ret = false;
        },
        error: function(e) {
            ret = false;
        }
    });
    return ret;
}

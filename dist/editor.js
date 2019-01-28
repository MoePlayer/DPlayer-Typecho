$(function () {
    if ($('#wmd-button-row').length > 0) {
        $('#wmd-button-row').append('<li class="wmd-spacer wmd-spacer1" id="wmd-spacer5"></li><li class="wmd-button" id="wmd-meting-button" style="" title="插入音乐"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABGUlEQVQ4T6XTvyuFURgH8M9lkTKYlMGiRDKIxSQDkcFgYVAmi8WPwY+Uxa8FhWQmWdgMiAxmf4BYpFAGSRkY6K1z6tJ1vTdnfc/zOU/P830z/nkyoX4GIyjHHKrQjyXUoh3raEQT9nGDjQQowjk6cYcBnOIJHbjCY4DecYtK7KIrAUqwiNHweh16sRa+DWEbD5jAIS5QgekIJB0cB3kwgNXowTLq0YpNNKMB92iLwALGCpznSnYHP4EyvP4B5gX6wlaGcfkL9Cewh0/sYDIMMdtKBcSCN4xjK0tIDXyE6c/ipVAg2Xmynescc/jWQQxSvNeCUpzl2cQqpmKUj0JsC4nCSRL/+DMl66rBcwqhGN04wHwEUtTlvvIFs5ZDZeiythMAAAAASUVORK5CYII="/></li>');
    }

    $(document).on('click', '#wmd-meting-button', function () {
        $('body').append(
            '<div id="DPlayer-Panel">' +
            '<div class="wmd-prompt-background" style="position: absolute; top: 0px; z-index: 1000; opacity: 0.5; height: 875px; left: 0px; width: 100%;"></div>' +
            '<div class="wmd-prompt-dialog">' +
            '<div>' +
            '<p><b>插入视频</b></p>' +
            '<p>在下方输入参数</p>' +
            '<p><input type="text" id="DP-url" value="" placeholder="链接"></input></p>' +
            '<p><input type="text" id="DP-pic" value="" placeholder="封面图"></input></p>' +
            '<p><input type="text" id="DP-addition" value="" placeholder="额外弹幕源"></input></p>' +
            '<p><input type="checkbox" id="DP-danmu" checked>开启弹幕</input></p>' +
            '<p><input type="checkbox" id="DP-autoplay">自动播放</input></p>' +
            '</div>' +
            '<form>' +
            '<button type="button" class="btn btn-s primary" id="ok">确定</button>' +
            '<button type="button" class="btn btn-s" id="cancel">取消</button>' +
            '</form>' +
            '</div>' +
            '</div>');
    });
    //cancel
    $(document).on('click', '#cancel', function () {
        $('#DPlayer-Panel').remove();
        $('textarea').focus();
    });
    //ok
    $(document).on('click', '#ok', function () {
        var DP_url = document.getElementById('DP-url').value,
            DP_pic = document.getElementById('DP-pic').value,
            DP_danmu = document.getElementById('DP-danmu').checked ? true : false,
            DP_autoplay = document.getElementById('DP-autoplay').checked ? true : false,
            DP_addition = document.getElementById('DP-addition').value;
        var tag = '[dplayer url="' + DP_url + '" pic="' + DP_pic + '" ';
        if (!DP_danmu) tag += 'danmu="' + DP_danmu + '" ';
        if (DP_autoplay) tag += 'autoplay="' + DP_autoplay + '" ';
        if (DP_addition) tag += 'addition="' + DP_addition + '" ';
        tag += '/]\n';
        
        myField = document.getElementById('text');

        if (document.selection) {
            myField.focus();
            sel = document.selection.createRange();
            sel.text = tag;
            myField.focus();
        }
        else if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            var cursorPos = startPos;
            myField.value = myField.value.substring(0, startPos)
                + tag
                + myField.value.substring(endPos, myField.value.length);
            cursorPos += tag.length;
            myField.focus();
            myField.selectionStart = cursorPos;
            myField.selectionEnd = cursorPos;
        }
        else {
            myField.value += tag;
            myField.focus();
        }

        $('#DPlayer-Panel').remove();
    })
});
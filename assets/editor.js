$(function () {
    if ($('#wmd-button-row').length > 0) {
        $('#wmd-button-row').append('<li class="wmd-button" id="wmd-dplayer-button" style="" title="插入视频"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABGUlEQVQ4T6XTvyuFURgH8M9lkTKYlMGiRDKIxSQDkcFgYVAmi8WPwY+Uxa8FhWQmWdgMiAxmf4BYpFAGSRkY6K1z6tJ1vTdnfc/zOU/P830z/nkyoX4GIyjHHKrQjyXUoh3raEQT9nGDjQQowjk6cYcBnOIJHbjCY4DecYtK7KIrAUqwiNHweh16sRa+DWEbD5jAIS5QgekIJB0cB3kwgNXowTLq0YpNNKMB92iLwALGCpznSnYHP4EyvP4B5gX6wlaGcfkL9Cewh0/sYDIMMdtKBcSCN4xjK0tIDXyE6c/ipVAg2Xmynescc/jWQQxSvNeCUpzl2cQqpmKUj0JsC4nCSRL/+DMl66rBcwqhGN04wHwEUtTlvvIFs5ZDZeiythMAAAAASUVORK5CYII="/></li>');
    }

    $(document).on('click', '#wmd-dplayer-button', function () {
        $('body').append(
            '<div id="DPlayer-Panel">' +
            '<div class="wmd-prompt-background" style="position: absolute; top: 0; z-index: 1000; opacity: 0.5; height: 875px; left: 0; width: 100%;"></div>' +
            '<div class="wmd-prompt-dialog">' +
            '<div>' +
            '<p><b>插入视频</b></p>' +
            '<p>在下方输入参数</p>' +
            '<p><input type="text" id="DP-url" value="" placeholder="链接"/></p>' +
            '<p><input type="text" id="DP-pic" value="" placeholder="封面图"/></p>' +
            '<p><input type="text" id="DP-addition" value="" placeholder="额外弹幕源"/></p>' +
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
        var url = document.getElementById('DP-url').value,
            pic = document.getElementById('DP-pic').value,
            danmu = !!document.getElementById('DP-danmu').checked,
            autoplay = !!document.getElementById('DP-autoplay').checked;
        var tag = '[dplayer url="' + url + '" pic="' + pic + '" ';
        if (!danmu) tag += 'danmu="' + danmu + '" ';
        if (autoplay) tag += 'autoplay="' + autoplay + '" ';
        tag += '/]\n';
        
        var editor = document.getElementById('text');

        if (document.selection) {
            editor.focus();
            sel = document.selection.createRange();
            sel.text = tag;
            editor.focus();
        }
        else if (editor.selectionStart || editor.selectionStart === '0') {
            var startPos = editor.selectionStart;
            var endPos = editor.selectionEnd;
            var cursorPos = startPos;
            editor.value = editor.value.substring(0, startPos)
                + tag
                + editor.value.substring(endPos, editor.textLength);
            cursorPos += tag.length;
            editor.focus();
            editor.selectionStart = cursorPos;
            editor.selectionEnd = cursorPos;
        }
        else {
            editor.value += tag;
            editor.focus();
        }

        $('#DPlayer-Panel').remove();
    })
});

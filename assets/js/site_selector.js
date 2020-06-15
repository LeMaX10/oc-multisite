$(document).ready(function () {
    // add menu item
    $('.mainmenu-accountmenu ul li.divider').before('<li>' +
        '   <a href="#" data-control="popup" data-handler="onMultisiteLoadModal" data-hotkey="shift+e" href="javascript:;">' + $.oc.lang.get('multisite.menu_label') +'</a> ' +
        '</li>');

    $('html').hotKey({
        hotkey: 'shift+e',
        hotkeyVisible: true,
        callback: function () {
            $('a[data-handler="onLoadMultisiteModal"]').click();
        }
    });
});

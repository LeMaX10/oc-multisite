$(document).ready(function () {
    // add menu item
    $('.mainmenu-accountmenu ul li.divider').before('<li>' +
        '   <a href="#" data-control="popup" data-handler="onLoadMultisiteModal" href="javascript:;">' + $.oc.lang.get('multisite.menu_label') +'</a> ' +
        '</li>');
});

(function ($, l10n, ajaxurl) {
    $(function () {
        $('#exposed-sortables').removeClass('meta-box-sortables');
        var $document = $(document);
        var $Wrap = $('#griddiv');
        var $Grid = $('#album_grid');
        var $Add = $('#add');
        var $Remove = $('#remove');
        var $Dialog = $('#album_dialog');
        var $DialogButton;
        var albumsLoaded = 0;
        var cssSelected = 'ui-selected';
        var cssAjax = 'ui-ajax';

        var buttons = { };
        buttons[l10n.add] = function () {
            $Dialog.children('.' + cssSelected).clone().removeClass(cssSelected).appendTo($Grid);
            $Dialog.dialog("close");
        };

        $Dialog.dialog({
            appendTo: 'body',
            autoOpen: false,
            buttons: buttons,
            closeOnEscape: true,
            closeText: l10n.close,
            height: 600,
            hide: false,
            minHeight: 400,
            minWidth: 563,
            modal: true,
            resizable: true,
            show: false,
            title: l10n.title,
            width: 800,
            open: function () {
                if (albumsLoaded < (Date.now() - 60000)) {
                    $Dialog.addClass(cssAjax).children('.album').remove();
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'text',
                        data: {
                            _load_albums_nonce: $('#_load_albums_nonce').val(),
                            action: 'load_albums'
                        },
                        success: function (albums) {
                            if (albums && albums != '-1') {
                                $Dialog.html(albums);
                                albumsLoaded = Date.now();
                            }
                        },
                        complete: function () {
                            $Dialog.removeClass(cssAjax);
                        }
                    });
                }
            },
            close: function () {
                $Dialog.children().removeClass(cssSelected);
            },
            create: function () {
                $DialogButton = $(this).next().find('button').attr('disabled', true).addClass('button-primary');
            }
        });
        $Add.on('click', function (e) {
            $Dialog.dialog('open');
        });
        $Remove.on('click', function () {
            $Grid.children('.' + cssSelected).remove();
            $Wrap.removeClass('removeable');
        });
        var maybeDelete = function (e) {
            if (e.which == 8 || e.which == 46) {
                $Remove.trigger('click');
                $document.off('keydown', maybeDelete);
                e.preventDefault();
            }
        };
        SelectSort($Grid, {
            filter: 'figure',
            change: function (_, selected) {
                $Wrap.toggleClass('removeable', selected);
                $document[selected ? 'on' : 'off']('keydown', maybeDelete);
            }
        });
        SelectSort($Dialog, {
            sortable: false,
            filter: 'figure',
            change: function (_, selected) {
                $DialogButton.attr('disabled', !selected);
            }
        });
    });
})(jQuery, wp_L10N, ajaxurl);
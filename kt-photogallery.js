(function ($, l10n, ajaxurl, type) {
    $(function () {
        // a bit hacky. prevent the grid metabox from beeing sortable
        $('#exposed-sortables').removeClass('meta-box-sortables');

        var $document = $(document);
        var $Wrap = $('#griddiv');
        var $Add = $('#add');
        var $Grid = $(type == 'photogallery' ? '#album_grid' : '#image_grid');
        var cssAjax = 'ui-ajax';

        // thumbnail chooser
        if (type == 'photoalbum') {
            var $Choose_Thumbnail = $('#choose_thumbnail');
            var $Clear_Thumbnail = $('#clear_thumbnail');
            var $Thumbnail_ID = $('#thumbnail_id');
            var $Thumbnail_Figure = $('#thumbnail_preview');
            var $Thumbnail_IMG = $Thumbnail_Figure.children('img');

            // create a new thumbnail if none is set
            if ($Thumbnail_IMG.length == 0) {
                $Thumbnail_IMG = $('<img alt />');
            }

            // init a WordPress Media Dialog for choosing a thumbnail
            var wp_media_Thumbnail = wp.media({
                title: l10n.thumbnail,
                multiple: false,
                library: {
                    type: 'image'
                },
                button: {
                    text: l10n.use
                }
            });

            // set selected image as thumbnail
            wp_media_Thumbnail.on('select', function () {
                wp_media_Thumbnail.state().get('selection').each(function (attachment) {
                    var sizes = attachment.attributes.sizes;
                    $Thumbnail_ID.val(attachment.id);
                    $Thumbnail_IMG.attr({
                        title: attachment.attributes.title,
                        src: sizes.thumbnail ? sizes.thumbnail.url : sizes.full.url
                    }).appendTo($Thumbnail_Figure);
                });
                wp_media_Thumbnail.close();
            });

            // choose a thumbnail
            $Choose_Thumbnail.on('click', function (e) {
                wp_media_Thumbnail.open();
            });

            // clear chosen thumbnail
            $Clear_Thumbnail.on('click', function () {
                $Thumbnail_IMG.detach();
                $Thumbnail_ID.val('');
            });
        }

        // add dialog
        if (type == 'photogallery') {
            var $Dialog = $('#album_dialog');
            var $DialogButton;
            var timestamp = Date.now() - 6e4;
            var buttons = { };
            var _load_albums_nonce = $('#_load_albums_nonce').val();

            // add selected albums to the grid
            buttons[l10n.add] = function () {
                var $selection = sortselect_Dialog.selection();
                sortselect_Dialog.deselect();
                $selection.clone().appendTo($Grid);
                $Dialog.dialog("close");
            };

            // init a jQuery UI Dialog for album selection
            $Dialog.dialog({
                autoOpen: false,
                buttons: buttons,
                closeText: l10n.close,
                height: 600,
                minHeight: 400,
                minWidth: 563,
                modal: true,
                title: l10n.title,
                width: 800,
                open: function () {
                    // reload albums every 60 seconds
                    if (timestamp < (Date.now() - 6e4)) {
                        $Dialog.addClass(cssAjax).empty();
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            dataType: 'text',
                            data: {
                                _load_albums_nonce: _load_albums_nonce,
                                action: 'load_albums'
                            },
                            success: function (albums) {
                                if (albums && albums != '-1') {
                                    $Dialog.html(albums);
                                    timestamp = Date.now();
                                }
                            },
                            complete: function () {
                                $Dialog.removeClass(cssAjax);
                            }
                        });
                    }
                },
                create: function () {
                    // main dialog button needs to be a primary button and disabled by default
                    $DialogButton = $(this).next().find('button').attr('disabled', true).addClass('button-primary');
                }
            });
        } else {
            // init a WordPress Media Dialog for image selection
            var wp_media_Images = wp.media({
                title: l10n.title,
                multiple: true,
                library: {
                    type: 'image'
                },
                button: {
                    text: l10n.add
                }
            });

            // add selected images to the grid
            wp_media_Images.on('select', function () {
                var url;
                var images = [ ];
                wp_media_Images.state().get('selection').each(function (attachment) {
                    var sizes = attachment.attributes.sizes;
                    url = sizes.thumbnail ? sizes.thumbnail.url : sizes.full.url;
                    images.push('<figure class="image" title="' + attachment.attributes.title + '"><input type="hidden" name="images[]" value="' + attachment.id + '"><span class="kt-thumbnail"><img src="' + url + '" /></span></figure>');
                });
                images.reverse();
                $Grid.append(images.join(''));
                wp_media_Images.close();
            });
        }

        // open a dialog to select items to be added to the grid
        $Add.on('click', function () {
            if (type == 'photogallery') {
                $Dialog.dialog('open');
            } else {
                wp_media_Images.open();
            }
        });

        // remove button
        var $Remove = $('#remove');
        $Remove.on('click', function () {
            sortselect_Grid.selection().remove();
            $Wrap.removeClass('removeable');
        });

        // catch delete and backspace keys
        var maybeDelete = function (e) {
            if (e.which == 8 || e.which == 46) {
                $Remove.trigger('click');
                $document.off('keydown', maybeDelete);
                e.preventDefault();
            }
        };

        // init SelectSort for jQuery UI Dialog
        if (type == 'photogallery') {
            var sortselect_Dialog = SortSelect($Dialog, {
                sortable: false,
                filter: 'figure'
            });
            sortselect_Dialog.on('change', function ($selection) {
                $DialogButton.attr('disabled', $selection.length == 0);
            });
        }

        // init SelectSort for grid
        var sortselect_Grid = SortSelect($Grid, {
            filter: 'figure',
            helper: function ($selection) {
                var $stack = $selection.slice(0, 3).find('img').clone();
                return $('<div class="ui-sort-helper" />').append($stack);
            }
        });
        sortselect_Grid.on('change', function ($selection) {
            $Wrap.toggleClass('removeable', $selection.length > 0);
            $document[$selection.length ? 'on' : 'off']('keydown', maybeDelete);
        });
    });
})(jQuery, kt_Photogallery_l10n, ajaxurl, typenow);
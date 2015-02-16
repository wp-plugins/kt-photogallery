(function ($, l10n) {
    $(function () {
        $('#exposed-sortables').removeClass('meta-box-sortables');
        var $document = $(document);
        var $Wrap = $('#griddiv');
        var $Grid = $('#image_grid');
        var $Add = $('#add');
        var $Remove = $('#remove');
        var $Choose_Thumbnail = $('#choose_thumbnail');
        var $Clear_Thumbnail = $('#clear_thumbnail');
        var $Thumbnail_ID = $('#thumbnail_id');
        var $Thumbnail_Figure = $('#thumbnail_preview');
        var $Thumbnail_IMG = $Thumbnail_Figure.children('img');
        if ($Thumbnail_IMG.length == 0) {
            $Thumbnail_IMG = $('<img alt />');
        }
        var cssSelected = 'ui-selected';
        var cssAjax = 'ui-ajax';

        var wp_media_selectImages = wp.media({
            title: l10n.title,
            multiple: true,
            library: {
                type: 'image'
            },
            button: {
                text: l10n.add
            }
        });
        wp_media_selectImages.on('select', function () {
            var url;
            var images = [ ];
            wp_media_selectImages.state().get('selection').each(function (attachment) {
                url = attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.sizes.full.url;
                images.push('<figure class="image" title="' + attachment.attributes.title + '"><input type="hidden" name="images[]" value="' + attachment.id + '"><span class="kt-thumbnail"><img src="' + url + '" /></span></figure>');
            });
            images.reverse();
            $Grid.append(images.join(''));
            wp_media_selectImages.close();
        });
        $Add.on('click', function (e) {
            wp_media_selectImages.open();
        });

        var wp_media_chooseThumbnail = wp.media({
            title: l10n.thumbnail,
            multiple: false,
            library: {
                type: 'image'
            },
            button: {
                text: l10n.use
            }
        });
        wp_media_chooseThumbnail.on('select', function () {
            wp_media_chooseThumbnail.state().get('selection').each(function (attachment) {
                var url = attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.sizes.full.url;
                $Thumbnail_ID.val(attachment.id);
                $Thumbnail_IMG.attr({
                    title: attachment.attributes.title,
                    src: url
                }).appendTo($Thumbnail_Figure);
            });
            wp_media_chooseThumbnail.close();
        });
        $Choose_Thumbnail.on('click', function (e) {
            wp_media_chooseThumbnail.open();
        });
        $Clear_Thumbnail.on('click', function () {
            $Thumbnail_IMG.remove();
            $Thumbnail_ID.val('');
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
            },
            helper: function($set) {
                var $stack = $set.slice(0, 3).find('img').clone();
                return $('<div class="ui-sort-helper" />').append($stack);
            }
        });
    });
})(jQuery, wp_L10N);
(function ($) {
    $(function () {
        var cssAjax = 'ajax';
        var cssActive = 'active';
        var isAlbum = !!$('#albumManager').length;
        var isGallery = !!$('#galleryManager').length;
        var isLayout = !!$('#layoutTabs').length;
        if (isAlbum || isGallery) {
            var $document = $(document);
            var $Manager = $(isGallery ? '#galleryManager' : '#albumManager');
            var $Grid = $('#ManagerGrid');
            var $AddButton = $('#AddButton');
            var $DeleteButton = $('#DeleteButton');
            var $PublishButton = $('#PublishButton');
            var $DraftButton = $('#DraftButton');
            var $StatusButton = $('#StatusButton');
            var $Title = isAlbum ? $('#album_title') : $('#gallery_title');
            var $Name = isGallery ? $('#gallery_name') : $('#album_name');
            var $Permalink = $('#Permalink');
            var $window = $(window);
            var cssSelected = 'ui-selected';
            var cssDeleting = 'ui-deleting';
            var cssNoPermalink = 'no-permalink';
            var cssNoThumbnail = 'no-thumbnail';
            var cssHidden = 'hidden';

            var isSaving = false;
            var savePending = false;
            var shortcodeFeedback = false;
            var canDelete = true;
            var saveTimeoutID;
            var saveDelay = 1500;
            var deleteDuration = 100;
            var shortcodeFeedbackTime = 2000;
            var oldPermalink;
            var ID = $Manager.data('id');
            var savePreparation, addItem;

            var stopSave = function () {
                if (saveTimeoutID) {
                    clearTimeout(saveTimeoutID);
                }
            };
            var startSave = function (timeout) {
                stopSave();
                if (isSaving) {
                    savePending = true;
                } else {
                    if (timeout) {
                        saveTimeoutID = setTimeout(savePreparation, timeout);
                    } else {
                        savePreparation();
                    }
                }
            };
            var saveData = function (data) {
                isSaving = true;
                $Manager.addClass(cssAjax);
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (data) {
                        if (data && data.shortcode) {
                            setShortcode(data.shortcode);
                        }
                    },
                    complete: function (a) {
                        $Manager.removeClass(cssAjax);
                        isSaving = false;
                        if (savePending) {
                            savePending = false;
                            savePreparation();
                        }
                    }
                });
            };

            var setShortcode = function (s) {
                $Name.val(s).trigger('propertychange');
                $Permalink.toggleClass(cssNoPermalink, !s);
                $StatusButton.toggleClass(cssHidden, !s);
                if (!$PublishButton.hasClass(cssActive) && !$DraftButton.hasClass(cssActive)) {
                    $DraftButton.addClass(cssActive);
                }
                if (shortcodeFeedback) {
                    shortcodeFeedback = false;
                    $('<span class="permalink-feedback ' + (oldPermalink == s ? 'fail' : 'ok') + '"/>').appendTo($Permalink).delay(shortcodeFeedbackTime).fadeOut(1000, function () {
                        $(this).remove();
                    });
                }
            };

            var deleteSelection = function (e) {
                switch (e.type) {
                    case 'mousedown':
                        $DeleteButton.addClass(cssHidden);
                        $Grid.children('.' + cssSelected).addClass(cssDeleting).fadeOut(deleteDuration, function () {
                            $(this).remove();
                            startSave();
                        });
                        break;
                    case 'keydown':
                        if (canDelete && (e.which == 8 || e.which == 46)) {
                            e.type = 'mousedown';
                            deleteSelection(e);
                            return false;
                        }
                        break;
                }
            };

            if (isGallery) {
                var $Dialog = $('#albumDialog');
                var $DialogButton;
                var albumsLoaded = 0;

                var buttons = { };
                buttons[wp_L10N.add] = function () {
                    var data = [ ];
                    var n = 0;
                    $Dialog.children('.' + cssSelected).each(function () {
                        data = $(this).data();
                        $Grid.append('<li data-id="' + data.id + '" data-title="' + data.title + '">' + (data.url ? '<img src="' + data.url + '" />' : '') + '</li>');
                        n++;
                    });
                    if (n) {
                        savePreparation();
                    }
                    $Dialog.dialog("close");
                };

                $Dialog.dialog({
                    appendTo: 'body',
                    autoOpen: false,
                    buttons: buttons,
                    closeOnEscape: false,
                    closeText: wp_L10N.close,
                    height: 600,
                    hide: false,
                    minHeight: 400,
                    minWidth: 563,
                    modal: true,
                    resizable: true,
                    show: false,
                    title: wp_L10N.title,
                    width: 800,
                    open: function () {
                        if (albumsLoaded < (Date.now() - 60000)) {
                            $Dialog.addClass(cssAjax).children('.album').remove();
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'load_albums'
                                },
                                success: function (albums) {
                                    var l = albums.length;
                                    if (l) {
                                        for (var i = 0, album = albums[i]; i < l; album = albums[++i]) {
                                            $Dialog.append($('<div class="album" data-title="' + album.title + '">' + (album.url.length > 0 ? '<img src="' + album.url + '" />' : '') + '</div>').data(album));
                                        }
                                    }
                                    albumsLoaded = Date.now();
                                },
                                complete: function (_, s) {
                                    $Dialog.removeClass(cssAjax);
                                }
                            });
                        }
                    },
                    close: function () {
                        $Dialog.children().removeClass(cssSelected);
                    },
                    create: function () {
                        $DialogButton = $(this).next().find('button').attr('disabled', true).addClass('dashicons-before dashicons-yes');
                    }
                });
                addItem = function (e) {
                    e.preventDefault();
                    $Dialog.dialog('open');
                };
                savePreparation = function () {
                    var albums = [ ];
                    $Grid.children('li[data-id]').each(function () {
                        albums.push($(this).data('id'));
                    });
                    saveData({
                        action: 'save_gallery',
                        id: ID,
                        title: $Title.val(),
                        name: $Name.val(),
                        albums: albums.join(',')
                    });
                };
            }
            if (isAlbum) {
                var $Thumbnail = $('#Thumbnail');
                var $ThumbnailImage = $Thumbnail.children('img');
                var $DeleteThumbnail = $('#DeleteThumbnail');

                var wp_media_selectImages = wp.media({
                    title: wp_L10N.image.title,
                    multiple: true,
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: wp_L10N.image.add
                    }
                });

                wp_media_selectImages.on('select', function () {
                    var url;
                    var images = [ ];
                    wp_media_selectImages.state().get('selection').each(function (attachment) {
                        url = attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.sizes.full.url;
                        images.push('<li data-id="' + attachment.id + '" title="' + (attachment.attributes.title || attachment.attributes.name) + '"><img src="' + url + '" /></li>');
                    });
                    images.reverse();
                    $Grid.append(images.join(''));
                    wp_media_selectImages.close();
                    startSave();
                });

                var wp_media_chooseThumbnail = wp.media({
                    title: wp_L10N.thumbnail.title,
                    multiple: false,
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: wp_L10N.thumbnail.use
                    }
                });

                wp_media_chooseThumbnail.on('select', function () {
                    wp_media_chooseThumbnail.state().get('selection').each(function (attachment) {
                        var url = attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.sizes.full.url;
                        $Thumbnail.data('thumbnail-id', attachment.id);
                        if ($Thumbnail.children('img').length) {
                            $ThumbnailImage.attr('src', url);
                        } else {
                            $ThumbnailImage = $('<img src="' + url + '" />');
                            $Thumbnail.append($ThumbnailImage);
                        }
                        $Thumbnail.removeClass(cssNoThumbnail);
                    });
                    wp_media_chooseThumbnail.close();
                    startSave();
                });

                $Thumbnail.on('click', null, 'thumb', function (e) {
                    e.preventDefault();
                    wp_media_chooseThumbnail.open();
                });

                $DeleteThumbnail.on('click', function () {
                    $Thumbnail.data('thumbnail-id', '');
                    $ThumbnailImage.remove();
                    $Thumbnail.addClass(cssNoThumbnail);
                    startSave();
                    return false;
                });
                $Thumbnail.toggleClass(cssNoThumbnail, !$ThumbnailImage.length);
                addItem = function (e) {
                    e.preventDefault();
                    wp_media_selectImages.open();
                };
                savePreparation = function () {
                    var images = [ ];
                    $Grid.children('li[data-id]').each(function () {
                        images.push($(this).data('id'));
                    });
                    saveData({
                        action: 'save_album',
                        id: ID,
                        title: $Title.val(),
                        name: $Name.val(),
                        thumbnail: $Thumbnail.data('thumbnail-id'),
                        images: images.join(',')
                    });
                };
            }

            SelectSort($Grid, {
                helper: function (_, $set) {
                    return '<div class="ui-sort">' + (wp_L10N.sortable[$set.length == 1 ? '1' : 'n'].replace('%d', $set.length)) + '</div>';
                },
                select: function () {
                    $DeleteButton.removeClass(cssHidden);
                },
                deselect: function () {
                    $DeleteButton.addClass(cssHidden);
                },
                sort: function () {
                    startSave(saveDelay);
                }
            });

            if (isGallery) {
                SelectSort($Dialog, {
                    sortable: false,
                    filter: 'div.album',
                    select: function () {
                        $DialogButton.removeAttr('disabled').addClass('button-primary');
                    },
                    deselect: function () {
                        $DialogButton.attr('disabled', true).removeClass('button-primary');
                    }
                });
            }

            $AddButton.on('click', null, 'add', addItem);

            $window.on('resize', function () {
                $Grid.css('min-height', ($window.height() - 345) + 'px');
            }).trigger('resize');

            $DeleteButton.on('mousedown', deleteSelection);
            $document.on('keydown', deleteSelection);
            $Title.on('change', startSave);

            $Name.on('change', function () {
                shortcodeFeedback = true;
                startSave();
            }).on('focus', function () {
                oldPermalink = $Name.val();
            }).on('keyup', function (e) {
                switch (e.which) {
                    case 13:
                        $Name.trigger('blur');
                        break;
                    case 27:
                        $Name.val(oldPermalink).trigger('propertychange').trigger('blur');
                        break;
                }
            });

            $Title.add($Name).on('focus blur', function (e) {
                canDelete = e.type == 'blur';
            });

            var setStatusButtons = function () {
                if ($PublishButton.hasClass(cssActive)) {
                    $PublishButton.attr('title', wp_L10N.status.published);
                    $DraftButton.attr('title', wp_L10N.status.draft);
                } else {
                    $PublishButton.attr('title', wp_L10N.status.publish);
                    $DraftButton.attr('title', wp_L10N.status.drafted);
                }
            };

            $StatusButton.on('click', 'button', function () {
                $Manager.addClass(cssAjax);
                isSaving = true;
                var $this = $(this);
                var publishing = this.id == 'PublishButton';
                var status = publishing ? 'publish' : 'draft';
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        id: ID,
                        action: isGallery ? 'publish_gallery' : 'publish_album',
                        status: status
                    },
                    success: function (ok) {
                        if (ok == '1') {
                            if (publishing) {
                                $this.addClass(cssActive).next().removeClass(cssActive);
                            } else {
                                $this.addClass(cssActive).prev().removeClass(cssActive);
                            }
                            setStatusButtons();
                        }
                    },
                    complete: function (_, s) {
                        isSaving = false;
                        $Manager.removeClass(cssAjax);
                        $StatusButton.children().trigger('blur');
                    }
                });
            });

            setStatusButtons();
            setShortcode($Name.val());
        } else if (isLayout) {
            var $Tabs = $('#layoutTabs');
            var saveType = function (type) {
                $Tabs.addClass(cssAjax);
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'text',
                    data: {
                        action: 'save_layout_type',
                        type: type
                    },
                    success: function (ok) {
                        
                    },
                    complete: function(){
                        $Tabs.removeClass(cssAjax);
                    }
                });
            };
            $Tabs.on('mousedown', 'span', function () {
                var $tab = $(this);
                $tab.addClass(cssActive).siblings().removeClass(cssActive);
                $($tab.data('content')).addClass(cssActive).siblings().removeClass(cssActive);
                saveType($tab.hasClass('custom') ? 'custom' : 'standart');
            });
        }
    });
})(jQuery);

(function ($) {
    var initialized = 'autosizeInputIninitialized';
    var validTypes = [ 'text', 'password', 'search', 'url', 'tel', 'email', 'number' ];
    var mirrorHTML = '<span style="position:absolute; top:-999px; left:0; white-space:pre"></span>';
    var mapProperties = [ 'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'letterSpacing', 'textTransform', 'wordSpacing', 'textIndent' ];

    $.fn.autosizeInput = function () {
        return this.each(function () {
            if (this.tagName === 'INPUT' && $.inArray(this.type.toLowerCase(), validTypes) > -1) {
                var $this = $(this);
                var space = parseInt($this.data('autosize-input')) || 2;
                if (!$this.data(initialized)) {
                    $this.data(initialized, true);
                    var $mirror = $(mirrorHTML).css($this.css(mapProperties)).appendTo('body');
                    var newValue, oldValue;
                    var update = function () {
                        newValue = $this.val() || '';
                        if (oldValue !== newValue) {
                            $mirror.text(newValue);
                            $this.width($mirror.width() + space);
                            oldValue = newValue;
                        }
                    };
                    $this.on('keydown keyup input propertychange change', update);
                    update();
                }
            }
        });
    };

    $(function () {
        $('input[data-autosize-input]').autosizeInput();
    });
})(jQuery);
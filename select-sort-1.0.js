/*
 * SelectSort v1.0
 *
 * Copyright 2014 Daniel Schneider
 * Released under GPLv2 or later
 * http://www.gnu.org/licenses/gpl-2.0.html
 */
window.SelectSort = (function ($) {
    var pythagoras = function (x, y) {
        return Math.sqrt(Math.pow(x, 2) + Math.pow(y, 2));
    };
    var validate = function (o, min, max) {
        var x = parseInt(o);
        return max ? Math.max(min, Math.min(isNaN(x) ? min : x, max)) : Math.max(min, isNaN(x) ? min : x);
    };
    var S = function (target, options) {
        var $document = $(document);
        var $body = $('body');
        var o = $.extend({ }, S.defaults, options);
        o.distance = validate(o.distance, 0);
        o.offsetX = validate(o.offsetX, 0);
        o.offsetY = validate(o.offsetY, 0);
        $(target).each(function () {
            var scope = this;
            var $target = $(this);
            var $current, $last, $set, $marquee, $helper;
            var last, offset, x, x1, x2, y, y1, y2, w, w1, h, h1, l, cache;
            var deselect = true;
            var select = function (e) {
                if (e.ctrlKey || e.metaKey) {
                    $current.toggleClass(o.selected);
                } else if (e.shiftKey && last) {
                    var index = $current.index();
                    var a = Math.min(index, last);
                    var b = Math.max(index, last) + 1;
                    $target.children(o.filter).removeClass(o.selected).slice(a, b).addClass(o.selected);
                    $current = $last;
                } else {
                    $current.addClass(o.selected).siblings(o.filter).removeClass(o.selected);
                }
                var $selected = $target.children(o.filter).filter('.' + o.selected);
                var some = $selected.length > 0;
                if (deselect) {
                    if (some) {
                        deselect = false;
                        $target.on('mousedown', selectNone);
                    } else {
                        $target.off('mousedown', selectNone);
                    }
                }
                $last = $current;
                last = $current.index();
                if (some) {
                    $target.trigger('select', [ $selected, cache ]);
                } else {
                    $target.trigger('deselect');
                }
            };
            var selectNone = function (e) {
                if (e.target == scope && -!(e.metaKey || e.ctrlKey || e.shiftKey)) {
                    $target.children(o.filter).removeClass(o.selected);
                    deselect = true;
                    $target.off('mousedown', selectNone);
                    $target.trigger('deselect');
                }
            };
            var cacheSelect = function (e) {
                e.preventDefault();
                if (o.distance == 0 || pythagoras(e.pageX - x1, e.pageY - y1) > o.distance) {
                    offset = $target.offset();
                    x1 -= offset.left;
                    y1 -= offset.top;
                    x2 = parseInt($target.css('paddingLeft'));
                    y2 = parseInt($target.css('paddingTop'));
                    cache = $target.children(o.filter).map(function () {
                        var $this = $(this);
                        var position = $this.position();
                        return {
                            $: $this,
                            x: position.left + x2,
                            y: position.top + y2,
                            w: $this.outerWidth(),
                            h: $this.outerHeight(),
                            s: $this.hasClass(o.selected)
                        };
                    }).get();
                    w1 = $target.innerWidth();
                    h1 = $target.innerHeight();
                    l = cache.length;
                    $marquee = $('<div class="' + o.marquee + '"></div>').hide().appendTo($target);
                    $body.addClass(o.selecting);
                    $document.off('mousemove', cacheSelect).off('mouseup', uncacheSelect);
                    $document.on('mousemove', updateSelect);
                    $document.one('mouseup', endSelect).on('keydown', cancelSelect);
                    if (deselect) {
                        deselect = false;
                        $target.on('mousedown', selectNone);
                    }
                    $target.trigger('selectStart', [ cache ]);
                }
            };
            var updateSelect = function (e) {
                e.preventDefault();
                x2 = Math.max(0, Math.min(e.pageX - offset.left, w1));
                y2 = Math.max(0, Math.min(e.pageY - offset.top, h1));
                w = Math.abs(x1 - x2);
                h = Math.abs(y1 - y2);
                x = Math.min(x1, x2);
                y = Math.min(y1, y2);
                $marquee.css({
                    left: x,
                    top: y,
                    width: w + 'px',
                    height: h + 'px',
                    display: 'block'
                });
                var ctrl = e.ctrlKey || e.metaKey || e.shiftKey;
                var $updated = $();
                $.each(cache, function (i, c) {
                    c.$.toggleClass(o.selected, c.s);
                    if (!(c.x + c.w < x || x + w < c.x || c.y + c.h < y || y + h < c.y)) {
                        $updated.push(c.$.toggleClass(o.selected, !(ctrl && c.s))[0]);
                    }
                });
                if ($updated.length) {
                    $target.trigger('selectUpdate', [ $updated, cache ]);
                }
            };
            var cancelSelect = function (e) {
                e.preventDefault();
                if (e.which == 27) {
                    $.each(cache, function () {
                        this.$.toggleClass(o.selected, this.s);
                    });
                    endSelect(false);
                }
            };
            var uncacheSelect = function () {
                $document.off('mousemove', cacheSelect);
            };
            var endSelect = function (e) {
                $marquee.remove();
                $document.off('mousemove', updateSelect).off('mouseup', endSelect).off('keydown', cancelSelect);
                $body.removeClass(o.selecting);
                if (e) {
                    var $selected = $target.children(o.filter).filter('.' + o.selected);
                    if ($selected.length) {
                        $target.trigger('select', [ $selected, cache ]);
                    } else {
                        $target.trigger('deselect');
                    }
                }
                $target.trigger('selectEnd', [ cache ]);
            };
            var cacheSort = function (e) {
                e.preventDefault();
                if (o.distance == 0 || pythagoras(e.pageX - x1, e.pageY - y1) > o.distance) {
                    offset = $target.offset();
                    cache = $target.children(o.filter).map(function () {
                        var $this = $(this);
                        return {
                            $: $this,
                            x: $this.offset().left,
                            w: $this.outerWidth() / 2
                        };
                    }).get();
                    $set.addClass(o.sorting);
                    $body.addClass(o.sorting);
                    $document.off('mousemove', cacheSort).off('mouseup', uncacheSort).off('mouseup', select);
                    $document.one('mouseup', endSort).on('keydown', cancelSort);
                    $target.on('mousemove', o.filter, updateSort);
                    if (o.helper) {
                        $helper = $(typeof o.helper == 'function' ? o.helper.call(scope, e, $set, cache) : o.helper).hide().appendTo($target);
                        $helper.hide();
                        $document.on('mousemove', helperSort);
                    }
                    $target.trigger('sortStart', [ $set, cache ]);
                }
            };
            var uncacheSort = function () {
                $document.off('mousemove', cacheSort);
            };
            var updateSort = function (e) {
                e.preventDefault();
                var $this = $(this);
                if (!$this.hasClass(o.sorting)) {
                    var i = $this.index();
                    $this[(e.pageX - cache[i].x < cache[i].w) ? 'before' : 'after']($set);
                    $target.trigger('sortUpdate', [ $this, $set, cache ]);
                }
            };
            var helperSort = function (e) {
                e.preventDefault();
                $helper.css({
                    left: e.pageX + o.offsetX - offset.left,
                    top: e.pageY + o.offsetY - offset.top,
                    display: 'block'
                });
            };
            var cancelSort = function (e) {
                e.preventDefault();
                if (e.which == 27) {
                    endSort(false);
                }
            };
            var endSort = function (e) {
                $set.removeClass(o.sorting);
                var updated = false;
                $.each(cache, function (i) {
                    if (i !== this.$.index()) {
                        updated = true;
                        return false;
                    }
                });
                $target.off('mousemove', o.filter, updateSort);
                $document.off('keydown', cancelSort);
                $body.removeClass(o.sorting);
                if (o.helper) {
                    $helper.remove();
                    $document.off('mousemove', helperSort);
                }
                if (e) {
                    if (updated) {
                        $target.trigger('sort', [ $set, cache ]);
                    }
                }
                $target.trigger('sortEnd');
            };
            var init = function (e) {
                e.preventDefault();
                var l = $target.children(o.filter).length;
                x1 = e.pageX;
                y1 = e.pageY;
                if (e.target == scope) {
                    if (l) {
                        $document.on('mousemove', cacheSelect);
                        $document.one('mouseup', uncacheSelect);
                    }
                } else {
                    $current = $(e.target).closest(o.filter);
                    if ($current.hasClass(o.selected)) {
                        $document.one('mouseup', select);
                    } else {
                        select(e);
                    }
                    if (o.sortable && l > 1) {
                        $set = $target.children(o.filter).filter('.' + o.selected);
                        $document.on('mousemove', cacheSort).one('mouseup', uncacheSort);
                    }
                }
            };
            $target.on('mousedown', init);
            $.each([ 'select', 'deselect', 'sort', 'selectStart', 'selectUpdate', 'selectEnd', 'sortStart', 'sortUpdate', 'sortEnd' ], function () {
                var fn = this + '';
                if (typeof o[fn] == 'function') {
                    $target.on(fn, o[fn]);
                }
            });
        });
    };
    S.defaults = {
        sortable: true,
        distance: 7,
        offsetX: 12,
        offsetY: 12,
        filter: 'li',
        selected: 'ui-selected',
        selecting: 'ui-selecting',
        sorting: 'ui-sorting',
        marquee: 'ui-marquee',
        helper: false
    };
    return S;
})(jQuery);
/*
 * SelectSort 1.1
 * by Daniel Schneider - https://profiles.wordpress.org/kungtiger
 *
 * Released under GPLv2 or later
 * http://www.gnu.org/licenses/gpl-2.0.html
 */
window.SelectSort = (function ($) {
    var pythagoras = function (x, y) {
        return Math.sqrt(Math.pow(x, 2) + Math.pow(y, 2));
    };
    var S = function (target, options) {
        var $document = $(document);
        var $body = $('body');
        var o = $.extend({ }, S.defaults, options);
        $(target).each(function () {
            var scope = this;
            var $target = $(this);
            var $current, $set, $marquee, $helper;
            var indexA, offset, x, x1, x2, y, y1, y2, w, w1, h, h1, Cache;
            var can_deselect = true;
            var children = function (selected) {
                return $target.children(o.filter + (selected ? '.' + o.selected : ''));
            };
            var select = function (e) {
                if (e.ctrlKey || e.metaKey) {
                    $current.toggleClass(o.selected);
                } else if (e.shiftKey && indexA !== null) {
                    var indexB = $current.index();
                    var a = Math.min(indexB, indexA);
                    var b = Math.max(indexB, indexA) + 1;
                    children().removeClass(o.selected).slice(a, b).addClass(o.selected);
                } else {
                    children().removeClass(o.selected);
                    $current.addClass(o.selected);
                }
                var $selected = children(true);
                var some = $selected.length > 0;
                if (can_deselect) {
                    if (some) {
                        can_deselect = false;
                        $document.on('mousedown', deselect);
                    } else {
                        $document.off('mousedown', deselect);
                    }
                }
                if (some) {
                    $target.trigger('select', [ $selected, Cache ]);
                } else {
                    $target.trigger('deselect');
                }
                $target.trigger('change', [ some, $selected, Cache ]);
            };
            var deselect = function (e) {
                if ((e.target == scope || $(e.target).closest(scope).length == 0) && !(e.metaKey || e.ctrlKey || e.shiftKey)) {
                    children().removeClass(o.selected);
                    can_deselect = true;
                    $document.off('mousedown', deselect);
                    $target.trigger('deselect');
                    $target.trigger('change', [ false ]);
                    indexA = null;
                }
            };
            var stopSelect = function () {
                $document.off('mousemove', startSelect);
            };
            var stopSort = function () {
                $document.off('mousemove', startSort);
            };
            var cancelSelect = function (e) {
                e.preventDefault();
                if (e.which == 27) {
                    $.each(Cache, function () {
                        this.$.toggleClass(o.selected, this.s);
                    });
                    endSelect();
                }
            };
            var cancelSort = function (e) {
                e.preventDefault();
                if (e.which == 27) {
                    endSort();
                }
            };
            var endSelect = function (e) {
                $body.removeClass(o.selecting);
                $marquee.remove();
                $document.off({
                    mousemove: doSelect,
                    keydown: cancelSelect
                });
                if (e) {
                    var $selected = children(true);
                    if ($selected.length) {
                        $target.trigger('select', [ $selected, Cache ]);
                    } else {
                        $target.trigger('deselect', [ Cache ]);
                    }
                    $target.trigger('change', [ !!$selected.length, $selected, Cache ]);
                }
                $target.trigger('selectEnd', [ Cache ]);
            };
            var endSort = function (e) {
                $body.removeClass(o.sorting);
                $set.removeClass(o.sorting);
                $target.off('mousemove', o.filter, doSort);
                $document.off('keydown', cancelSort);
                if (o.helper) {
                    $helper.remove();
                    $document.off('mousemove', sortHelper);
                }
                if (e) {
                    var updated = false;
                    $.each(Cache, function (i) {
                        if (i !== this.$.index()) {
                            updated = true;
                            return false;
                        }
                    });
                    if (updated) {
                        $target.trigger('update', [ $set, Cache ]);
                    }
                    $target.trigger('sort', [ updated, $set, Cache ]);
                }
                $target.trigger('sortEnd', [ $set, Cache ]);
            };
            var startSelect = function (e) {
                e.preventDefault();
                if (o.distance <= 0 || pythagoras(e.pageX - x1, e.pageY - y1) > o.distance) {
                    offset = $target.offset();
                    x1 -= offset.left;
                    y1 -= offset.top;
                    x2 = parseInt($target.css('paddingLeft'));
                    y2 = parseInt($target.css('paddingTop'));
                    Cache = children().map(function () {
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
                    $marquee = $('<div class="' + o.marquee + '"></div>').hide().appendTo($target);
                    $body.addClass(o.selecting);
                    $document.off({
                        mousemove: startSelect,
                        mouseup: stopSelect
                    });
                    $document.one('mouseup', endSelect);
                    $document.on({
                        keydown: cancelSelect,
                        mousemove: doSelect
                    });
                    if (can_deselect) {
                        can_deselect = false;
                        $document.on('mousedown', deselect);
                    }
                    $target.trigger('selectStart', [ Cache ]);
                }
            };
            var startSort = function (e) {
                e.preventDefault();
                if (o.distance <= 0 || pythagoras(e.pageX - x1, e.pageY - y1) > o.distance) {
                    Cache = children().map(function () {
                        var $this = $(this);
                        return {
                            $: $this,
                            x: $this.offset().left,
                            w: $this.outerWidth() / 2
                        };
                    }).get();
                    $set.addClass(o.sorting);
                    $body.addClass(o.sorting);
                    $document.off({
                        mousemove: startSort,
                        mouseup: stopSort
                    }).on('keydown', cancelSort).one('mouseup', endSort);
                    $target.off('mouseup', select).on('mousemove', o.filter, doSort);
                    if (o.helper) {
                        if (typeof o.helper == 'function') {
                            $helper = o.helper.call(scope, $set, Cache);
                        } else {
                            $helper = $(o.helper);
                        }
                        console.log($helper);
                        $body.append($helper);
                        $document.on('mousemove', sortHelper);
                    }
                    $target.trigger('sortStart', [ $set, Cache ]);
                }
            };
            var doSelect = function (e) {
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
                $.each(Cache, function (_, c) {
                    c.$.toggleClass(o.selected, c.s);
                    if (!(c.x + c.w < x || x + w < c.x || c.y + c.h < y || y + h < c.y)) {
                        $updated.push(c.$.toggleClass(o.selected, !(ctrl && c.s))[0]);
                    }
                });
                if ($updated.length) {
                    $target.trigger('selecting', [ $updated, Cache ]);
                }
            };
            var doSort = function (e) {
                e.preventDefault();
                var $this = $(this);
                if (!$this.hasClass(o.sorting)) {
                    var i = $this.index();
                    $this[(e.pageX - Cache[i].x < Cache[i].w) ? 'before' : 'after']($set);
                    $target.trigger('sorting', [ $this, $set, Cache ]);
                }
            };
            var sortHelper = function (e) {
                $helper.css({
                    top: e.pageY,
                    left: e.pageX
                });
            };
            $target.on('mousedown', function (e) {
                if (e.which == 1 && children().length) {
                    x1 = e.pageX;
                    y1 = e.pageY;
                    if (e.target == scope) {
                        $document.on('mousemove', startSelect).on('mouseup', stopSelect);
                    } else {
                        $current = $(e.target).closest(o.filter);
                        if (!e.shiftKey) {
                            indexA = $current.index();
                        }
                        if ($current.hasClass(o.selected)) {
                            $target.one('mouseup', select);
                        } else {
                            select(e);
                        }
                        if (o.sortable) {
                            $set = children(true);
                            $document.on('mousemove', startSort).one('mouseup', stopSort);
                        }
                    }
                }
            });
            if (o.sortable) {
                children().find('img').attr('draggable', 'false');
            }
            $.each([ 'select', 'deselect', 'sort', 'change', 'update', 'selectStart', 'selecting', 'selectEnd', 'sortStart', 'sorting', 'sortEnd' ], function () {
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
        filter: 'li',
        selected: 'ui-selected',
        selecting: 'ui-selecting',
        sorting: 'ui-sorting',
        marquee: 'ui-marquee'
    };
    return S;
})(jQuery);
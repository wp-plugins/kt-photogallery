/*
 * SortSelect 1.2
 * by Daniel Schneider - https://github.com/kungtiger/SortSelect
 *
 * Released under GPLv2 or later
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

window.SortSelect = (function ($) {
    var limit = function (min, x, max) {
        return Math.max(min, Math.min(x, max));
    };
    var ctrl = function (e) {
        return e.ctrlKey || e.shiftKey || e.metaKey;
    };
    var eventNames = function (string) {
        return String(string).replace(/^\s+|\s+$/, '').split(/\s+/);
    };
    var Instances = [ ];
    var SortSelect = function (target, init) {
        var O = SortSelect.get(target);
        if (O !== null) {
            return O;
        }
        var $grid = $(target).first();
        if ($grid.length == 0) {
            return null;
        }
        var grid = $grid[0];
        var Events = { };
        O = $.extend({
            grid: grid,
            select: function (target) {
                select(the(target));
                return this;
            },
            deselect: function (target) {
                deselect(the(target));
                return this;
            },
            selection: function () {
                return selection();
            },
            cancel: function () {
                cancel();
                return this;
            },
            selecting: function () {
                return selecting;
            },
            sorting: function () {
                return sorting;
            },
            on: function (name, fn) {
                if (typeof fn == 'function') {
                    $.each(eventNames(name), function () {
                        if (!Events[this]) {
                            Events[this] = [ ];
                        }
                        Events[this].push(fn);
                    });
                }
                return this;
            },
            off: function (name, fn) {
                var empty = typeof fn != 'function';
                $.each(eventNames(name), function () {
                    var name = this;
                    if (empty) {
                        Events[name] = [ ];
                    } else {
                        $.each(Events[name], function (i) {
                            if (this == fn) {
                                Events[name].splice(i, 1);
                                return false;
                            }
                        });
                    }
                });
                return this;
            },
            trigger: function (name) {
                var parameters = Array.prototype.slice.call(arguments, 1);
                $.each(eventNames(name), function () {
                    if (Events[this]) {
                        $.each(Events[this], function () {
                            this.apply(O, parameters);
                        });
                    }
                });
                return this;
            },
            destroy: function () {
                $document.off('mousemove', prepare);
                $document.off('mousemove', marquee);
                $document.off('mousemove', follow);
                $document.off('mouseup', stop);
                $document.off('keydown', cancel);
                $grid.off('mousedown', start);
                $grid.off('mousedown', deselect);
                $grid.off('mousemove', O.filter, sort);
                $grid.off('mouseup', select);
                $grid.removeClass(SortSelect.selecting);
                $grid.removeClass(SortSelect.sorting);
                children().removeClass(SortSelect.selected).removeClass(SortSelect.sorting);
                $marquee.remove();
                if ($helper) {
                    $helper.remove();
                }
                for (var i = 0, l = Instances.length; i < l; i++) {
                    if (Instances[i] == O) {
                        Instances.splice(i, 1);
                    }
                }
                delete O;
            }
        }, SortSelect.fn, init);
        var $document = $(document);
        var $current, $selection, $helper;
        var $marquee = $('<div class="' + SortSelect.marquee + '"/>');
        var a, offsetX, offsetY, x0, y0, width, height, Cache;
        var deselectable = true;
        var beyond = false;
        var selecting = false;
        var sorting = false;
        var children = function () {
            return $grid.children(O.filter);
        };
        var selection = function () {
            return children().filter('.' + SortSelect.selected);
        };
        var the = function (mixed) {
            if (mixed) {
                var $elements;
                if (typeof mixed == 'string') {
                    $elements = children().filter(mixed);
                } else {
                    $elements = $(mixed);
                }
                return $elements.filter(function () {
                    return this.parentElement == grid;
                });
            }
            return $();
        };
        var select = function (e) {
            if (e.jquery) {
                if (e.length) {
                    if (O.multiple) {
                        e.addClass(SortSelect.selected);
                    } else {
                        children().removeClass(SortSelect.selected);
                        e.first().addClass(SortSelect.selected);
                    }
                } else {
                    return;
                }
            } else if (!O.multiple) {
                children().removeClass(SortSelect.selected);
                $current.addClass(SortSelect.selected);
            } else if (e.ctrlKey || e.metaKey) {
                $current.toggleClass(SortSelect.selected);
            } else {
                var $children = children();
                $children.removeClass(SortSelect.selected);
                if (e.shiftKey && null !== a) {
                    var b = $current.index();
                    $children.slice(Math.min(a, b), Math.max(a, b) + 1).addClass(SortSelect.selected);
                } else {
                    $current.addClass(SortSelect.selected);
                }
            }
            dispatch();
        };
        var deselect = function (e) {
            var all = false;
            if (e.jquery) {
                all = e.length == 0;
                if (!all) {
                    e.removeClass(SortSelect.selected);
                    dispatch();
                }
            } else {
                all = (e.target == grid || $(e.target).closest(grid).length == 0) && !ctrl(e);
            }
            if (all) {
                children().removeClass(SortSelect.selected);
                $grid.off('mousedown', deselect);
                deselectable = true;
                O.trigger('deselect');
                O.trigger('change', $());
                a = null;
            }
        };
        var dispatch = function () {
            var $selection = selection();
            var any = $selection.length > 0;
            if (any && deselectable) {
                $grid.on('mousedown', deselect);
                deselectable = false;
            } else if (!deselectable && !any) {
                $grid.off('mousedown', deselect);
                deselectable = true;
            }
            if (any) {
                O.trigger('select', $selection);
            } else {
                O.trigger('deselect');
            }
            any ? O.trigger('select', $selection) : O.trigger('deselect');
            O.trigger('change', $selection);
        };
        var start = function (e) {
            var l = children().length;
            if (l && O.selectable && e.which == 1) {
                x0 = e.pageX;
                y0 = e.pageY;
                if (e.target == grid) {
                    if (O.multiple) {
                        selecting = true;
                        $document.on('mousemove', prepare);
                    }
                } else {
                    $current = $(e.target).closest(O.filter);
                    if (!e.shiftKey) {
                        a = $current.index();
                    }
                    if ($current.hasClass(SortSelect.selected)) {
                        $grid.one('mouseup', select);
                    } else {
                        select(e);
                    }
                    if (O.sortable) {
                        $selection = selection();
                        if ($selection.length < l) {
                            sorting = true;
                            $document.on('mousemove', prepare);
                        }
                    }
                }
            }
        };
        var prepare = function (e) {
            e.preventDefault();
            var dx = x0 - e.pageX;
            var dy = y0 - e.pageY;
            beyond = O.distance <= 0 || Math.sqrt(dx * dx + dy * dy) > O.distance;
            if (beyond) {
                var offset = $grid.offset();
                offsetX = offset.left;
                offsetY = offset.top;
                selecting ? prepare_select() : prepare_sort();
                $document.off('mousemove', prepare);
                $document.on('keydown', cancel);
            }
        };
        var prepare_select = function () {
            x0 -= offsetX;
            y0 -= offsetY;
            var paddingX = parseInt($grid.css('paddingLeft'));
            var paddingY = parseInt($grid.css('paddingTop'));
            Cache = children().map(function () {
                var $this = $(this);
                var position = $this.position();
                return {
                    $: $this,
                    x: position.left + paddingX,
                    y: position.top + paddingY,
                    w: $this.outerWidth(),
                    h: $this.outerHeight(),
                    s: $this.hasClass(SortSelect.selected)
                };
            }).get();
            width = $grid.innerWidth();
            height = $grid.innerHeight();
            $marquee.css({
                width: '0px',
                height: '0px'
            });
            $grid.append($marquee);
            $grid.addClass(SortSelect.selecting);
            $document.on('mousemove', marquee);
            O.trigger('start');
        };
        var prepare_sort = function () {
            Cache = children().map(function () {
                var $this = $(this);
                return {
                    $: $this,
                    x: $this.offset().left,
                    w: $this.outerWidth() / 2
                };
            }).get();
            $grid.addClass(SortSelect.sorting);
            $grid.off('mouseup', select);
            $grid.on('mousemove', O.filter, sort);
            if (O.helper) {
                if (typeof O.helper == 'function') {
                    $helper = $(O.helper.call(O, $selection));
                } else {
                    $helper = $(O.helper);
                }
                $helper.css({
                    left: x0,
                    top: y0
                });
                $grid.append($helper);
                $document.on('mousemove', follow);
            }
            O.trigger('start', $selection);
        };
        var marquee = function (e) {
            e.preventDefault();
            var x1 = limit(0, e.pageX - offsetX, width);
            var y1 = limit(0, e.pageY - offsetY, height);
            var w = Math.abs(x0 - x1);
            var h = Math.abs(y0 - y1);
            var x = Math.min(x0, x1);
            var y = Math.min(y0, y1);
            $marquee.css({
                left: x,
                top: y,
                width: w + 'px',
                height: h + 'px'
            });
            $.each(Cache, function (i, c) {
                c.$.toggleClass(SortSelect.selected, c.s);
                if (!(c.x + c.w < x || x + w < c.x || c.y + c.h < y || y + h < c.y)) {
                    c.$.toggleClass(SortSelect.selected, !(ctrl(e) && c.s));
                }
            });
            var $selection = selection();
            O.trigger('change', $selection);
        };
        var sort = function (e) {
            e.preventDefault();
            var $this = $(this);
            if (!$this.hasClass(SortSelect.selected)) {
                var i = $this.index();
                $this[(e.pageX - Cache[i].x < Cache[i].w) ? 'before' : 'after']($selection);
                O.trigger('sorting', $selection);
            }
        };
        var follow = function (e) {
            $helper.css({
                left: e.pageX - offsetX,
                top: e.pageY - offsetY
            });
        };
        var cancel = function (e) {
            if (!e || e.which == 27) {
                if (selecting) {
                    $.each(Cache, function () {
                        this.$.toggleClass(SortSelect.selected, this.s);
                    });
                } else {
                    $.each(Cache, function () {
                        $grid.append(this.$);
                    });
                }
                stop();
                O.trigger('cancel');
                if (e) {
                    e.preventDefault();
                }
            }
        };
        var stop = function (e) {
            if (beyond) {
                $document.off('keydown', cancel);
                if (selecting) {
                    $grid.removeClass(SortSelect.selecting);
                    $marquee.detach();
                    $document.off('mousemove', marquee);
                    if (e) {
                        dispatch();
                        O.trigger('stop');
                    }
                } else {
                    $grid.removeClass(SortSelect.sorting);
                    $grid.off('mousemove', O.filter, sort);
                    if (O.helper) {
                        $helper.remove();
                        $document.off('mousemove', follow);
                    }
                    if (e) {
                        $.each(Cache, function (i) {
                            if (i !== this.$.index()) {
                                O.trigger('update');
                                return false;
                            }
                        });
                        O.trigger('stop');
                    }
                }
                beyond = false;
            } else {
                $document.off('mousemove', prepare);
            }
            selecting = false;
            sorting = false;
        };
        $grid.on('mousedown', start);
        $document.on('mouseup', stop);
        Instances.push(O);
        return O;
    };
    $.extend(SortSelect, {
        selected: 'ui-selected',
        selecting: 'ui-selecting',
        sorting: 'ui-sorting',
        marquee: 'ui-marquee',
        fn: {
            selectable: true,
            multiple: true,
            sortable: true,
            distance: 7,
            filter: 'li'
        },
        get: function (target) {
            var grid = $(target)[0];
            for (var i = 0, l = Instances.length; i < l; i++) {
                if (Instances[i].grid == grid) {
                    return Instances[i];
                }
            }
            return null;
        }
    });
    return SortSelect;
})(jQuery);

=== Photogallery ===
Contributors: kungtiger
Donate Link: none
Tags: photo, image, gallery
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create photo-galleries with ease.

== Description ==

**This plugin is meant primarily for theme developers.**

It allows to collect photos from the the Media Manager and arrange them into albums.   
These albums can be combined into galleries.   
Both albums and galleries can be added to a theme's navigation menu.

**Theme Integration**

You have to write a post type template file for your theme in order for an album or gallery to actually work.
This gives Theme developers the most control over a frontend presentation and users a convenient way to create galleries through the WordPress dashboard.
If you install this plugin, create albums and galleries and include them into your theme's menu, you will be disappointed, since nothing will happen.
There are a number of default templates a user can choose from but you have always the option to write your own template.
This zip comes with example templates. You can use them as a starting point. Just copy them into your current theme's folder.

**Language**

This plugin is in English (en_US) by default but comes with a German (de_DE) po-file.
See also [Using Localizations](https://developer.wordpress.org/plugins/internationalization/localization/#using-localizations).

1. Get [Poedit](http://poedit.net).
2. Open the the po file with Poedit and translate it.
3. Save a copy as e.g `photogallery-fr_FR.po` in `/wp-content/languages/plugins`. The mo-file will be created automatically by Poedit.

**API**

**jQuery `SelectSort` Plugin**

Very simple jQuery Plugin which makes a set of child elements selectable and optionally sortable through drag and drop.   
It offers:

- events
- multiple selection
- keyboard integration
- drag and drop helper for additional UI/UX

`SelectSort(selector, {
    sortable: true,
    distance: 7,
    helper: false,
    offsetX: 12,
    offsetY: 12,
    filter: 'li',
    selected: 'ui-selected',
    selecting: 'ui-selecting',
    sorting: 'ui-sorting',
    marquee: 'ui-marquee',
    select: callback,
    deselect: callback,
    sort: callback,
    selectStart: callback,
    selectUpdate: callback,
    selectEnd: callback,
    sortStart: callback,
    sortUpdate: callback,
    sortEnd: callback
});`

**Initialization Parameters**

- `selector` is a jQuery selector, DOM object or jQuery; basically anything, you would pass to `jQuery()` or `$()`.
- `options` is an optional object for initialization.

**Initialization Options**

- `sortable` *Boolean* `true` if you want to enable drag and drop. `false` for only selecting.
- `distance` *Number* Amount of pixels the mouse has to move before a selection and a sort happens.
- `helper` *DOM|String|jQuery|Function|`false`* If not `false` you can provide a helper which will follow the mouse during a sort. You can use it for additional UX.
- `offsetX` *Number* Amount of offset pixels in x direction relative to the mouse for the helper during a drag.
- `offsetY` *Number* See above, but in y direction.
- `filter` *DOM|String|jQuery* The matching child elements will be selectable and/or sortable.
- `selected` *String* CSS class appended to selected child elements.
- `selecting` *String* CSS class appended to `body` during marquee selection.
- `sorting` *String* CSS class append to selected child elements during sorting.
- `marquee` *String* CSS class append to the marquee during selecting.

**Events**

**`elements`** refers to either all selected or currently sorted child elements.   
**`cache`** refers to an internal cache which stores jQuery representations, position, dimension and other status of all child elements possibly involved in a selection or sorting process. You can access it for speed-up or whatever reason during most events.

- **`select`** `callback(elements, cache)` Fired after one or more child elements got selected.
- **`deselect`** Fired after all child elements are unselected.
- **`sort`** `callback(elements, cache)` Fired after any element got sorted.
- **`selectStart`** `callback(cache)` Always fired after mouse moved beyond `distance` and before a selection marquee is rendered.
- **`selectUpdate`** `callback(elements, cache)` Fired after any child element got selected and during marquee selection.
- **`selectEnd`** Always fired after user releases the mouse and the marquee disappeared.
- **`sortStart`** `callback(elements, cache)` Always fired after mouse moved beyond `distance` and before `helper` gets rendered.
- **`sortUpdate`** `callback(target, elements, cache)` Fires after child elements moved to a new place. `target` refers to the element which triggered `elements` to to be moved before or after it.
- **`sortEnd`** Always fired after user releases the mouse and `helper` disappeared.

== Installation ==

**Through WordPress' Plugin Repository**

1. Goto [wordpress.org/plugins/kt-photogallery](http://wordpress.org/plugins/kt-photogallery) and download the zip
2. Goto your WordPress. You can upload a zip-archive via `/wp-admin/plugin-install.php?tab=upload`
3. Refere to the API section for further details on how to integrate this plugin into a theme

**Manual installation**

1. Upload all files found inside the zip archive to /wp-content/plugins/photogallery
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Refere to the API section for further details on how to integrate this plugin into a theme

== Frequently Asked Questions ==

= Where the heck are my images? =

For now you have to write a custom post type template for galleries and albums.

== Screenshots ==

1. Album Listing (Chromium 38 on Arch Linux 3.17)
2. Album Editor (Chromium 38 on Arch Linux 3.17)
3. Gallery Listing (Chromium 38 on Arch Linux 3.17)
4. Gallery Editor (Chromium 38 on Arch Linux 3.17)
5. Add Album Dialog for the Gallery Editor (Chromium 38 on Arch Linux 3.17)
6. Standart Layouts to choose from (Chromium 38 on Arch Linux 3.17)

== Changelog ==

= 0.9 =
Initial alpha release.

= 1.0 =
- Added default templates for galleries and albums
- Added options page to choose default templates
- Added documentation to options page

== Upgrade Notice ==

= 0.9 =
Initial alpha release.

= 1.0 =
Note that all previous functions for custom templates are now deprecated in favour of OOP versions

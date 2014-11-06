=== Photogallery ===
Contributors: kungtiger
Donate Link: none
Tags: photo, image, gallery
Requires at least: 4.0
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create photo-galleries with ease.

== Description ==

**For now this plugin is meant primarily for theme developers until its first stable release.**

It allows to collect photos from the the Media Manager and arrange them into albums.   
These albums can be combined into galleries.   
Both albums and galleries can be added to a theme's navigation menu.

**Theme Integration**

You have to write a post type template file for your theme in order for an album or gallery to actually work.
This gives Theme developers the most control over a frontend presentation and users a convenient way to create galleries through the WordPress dashboard.
If you install this plugin, create albums and galleries and include them into your theme's menu, you will be disappointed, since nothing will happen.
I will include basic theme support in the first stable release, so users can choose from a simple variety of gallery templates and behaviors.

But for now you need to write two theme templates for custom post types:

- `single-photogallery.php` is for generating an album list or grid
- `single-photogallery_album.php` is for displaying images found inside an album

This zip comes with example templates. You can use them as a starting point. Just copy them into your current theme's folder.

**Language**

This plugin is in English by default but comes with a German po-file.
See also [Using Localizations](https://developer.wordpress.org/plugins/internationalization/localization/#using-localizations).

1. Get [Poedit](http://poedit.net).
2. Open the the po file with Poedit and translate it.
3. Save a copy as `photogallery-de_DE.po` in /wp-content/languages/plugins. The mo-file will be created automatically by Poedit.

== Installation ==

**Through WordPress' Plugin Repository**

1. Goto [wordpress.org/plugins/photogallery](http://wordpress.org/plugins/photogallery) and download the zip
2. Goto your WordPress. You can upload a zip-archive via `/wp-admin/plugin-install.php?tab=upload`
3. Refere to the API section for further details on how to integrate this plugin into a theme

**Manual installation**

1. Upload all files found inside the zip archive to /wp-content/plugins/photogallery
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Refere to the API section for further details on how to integrate this plugin into a theme

== Frequently Asked Questions ==

= Where the heck are my images? =

For now you have to write a custom post type template for galleries and albums. Check out the description and API section for more details.

== Screenshots ==

1. Album Listing
2. Album Editor
3. Gallery Listing
4. Gallery Editor

== Changelog ==

= 0.9 =
Initial alpha release.

== Upgrade Notice ==

= 0.9 =
Initial alpha release.

== API ==

**PHP: `get_photogallery([int $ID])`**

Fetches a Photogallery with all Albums IDs attached to it.
Takes an optional ID of a gallery to be loaded. Defaults to `$wp_query->post->ID` which is the current post/gallery ID if used inside the Loop of WordPress.
Returns an object on success, otherwise false. Use e.g `print_var()` for further details.

**PHP: `get_photoalbum([int $ID])`**

Fetches a Photoalbum with all Data, its Thumbnail and attached Images.
Takes an optional ID of an album to be loaded. Defaults to `$wp_query->post->ID` which is the current post/album ID if used the Loop of WordPress.
Returns an object on success, otherwise false. Use e.g `print_var()` for further details.

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

- **`select`** `callback(elements, cache)` Fired after any child element got selected.
- **`deselect`** Fired after all child elements are unselected.
- **`sort`** `callback(elements, cache)` Fired after any element got sorted.
- **`selectStart`** `callback(cache)` Always fired after mouse moved beyond `distance` and before a selection marquee gets rendered.
- **`selectUpdate`** `callback(elements, cache)` Fired after any child element got selected and during marquee selection.
- **`selectEnd`** Always fired after user releases the mouse and the marquee disappeared.
- **`sortStart`** `callback(elements, cache)` Always fired after mouse moved beyond `distance` and before `helper` gets rendered.
- **`sortUpdate`** `callback(target, elements, cache)` Fires after child elements moved to a new place. `target` refers to the element which triggered `elements` to to be moved before or after it.
- **`sortEnd`** Always fired after user releases the mouse and `helper` disappeared.

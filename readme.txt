=== Photogallery ===
Contributors: kungtiger
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.0.1
Tags: photo, image, gallery, album
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create galleries with ease.

== Description ==

**This plugin is meant primarily for theme developers.**

This plugin allows to collect photos from the the Media Manager and arrange them into albums.   
These albums can be combined into galleries.   
Both albums and galleries can be added to a theme's navigation menu.

Note that this plugin does not provide any CSS formatting and JavaScript for frontend presentation of galleries and albums. You have to format them yourself and integrate necessary JavaScript libraries, e.g Lightbox, yourself. This plugin merely gives a framework for gallery and album creation via custom post types and registration of designs for a frontend presentation.

If you found a bug or have any questions, complains or suggestions please feel free to contact me.

**Theme Integration**

You have to write post type template files for your theme in order for an album or gallery to work.
This gives Theme developers the most control over a frontend presentation and users a convenient way to create galleries through the WordPress dashboard.
If you install this plugin, create albums and galleries and include them into your theme's menu, you will be disappointed, since nothing will happen.

1. Create two php files inside your theme's directory: `single-photogallery.php` and `single-photoalbum.php`. `single-photogallery.php` gets called everytime a post with post type `photogallery` is about to be viewed and `single-photoalbum.php` for post with post type `photoalbum`.  
2. Now you have two options. You could either register a custom design inside your theme's `function.php` via e.g `$kt_Photogallery->register_gallery_design()` and call `$kt_Photogallery->render()` at an appropriated place inside your `single-photogallery.php` or `single-photoalbum.php` to render it depending on the user's choise, or you ignore the user's choise and fetch albums, images and thumbnail details, and directly render your own HTML.  
3. Refere to the PHP API section in this readme for further details on how to retrieve album IDs, image IDs and thumbnail details.

**Language**

This plugin is in English (en_US) by default but comes with a German (de_DE) po-file.
There is also a pot file containing untranslated strings so you can use it as a starting point if you wish to translate this plugin.
See also [Using Localizations](https://developer.wordpress.org/plugins/internationalization/localization/#using-localizations).
And especially [WordPress - Poedit: Translation Secrets](http://www.cssigniter.com/ignite/wordpress-poedit-translation-secrets/).

1. Get [Poedit](http://poedit.net).
2. Open the the pot file with Poedit and translate it.
3. Save a copy as e.g `kt-photogallery-fr_FR.po` in `/wp-content/plugins/kt-photogallery/language`. The mo-file will be created automatically by Poedit if you ticked the checkbox in the preferences.

**PHP API**

I have included a number of functions for fetching album, image and thumbnail IDs associated with a gallery or album.
Please not that all methods starting with an underscore are considered internal and are briefly documented here for the sake of completeness. Although they are publicly accessible you should not use them directly unless you know what you are doing.

- **`kt_Photogallery::get_album_count ( [$gallery_ID] )`**  
Returns the number of albums associated with a gallery  
**Argument** `$gallery_ID` *Optional* - ID of a gallery. Defaults to the current ID if used inside the Loop  
**Returns** `integer`|`boolean` - Returns integer on success, or `false` if `$gallery_ID`yields no gallery

- **`kt_Photogallery::get_albums ( [$gallery_ID] )`**  
Returns an array of album IDs associated with an gallery.  
**Argument** `$gallery_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Returns** `array`|`boolean` - Returns an array of IDs on success, `false` if `$gallery_ID` yields no gallery

- **`kt_Photogallery::get_image_count ( [$album_ID] )`**  
Returns the number of images associated with an album  
**Argument** `$album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Returns** `integer`|`boolean` - Returns integer on success, or `false` if `$album_ID` yields no album

- **`kt_Photogallery::get_images ( [$album_ID] )`**  
Returns an array of image IDs associated with an album.  
**Argument** `$album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Returns** `array`|`boolean` - Returns an array of IDs on success, `false` if `$album_ID` yields no album

- **`kt_Photogallery::get_thumbnail ( [$album_ID, [$fallback] ] )`**  
Returns the ID of the image (attachment) used as thumbnail for an album  
**Argument** `$album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Argument** `$fallback` *Optional* - if `true` and `$album_ID` yields no album the method returns the ID of the first image associated with the album. Default is `true`  
**Returns** `integer|false` - Returns an array on success, `false` if `$album_ID` yields no album, no thumbnail is set or fallback could be resolved

- **`kt_Photogallery::get_thumbnail_src ( [$album_ID, [$fallback] ] )`**  
Returns an ordered array with values corresponding to the (0) url, (1) width, (2) height and (3) scale of the thumbnail associated with an album.  
**Argument** `$album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Argument** `$fallback` *Optional* - if `true` and `$album_ID` yields no album the method returns the ID of the first image associated with the album. Default is `true`  
**Returns** `array|false` - Returns an array on success, `false` if `$album_ID` yields no album, no thumbnail is set or fallback could be resolved

- **`kt_Photogallery::register_album_design ( $key, $options )`**
- **`kt_Photogallery::register_gallery_design ( $key, $options )`**
Registers a custom design for albums and galleries respectively.  
The design will be available in the Album Design metabox during editing  
**Returns** `boolean` - returns `true` if the design was registered successfully, `false` on failure.  
  **Argument** `$key` *Required* - A key used as id inside HTML/CSS and for general identification  
  **Argument** `$options` *Required* - An associative array:

  - `string label` - The text for the label
  - `string icon` - The image shown next to the label. Can be `dashicons-*`, an URL to an image or a base 64 encoded image
  - `string title` - Text used inside the HTML title tag, usually containing a description
  - `callback render ($post, $options)` - Callback rendering the design on the frontend. The arguments passed are the current post as a WP_Post instance and an associative array of the options straight from the database
  - `callback options ($current_options, $defaults, $post)` - Callback for additional form fields, should echo HTML. The arguments passed are the current post as a WP_Post instance and an associative array of the options straight from the database
  - `array defaults` - Associative array containing the default values for options. Note that its keys are used during saving so you should generate HTML form fields `options` and maybe a callback for filtering.
  - `callback filter ($current_options, $defaults, $post)` - Callback for filtering the options before they are saved. This callback is called every time a post is saved. The arguments passed are the default options merged with the values from the current request, the default options as second argument and the current post as a WP_Post instance as third. The callback must return an associative array otherwise no options are stored for this design.

- **`kt_Photogallery::render`**  
Main output method. Depending on the current post type the method prints out a design for a gallery or album.

**Internal**

- **`kt_Photogallery::_add_custom_album_columns`** - Filter for custom post type specific table columns
- **`kt_Photogallery::_add_custom_gallery_columns`** - Filter for custom post type specific table columns
- **`kt_Photogallery::_add_album_metaboxes`** - Handler for hook `add_meta_boxes_photoalbum`
- **`kt_Photogallery::_add_gallery_metaboxes`** - Handler for hook `add_meta_boxes_photogallery`
- **`kt_Photogallery::_add_help_tabs`** - Handler for hook `admin_head`. Adds post type specific help tabs
- **`kt_Photogallery::_ajax_load_albums`** - Ajax handler for action `load_albums`
- **`kt_Photogallery::_deprecated`** - Helper method showing a waring if some deprecated is used
- **`kt_Photogallery::_enqueue_scripts`** - Handler for hook `admin_enqueue_scripts`
- **`kt_Photogallery::_error`** - Helper method for error triggering with proper function backtrace
- **`kt_Photogallery::_init`** - Handler for hook `plugins_loaded`
- **`kt_Photogallery::_menu`** - Handler for hook `admin_menu`. Adds missing 'New Album' link to the menu
- **`kt_Photogallery::_register_post_types`** - Registers custom post types `photogallery` and `photoalbum`
- **`kt_Photogallery::_render_album_design_metabox`** - Handler for `add_meta_box()`
- **`kt_Photogallery::_render_album_thumbnail_metabox`** - Handler for `add_meta_box()`
- **`kt_Photogallery::_render_custom_album_columns`** - Handler for hook `manage_photoalbum_posts_custom_column`. Renders custom post type specific table columns
- **`kt_Photogallery::_render_custom_gallery_columns`** - Handler for hook `manage_photogallery_posts_custom_column`. Renders custom post type specific table columns
- **`kt_Photogallery::_render_grid`** - Handler for `add_meta_box()`
- **`kt_Photogallery::_render_grid_metabox`** - Handler for hook `edit_form_after_title`
- **`kt_Photogallery::_render_gallery_design_metabox`** - Handler for `add_meta_box()`
- **`kt_Photogallery::_rewrite_flush`** - Handler for `register_activation_hook()`
- **`kt_Photogallery::_save_gallery_metadata`** - Handler for hook `save_post_photogallery`
- **`kt_Photogallery::_save_album_metadata`** - Handler for hook `save_post_photoalbum`
- **`kt_Photogallery::_slim_editor`** - Filter for TinyMCE init options
- **`kt_Photogallery::_update_messages`** - Filter for custom post type specific feedback messages

**Protected**

- **`kt_Photogallery::ensure`** - Helper method for fool-proofed `$_REQUEST` value fetching
- **`kt_Photogallery::get_meta`** - Helper method for post meta retrieval and processing
- **`kt_Photogallery::help_sidebar`** - Helper for adding the sidebar to the help tabs
- **`kt_Photogallery::maybe_update`** - Contains update procedures and version management
- **`kt_Photogallery::register_default_designs`** - Adds the default album and gallery designs
- **`kt_Photogallery::register_design`** - Helper method for registering a new design
- **`kt_Photogallery::render_album`** - Helper method for rendering album HTML to be displayed on the backend  
- **`kt_Photogallery::render_default_album_list`** - Handler for `add_album_design`
- **`kt_Photogallery::render_default_gallery_list`** - Handler for `add_gallery_design`
- **`kt_Photogallery::render_default_album_grid`** - Handler for `add_album_design`
- **`kt_Photogallery::render_default_gallery_grid`** - Handler for `add_gallery_design`
- **`kt_Photogallery::render_design_metabox`** - Helper for `kt_Photogallery::_render_album_design_metabox` and `kt_Photogallery::_render_gallery_design_metabox`
- **`kt_Photogallery::save_design_metadata`** - Helper method for fetching, processing and saving design metadata. Option filters are called here.

**jQuery `SelectSort` Plugin** version 1.1

Very simple jQuery Plugin which makes a set of child elements selectable and optionally sortable through drag and drop.   
It offers:

- events
- multiple selection
- basic keyboard integration for Ctrl and Shift
- drag and drop helper for additional UI/UX

`SelectSort(selector, {
    sortable: true,
    distance: 7,
    helper: false,
    filter: 'li',
    selected: 'ui-selected',
    selecting: 'ui-selecting',
    sorting: 'ui-sorting',
    marquee: 'ui-marquee',
    select: callback,
    deselect: callback,
    sort: callback,
    change: callback,
    update: callback,
    selectStart: callback,
    selecting: callback,
    selectEnd: callback,
    sortStart: callback,
    sorting: callback,
    sortEnd: callback
});`

**Initialization Parameters**

- `selector` is a jQuery selector, DOM object or jQuery; basically anything, you would pass to `jQuery()`.
- `options` is an optional object for initialization.

**Initialization Options**

- `sortable` *Boolean* `true` if you want to enable drag and drop. `false` for only selecting.
- `distance` *Number* Amount of pixels the mouse has to move before a selection and a sort happens.
- `helper` *DOM|String|jQuery|Function|`false`* If not `false` you can provide a helper which will follow the mouse during a sort. You can use it for additional UX.
- `filter` *DOM|String|jQuery* The matching child elements will be selectable and/or sortable.
- `selected` *String* CSS class appended to selected child elements.
- `selecting` *String* CSS class appended to `body` during marquee selection.
- `sorting` *String* CSS class append to selected child elements during sorting.
- `marquee` *String* CSS class append to the marquee during selecting.

**Events**

**`event`** refers to jQuery's Event Object, see [jQuery API - Event Object](http://api.jquery.com/category/events/event-object/)  
**`$elements`** refers to either all selected or currently sorted child elements.  
**Cache`** refers to an internal cache which stores jQuery representations, position, dimension and other status of all child elements possibly involved in a selection or sorting process. You can access it for speed-up or whatever reason during most events.

- **`selectStart`** `callback(object event, array Cache)` Always fired after mouse moved beyond `distance` and before a selection marquee is rendered.
- **`selecting`** `callback(object event, jQuery $elements, array Cache)` Fired after any `$elements` got selected and during marquee selection.
- **`select`** `callback(object event, jQuery $elements, array Cache)` Fired after one or more child elements got selected.
- **`deselect`** `callback(object event)` Fired after all child elements are unselected.
- **`change`** `callback(object event, bool changed, jQuery $elements, array Cache)` Always fired after user releases the mouse and the marquee disappeared.
- **`selectEnd`** `callback(object event, array Cache)` Always fired after user releases the mouse and the marquee disappeared.
- **`sortStart`** `callback(object event, jQuery $elements, array Cache)` Always fired after mouse moved beyond `distance` and before `helper` gets rendered.
- **`sort`** `callback(object event, $elements, cache)` Fired after any element got sorted.
- **`sorting`** `callback(object event, jQuery $target, jQuery $elements, array Cache)` Fires after `$elements` moved to a new place. `$target` refers to the element which triggered `$elements` to to be moved before or after it.
- **`update`** `callback(object event, jQuery $elements, array Cache)` Only fired after user released the mouse and `$elements` moved to a new position.
- **`sort`** `callback(object event, bool updated, jQuery $elements, array Cache)` Always fired after user releases the mouse and `helper` disappeared.
- **`sortEnd`** `callback(object event, jQuery $elements, array Cache)` Always fired after user releases the mouse and `helper` disappeared.

== Installation ==

**Through WordPress' Plugin Repository**

1. Goto [wordpress.org/plugins/kt-photogallery](http://wordpress.org/plugins/kt-photogallery) and download the zip
2. Goto your WordPress. You can upload a zip-archive via `/wp-admin/plugin-install.php?tab=upload`
3. Refere to the API section for further details on how to integrate this plugin into a theme

**Manual installation**

1. Upload all files found inside the zip archive to /wp-content/plugins/kt-photogallery
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Refere to the API section for further details on how to integrate this plugin into a theme

== Frequently Asked Questions ==

= Where the heck are my images? =

You have to write template files for custom post types and add them to your theme. Refere to the Description were you find instructions.

== Screenshots ==

1. Album Listing
2. Album Editor
3. Gallery Listing
4. Gallery Editor
5. Add Album Dialog

== Changelog ==

= 0.9 =
Initial alpha release.

= 1.0 =
- Improved custom post type integration
- Added default design for galleries and albums
- Added support of custom designs
- Added API for fetching albums, images and thumbnails
- Deprecated `get_photogallery` and `get_photoalbum`

== Upgrade Notice ==

= 0.9 =
Initial alpha release.

= 1.0 =
Note that `get_photogallery` and `get_photoalbum` are now deprecated in favour of OOP versions

=== Photogallery ===
Contributors: kungtiger
Requires at least: 4.0
Tested up to: 4.1.1
Stable tag: 1.2.1
Tags: photo, image, picture, gallery, album
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

1. Create two php files inside your theme's directory: `single-photogallery.php` and `single-photoalbum.php`.
  - `single-photogallery.php` gets called everytime a gallery is about to be viewed
  - `single-photoalbum.php` gets called everytime a album is about to be viewed
2. Now you have two options.
  - You can register a custom design inside your theme's `function.php` via e.g `$kt_Photogallery->register_gallery_design()` and call `$kt_Photogallery->render()` at an appropriated place inside your `single-photogallery.php` to render it depending on the user's choice.
  - You fetch albums, images and thumbnail details, and render consistent HTML for all albums and galleries.  
3. Refere to the PHP API section for further details on how to retrieve album IDs, image IDs and thumbnail details.

**Example**

A basic example for a custom gallery design

`# functions.php

$kt_Photogallery->register_gallery_design ('my_gallery_design', array(
  'label' => __('My Gallery Design', 'my-textdomain'),
  'icon' => 'dashicons-format-gallery',
  'title' => __('This is my custom gallery design', 'my-textdomain'),
  'render' => 'render_my_gallery_design'
));

$kt_Photogallery->register_album_design ('my_album_design', array(
  'label' => __('My Album Design', 'my-textdomain'),
  'icon' => 'dashicons-format-image',
  'title' => __('This is my custom album design', 'my-textdomain'),
  'render' => 'render_my_album_design'
));

function render_my_gallery_design ($post) {
  global $kt_Photogallery;
  $album_IDs = $kt_Photogallery->get_albums($post);
  if ($album_IDs) {
    foreach ($album_IDs as $album_ID) {
      $album_thumbnail = $kt_Photogallery->get_thumbnail_src($album_ID);
      echo '<a href="' . get_permalink($album_ID) . '">';
      if ($album_thumbnail) {
        echo '<img src="' . $album_thumbnail[0] . '" alt />';
      }
      echo '</a>';
    }
  } else {
    printf(__('The gallery %s does not contain any albums', 'my-textdomain'), esc_html($post->post_title));
  }
}

function render_my_album_design ($post) {
  global $kt_Photogallery;
  $image_IDs = $kt_Photogallery->get_images($post);
  if ($image_IDs) {
    foreach ($image_IDs as $image_ID) {
      $image = get_post($image_ID);
      if ($image) {
        $image_src = wp_get_attachment_image_src($image_ID, 'medium');
        if (!$image_src) {
          $image_src = wp_get_attachment_image_src($image_ID, 'full');
        }
        echo '<img src="' . $image_src[0] . '" alt />';
      }
    }
  } else {
    printf(__('The album %s does not contain any images', 'my-textdomain'), esc_html($post->post_title));
  }
}`

Basic integration into [Twenty Fifteen](https://wordpress.org/themes/twentyfifteen):

`# single-photogallery.php or single-photoalbum.php
get_header();
?>
<div id="primary" class="content-area">
  <main id="main" class="site-main" role="main">
  <?php
  while (have_posts()) {
    the_post();
    ?>
    <article class="hentry">
      <header class="entry-header">
      <?php
        the_title('<h1 class="entry-title">', '</h1>');
      ?>
      </header>
      <div class="entry-content">
      <?php
        $kt_Photogallery->render();
      ?>
      </div>
    </article>
    <?php
  }
  ?>
  </main>
</div>
<?php
get_footer();`

**Language & Translation**

This plugin is in English (en_US) by default but comes with a German (de_DE) po-file.
There is also a pot file containing untranslated strings so you can use it if you wish to translate this plugin.  
See also [Using Localizations](https://developer.wordpress.org/plugins/internationalization/localization/#using-localizations).  
And especially [WordPress - Poedit: Translation Secrets](http://www.cssigniter.com/ignite/wordpress-poedit-translation-secrets/).

If you want your translation included in the next version of Photogallery, don't hesitate and let me know.

1. Get [Poedit](http://poedit.net).
2. Open the the pot file with Poedit and translate it.
3. Save a copy as e.g `kt-photogallery-fr_FR.po` in `/wp-content/plugins/kt-photogallery/language`. The mo-file will be created automatically by Poedit if you ticked the checkbox in the preferences.

**PHP API**

I have included a number of functions for fetching album, image and thumbnail IDs associated with a gallery or album.
Please note that all methods starting with an underscore are considered internal and are not documented here. Although some are publicly accessible you should not use them directly unless you know what you are doing.

You do not have to create a new kt_Photogallery instance, there is already one in the global namespace.  
Access all public methods via `$kt_Photogallery`.

- **`$kt_Photogallery->get_album_count ( [$gallery_ID] )`**  
Returns the number of albums associated with a gallery  
**Argument** `int`|`null $gallery_ID` *Optional* - ID of a gallery. Defaults to the current ID if used inside the Loop  
**Returns** `integer`|`boolean` - Returns an integer on success, or `false` if `$gallery_ID` yields no gallery

- **`$kt_Photogallery->get_albums ( [$gallery_ID] )`**  
Returns an array of album IDs associated with an gallery.  
**Argument** `int`|`null $gallery_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Returns** `array`|`boolean` - Returns an array of IDs on success, `false` if `$gallery_ID` yields no gallery

- **`$kt_Photogallery->get_image_count ( [$album_ID] )`**  
Returns the number of images associated with an album  
**Argument** `int`|`null $album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Returns** `integer`|`boolean` - Returns an integer on success, or `false` if `$album_ID` yields no album

- **`$kt_Photogallery->get_images ( [$album_ID] )`**  
Returns an array of image IDs associated with an album.  
**Argument** `int`|`null $album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Returns** `array`|`boolean` - Returns an array of IDs on success, `false` if `$album_ID` yields no album

- **`$kt_Photogallery->get_thumbnail ( [$album_ID, [$fallback] ] )`**  
Returns the ID of the image (attachment) used as thumbnail for an album  
**Argument** `int`|`null $album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Argument** `boolean $fallback` *Optional* - if `true` and `$album_ID` yields no album the method returns the ID of the first image associated with the album. Default is `true`  
**Returns** `integer`|`false` - Returns an integer on success, `false` if `$album_ID` yields no album, no thumbnail is set or a fallback could not been resolved

- **`$kt_Photogallery->get_thumbnail_src ( [$album_ID, [$fallback] ] )`**  
Returns an ordered array with values corresponding to the (0) url, (1) width, (2) height and (3) scale of the thumbnail associated with an album.  
**Argument** `int`|`null $album_ID` *Optional* - ID of an album. Defaults to the current ID if used inside the Loop  
**Argument** `boolean $fallback` *Optional* - if `true` and `$album_ID` yields no album the method returns the ID of the first image associated with the album. Default is `true`  
**Returns** `array`|`false` - Returns an array on success, `false` if `$album_ID` yields no album, no thumbnail is set or a fallback could not been resolved

- **`$kt_Photogallery->register_album_design ( $key, $options )`**
- **`$kt_Photogallery->register_gallery_design ( $key, $options )`**  
Registers a custom design for albums and galleries respectively.  
The design will be available in the Design metabox during editing  
**Returns** `boolean` - returns `true` if the design was registered successfully, `false` on failure.  
  **Argument** `string $key` *Required* - A key used as id inside HTML/CSS and for general identification  
  **Argument** `callable`|`array $options` *Required* - A callback rendering the design on the frontend or an associative array:

  - `string label` - The text for the label
  - `string icon` - The image shown next to the label. Can be `dashicons-*`, an URL to an image or a base 64 encoded image
  - `string title` - Text used inside the HTML title tag, usually containing a description
  - `callback render ($post, $options)` - Callback rendering the design on the frontend. The arguments passed are the current post as a WP_Post instance and an associative array of the options straight from the database
  - `callback options ($current_options, $defaults, $post)` - Callback for additional form fields, should echo HTML. The arguments passed are an associative array of the options straight from the database, the default options as second argument and the current post as a WP_Post instance as third.
  - `array  defaults` - Associative array containing default values for options. Its keys are used during saving so you should generate HTML form fields using its keys and provide a callback for filtering.
  - `callback filter ($current_options, $defaults, $post)` - Callback for filtering the options before they are saved. This callback is called every time a post is saved. The arguments passed are the default options merged with the values from the current request, the default options as second argument and the current post as a WP_Post instance as third. The callback must return an associative array otherwise no options are stored.

- **`$kt_Photogallery->render`**  
Main output method. Depending on the current post type the method prints out a design for a gallery or an album.

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

= So far nothing came up =

If you have a question please contact me and we'll see what we can do

== Screenshots ==

1. Album Listing
2. Album Editor
3. Gallery Listing
4. Gallery Editor
5. Add Album Dialog

== Changelog ==

= 1.2.1 =
- Fixes some overzealous esc_html
- Fixes jQuery UI Dialog sizing issue
- Add a spinning animation to the album dialog while loading

= 1.2 =
- Fixes a security issue
- Fixed a JavaScript bug which can break the page
- Updated help tabs

= 1.1 =
- Design Metabox will be hidden if no designs are registered
- Fixed `get_albums()`: protected and private albums are now properly included or excluded
- Fixed `get_meta()`
- Fixed SQL query inside `get_photoalbum()`
- Updated SortSelect.js to version 1.2
- Merged `gallery.js` and `album.js`

= 1.0.1 =
- Removed dead code
- Reduced redundancies
- Fixed some translation strings
- Improved examples inside readme.txt

= 1.0 =
- Improved custom post type integration
- Added support of custom designs
- Added API for fetching albums, images and thumbnails
- Updated SortSelect.js to version 1.1
- Deprecated `get_photogallery` and `get_photoalbum`

= 0.9 =
Initial alpha release.

== Upgrade Notice ==

= 1.2.1 =
- Fixes some styling errors and adds a nice spinner to the album dialog

= 1.2 =
- Fixes a security issue and a nasty JavaScript bug

= 1.1 =
Fixes some bugs

= 1.0.1 =
Maintenance update, no changes to the API

= 1.0 =
Note that `get_photogallery` and `get_photoalbum` are now deprecated in favour of OOP versions

= 0.9 =
Initial alpha release.
<?php

/*
 * Plugin Name: Photogallery
 * Plugin URI: https://wordpress.org/plugins/kt-photogallery
 * Description: Create photo-galleries with ease.
 * Version: 1.0
 * Author: Daniel Schneider
 * Author URI: http://profiles.wordpress.org/kungtiger
 * License: GPL2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /language
 * Text Domain: kt-photogallery
 */

$kt_Photogallery = new kt_Photogallery();

class kt_Photogallery {

    const VERSION = '1.0';

    protected $dir;
    protected $url;

    public function __construct() {
        $this->dir = plugin_basename(dirname(__FILE__));
        $this->url = plugins_url() . '/' . $this->dir;
        register_activation_hook(__FILE__, array($this, '_rewrite_flush'));
        add_action('init', array($this, '_register_post_types'));
        add_action('plugins_loaded', array($this, '_init'));
        add_action('admin_head', array($this, '_add_help_tabs'));
        add_action('admin_menu', array($this, '_menu'));
        add_action('admin_enqueue_scripts', array($this, '_enqueue_scripts'));
        add_action('edit_form_after_title', array($this, '_render_grid_metabox'));
        add_filter('post_updated_messages', array($this, '_update_messages'));
        add_action('add_meta_boxes_photogallery', array($this, '_add_gallery_metaboxes'));
        add_action('add_meta_boxes_photoalbum', array($this, '_add_album_metaboxes'));
        add_action('save_post_photogallery', array($this, '_save_gallery_metadata'));
        add_action('save_post_photoalbum', array($this, '_save_album_metadata'));
        add_action('wp_ajax_load_albums', array($this, '_ajax_load_albums'));
        add_filter('wp_editor_settings', array($this, '_slim_editor'), 10, 2);

        add_filter('manage_photoalbum_posts_columns', array($this, '_add_custom_album_columns'));
        add_action('manage_photoalbum_posts_custom_column', array($this, '_render_custom_album_columns'), 1, 2);
        add_filter('manage_photogallery_posts_columns', array($this, '_add_custom_gallery_columns'));
        add_action('manage_photogallery_posts_custom_column', array($this, '_render_custom_gallery_columns'), 1, 2);

        $this->register_default_designs();
    }

    public function _rewrite_flush() {
        $this->_register_post_types();
        flush_rewrite_rules();
    }

    public function _init() {
        load_plugin_textdomain('kt-photogallery', false, $this->dir . '/language/');
        $this->maybe_update();
    }

    protected function maybe_update(){
        add_option('kt_photogallery_version', self::VERSION);
    }

    public function _menu() {
        global $submenu;
        $submenu['edit.php?post_type=photogallery'][12] = array(__('New Album', 'kt-photogallery'), 'edit_posts', 'post-new.php?post_type=photoalbum');
    }

    public function _add_custom_album_columns($current_columns) {
        # try to insert 'thumbnail' after 'cb' or before 'title'
        $_new = array();
        $added_thumbnail = false;
        foreach ($current_columns as $key => $text) {
            if (!$added_thumbnail) {
                if ($key == 'cb') {
                    $_new[$key] = $text;
                    $_new['thumbnail'] = '';
                    $added_thumbnail = true;
                    continue;
                }
                if ($key == 'title') {
                    $_new['thumbnail'] = '';
                    $_new[$key] = $text;
                    $added_thumbnail = true;
                    continue;
                }
            }
            $_new[$key] = $text;
        }

        # try to insert 'image_count' after 'title' or before 'author'
        $_new2 = array();
        $added_count = false;
        foreach ($_new as $key => $text) {
            if (!$added_count) {
                if ($key == 'title') {
                    $_new2[$key] = $text;
                    $_new2['image_count'] = __('Image Count', 'kt-photogallery');
                    $added_count = true;
                    continue;
                }
                if ($key == 'author') {
                    $_new2['image_count'] = __('Image Count', 'kt-photogallery');
                    $_new2[$key] = $text;
                    $added_count = true;
                    continue;
                }
            }
            $_new2[$key] = $text;
        }

        # if we did not add 'thumbnail' just prepend it
        $_new3 = array();
        if (!$added_thumbnail) {
            $_new3['thumbnail'] = '';
            foreach ($_new2 as $key => $text) {
                $_new3[$key] = $text;
            }
        } else {
            $_new3 = $_new2;
        }

        # if we did not add 'image_count' insert it after 'thumbnail'
        $new_columns = array();
        if (!$added_count) {
            foreach ($_new3 as $key => $text) {
                $new_columns[$key] = $text;
                if ($key == 'thumbnail') {
                    $new_columns['image_count'] = __('Image Count', 'kt-photogallery');
                }
            }
        } else {
            $new_columns = $_new3;
        }

        return $new_columns;
    }

    public function _add_custom_gallery_columns($current_columns) {
        # try to insert 'album_count' after 'title' or before 'author'
        $_new = array();
        $added_count = false;
        foreach ($current_columns as $key => $text) {
            if (!$added_count) {
                if ($key == 'title') {
                    $_new[$key] = $text;
                    $_new['album_count'] = __('Album Count', 'kt-photogallery');
                    $added_count = true;
                    continue;
                }
                if ($key == 'author') {
                    $_new['album_count'] = __('Album Count', 'kt-photogallery');
                    $_new[$key] = $text;
                    $added_count = true;
                    continue;
                }
            }
            $_new[$key] = $text;
        }

        # if we did not add 'album_count' insert it after 'cb'
        $_new2 = array();
        if (!$added_count) {
            foreach ($_new as $key => $text) {
                $_new2[$key] = $text;
                if ($key == 'cb') {
                    $_new2['album_count'] = __('Album Count', 'kt-photogallery');
                    $added_count = true;
                }
            }
        } else {
            $_new2 = $_new;
        }

        # if we still did not add 'album_count' just prepend it
        $new_columns = array();
        if (!$added_count) {
            $new_columns['album_count'] = __('Album Count', 'kt-photogallery');
            foreach ($_new2 as $key => $text) {
                $new_columns[$key] = $text;
            }
        } else {
            $new_columns = $_new2;
        }
        return $new_columns;
    }

    public function _render_custom_album_columns($column_name, $album_ID) {
        if ($column_name == 'thumbnail') {
            echo '<span class="kt-thumbnail">';
            $thumbnail_meta = get_post_meta($album_ID, '_photoalbum_thumbnail', true);
            if ($thumbnail_meta) {
                $thumbnail = wp_get_attachment_image_src($thumbnail_meta, 'thumbnail');
                if ($thumbnail) {
                    echo '<img src="' . $thumbnail[0] . '" alt title="' . esc_attr(get_the_title($thumbnail_meta)) . '" />';
                }
            }
            echo '</span>';
        } else if ($column_name == 'image_count') {
            echo $this->get_image_count($album_ID);
        }
    }

    public function _render_custom_gallery_columns($column_name, $gallery_ID) {
        if ($column_name == 'album_count') {
            echo $this->get_album_count($gallery_ID);
        }
    }

    public function _slim_editor($settings) {
        $post_type = get_post_type();
        if ($post_type && in_array($post_type, array('photogallery', 'photoalbum'))) {
            $settings['media_buttons'] = false;
            $settings['drag_drop_upload'] = false;
            $settings['teeny'] = true;
        }
        return $settings;
    }

    public function _enqueue_scripts() {
        $post_type = get_post_type();
        if (in_array($post_type, array('photogallery', 'photoalbum'))) {
            wp_enqueue_script('selectsort', $this->url . '/js/selectsort-1.1.js', array('jquery'), '1.1');
            wp_enqueue_style('kt-photogallery', $this->url . '/css/admin.css', null, '1.0');
            if ($post_type == 'photogallery') {
                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_style('wp-jquery-ui-dialog');
                wp_enqueue_script('kt-photogallery', $this->url . '/js/gallery.js', array('jquery'), '1.0');
                wp_localize_script('kt-photogallery', 'kt_Photogallery_l10n', array(
                    'add' => __('Add to Gallery', 'kt-photogallery'),
                    'close' => __('Close', 'kt-photogallery'),
                    'title' => __('Choose Albums', 'kt-photogallery')
                ));
            }
            if ($post_type == 'photoalbum') {
                wp_enqueue_media();
                wp_enqueue_script('kt-photoalbum', $this->url . '/js/album.js', array('jquery'), '1.0');
                wp_localize_script('kt-photoalbum', 'kt_Photogallery_l10n', array(
                    'title' => __('Choose Images', 'kt-photogallery'),
                    'add' => __('Add to Album', 'kt-photogallery'),
                    'thumbnail' => __('Choose Thumbnail', 'kt-photogallery'),
                    'use' => __('Choose as Thumbnail', 'kt-photogallery')
                ));
            }
        }
    }

        public function _register_post_types() {
        register_post_type('photogallery', array(
            'label' => __('Photogalleries', 'kt-photogallery'),
            'description' => __('A custom post type for photo galleries', 'kt-photogallery'),
            'labels' => array(
                'name' => __('Galleries', 'kt-photogallery'),
                'singular_name' => __('Gallery', 'kt-photogallery'),
                'menu_name' => __('Photogalleries'),
                'name_menu_bar' => __('Photogallery'),
                'all_items' => __('All Galleries'),
                'add_new' => _x('New Gallery', 'kt-photogallery new gallery', 'kt-photogallery'),
                'add_new_item' => __('Add New Gallery', 'kt-photogallery'),
                'edit_item' => __('Edit Gallery', 'kt-photogallery'),
                'new_item' => __('New Gallery', 'kt-photogallery'),
                'view_item' => __('View Gallery', 'kt-photogallery'),
                'search_items' => __('Search Galleries', 'kt-photogallery'),
                'not_found' => __('No galleries found', 'kt-photogallery'),
                'not_found_in_trash' => __('No galleries found in Trash', 'kt-photogallery')
            ),
            'public' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => true,
            'show_in_menu_bar' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-format-gallery',
            'hierarchical' => false,
            'supports' => array('title', 'author', 'editor', 'comments', 'custom-fields'),
            'has_archive' => true,
            'can_export' => true
        ));
        register_post_type('photoalbum', array(
            'label' => __('Photoalbums', 'kt-photogallery'),
            'description' => __('A custom post type for photo albums', 'kt-photogallery'),
            'labels' => array(
                'name' => __('Albums', 'kt-photogallery'),
                'singular_name' => __('Album', 'kt-photogallery'),
                'menu_name' => __('Photoalbums'),
                'name_menu_bar' => __('Photoalbum'),
                'all_items' => __('All Albums'),
                'add_new' => _x('New Album', 'kt-photogallery new album', 'kt-photogallery'),
                'add_new_item' => __('Add New Album', 'kt-photogallery'),
                'edit_item' => __('Edit Album', 'kt-photogallery'),
                'new_item' => __('New Album', 'kt-photogallery'),
                'view_item' => __('View Album', 'kt-photogallery'),
                'search_items' => __('Search Albums', 'kt-photogallery'),
                'not_found' => __('No albums found', 'kt-photogallery'),
                'not_found_in_trash' => __('No albums found in Trash', 'kt-photogallery')
            ),
            'public' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => 'edit.php?post_type=photogallery',
            'show_in_menu_bar' => true,
            'hierarchical' => false,
            'supports' => array('title', 'author', 'editor', 'comments', 'custom-fields'),
            'has_archive' => true,
            'can_export' => true
        ));
    }

    public function _update_messages($messages) {
        $post = get_post();
        $post_type = $post->post_type;
        if (in_array($post_type, array('photogallery', 'photoalbum'))) {
            $date = '<strong>' . get_the_date(null, $post) . '</strong>';
            $permalink = get_permalink($post->ID);
            $preview_link = esc_url(add_query_arg('preview', 'true', $permalink));
            if ($post_type == 'photoalbum') {
                $messages[$post_type][1] = __('Album updated.', 'kt-photogallery');
                $messages[$post_type][4] = __('Album updated', 'kt-photogallery');
                $messages[$post_type][5] = __('Album published.', 'kt-photogallery');
                $messages[$post_type][6] = __('Album updated.', 'kt-photogallery');
                $messages[$post_type][7] = __('Album saved', 'kt-photogallery');
                $messages[$post_type][8] = __('Album submitted.', 'kt-photogallery');
                $messages[$post_type][9] = sprintf(__('Album scheduled for: $s.', 'kt-photogallery'), $date);
                $messages[$post_type][10] = __('Album draft updated.', 'kt-photogallery');
                $s = __('View album', 'kt-photogallery');
            } else {
                $messages[$post_type][1] = __('Gallery updated.', 'kt-photogallery');
                $messages[$post_type][4] = __('Gallery updated', 'kt-photogallery');
                $messages[$post_type][6] = __('Gallery published.', 'kt-photogallery');
                $messages[$post_type][7] = __('Gallery saved', 'kt-photogallery');
                $messages[$post_type][8] = __('Gallery submitted.', 'kt-photogallery');
                $messages[$post_type][9] = sprintf(__('Gallery scheduled for: $s.', 'kt-photogallery'), $date);
                $messages[$post_type][10] = __('Gallery draft updated.', 'kt-photogallery');
                $s = __('View gallery', 'kt-photogallery');
            }
            $view = sprintf(' <a href="%s">%s</a>', esc_url($permalink), $s);
            $preview = sprintf(' <a target="_blank" href="%s">%s</a>', $preview_link, $s);
            $messages[$post_type][1] .= $view;
            $messages[$post_type][5] = false;
            $messages[$post_type][6] .= $view;
            $messages[$post_type][8] .= $preview;
            $messages[$post_type][9] .= $view;
            $messages[$post_type][10] .= $preview;
        }
        return $messages;
    }

    public function _add_help_tabs() {
        $screen = get_current_screen();
        if ($screen->post_type == 'photogallery') {
            if ($screen->base == 'edit') {
                $screen->add_help_tab(array(
                    'id' => 'help_general',
                    'title' => __('Overview', 'kt-photogallery'),
                    'content' => '<p>' . __('On this page you find all your photogalleries. A photogallery consists of albums which themselfs contain images from your Media Manager. If your current theme supports menus you can add photogalleries to it.', 'kt-photogallery') . '</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_actions',
                    'title' => __('Actions', 'kt-photogallery'),
                    'content' => '
<p>' . __('If you move your mouse over an entry in the list additional options show up:', 'kt-photogallery') . '</p>
<p>
    <ul>
        <li><strong>' . __('Edit', 'kt-photogallery') . '</strong> ' . __('will lead you to the page "Edit Photogallery". A simple click on its name will do the same.', 'kt-photogallery') . '</li>
        <li><strong>' . __('Delete', 'kt-photogallery') . '</strong> ' . __('will move a gallery to the trash. A trashed gallery can not be viewed on your Wordpress side. You can restore a gallery from the trash or delete it permanently at any time.', 'kt-photogallery') . '</li>
    </ul>
</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_nav_menus',
                    'title' => __('Useage', 'kt-photogallery'),
                    'content' => '
<p>' . __('If your current theme supports menus you can add a gallery to them. Simply click Design > Menus and then choose your gallery from the list on the left.', 'kt-photogallery') . '</p>
<p>' . __('If you cannot choose a gallery them make sure your checked Galleries in the Screen Options tab.', 'kt-photogallery') . '</p>
<p>' . __("Depending on your theme's design your gallery will now show up on your Wordpress side.", 'kt-photogallery') . '</p>'
                ));

                $screen->set_help_sidebar('<p><strong>' . __('For more information:', 'kt-photogallery') . '</strong></p>
<p><a href="http://codex.wordpress.org/Posts_Screen" target="_blank">' . __('Documentation on Managing Posts', 'kt-phototgallery') . '</a></p>
<p><a href="https://wordpress.org/support/" target="_blank">' . __('WordPress Support Forums', 'kt-photogallery') . '</a></p>
<p><a href="https://wordpress.org/support/plugin/kt-photogallery/" target="_blank">' . __('Photogallery Support Forum', 'kt-photogallery') . '</a></p>
<p><a href="' . $this->url . '/api/documentation.php" target="_blank">' . __('API Documentation', 'kt-photogallery') . '</a></p>');
            } else {
                $screen->add_help_tab(array(
                    'id' => 'help_general',
                    'title' => __('Name &amp; Permalink', 'kt-photogallery'),
                    'content' => '
<p>
    <ul>
        <li><strong>' . __('Gallery Name', 'kt-photogallery') . '</strong> - ' . __('You have to type a name for your gallery into the text field. Otherwise your gallery will not be available to the public.', 'kt-photogallery') . '</li>
        <li><strong>' . __('Permalink', 'kt-photogallery') . '</strong> - ' . __('After you have typed in a name for your gallery, its unique permalink will be shown beneath the text field based on the name. A permalink is used inside a link to a gallery and it is unique for one gallery.', 'kt-photogallery') . '</li>
    </ul>
</p>
<p>' . __('You can edit the permalink by clicking on the yellow box at the end of the web-address/URL. When you are done editing the permalink click outside the yellow box or press <code>Enter</code> and wait for the new permalink to be checked. If your choice is unique then it is kept, otherwise the yellow box restores the permalink given by Wordpress.', 'kt-photogallery') . '</p>
<p>' . __('If you want to cancel your change press <code>Esc</code>.', 'kt-photogallery') . '</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_actions',
                    'title' => __('Actions', 'kt-photogallery'),
                    'content' => '
<p>
    <ul>
        <li><strong>' . __('Add Album', 'kt-photogallery') . '</strong> - ' . __('Click this button to add albums to the gallery. A dialog will show up where you can choose albums to be added to the gallery. When finished hit OK.', 'kt-photogallery') . '</li>
        <li><strong>' . __('Remove from Gallery', 'kt-photogallery') . '</strong> - ' . __('After you selected an album this button will show up. Click it to remove albums from this gallery.', 'kt-photogallery') . '</li>
        <li><strong>' . __('Reorder Albums', 'kt-photogallery') . '</strong> - ' . __('To change the order of your albums simply drag and drop them. Left click on an album, keep the button pressed, move the album to its new location and release the mouse button.', 'kt-photogallery') . '</li>
    </ul>
</p>
<p>' . __('If you having trouble rearranging your album try dragging your selection over another album. Try to avoid gaps between albums because only if your mouse pointer is over another album will your selection move to a new location.', 'kt-photogallery') . '</p>
<p>' . __('Hold down <code>Ctrl</code> or <code>Shift</code>, or use your mouse and draw a frame to select more than one album at a time.', 'kt-photogallery') . '</p>'
                ));

                $screen->set_help_sidebar('<p><strong>' . __('For more information:', 'kt-photogallery') . '</strong></p>
<p><a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">' . __('Documentation on Writing and Editing Posts', 'kt-phototgallery') . '</a></p>
<p><a href="https://wordpress.org/support/" target="_blank">' . __('WordPress Support Forums', 'kt-photogallery') . '</a></p>
<p><a href="https://wordpress.org/support/plugin/kt-photogallery/" target="_blank">' . __('Photogallery Support Forum', 'kt-photogallery') . '</a></p>
<p><a href="' . $this->url . '/api/documentation.php" target="_blank">' . __('API Documentation', 'kt-photogallery') . '</a></p>');
            }
        } else if ($screen->post_type == 'photoalbum') {
            if ($screen->base == 'edit') {
                $screen->add_help_tab(array(
                    'id' => 'help_general',
                    'title' => __('Overview', 'kt-photogallery'),
                    'content' => '<p>' . __('On this page you find all your photoalbums. An album contains images from the Media Manager.', 'kt-photogallery') . '</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_actions',
                    'title' => __('Actions', 'kt-photogallery'),
                    'content' => '
<p>' . __('If you move your mouse over an entry in the list additional options show up:', 'kt-photogallery') . '</p>
<p>
    <ul>
        <li><strong>' . __('Edit', 'kt-photogallery') . '</strong> ' . __('will lead you to the page "Edit Photoalbum". A simple click on its name will do the same.', 'kt-photogallery') . '</li>
        <li><strong>' . __('Delete', 'kt-photogallery') . '</strong> ' . __('will move an album to the trash. A trashed album can not be viewed on your Wordpress side. You can restore an album from the trash or delete it permanently at any time.', 'kt-photogallery') . '</li>
    </ul>
</p>'
                ));

                $screen->set_help_sidebar('<p><strong>' . __('For more information:', 'kt-photogallery') . '</strong></p>
<p><a href="http://codex.wordpress.org/Posts_Screen" target="_blank">' . __('Documentation on Managing Posts', 'kt-phototgallery') . '</a></p>
<p><a href="https://wordpress.org/support/" target="_blank">' . __('WordPress Support Forums', 'kt-photogallery') . '</a></p>
<p><a href="https://wordpress.org/support/plugin/kt-photogallery/" target="_blank">' . __('Photogallery Support Forum', 'kt-photogallery') . '</a></p>
<p><a href="' . $this->url . '/api/documentation.php" target="_blank">' . __('API Documentation', 'kt-photogallery') . '</a></p>');
            } else {
                $screen->add_help_tab(array(
                    'id' => 'help_general',
                    'title' => __('Album Name', 'kt-photogallery'),
                    'content' => '
<p>' . __('You have to enter a name for your album into the first text field.', 'kt-photogallery') . '</p>
<p>' . __('Depending on your current theme the album name might actually been used on your Wordpress side.', 'kt-photogallery') . '</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_thumbnail',
                    'title' => __('Thumbnail', 'kt-photogallery'),
                    'content' => '
<p>' . __('If you click on the box on the left you can choose a thumbnail for the album.', 'kt-photogallery') . '</p>
<p>' . __('You can delete a thumbnail by hovering over the box and click on the red button showing up.', 'kt-photogallery') . '</p>
<p>' . __('If you do not choose an thumbnail but the current theme needs one, Wordpress will choose the first available image inside the album and uses it as thumbnail for the album.', 'kt-photogallery') . '</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_photo',
                    'title' => __('Add &amp; Remove Photos', 'kt-photogallery'),
                    'content' => '
<p>
    <ul>
        <li><strong>' . __('Add Photo', 'kt-photogallery') . '</strong> - ' . __('Click this button to add images from the Media Manager to your album.', 'kt-photogallery') . '</li>
        <li><strong>' . __('Remove from Album', 'kt-photogallery') . '</strong> - ' . __('After you selected one or more albums this button will show up. Click it to delete images from the album. Hold down <code>Ctrl</code> or <code>Shift</code>, or use your mouse and draw a frame to delete multiple images at once.', 'kt-photogallery') . '</li>
    </ul>
<p>' . __('Images you deleted from this album are still available to other albums, posts and such. To actually delete an image you have to delete it inside the Media Manager.', 'kt-photogallery') . '</p>'
                ));
                $screen->add_help_tab(array(
                    'id' => 'help_order',
                    'title' => __('Order Photos', 'kt-photogallery'),
                    'content' => '
<p>' . __('To change the order of your images simply drag and drop them. Left click on an image, keep the button pressed, move the image to its new location and release the mouse button.', 'kt-photogallery') . '</p>
<p>' . __('Hold down <code>Ctrl</code> or <code>Shift</code>, or use your mouse and draw a frame to move more than one image at a time.', 'kt-photogallery') . '</p>
<p>' . __('If you having trouble rearranging your images try dragging your selection over another image. Try to avoid gaps between images because only if your mouse pointer is over another image will your selection move to a new location.', 'kt-photogallery') . '</p>'
                ));

                $screen->set_help_sidebar('<p><strong>' . __('For more information:', 'kt-photogallery') . '</strong></p>
<p><a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">' . __('Documentation on Writing and Editing Posts', 'kt-phototgallery') . '</a></p>
<p><a href="' . $this->url . '/api/documentation.html" target="_blank">' . __('API Documentation', 'kt-photogallery') . '</a></p>');
            }
        }
    }

    protected function help_sidebar($screen) {
        $screen->set_help_sidebar('<p><strong>' . __('For more information:', 'kt-photogallery') . '</strong></p>
<p><a href="http://codex.wordpress.org/Posts_Screen" target="_blank">' . __('Documentation on Managing Posts', 'kt-phototgallery') . '</a></p>
<p><a href="https://wordpress.org/support/" target="_blank">' . __('WordPress Support Forums', 'kt-photogallery') . '</a></p>
<p><a href="https://wordpress.org/plugins/kt-photogallery" target="_blank">' . __('Photogallery API', 'kt-photogallery') . '</a></p>
<p><a href="https://wordpress.org/support/plugin/kt-photogallery" target="_blank">' . __('Photogallery Support Forum', 'kt-photogallery') . '</a></p>');
    }

    public function _ajax_load_albums() {
        $nonce = $this->ensure('_load_albums_nonce');
        if ($nonce == null || !wp_verify_nonce($nonce, 'ajax_load_albums')) {
            die('-1');
        }
        global $wpdb;
        $album_IDs = $wpdb->get_col("SELECT `ID` FROM `" . $wpdb->posts . "` WHERE `post_type` = 'photoalbum' AND `post_status` NOT IN ('auto-draft', 'trash', 'revision') ORDER BY `post_title`");
        $html = '';
        if ($album_IDs) {
            foreach ($album_IDs as $album_ID) {
                $html .= $this->render_album($album_ID);
            }
        }
        die($html);
    }

    protected function render_album($album_ID) {
        $html = '';
        $album = get_post($album_ID);
        if ($album) {
            switch ($album->post_status) {
                case 'future':
                    $status_str = sprintf(__('Scheduled for %s', 'kt-photogallery'), get_the_date(null, $album));
                    break;
                case 'publish':
                    $status_str = $album->post_password ? __('Password Protected', 'kt-photogallery') : __('Public', 'kt-photogallery');
                    break;
                case 'draft':
                    $status_str = '<em>' . __('Draft', 'kt-photogallery') . '</em>';
                    break;
                case 'private':
                    $status_str = __('Private', 'kt-photogallery');
                    break;
                default:
                    # something else is going on, better cancel
                    return $html;
            }
            $thumb = false;
            $thumb_ID = get_post_meta($album_ID, '_photoalbum_thumbnail', true);
            if ($thumb_ID) {
                $thumb = wp_get_attachment_image_src($thumb_ID, 'thumbnail');
                if (!$thumb) {
                    $thumb = wp_get_attachment_image_src($thumb_ID, 'full');
                }
            }
            $thumb_html = '';
            if ($thumb) {
                $thumb_html = '<img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" alt />';
            }

            $image_IDs = get_post_meta($album_ID, '_photoalbum_images', true);
            $image_count = 0;
            if ($image_IDs) {
                $image_count = count(explode(',', $image_IDs));
            }
            if ($image_count) {
                $count_str = sprintf(_n('%d Image', '%d Images', $image_count, 'kt-photogallery'), $image_count);
            } else {
                $count_str = __('0 Images', 'kt-photogallery');
            }
            $title_str = $album->post_title;
            if (empty($title_str)) {
                $title_str = '<em>' . __('Unnamed Album', 'kt-photogallery') . '</em>';
            }
            $author_str = sprintf(__('By %s', 'kt-photogallery'), get_the_author_meta('display_name', $album->post_author));
            $html = '<figure class="album">
<input type="hidden" name="albums[]" value="' . $album_ID . '" />
    <span class="kt-thumbnail">' . $thumb_html . '</span>
    <aside>
        <span class="album_title">' . esc_html($title_str) . '</span>
        <span class="album_author">' . esc_html($author_str) . '</span>
        <span class="image_count">' . esc_html($count_str) . '</span>
        <span class="album_status">' . esc_html($status_str) . '</span>
    </aside>
</figure>';
        }
        return $html;
    }

    public function _render_grid($post) {
        echo '
<div class="grid_toolbar">
    <button type="button" id="add" class="button button-primary">' . __('Add', 'kt-photogallery') . '</button>
    <button type="button" id="remove" class="button">' . __('Remove', 'kt-photogallery') . '</button>
</div>';
        if ($post->post_type == 'photogallery') {
            wp_nonce_field('choose_albums', '_albums_nonce', false);
            wp_nonce_field('ajax_load_albums', '_load_albums_nonce', false);
            echo '
<div id="album_dialog" class="kt-grid"></div>
<div id="album_grid" class="kt-grid" data-no-items="' . __('- No Albums -', 'kt-photogallery') . '">';
            $album_meta = get_post_meta($post->ID, '_photogallery_albums', true);
            if ($album_meta) {
                $album_IDs = explode(',', $album_meta);
                foreach ($album_IDs as $album_ID) {
                    echo $album_html = $this->render_album($album_ID);
                }
            }
        } else {
            wp_nonce_field('choose_images', '_images_nonce');
            echo '
<div id="image_grid" class="kt-grid" data-no-items="' . __('- No Images -', 'kt-photogallery') . '">';
            $image_meta = get_post_meta($post->ID, '_photoalbum_images', true);
            if ($image_meta) {
                $image_IDs = explode(',', $image_meta);
                foreach ($image_IDs as $image_ID) {
                    $thumbnail = wp_get_attachment_image_src($image_ID, 'thumbnail');
                    if ($thumbnail) {
                        echo '
    <figure class="image" title="' . esc_attr(get_the_title($image_ID)) . '">
        <input type="hidden" name="images[]" value="' . $image_ID . '" />
        <span class="kt-thumbnail"><img src="' . $thumbnail[0] . '" width="' . $thumbnail[1] . '" height="' . $thumbnail[2] . '" alt /></span>
    </figure>';
                    }
                }
            }
        }
        echo '</div>';
    }

    public function _render_grid_metabox($post) {
        if (in_array($post->post_type, array('photogallery', 'photoalbum'))) {
            global $wp_meta_boxes;
            do_meta_boxes(get_current_screen(), 'exposed', $post);
            unset($wp_meta_boxes[$post->post_type]['exposed']);
        }
    }

    public function _add_gallery_metaboxes() {
        add_meta_box('griddiv', __('Albums', 'kt-photogallery'), array($this, '_render_grid'), 'photogallery', 'exposed');
        add_meta_box('gallery_design', __('Gallery Design', 'kt-photogallery'), array($this, '_render_gallery_design_metabox'), 'photogallery', 'side');
    }

    public function _add_album_metaboxes() {
        add_meta_box('griddiv', __('Images', 'kt-photogallery'), array($this, '_render_grid'), 'photoalbum', 'exposed');
        add_meta_box('album_thumbnail', __('Thumbnail', 'kt-photogallery'), array($this, '_render_album_thumbnail_metabox'), 'photoalbum', 'side');
        add_meta_box('album_design', __('Album Design', 'kt-photogallery'), array($this, '_render_album_design_metabox'), 'photoalbum', 'side');
    }

    protected function render_design_metabox($post_type, $post, $default_design) {
        $design_meta = get_post_meta($post->ID, '_' . $post_type . '_design', true);
        if($design_meta == ''){
            $design_meta = array();
        }
        $current = $design_meta ? $design_meta['id'] : $default_design;
        if (!key_exists('options', $design_meta)) {
            $design_meta['options'] = array();
        }
        wp_nonce_field('choose_design', '_design_nonce', false);
        $designs = $GLOBALS['kt-' . $post_type . '-designs'];
        foreach ($designs as $design_ID => $setup) {
            $icon_class = ' dashicons-before';
            $icon = '';
            $_icon = $setup['icon'];
            $icon_style = '';
            if (!empty($_icon)) {
                if (0 === strpos($_icon, 'data:image/svg+xml;base64,')) {
                    $icon_style = ' style="background-image:url(\'' . esc_attr($_icon) . '\')"';
                    $icon_class = ' svg';
                } else if (preg_match('/^data:image\/(bmp|gif|jpe?g|png);base64,/i', $_icon)) {
                    $icon_style = ' style="background-image:url(\'' . esc_attr($_icon) . '\')"';
                    $icon_class = ' base64';
                } else if (0 === strpos($_icon, 'dashicons-')) {
                    $icon_class = ' dashicons-before ' . sanitize_html_class($_icon);
                } else {
                    $icon = '<img src="' . $_icon . '" alt />';
                }
            }
            echo '
<div class="design-option" id="design-' . $design_ID . '-option">
    <input type="radio" id="design-' . $design_ID . '" name="design" value="' . $design_ID . '"' . checked($current, $design_ID, false) . ' />
    <label for="design-' . $design_ID . '" title="' . ($setup['title'] ? esc_attr($setup['title']) : '') . '">
        <span class="design-icon' . $icon_class . '"' . $icon_style . '>' . $icon . '</span>
        <span class="design-label">' . esc_html($setup['label']) . '</span>
    </label>';
            if ($setup['options']) {
                echo '
    <div class="design-options" id="design-' . $design_ID . '-options">';
                if (key_exists($design_ID, $design_meta['options'])) {
                    $options = $design_meta['options'][$design_ID];
                } else {
                    $options = $setup['defaults'];
                }
                call_user_func($setup['options'], $options, $setup['defaults'], $post);
                echo '</div>';
            }
            echo '
</div>';
        }
    }

    public function _render_gallery_design_metabox($gallery) {
        $this->render_design_metabox('photogallery', $gallery, 'list');
    }

    public function _render_album_design_metabox($album) {
        $this->render_design_metabox('photoalbum', $album, 'grid');
    }

    public function _render_album_thumbnail_metabox($album) {
        $thumb_ID = get_post_meta($album->ID, '_photoalbum_thumbnail', true);
        $thumb = wp_get_attachment_image_src($thumb_ID, 'thumbnail');
        wp_nonce_field('choose_thumbnail', '_thumbnail_nonce', false);
        echo '
<input type="hidden" id="thumbnail_id" name="thumbnail_id" value="' . $thumb_ID . '" />
<div class="inside-top">
    <span id="thumbnail_preview" class="kt-thumbnail">' . ($thumb ? '<img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" alt title="' . esc_attr(get_the_title($thumb_ID)) . '"/>' : '') . '</span>
</div>
<div class="inside-bottom">
    <a id="clear_thumbnail">' . __('Clear', 'kt-photogallery') . '</a>
    <button type="button" id="choose_thumbnail" class="button">' . __('Choose', 'kt-photogallery') . '</button>
</div>';
    }

    public function _save_gallery_metadata($ID) {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) or ! current_user_can('edit_post')) {
            return;
        }
        $albums_nonce = $this->ensure('_albums_nonce');
        if ($albums_nonce !== null && wp_verify_nonce($albums_nonce, 'choose_albums')) {
            $album_IDs = $this->ensure('albums');
            if ($album_IDs !== null && is_array($album_IDs)) {
                $album_IDs = array_filter(array_map('trim', $album_IDs));
                update_post_meta($ID, '_photogallery_albums', implode(',', $album_IDs));
            }
        }
        $this->save_design_metadata($ID, 'photogallery', 'list');
    }

    public function _save_album_metadata($ID) {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) or ! current_user_can('edit_post')) {
            return;
        }
        $thumbnail_nonce = $this->ensure('_thumbnail_nonce');
        if ($thumbnail_nonce !== null && wp_verify_nonce($thumbnail_nonce, 'choose_thumbnail')) {
            $thumb_ID = $this->ensure('thumbnail_id');
            if ($thumb_ID !== null) {
                update_post_meta($ID, '_photoalbum_thumbnail', $thumb_ID);
            }
        }
        $images_nonce = $this->ensure('_images_nonce');
        if ($images_nonce !== null && wp_verify_nonce($images_nonce, 'choose_images')) {
            $image_IDs = $this->ensure('images');
            if ($image_IDs !== null && is_array($image_IDs)) {
                $image_IDs = array_filter(array_map('trim', $image_IDs));
                update_post_meta($ID, '_photoalbum_images', implode(',', $image_IDs));
            }
        }
        $this->save_design_metadata($ID, 'photoalbum', 'grid');
    }

    protected function save_design_metadata($ID, $post_type, $default_design) {
        $design_nonce = $this->ensure('_design_nonce');
        if ($design_nonce !== null && wp_verify_nonce($design_nonce, 'choose_design')) {
            $design_IDs = array_keys($GLOBALS['kt-' . $post_type . '-designs']);
            $chosen_design = $this->ensure('design', $design_IDs, $default_design);
            $options = array();
            foreach ($design_IDs as $design_ID) {
                $setup = $GLOBALS['kt-' . $post_type . '-designs'][$design_ID];
                if ($setup['options']) {
                    $defaults = $setup['defaults'];
                    $options[$design_ID] = array();
                    foreach ($defaults as $default_key => $default_value) {
                        $options[$design_ID][$default_key] = $this->ensure($default_key, null, $default_value);
                    }
                    if ($setup['filter']) {
                        $options[$design_ID] = call_user_func($setup['filter'], $options[$design_ID], $defaults, get_post($ID));
                    }
                }
            }
            update_post_meta($ID, '_' . $post_type . '_design', array(
                'id' => $chosen_design,
                'options' => $options
            ));
        }
    }

    protected function ensure($key, $expect = null, $default = null) {
        $value = key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
        if ($expect === null) {
            return $value;
        }
        if (!is_array($expect)) {
            $expect = array($expect);
        }
        return in_array($value, $expect) ? $value : $default;
    }

    public function _error($message, $level = E_USER_WARNING) {
        $backtrace = debug_backtrace();
        $caller = next($backtrace);
        trigger_error('<span style="color: #F00">' . $message . '</span> Error occured in <strong>' . $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong> and triggered', $level);
    }

    public function _deprecated($function, $version, $alternative = null) {
        $this->_error('<code>'.$function . '</code> is depricated since Photogallery version ' . $version . ' and will be removed soon.' . ($alternative ? ' Consider using <code>Photogallery::' . $alternative . '</code>.' : ''));
    }

    protected function register_default_designs() {
        $this->register_album_design('list', array(
            'label' => __('List', 'kt-photogallery'),
            'title' => __('Lists all images next to their title, name of author and date of publication', 'kt-photogallery'),
            'render' => array($this, 'render_default_album_list'),
            'icon' => 'dashicons-list-view'
        ));
        $this->register_album_design('grid', array(
            'label' => __('Grid', 'kt-photogallery'),
            'title' => __('Arranges all images inside a grid using all available width', 'kt-photogallery'),
            'render' => array($this, 'render_default_album_grid'),
            'icon' => 'dashicons-grid-view'
        ));
        $this->register_gallery_design('list', array(
            'label' => __('List', 'kt-photogallery'),
            'title' => __('List all albums thumbnails next-to their title, image count, name of author and date of publication', 'kt-photogallery'),
            'render' => array($this, 'render_default_gallery_list'),
            'icon' => 'dashicons-list-view'
        ));
        $this->register_gallery_design('grid', array(
            'label' => __('Grid', 'kt-photogallery'),
            'title' => __('Arranges all albums thumbnails inside a grid using all available width', 'kt-photogallery'),
            'render' => array($this, 'render_default_gallery_grid'),
            'icon' => 'dashicons-grid-view'
        ));
    }

    /**
     * Registers a custom design for albums. The design will be available in the Album Design metabox during editing
     * @param string $id Unique identifier
     * @param array $options An associative array:<ul>
     * <li><b>label</b> - The text for the label</li>
     * <li><b>icon</b> - The image shown next to the label</li>
     * <li><b>title</b> - Text used inside the HTML title tag, usually containing a description</li>
     * <li><b>render ($post, $options)</b> - Callback rendering the design on the frontend</li>
     * <li><b>options ($current_options, $defaults, $post)</b> - Callback for additional form fields, should echo HTML</li>
     * <li><b>defaults</b> - >Associative array containing the default values for options</li>
     * <li><b>filter ($current_options, $defaults, $post)</b> - Callback for filtering the options before they are saved</li><ul>
     * @return bool Returns <code>true</code> if the design successfuly registered and <code>false</code> on failure
     */
    public function register_album_design($id, $options = null) {
        return $this->register_design('photoalbum', $id, $options);
    }

    /**
     * Registers a custom design for galleries. The design will be available in the Gallery Design metabox during editing
     * @param string $id Unique identifier
     * @param array $options An associative array:<ul>
     * <li><b>label</b> - The text for the label</li>
     * <li><b>icon</b> - The image shown next to the label</li>
     * <li><b>title</b> - Text used inside the HTML title tag, usually containing a description</li>
     * <li><b>render ($post, $options)</b> - Callback rendering the design on the frontend</li>
     * <li><b>options ($current_options, $defaults, $post)</b> - Callback for additional form fields, should echo HTML</li>
     * <li><b>defaults</b> - >Associative array containing the default values for options</li>
     * <li><b>filter ($current_options, $defaults, $post)</b> - Callback for filtering the options before they are saved</li><ul>
     * @return bool Returns <code>true</code> if the design successfuly registered and <code>false</code> on failure
     */
    public function register_gallery_design($id, $options = null) {
        return $this->register_design('photogallery', $id, $options);
    }

    protected function register_design($post_type, $id, $options) {
        $_id = sanitize_key($id);
        if ($_id == '' || !$options['render']) {
            return false;
        }
        $setup = wp_parse_args($options, array(
            'label' => '',
            'render' => null,
            'icon' => 'dashicons-admin-appearance',
            'title' => '',
            'options' => null,
            'defaults' => array(),
            'filter' => null
        ));
        if (!$setup['options']) {
            $setup['defaults'] = array();
            $setup['filter'] = null;
        }
        $key = 'kt-' . $post_type . '-designs';
        if (!key_exists($key, $GLOBALS)) {
            $GLOBALS[$key] = array();
        }
        $GLOBALS[$key][$_id] = $setup;
        return true;
    }

    protected function render_default_gallery_list($gallery) {
        $album_IDs = $this->get_albums($gallery->ID);
        if ($album_IDs) {
            echo '
    <section id="photogallery-' . $gallery->ID . '" class="photogallery photogallery-list">';
            foreach ($album_IDs as $album_ID) {
                $album = get_post($album_ID);
                if ($album && !in_array($album->post_status, array('draft', 'future', 'private')) && empty($album->password)) {
                    $permalink = get_permalink($album_ID);
                    $image_count = $this->get_image_count($album_ID);
                    if ($image_count) {
                        $count_str = sprintf(_n('%d Image', '%d Images', $image_count, 'kt-photogallery'), $image_count);
                    } else {
                        $count_str = __('0 Images', 'kt-photogallery');
                    }
                    $thumb = $this->get_thumbnail_src($album_ID);
                    $author = get_the_author_meta('display_name', $album->post_author);
                    $date = get_the_date(null, $album);
                    echo '
        <figure id="photoalbum-' . $album_ID . '" class="photogallery-list-item">
            <a href="' . $permalink . '" class="album-thumbnail">' . ($thumb ? '
                <img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" />' : '') . '</a>
            <aside class="album-details">
                <a href="' . $permalink . '">' . esc_html($album->post_title) . '</a>
                <span class="album-author">' . esc_html($author) . '</span>
                <span class="image-count">' . esc_html($count_str) . '</span>
                <span class="album-date">' . esc_html($date) . '</span>
            </aside>
        </figure>';
                }
            }
            echo '
    </section>';
        }
    }

    protected function render_default_gallery_grid($gallery) {
        $album_IDs = $this->get_albums($gallery->ID);
        if ($album_IDs) {
            echo '
    <section id="photogallery-' . $gallery->ID . '" class="photogallery photogallery-grid">';
            foreach ($album_IDs as $album_ID) {
                $album = get_post($album_ID);
                if ($album && !in_array($album->post_status, array('draft', 'future', 'private')) && empty($album->password)) {
                    $permalink = get_permalink($album_ID);
                    $thumb = $this->get_thumbnail_src($album_ID);
                    echo '
        <figure id="photogalbum-' . $album_ID . '" class="photogallery-grid-item">
            <a href="' . $permalink . '" class="album-thumbnail">' . ($thumb ? '
                <img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" />' : '') . '</a>
        </figure>';
                }
            }
            echo '
    </section>';
        }
    }

    protected function render_default_album_list($album) {
        $image_IDs = $this->get_images($album->ID);
        if ($image_IDs) {
            echo '
    <section id="photoalbum-' . $album->ID . '" class="photoalbum photoalbum-list">';
            foreach ($image_IDs as $image_ID) {
                $image = get_post($image_ID);
                if ($image) {
                    $permalink = get_permalink($image_ID);
                    $thumb = wp_get_attachment_image_src($image_ID, 'thumbnail');
                    $author = get_the_author_meta('display_name', $image->post_author);
                    $date = get_the_date(null, $image);
                    echo '
        <figure id="photoalbum-image-' . $image_ID . '" class="photoalbum-list-item">
            <a href="' . $permalink . '" class="image-thumbnail">
                <img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" />
            </a>
            <aside class="album-details">
                <a href="' . $permalink . '">' . esc_html($image->post_title) . '</a>
                <span class="album-author">' . esc_html($author) . '</span>
                <span class="album-date">' . esc_html($date) . '</span>
            </aside>
        </figure>';
                }
            }
            echo '
    </section>';
        }
    }

    protected function render_default_album_grid($album) {
        $image_IDs = $this->get_images($album->ID);
        if ($image_IDs) {
            echo '
    <section id="photoalbum-' . $album->ID . '" class="photoalbum photoalbum-grid">';
            foreach ($image_IDs as $image_ID) {
                $image = get_post($image_ID);
                if ($image) {
                    $permalink = get_permalink($image_ID);
                    $thumb = wp_get_attachment_image_src($image_ID, 'thumbnail');
                    echo '
        <figure id="photoalbum-image-' . $image_ID . '" class="photoalbum-grid-item">
            <a href="' . $permalink . '" class="image-thumbnail">
                <img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" />
            </a>
        </figure>';
                }
            }
            echo '
    </section>';
        }
    }

    /**
     * Renders the chosen design for a gallery or an album depending on the current post type.
     */
    public function render() {
        if (!is_admin()) {
            $post = get_post();
            if ($post && in_array($post->post_type, array('photogallery', 'photoalbum'))) {
                $design_meta = get_post_meta($post->ID, '_' . $post->post_type . '_design', true);
                $design_ID = $design_meta['id'];
                $render = $GLOBALS['kt-' . $post->post_type . '-designs'][$design_ID]['render'];
                $options = array();
                if (key_exists($design_ID, $design_meta['options'])) {
                    $options = $design_meta['options'][$design_ID];
                }
                call_user_func($render, $post, $options);
            }
        }
    }

    protected function get_meta($ID, $key) {
        $post = get_post($ID);
        if ($post) {
            $meta = get_post_meta($ID, $key, true);
            if ($meta) {
                return explode(',', $meta);
            }
        }
        return false;
    }

    /**
     * Returns an array containing the IDs of albums associated with an gallery.
     * @param int $gallery_ID Gallery ID
     * @return bool|array Returns the IDs or false if the gallery could not be found
     */
    public function get_albums($gallery_ID = null) {
        return $this->get_meta($gallery_ID, '_photogallery_albums');
    }

    /**
     * Returns an array containing the IDs of images associated with an album.
     * @param int $album_ID Album ID
     * @return bool|array Returns the IDs or false if the album could not be found
     */
    public function get_images($album_ID = null) {
        return $this->get_meta($album_ID, '_photoalbum_images');
    }

    /**
     * Returns the number of albums associated with an gallery.
     * @param int $gallery_ID Gallery ID
     * @return bool|int Returns the number of albums, or false if no albums are found or the gallery does not exist
     */
    public function get_album_count($gallery_ID = null) {
        $album_IDs = $this->get_albums($gallery_ID);
        if ($album_IDs) {
            return count($album_IDs);
        }
        return false;
    }

    /**
     * Returns the number of images associated with an album.
     * @param int $album_ID Album ID
     * @return bool|int Returns the number of images, or false if no images are found or the album does not exist
     */
    public function get_image_count($album_ID = null) {
        $image_IDs = $this->get_images($album_ID);
        if ($image_IDs) {
            return count($image_IDs);
        }
        return false;
    }

    /**
     * Return the ID of the thumbnail attached to an album. If <code>$fallback</code> is <code>true</code> and <code>$album_ID</code> yields no thumbnail the method will return the ID of the first image associated with this album.
     * @param int $album_ID Album ID
     * @param bool $fallback [optional] If <code>true</code> and <code>$album_ID</code> yields no thumbnail the ID of the first image will be returnd.
     * @return bool|int Returns the ID of the thumbnail, or false if no thumbnail or images are available or the album does not exist
     */
    public function get_thumbnail($album_ID = null, $fallback = true) {
        $thumbnail_ID = false;
        $album = get_post($album_ID);
        if ($album) {
            $thumbnail_ID = get_post_meta($album_ID, '_photoalbum_thumbnail', true);
            if (!$thumbnail_ID && $fallback) {
                $thumbnail_ID = reset($this->get_images($album_ID));
            }
        }
        return $thumbnail_ID;
    }

    /**
     * Returns an ordered array with values corresponding to the (0) url, (1) width, (2) height and (3) scale of the thumbnail associated with an album. If <code>$fallback</code> is <code>true</code> and <code>$album_ID</code> yields no thumbnail the method will return the details of the first image associated with this album.
     * @param int $album_ID Album ID
     * @param bool $fallback [optional] If <code>true</code> and <code>$album_ID</code> yields no thumbnail the details of the first image will be returnd.
     * @return bool|array Returns an array or false if no thumbnail or images are available
     */
    public function get_thumbnail_src($album_ID = null, $fallback = true) {
        $thumbnail = false;
        $thumbnail_ID = $this->get_thumbnail($album_ID, $fallback);
        if ($thumbnail_ID) {
            $thumbnail = wp_get_attachment_image_src($thumbnail_ID, 'thumbnail');
            if (!$thumbnail) {
                $thumbnail = wp_get_attachment_image_src($thumbnail_ID, 'full');
            }
        }
        return $thumbnail;
    }

}

/**
 * Fetches a Photogallery with all Albums IDs attached to it.
 * @param integer $ID [optional] ID of the gallery to be loaded. Defaults to $wp_query->post->ID which is the current post/gallery ID if used on the frontend of WordPress.
 * @return boolean|object Returns an object on success, otherwise <b>false</b>. Use <b>print_var</b> for further details.
 * @deprecated since version 1.0
 */
function get_photogallery($ID = null) {
    global $kt_Photogallery;
    $kt_Photogallery->_deprecated(__FUNCTION__, '1.0', 'get_albums');
    global $wpdb;
    if (empty($ID)) {
        global $wp_query;
        if ($wp_query && $wp_query->post && $wp_query->post->ID) {
            $ID = $wp_query->post->ID;
        } else {
            return false;
        }
    }
    $gallery = $wpdb->get_row($wpdb->prepare("SELECT `ID`, `post_title` AS `title`, `post_author` AS `author`, `post_date` AS `date`, `post_date_gmt` AS `date_gmt`, `post_modified` AS `modified`, `post_modified_gmt` AS `modified_gmt`, `post_status` AS `status` FROM `" . $wpdb->prefix . "posts` WHERE `ID` = %d AND `post_type` = 'photogallery'", $ID));
    if ($gallery) {
        $albums = get_post_meta($gallery->ID, '_photogallery_albums', true);
        $gallery->albums = $albums ? explode(',', $albums) : array();
    }
    return $gallery;
}

/**
 * Fetches a Photoalbum with all Data, its Thumbnail and attached Images.
 * @param type $ID [optional] ID of the album to be loaded. Defaults to $wp_query->post->ID which is the current post/album ID if used on the frontend of WordPress.
 * @return boolean Returns an object on success, otherwise <b>false</b>. Use <b>print_var</b> for further details.
 * @deprecated since version 1.0
 */
function get_photoalbum($ID = null) {
    global $kt_Photogallery;
    $kt_Photogallery->_deprecated(__FUNCTION__, '1.0', 'get_images');
    global $wpdb;
    if (empty($ID)) {
        global $wp_query;
        if ($wp_query && $wp_query->post && $wp_query->post->ID) {
            $ID = $wp_query->post->ID;
        } else {
            return false;
        }
    }
    $album = $wpdb->get_row($wpdb->prepare("SELECT `ID`, `post_title` AS `title`, `post_author` AS `author`, `post_date` AS `date`, `post_date_gmt` AS `post_gmt`, `post_modified` AS `modified`, `post_modified_gmt` AS `modified_gmt`, `post_status` AS `status` FROM `" . $wpdb->prefix . "posts` WHERE `ID` = %d AND `post_type` = 'photogallery_album'", $ID));
    if ($album) {
        $album->thumbnail = array();
        $thumbnail_ID = get_post_meta($album->ID, '_photoalbum_thumbnail', true);
        if ($thumbnail_ID) {
            $thumbnail = wp_get_attachment_metadata($thumbnail_ID);
            if ($thumbnail) {
                $thumbnail = array_merge(array('ID' => $thumbnail_ID), $thumbnail);
                $album->thumbnail = $thumbnail;
            }
        }
        $album->images = array();
        $images = get_post_meta($album->ID, '_photoalbum_images', true);
        if ($images) {
            $imageIDs = explode(',', $images);
            foreach ($imageIDs as $imageID) {
                $image = wp_get_attachment_metadata($imageID);
                if ($image) {
                    $image = array_merge(array('ID' => $imageID), $image);
                    $album->images[] = $image;
                }
            }
        }
    }
    return $album;
}

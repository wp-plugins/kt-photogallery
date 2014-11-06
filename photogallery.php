<?php
/*
 * Plugin Name: Photogallery
 * Plugin URI: http://wordpress/plugins/photogallery
 * Description: Create photo-galleries with ease.
 * Version: 1.0
 * Author: Daniel Schneider
 * Author URI: http://profiles.wordpress.org/kungtiger
 * License: GPL2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: photogallery
 */

class Photogallery {

    protected $url;
    protected $wp_posts;
    protected $list_table;

    public function __construct() {
        global $wpdb;
        $this->wp_posts = $wpdb->prefix . 'posts';
        $this->url = plugins_url() . '/photogallery/';

        add_action('init', array($this, 'register_post_type'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_bar_menu', array($this, 'admin_bar_menu'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

        add_action('wp_ajax_save_gallery', array($this, 'ajax_save_gallery'));
        add_action('wp_ajax_save_album', array($this, 'ajax_save_album'));
        add_action('wp_ajax_publish_gallery', array($this, 'ajax_publish_gallery'));
        add_action('wp_ajax_publish_album', array($this, 'ajax_publish_album'));
        add_action('wp_ajax_load_albums', array($this, 'ajax_load_albums'));
        add_action('wp_ajax_save_layout_type', array($this, 'ajax_save_layout_type'));

        add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);

        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function activate() {
        $this->register_post_type();
        flush_rewrite_rules();
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => 'a:0:{}'), array('meta_key' => 'metaboxhidden_nav-menus'), '%s', '%s');
    }

    public function set_screen_option($status, $option, $value) {
        if ($option == 'galleries_per_page' || $option == 'albums_per_page') {
            return $value;
        }
        return $status;
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('photogallery', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function register_post_type() {
        register_post_type('photogallery', array(
            'can_export' => true,
            'has_archive' > true,
            'hierarchical' => false,
            'label' => __('Photogallery', 'photogallery'),
            'public' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_in_nav_menus' => true,
            'query_var' => 'photogallery',
            'labels' => array(
                'name' => __('Photogalleries', 'photogallery'),
                'singular_name' => __('Photogallery', 'photogallery')
            )
        ));
        register_post_type('photogallery_album', array(
            'can_export' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'label' => __('Photoalbum', 'photogallery'),
            'public' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_in_nav_menus' => true,
            'query_var' => 'photoalbum',
            'labels' => array(
                'name' => __('Photoalbum', 'photogallery'),
                'singular_name' => __('Photoalbum', 'photogallery')
            )
        ));
    }

    public function admin_enqueue_scripts() {
        $page = self::ensure('page');
        $action = self::ensure('action');

        wp_register_style('photogallery_admin_style', $this->url . 'photogallery.css');
        wp_register_script('select_sort', $this->url . 'select-sort-1.0.min.js', array('jquery'));
        wp_register_script('photogallery_admin_script', $this->url . 'photogallery.js', array('jquery-ui-dialog'));

        if (in_array($page, array('photogallery', 'photogallery_gallery_manager', 'photogallery_album_list', 'photogallery_album_manager', 'photogallery_layout'))) {
            wp_enqueue_style('photogallery_admin_style');
            $l10n = array(
                'sortable' => array(
                    '1' => esc_js(__('Sorting one Image', 'photogallery')),
                    'n' => esc_js(__('Sorting %d Images', 'photogallery'))
                )
            );
            if (($page == 'photogallery' && $action == 'edit') || $page == 'photogallery_gallery_manager') {
                wp_enqueue_style('wp-jquery-ui-dialog');
                wp_enqueue_script('select_sort');
                $l10n['title'] = esc_js(__('Choose Albums', 'photogallery'));
                $l10n['close'] = esc_js(__('Close', 'photogallery'));
                $l10n['add'] = esc_js(__('Add', 'photogallery'));
                $l10n['status'] = array(
                    'publish' => esc_js(__('Click here to publish this gallery', 'photogallery')),
                    'published' => esc_js(__('This gallery is published', 'photogallery')),
                    'draft' => esc_js(__('Click here to turn this gallery into a draft', 'photogallery')),
                    'drafted' => esc_js(__('This gallery is a draft', 'photogallery'))
                );
                wp_localize_script('photogallery_admin_script', 'wp_L10N', $l10n);
            } else if (($page == 'photogallery_album_list' && $action == 'edit') || $page == 'photogallery_album_manager') {
                wp_enqueue_media();
                wp_enqueue_script('select_sort');
                $l10n['thumbnail'] = array(
                    'title' => esc_js(__('Choose Thumbnail', 'photogallery')),
                    'use' => esc_js(__('Use', 'photogallery'))
                );
                $l10n['image'] = array(
                    'title' => esc_js(__('Choose Images', 'photogallery')),
                    'add' => esc_js(__('Add', 'photogallery'))
                );
                $l10n['status'] = array(
                    'publish' => esc_js(__('Click here to publish this album', 'photogallery')),
                    'published' => esc_js(__('This album is published', 'photogallery')),
                    'draft' => esc_js(__('Click here to turn this album into a draft', 'photogallery')),
                    'drafted' => esc_js(__('This album is a draft', 'photogallery'))
                );
                wp_localize_script('photogallery_admin_script', 'wp_L10N', $l10n);
            }
            wp_enqueue_script('photogallery_admin_script');
        }
    }

    public function admin_bar_menu($wp_admin_bar) {
        $wp_admin_bar->add_node(array(
            'id' => 'new-photogallery',
            'title' => __('Photogallery', 'photogallery'),
            'parent' => 'new-content',
            'href' => get_bloginfo('url') . '/wp-admin/admin.php?page=photogallery_gallery_manager'
        ));
        $wp_admin_bar->add_node(array(
            'id' => 'new-photoalbum',
            'title' => __('Photoalbum', 'photogallery'),
            'parent' => 'new-content',
            'href' => get_bloginfo('url') . '/wp-admin/admin.php?page=photogallery_album_manager'
        ));
    }

    public function admin_menu() {
        $_photogalleries = __('Photogalleries', 'photogallery');
        $_new_gallery = __('New Gallery', 'photogallery');
        $_new_album = __('New Album', 'photogallery');
        $_layout = __('Gallery Layout', 'photogallery');
        $editing = self::ensure('action') == 'edit';
        $gallery_list = add_menu_page($_photogalleries, $_photogalleries, 'upload_files', 'photogallery', array($this, 'gallery_list'), 'dashicons-images-alt2', 35);
        add_submenu_page('photogallery', ($editing ? __('Edit Gallery', 'photogallery') : $_photogalleries), __('All Galleries', 'photogallery'), 'upload_files', 'photogallery', array($this, 'gallery_list'));
        $gallery_manager = add_submenu_page('photogallery', $_new_gallery, $_new_gallery, 'upload_files', 'photogallery_gallery_manager', array($this, 'gallery_manager'));
        $album_list = add_submenu_page('photogallery', ($editing ? __('Edit Album', 'photogallery') : __('Photoalbums', 'photogallery')), __('All Albums', 'photogallery'), 'upload_files', 'photogallery_album_list', array($this, 'album_list'));
        $album_manager = add_submenu_page('photogallery', $_new_album, $_new_album, 'upload_files', 'photogallery_album_manager', array($this, 'album_manager'));
        $gallery_layout = add_submenu_page('photogallery', $_layout, $_layout, 'upload_files', 'photogallery_layout', array($this, 'layout'));

        add_action('load-' . $gallery_list, array($this, 'gallery_list_help'));
        add_action('load-' . $gallery_list, array($this, 'gallery_list_options'));
        add_action('load-' . $gallery_manager, array($this, 'gallery_manager_help'));
        add_action('load-' . $album_list, array($this, 'album_list_help'));
        add_action('load-' . $album_list, array($this, 'album_list_options'));
        add_action('load-' . $album_manager, array($this, 'album_manager_help'));
        add_action('load-' . $gallery_layout, array($this, 'layout_help'));
    }

    public function gallery_list_help() {
        if (self::ensure('action') == 'edit') {
            $this->gallery_manager_help();
            return;
        }
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'help_general',
            'title' => __('Overview', 'photogallery'),
            'content' => '<p>' . __('On this page you find all your photogalleries. A photogallery consists of albums which themselfs contain images from your Media Manager. If your current theme supports menus you can add photogalleries to it.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_actions',
            'title' => __('Actions', 'photogallery'),
            'content' => '
<p>' . __('If you move your mouse over an entry in the list additional options show up:', 'photogallery') . '</p>
<p>
    <ul>
        <li><strong>' . __('Edit', 'photogallery') . '</strong> ' . __('will lead you to the page "Edit Photogallery". A simple click on its name will do the same.', 'photogallery') . '</li>
        <li><strong>' . __('Delete', 'photogallery') . '</strong> ' . __('will move a gallery to the trash. A trashed gallery can not be viewed on your Wordpress side. You can restore a gallery from the trash or delete it permanently at any time.', 'photogallery') . '</li>
    </ul>
</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_nav_menus',
            'title' => __('Useage', 'photogallery'),
            'content' => '
<p>' . __("If your current theme supports menus you can add it to it. Simply click Design > Menus and then choose your gallery from the list on the left.", 'photogallery') . '</p>
<p>' . __("Depending on your theme's design your gallery will now show up on your Wordpress side.", 'photogallery') . '</p>'
        ));
    }

    public function gallery_list_options() {
        $this->list_table = new Photogallery_Gallery_List();
        add_screen_option('per_page', array(
            'label' => __('Galleries', 'photogallery'),
            'default' => 10,
            'option' => 'galleries_per_page'
        ));
    }

    public function gallery_list() {
        // No permission? Off you go ...
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have sufficient permissions to view this page.', 'photogallery'));
        }
        global $wpdb;
        $message = '';

        // Here are all possible actions one can perform on a Gallery.
        $action = self::ensure('action', array('edit', 'publish', 'draft', 'delete', 'restore', 'purge', 'undo'), null);
        $action2 = self::ensure('action2', array('publish', 'draft', 'delete', 'restore', 'purge'), null);
        if ($action == null) {
            $action = $action2;
        }
        if ($action) {
            if ($action == 'edit') {
                $this->gallery_manager();
                return;
            } else {
                $bulk = 0;
                $gallery_ID = self::ensure('gallery_id');
                if ($gallery_ID) {
                    $gallery_IDs = array($gallery_ID);
                } else {
                    $gallery_IDs = self::ensure('galleries');
                    if (!is_array($gallery_IDs)) {
                        $gallery_IDs = array();
                    }
                }
                if (count($gallery_IDs) == 1) {
                    $gallery_title = $wpdb->get_var($wpdb->prepare("SELECT `post_title` FROM `" . $this->wp_posts . "` WHERE `ID` = %d", $gallery_IDs[0]));
                    if (!$gallery_title) {
                        $gallery_title = '<i>' . __('Unnamed Gallery', 'photogallery') . '</i>';
                    }
                }
                $gallery_date = date('Y-m-d H:i:s');
                $gallery_date_gmt = gmdate("Y-m-d H:i:s");
                foreach ($gallery_IDs as $gallery_ID) {
                    switch ($action) {
                        case 'restore':
                            if ($wpdb->query($wpdb->prepare("UPDATE `" . $this->wp_posts . "` SET `post_status` = IF(`post_date` = '0000-00-00 00:00:00','draft','publish') WHERE `ID` = %d", $gallery_ID))) {
                                $bulk++;
                            }
                            break;
                        case 'delete':
                            if ($wpdb->update($this->wp_posts, array('post_status' => 'trash'), array('ID' => $gallery_ID), '%s', '%d')) {
                                $bulk++;
                            }
                            break;
                        case 'publish':
                            if ($wpdb->update($this->wp_posts, array('post_status' => 'publish', 'post_date' => $gallery_date, 'post_date_gmt' => $gallery_date_gmt), array('ID' => $gallery_ID), '%s', '%d')) {
                                $bulk++;
                            }
                            break;
                        case 'draft':
                            if ($wpdb->update($this->wp_posts, array('post_status' => 'draft'), array('ID' => $gallery_ID), '%s', '%d')) {
                                $bulk++;
                            }
                            break;
                        case 'purge':
                            if ($wpdb->delete($this->wp_posts, array('ID' => $gallery_ID), '%d')) {
                                $bulk++;
                            }
                            break;
                    }
                }
                if ($bulk == 1) {
                    $redo_action = array(
                        'publish' => 'draft',
                        'draft' => 'publish',
                        'delete' => 'restore',
                        'restore' => 'delete'
                    );
                    $messages = array(
                        'publish' => sprintf(__('Gallery %s is now public.', 'photogallery'), $gallery_title),
                        'draft' => sprintf(__('Gallery %s is now a draft.', 'photogallery'), $gallery_title),
                        'delete' => sprintf(__('Gallery %s moved to trash', 'photogallery'), $gallery_title),
                        'restore' => sprintf(__('Gallery %s restored from trash', 'photogallery'), $gallery_title),
                        'purge' => sprintf(__('Gallery %s was permanently deleted', 'photogallery'), $gallery_title)
                    );
                    $redo_link = ($action != 'purge' && !Photogallery::ensure('undo')) ? '<br><a href="?page=photogallery&action=' . $redo_action[$action] . '&undo=' . $action . '&gallery_id=' . $gallery_ID . '"> ' . __('Undo', 'photogallery') . '</a>' : '';
                    $message = $messages[$action] . $redo_link;
                } else if ($bulk > 1) {
                    $messages = array(
                        'publish' => sprintf(__('%d galleries are now public.', 'photogallery'), $bulk),
                        'draft' => sprintf(__('%d galleries are now a draft.', 'photogallery'), $bulk),
                        'delete' => sprintf(__('%d galleries moved to trash.', 'photogallery'), $bulk),
                        'restore' => sprintf(__('%d galleries restored from trash.', 'photogallery'), $bulk),
                        'purge' => sprintf(__('%d galleries were permanently deleted.', 'photogallery'), $bulk)
                    );
                    $message = $messages[$action];
                }
            }
        }

        // Main HTML output. Header, Feedback message, Gallery status filter and Gallery Table
        ?>
        <div class="wrap">
            <h2><?php echo esc_html(self::get_title()); ?> <a href="?page=photogallery_gallery_manager&action=new" class="add-new-h2"> <?php self::_e_new_item(); ?></a><?php
                $search_term = self::ensure('s');
                if (!empty($search_term)) {
                    printf(' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;', 'photogallery') . '</span>', esc_html($search_term));
                }
                ?></h2>
            <?php
            $this->list_table->prepare_items();
            $num_publish = $wpdb->get_var("SELECT COUNT(*) FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery' AND `post_status` = 'publish'");
            $num_draft = $wpdb->get_var("SELECT COUNT(*) FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery' AND `post_status` = 'draft'");
            $num_all = $num_publish + $num_draft;
            $num_trash = $wpdb->get_var("SELECT COUNT(*) FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery' AND `post_status` = 'trash'");
            $status = self::ensure('status', array('trash', 'draft', 'publish'), 'all');
            if ($message) {
                echo '
        <div id="message" class="updated"><p>' . $message . '</p></div>';
            }

            // Album status filter: show all or deleted ones
            ?>
            <form method="GET">
                <input type="hidden" name="page" value="<?php echo Photogallery::ensure('page'); ?>" />
                <input type="hidden" name="status" value="<?php echo Photogallery::ensure('status'); ?>" />
                <?php
                $orderby = self::ensure('orderby');
                if (!empty($orderby)) {
                    echo '
                <input type="hidden" name="orderby" value="' . esc_attr($orderby) . '" />';
                }
                $order = self::ensure('order');
                if (!empty($order)) {
                    echo '
                <input type="hidden" name="order" value="' . esc_attr($order) . '" />';
                }
                ?>
                <div class="wp-filter">
                    <ul class="filter-links">
                        <li class="all">
                            <a href="?page=photogallery"<?php echo ($status == 'all' ? ' class="current"' : '') ?>><?php _e('All', 'photogallery'); ?> <span class="count">(<?php echo $num_all; ?>)</span></a>
                        </li>
                        <li class="publish">
                            <a href="?page=photogallery&status=publish"<?php echo ($status == 'publish' ? ' class="current"' : '') ?>><span class="label"><?php _e('Public', 'photogallery'); ?></span> <span class="count">(<?php echo $num_publish; ?>)</span></a>
                        </li>
                        <li class="draft">
                            <a href="?page=photogallery&status=draft"<?php echo ($status == 'draft' ? ' class="current"' : '') ?>><span class="label"><?php _e('Drafts', 'photogallery'); ?></span> <span class="count">(<?php echo $num_draft; ?>)</span></a>
                        </li>
                        <li class="trash">
                            <a href="?page=photogallery&status=trash"<?php echo ($status == 'trash' ? ' class="current"' : '') ?>><span class="label"><?php _e('Trash', 'photogallery'); ?></span> <span class="count">(<?php echo $num_trash; ?>)</span></a>
                        </li>
                    </ul>
                    <div class="search-form<?php echo(${'num_' . $status} > 1 ? '' : ' hidden') ?>">
                        <label title="<?php esc_attr_e('Click here to search for galleries', 'photogallery') ?>">
                            <span class="screen-reader-text"><?php _e('Search Galleries', 'photogallery'); ?></span>
                            <span class="magnifier"></span>
                            <input type="search" name="s" value="<?php _admin_search_query(); ?>" class="wp-filter-search" placeholder="<?php esc_attr_e('Search Galleries', 'photogallery'); ?>" />
                        </label>
                        <input type="submit" name id="search-submit" class="button screen-reader-text" value="<?php _e('Search Galleries', 'photogallery'); ?>" />
                    </div>
                </div>
                <?php
                // Render the Table
                $this->list_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function gallery_manager_help() {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'help_general',
            'title' => __('Name &amp; Permalink', 'photogallery'),
            'content' => '
<p>
    <ul>
        <li><strong>' . __('Gallery Name', 'photogallery') . '</strong> - ' . __('You have to type a name for your gallery into the text field. Otherwise your gallery will not be available to the public.', 'photogallery') . '</li>
        <li><strong>' . __('Permalink', 'photogallery') . '</strong> - ' . __('After you have typed in a name for your gallery, its unique permalink will be shown beneath the text field based on the name. A permalink is used inside a link to a gallery and it is unique for one gallery.', 'photogallery') . '</li>
    </ul>
</p>
<p>' . __('You can edit the permalink by clicking on the yellow box at the end of the web-address/URL. When you are done editing the permalink click outside the yellow box or press <code>Enter</code> and wait for the new permalink to be checked. If your choice is unique then it is kept, otherwise the yellow box restores the permalink given by Wordpress.', 'photogallery') . '</p>
<p>' . __('If you want to cancel your change press <code>Esc</code>.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_actions',
            'title' => __('Actions', 'photogallery'),
            'content' => '
<p>
    <ul>
        <li><strong>' . __('Add Album', 'photogallery') . '</strong> - ' . __('Click this button to add albums to the gallery. A dialog will show up where you can choose albums to be added to the gallery. When finished hit OK.', 'photogallery') . '</li>
        <li><strong>' . __('Remove from Gallery', 'photogallery') . '</strong> - ' . __('After you selected an album this button will show up. Click it to remove albums from this gallery.', 'photogallery') . '</li>
        <li><strong>' . __('Reorder Albums', 'photogallery') . '</strong> - ' . __('To change the order of your albums simply drag and drop them. Left click on an album, keep the button pressed, move the album to its new location and release the mouse button.', 'photogallery') . '</li>
    </ul>
</p>
<p>' . __('If you having trouble rearranging your album try dragging your selection over another album. Try to avoid gaps between albums because only if your mouse pointer is over another album will your selection move to a new location.', 'photogallery') . '</p>
<p>' . __('Hold down <code>Ctrl</code> or <code>Shift</code>, or use your mouse and draw a frame to select more than one album at a time.', 'photogallery') . '</p>'
        ));
    }

    public function gallery_manager() {
        // No permission? Off you go...
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have sufficient permissions to view this page.', 'photogallery'));
        }

        // Used to display thumbnails properly.
        $thumb_width = get_option('thumbnail_size_w');
        $thumb_height = get_option('thumbnail_size_h');

        global $wpdb;
        // If one wants to edit an gallery, fetch its data.
        $action = self::ensure('action', array('new', 'edit'), 'new');
        if ($action == 'edit') {
            $gallery_ID = self::ensure('gallery_id');
            $gallery_data = $wpdb->get_row($wpdb->prepare("SELECT `ID`, `post_title`, `post_name`, `post_status` FROM `" . $this->wp_posts . "` WHERE `id` = %d", $gallery_ID), ARRAY_N);
            if ($gallery_data) {
                list($gallery_ID, $gallery_title, $gallery_name, $gallery_status) = $gallery_data;
            } else {
                $action = 'new';
            }
        }

        // If one wants to create a new gallery, look for a draft or create a new one.
        if ($action === 'new') {
            $gallery_title = '';
            $gallery_name = '';
            $gallery_ID = $wpdb->get_var("SELECT `ID` FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery' AND `post_status` = 'new'");
            if (!$gallery_ID) {
                $wpdb->insert($this->wp_posts, array(
                    'post_status' => 'new',
                    'post_type' => 'photogallery',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
                $gallery_ID = $wpdb->insert_id;
            }
        }

        // Fetch Images and their URL

        $_albums = array();
        $album_IDs = get_post_meta($gallery_ID, '_photogallery_albums', true);
        if ($album_IDs != '') {
            $album_IDs = explode(',', $album_IDs);
            foreach ($album_IDs as $album_ID) {
                list($album_title, $album_status) = $wpdb->get_row("SELECT `post_title`, `post_status` FROM `" . $this->wp_posts . "` WHERE `ID` = " . $album_ID, ARRAY_N);
                if ($album_status == 'publish') {
                    $thumbnail_URL = '';
                    $thumbnail_ID = get_post_meta($album_ID, '_photoalbum_thumbnail', true);
                    if ($thumbnail_ID) {
                        $thumbnail_URL = wp_get_attachment_thumb_url($thumbnail_ID);
                        if (!$thumbnail_URL) {
                            $thumbnail_URL = wp_get_attachment_url($thumbnail_ID);
                        }
                    }
                    $_albums[] = '<li data-id="' . $album_ID . '" data-title="' . esc_attr($album_title) . '">' . ($thumbnail_URL ? '<img src="' . $thumbnail_URL . '" />' : '') . '</li>';
                }
            }
        }
        $albums = join('', $_albums);
        ?>
        <div class="wrap">
            <h2><?php
                echo self::get_title();
                if (self::ensure('action') == 'edit') {
                    echo ' <a href="?page=photogallery_gallery_manager&action=new" class="add-new-h2"> ';
                    self::_e_new_item();
                    echo '</a>';
                }
                ?></h2>
            <div id="galleryManager" data-id="<?php echo $gallery_ID; ?>">
                <div id="galleryManagerTop">
                    <label id="gallery_title_label" for="gallery_title"><?php _e('Gallery Name:', 'photogallery'); ?></label>
                    <input type="text" id="gallery_title" value="<?php echo esc_attr($gallery_title); ?>" autocomplete="off" tabindex="1" />
                    <div id="Permalink" class="no-permalink">
                        <label for="gallery_name" title="<?php esc_attr_e('Click here to edit the Permalink', 'photogallery'); ?>">
                            <strong><?php _e('Permalink:', 'photogallery'); ?></strong>
                            <code><?php bloginfo('url'); ?>/photogallery/</code>
                            <input type="text" id="gallery_name" data-autosize-input="on" value="<?php echo esc_html($gallery_name); ?>" autocomplete="off" size="1" />
                        </label>
                    </div>
                    <div id="galleryManagerToolbar">
                        <button id="AddButton" class="button button-secondary dashicons-before dashicons-plus"><?php _e('Add Album', 'photogallery'); ?></button>
                        <button id="DeleteButton" class="button button-secondary hidden dashicons-before dashicons-trash"><?php _e('Remove from Gallery', 'photogallery'); ?></button>
                        <div id="StatusButton" class="button-set<?php echo ($gallery_status == 'new' ? ' hidden' : '') ?>">
                            <button id="PublishButton" class="button button-secondary dashicons-before dashicons-admin-site<?php echo ($gallery_status == 'publish' ? ' active' : '') ?>" data-label="<?php esc_attr_e('Published', 'photogallery'); ?>"></button>
                            <button id="DraftButton" class="button button-secondary dashicons-before dashicons-edit<?php echo ($gallery_status == 'draft' ? ' active' : '') ?>" data-label="<?php esc_attr_e('Draft', 'photogallery'); ?>"></button>
                        </div>
                    </div>
                </div>
                <ul id="ManagerGrid" data-hint="<?php esc_attr_e('Click here to add albums!', 'photogallery'); ?>"><?php echo $albums; ?></ul>
            </div>
            <div id="albumDialog" data-empty="<?php esc_attr_e('No Albums found', 'photogallery'); ?>"></div>
        </div>
        <style type="text/css">
            #ManagerGrid li,
            #albumDialog .album {
                width: <?php echo $thumb_width; ?>px;
                height: <?php echo $thumb_height; ?>px;
            }
        </style>
        <?php
    }

    public function album_list_help() {
        if (self::ensure('action') == 'edit') {
            $this->album_manager_help();
            return;
        }
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'help_general',
            'title' => __('Overview', 'photogallery'),
            'content' => '<p>' . __('On this page you find all your photoalbums. An album contains images from the Media Manager.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_actions',
            'title' => __('Actions', 'photogallery'),
            'content' => '
<p>' . __('If you move your mouse over an entry in the list additional options show up:', 'photogallery') . '</p>
<p>
    <ul>
        <li><strong>' . __('Edit', 'photogallery') . '</strong> ' . __('will lead you to the page "Edit Photoalbum". A simple click on its name will do the same.', 'photogallery') . '</li>
        <li><strong>' . __('Delete', 'photogallery') . '</strong> ' . __('will move a album to the trash. A trashed album can not be viewed on your Wordpress side. You can restore a album from the trash or delete it permanently at any time.', 'photogallery') . '</li>
    </ul>
</p>'
        ));
    }

    public function album_list_options() {
        $this->list_table = new Photogallery_Album_List();
        add_screen_option('per_page', array(
            'label' => __('Albums', 'photogallery'),
            'default' => 10,
            'option' => 'albums_per_page'
        ));
    }

    public function album_list() {
        // No permission? Off you go ...
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have sufficient permissions to view this page.', 'photogallery'));
        }

        global $wpdb;
        $message = '';
        // Here are all possible actions one can perform on an album.
        $action = self::ensure('action', array('edit', 'publish', 'draft', 'delete', 'restore', 'purge'), null);
        $action2 = self::ensure('action2', array('publish', 'draft', 'delete', 'restore', 'purge'), null);
        if ($action == null) {
            $action = $action2;
        }
        if ($action) {
            if ($action == 'edit') {
                $this->album_manager();
                return;
            } else {
                $bulk = 0;
                $album_ID = self::ensure('album_id');
                if ($album_ID) {
                    $album_IDs = array($album_ID);
                } else {
                    $album_IDs = self::ensure('albums');
                    if (!is_array($album_IDs)) {
                        $album_IDs = array();
                    }
                }
                if (count($album_IDs) == 1) {
                    $album_title = $wpdb->get_var($wpdb->prepare("SELECT `post_title` FROM `" . $this->wp_posts . "` WHERE `ID` = %d", $album_IDs[0]));
                    if (!$album_title) {
                        $album_title = '<i>' . __('Unnamed Album', 'photogallery') . '</i>';
                    }
                }
                $gallery_date = date('Y-m-d H:i:s');
                $gallery_date_gmt = gmdate("Y-m-d H:i:s");
                foreach ($album_IDs as $album_ID) {
                    switch ($action) {
                        case 'restore':
                            if ($wpdb->query($wpdb->prepare("UPDATE `" . $this->wp_posts . "` SET `post_status` = IF(`post_date` = '0000-00-00 00:00:00','draft','publish') WHERE `ID` = %d", $album_ID))) {
                                $bulk++;
                            }
                            break;
                        case 'delete':
                            if ($wpdb->update($this->wp_posts, array('post_status' => 'trash'), array('ID' => $album_ID), '%s', '%d')) {
                                $bulk++;
                            }
                            break;
                        case 'publish':
                            if ($wpdb->update($this->wp_posts, array('post_status' => 'publish', 'post_date' => $gallery_date, 'post_date_gmt' => $gallery_date_gmt), array('ID' => $album_ID), '%s', '%d')) {
                                $bulk++;
                            }
                            break;
                        case 'draft':
                            if ($wpdb->update($this->wp_posts, array('post_status' => 'draft'), array('ID' => $album_ID), '%s', '%d')) {
                                $bulk++;
                            }
                            break;
                        case 'purge':
                            if ($wpdb->delete($this->wp_posts, array('ID' => $album_ID), '%d')) {
                                $bulk++;
                            }
                            break;
                    }
                }
                if ($bulk == 1) {
                    $redo_action = array(
                        'publish' => 'draft',
                        'draft' => 'publish',
                        'delete' => 'restore',
                        'restore' => 'delete'
                    );
                    $messages = array(
                        'publish' => sprintf(__('Album %s is now public.', 'photogallery'), $album_title),
                        'draft' => sprintf(__('Album %s is now a draft.', 'photogallery'), $album_title),
                        'delete' => sprintf(__('Album %s moved to trash', 'photogallery'), $album_title),
                        'restore' => sprintf(__('Album %s restored from trash', 'photogallery'), $album_title),
                        'purge' => sprintf(__('Album %s was permanently deleted', 'photogallery'), $album_title)
                    );
                    $redo_link = ($action != 'purge' && !Photogallery::ensure('undo')) ? '<br><a href="?page=photogallery_album_list&action=' . $redo_action[$action] . '&undo=' . $action . '&album_id=' . $album_ID . '"> ' . __('Undo', 'photogallery') . '</a>' : '';
                    $message = $messages[$action] . $redo_link;
                } else if ($bulk > 1) {
                    $messages = array(
                        'publish' => sprintf(__('%d albums are now public.', 'photogallery'), $bulk),
                        'draft' => sprintf(__('%d albums are now a draft.', 'photogallery'), $bulk),
                        'delete' => sprintf(__('%d albums moved to trash.', 'photogallery'), $bulk),
                        'restore' => sprintf(__('%d albums restored from trash.', 'photogallery'), $bulk),
                        'purge' => sprintf(__('%d albums were permanently deleted.', 'photogallery'), $bulk)
                    );
                    $message = $messages[$action];
                }
            }
        }

        // Main HTML output. Header, Feedback message, Album status filter and Table
        ?>
        <div class="wrap">
            <h2><?php echo self::get_title(); ?> <a href="?page=photogallery_album_manager&action=new" class="add-new-h2"> <?php self::_e_new_item(); ?></a><?php
                $search_term = self::ensure('s');
                if (!empty($search_term)) {
                    printf(' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;', 'photogallery') . '</span>', esc_html($search_term));
                }
                ?></h2>
            <?php
            $this->list_table->prepare_items();
            $num_publish = $wpdb->get_var("SELECT COUNT(*) FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery_album' AND `post_status` = 'publish'");
            $num_draft = $wpdb->get_var("SELECT COUNT(*) FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery_album' AND `post_status` = 'draft'");
            $num_all = $num_publish + $num_draft;
            $num_trash = $wpdb->get_var("SELECT COUNT(*) FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery_album' AND `post_status` = 'trash'");
            $status = self::ensure('status', array('trash', 'draft', 'publish'), 'all');
            if ($message) {
                echo '
        <div id="message" class="updated"><p>' . $message . '</p></div>';
            }

            // Album status filter: show all or deleted ones
            ?>

            <form method="GET">
                <input type="hidden" name="page" value="<?php echo Photogallery::ensure('page'); ?>" />
                <input type="hidden" name="status" value="<?php echo Photogallery::ensure('status'); ?>" />
                <?php
                $orderby = self::ensure('orderby');
                if (!empty($orderby)) {
                    echo '
                <input type="hidden" name="orderby" value="' . esc_attr($orderby) . '" />';
                }
                $order = self::ensure('order');
                if (!empty($order)) {
                    echo '
                <input type="hidden" name="order" value="' . esc_attr($order) . '" />';
                }
                ?>
                <div class="wp-filter">
                    <ul class="filter-links">
                        <li class="all">
                            <a href="?page=photogallery_album_list"<?php echo ($status == 'all' ? ' class="current"' : '') ?>><?php _e('All', 'photogallery'); ?> <span class="count">(<?php echo $num_all; ?>)</span></a>
                        </li>
                        <li class="publish">
                            <a href="?page=photogallery_album_list&status=publish"<?php echo ($status == 'publish' ? ' class="current"' : '') ?>><span class="label"><?php _e('Public', 'photogallery'); ?></span> <span class="count">(<?php echo $num_publish; ?>)</span></a>
                        </li>
                        <li class="draft">
                            <a href="?page=photogallery_album_list&status=draft"<?php echo ($status == 'draft' ? ' class="current"' : '') ?>><span class="label"><?php _e('Drafts', 'photogallery'); ?></span> <span class="count">(<?php echo $num_draft; ?>)</span></a>
                        </li>
                        <li class="trash">
                            <a href="?page=photogallery_album_list&status=trash"<?php echo ($status == 'trash' ? ' class="current"' : '') ?>><span class="label"><?php _e('Trash', 'photogallery'); ?></span> <span class="count">(<?php echo $num_trash; ?>)</span></a>
                        </li>
                    </ul>
                    <div class="search-form<?php echo(${'num_' . $status} > 1 ? '' : ' hidden') ?>">
                        <label title="<?php esc_attr_e('Click here to search for albums', 'photogallery') ?>">
                            <span class="screen-reader-text"><?php _e('Search Albums', 'photogallery'); ?></span>
                            <span class="magnifier"></span>
                            <input type="search" name="s" value="<?php _admin_search_query(); ?>" class="wp-filter-search" placeholder="<?php _e('Search Albums', 'photogallery'); ?>" />
                        </label>
                        <input type="submit" name id="search-submit" class="button screen-reader-text" value="<?php _e('Search Albums', 'photogallery'); ?>" />
                    </div>
                </div>
                <?php
                // Render the Table
                $this->list_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function album_manager_help() {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'help_general',
            'title' => __('Album Name', 'photogallery'),
            'content' => '
<p>' . __('You have to enter a name for your album into the first text field.', 'photogallery') . '</p>
<p>' . __('Depending on your current theme the album name might actually been used on your Wordpress side.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_thumbnail',
            'title' => __('Thumbnail', 'photogallery'),
            'content' => '
<p>' . __('If you click on the box on the left you can choose a thumbnail for the album.', 'photogallery') . '</p>
<p>' . __('You can delete a thumbnail by hovering over the box and click on the red button showing up.', 'photogallery') . '</p>
<p>' . __('If you do not choose an thumbnail but the current theme needs one, Wordpress will choose the first available image inside the album and uses it as thumbnail for the album.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_photo',
            'title' => __('Add &amp; Remove Photos', 'photogallery'),
            'content' => '
<p>
    <ul>
        <li><strong>' . __('Add Photo', 'photogallery') . '</strong> - ' . __('Click this button to add images from the Media Manager to your album.', 'photogallery') . '</li>
        <li><strong>' . __('Remove from Album', 'photogallery') . '</strong> - ' . __('After you selected one or more albums this button will show up. Click it to delete images from the album. Hold down <code>Ctrl</code> or <code>Shift</code>, or use your mouse and draw a frame to delete multiple images at once.', 'photogallery') . '</li>
    </ul>
<p>' . __('Images you deleted from this album are still available to other albums, posts and such. To actually delete an image you have to delete it inside the Media Manager.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_order',
            'title' => __('Order Photos', 'photogallery'),
            'content' => '
<p>' . __('To change the order of your images simply drag and drop them. Left click on an image, keep the button pressed, move the image to its new location and release the mouse button.', 'photogallery') . '</p>
<p>' . __('Hold down <code>Ctrl</code> or <code>Shift</code>, or use your mouse and draw a frame to move more than one image at a time.', 'photogallery') . '</p>
<p>' . __('If you having trouble rearranging your images try dragging your selection over another image. Try to avoid gaps between images because only if your mouse pointer is over another image will your selection move to a new location.', 'photogallery') . '</p>'
        ));
    }

    public function album_manager() {
        // No permission? Off you go...
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have sufficient permissions to view this page.', 'photogallery'));
        }

        // Used to display thumbnails propperly.
        $thumb_width = get_option('thumbnail_size_w');
        $thumb_height = get_option('thumbnail_size_h');

        global $wpdb;

        // If one wants to edit an album, fetch its data.
        $action = self::ensure('action', array('new', 'edit'), 'new');
        if ($action === 'edit') {
            $album_ID = self::ensure('album_id');
            $album_data = $wpdb->get_row($wpdb->prepare("SELECT `ID`, `post_title`, `post_name`, `post_status` FROM `" . $this->wp_posts . "` WHERE `id` = %d AND `post_type`='photogallery_album'", $album_ID), ARRAY_N);
            if ($album_data) {
                list($album_ID, $album_title, $album_name, $album_status) = $album_data;
            } else {
                $action = 'new';
            }
        }

        // If one wants to create a new album, look for a draft or create a new one.
        if ($action === 'new') {
            $album_title = '';
            $album_name = '';
            $album_ID = $wpdb->get_var("SELECT `ID` FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery_album' AND `post_status` = 'new'");
            if (!$album_ID) {
                $wpdb->insert($this->wp_posts, array(
                    'post_status' => 'new',
                    'post_type' => 'photogallery_album',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
                $album_ID = $wpdb->insert_id;
            }
        }

        // Get Thumbnail ID and URL
        $thumbnail_ID = get_post_meta($album_ID, '_photoalbum_thumbnail', true);
        $thumbnail_URL = '';
        $thumbnail_IMG = '';
        if ($thumbnail_ID) {
            $url = wp_get_attachment_thumb_url($thumbnail_ID);
            if (!$url) {
                $url = wp_get_attachment_url($thumbnail_ID);
            }
            if ($url) {
                $thumbnail_URL = $url;
            }
        }
        if ($thumbnail_URL) {
            $thumbnail_IMG = '<img src="' . $thumbnail_URL . '" />';
        }

        // Fetch Images and their URL
        $_images = array();
        $imageIDs = get_post_meta($album_ID, '_photoalbum_images', true);
        if ($imageIDs != '') {
            $imageIDs = explode(',', $imageIDs);
            foreach ($imageIDs as $imageID) {
                if ($imageID) {
                    $url = wp_get_attachment_thumb_url($imageID);
                    if (!$url) {
                        $url = wp_get_attachment_url($imageID);
                    }
                    if ($url) {
                        $attachment = get_post($imageID);
                        $_images[] = '<li data-id="' . $imageID . '" title="' . esc_attr($attachment->post_title ? $attachment->post_title : $attachment->post_name) . '"><img src="' . $url . '" /></li>';
                    }
                }
            }
        }
        $images = implode('', $_images);
        ?>
        <div class="wrap">
            <h2><?php
                echo self::get_title();
                if (self::ensure('action') == 'edit') {
                    echo ' <a href="?page=photogallery_album_manager&action=new" class="add-new-h2"> ';
                    self::_e_new_item();
                    echo'</a>';
                }
                ?></h2>
            <div id="albumManager" data-id="<?php echo $album_ID; ?>">
                <div id="albumManagerTop">
                    <div id="albumManagerTopRight">
                        <label id="album_title_label" for="album_title"><?php _e('Album Name:', 'photogallery'); ?></label>
                        <input type="text" id="album_title" value="<?php echo esc_attr($album_title); ?>" autocomplete="off" tabindex="1" />
                        <div id="Permalink" class="no-permalink">
                            <label for="album_name" title="<?php esc_attr_e('Click here to edit the Permalink', 'photogallery'); ?>">
                                <strong><?php _e('Permalink:', 'photogallery'); ?></strong>
                                <code><?php bloginfo('url'); ?>/photoalbum/</code>
                                <input type="text" id="album_name" data-autosize-input="on" value="<?php echo $album_name; ?>" autocomplete="off" size="1" />
                            </label>
                        </div>
                        <div id="albumManagerToolbar">
                            <button id="AddButton" class="button button-secondary dashicons-before dashicons-plus"><?php _e('Add Photos', 'photogallery'); ?></button>
                            <button id="DeleteButton" class="button button-secondary hidden dashicons-before dashicons-trash"><?php _e('Remove from Album', 'photogallery'); ?></button>
                            <div id="StatusButton" class="button-set<?php echo ($album_status == 'new' ? ' hidden' : '') ?>">
                                <button id="PublishButton" class="button button-secondary dashicons-before dashicons-admin-site<?php echo ($album_status == 'publish' ? ' active' : '') ?>" data-label="<?php esc_attr_e('Published', 'photogallery'); ?>"></button>
                                <button id="DraftButton" class="button button-secondary dashicons-before dashicons-edit<?php echo ($album_status == 'draft' ? ' active' : '') ?>" data-label="<?php esc_attr_e('Draft', 'photogallery'); ?>"></button>
                            </div>
                        </div>
                    </div>
                    <div id="Thumbnail" title="<?php esc_attr_e('Click here to choose a thumbnail for this album', 'photogallery'); ?>"<?php echo ($thumbnail_URL ? ' data-thumbnail-id="' . $thumbnail_ID . '"' : ''); ?> data-empty="<?php esc_attr_e('No Thumbnail', 'photogallery') ?>" data-choose="<?php esc_attr_e('Choose', 'photogallery'); ?>"><?php echo $thumbnail_IMG; ?>
                        <div id="DeleteThumbnail" title="<?php esc_attr_e('Delete Thumbnail', 'photogallery'); ?>"></div>
                    </div>
                </div>
                <ul id="ManagerGrid" data-hint="<?php esc_attr_e('Click here to add photos!', 'photogallery'); ?>"><?php echo $images; ?></ul>
            </div>
        </div>
        <style type="text/css">
            #ManagerGrid li {
                width: <?php echo $thumb_width; ?>px;
                height: <?php echo $thumb_height; ?>px;
            }
        </style>
        <?php
    }

    public function layout_help() {
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'help_general',
            'title' => __('General', 'photogallery'),
            'content' => '
<p>' . __('Here you can choose from a variety of album and gallery layouts.', 'photogallery') . '</p>
<p>' . __('On the left side you find layouts for albums, on the right for galleries.', 'photogallery') . '</p>'
        ));
        $screen->add_help_tab(array(
            'id' => 'help_custom',
            'title' => __('Custom Layout', 'photogallery'),
            'content' => '
<p>' . __('If you want to use your own custom layout just tick the checkbox below the header and follow the on screen instructions.', 'photogallery') . '</p>'
        ));
    }

    public function layout() {
        $type = get_option('photogallery_layout_type', 'standart');
        ?>
        <div class="wrap">
            <h2><?php
                echo self::get_title();
                ?></h2>
            <div id="layoutTabs">
                <span class="standart<?php echo ($type == 'standart' ? ' active' : ''); ?>" data-content="#layoutStandart"><?php _e('Standart Layouts', 'photogallery'); ?></span>
                <span class="custom<?php echo ($type == 'custom' ? ' active' : ''); ?>" data-content="#layoutCustom"><?php _e('Custom Layout', 'photogallery'); ?></span>
            </div>
            <div id="layoutContent">
                <div id="layoutStandart"<?php echo ($type == 'standart' ? 'class="active"' : ''); ?>>
                    standart
                </div>
                <div id="layoutCustom"<?php echo ($type == 'custom' ? 'class="active"' : ''); ?>>
                    custom
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_save_gallery() {
        $gallery_ID = self::ensure('id');
        if ($gallery_ID) {
            global $wpdb;
            list($gallery_old_name, $gallery_status) = $wpdb->get_row($wpdb->prepare("SELECT `post_name`, `post_status` FROM `" . $this->wp_posts . "` WHERE `ID` = %d AND `post_type` = 'photogallery'", $gallery_ID), ARRAY_N);
            if ($gallery_status) {
                $gallery_title = trim(self::ensure('title'));
                if ($gallery_title) {
                    if ($gallery_status == 'new' || !$gallery_old_name) {
                        $gallery_name = $this->get_unique_permalink(sanitize_title($gallery_title), $gallery_ID, 'photogallery');
                    } else {
                        $gallery_name = trim(self::ensure('name'));
                        if ($gallery_name) {
                            $gallery_name = sanitize_title($gallery_name);
                            if (!$gallery_name || !$this->is_unique_permalink($gallery_name, $gallery_ID, 'photogallery')) {
                                $gallery_name = $gallery_old_name;
                            }
                        } else {
                            $gallery_name = $gallery_old_name;
                        }
                    }
                    $GUID = get_permalink($gallery_ID);
                    $values = array();
                    $values['post_title'] = $gallery_title;
                    $values['post_name'] = $gallery_name;
                    $values['guid'] = $GUID ? $GUID : get_bloginfo('url') . '/?post_type=photogallery&p=' . $gallery_ID;
                    $values['post_modified'] = date("Y-m-d H:i:s");
                    $values['post_modified_gmt'] = gmdate("Y-m-d H:i:s");
                    if ($gallery_status == 'new') {
                        $values['post_author'] = get_current_user_id();
                        $values['post_status'] = 'draft';
                    }
                    $wpdb->update($this->wp_posts, $values, array('ID' => $gallery_ID), '%s', '%d');
                }
                update_post_meta($gallery_ID, '_photogallery_albums', self::ensure('albums'));
                $json = json_encode($gallery_name ? array('shortcode' => $gallery_name) : '1');
                if ($json) {
                    die($json);
                }
            }
        }
        die('0');
    }

    public function ajax_save_album() {
        $album_ID = self::ensure('id');
        if ($album_ID) {
            global $wpdb;
            list($album_old_name, $album_status) = $wpdb->get_row($wpdb->prepare("SELECT `post_name`, `post_status` FROM `" . $this->wp_posts . "` WHERE `ID` = %d AND `post_type` = 'photogallery_album'", $album_ID), ARRAY_N);
            if ($album_status) {
                $album_title = trim(self::ensure('title'));
                if ($album_title) {
                    if ($album_status == 'new' || !$album_old_name) {
                        $album_name = $this->get_unique_permalink(sanitize_title($album_title), $album_ID, 'photogallery_album');
                    } else {
                        $album_name = trim(self::ensure('name'));
                        if ($album_name) {
                            $album_name = sanitize_title($album_name);
                            if (!$album_name || !$this->is_unique_permalink($album_name, $album_ID, 'photogallery_album')) {
                                $album_name = $album_old_name;
                            }
                        } else {
                            $album_name = $album_old_name;
                        }
                    }
                    $GUID = get_permalink($album_ID);
                    $values = array();
                    $values['post_title'] = $album_title;
                    $values['post_name'] = $album_name;
                    $values['guid'] = $GUID ? $GUID : get_bloginfo('url') . '/?post_type=photoalbum&p=' . $album_ID;
                    $values['post_modified'] = date("Y-m-d H:i:s");
                    $values['post_modified_gmt'] = gmdate("Y-m-d H:i:s");
                    if ($album_status == 'new') {
                        $values['post_author'] = get_current_user_id();
                        $values['post_status'] = 'draft';
                    }
                    $wpdb->update($this->wp_posts, $values, array('ID' => $album_ID), '%s', '%d');
                }

                update_post_meta($album_ID, '_photoalbum_thumbnail', self::ensure('thumbnail'));
                update_post_meta($album_ID, '_photoalbum_images', self::ensure('images'));

                $json = json_encode($album_name ? array('shortcode' => $album_name) : '1');
                if ($json) {
                    die($json);
                }
            }
        }
        die('0');
    }

    public function ajax_publish_gallery() {
        $gallery_ID = self::ensure('id');
        if ($gallery_ID) {
            global $wpdb;
            $gallery_name = $wpdb->get_var($wpdb->prepare("SELECT `post_name` FROM `" . $this->wp_posts . "` WHERE `ID` = %d", $gallery_ID));
            $status = self::ensure('status', array('draft', 'publish'), false);
            if ($gallery_name && $status) {
                if ($wpdb->update($this->wp_posts, array('post_status' => $status, 'post_date' => date("Y-m-d H:i:s"), 'post_date_gmt' => gmdate("Y-m-d H:i:s")), array('ID' => $gallery_ID), '%s', '%d')) {
                    die('1');
                }
            }
        }
        die('0');
    }

    public function ajax_publish_album() {
        $album_ID = self::ensure('id');
        if ($album_ID) {
            global $wpdb;
            $album_name = $wpdb->get_var($wpdb->prepare("SELECT `post_name` FROM `" . $this->wp_posts . "` WHERE `ID` = %d", $album_ID));
            $status = self::ensure('status', array('draft', 'publish'), false);
            if ($album_name && $status) {
                if ($wpdb->update($wpdb->prefix . 'posts', array('post_status' => $status, 'post_date' => date("Y-m-d H:i:s"), 'post_date_gmt' => gmdate("Y-m-d H:i:s")), array('ID' => $album_ID), '%s', '%d')) {
                    die('1');
                }
            }
        }
        die('0');
    }

    public function ajax_load_albums() {
        $feedback = array();
        global $wpdb;
        $albums = $wpdb->get_results("SELECT `ID`, `post_title`, `post_name` FROM `" . $this->wp_posts . "` WHERE `post_type` = 'photogallery_album' AND `post_status` = 'publish'", ARRAY_A);
        foreach ($albums as $album) {
            $thumbnail_URL = '';
            $thumbnail_ID = get_post_meta($album['ID'], '_photoalbum_thumbnail', true);
            if ($thumbnail_ID) {
                $thumbnail_URL = wp_get_attachment_thumb_url($thumbnail_ID);
                if (!$thumbnail_URL) {
                    $thumbnail_URL = wp_get_attachment_url($thumbnail_ID);
                }
            }
            $feedback[] = array(
                'id' => $album['ID'],
                'title' => $album['post_title'],
                'url' => $thumbnail_URL
            );
        }
        $json = json_encode($feedback);
        if ($json) {
            die($json);
        }
        die('0');
    }

    public function ajax_save_layout_type(){
        $type = self::ensure('type', array('standart', 'custom'), 'standart');
        return update_option('photogallery_layout_type', $type) ? '1' : '0';
    }

    static function get_title() {
        $page = self::ensure('page');
        $action = self::ensure('action');
        switch ($page) {
            case 'photogallery':
                return $action == 'edit' ? __('Edit Gallery', 'photogallery') : __('Photogalleries', 'photogallery');
            case 'photogallery_album_list':
                return $action == 'edit' ? __('Edit Album', 'photogallery') : __('Photoalbums', 'photogallery');
            case 'photogallery_gallery_manager':
                return __('New Gallery', 'photogallery');
            case 'photogallery_gallery_manager':
                return __('New Album', 'photogallery');
            case 'photogallery_layout':
                return __('Gallery Layout', 'photogallery');
        }
    }

    static function _e_new_item() {
        esc_html_e('New', 'photogallery');
    }

    public function is_unique_permalink($name, $id, $type) {
        global $wpdb;
        return !$wpdb->query($wpdb->prepare("SELECT `ID` FROM `" . $this->wp_posts . "` WHERE `post_type` = '%s' AND `ID` != %d AND `post_name` = %s", $type, $id, $name));
    }

    public function get_unique_permalink($name, $id, $type) {
        $trimmed = substr($name, 0, 198);
        $copy = $trimmed;
        $i = 1;
        $unique = false;
        while (!$unique && strlen($copy) < 200) {
            if ($this->is_unique_permalink($copy, $id, $type)) {
                $unique = true;
            } else {
                $copy = $trimmed . $i++;
            }
        }
        return $copy;
    }

    static function ensure($key, $expect = null, $default = null) {
        $value = key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
        if ($expect === null) {
            return $value;
        }
        if (!is_array($expect)) {
            $expect = array($expect);
        }
        return in_array($value, $expect) ? $value : $default;
    }

}

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Photogallery_Gallery_List extends WP_List_Table {

    function __construct() {
        parent::__construct(array(
            'singular' => 'gallery',
            'plural' => 'galleries',
            'ajax' => true
        ));
    }

    function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'photogallery'),
            'count' => __('Albums', 'photogallery'),
            'author' => __('Author', 'photogallery'),
            'date' => __('Date', 'photogallery')
        );
    }

    function get_sortable_columns() {
        return array(
            'name' => array('title', true),
            'author' => array('author', false),
            'date' => array('date', false)
        );
    }

    function get_bulk_actions() {
        switch (Photogallery::ensure('status')) {
            case 'trash':
                return array(
                    'restore' => __('Restore', 'photogallery'),
                    'purge' => __('Delete Permanently', 'photogallery')
                );
            default:
                return array(
                    'delete' => __('Delete', 'photogallery')
                );
        }
    }

    function column_default($item, $name) {
        return $item[$name];
    }

    function column_cb($gallery) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s"', $this->_args['plural'], $gallery['ID']);
    }

    function column_name($gallery) {
        if (empty($gallery['post_title'])) {
            $gallery_title = '<i>' . __('Unnamed Gallery', 'photogallery') . '</i>';
        } else {
            $search_term = Photogallery::ensure('s');
            if (empty($search_term)) {
                $gallery_title = $gallery['post_title'];
            } else {
                $gallery_title = str_replace($search_term, '<mark>' . $search_term . '</mark>', $gallery['post_title']);
            }
        }
        $status = Photogallery::ensure('status', array('trash', 'draft', 'publish'), 'all');
        $gallery_ID = $gallery['ID'];
        $editURL = "<a href=\"?page=photogallery&action=edit&gallery_id=$gallery_ID\"";
        if ($status == 'trash') {
            $actions = array(
                'restore' => "<a href=\"?page=photogallery&status=$status&action=restore&gallery_id=$gallery_ID\">" . __('Restore', 'photogallery') . "</a>",
                'delete' => "<a href=\"?page=photogallery&status=$status&action=purge&gallery_id=$gallery_ID\">" . __('Delete Permanently', 'photogallery') . "</a>"
            );
        } else {
            $actions = array(
                'edit' => $editURL . '>' . __('Edit', 'photogallery') . '</a>',
                'delete' => "<a href=\"?page=photogallery&status=$status&action=delete&gallery_id=$gallery_ID\">" . __('Trash', 'photogallery') . "</a>"
            );
        }
        return '<strong>' . $editURL . ' title="' . esc_attr(sprintf(__('Edit %s', 'photogallery'), $gallery_title)) . '">' . esc_html($gallery_title) . '</a>' . ($status == 'all' && $gallery['post_status'] == 'draft' ? '&ensp;&ndash;&ensp;' . __('Draft', 'photogallery') : '') . '</strong> ' . $this->row_actions($actions);
    }

    function column_count($gallery) {
        global $wpdb;
        $albums = get_post_meta($gallery['ID'], '_photogallery_albums', true);
        $album_count = 0;
        if ($albums) {
            $album_IDs = explode(',', $albums);
            foreach ($album_IDs as $album_ID) {
                if ($wpdb->get_var($wpdb->prepare("SELECT `ID` FROM `" . $wpdb->prefix . 'posts` WHERE `ID` = %d', $album_ID))) {
                    $album_count++;
                }
            }
        }
        return $album_count;
    }

    function column_author() {
        $author = new WP_User(get_current_user_id());
        return $author->display_name;
    }

    function column_date($gallery) {
        switch ($gallery['post_status']) {
            case 'draft':
                $time_raw = $gallery['post_modified'];
                $no_time = __('Never modified', 'photogallery');
                $has_time = __('Modified', 'photogallery');
                break;
            default:
                $time_raw = $gallery['post_date'];
                $no_time = __('Unpublished', 'photogallery');
                $has_time = __('Published', 'photogallery');
        }
        if ('0000-00-00 00:00:00' == $time_raw) {
            return $no_time;
        } else {
            $time = strtotime($time_raw);
            $time_title = date(__('Y/m/d g:i:s A', 'photogallery'), $time);

            $time_diff = time() - $time;

            if ($time_diff > 0 && $time_diff < DAY_IN_SECONDS) {
                $time_html = sprintf(__('%s ago', 'photogallery'), human_time_diff($time));
            } else {
                $time_html = mysql2date(__('Y/m/d', 'photogallery'), $time_raw);
            }
            return '<abbr title="' . esc_attr($time_title) . '">' . $time_html . '</abbr><br>' . $has_time;
        }
    }

    function no_items() {
        switch (Photogallery::ensure('status')) {
            case 'publish':
                echo __('No published galleries found', 'photogallery');
                break;
            case 'draft':
                echo __('No gallery drafts found', 'photogallery');
                break;
            case 'trash':
                echo __('No galleries in trash found', 'photogallery');
                break;
            default:
                echo __('No galleries found', 'photogallery');
        }
    }

    function prepare_items() {
        global $wpdb;

        $order = Photogallery::ensure('orderby', array('title', 'name', 'date'), 'name');
        $dir = Photogallery::ensure('order', array('asc', 'desc'), 'desc') == 'asc' ? 'DESC' : 'ASC';
        $status = Photogallery::ensure('status', array('trash', 'draft', 'publish'), 'all');
        if ($status == 'all') {
            $status = "(`post_status` = 'publish' OR `post_status` = 'draft')";
        } else {
            $status = "`post_status` = '$status'";
        }

        $per_page = $this->get_items_per_page('galleries_per_page', 10);
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $this->_column_headers = $this->get_column_info();
        $search_term = Photogallery::ensure('s');
        if (!empty($search_term)) {
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type`='photogallery' AND $status AND `post_title` LIKE '%%%s%%' ORDER BY `post_$order` $dir", $search_term), ARRAY_A);
            $total_items = count($result);
            $this->items = array_slice($result, $offset, $per_page);
        } else {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'photogallery' AND $status");
            $this->items = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'photogallery' AND $status ORDER BY `post_$order` $dir LIMIT $offset, $per_page", ARRAY_A);
        }
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

}

class Photogallery_Album_List extends WP_List_Table {

    function __construct() {
        parent::__construct(array(
            'singular' => 'album',
            'plural' => 'albums',
            'ajax' => true
        ));
    }

    function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'front' => '',
            'name' => __('Name', 'photogallery'),
            'count' => __('Photos', 'photogallery'),
            'author' => __('Author', 'photogallery'),
            'date' => __('Date', 'photogallery')
        );
    }

    function get_sortable_columns() {
        return array(
            'name' => array('title', true),
            'author' => array('author', false),
            'date' => array('date', false)
        );
    }

    function get_bulk_actions() {
        switch (Photogallery::ensure('status')) {
            case 'trash':
                return array(
                    'restore' => __('Restore', 'photogallery'),
                    'purge' => __('Delete Permanently', 'photogallery')
                );
            default:
                return array(
                    'delete' => __('Delete', 'photogallery')
                );
        }
    }

    function column_cb($album) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s"', $this->_args['plural'], $album['ID']);
    }

    function column_front($album) {
        $album_ID = $album['ID'];
        $album_edit_URL = '<a href="?page=photogallery_album_list&action=edit&album_id=' . $album_ID . '" title="' . esc_attr(sprintf(__('Edit %s', 'photogallery'), $album['post_title'])) . '">';
        $thumbnail_ID = get_post_meta($album_ID, '_photoalbum_thumbnail', true);
        if ($thumbnail_ID) {
            $thumbnail_URL = wp_get_attachment_thumb_url($thumbnail_ID);
            if (!$thumbnail_URL) {
                $thumbnail_URL = wp_get_attachment_url($album_ID);
            }
            if ($thumbnail_URL) {
                $album_edit_URL .= '<img src="' . $thumbnail_URL . '" alt="" />';
            }
        }
        return $album_edit_URL . '</a>';
    }

    function column_default($item, $name) {
        return $item[$name];
    }

    function column_name($album) {
        if (empty($album['post_title'])) {
            $album_title = '<i>' . __('Unnamed Album', 'photogallery') . '</i>';
        } else {
            $search_term = Photogallery::ensure('s');
            if (empty($search_term)) {
                $album_title = $album['post_title'];
            } else {
                $album_title = str_replace($search_term, '<mark>' . $search_term . '</mark>', $album['post_title']);
            }
        }
        $status = Photogallery::ensure('status', array('trash', 'draft', 'publish'), 'all');
        $album_ID = $album['ID'];
        $album_edit_URL = "<a href=\"?page=photogallery_album_list&action=edit&album_id=$album_ID\"";
        if ($status == 'trash') {
            $actions = array(
                'restore' => "<a href=\"?page=photogallery_album_list&status=$status&action=restore&album_id=$album_ID\">" . __('Restore', 'photogallery') . "</a>",
                'delete' => "<a href=\"?page=photogallery_album_list&status=$status&action=purge&album_id=$album_ID\">" . __('Delete Permanently', 'photogallery') . "</a>"
            );
        } else {
            $actions = array(
                'edit' => $album_edit_URL . '>' . __('Edit', 'photogallery') . '</a>',
                'delete' => "<a href=\"?page=photogallery_album_list&status=$status&action=delete&album_id=$album_ID\">" . __('Trash', 'photogallery') . "</a>"
            );
        }
        return '<strong>' . $album_edit_URL . ' title="' . esc_attr(sprintf(__('Edit %s', 'photogallery'), $album_title)) . '">' . esc_html($album_title) . '</a>' . ($status == 'all' && $album['post_status'] == 'draft' ? '&ensp;&ndash;&ensp;' . __('Draft', 'photogallery') : '') . '</strong> ' . $this->row_actions($actions);
    }

    function column_count($album) {
        global $wpdb;
        $image_count = 0;
        $image_IDs = explode(',', get_post_meta($album['ID'], '_photoalbum_images', true));
        foreach ($image_IDs as $image_ID) {
            if ($wpdb->get_var($wpdb->prepare("SELECT `ID` FROM `" . $wpdb->prefix . 'posts` WHERE `ID` = %d', $image_ID))) {
                $image_count++;
            }
        }
        return $image_count;
    }

    function column_author() {
        $author = new WP_User(get_current_user_id());
        return $author->display_name;
    }

    function column_date($album) {
        switch ($album['post_status']) {
            case 'draft':
                $time_raw = $album['post_modified'];
                $no_time = __('Never modified', 'photogallery');
                $has_time = __('Modified', 'photogallery');
                break;
            default:
                $time_raw = $album['post_date'];
                $no_time = __('Unpublished', 'photogallery');
                $has_time = __('Published', 'photogallery');
        }
        if ('0000-00-00 00:00:00' == $time_raw) {
            $time_title = $time_html = $no_time;
        } else {
            $time = strtotime($time_raw);
            $time_title = date(__('Y/m/d g:i:s A', 'photogallery'), $time);

            $time_diff = time() - $time;

            if ($time_diff > 0 && $time_diff < DAY_IN_SECONDS) {
                $time_html = sprintf(__('%s ago', 'photogallery'), human_time_diff($time));
            } else {
                $time_html = mysql2date(__('Y/m/d', 'photogallery'), $time_raw);
            }
            $time_html .='<br>' . $has_time;
        }
        return '<abbr title="' . esc_attr($time_title) . '">' . $time_html . '</abbr>';
    }

    function no_items() {
        switch (Photogallery::ensure('status')) {
            case 'publish':
                echo __('No published albums found', 'photogallery');
                break;
            case 'draft':
                echo __('No album drafts found', 'photogallery');
                break;
            case 'trash':
                echo __('No albums in trash found', 'photogallery');
                break;
            default:
                echo __('No albums found', 'photogallery');
        }
    }

    function prepare_items() {
        global $wpdb;

        $order = Photogallery::ensure('orderby', array('title', 'name', 'date'), 'name');
        $dir = Photogallery::ensure('order', array('asc', 'desc'), 'desc') == 'asc' ? 'DESC' : 'ASC';
        $status = Photogallery::ensure('status', array('trash', 'draft', 'publish'), 'all');
        if ($status == 'all') {
            $status = "(`post_status` = 'publish' OR `post_status` = 'draft')";
        } else {
            $status = "`post_status` = '$status'";
        }

        $per_page = $this->get_items_per_page('galleries_per_page', 10);
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $this->_column_headers = $this->get_column_info();
        $search_term = Photogallery::ensure('s');
        if (!empty($search_term)) {
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type`='photogallery_album' AND $status AND `post_title` LIKE '%%%s%%' ORDER BY `post_$order` $dir", $search_term), ARRAY_A);
            $total_items = count($result);
            $this->items = array_slice($result, $offset, $per_page);
        } else {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'photogallery_album' AND $status");
            $this->items = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'photogallery_album' AND $status ORDER BY `post_$order` $dir LIMIT $offset, $per_page", ARRAY_A);
        }
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

}

new Photogallery();

/**
 * Fetches a Photogallery with all Albums IDs attached to it.
 * @global object $wpdb global WordPress database wrapper.
 * @global string $wpdb->prefix database prefix as defined in wp-config.php:61.
 * @global object $wp_query global WordPress query object.
 * @param integer $ID [optional] ID of the gallery to be loaded. Defaults to $wp_query->post->ID which is the current post/gallery ID if used on the frontend of WordPress.
 * @return boolean|object Returns an object on success, otherwise <b>false</b>. Use <b>print_var</b> for further details.
 */
function get_photogallery($ID = null) {
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
 * @global object $wpdb global WordPress database wrapper.
 * @global object $wp_query global WordPress query object.
 * @param type $ID [optional] ID of the album to be loaded. Defaults to $wp_query->post->ID which is the current post/album ID if used on the frontend of WordPress.
 * @return boolean Returns an object on success, otherwise <b>false</b>. Use <b>print_var</b> for further details.
 */
function get_photoalbum($ID = null) {
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

<?php
# Photogallery

get_header();
?>

<div id="content" class="site-content">
    <?php
    $gallery = get_photogallery($post->ID);
    foreach ($gallery->albums as $albumID) {
        $album = get_photoalbum($albumID);
        $upload_dir = wp_upload_dir();
        $thumbnail = '';
        if ($album->thumbnail) {
            if (key_exists('sizes', $album->thumbnail) && key_exists('thumbnail', $album->thumbnail['sizes'])) {
                $thumbnail = $upload_dir['baseurl'] . '/' . dirname($album->thumbnail['file']) . '/' . $album->thumbnail['sizes']['thumbnail']['file'];
            } else {
                $thumbnail = $upload_dir['baseurl'] . '/' . $album->thumbnail['file'];
            }
        }
        ?>
        <section class="album">
            <a href="<?php echo get_the_permalink($albumID); ?>">
                <img src="<?php echo $thumbnail; ?>" alt="<?php echo $album->title; ?>"/>
                <span><?php echo $album->title; ?></span>
            </a>
        </section>
        <?php
    }
    ?>
</div>
<?php
get_sidebar('content');
get_sidebar();
get_footer();

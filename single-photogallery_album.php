<?php
#Photoalbum

get_header();
?>

<div id="content" class="site-content">
    <section class="gallery">
        <?php
        $album = get_photoalbum($post->ID);
        if ($album) {
            $upload_dir = wp_upload_dir();
            foreach ($album->images as $image) {
                ?>
        <img src="<?php echo $upload_dir['baseurl'] . '/' . $image['file']; ?>" alt="<?php echo $image['file']; ?>" />
                <?php
            }
        }
        ?>
    </section>
</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
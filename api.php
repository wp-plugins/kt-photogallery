<?php
if (!defined('ABSPATH')) {
    die('Cheatin\', eh?');
}
?>
<div id="layoutCustom" <?php echo $show ? ' class="active"' : ''; ?>>
    <nav>
        <a href="#overview">Overview</a>
        <a href="#references">References</a>
        <a href="#php-api">PHP API</a>
        <a href="#default-templates">Default Templates</a>
        <a href="#custom-template">Custom Template</a>
    </nav>
    <h3 id="overview">Overview</h3>
    <ol>
        <li>
            <p>Create two files named <code>single-photogallery.php</code> and <code>single-photogallery_album.php</code></p>
            <p><code>single-photogallery.php</code> is for rendering a gallery, e.g a list or grid of album thumbnails.</p>
            <p><code>single-photogallery_album.php</code> is for rendering an album, e.g a list or grid of image thumbnails.</p>
        </li>
        <li>
            <p>Code 'em cobba!</p>
        </li>
        <li>
            <p>The current theme in use is called "<em><?php echo wp_get_theme()->Name; ?></em>" so upload both files to:<br>
                <code><?php echo bloginfo('template_url'); ?>/</code></p>
        </li>
    </ol>
    <hr>
    <h3 id="references">References</h3>
    <p>
        <a href="http://codex.wordpress.org/Post_Type_Templates" target="_blank">WP Codex: Custom Post Type Templates</a><br>
        <a href="http://codex.wordpress.org/Function_Reference/#Theme-Related_Functions" target="_blank">WP Codex: Function Reference</a>
    </p>
    <hr>
    <h3 id="php-api">PHP API</h3>
    <p>This Plugin ships with some PHP stuff for your coding pleasure.<br>
        Unlike Wordpress' build in functions for printing stuff like e.g <code>get_the_title()</code> all functions offered by this plugin are bound to the PHP class <code>Photogallery</code> to keep PHP's global namespace clear and avoid collitions with other plugins who might add a global function by the same name as this plugin would do.</p>

    <h4 class="toggle">Photogallery::is_custom</h4>
    <div>
    <pre><sup>Photogallery</sup><b>::</b><sub>is_custom</sub>():<p>boolean</p></pre>
    <p>Checks if the user wishes to use a custom template. If so the method will return <code>true</code>, otherwise <code>false</code>.<br>
        For an example refere to the section <a href="#custom-template">Custom Template</a>.
    </p>
    </div>

    <h4 class="toggle">Photogallery::is_default</h4>
    <div>
    <pre><sup>Photogallery</sup><b>::</b><sub>is_default</sub>():<p>boolean</p></pre>
    <p>
        Checks if the user wishes to use a default template. If so the method will return <code>true</code>, otherwise <code>false</code>.<br>
        Use this method in combination with <code>Photogallery::print_default()</code>.<br>
        For examples refere to the section <a href="#custom-template">Custom Template</a>.
    </p>
    </div>

    <h4 class="toggle">Photogallery::print_default</h4>
    <div>
    <pre><sup>Photogallery</sup><b>::</b><sub>print_default</sub>(<p>int</p> <var>$ID</var>):<p>void</p></pre>
    <p>
        <i class="indent"></i><em>optional</em> <code>int $ID</code> ID of a Photogallery or Photoalbum. Defaults to <code>$post->ID</code>
    </p>
    <p>
        This method will print a Photogallery or Photoalbum with a default template chosen by the user regardless whether the user wishes to use default templates. There is no return value.
    </p>
    </div>

    <h4 class="toggle">Photogallery::print_default_gallery_template</h4>
    <div>
        <pre><sup>Photogallery</sup><b>::</b><sub>print_default_gallery_template</sub>(<p>int</p> <var>$ID</var>, <p>string</p> <var>$type</var>):<p>void</p></pre>
    <p>
        <i class="indent"></i><em>optional</em> <code>int $ID</code> ID of a Photogallery. Defaults to <code>$post->ID</code><br>
        <i class="indent"></i><em>optional</em> <code>string $type</code> Two possible values, <code>grid</code> and <code>list</code>. Defaults to the layout chosen via the dashboard
    </p>
    <p>
        This method will print a Photogallery with a default template chosen by the user regardless whether the user wishes to use default templates. There is no return value.
    </p>
    </div>

    <h4 class="toggle">Photogallery::print_default_album_template</h4>
    <div>
        <pre><sup>Photogallery</sup><b>::</b><sub>print_default_album_template</sub>(<p>int</p> <var>$ID</var>, <p>string</p> <var>$type</var>):<p>void</p></pre>
    <p>
        <i class="indent"></i><em>optional</em> <code>int $ID</code> ID of a Photoalbum. Defaults to <code>$post->ID</code><br>
        <i class="indent"></i><em>optional</em> <code>string $type</code> Four possible values, <code>single</code>, <code>slideshow</code>, <code>grid</code> and <code>list</code>. Defaults to the layout chosen via the dashboard
    </p>
    <p>
        This method will print a Photoalbum with a default template chosen by the user regardless whether the user wishes to use default templates. There is no return value.
    </p>
    </div>

    <h4 class="toggle">Photogallery::get_albums</h4>
    <div>
    <pre><sup>Photogallery</sup><b>::</b><sub>get_albums(<p>int</p> <var>$ID</var>):<p>mixed</p></sub></pre>
    <p>
        <i class="indent"></i><em>optional</em> <code>int $ID</code> ID of a Photogallery. Defaults to <code>$post->ID</code>
    </p>
    <p>
        Returns all Photoalbum IDs associated with a Photogallery as an array. If no albums are associated or no Photogallery exists for the given ID the method will return <code>false</code>.
    </p>
    </div>

    <h4 class="toggle">Photogallery::get_thumbnail</h4>
    <div>
    <pre><sup>Photogallery</sup><b>::</b><sub>get_thumbnail</sub>(<p>int</p> <var>$ID</var>):<p>mixed</p></pre>
    <p>
        <i class="indent"></i><em>optional</em> <code>int $ID</code> is an ID of a Photoalbum and will default to <code>$post->ID</code>
    </p>
    <p>
        Returns the ID of the image used as a thumbnail for a Photoalbum. If no thumbnail is set or no Photoalbum exists for the ID the method will return <code>false</code>.
    </p>
    </div>

    <h4 class="toggle">Photogallery::get_images</h4>
    <div>
    <pre><sup>Photogallery</sup><b>::</b><sub>get_images</sub>(<p>int</p> <var>$ID</var>):<p>mixed</p></pre>
    <p>
        <i class="indent"></i><em>optional</em> <code>int $ID</code> is an ID of a Photoalbum and will default to <code>$post->ID</code>
    </p>
    <p>
        Returns the IDs of all images associated with a Photoalbum as an array. If no images are associated or no Photoalbum exists for the ID the method will return <code>false</code>.
    </p>
    </div>

    <hr>
    <h3 id="default-templates">Default Templates</h3>
    <p>If a template prints a plugin's default templates you might want to know what HTML markup and CSS classes are generated.</p>
    <p>An album is always rendered as a preview thumbnail representing the entire album so user get an idea what to find inside. If no thumbnail is set for an album the first image found inside the album is chosen automatically as a thumbnail for the entire album. Its name and additional information might been shown as well.</p>
    <p>An image is always shown as its thumbnail representation - by default both 100 pixel width and height, but you can change these values via <a href="options-media.php">Settings &raquo; Media</a>. The albums' name can been shown as well as a <code>h1</code> element.</p>
    <p>CSS is kept at a minimum so it is easier for you to integrate it into your theme. Styling like borders, margins and paddings are up to you. For readability the CSS selectors are overly accurate, the actual selectors are more efficent.</p>

    <h4 class="toggle">Photogallery: Grid</h4>
    <div>
    <p><span class="layout-image grid"></span>
        This template arranges album thumbnails in a grid.<br>
        The wrapper <code>section</code> will adopt its parent's width and all album <code>a</code>s will be floated left and if clicked will lead to the album's permalink.<br>
        Optionally the album's name will been shown with an optional hover effect. The album's title will be generated by CSS as a pseudo element and uses the <code>a</code>'s <code>data-title</code> attribute.<br>
        The number of images per row depends on the thumbnail's and the wrapper's width.
        </p>
    <pre class="html">
<span><q>Wrapper</q></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery grid"</s>></p></span>
<span></span>
<span>    <q>Just Album Thumbnail</q></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/album/permalink"</s> <a>class</a>=<s>"album"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/thumbnail-image.jpg"</s> <a>alt</a> /></p></span>
<span>    <p>&lt;/a></p></span>
<span></span>
<span>    <q>Showing Album Names</q></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/album/permalink"</s> <a>data-title</a>=<s>"<strong>Album Title</strong>"</s> <a>class</a>=<s>"album"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/thumbnail-image.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span>
<span></span>
<span><q>Hover Effect for Album Titels</q></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery grid <strong>hover</strong>"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/album/permalink"</s> <a>data-title</a>=<s>"<strong>Album Title</strong>"</s> <a>class</a>=<s>"album"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/thumbnail-image.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span></pre>
    <pre class="css">
<span><a>.photogallery.grid</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>}</span>
<span></span>
<span><a>.photogallery.grid .album</a> {</span>
<span>    <p>position</p>: <u>relative</u>;</span>
<span>    <p>float</p>: <u>left</u>;</span>
<span>}</span>
<span><a>.photogallery.grid .album</a> <p>img</p> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>0</i>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>}</span>
<span><a>.photogallery.grid .album<p>::after</p></a> {</span>
<span>    <p>content</p>: <u>attr(<s>data-title</s>)</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>bottom</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(</u><i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i><u>)</u>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>    <p>text-overflow</p>: <u>ellipsis</u>;</span>
<span>    <p>white-space</p>: <u>nowrap</u>;</span>
<span>    <p>overflow</p>: <u>hidden</u>;</span>
<span>}</span>
<span><a>.photogallery.grid.hover .album<p>::after</p></a> {</span>
<span>    <p>display</p>: <u>none</u>;</span>
<span>}</span>
<span><a>.photogallery.grid.hover .album<p>:hover::after</p></a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span></pre>
    </div>

    <h4 class="toggle">Photogallery: List</h4>
    <div>
    <p><span class="layout-image list"></span>
        All albums are shown as a list with their name next to their thumbnail.<br>
        The albums's title is always shown and is generated by CSS as a pseudo element and uses the <code>a</code>'s <code>data-title</code> attribute.<br>
        The album contains a <code>a</code> element making the thumbnail clickable and uses the album's permalink.<br>
        Depending on the options ticked via the Dashboard additional information like date of creation, author name and image count will been shown as <code>span</code>s as well.</p>
    <pre class="html">
<span><q>Wrapper</q></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery list"</s>></p></span>
<span></span>
<span>    <q>Just Thumbnail and Album Name</q></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/album/permalink"</s> <a>data-title</a>=<s>"Image Title"</s> <a>class</a>=<s>"album"</s>></p></span>
<span>        <p>::before</p></span>
<span>        <p>&lt;img</p> <a>src</a>=<s>"http://url.to/thumbnail-image.jpg"</s> <p>/></p></span>
<span>    <p>&lt;/a></p></span>
<span></span>
<span>    <q>Thumbnail and all Additional Information</q></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/album/permalink"</s> <a>data-title</a>=<s>"Image Title"</s> <a>class</a>=<s>"album"</s>></p></span>
<span>        <p>::before</p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/thumbnail-image.jpg"</s> /></p></span>
<span>        <p>&lt;span <a>class</a>=<s>"count"</s>></p>123<p>&lt;/span></p></span>
<span>        <p>&lt;span <a>class</a>=<s>"author"</s>></p>Author Name<p>&lt;/span></p></span>
<span>        <p>&lt;span <a>class</a>=<s>"date"</s>></p>01.02.03<p>&lt;/span></p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span></pre>
    <pre class="css">
<span><a>.photogallery.list</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>}</span>
<span><a>.photogallery.list .album</a> {</span>
<span>    <p>position</p>: <u>relative</u>;</span>
<span>    <p>float</p>: <u>left</u>;</span>
<span>    <p>clear</p>: <u>both</u>;</span>
<span>}</span>
<span><a>.photogallery.list .album<p>::before</p></a> {</span>
<span>    <p>content</p>: <u>attr(<s>data-title</s>)</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>bottom</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(</u><i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i><u>)</u>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>    <p>text-overflow</p>: <u>ellipsis</u>;</span>
<span>    <p>white-space</p>: <u>nowrap</u>;</span>
<span>    <p>overflow</p>: <u>hidden</u>;</span>
<span>}</span>
<span><a>.photogallery.list .album .count</a> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>50%</i>;</span>
<span>    <p>margin-top</p>: <i>-10px</i>;</span>
<span>    <p>line-height</p>: <i>20px</i>;</span>
<span>}</span>
<span><a>.photogallery.list .album .author</a> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>50%</i>;</span>
<span>    <p>margin-top</p>: <i>-10px</i>;</span>
<span>    <p>line-height</p>: <i>20px</i>;</span>
<span>}</span>
<span><a>.photogallery.list .album .date</a> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>50%</i>;</span>
<span>    <p>margin-top</p>: <i>-10px</i>;</span>
<span>    <p>line-height</p>: <i>20px</i>;</span>
<span>}</span></pre>
    </div>

    <h4 class="toggle">Photoalbum: Single Image</h4>
    <div>
    <p><span class="layout-image single"></span>
        One image is shown in the middle.<br>
        If fullview is enabled via the dashboard if an image is clicked it will open its fullview version.<br>
        Optionally the image name will be shown as well with a optional hover effect.<br>
        Just as the photogallery's grid template this template uses a CSS pseudo element for showing the image's title.<br>
        Optionally navigation buttons are shown on both sides of the image so the user can navigate through the album.<br>
        All images are hidden by default and only the current image has a CSS class <code>current</code> for it to be visible.<br>
        <strong>Note:</strong> This template uses JavaScript/jQuery for navigation.
    </p>
    <pre class="html">
<span><p>&lt;section</p> <a>class</a>=<s>"photogallery single"</s><p>></p></span>
<span>    <p>&lt;h1></p>Album Title<p>&lt;/h1></p></span>
<span></span>
<span>    <q>Only Thumbnail</q></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>    <p>&lt;/a></p></span>
<span></span>
<span>    <q>Image Title</q></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span>
<span></span>
<span><q>Image Title Hover Effect</q></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery single <strong>hover</strong>"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span>
<span></span>
<span><q>Navigation Buttons</q></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery single"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>&lt;nav <a>class</a>=<s>"previous"</s>>&lt;/nav></p></span>
<span>        <p>&lt;nav <a>class</a>=<s>"next"</s>>&lt;/nav></p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span></pre>
    <pre class="css">
<span><a>.photogallery.single</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>}</span>
<span><a>.photogallery.single .image</a> {</span>
<span>    <p>display</p>: <u>none</u>;</span>
<span>    <p>position</p>: <u>relative</u>;</span>
<span>}</span>
<span><a>.photogallery.single .image.current</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span>
<span><a>.photogallery.single .image<p>::after</p></a> {</span>
<span>    <p>content</p>: <u>attr(<s>data-title</s>)</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>bottom</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(</u><i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i><u>)</u>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>    <p>text-overflow</p>: <u>ellipsis</u>;</span>
<span>    <p>white-space</p>: <u>nowrap</u>;</span>
<span>    <p>overflow</p>: <u>hidden</u>;</span>
<span>}</span>
<span><a>.photogallery.single.hover .image<p>::after</p></a> {</span>
<span>    <p>display</p>: <u>none</u>;</span>
<span>}</span>
<span><a>.photogallery.single.hover .image<p>:hover::after</p></a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span>
<span><a>.photogallery.single .image</a> <p>nav</p> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>25%</i>;</span>
<span>    <p>height</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(<i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i>)</u>;</span>
<span>}</span>
<span><a>.photogallery.single .image .previous</a> {</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span><a>.photogallery.single .image .next</a> {</span>
<span>    <p>right</p>: <i>0</i>;</span>
<span>}</span></pre>
    </div>

    <h4 class="toggle">Photoalbum: Slideshow</h4>
    <div>
    <p><span class="layout-image slideshow"></span>This template will arrange thumbnails in a slideshow.<br>
        One thumbnail is shown in the middle and on both sides thumbnails of the directly adjacted images.<br>
        If an adjacted thumbnail is clicked it is flicked to the middle.<br>
        Optionally the image name is shown with an optional hover effect.<br>
        By default all images are hidden and only the current image has the CSS class <code>current</code>, its previous image <code>previous</code> and its next image <code>next</code> for them to be visible.<br>
        <strong>Note:</strong> This template uses Javascript/jQuery for navigation.
    </p>
    <pre class="html">
<span><p>&lt;section <a>class</a>=<s>"photogallery slideshow"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>    <p>&lt;/div></p></span>
<span></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/div></p></span>
<span><p>&lt;/div></p></span>
<span></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery slideshow <strong>hover</strong>"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/div></p></span>
<span><p>&lt;/div></p></span></pre>
    <pre class="css">
<span><a>.photogallery.slideshow</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>}</span>
<span><a>.photogallery.slideshow .image</a> {</span>
<span>    <p>display</p>: <u>none</u>;</span>
<span>}</span>
<span><a>.photogallery.slideshow .previous</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span>
<span><a>.photogallery.slideshow .next</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span>
<span><a>.photogallery.slideshow .current</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span>
<span><a>.photogallery.slideshow .image<p>::after</p></a> {</span>
<span>    <p>content</p>: <u>attr(<s>data-title</s>)</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>bottom</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(</u><i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i><u>)</u>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>    <p>text-overflow</p>: <u>ellipsis</u>;</span>
<span>    <p>white-space</p>: <u>nowrap</u>;</span>
<span>    <p>overflow</p>: <u>hidden</u>;</span>
<span>}</span>
<span><a>.photogallery.slideshow.hover .image<p>::after</p></a> {</span>
<span>    <p>display</p>: <u>none</u>;</span>
<span><span>}</span>
<span><a>.photogallery.slideshow.hover .image<p>:hover::after</p></a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span></pre>
    </div>

    <h4 class="toggle">Photoalbum: Grid</h4>
    <div>
    <p><span class="layout-image grid"></span>
        All images are shown in a grid.<br>
        The wrapper <code>section</code> will adopt the parent's width and all image <code>a</code>s are floated left.<br>
        If fullview is enabled via the dashboard and an image is clicked it will open its fullview version.<br>
        Optionally the image name is shown with an optional hover effect. The album's title will be generated by CSS as a pseudo element and uses the <code>a</code>'s <code>data-title</code> attribute.<br>
        The number of images per row depends on the thumbnail's and the wrapper's width.
    </p>
    <pre class="html">
<span><p>&lt;section <a>class</a>=<s>"photogallery grid"</s>></p></span>
<span>    <p>&lt;h1></p>Album Title<p>&lt;/h1></p></span>
<span></span>
<span>    <p>&lt;a <a>href</a>=<s>"http:://url.to/image.jpg"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>    <p>&lt;/a></p></span>
<span></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span>
<span></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery grid <strong>hover</strong>"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span></pre>
    <pre class="css">
<span><a>.photogallery.grid</a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>}</span>
<span></span>
<span><a>.photogallery.grid .image</a> {</span>
<span>    <p>position</p>: <u>relative</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>float</p>: <u>left</u>;</span>
<span>}</span>
<span><a>.photogallery.grid .image</a> <p>img</p> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>0</i>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>}</span>
<span><a>.photogallery.grid .image<p>::after</p></a> {</span>
<span>    <p>content</p>: <u>attr(<s>data-title</s>)</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>bottom</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(</u><i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i><u>)</u>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>    <p>text-overflow</p>: <u>ellipsis</u>;</span>
<span>    <p>white-space</p>: <u>nowrap</u>;</span>
<span>    <p>overflow</p>: <u>hidden</u>;</span>
<span>}</span>
<span><a>.photogallery.grid.hover .image<p>::after</p></a> {</span>
<span>    <p>display</p>: <u>none</u>;</span>
<span>}</span>
<span><a>.photogallery.grid.hover .image<p>:hover::after</p></a> {</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>}</span></pre>
    </div>

    <h4 class="toggle">Photoalbum: List</h4>
    <div>
    <p><span class="layout-image list"></span>
        All Images shown as a list with their name next to their thumbnail.<br>
        The image's title is always shown and is generated by CSS as a pseudo element and uses the <code>a</code>'s <code>data-title</code> attribute.<br>
        Depending on the options ticked via the dashboard additional information like date of creation and author name will been shown as well.
    </p>
    <pre class="html">
<span><q>Wrapper</q></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery list"</s>></p></span>
<span>    <p>&lt;h1></p>Album Title<p>&lt;/h1></p></span>
<span></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"Image Title"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>::before</p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>    <p>&lt;/a></p></span>
<span></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"Image Title"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>::before</p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image-thumbnail.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>&lt;span <a>class</a>=<s>"author"</s>></p>Image Author<p>&lt;/span></p></span>
<span>        <p>&lt;span <a>class</a>=<s>"date"</s>></p>01.02.03<p>&lt;/span></p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span></pre>
    <pre class="css">
<span><a>.photogallery.list</a> {</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>}</span>
<span><a>.photogallery.list .image</a> {</span>
<span>    <p>position</p>: <u>relative</u>;</span>
<span>    <p>margin</p>: <i>5px 0</i>;</span>
<span>}</span>
<span><a>.photogallery.list .image<p>::before</p></a> {</span>
<span>    <p>content</p>: <u>attr(<s>data-title</s>)</u>;</span>
<span>    <p>display</p>: <u>inline-block</u>;</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>bottom</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(</u><i>255</i>, <i>255</i>, <i>255</i>, <i>.5</i><u>)</u>;</span>
<span>    <p>text-align</p>: <u>center</u>;</span>
<span>    <p>text-overflow</p>: <u>ellipsis</u>;</span>
<span>    <p>white-space</p>: <u>nowrap</u>;</span>
<span>    <p>overflow</p>: <u>hidden</u>;</span>
<span>}</span>
<span><a>.photogallery.list .image .count</a> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>50%</i>;</span>
<span>    <p>margin-top</p>: <i>-10px</i>;</span>
<span>    <p>line-height</p>: <i>20px</i>;</span>
<span>}</span>
<span><a>.photogallery.list .image .author</a> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>50%</i>;</span>
<span>    <p>margin-top</p>: <i>-10px</i>;</span>
<span>    <p>line-height</p>: <i>20px</i>;</span>
<span>}</span>
<span><a>.photogallery.list .image .date</a> {</span>
<span>    <p>position</p>: <u>absolute</u>;</span>
<span>    <p>top</p>: <i>50%</i>;</span>
<span>    <p>margin-top</p>: <i>-10px</i>;</span>
<span>    <p>line-height</p>: <i>20px</i>;</span>
<span>}</span></pre>
    </div>

    <h4 class="toggle">Fullview</h4>
    <div>
    <p>If a template is used to show a fullview the wrapper <code>section</code> will have an additional CSS class <code>fullview</code>.</p>
    <pre class="html">
<span><p>&lt;section <a>class</a>=<s>"photogallery fullview"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image.jpg"</s> <a>alt</a> /></p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span>
<span></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery fullview"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>data-title</a>=<s>"<strong>Image Title</strong>"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>::after</p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span>
<span></span>
<span><p>&lt;section <a>class</a>=<s>"photogallery fullview"</s>></p></span>
<span>    <p>&lt;a <a>href</a>=<s>"http://url.to/image.jpg"</s> <a>class</a>=<s>"image"</s>></p></span>
<span>        <p>&lt;img <a>src</a>=<s>"http://url.to/image.jpg"</s> <a>alt</a> /></p></span>
<span>        <p>&lt;nav <a>class</a>=<s>"previous"</s>>&lt;/nav></p></span>
<span>        <p>&lt;nav <a>class</a>=<s>"next"</s>>&lt;/nav></p></span>
<span>    <p>&lt;/a></p></span>
<span><p>&lt;/section></p></span></pre>
    <pre class="css">
<span><a>.photogallery.fullview</a> {</span>
<span>    <p>display</p>: <u>block</u>;</span>
<span>    <p>position</p>: <u>fixed</u>;</span>
<span>    <p>left</p>: <i>0</i>;</span>
<span>    <p>right</p>: <i>0</i>;</span>
<span>    <p>width</p>: <i>100%</i>;</span>
<span>    <p>height</p>: <i>100%</i>;</span>
<span>    <p>background-color</p>: <u>rgba(<i>0</i>, <i>0</i>, <i>0</i>, <i>.5</i>)</u>;</span>
<span>}</span>
<span><a>.photogallery.fullview .image</a> {</span>
<span></span>
<span>}</span>
<span><a>.photogallery.fullview img</a> {</span>
<span></span>
<span>}</span></pre>
    </div>

    <hr>
    <h3 id="custom-template">Custom Template</h3>
    <p>A good idea is to start out with a theme's <code>single.php</code> template, or if it has none <code>index.php</code> will do as well. Make a copy of the template and rename it to <code>single-photogallery.php</code> or <code>single-photogallery_album.php</code> respectively. Usually a theme's template contains the famous <a href="http://codex.wordpress.org/The_Loop#Using_the_Loop">WordPress Loop</a>. Everything around it is most likely HTML markup and WordPress functions like <code>get_header()</code>, <code>get_sidebar()</code> or <code>get_footer()</code>. The idea is to replace the Loop with your code for displaying a Photogallery or Photoalbum, yet keeping the surrounding markup and function calls.</p>
    <h4 class="toggle">Basic Example</h4>
    <div>
    <pre class="php">
<span><q># ... surrounding HTML markup</q></span>
<span>&lt;?php <sup>Photogallery</sup>::<sub>print_default</sub>(); ?&gt;</span>
<span><q># surrounding HTML markup goes on ...</q></span></pre>
    </div>

    <h4 class="toggle">Advanced Example</h4>
    <div>
    <p>The following example shows an advanced example for <code>single-photogallery.php</code>. It checks if the the user wishes to use the plugin's default templates and if so displays them. Otherwise it prints out a custom template.</p>
    <pre class="php">
<span><q># ... surrounding HTML markup</q></span>
<span>&lt;?php</span>
<span><b>if</b> (<sup>Photogallery</sup><b>::</b><sub>is_default</sub>()) {</span>
<span>    <q># print default template</q></span>
<span>    <sup>Photogallery</sup><b>::</b><sub>print_default</sub>();</span>
<span>} <b>else</b> {</span>
<span>    <q># print custom template</q></span>
<span>    <var>$album_IDs</var> = <sup>Photogallery</sup><b>::</b><sub>get_albums</sub>();</span>
<span>    ?&gt;</span>
<span><p>&lt;section</p> <a>class</a>=<s>"photogallery custom"</s><p>></p></span>
<span>    &lt;?php</span>
<span>    <b>foreach</b> (<var>$album_IDs</var> <b>as</b> <var>$album_ID</var>) {</span>
<span>        <var>$thumbnail_ID</var> = <sup>Photogallery</sup><b>::</b><sub>get_thumbnail</sub>(<var>$album_ID</var>);</span>
<span>        <var>$thumbnail_URL</var> = <sub>wp_get_attachment_thumb_url</sub>(<var>$thumbnail_ID</var>);</span>
<span>        <var>$album_permalink</var> = <sub>get_permalink</sub>(<var>$album_ID</var>);</span>
<span>        <var>$album_title</var> = <sub>get_the_title</sub>(<var>$album_ID</var>);</span>
<span>        ?&gt;</span>
<span>    <p>&lt;a</p> <a>href</a>=<s>"</s>&lt;?php echo <var>$album_permalink</var>; ?&gt;<s>"</s> <a>data-title</a>=<s>"</s>&lt;?php echo <var>$album_title</var>; ?&gt;<s>"</s> <a>class</a>=<s>"album"</s><p>></p></span>
<span>        <p>&lt;img</p> <a>src</a>=<s>"</s>&lt;?php echo <var>$thubmnail_URL</var>; ?&gt;<s>"</s> <a>alt</a> <p>/></p></span>
<span>    <p>&lt;/a></p></span>
<span>        &lt;?php</span>
<span>    }</span>
<span>    ?&gt;</span>
<span><p>&lt;/section></p></span>
<span>    &lt;?php</span>
<span>}</span>
<span>?&gt;</span>
<span><q># surrounding HTML markup goes on ...</q></span></pre>

    <p>And the following example shows how to use a custom template for showing images from an album. That code would go into <code>single-photogallery_album.php</code>.</p>
    <pre class="php">
<span><q># ... surrounding HTML markup</q></span>
<span>&lt;?php</span>
<span><b>if</b> (<sup>Photogallery</sup><b>::</b><sub>is_default</sub>()) {</span>
<span>    <q># print default template</q></span>
<span>    <sup>Photogallery</sup><b>::</b><sub>print_default</sub>();</span>
<span>} <b>else</b> {</span>
<span>    <q># print custom template</q></span>
<span>    <var>$image_IDs</var> = <sup>Photogallery</sup><b>::</b><sub>get_images</sub>();</span>
<span>    ?&gt;</span>
<span><p>&lt;section <a>class</a>=<s>"photogallery custom"</s>></p></span>
<span>    &lt;?php</span>
<span>    <b>foreach</b> (<var>$image_IDs</var> <b>as</b> <var>$image_ID</var>) {</span>
<span>        <var>$thumbnail_ID</var> = <sup>Photogallery</sup><b>::</b><sub>get_thumbnail</sub>(<var>$image_ID</var>);</span>
<span>        <var>$thumbnail_URL</var> = <sub>wp_get_attachment_thumb_url</sub>(<var>$thumbnail_ID</var>);</span>
<span>        <var>$image_URL</var> = <sub>wp_get_attachment_url</sub>(<var>$image_ID</var>);</span>
<span>        <var>$image_title</var> = <sub>get_the_title</sub>(<var>$image_ID</var>);</span>
<span>        ?&gt;</span>
<span>    <p>&lt;a</p> <a>href</a>=<s>"</s>&lt;?php echo <var>$image_URL</var>; ?&gt;<s>"</s> <a>data-title</a>=<s>"</s>&lt;?php echo <var>$image_title</var>; ?&gt;<s>"</s> <a>class</a>=<s>"image"</s><p>></p></span>
<span>        <p>&lt;img</p> <a>src</a>=<s>"</s>&lt;?php echo <var>$thumbnail_URL</var>; ?&gt;<s>"</s> <a>alt</a> <p>/></p></span>
<span>    <p>&lt;/a></p></span>
<span>        &lt;?php</span>
<span>    }</span>
<span>    ?&gt;</span>
<span><p>&lt;/section></p></span>
<span>    &lt;?php</span>
<span>}</span>
<span>?&gt;</span>
<span><q># surrounding HTML markup goes on ...</q></span></pre>
    </div>
</div>
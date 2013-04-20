<?php
/*
Plugin Name: JM Twitter Cards
Plugin URI: http://tweetpress.fr
Description: Meant to help users to implement and customize Twitter Cards easily
Author: Julien Maury
Author URI: http://tweetpress.fr
Version: 3.1.1
License: GPL2++
*/

/*
*    Sources: - https://dev.twitter.com/docs/cards
* 			  - http://codex.wordpress.org/Function_Reference/wp_enqueue_style
*             - I decided to remove former sources because I've been enhanced them by far and above all these sources are wrong : get_the_excerpt() outside the loop or undefined var !)
*			  - http://wptheming.com/2011/08/admin-notices-in-wordpress/
*		      - https://plus.gotwitterle.com/u/0/110977198681221304891/posts/axa3UaVF8x2
*			  - http://stackoverflow.com/questions/13677265/wordpress-how-to-get-the-second-article-attached-images
*			  - https://github.com/rilwis/meta-box
*			  - http://codex.wordpress.org/Function_Reference/wp_get_attachment_image_src
*/


// Some constants
define ('JM_TC_VERSION','3.1.1');


// Plugin activation: create default values if they don't exist
register_activation_hook( __FILE__, 'jm_tc_init' );
function jm_tc_init() {
$opts = get_option( 'jm_tc' );
if ( !is_array($opts) )
update_option('jm_tc', jm_tc_get_default_options());
}


// Plugin uninstall: delete option
register_uninstall_hook( __FILE__, 'jm_tc_uninstall' );
function jm_tc_uninstall() {
delete_option( 'jm_tc' );
}


// grab our datas
$opts = jm_tc_get_options();          

if($opts['twitterCardCustom'] == 'yes') {	 
add_action( 'add_meta_boxes', 'jm_tc_meta_box_add' );
function jm_tc_meta_box_add()
{
$post_type = get_post_type();// add support for CPT
add_meta_box( 'jm_tc-meta-box-id', 'Twitter Cards', 'jm_tc_meta_box_cb', $post_type, 'side', 'high' );
}

function jm_tc_meta_box_cb( $post )
{
$values = get_post_custom( $post->ID );
$selected = isset( $values['twitterCardType'] ) ? esc_attr( $values['twitterCardType'][0] ) : '';
wp_nonce_field( 'jm_tc_meta_box_nonce', 'meta_box_nonce' );
?>

<p>
<label for="twitterCardType"><?php _e('Choose what type of card you want to use', 'jm-tc'); ?></label>
<select name="twitterCardType" id="twitterCardType">
<option value="summary" <?php selected( $selected, 'summary' ); ?>><?php _e('summary', 'jm-tc'); ?></option>
<option value="summary_large_image" <?php selected( $selected, 'summary_large_image' ); ?>><?php _e('summary_large_image', 'jm-tc'); ?></option>
<option value="photo" <?php selected( $selected, 'photo' ); ?>><?php _e('photo', 'jm-tc'); ?></option>
<option value="gallery" <?php selected( $selected, 'gallery' ); ?>><?php _e('gallery', 'jm-tc'); ?></option>
</select>
</p>

<?php	
}


add_action( 'save_post', 'jm_tc_meta_box_save' );
function jm_tc_meta_box_save( $post_id )
{
// Bail if we're doing an auto save
if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

// if our nonce isn't there, or we can't verify it, bail
if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'jm_tc_meta_box_nonce' ) ) return;

// if our current user can't edit this post, bail
if( !current_user_can( 'edit_post' ) ) return;

// now we can actually save the data
$allowed = array( 
'a' => array( // on allow a tags
'href' => array() // and those anchords can only have href attribute
)
);

// Probably a good idea to make sure your data is set		
if( isset( $_POST['twitterCardType'] ) )
update_post_meta( $post_id, 'twitterCardType', esc_attr( $_POST['twitterCardType'] ) );
}

} 

//add twitter infos
$opts = jm_tc_get_options(); 
if(!function_exists( 'jm_tc_contactmethods' ) && $opts['twitterProfile'] == 'yes') {
function jm_tc_contactmethods( $contactmethods ) {
if ( current_user_can( 'publish_posts') ) 
// Add Twitter
$contactmethods['twitter'] = 'Twitter Cards Creator @';                     
return $contactmethods;
}
add_filter('user_contactmethods','jm_tc_contactmethods',10,1);
}


//grab excerpt
if(!function_exists( 'get_excerpt_by_id' )) {

function get_excerpt_by_id($post_id){
$the_post = get_post($post_id); 
$the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt

//SET LENGTH
$excerpt_length = jm_tc_get_options();
$excerpt_length = $excerpt_length['twitterExcerptLength'];


$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
$words = explode(' ', $the_excerpt, $excerpt_length + 1);
if(count($words) > $excerpt_length) :
array_pop($words);
array_push($words, '…');
$the_excerpt = implode(' ', $words);
endif;
return esc_attr($the_excerpt);// to prevent meta from being broken by ""
}
}


// function to add markup in head section of post types
if(!function_exists( 'add_twitter_card_info' )) {

function add_twitter_card_info() {
global $post;	
/* get options */          		
$opts = jm_tc_get_options(); 	
if ( is_front_page()||is_home()) {
echo "\n".'<!-- JM Twitter Cards by Julien Maury '.JM_TC_VERSION.' -->'."\n";  	                   					
echo '<meta property="twitter:card" content="'. $opts['twitterCardType'] .'"/>'."\n"; 
echo '<meta property="twitter:creator" content="@'. $opts['twitterCreator'] .'"/>'."\n";
echo '<meta property="twitter:site" content="@'. $opts['twitterSite'] .'"/>'."\n";								
echo '<meta property="twitter:domain" content="' . get_bloginfo('wpurl') . '"/>'."\n";//URL is no longer necessary and it's quite logical because if domain is approuved all URL are approuved
echo '<meta property="twitter:title" content="' .$opts['twitterPostPageTitle'] . '"/>'."\n";     
echo '<meta property="twitter:description" content="' . $opts['twitterPostPageDesc'] . '"/>'."\n"; 
echo '<meta property="twitter:image" content="' . $opts['twitterImage'] . '"/>'."\n";                   
echo '<!-- /JM Twitter Cards -->'."\n\n"; 
} 

	else {
echo "\n".'<!-- JM Twitter Cards by Julien Maury '.JM_TC_VERSION.' -->'."\n";  
	
// get current post meta data
$creator   = get_the_author_meta('twitter', $post->post_author);		
$cardType  = get_post_meta($post->ID, 'twitterCardType', true);

// support for custom meta description WordPress SEO by Yoast or All in One SEO
if (class_exists('WPSEO_Frontend') ) { // little trick to check if plugin is here and active :)
$object = new WPSEO_Frontend();
if($opts['twitterCardSEOTitle'] == 'yes') { $cardTitle = $object->title( false );} else { $cardTitle = the_title_attribute( array('echo' => false) );}
if($opts['twitterCardSEODesc'] == 'yes') { $cardDescription = $object->metadesc( false ); } else { $cardDescription = get_excerpt_by_id($post->ID);}
} elseif (class_exists('All_in_One_SEO_Pack')) {
global $post;
$post_id = $post;
if (is_object($post_id)) $post_id = $post_id->ID;
if($opts['twitterCardSEOTitle'] == 'yes') { $cardTitle  = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_title', true))); } else { $cardTitle = the_title_attribute( array('echo' => false) );}
if($opts['twitterCardSEODesc'] == 'yes') { $cardDescription = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_description', true))); } else { $cardDescription = get_excerpt_by_id($post->ID); }
}
else { //default (I'll probaly make a switch next time)
$cardTitle = the_title_attribute( array('echo' => false) );
$cardDescription = get_excerpt_by_id($post->ID);
}

if(($opts['twitterCardCustom'] == 'yes') && !empty($cardType)) {

echo '<meta property="twitter:card" content="'. $cardType .'"/>'."\n";
} else {
echo '<meta property="twitter:card" content="'. $opts['twitterCardType'] .'"/>'."\n"; 
}
if(!empty($creator)) { // this part has to be optional, this is more for guest bltwitterging but it's no reason to bother everybody.
echo '<meta property="twitter:creator" content="@'. $creator .'"/>'."\n";												
} else {
echo '<meta property="twitter:creator" content="@'. $opts['twitterCreator'] .'"/>'."\n";
}
// these next 4 parameters should not be editable in post admin 
echo '<meta property="twitter:site" content="@'. $opts['twitterSite'] .'"/>'."\n";												  
echo '<meta property="twitter:domain" content="' . get_bloginfo('wpurl') . '"/>'."\n";
echo '<meta property="twitter:title" content="' . $cardTitle  . '"/>'."\n";  // filter used by plugin to customize title  
echo '<meta property="twitter:description" content="' . $cardDescription . '"/>'."\n"; 

if(!has_post_thumbnail( $post->ID ) && $cardType !== 'gallery') {
echo '<meta property="twitter:image" content="' . $opts['twitterImage'] . '"/>'."\n";
} elseif( $cardType  == 'gallery') {
//get attachment for gallery cards
$args = array( 
'post_type' => 'attachment', 
'numberposts' => -1, 
'exclude'=> get_post_thumbnail_id(), 
'post_mime_type' => 'image', 
'post_status' => null, 
'post_parent' => $post->ID 
); 
 $attachments = get_posts($args);
if ($attachments &&  count( $attachments ) > 3 ) {
	$i = 0; 
	  foreach ( $attachments as $attachment ) {
	  // get attachment array with the ID from the returned posts 
	  $pic = wp_get_attachment_url( $attachment->ID);
	  echo '<meta property="twitter:image'.$i.'" content="' . $pic . '"/>'."\n";
	  $i++;
	  if ($i > 3) break; //in case there are more than 4 images in post
	  }
	 }	
 } else {
$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
echo '<meta property="twitter:image" content="' . $thumb[0] . '"/>'."\n";
}
if($cardType  == 'photo') {
echo '<meta property="twitter:image:width" content="'.$opts['twitterImageWidth'].'">'."\n";
echo '<meta property="twitter:image:height" content="'.$opts['twitterImageHeight'].'">'."\n";
} 
echo '<!-- /JM Twitter Cards -->'."\n\n"; 
}      

}
add_action( 'wp_head', 'add_twitter_card_info');
}

/*
* ADMIN OPTION PAGE
*/

// Language support
add_action( 'admin_init', 'jm_tc_lang_init' );
function jm_tc_lang_init() {
load_plugin_textdomain( 'jm-tc', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

// Add a "Settings" link in the plugins list
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'jm_tc_settings_action_links', 10, 2 );
function jm_tc_settings_action_links( $links, $file ) {
$settings_link = '<a href="' . admin_url( 'options-general.php?page=jm_tc_options' ) . '">' . __("Settings") . '</a>';
array_unshift( $links, $settings_link );

return $links;
}


//The add_action to add onto the WordPress menu.
add_action('admin_menu', 'jm_tc_add_options');
function jm_tc_add_options() {
$page = add_submenu_page( 'options-general.php', 'JM Twitter Cards Options', 'JM Twitter Cards', 'manage_options', 'jm_tc_options', 'jm_tc_options_page' );
register_setting( 'jm-tc', 'jm_tc', 'jm_tc_sanitize' );
add_action( 'admin_print_styles-' . $page, 'jm_tc_admin_css' );//add styles for our options page the WordPress way
}


// Add styles the WordPress Way >> http://codex.wordpress.org/Function_Reference/wp_enqueue_style#Load_stylesheet_only_on_a_plugin.27s_options_page
function jm_tc_admin_css() {  
wp_enqueue_style( 'jm-style-tc', plugins_url('admin/jm-tc-admin-style.css', __FILE__)); 
} 

// Check if a plugin is active (> SEO by Yoast)
function jm_tc_is_plugin_active( $plugin ) {
return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || is_plugin_active_for_network( $plugin );	
}	
// Add dismissible notice	
add_action('admin_notices', 'example_admin_notice');
if(!function_exists( 'example_admin_notice' )) {
function example_admin_notice() {
global $current_user ;
$user_id = $current_user->ID;
if ( ! get_user_meta($user_id, 'example_ignore_notice') && current_user_can( 'install_plugins' ) && jm_tc_is_plugin_active('wordpress-seo/wp-seo.php') ) {
echo '<div class="error"><p>';
printf(__('WordPress SEO by Yoast is activated, please uncheck Twitter Card option in this plugin if it is enabled to avoid adding markup twice | <a href="%1$s">Hide Notice</a>'), '?example_nag_ignore=0','jm-tc');
echo "</p></div>";
}
}
}
add_action('admin_init', 'example_nag_ignore');
if(!function_exists( 'example_nag_ignore' )) {
function example_nag_ignore() {
global $current_user;
$user_id = $current_user->ID;
/* If user clicks to ignore the notice, add that to their user meta */
if ( isset($_GET['example_nag_ignore']) && '0' == $_GET['example_nag_ignore'] ) {
add_user_meta($user_id, 'example_ignore_notice', 'true', true);
}
}
}


// Settings page
function jm_tc_options_page() {
$opts = jm_tc_get_options();
?>
<div id="jm-tc">
<?php screen_icon('options-general'); ?>
<h2><?php _e('JM Twitter Cards Options', 'jm-tc'); ?></h2>

<p><?php _e('This plugin allows you to get Twitter photo, summary <strong>and gallery</strong> cards for your blog. You can even go further in your Twitter Cards experience, see last section.', 'jm-tc'); ?></p>

<form id="jm-tc-form" method="post" action="options.php">

<?php settings_fields('jm-tc'); ?>


<fieldset>  
<legend><?php _e('General', 'jm-tc'); ?></legend>
<p>
<label for="twitterCardType"><?php _e('Choose what type of card you want to use', 'jm-tc'); ?> :</label>
<select id="twitterCardType" name="jm_tc[twitterCardType]">
<option value="summary" <?php echo $opts['twitterCardType'] == 'summary' ? 'selected="selected"' : ''; ?> ><?php _e('summary', 'jm-tc'); ?></option>
<option value="summary_large_image" <?php echo $opts['twitterCardType'] == 'summary_large_image' ? 'selected="selected"' : ''; ?> ><?php _e('summary_large_image', 'jm-tc'); ?></option>
<option value="photo" <?php echo $opts['twitterCardType'] == 'photo' ? 'selected="selected"' : ''; ?> ><?php _e('photo', 'jm-tc'); ?></option>
</select>
</p>
<p>
<label for="twitterCreator"><?php _e('Enter your Personal Twitter account', 'jm-tc'); ?> :</label>
<input id="twitterCreator" type="text" name="jm_tc[twitterCreator]" class="regular-text" value="<?php echo $opts['twitterCreator']; ?>" />
</p>
<p>
<label for="twitterSite"><?php _e('Enter Twitter account for your Website', 'jm-tc'); ?> :</label>
<input id="twitterSite" type="text" name="jm_tc[twitterSite]" class="regular-text" value="<?php echo $opts['twitterSite']; ?>" />
</p>
<p>
<label for="twitterExcerptLength"><?php _e('Set description according to excerpt length (words count)', 'jm-tc'); ?> :</label>
<input id="twitterExcerptLength" type="number" min="10" max="70" name="jm_tc[twitterExcerptLength]" class="small-number" value="<?php echo $opts['twitterExcerptLength']; ?>" />
</p>
<p>
<label for="twitterImage"><?php _e('Enter URL for fallback image (image by default)', 'jm-tc'); ?> :</label>
<input id="twitterImage" type="url" name="jm_tc[twitterImage]" class="regular-text" value="<?php echo $opts['twitterImage']; ?>" />
</p>
<?php submit_button(null, 'primary', 'JM_submit'); ?>
</fieldset>
<fieldset>   
<legend><?php _e('SEO By Yoast or All in One SEO Users', 'jm-tc'); ?></legend>  
<p>
<label for="twitterCardSEOTitle"><?php _e('Use SEO by Yoast or All in ONE SEO meta title for your cards (<strong>default is yes</strong>)', 'jm-tc'); ?> :</label>
<select id="twitterCardSEOTitle" name="jm_tc[twitterCardSEOTitle]">
<option value="yes" <?php echo $opts['twitterCardSEOTitle'] == 'yes' ? 'selected="selected"' : ''; ?> ><?php _e('yes', 'jm-tc'); ?></option>
<option value="no" <?php echo $opts['twitterCardSEOTitle'] == 'no' ? 'selected="selected"' : ''; ?> ><?php _e('no', 'jm-tc'); ?></option>
</select></p> 
<p>
<label for="twitterCardSEODesc"><?php _e('Use SEO by Yoast or All in ONE SEO meta description for your cards (<strong>default is yes</strong>)', 'jm-tc'); ?> :</label>
<select id="twitterCardSEODesc" name="jm_tc[twitterCardSEODesc]">
<option value="yes" <?php echo $opts['twitterCardSEODesc'] == 'yes' ? 'selected="selected"' : ''; ?> ><?php _e('yes', 'jm-tc'); ?></option>
<option value="no" <?php echo $opts['twitterCardSEODesc'] == 'no' ? 'selected="selected"' : ''; ?> ><?php _e('no', 'jm-tc'); ?></option>
</select></p> 

<?php submit_button(null, 'primary', 'JM_submit'); ?>	
</fieldset>   	
<fieldset>
<legend><?php _e('Options for photo cards', 'jm-tc'); ?></legend>			              
<p>

<blockquote class="jm-doc">
<?php _e(' To define a photo card experience, set your card type to "photo" and provide a twitter:image. Twitter will resize images, maintaining original aspect ratio to fit the following sizes:', 'jm-tc'); ?>
<ul class="jm-doc-photocard">
<li> <?php _e('<strong>Web</strong>: maximum height of 375px, maximum width of 435px', 'jm-tc'); ?></li>
<li> <?php _e('<strong>Mobile (non-retina displays)</strong>: maximum height of 375px, maximum width of 280px', 'jm-tc'); ?></li>
<li> <?php _e('<strong>Mobile (retina displays)</strong>: maximum height of 750px, maximum with of 560px', 'jm-tc'); ?></li>
<li> <?php _e('Twitter will not create a photo card unless the twitter:image is of a minimum size of 280px wide by 150px tall. Images will not be cropped unless they have an exceptional aspect ratio', 'jm-tc'); ?></li>
</ul>                                    

</blockquote>
</p>
<p>
<label for="twitterImageWidth"><?php _e('Image width', 'jm-tc'); ?> :</label>
<input id="twitterImageWidth" type="number" min="280" name="jm_tc[twitterImageWidth]" class="small-number" value="<?php echo $opts['twitterImageWidth']; ?>" />
</p>
<p>
<label for="twitterImageHeight"><?php _e('Image height', 'jm-tc'); ?> :</label>
<input id="twitterImageHeight" type="number" min="150" name="jm_tc[twitterImageHeight]" class="small-number" value="<?php echo $opts['twitterImageHeight']; ?>" />
</p>
<?php submit_button(null, 'primary', 'JM_submit'); ?>	
</fieldset>		
<fieldset>  
<legend><?php _e('Custom Twitter Cards', 'jm-tc'); ?></legend>

<p>
<?php _e('If you activate this option, you can custom every single post (page or post or even attachment). You are able to choose creator and card type for each post.', 'jm-tc'); ?>
</p>

<p>

<label for="twitterCardCustom"><?php _e('Get a <strong>custom metabox</strong> on each post type admin', 'jm-tc'); ?> :</label>
<select id="twitterCardCustom" name="jm_tc[twitterCardCustom]">
<option value="yes" <?php echo $opts['twitterCardCustom'] == 'yes' ? 'selected="selected"' : ''; ?> ><?php _e('yes', 'jm-tc'); ?></option>
<option value="no" <?php echo $opts['twitterCardCustom'] == 'no' ? 'selected="selected"' : ''; ?> ><?php _e('no', 'jm-tc'); ?></option>
</select>
<br />
(<em><?php _e('If enabled, a custom metabox will appear (admin panel) in your edit', 'jm-tc'); ?></em>)
</p>
<p>
<?php _e('In 1.1.8 creator has been removed from metabox. Now it will grab this directly from profiles. This should be more comfortable for guest bltwitterging. If you do not have any Twitter option like that on profiles, just activate it here :','jm-tc'); ?>
</p>
<p>
<label for="twitterProfile"><?php _e('Add a field Twitter to profiles', 'jm-tc'); ?> :</label>
<select id="twitterProfile" name="jm_tc[twitterProfile]">
<option value="yes" <?php echo $opts['twitterProfile'] == 'yes' ? 'selected="selected"' : ''; ?> ><?php _e('yes', 'jm-tc'); ?></option>
<option value="no" <?php echo $opts['twitterProfile'] == 'no' ? 'selected="selected"' : ''; ?> ><?php _e('no', 'jm-tc'); ?></option>
</select>
</p>	          

<?php submit_button(null, 'primary', 'JM_submit'); ?>	
</fieldset>   	
<fieldset>
<legend><?php _e('New feature : Gallery Cards', 'jm-tc'); ?></legend>
<p><?php _e('Gallery cards is a brand new feature that enhance Twitter Card Experience. This is quite experimental so be careful with it. Nevertheless this is a cool way to integrate multiple images in your tweets (4 images). See the screenshot in plugin folder :', 'jm-tc'); ?></p>
<p><img src="<?php echo plugins_url('screenshot-4.png',__FILE__);?>" /></p>
<p><?php _e('This option is available <strong>only in full custom mode</strong>. You must insert at least 4 images in a post with gallery card and 4 images will be shown at most. Post thumbnail will not be included if set, gallery cards are meant for attachments.','jm-tc'); ?></p>
</fieldset>
<fieldset>   
<legend>Home - <?php _e('Posts page', 'jm-tc'); ?></legend>  
<p>
<?php _e('In case you use home page as post page, this part will allow you to specify some parameters. Otherwise it would not work for this specific page. I know this is not ideal but until I find a better solution it fixes bug!','jm-tc'); ?>
</p> 

<p>
<label for="twitterPostPageTitle"><strong><?php _e('Enter title for Posts Page :', 'jm-tc'); ?> </strong>:</label><br />
<input id="twitterPostPageTitle" type="text" name="jm_tc[twitterPostPageTitle]" class="regular-text" value="<?php echo $opts['twitterPostPageTitle']; ?>" />
</p>
<p>
<label for="twitterPostPageDesc"><strong><?php _e('Enter description for Posts Page (max: 70 words)', 'jm-tc'); ?> </strong>:</label><br />
<textarea id="twitterPostPageDesc" rows="4" cols="100" name="jm_tc[twitterPostPageDesc]" class="regular-text"><?php echo $opts['twitterPostPageDesc']; ?></textarea>
</p>

<?php submit_button(null, 'primary', 'JM_submit'); ?>	
</fieldset>   	     


</form>
<h3><?php _e('Validation', 'jm-tc') ?></h3>
<p><strong><?php _e('Do not forget to valid your website on dev.twitter :', 'jm-tc') ?></strong></p>
<ul class="jm-tools">
<li><a class="jm-preview" title="Twitter Cards Preview Tool" target="_blank" href="https://dev.twitter.com/docs/cards/preview" rel="nofollow" target="_blank"><?php _e('Preview tool', 'jm-tc') ?></a></li>
<li><a class="jm-valid-card" title="Twitter Cards Application Form" target="_blank" href="https://dev.twitter.com/node/7940" rel="nofollow" target="_blank"><?php _e('Validation form', 'jm-tc') ?></a></li>
</ul>

<h3><?php _e('About the plugin', 'jm-tc') ?></h3>

<ul class="jm-other-links">
<li><a class="jm-rating" target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jm-twitter-cards"><?php _e('Rate the plugin on WordPress.org', 'jm-tc') ?></a></li>
<li><a class="jm-twitter" target="_blank" href="<?php _e('https://twitter.com/intent/tweet?source=webclient&amp;hastags=WordPress,Plugin&amp;text=JM%20Twitter%20Cards%20%20is%20a%20great%20WordPress%20plugin%20to%20get%20Twitter%20Cards%20Try%20it!&amp;url=http://wordpress.org/extend/plugins/jm-twitter-cards/&amp;related=TweetPressFr&amp;via=TweetPressFr','jm-tc'); ?>"><?php _e('Tweet it', 'jm-tc') ?></a></li>
<li><a class="jm-donate" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=jmlapam%40gmail%2ecom&amp;item_name=JM%20Twitter%20Cards&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8"><?php _e('Make a donation', 'jm-tc') ?></a></li>	 
<li><a class="jm-api-version" target="_blank" href="https://dev.twitter.com/docs/api/1.1"><?php _e('REST API version 1.1 (last version)', 'jm-tc'); ?></a></li>			
</ul>

</div>
<?php
}

/*
* OPTIONS TREATMENT
*/

// Process options when submitted
function jm_tc_sanitize($options) {
return array_merge(jm_tc_get_options(), jm_tc_sanitize_options($options));
}

// Sanitize options
function jm_tc_sanitize_options($options) {
$new = array();

if ( !is_array($options) )
return $new;

if ( isset($options['twitterCardType']) )
$new['twitterCardType']       = $options['twitterCardType'];
if ( isset($options['twitterCreator']) )
$new['twitterCreator']		  = esc_attr(strip_tags( $options['twitterCreator'] ));
if ( isset($options['twitterSite']) )
$new['twitterSite']           = esc_attr(strip_tags($options['twitterSite']));
if ( isset($options['twitterExcerptLength']) )
$new['twitterExcerptLength']  = (int) $options['twitterExcerptLength'];
if ( isset($options['twitterImage']) )
$new['twitterImage']          = esc_url($options['twitterImage']);
if ( isset($options['twitterImageWidth']) )
$new['twitterImageWidth']     = (int) $options['twitterImageWidth'];
if ( isset($options['twitterImageHeight']) )
$new['twitterImageHeight']    = (int) $options['twitterImageHeight'];
if ( isset($options['twitterCardCustom']) )
$new['twitterCardCustom']     = $options['twitterCardCustom'];
if ( isset($options['twitterProfile']) )
$new['twitterProfile']        = $options['twitterProfile'];
if ( isset($options['twitterPostPageTitle']) )
$new['twitterPostPageTitle']  = esc_attr(strip_tags($options['twitterPostPageTitle']));
if ( isset($options['twitterPostPageDesc']) )
$new['twitterPostPageDesc']   = esc_attr(strip_tags($options['twitterPostPageDesc']));
if ( isset($options['twitterCardSEOTitle']) )
$new['twitterCardSEOTitle']   = $options['twitterCardSEOTitle'];
if ( isset($options['twitterCardSEODesc']) )
$new['twitterCardSEODesc']   = $options['twitterCardSEODesc'];
return $new;
}

// Return default options
function jm_tc_get_default_options() {	

return array(
'twitterCardType'           => 'summary',
'twitterCreator'		    => 'TweetPressFr',
'twitterSite'               => 'TweetPressFr',
'twitterExcerptLength'	    => 35,
'twitterImage'              => 'http://www.gravatar.com/avatar/avatar.jpg',
'twitterImageWidth'         => '280',
'twitterImageHeight'        => '150',
'twitterCardCustom'         => 'no',
'twitterProfile'            => 'no',
'twitterPostPageTitle' 		=> get_bloginfo ( 'name' ),// filter used by plugin to customize title
'twitterPostPageDesc'       => __('Welcome to','jm-tc').' '.get_bloginfo ( 'name' ).' - '. __('see bltwitter posts','jm-tc'),
'twitterCardSEOTitle'       => 'yes',
'twitterCardSEODesc'        => 'yes'
);
}

// Retrieve and sanitize options
function jm_tc_get_options() {
$options = get_option( 'jm_tc' );
return array_merge(jm_tc_get_default_options(), jm_tc_sanitize_options($options));
}


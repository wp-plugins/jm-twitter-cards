<?php
/*
Plugin Name: JM Twitter Cards
Description: Meant to help users which do not use SEO  by Yoast to add Twitter Cards easily
Author: Julien Maury
Author URI: http://www.jmperso.eu
Version: 1.0
License: GPL2++
*/

/*
*    Sources: 
*             -http://sumtips.com/2012/06/add-twitter-cards-meta-data-in-wordpress-themes-other-sites.html 
*            -http://www.uplifted.net/programming/wordpress-get-the-excerpt-automatically-using-the-post-id-outside-of-the-loop/      
*/



          // Plugin activation: create default values if they don't exist
          register_activation_hook( __FILE__, 'jm_tc_init' );
          function jm_tc_init() {
	          $opt_val = get_option( 'jm_tc' );
	          if ( !is_array($opt_val) )
		          update_option('jm_tc', jm_tc_get_default_options());
          }


          // Plugin uninstall: delete option
          register_uninstall_hook( __FILE__, 'jm_tc_uninstall' );
          function jm_tc_uninstall() {
	          delete_option( 'jm_tc' );
          }


          
          //functions to add our card

	          			function add_twitter_card_info() {
                                    global $post;
                                    if ( !is_single())
                                    return;
                                     
                                        $opts = jm_tc_get_options();
                                        echo '<meta name="twitter:card" content="'. $opts['twitterCardType'] .'"/>'."\n";
                                        echo '<meta name="twitter:url" content="' . get_permalink() . '"/>'."\n"; //Le permalien
                                        echo '<meta name="twitter:title" content="' . the_title_attribute( array('echo' => false) ) . '"/>'."\n"; //Le titre
                                        echo '<meta name="twitter:description" content="' . get_excerpt_by_id($post->ID) . '"/>'."\n"; //L'extrait
                                        echo '<meta name="twitter:site" content="@'. $opts['twitterSite'] .'"/>'."\n";
                                        echo '<meta name="twitter:creator" content="@'. $opts['twitterCreator'] .'"/>'."\n";
                                         
                                        if(!has_post_thumbnail( $post->ID )) {
                                                echo '<meta name="twitter:image" content="' . $opts['twitterImage'] . '"/>'."\n";
                                        } else {
                                                $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
                                                echo '<meta name="twitter:image" content="' . $thumb[0] . '"/>'."\n";
                                        }
                                         
                                        if(isset($cardType) && ($cardType == 'photo' )) {
                                                echo '<meta name="twitter:image:width" content="'.$opts['twitterImageWidth'].'">'."\n";
                                                echo '<meta name="twitter:image:height" content="'.$opts['twitterImageHeight'].'">'."\n";
                                        }
                                   
                                   }
                                   add_action( 'wp_head', 'add_twitter_card_info');
                                   
                                   //adjust excerpt length
                                   function get_excerpt_by_id(){
                                        $the_post = get_post($post_id); //Gets post ID
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

  

           // Language support
          add_action( 'admin_init', 'jm_tc_lang_init' );
          function jm_tc_lang_init() {
	          load_plugin_textdomain( 'jm-tc', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
          }

          // Add a "Settings" link in the plugins list
          add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'jm_tc_settings_action_links', 10, 2 );
          function jm_tc_settings_action_links( $links, $file ) {
	          $settings_link = '<a href="' . admin_url( 'options-general.php?page=jmoptions' ) . '">' . __("Settings") . '</a>';
	          array_unshift( $links, $settings_link );

	          return $links;
          }


          //The add_action to add onto the WordPress menu.
          add_action('admin_menu', 'jm_tc_add_options');
          function jm_tc_add_options() {
	          add_submenu_page( 'options-general.php', 'JM Twitter Cards Options', 'JM Twitter Cards', 'manage_options', 'jmoptions', 'jm_tc_options_page' );
	          register_setting( 'jm-tc', 'jm_tc', 'jm_tc_sanitize' );
          }


          // Settings page
          function jm_tc_options_page() {
	          $opt_val = jm_tc_get_options();
	          ?>
	          <div class="wrap">
		          <?php screen_icon('options-general'); ?>
		          <h2><?php _e( 'JM Twitter Cards Options', 'jm-tc' ); ?></h2>
		          
		          <p><?php _e('I created this plugin to provide quite the same feature to those who do not use the amazing plugin Seo by Yoast. But this plugin goes further.','jm-tc');?></p>

		          <form id="jm_tc" method="post" action="options.php">

			          <?php settings_fields( 'jm-tc' ); ?>
			          <fieldset>  
			               <legend><h3 style="padding:1em 0;"><?php _e('General:', 'jm-tc' ); ?></h3></legend>
			                 <p>
			                   <label for="twitterCardType"><?php _e('Choose what type of card you want to use:', 'jm-tc' ); ?>:</label>
				          	 <select id="twitterCardType" name="jm_tc[twitterCardType]">
                                      <option value="summary" <?php echo $opt_val['twitterCardType'] == 'summary' ? 'selected="selected"' : ''; ?> ><?php _e('summary','jm-tc'); ?></option>
					               <option value="photo" <?php echo $opt_val['twitterCardType'] == 'photo' ? 'selected="selected"' : ''; ?> ><?php _e('photo','jm-tc'); ?></option>
                                     </select>
				           </p>
			                <p>
				               <label for="twitterCreator"><?php _e('Enter your Personal Twitter account:', 'jm-tc' ); ?>:</label>
				               <input id="twitterCreator" type="text" name="jm_tc[twitterCreator]" class="regular-text" value="<?php echo $opt_val['twitterCreator']; ?>" />
			               </p>
			               <p>
				               <label for="twitterSite"><?php _e('Enter Twitter account for your Website:', 'jm-tc' ); ?>:</label>
				               <input id="twitterSite" type="text" name="jm_tc[twitterSite]" class="regular-text" value="<?php echo $opt_val['twitterSite']; ?>" />
			               </p>
			               <p>
				               <label for="twitterExcerptLength"><?php _e('Set description according to excerpt length (words count)', 'jm-tc' ); ?>:</label>
				               <input id="twitterExcerptLength" type="number" min="10" name="jm_tc[twitterExcerptLength]" class="small-number" value="<?php echo $opt_val['twitterExcerptLength']; ?>" />
			               </p>
			                     <p>
				               <label for="twitterImage"><?php _e('Enter URL for fallback image (image by default):', 'jm-tc' ); ?></label>
				               <input id="twitterImage" type="url" name="jm_tc[twitterImage]" class="regular-text" value="<?php echo $opt_val['twitterImage']; ?>" />
			               </p>
			          </fieldset>
			          
			          <fieldset>
			               <legend><h3 style="padding:1em 0;"><?php _e('Options for photo cards:', 'jm-tc' ); ?></h3></legend>
			              
			              <p>
			                    
			                    <blockquote style="background-color:#FFFFE0; border:1px solid #A36B00; font-style:italic; margin-left:0em; padding:1.4em;">
			                             <?php _e(' To define a photo card experience, set your card type to "photo" and provide a twitter:image. Twitter will resize images, maintaining original aspect ratio to fit the following sizes:','jm-tc'); ?>
                                        <ul>
                                            <li style="margin:.3em 0; font-size:normal">- <?php _e('Web: maximum height of 375px, maximum width of 435px','jm-tc'); ?></li>
                                             <li style="margin:.3em 0; font-size:normal">- <?php _e('Mobile (non-retina displays): maximum height of 375px, maximum width of 280px','jm-tc'); ?></li>
                                             <li style="margin:.3em 0; font-size:normal">- <?php _e('Mobile (retina displays): maximum height of 750px, maximum with of 560px','jm-tc'); ?></li>
                                            <li style="margin:.3em 0; font-size:normal">- <?php _e('Twitter will not create a photo card unless the twitter:image is of a minimum size of 280px wide by 150px tall. Images will not be cropped unless they have an exceptional aspect ratio','jm-tc'); ?></li>
			                          </ul>                                    
			                   
			                   </blockquote>
			              </p>
			           <p>
				               <label for="twitterImageWidth"><?php _e('Image width', 'jm-tc' ); ?>:</label>
				               <input id="twitterImageWidth" type="number" min="280" name="jm_tc[twitterImageWidth]" class="small-number" value="<?php echo $opt_val['twitterImageWidth']; ?>" />
			               </p>
			                     <p>
				                 <label for="twitterImageHeight"><?php _e('Image height', 'jm-tc' ); ?>:</label>
				                 <input id="twitterImageHeight" type="number" min="150" name="jm_tc[twitterImageHeight]" class="small-number" value="<?php echo $opt_val['twitterImageHeight']; ?>" />
			              </p>
			          </fieldset>
	
			          
			          <?php submit_button(null, 'primary', 'JM_submit'); ?>
		          </form>
		          <h3><?php _e('Validation', 'jm-tc') ?></h3>
		          <p><strong><?php _e('Do not forget to valid your website on dev.twitter :', 'jm-tc') ?></strong></p>
		          <ul>
		                    <li><a title="Twitter Cards Preview Tool" href="https://dev.twitter.com/docs/cards/preview" rel="nofollow" target="_blank"><?php _e('Preview tool', 'jm-tc') ?></a></li>
		                    <li><a title="Twitter Cards Application Form" href="https://dev.twitter.com/node/7940" rel="nofollow" target="_blank"><?php _e('Validation form', 'jm-tc') ?></a></li>
		               </ul>

		          <h3><?php _e('Make a donation', 'jm-tc') ?></h3>

		          <p><?php _e( 'Like this plugin? Consider making a donation to help me providing good stuffs:', 'jm-tc' ); ?></p>
		          <p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=jmlapam%40gmail%2ecom&amp;item_name=JM%20Widget%20Feed%20Panel&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8"><?php _e('Donate', 'jm-tc') ?></a></p>
	          </div>
	          <?php
          }


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
		          $new['twitterCardType']        = $options['twitterCardType'];
	          if ( isset($options['twitterCreator']) )
		          $new['twitterCreator']		    = esc_html(strip_tags( $options['twitterCreator'] ));
	          if ( isset($options['twitterSite']) )
		          $new['twitterSite']			    = esc_html(strip_tags($options['twitterSite']));
	          if ( isset($options['twitterExcerptLength']) )
		          $new['twitterExcerptLength']  = (int) $options['twitterExcerptLength'];
		     if ( isset($options['twitterImage']) )
		          $new['twitterImage']              = esc_url($options['twitterImage']);
		      if ( isset($options['twitterImageWidth']) )
		          $new['twitterImageWidth']     = (int) $options['twitterImageWidth'];
		       if ( isset($options['twitterImageHeight']) )
		          $new['twitterImageHeight']     = (int) $options['twitterImageHeight'];

	          return $new;
          }


          // Return default options
          function jm_tc_get_default_options() {
	          return array(
	               'twitterCardType'          => 'summary',
		          'twitterCreator'			=> 'jmlapam',
		          'twitterSite'                   => 'jmlapam',
		          'twitterExcerptLength'	=> 30,
		          'twitterImage'               =>  'http://www.gravatar.com/avatar/avatar.jpg',
		          'twitterImageWidth'      =>  '280',
		          'twitterImageHeight'     =>  '150'
	          );
          }


          // Retrieve and sanitize options
          function jm_tc_get_options() {
	          $options = get_option( 'jm_tc' );
	          return array_merge(jm_tc_get_default_options(), jm_tc_sanitize_options($options));
          }

<?php
/*
Plugin Name: JM Twitter Cards
Description: Meant to help users which do not use SEO  by Yoast to add Twitter Cards easily
Author: Julien Maury
Author URI: http://wp.jmperso.eu
Version: 1.1.3
License: GPL2++
*/

/*
*    Sources: - https://dev.twitter.com/docs/cards
 * 			  - http://codex.wordpress.org/Function_Reference/wp_enqueue_style
*             I decided to remove former sources because I've been enhanced them by far and above all these sources are wrong : get_the_excerpt() outside the loop or undefined var !)
*            
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



                 if( !function_exists( 'get_excerpt_by_id' )  ) {  
	                                   //adjust excerpt length
	                                   function get_excerpt_by_id(){
	                                        $the_post = get_post(); //Gets post ID
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

				if( !function_exists( 'add_twitter_card_info' )  ) {
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
                                        
                                        if( $opts['twitterCardType'] == 'photo') {
                                                echo '<meta name="twitter:image:width" content="'.$opts['twitterImageWidth'].'">'."\n";
                                                echo '<meta name="twitter:image:height" content="'.$opts['twitterImageHeight'].'">'."\n";
                                        }
                                   
                                   }
                                   add_action( 'wp_head', 'add_twitter_card_info');
							
  

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
	          $page = add_submenu_page( 'options-general.php', 'JM Twitter Cards Options', 'JM Twitter Cards', 'manage_options', 'jmoptions', 'jm_tc_options_page' );
	          register_setting( 'jm-tc', 'jm_tc', 'jm_tc_sanitize' );
			  add_action( 'admin_print_styles-' . $page, 'jm_tc_admin_css' );//add styles for our options page the WordPress way
          }

		  
		  // Add styles the WordPress Way >> http://codex.wordpress.org/Function_Reference/wp_enqueue_style#Load_stylesheet_only_on_a_plugin.27s_options_page
				function jm_tc_admin_css() {  
				 wp_enqueue_style( 'jm-style-tc', plugins_url('admin/jm-tc-admin-style.css', __FILE__)); 
				} 

					  

          // Settings page
          function jm_tc_options_page() {
	          $opt_val = jm_tc_get_options();
	          ?>
	          <div id="jm-tc">
		          <?php screen_icon('options-general'); ?>
		          <h2><?php _e('JM Twitter Cards Options', 'jm-tc'); ?></h2>
		          
		          <blockquote class="desc"><?php _e('This plugin allows you to get Twitter photo and summary cards for your blogs if you do not use SEO by Yoast.', 'jm-tc'); ?></blockquote>

		          <form id="jm-tc-form" method="post" action="options.php">

			          <?php settings_fields('jm-tc'); ?>
			          <fieldset>  
			               <legend><?php _e('General', 'jm-tc'); ?></legend>
			                 <p>
			                   <label for="twitterCardType"><?php _e('Choose what type of card you want to use', 'jm-tc'); ?> :</label>
				          	 <select id="twitterCardType" name="jm_tc[twitterCardType]">
                                      <option value="summary" <?php echo $opt_val['twitterCardType'] == 'summary' ? 'selected="selected"' : ''; ?> ><?php _e('summary', 'jm-tc'); ?></option>
					               <option value="photo" <?php echo $opt_val['twitterCardType'] == 'photo' ? 'selected="selected"' : ''; ?> ><?php _e('photo', 'jm-tc'); ?></option>
                                     </select>
				           </p>
			                <p>
				               <label for="twitterCreator"><?php _e('Enter your Personal Twitter account', 'jm-tc'); ?> :</label>
				               <input id="twitterCreator" type="text" name="jm_tc[twitterCreator]" class="regular-text" value="<?php echo $opt_val['twitterCreator']; ?>" />
			               </p>
			               <p>
				               <label for="twitterSite"><?php _e('Enter Twitter account for your Website', 'jm-tc'); ?> :</label>
				               <input id="twitterSite" type="text" name="jm_tc[twitterSite]" class="regular-text" value="<?php echo $opt_val['twitterSite']; ?>" />
			               </p>
			               <p>
				               <label for="twitterExcerptLength"><?php _e('Set description according to excerpt length (words count)', 'jm-tc'); ?> :</label>
				               <input id="twitterExcerptLength" type="number" min="10" name="jm_tc[twitterExcerptLength]" class="small-number" value="<?php echo $opt_val['twitterExcerptLength']; ?>" />
			               </p>
			                     <p>
				               <label for="twitterImage"><?php _e('Enter URL for fallback image (image by default)', 'jm-tc'); ?> :</label>
				               <input id="twitterImage" type="url" name="jm_tc[twitterImage]" class="regular-text" value="<?php echo $opt_val['twitterImage']; ?>" />
			               </p>
			                      <?php submit_button(null, 'primary', 'JM_submit'); ?>
			          </fieldset>
			      
			          <fieldset>
			               <legend><?php _e('Options for photo cards', 'jm-tc'); ?></legend>
			              
			              <p>
			                    
			                    <blockquote class="jm-doc">
			                             <?php _e(' To define a photo card experience, set your card type to "photo" and provide a twitter:image. Twitter will resize images, maintaining original aspect ratio to fit the following sizes:', 'jm-tc'); ?>
                                        <ul class="jm-doc-photocard">
                                            <li> <?php _e('Web: maximum height of 375px, maximum width of 435px', 'jm-tc'); ?></li>
                                             <li> <?php _e('Mobile (non-retina displays): maximum height of 375px, maximum width of 280px', 'jm-tc'); ?></li>
                                             <li> <?php _e('Mobile (retina displays): maximum height of 750px, maximum with of 560px', 'jm-tc'); ?></li>
                                            <li> <?php _e('Twitter will not create a photo card unless the twitter:image is of a minimum size of 280px wide by 150px tall. Images will not be cropped unless they have an exceptional aspect ratio', 'jm-tc'); ?></li>
			                          </ul>                                    
			                   
			                   </blockquote>
			              </p>
			           <p>
				               <label for="twitterImageWidth"><?php _e('Image width', 'jm-tc'); ?> :</label>
				               <input id="twitterImageWidth" type="number" min="280" name="jm_tc[twitterImageWidth]" class="small-number" value="<?php echo $opt_val['twitterImageWidth']; ?>" />
			               </p>
			                     <p>
				                 <label for="twitterImageHeight"><?php _e('Image height', 'jm-tc'); ?> :</label>
				                 <input id="twitterImageHeight" type="number" min="150" name="jm_tc[twitterImageHeight]" class="small-number" value="<?php echo $opt_val['twitterImageHeight']; ?>" />
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
				    <li><a class="jm-api-version" target="_blank" href="https://dev.twitter.com/docs/api/1.1"><?php _e('REST API version 1.1 (last version)', 'jm-tc'); ?></a></li>
					<li><a class="jm-rating" target="_blank" href="http://wordpress.org/support/view/plugin-reviews/jm-twitter-cards"><?php _e('Rate the plugin on WordPress.org', 'jm-tc') ?></a></li>
					<li><a class="jm-twitter" target="_blank" href="https://twitter.com/intent/tweet?source=webclient&amp;hastags=WordPress,Plugin&amp;text=JM%20Twitter%20Cards%20%20is%20a%20great%20WordPress%20plugin%20to%20get%20Twitter%20Cards%20Try%20it!&amp;url=http://wordpress.org/extend/plugins/jm-twitter-cards/&amp;related=jmlapam&amp;via=jmlapam"><?php _e('Tweet it', 'jm-tc') ?></a></li>
					<li><a class="jm-donate" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=jmlapam%40gmail%2ecom&amp;item_name=JM%20Twitter%20Cards&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8"><?php _e('Make a donation', 'jm-tc') ?></a></li>					
			  	 </ul>
			 
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
			'twitterCardType'           => 'summary',
			'twitterCreator'			=> 'jmlapam',
			'twitterSite'               => 'jmlapam',
			'twitterExcerptLength'	    => 30,
			'twitterImage'              => 'http://www.gravatar.com/avatar/avatar.jpg',
			'twitterImageWidth'         => '280',
			'twitterImageHeight'        => '150'
			);
			}

			// Retrieve and sanitize options
			function jm_tc_get_options() {
			$options = get_option( 'jm_tc' );
			return array_merge(jm_tc_get_default_options(), jm_tc_sanitize_options($options));
			}
			}
		

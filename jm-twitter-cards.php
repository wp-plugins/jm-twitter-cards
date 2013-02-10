<?php
/*
Plugin Name: JM Twitter Cards
Plugin URI: http://tweetpress.fr
Description: Meant to help users which do not use SEO  by Yoast to add Twitter Cards easily
Author: Julien Maury
Author URI: http://tweetpress.fr
Version: 1.1.8
License: GPL2++
*/

/*
*    Sources: - https://dev.twitter.com/docs/cards
* 			  - http://codex.wordpress.org/Function_Reference/wp_enqueue_style
*             - I decided to remove former sources because I've been enhanced them by far and above all these sources are wrong : get_the_excerpt() outside the loop or undefined var !)
*												 - http://wptheming.com/2011/08/admin-notices-in-wordpress/
*/



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
       
       
       // Add the Meta Box
        function jm_tc_add_jm_tc_meta_boxes() {
            add_meta_box(
		             'jm_tc_meta_box', // $id
		             'JM Twitter Cards', // $title 
		             'jm_tc_show_meta_box', // $callback
		             'post', // $page
		             'side', // $context
		             'high'); // $priority
		             
		             add_meta_box(
		             'jm_tc_meta_box', // $id
		             'JM Twitter Cards', // $title 
		             'jm_tc_show_meta_box', // $callback
		             'page', // $page
		             'side', // $context
		             'high'); // $priority
		             
		             add_meta_box(
		             'jm_tc_meta_box', // $id
		             'JM Twitter Cards', // $title 
		             'jm_tc_show_meta_box', // $callback
		             'attachment', // $page
		             'side', // $context
		             'high'); // $priority
        }
        add_action('add_meta_boxes', 'jm_tc_add_jm_tc_meta_boxes');
        
        
       $custom_meta_fields = array(      
                array(  
               'label' => __('Card type', 'jm-tc'),  
               'desc'  => __('Choose what type of card you want to use', 'jm-tc'),  
               'id'    =>'twitterCardType',  
               'type'  => 'select',  
               'options' => array (    
                   'summary' => array (  
                       'label' => __('Summary','jm-tc'),  
                       'value' => 'summary'  
                   ),  
                   'photo' => array (  
                       'label' => __('Photo','jm-tc'),  
                       'value' => 'photo'  
                   )  
               )  
           )  
           
        );
        
        
        
        // The Callback
        function jm_tc_show_meta_box() {
        global $custom_meta_fields, $post;
        // Use nonce for verification
        echo '<input type="hidden" name="jm_tc_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	
	        // Begin the field table and loop
	        echo '<table class="form-table">';
	        foreach ($custom_meta_fields as $field) {
		        // get value of this field if it exists for this post
		        $meta = get_post_meta($post->ID, $field['id'], true);
		        // begin a table row with
		        echo '<tr>
				        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
				        <td>';           
                echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';  
            foreach ($field['options'] as $option) {  
                echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';  
            }  
            echo '</select>';
		        echo '</td></tr>';
	        } // end foreach
	        echo '</table><br /><span class="description">'.$field['desc'].'</span>'; // end table
        }
        
        // Save the Data
        function jm_tc_save_custom_meta($post_id) {
            global $custom_meta_fields;
	
	        // verify nonce
	        if (!wp_verify_nonce($_POST['jm_tc_meta_box_nonce'], basename(__FILE__))) 
		        return $post_id;
	        // check autosave
	        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		        return $post_id;
	        // check permissions
	        if ('page' == $_POST['post_type']) {
		        if (!current_user_can('edit_page', $post_id))
			        return $post_id;
		        } elseif (!current_user_can('edit_post', $post_id)) {
			        return $post_id;
	        }
	
	        // loop through fields and save the data
	        foreach ($custom_meta_fields as $field) {
		        $old = get_post_meta($post_id, $field['id'], true);
		        $new = $_POST[$field['id']];
		        if ($new && $new != $old) {
			        update_post_meta($post_id, $field['id'], $new);
		        } elseif ('' == $new && $old) {
			        delete_post_meta($post_id, $field['id'], $old);
		        }
	        } // end foreach
        }
        add_action('save_post', 'jm_tc_save_custom_meta');  
       
       
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
				                    			
				                    			if (!is_singular() && !is_single() && !is_home())// with those conditional tags we can test if it's a page, a post or an attachment
				                    			return;
				                    			
				          /* get */          		
               $opts = jm_tc_get_options(); 
             // get current post meta data
               $cardType = get_post_meta($post->ID, 'twitterCardType', true);
               $creator  = get_the_author_meta('twitter');
              
             echo "\n".'<!-- JM Twitter Cards by Julien Maury (version 1.1.7) -->'."\n";  	                   					
												
												/* retrieve datas from our metabox */	

											     
											   if(($opts['twitterCardCustom'] == 'yes') && !empty($cardType)) {
													
												  echo '<meta name="twitter:card" content="'. $cardType .'"/>'."\n";
												 } else {
              echo '<meta name="twitter:card" content="'. $opts['twitterCardType'] .'"/>'."\n"; 
             }
             if(!empty($creator)) { // this part has to be optional, this is more for guest blogging but it's no reason to bother everybody.
												  echo '<meta name="twitter:creator" content="@'. $creator .'"/>'."\n";												
												} else {
												  echo '<meta name="twitter:creator" content="@'. $opts['twitterCreator'] .'"/>'."\n";
												  
												}
												  // these next 4 parameters should not be editable in post admin 
												  echo '<meta name="twitter:site" content="@'. $opts['twitterSite'] .'"/>'."\n";												  
                                                  echo '<meta name="twitter:url" content="' . get_permalink() . '"/>'."\n";
                                                  echo '<meta name="twitter:title" content="' . the_title_attribute( array('echo' => false) ) . '"/>'."\n";     
                                                  echo '<meta name="twitter:description" content="' . get_excerpt_by_id($post->ID) . '"/>'."\n"; 
     
                                                  if(!has_post_thumbnail( $post->ID )) {
                                                          echo '<meta name="twitter:image" content="' . $opts['twitterImage'] . '"/>'."\n";
                                                  } else {
                                                          $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
                                                          echo '<meta name="twitter:image" content="' . $thumb[0] . '"/>'."\n";
                                                  }
                                                   
                                                  if($opts['twitterCardType'] == 'photo') {
                                                          echo '<meta name="twitter:image:width" content="'.$opts['twitterImageWidth'].'">'."\n";
                                                          echo '<meta name="twitter:image:height" content="'.$opts['twitterImageHeight'].'">'."\n";
             
                                                  }
                                                                                               
                                                          echo '<!-- /JM Twitter Cards -->'."\n\n"; 
                                             
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
function example_admin_notice() {
	global $current_user ;
        $user_id = $current_user->ID;
	if ( ! get_user_meta($user_id, 'example_ignore_notice') && current_user_can( 'install_plugins' ) && jm_tc_is_plugin_active('wordpress-seo/wp-seo.php') ) {
        echo '<div class="updated"><p>';
        printf(__('WordPress SEO by Yoast is activated, please uncheck Twitter Card option in this plugin if it is enabled to avoid adding markup twice | <a href="%1$s">Hide Notice</a>'), '?example_nag_ignore=0','jm-tc');
        echo "</p></div>";
	}
}
add_action('admin_init', 'example_nag_ignore');
function example_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['example_nag_ignore']) && '0' == $_GET['example_nag_ignore'] ) {
             add_user_meta($user_id, 'example_ignore_notice', 'true', true);
	}
}
					  

          // Settings page
          function jm_tc_options_page() {
	          $opts = jm_tc_get_options();
	          ?>
	          <div id="jm-tc">
		          <?php screen_icon('options-general'); ?>
		          <h2><?php _e('JM Twitter Cards Options', 'jm-tc'); ?></h2>
		          
		          <p><?php _e('This plugin allows you to get Twitter photo and summary cards for your blogs if you do not use SEO by Yoast. But now you can go further in your Twitter Cards experience, see last section.', 'jm-tc'); ?></p>
						 				        
		          <form id="jm-tc-form" method="post" action="options.php">

			          <?php settings_fields('jm-tc'); ?>

	
			             <fieldset>  
			               <legend><?php _e('General', 'jm-tc'); ?></legend>
			                 <p>
			                 <label for="twitterCardType"><?php _e('Choose what type of card you want to use', 'jm-tc'); ?> :</label>
				          	 <select id="twitterCardType" name="jm_tc[twitterCardType]">
                                   <option value="summary" <?php echo $opts['twitterCardType'] == 'summary' ? 'selected="selected"' : ''; ?> ><?php _e('summary', 'jm-tc'); ?></option>
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
			             <?php _e('In 1.1.8 creator has been removed from metabox. Now it will grab this directly from profiles. This should be more comfortable for guest blogging.','jm-tc'); ?>
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
					<li><a class="jm-donate" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=TweetPressFr%40gmail%2ecom&amp;item_name=JM%20Twitter%20Cards&amp;no_shipping=0&amp;no_note=1&amp;tax=0&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8"><?php _e('Make a donation', 'jm-tc') ?></a></li>	 
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
			$new['twitterCreator']		      = esc_attr(strip_tags( $options['twitterCreator'] ));
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
			return $new;
			}

			// Return default options
			function jm_tc_get_default_options() {
			return array(
			'twitterCardType'           => 'summary',
			'twitterCreator'		          => 'TweetPressFr',
			'twitterSite'               => 'TweetPressFr',
			'twitterExcerptLength'	    => 35,
			'twitterImage'              => 'http://www.gravatar.com/avatar/avatar.jpg',
			'twitterImageWidth'         => '280',
			'twitterImageHeight'        => '150',
			'twitterCardCustom'         => 'no'
			);
			}

			// Retrieve and sanitize options
			function jm_tc_get_options() {
			$options = get_option( 'jm_tc' );
			return array_merge(jm_tc_get_default_options(), jm_tc_sanitize_options($options));
			}
		

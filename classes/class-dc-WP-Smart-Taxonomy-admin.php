<?php
class DC_Wp_Smart_Taxonomy_Admin {
  
  public $settings;

	public function __construct() {
		//admin script and style
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'));
		
		add_action('dc_WP_ST_dualcube_admin_footer', array(&$this, 'dualcube_admin_footer_for_dc_WP_Smart_Taxonomy'));
		
		add_action( 'add_meta_boxes', array(&$this, 'add_custom_meta_boxes') );
		
		add_action( 'save_post', array(&$this, 'assign_smart_taxonomy') );

		$this->load_class('settings');
		$this->settings = new DC_Wp_Smart_Taxonomy_Settings();
	}
	
	/**
   * WP Samrt Taxonomy settings custom meta options
   */
  function add_custom_meta_boxes() {
    global $DC_Wp_Smart_Taxonomy;
    
    // Smart Taxonomy settings
    add_meta_box( 
        'wp_smart_taxonomy_options',
        __( 'WP Smart Taxonomy', $DC_Wp_Smart_Taxonomy->text_domain ),
        array(&$this, 'set_wp_smart_taxonomy_options'),
        'post', 'normal', 'high'
    );
    
  }
  
  function set_wp_smart_taxonomy_options($post) {
    global $DC_Wp_Smart_Taxonomy;
    
    $smart_cat_settings = get_post_meta($post->ID, '_smart_cat_settings', true);
    if(!$smart_cat_settings) $smart_cat_settings = get_WP_Smart_Taxonomy_settings('', 'dc_WP_ST_general');
    
    echo '<table>';
    $settings_options = array(
                             "placeholder" => array('type' => 'hidden', 'name' => 'smart_cat_settings[placeholder]', 'value' => 'placeholder'),
                             "is_enable" => array('label' => __('Enable Smart Category', $DC_Wp_Smart_Taxonomy->text_domain), 'type' => 'checkbox', 'name' => 'smart_cat_settings[is_enable]', 'value' => 'Enable', 'dfvalue' => $smart_cat_settings['is_enable']),
                             "is_append" => array('label' => __('Append with existing smart categories', $DC_Wp_Smart_Taxonomy->text_domain), 'type' => 'checkbox', 'name' => 'smart_cat_settings[is_append]', 'value' => 'Append', 'dfvalue' => $smart_cat_settings['is_append'], 'hints' => __('If unchecked will replace existing smart categories', $DC_Wp_Smart_Taxonomy->text_domain)),
                             "is_title" => array('label' => __('Generate Smart Category from Post Title', $DC_Wp_Smart_Taxonomy->text_domain), 'type' => 'checkbox', 'name' => 'smart_cat_settings[is_title]', 'value' => 'Title', 'dfvalue' => $smart_cat_settings['is_title']),
                             "is_tag" => array('label' => __('Generate Smart Category from Post Tags', $DC_Wp_Smart_Taxonomy->text_domain), 'type' => 'checkbox', 'name' => 'smart_cat_settings[is_tag]', 'value' => 'Tag', 'dfvalue' => $smart_cat_settings['is_tag'])
                             );
    
    $DC_Wp_Smart_Taxonomy->dc_wp_fields->dc_generate_form_field($settings_options, array('in_table' => true));
    echo '</table>';
    do_action('dc_WP_ST_dualcube_admin_footer');
  }
  
	public function assign_smart_taxonomy($post_id) {
	  
	  // If this is just a autosave, don't do anything
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
	  
	  // If this is just a revision, don't do anything
    if ( wp_is_post_revision( $post_id ) )
      return $post_id;
    
    if( get_post_type($post_id) != 'post' )
      return $post_id;
    
    $post_categories = get_terms( 'category', array( 'hide_empty' => 0 ) );
    if(count($post_categories) == 0)
      return $post_id;
    
    $smart_cat_settings = $_POST['smart_cat_settings'];
    if(!$smart_cat_settings) $smart_cat_settings = get_WP_Smart_Taxonomy_settings('', 'dc_WP_ST_general');
    
    update_post_meta($post_id, '_smart_cat_settings', $smart_cat_settings);
    
    $old_smart_cats = (get_post_meta($post_id, '_smart_cats', true)) ? get_post_meta($post_id, '_smart_cats', true) : array();
    if(!empty($old_smart_cats)) wp_remove_object_terms( $post_id, $old_smart_cats, 'category' );
    
    if(!$smart_cat_settings['is_enable'])
      return $post_id;
    
    $post_title = get_the_title( $post_id );
    $post_tags = wp_get_post_tags( $post_id );
    
    $smart_cats = array();
    
    // Choose Samrt Cats from Post Title
    if($smart_cat_settings['is_title']) {
      foreach($post_categories as $post_category) {
        if(strpos(strtolower($post_title), wptexturize(strtolower($post_category->name))) !== false) {
          $smart_cats[] = $post_category->term_id;
        }
      }
    }
    
    // Choose Samrt Cats from associated Tags
    if($smart_cat_settings['is_tag']) {
      if(!empty($post_tags)) {
        foreach($post_tags as $post_tag) {
          foreach($post_categories as $post_category) {
            if(strtolower($post_category->name) == strtolower($post_tag->name)) {
              $smart_cats[] = $post_category->term_id;
            }
          }
        }
      }
    }
    
    if(!empty($smart_cats)) {
      $smart_cats = array_map('intval', $smart_cats);
      $smart_cats = array_unique( $smart_cats );
      
      if($smart_cat_settings['is_append']) {
        $smart_cats = array_merge((array)$smart_cats, (array)$old_smart_cats);
        $smart_cats = array_unique( $smart_cats );
      }
        
      wp_set_object_terms( $post_id, $smart_cats, 'category', true );
        
      update_post_meta($post_id, '_smart_cats', $smart_cats);
    }
    
    return $post_id;
	}

	function load_class($class_name = '') {
	  global $DC_Wp_Smart_Taxonomy;
		if ('' != $class_name) {
			require_once ($DC_Wp_Smart_Taxonomy->plugin_path . '/admin/class-' . esc_attr($DC_Wp_Smart_Taxonomy->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}// End load_class()
	
	function dualcube_admin_footer_for_dc_WP_Smart_Taxonomy() {
    global $DC_Wp_Smart_Taxonomy;
    ?>
    <div style="clear: both"></div>
    <div id="dc_admin_footer">
      <?php _e('Powered by', $DC_Wp_Smart_Taxonomy->text_domain); ?> <a href="http://dualcube.com" target="_blank"><img src="<?php echo $DC_Wp_Smart_Taxonomy->plugin_url.'/assets/images/dualcube.png'; ?>"></a><?php _e('Dualcube', $DC_Wp_Smart_Taxonomy->text_domain); ?> &copy; <?php echo date('Y');?>
    </div>
    <?php
	}

	/**
	 * Admin Scripts
	 */

	public function enqueue_admin_script() {
		global $DC_Wp_Smart_Taxonomy;
		$screen = get_current_screen();
		
		if (in_array( $screen->id, array( 'post' ))) :  
		  $DC_Wp_Smart_Taxonomy->library->load_qtip_lib();
		  wp_enqueue_style('admin_css',  $DC_Wp_Smart_Taxonomy->plugin_url.'assets/admin/css/admin.css', array(), $DC_Wp_Smart_Taxonomy->version);
		endif;
		
		// Enqueue admin script and stylesheet from here
		if (in_array( $screen->id, array( 'toplevel_page_dc-WP-ST-setting-admin' ))) :   
		  $DC_Wp_Smart_Taxonomy->library->load_qtip_lib();
		  $DC_Wp_Smart_Taxonomy->library->load_upload_lib();
		  $DC_Wp_Smart_Taxonomy->library->load_colorpicker_lib();
		  $DC_Wp_Smart_Taxonomy->library->load_datepicker_lib();
		  wp_enqueue_script('admin_js', $DC_Wp_Smart_Taxonomy->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Wp_Smart_Taxonomy->version, true);
		  wp_enqueue_style('admin_css',  $DC_Wp_Smart_Taxonomy->plugin_url.'assets/admin/css/admin.css', array(), $DC_Wp_Smart_Taxonomy->version);
	  endif;
	}
}
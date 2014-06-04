<?php
/*
Plugin Name: WP Smart Taxonomy
Plugin URI: http://dualcube.com
Description: A cool new Wordpress plugin that helps you to make smart collection of posts.
Author: Dualcube
Version: 1.0.7
Author URI: http://dualcube.com
*/

if ( ! class_exists( 'WC_Dependencies' ) )
	require_once 'includes/class-dc-dependencies.php';
require_once 'includes/dc-WP-Smart-Taxonomy-core-functions.php';
require_once 'config.php';
if(!defined('ABSPATH')) exit; // Exit if accessed directly
if(!defined('DC_WP_SMART_TAXONOMY_PLUGIN_TOKEN')) exit;
if(!defined('DC_WP_SMART_TAXONOMY_TEXT_DOMAIN')) exit;

if(!class_exists('DC_Wp_Smart_Taxonomy')) {
	require_once( 'classes/class-dc-WP-Smart-Taxonomy.php' );
	global $DC_Wp_Smart_Taxonomy;
	$DC_Wp_Smart_Taxonomy = new DC_Wp_Smart_Taxonomy( __FILE__ );
	$GLOBALS['DC_Wp_Smart_Taxonomy'] = $DC_Wp_Smart_Taxonomy;
	
	// Activation Hooks
	register_activation_hook( __FILE__, array('DC_Wp_Smart_Taxonomy', 'activate_dc_WP_Smart_Taxonomy') );
	register_activation_hook( __FILE__, 'flush_rewrite_rules' );
	
	// Deactivation Hooks
	register_deactivation_hook( __FILE__, array('DC_Wp_Smart_Taxonomy', 'deactivate_dc_WP_Smart_Taxonomy') );
}
?>

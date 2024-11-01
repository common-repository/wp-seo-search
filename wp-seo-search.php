<?php
/**
* Plugin Name: WP SEO Search
* Plugin URI: https://angelcosta.com.br/wp-seo-search/
* Description: Get a better permalink for your search results page.
* Version: 1.0
* Requires at least: 5.4
* Requires PHP: 7.0
* Author: Angel Costa
* Author URI: https://angelcosta.com.br/
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wpseosearch
* Domain Path: /languages
*/

//Let's make this plugin available in all languages
load_plugin_textdomain( 'wpseosearch', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

//Creates the form in the permalinks page
add_action( 'load-options-permalink.php', 'wpseosearch_settings' );

if ( !function_exists( 'wpseosearch_settings' ) ) {
	
	function wpseosearch_settings(){
		if( isset( $_POST[ 'wpseosearch_base' ] ) ){
			update_option( 'wpseosearch_base', sanitize_key( $_POST[ 'wpseosearch_base' ] ) );
		}
		add_settings_field( 'wpseosearch_base', __( 'Search Base', 'wpseosearch' ), 'wpseosearch_input', 'permalink', 'optional' );
	}

}

if ( !function_exists( 'wpseosearch_input' ) ){
	
	function wpseosearch_input(){
		$value = get_option( 'wpseosearch_base', 'search');	
		echo '<input type="text" value="' . esc_attr( $value ) . '" name="wpseosearch_base" id="wpseosearch_base" class="regular-text" />';
	}

}

//Let's do some rewriting as soon as WP loads
add_action( 'init', 'wpseosearch_base' );

if ( !function_exists( 'wpseosearch_base' ) ){
	
	function wpseosearch_base() {
		global $wp_rewrite;
		$wp_rewrite->search_base = get_option( 'wpseosearch_base', 'search' );
		$wp_rewrite->flush_rules();
	}

}

//And also some redirecting...
add_action( 'template_redirect', 'wpseosearch_rewrite' );

if ( !function_exists( 'wpseosearch_rewrite' ) ){
	
	function wpseosearch_rewrite() {
		global $wp_rewrite;

		if ( !isset( $wp_rewrite ) || !is_object( $wp_rewrite ) || !$wp_rewrite->using_permalinks() ){
			return;
		}

		$search_base = $wp_rewrite->search_base;

		if ( is_search() && !is_admin() && strpos( $_SERVER['REQUEST_URI'], "/{$search_base}/" ) === false ) {
			wp_redirect( home_url( "/{$search_base}/" . urlencode( get_query_var( 's' ) ) ) );
			exit();
		}
	}

}

//Easy shortcut for the configuration
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'wpseosearch_settings_link');

if ( !function_exists('wpseosearch_settings_link')) {

	function wpseosearch_settings_link( $links ) {
		$links[] = '<a href="' .
			admin_url( 'options-permalink.php' ) .
			'">' . __('Settings') . '</a>';
		return $links;
	}

}

// Let's add some useful links
add_filter( 'plugin_row_meta', 'wpseosearch_row_meta', 10, 4 );
if ( !function_exists( 'wpseosearch_row_meta' )) {
	function wpseosearch_row_meta( $links, $file ) {
	    if ( $file == plugin_basename( __FILE__ ) ){
	    	$links[] = '<a href="https://angelcosta.com.br/wp-seo-search">'. __( 'Documentation' ). '</a>';
	     	$links[] = '<a href="https://angelcosta.com.br/apoie">'. __( 'Donations' ). '</a>';
	     	$links[] = '<a href="options-permalink.php">'. __( 'Settings' ). '</a>';
	     	$links[] = '<a href="https://wordpress.org/support/plugin/wp-seo-search/">'. __( 'Support' ). '</a>';
	     }
	    return $links;
	}
}
//In the next episode...

//Run after plugin is activated
//register_activation_hook( __FILE__, 'pluginprefix_function_to_run' );
//Run after plugin is deactivated
//register_deactivation_hook( __FILE__, 'pluginprefix_function_to_run' );
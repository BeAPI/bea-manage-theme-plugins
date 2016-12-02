<?php namespace BEA\Manage_Theme_Plugins;

/**
 * The purpose of the main class is to init all the plugin base code like :
 *  - Taxonomies
 *  - Post types
 *  - Shortcodes
 *  - Posts to posts relations etc.
 *  - Loading the text domain
 *
 * Class Main
 * @package BEA\Manage_Theme_Plugins
 */
class Main {
	/**
	 * Use the trait
	 */
	use Singleton;

	protected function init() {
		add_action( 'init', array( $this, 'init_translations' ) );
	}

	/**
	 * Load the plugin translation
	 */
	public function init_translations() {
		// Load translations
		load_plugin_textdomain( 'bea-manage-theme-plugins', false, BEA_MANAGE_THEME_PLUGINS_PLUGIN_DIRNAME . '/languages' );
	}
}
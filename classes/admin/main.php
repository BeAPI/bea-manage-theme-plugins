<?php namespace BEA\Manage_Theme_Plugins\Admin;

use BEA\Manage_Theme_Plugins\Singleton;

/**
 * Basic class for Admin
 *
 * Class Main
 * @package BEA\Manage_Theme_Plugins\Admin
 */
class Main {
	/**
	 * Use the trait
	 */
	use Singleton;

	static $logs_messages = array();

	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_plugins' ) );
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
	}

	/**
	 * Check plugins to be activated or deactivated.
	 *
	 * @since 1.0.1
	 *
	 * @author Benjamin Niess
	 * @author Maxime CULEA
	 */
	public function check_plugins() {
		// Get all installed plugin, active or not
		$installed_plugins = get_plugins();

		// Get only active plugins
		$active_plugins = get_option( 'active_plugins' );
		$active_plugins = empty( $active_plugins ) || ! is_array( $active_plugins ) ? array() : $active_plugins;

		/**
		 * Get all theme's plugin to be managed.
		 *
		 * @since 1.0.1
		 *
		 * @return array $theme_plugins List of actions to perform and corresponding wanted plugins.
		 * 
		 * Here is an example of how is array formatted :
		 * $theme_plugins = array(
		 *      $action1 = array(
		 *          plugin-folder1/plugin1.php,
		 *          plugin-folder2/plugin2.php
		 *      )
		 * );
		 *
		 * Actions are :
		 *  - force_activation
		 *  - suggest_activation
		 *  - force_deactivation
		 *  - suggest_deactivation
		 */
		$theme_plugins = apply_filters( 'bea\manage_theme_plugins\theme_plugins', array() );
		if ( ! is_array( $theme_plugins ) || empty( $theme_plugins ) ) {
			return;
		}

		// Init the return array (the array with all infos about plugins enabled or disabled by the function)
		$log_infos = array(
			"force_activation"     => array(),
			"force_deactivation"   => array(),
			"suggest_activation"   => array(),
			"suggest_deactivation" => array(),
			"doesntexists"         => array()
		);

		// Scan plugins
		foreach ( $theme_plugins as $action => $plugins ) {
			foreach ( $plugins as $plugin_slug ) {
				// Does the plugin exists in plugins folder ?
				if ( ! is_file( WP_PLUGIN_DIR . '/' . $plugin_slug ) || ! isset( $installed_plugins[ $plugin_slug ] ) ) {
					$log_infos['doesntexists'][] = $plugin_slug;
					continue;
				}

				// Get the plugin key inside active plugins array
				$key_in_active_plugins = array_search( $plugin_slug, $active_plugins );

				// Check which action the plugin need to do
				switch ( $action ) {
					// Plugins that need to be automatically activated
					case 'force_activation' :
						if ( $key_in_active_plugins !== false ) {
							continue;
						}
						$active_plugins[] = $plugin_slug;
						break;

					// Plugin that should be activated
					case 'suggest_activation' :
						if ( $key_in_active_plugins !== false ) {
							continue;
						}
						break;

					// Plugins that need to be automatically deativated
					case 'force_deactivation' :
						if ( $key_in_active_plugins === false ) {
							continue;
						}
						unset( $active_plugins[ $key_in_active_plugins ] );
						break;

					// Plugin that should not be activated
					case 'suggest_deactivation' :
						if ( $key_in_active_plugins === false ) {
							continue;
						}
						break;
				}

				// Add plugin for logs
				$log_infos[ $action ][] = $plugin_slug;
			}
		}

		update_option( 'active_plugins', $active_plugins );

		if ( empty( $log_infos ) ) {
			return;
		}

		self::$logs_messages = $log_infos;
	}

	/**
	 * On the plugin's page, show notices with plugin suggestions
	 *
	 * @since 1.0.1
	 *
	 * @author Benjamin Niess
	 * @author Maxime CULEA
	 */
	public function show_notices() {
		// Don't show message on other screens than plugins
		$current_screen = get_current_screen();
		if ( empty( $current_screen ) || ! is_object( $current_screen ) || ! isset( $current_screen->base ) || $current_screen->base != 'plugins' ) {
			return;
		}

		if ( empty( self::$logs_messages ) || ! is_array( self::$logs_messages ) ) {
			return;
		}

		// Display errors with required plugins that are not installed
		if ( isset( self::$logs_messages['doesntexists'] ) && is_array( self::$logs_messages['doesntexists'] ) ) {
			foreach ( self::$logs_messages['doesntexists'] as $message ) {
				echo '<div class="error"><p>' . sprintf( __( 'The plugin %s is not installed in your WordPress instance and is required to use this theme.', 'bea-manage-theme-plugins' ), '<strong>' . $message . '</strong>' ) . '</p></div>';
			}
		}

		// Display notice for plugins disable suggestions
		if ( isset( self::$logs_messages['suggest_deactivation'] ) && is_array( self::$logs_messages['suggest_deactivation'] ) ) {
			foreach ( self::$logs_messages['suggest_deactivation'] as $message ) {
				echo '<div class="updated"><p>' . sprintf( __( 'The plugin %s should be deactivated with this theme.', 'bea-manage-theme-plugins' ), '<strong>' . $message . '</strong>' ) . '</p></div>';
			}
		}

		// Display notice for plugins activation suggestions
		if ( isset( self::$logs_messages['suggest_activation'] ) && is_array( self::$logs_messages['suggest_activation'] ) ) {
			foreach ( self::$logs_messages['suggest_activation'] as $message ) {
				echo '<div class="updated"><p>' . sprintf( __( 'The plugin %s should be activated with this theme.', 'bea-manage-theme-plugins' ), '<strong>' . $message . '</strong>' ) . '</p></div>';
			}
		}
	}
}
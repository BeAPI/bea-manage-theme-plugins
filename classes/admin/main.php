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

	private $_dispatch_plugins = [];

	public function __construct() {
		add_action( 'admin_init', [ $this, 'check_plugins' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

		add_filter( 'plugin_action_links', [ $this, 'plugin_action_links' ], 10, 2 );
	}

	/**
	 * Remove actions forbidden by rules
	 *
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
	public function plugin_action_links( $actions, $plugin_file ) {
		if ( isset( $this->_dispatch_plugins['force_activation'][ $plugin_file ] ) ) {
			$actions['deactivate'] = __( 'Forced activation' );
		}

		if ( isset( $this->_dispatch_plugins['force_deactivation'][ $plugin_file ] ) ) {
			$actions['activate'] = __( 'Forced deactivation' );
		}

		return $actions;
	}

	/**
	 * Get a plugin object by plugin shortname (same algo as WP-CLI)
	 *
	 * @param string $name
	 *
	 * @return object|false
	 */
	public function get_plugin_obj( $name ) {
		foreach ( apply_filters( 'all_plugins', get_plugins() ) as $file => $_ ) {
			if ( $file === "$name.php" ||
			     ( $name && $file === $name ) ||
			     ( dirname( $file ) === $name && $name !== '.' ) ) {
				return (object) compact( 'name', 'file' );
			}
		}

		return false;
	}

	/**
	 * Check plugins to be activated or deactivated.
	 *
	 * @since 1.0.1
	 *
	 * @return boolean
	 *
	 * @author Benjamin Niess
	 * @author Maxime CULEA
	 */
	public function check_plugins() {
		/**
		 * Get all theme's plugin to be managed.
		 *
		 * @since 1.0.1
		 *
		 * @return array $theme_plugins List of actions to perform and corresponding wanted plugins.
		 *
		 * Here is an example of how is array formatted :
		 * $theme_plugins = [
		 *     'plugin-folder1/plugin1.php' => 'force_activation',
		 *     'plugin2.php' => 'force_deactivation'
		 * ];
		 *
		 * Actions are :
		 *  - force_activation
		 *  - suggest_activation
		 *  - force_deactivation
		 *  - suggest_deactivation
		 */
		$theme_plugins = apply_filters( 'bea\manage_theme_plugins\theme_plugins', [] );
		if ( ! is_array( $theme_plugins ) || empty( $theme_plugins ) ) {
			return false;
		}

		// Init the return array (the array with all infos about plugins enabled or disabled by the function)
		$log_infos = [
			'force_activation'     => [],
			'force_deactivation'   => [],
			'suggest_activation'   => [],
			'suggest_deactivation' => [],
			'doesntexists'         => [],
		];

		// Scan plugins
		foreach ( $theme_plugins as $plugin_slug => $action ) {
			if ( empty( $action ) ) {
				continue;
			}

			// Get plugin data from human slug
			$plugin_data = $this->get_plugin_obj( $plugin_slug );

			// Add plugin for logs
			if ( empty( $plugin_data ) ) {
				$action = 'doesntexists';
			}

			// Check which action the plugin need to do
			switch ( $action ) {
				// Plugins that need to be automatically activated
				case 'force_activation' :
					activate_plugin( $plugin_data->file );
					break;

				// Plugin that should be activated
				case 'suggest_activation' :
					break;

				// Plugins that need to be automatically deativated
				case 'force_deactivation' :
					deactivate_plugins( array( $plugin_data->file ) );
					break;

				// Plugin that should not be activated
				case 'suggest_deactivation' :
					break;
			}

			// Append to logs
			$log_infos[ $action ][ $plugin_data->file ] = $plugin_slug;
		}

		if ( ! empty( $log_infos ) ) {
			$this->_dispatch_plugins = $log_infos;
		}

		return true;
	}

	/**
	 * On the plugin's page, show notices with plugin suggestions
	 *
	 * @since 1.0.1
	 *
	 * @author Benjamin Niess
	 * @author Maxime CULEA
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_plugins' ) ) {
			return;
		}

		// Don't show message on other screens than plugins
		$current_screen = get_current_screen();
		if ( empty( $current_screen ) || ! is_object( $current_screen ) || ! isset( $current_screen->base ) || $current_screen->base !== 'plugins' ) {
			return;
		}

		if ( empty( $this->_dispatch_plugins ) || ! is_array( $this->_dispatch_plugins ) ) {
			return;
		}

		// Display errors with required plugins that are not installed
		if ( ! empty( $this->_dispatch_plugins['doesntexists'] ) && is_array( $this->_dispatch_plugins['doesntexists'] ) ) {
			echo '<div class="error"><p>' . sprintf( __( 'These plugins %s are not installed in your WordPress instance.', 'bea-manage-theme-plugins' ), '<strong>' . implode( ', ', $this->_dispatch_plugins['doesntexists'] ) . '</strong>' ) . '</p></div>';
		}

		// Display notice for plugins disable suggestions
		if ( ! empty( $this->_dispatch_plugins['suggest_deactivation'] ) && is_array( $this->_dispatch_plugins['suggest_deactivation'] ) ) {
			echo '<div class="updated"><p>' . sprintf( __( 'These plugins %s should be deactivated with this theme.', 'bea-manage-theme-plugins' ), '<strong>' . implode( ', ', $this->_dispatch_plugins['suggest_deactivation'] ) . '</strong>' ) . '</p></div>';
		}

		// Display notice for plugins activation suggestions
		if ( ! empty( $this->_dispatch_plugins['suggest_activation'] ) && is_array( $this->_dispatch_plugins['suggest_activation'] ) ) {
			echo '<div class="updated"><p>' . sprintf( __( 'These plugins %s should be activated with this theme.', 'bea-manage-theme-plugins' ), '<strong>' . implode( ', ', $this->_dispatch_plugins['suggest_activation'] ) . '</strong>' ) . '</p></div>';
		}
	}
}
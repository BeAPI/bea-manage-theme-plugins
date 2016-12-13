<?php Namespace BEA\Manage_Theme_Plugins\Admin;

class Main_CLI extends \WP_CLI_Command {

	/**
	 * Activate/Deactive foreach site, theme's dependencies.
	 *
	 * ## EXAMPLES
	 * wp plugins manage_all
	 *
	 * @since 1.1.0
	 *
	 * @author Maxime CULEA
	 *
	 * @synopsis
	 */
	function manage_all() {
		global $wp_version;

		// Default
		$sites       = [];
		$found_sites = 0;

		if ( version_compare( $wp_version, '4.6', '>=' ) && class_exists( 'WP_Site_Query' ) ) {
			// With WP_Site_Query
			$site_query = new \WP_Site_Query();
			$sites      = $site_query->get_sites();

			$found_sites = count( $sites );
		} else {
			// Without WP_Site_Query
		}

		if ( empty( $sites ) ) {
			\WP_CLI::error( 'Not sites.' );
			return;
		}

		\WP_CLI::log( 'Starting theme\'s plugins management.' );
		foreach ( $sites as $site ) {
			\WP_CLI::log( \WP_CLI::runcommand( sprintf( 'plugins manage_single --url=%s%s', $site->domain, $site->path ), [ 'return' => true, 'exit_error' => false ] ) );
		}

		\WP_CLI::success( sprintf( 'Management of %s site(s) is finish !', $found_sites ) );
	}

	/**
	 * Activate/Deactive foreach site, theme's dependencies.
	 *
	 * ## EXAMPLES
	 * wp plugins manage_single --url=
	 *
	 * @since 1.1.0
	 *
	 * @author Maxime CULEA
	 *
	 * @synopsis
	 */
	public function manage_single() {
		$site_id = get_current_blog_id();
		$site    = \WP_Site::get_instance( $site_id );

		\WP_CLI::log( sprintf( 'Managing %s%s theme\'s plugins.', $site->domain, $site->path ) );
		$main    = Main::get_instance();
		$plugins = $main->check_plugins();

		self::activate_deactivate( 'activate', 'force_activation', $plugins, $site );
		self::activate_deactivate( 'deactivate', 'force_deactivation', $plugins, $site );

		\WP_CLI::success( sprintf( '%s%s theme\'s plugins managed !', $site->domain, $site->path ) );
	}

	/**
	 * Private method for activation / deactivation the given plugins
	 *
	 * @param $action
	 * @param $key
	 * @param $plugins
	 * @param $site
	 *
	 * @author Maxime CULEA
	 */
	private function activate_deactivate( $action, $key, $plugins, $site ) {
		if ( ! isset( $plugins[$key] ) || empty( $plugins[$key] ) ) {
			return;
		}

		foreach ( $plugins[$key] as $plugin ) {
			$path   = explode( '/', $plugin );
			$return = \WP_CLI::runcommand(
				sprintf( 'plugin %s %s --url=%s%s', $action, $path[0], $site->domain, $site->path ),
				[ 'return' => true, 'exit_error' => false ]
			);

			\WP_CLI::log( sprintf( '%s : %s', $path[0], $return ) );

			// TODO : Check for success, before doing actions !

			/** This actions are documented in wp-admin/includes/plugin.php */
			do_action( $action . '_plugin', $plugin, false );
			do_action( $action . '_' . $plugin, false );
			do_action( $action . 'ed_plugin', $plugin, false );
		}
	}
}
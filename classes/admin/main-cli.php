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
		$sites       = array();
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

		\WP_Cli::log( 'Starting theme\'s plugins management.' );
		foreach ( $sites as $site ) {
			\WP_CLI::runcommand( 'plugins manage_single --url=' . $site->domain . $site->path, array( 'return' => true ) );
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

		if ( isset( $plugins['force_activation'] ) && ! empty( $plugins['force_activation'] ) ) {
			foreach ( $plugins['force_activation'] as $plugin ) {
				$path = explode( $plugin, '/' );
				\WP_CLI::runcommand( sprintf( 'plugin activate %s --url=%s%s%s', $path[0] ), array( 'return' => true ) );
			}
		}

		if ( isset( $plugins['force_deactivation'] ) && ! empty( $plugins['force_deactivation'] ) ) {
			foreach ( $plugins['force_deactivation'] as $plugin ) {
				$path = explode( $plugin, '/' );
				\WP_CLI::runcommand( sprintf( 'plugin deactivate %s --url=%s%s', $path[0] ), array( 'return' => true ) );
			}
		}
	}
}
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
			\WP_CLI::log( \WP_CLI::runcommand( sprintf( 'plugins manage_single --url=%s%s', $site->domain, $site->path ), array( 'return' => true ) ) );
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
				$path   = explode( '/', $plugin );
				$return = \WP_CLI::runcommand(
					sprintf( 'plugin activate %s --url=%s%s', $path[0], $site->domain, $site->path ),
					array( 'return' => true, 'exit_error' => false )
				);

				\WP_CLI::log( sprintf( '%s : %s', $path[0], $return ) );

				/** This actions are documented in wp-admin/includes/plugin.php */
				do_action( 'activate_plugin', $plugin, false );
				do_action( 'activate_' . $plugin, false );
				do_action( 'activated_plugin', $plugin, false );
			}
		}

		if ( isset( $plugins['force_deactivation'] ) && ! empty( $plugins['force_deactivation'] ) ) {
			foreach ( $plugins['force_deactivation'] as $plugin ) {
				$path   = explode( '/', $plugin );
				$return = \WP_CLI::runcommand(
					sprintf( 'plugin deactivate %s --url=%s%s', $path[0], $site->domain, $site->path ),
					array( 'return' => true, 'exit_error' => false )
				);

				\WP_CLI::log( sprintf( '%s : %s', $path[0], $return ) );

				/** This actions are documented in wp-admin/includes/plugin.php */
				do_action( 'deactivate_plugin', $plugin, false );
				do_action( 'deactivate_' . $plugin, false );
				do_action( 'deactivated_plugin', $plugin, false );
			}
		}
	}
}
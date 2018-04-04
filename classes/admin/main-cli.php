<?php Namespace BEA\Manage_Theme_Plugins\Admin;

class Main_CLI extends \WP_CLI_Command {

	public function manage_all() {
		\WP_CLI::success( 'Deprecated' );
	}

	/**
	 * Activate/deactivate for one site, theme's dependencies.
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

		$main    = Main::get_instance();
		$main->check_plugins();

		\WP_CLI::success( sprintf( '%s%s theme\'s plugins managed !', $site->domain, $site->path ) );
	}
}
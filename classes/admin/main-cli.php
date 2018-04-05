<?php Namespace BEA\Manage_Theme_Plugins\Admin;

class Main_CLI extends \WP_CLI_Command {
	/**
	 * Activate/deactivate for one site, theme's dependencies.
	 *
	 * ## EXAMPLES
	 * wp plugin theme_management --url=
	 *
	 * @since 1.1.0
	 *
	 * @author Maxime CULEA
	 *
	 * @synopsis
	 */
	public function theme_management() {
		$site_id = get_current_blog_id();
		$site    = \WP_Site::get_instance( $site_id );

		$main    = Main::get_instance();
		$main->check_plugins();

		\WP_CLI::success( sprintf( '%s%s theme\'s plugins managed !', $site->domain, $site->path ) );
	}
}
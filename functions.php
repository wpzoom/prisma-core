<?php
/**
 * Theme functions and definitions.
 *
 * @package Prisma Core
 * @author  Prisma Core Team
 * @since   1.0.0
 */

/**
 * Main Prisma Core class.
 *
 * @since 1.0.0
 */
final class Prisma_Core {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Theme version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $version = '1.4.0';

	/**
	 * Theme options.
	 *
	 * @since 1.0.0
	 * @var Prisma_Core_Options
	 */
	public $options;

	/**
	 * Fonts.
	 *
	 * @since 1.0.0
	 * @var Prisma_Core_Fonts
	 */
	public $fonts;

	/**
	 * Icons.
	 *
	 * @since 1.0.0
	 * @var Prisma_Core_Icons
	 */
	public $icons;

	/**
	 * Customizer.
	 *
	 * @since 1.0.0
	 * @var Prisma_Core_Customizer
	 */
	public $customizer;

	/**
	 * Admin.
	 *
	 * @since 1.0.0
	 * @var Prisma_Core_Admin
	 */
	public $admin;

	/**
	 * Main Prisma Core Instance.
	 *
	 * Insures that only one instance of Prisma Core exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0.0
	 * @return Prisma Core
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Core ) ) {
			self::$instance = new Prisma_Core();

			self::$instance->constants();
			self::$instance->includes();
			self::$instance->objects();

			// Hook now that all of the Prisma Core stuff is loaded.
			do_action( 'prisma_core_loaded' );
		}
		return self::$instance;
	}

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Setup constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function constants() {

		if ( ! defined( 'PRISMA_CORE_THEME_VERSION' ) ) {
			define( 'PRISMA_CORE_THEME_VERSION', $this->version );
		}

		if ( ! defined( 'PRISMA_CORE_THEME_URI' ) ) {
			define( 'PRISMA_CORE_THEME_URI', get_parent_theme_file_uri() );
		}

		if ( ! defined( 'PRISMA_CORE_THEME_PATH' ) ) {
			define( 'PRISMA_CORE_THEME_PATH', get_parent_theme_file_path() );
		}

	}

	/**
	 * Include files.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function includes() {

		require_once PRISMA_CORE_THEME_PATH . '/inc/common.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/deprecated.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/helpers.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/widgets.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/template-tags.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/template-parts.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/icon-functions.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/breadcrumbs.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/class-prisma-core-dynamic-styles.php';

		// Core.
		require_once PRISMA_CORE_THEME_PATH . '/inc/core/class-prisma-core-options.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/core/class-prisma-core-enqueue-scripts.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/core/class-prisma-core-fonts.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/core/class-prisma-core-theme-setup.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/core/class-prisma-core-db-updater.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/core/class-prisma-core-migration.php';

		// Compatibility.
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/woocommerce/class-prisma-core-woocommerce.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/socialsnap/class-prisma-core-socialsnap.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-wpforms.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-jetpack.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-endurance.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-beaver-themer.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-elementor.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-elementor-pro.php';
		require_once PRISMA_CORE_THEME_PATH . '/inc/compatibility/class-prisma-core-hfe.php';

		if ( is_admin() ) {
			require_once PRISMA_CORE_THEME_PATH . '/inc/utilities/class-prisma-core-plugin-utilities.php';
			require_once PRISMA_CORE_THEME_PATH . '/inc/admin/class-prisma-core-admin.php';
		}

		// Customizer.
		require_once PRISMA_CORE_THEME_PATH . '/inc/customizer/class-prisma-core-customizer.php';
	}

	/**
	 * Setup objects to be used throughout the theme.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function objects() {

		prisma_core()->options    = new Prisma_Core_Options();
		prisma_core()->fonts      = new Prisma_Core_Fonts();
		prisma_core()->icons      = new Prisma_Core_Icons();
		prisma_core()->customizer = new Prisma_Core_Customizer();

		if ( is_admin() ) {
			prisma_core()->admin = new Prisma_Core_Admin();
		}
	}
}

/**
 * The function which returns the one Prisma Core instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $prisma-core = prisma_core(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function prisma_core() {
	return Prisma_Core::instance();
}

prisma_core();

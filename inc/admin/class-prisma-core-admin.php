<?php
/**
 * Admin class.
 *
 * This class ties together all admin classes.
 *
 * @package     Prisma Core
 * @author      Prisma Core Team
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Prisma_Core_Admin' ) ) :

	/**
	 * Admin Class
	 */
	class Prisma_Core_Admin {

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			/**
			 * Include admin files.
			 */
			$this->includes();

			/**
			 * Load admin assets.
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

			/**
			 * Add filters for WordPress header and footer text.
			 */
			add_filter( 'update_footer', array( $this, 'filter_update_footer' ), 50 );
			add_filter( 'admin_footer_text', array( $this, 'filter_admin_footer_text' ), 50 );

			/**
			 * Admin page header.
			 */
			add_action( 'in_admin_header', array( $this, 'admin_header' ), 100 );

			/**
			 * Admin page footer.
			 */
			add_action( 'in_admin_footer', array( $this, 'admin_footer' ), 100 );

			/**
			 * Add notices.
			 */
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			/**
			 * After admin loaded
			 */
			do_action( 'prisma_core_admin_loaded' );
		}

		/**
		 * Includes files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {

			/**
			 * Include helper functions.
			 */
			require_once PRISMA_CORE_THEME_PATH . '/inc/admin/helpers.php'; // phpcs:ignore

			/**
			 * Include Prisma Core welcome page.
			 */
			require_once PRISMA_CORE_THEME_PATH . '/inc/admin/class-prisma-core-dashboard.php'; // phpcs:ignore

			/**
			 * Include Prisma Core meta boxes.
			 */
			require_once PRISMA_CORE_THEME_PATH . '/inc/admin/metabox/class-prisma-core-meta-boxes.php'; // phpcs:ignore
		}

		/**
		 * Load our required assets on admin pages.
		 *
		 * @since 1.0.0
		 * @param string $hook it holds the information about the current page.
		 */
		public function load_assets( $hook ) {

			/**
			 * Do not enqueue if we are not on one of our pages.
			 */
			if ( ! prisma_core_is_admin_page( $hook ) ) {
				return;
			}

			// Script debug.
			$prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'dev/' : '';
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			/**
			 * Enqueue admin pages stylesheet.
			 */
			wp_enqueue_style(
				'prisma-core-admin-styles',
				PRISMA_CORE_THEME_URI . '/inc/admin/assets/css/prisma-core-admin' . $suffix . '.css',
				false,
				PRISMA_CORE_THEME_VERSION
			);

			/**
			 * Enqueue admin pages script.
			 */
			wp_enqueue_script(
				'prisma-core-admin-script',
				PRISMA_CORE_THEME_URI . '/inc/admin/assets/js/' . $prefix . 'prisma-core-admin' . $suffix . '.js',
				array( 'jquery', 'wp-util', 'updates' ),
				PRISMA_CORE_THEME_VERSION,
				true
			);

			/**
			 * Localize admin strings.
			 */
			$texts = array(
				'install'               => esc_html__( 'Install', 'prisma-core' ),
				'install-inprogress'    => esc_html__( 'Installing...', 'prisma-core' ),
				'activate-inprogress'   => esc_html__( 'Activating...', 'prisma-core' ),
				'deactivate-inprogress' => esc_html__( 'Deactivating...', 'prisma-core' ),
				'active'                => esc_html__( 'Active', 'prisma-core' ),
				'retry'                 => esc_html__( 'Retry', 'prisma-core' ),
				'please_wait'           => esc_html__( 'Please Wait...', 'prisma-core' ),
				'importing'             => esc_html__( 'Importing... Please Wait...', 'prisma-core' ),
				'currently_processing'  => esc_html__( 'Currently processing: ', 'prisma-core' ),
				'import'                => esc_html__( 'Import', 'prisma-core' ),
				'import_demo'           => esc_html__( 'Import Demo', 'prisma-core' ),
				'importing_notice'      => esc_html__( 'The demo importer is still working. Closing this window may result in failed import.', 'prisma-core' ),
				'import_complete'       => esc_html__( 'Import Complete!', 'prisma-core' ),
				'import_complete_desc'  => esc_html__( 'The demo has been imported.', 'prisma-core' ) . ' <a href="' . esc_url( get_home_url() ) . '">' . esc_html__( 'Visit site.', 'prisma-core' ) . '</a>',
			);

			$strings = array(
				'ajaxurl'       => esc_url( admin_url( 'admin-ajax.php' ) ),
				'wpnonce'       => wp_create_nonce( 'prisma_core_nonce' ),
				'texts'         => $texts,
				'color_pallete' => array( '#3857f1', '#06cca6', '#2c2e3a', '#e4e7ec', '#f0b849', '#ffffff', '#000000' ),
			);

			$strings = apply_filters( 'prisma_core_admin_strings', $strings );

			wp_localize_script( 'prisma-core-admin-script', 'prisma_core_strings', $strings );
		}

		/**
		 * Filters WordPress footer right text to hide all text.
		 *
		 * @since 1.0.0
		 * @param string $text Text that we're going to replace.
		 */
		public function filter_update_footer( $text ) {

			$base = get_current_screen()->base;

			/**
			 * Only do this if we are on one of our plugin pages.
			 */
			if ( prisma_core_is_admin_page( $base ) ) {
				return apply_filters( 'prisma_core_footer_version', esc_html__( 'Prisma Core Theme', 'prisma-core' ) . ' ' . PRISMA_CORE_THEME_VERSION . '<br/><a href="' . esc_url( 'https://x.com/wpzoom' ) . '" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a><a href="' . esc_url( 'https://facebook.com/wpzoom' ) . '" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a>' );
			} else {
				return $text;
			}
		}

		/**
		 * Filter WordPress footer left text to display our text.
		 *
		 * @since 1.0.0
		 * @param string $text Text that we're going to replace.
		 */
		public function filter_admin_footer_text( $text ) {

			if ( prisma_core_is_admin_page() ) {
				return '';
			}

			return $text;
		}

		/**
		 * Outputs the page admin header.
		 *
		 * @since 1.0.0
		 */
		public function admin_header() {

			$base = get_current_screen()->base;

			if ( ! prisma_core_is_admin_page( $base ) ) {
				return;
			}
			?>

			<div id="prisma-core-header">
				<div class="pr-container">

					<a href="<?php echo esc_url( admin_url( 'admin.php?page=prisma-core-dashboard' ) ); ?>" class="prisma-core-logo">
						<?php esc_html_e( 'Prisma Core', 'prisma-core' ); ?>
					</a>

					<span class="prisma-core-header-action">
						<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'prisma-core' ); ?></a>
						<a href="<?php echo esc_url( 'https://www.wpzoom.com/documentation/prisma-core' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help Articles', 'prisma-core' ); ?></a>
					</span>

				</div>
			</div><!-- END #prisma-core-header -->
			<?php
		}

		/**
		 * Outputs the page admin footer.
		 *
		 * @since 1.0.0
		 */
		public function admin_footer() {

			$base = get_current_screen()->base;

			if ( ! prisma_core_is_admin_page( $base ) || prisma_core_is_admin_page( $base, 'prisma_core_wizard' ) ) {
				return;
			}
			?>
			<div id="prisma-core-footer">
			<ul>
				<li><a href="<?php echo esc_url( 'https://www.wpzoom.com/documentation/prisma-core' ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Help Articles', 'prisma-core' ); ?></span></span></a></li>
				<li><a href="<?php echo esc_url( 'https://www.facebook.com/groups/wpzoom/' ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Join Facebook Group', 'prisma-core' ); ?></span></span></a></li>
				<!-- <li><a href="<?php echo esc_url( 'https://wordpress.org/support/theme/prisma-core/reviews/#new-post' ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-heart" aria-hidden="true"></span><span><?php esc_html_e( 'Leave a Review', 'prisma-core' ); ?></span></a></li> -->
			</ul>
			</div><!-- END #prisma-core-footer -->

			<?php
		}

		/**
		 * Admin Notices
		 *
		 * @since 1.0.0
		 */
		public function admin_notices() {

			$screen = get_current_screen();

			// Display on Dashboard, Themes and Prisma Core admin pages.
			if ( ! in_array( $screen->base, array( 'dashboard', 'themes' ), true ) && ! prisma_core_is_admin_page() ) {
				return;
			}

			// Display if not dismissed and not on Prisma Core plugins page.
			if ( ! prisma_core_is_notice_dismissed( 'prisma_core_notice_recommended-plugins' ) && ! prisma_core_is_admin_page( false, 'prisma-core-plugins' ) ) {

				$plugins = prisma_core_plugin_utilities()->get_recommended_plugins();
				$plugins = prisma_core_plugin_utilities()->get_deactivated_plugins( $plugins );

				$plugin_list = '';

				if ( is_array( $plugins ) && ! empty( $plugins ) ) {

					foreach ( $plugins as $slug => $plugin ) {

						$url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . esc_attr( $slug ) . '&TB_iframe=true&width=990&height=500' );

						$plugin_list .= '<a href="' . esc_url( $url ) . '" class="thickbox">' . esc_html( $plugin['name'] ) . '</a>, ';
					}

					wp_enqueue_script( 'plugin-install' );
					add_thickbox();

					$plugin_list = trim( $plugin_list, ', ' );

					/* translators: %1$s <strong> tag, %2$s </strong> tag */
					$message = sprintf( wp_kses( __( 'Prisma Core theme recommends the following plugins: %1$s.', 'prisma-core' ), prisma_core_get_allowed_html_tags() ), $plugin_list );

					$navigation_items = prisma_core_dashboard()->get_navigation_items();

					prisma_core_print_notice(
						array(
							'type'        => 'info',
							'message'     => $message,
							'message_id'  => 'recommended-plugins',
							'expires'     => 7 * 24 * 60 * 60,
							'action_link' => $navigation_items['plugins']['url'],
							'action_text' => esc_html__( 'Install Now', 'prisma-core' ),
						)
					);
				}
			}

		}
	}
endif;

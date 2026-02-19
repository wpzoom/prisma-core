<?php
/**
 * Prisma Core About page class.
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

if ( ! class_exists( 'Prisma_Core_Dashboard' ) ) :
	/**
	 * Prisma Core Dashboard page class.
	 */
	final class Prisma_Core_Dashboard {

		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance;

		/**
		 * Main Prisma Core Dashboard Instance.
		 *
		 * @since 1.0.0
		 * @return Prisma_Core_Dashboard
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Core_Dashboard ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			/**
			 * Register admin menu item under Appearance menu item.
			 */
			add_action( 'admin_menu', array( $this, 'add_to_menu' ), 10 );
			add_filter( 'submenu_file', array( $this, 'highlight_submenu' ) );

			/**
			 * Ajax activate & deactivate plugins.
			 */
			add_action( 'wp_ajax_prisma-core-plugin-activate', array( $this, 'activate_plugin' ) );
			add_action( 'wp_ajax_prisma-core-plugin-deactivate', array( $this, 'deactivate_plugin' ) );
		}

		/**
		 * Register our custom admin menu item.
		 *
		 * @since 1.0.0
		 */
		public function add_to_menu() {

			/**
			 * Dashboard page.
			 */
			add_theme_page(
				esc_html__( 'Prisma Core Theme', 'prisma-core' ),
				'Prisma Core Theme',
				apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ),
				'prisma-core-dashboard',
				array( $this, 'render_dashboard' )
			);

			/**
			 * Plugins page.
			 */
			add_theme_page(
				esc_html__( 'Plugins', 'prisma-core' ),
				'Plugins',
				apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ),
				'prisma-core-plugins',
				array( $this, 'render_plugins' )
			);

			// Hide from admin navigation.
			remove_submenu_page( 'themes.php', 'prisma-core-plugins' );

			// Set page title before admin-header.php runs strip_tags() on it (PHP 8.x compat).
			add_action( 'load-appearance_page_prisma-core-plugins', function() {
				global $title;
				$title = __( 'Plugins', 'prisma-core' );
			} );

			/**
			 * Changelog page.
			 */
			add_theme_page(
				esc_html__( 'Changelog', 'prisma-core' ),
				'Changelog',
				apply_filters( 'prisma_core_manage_cap', 'edit_theme_options' ),
				'prisma-core-changelog',
				array( $this, 'render_changelog' )
			);

			// Hide from admin navigation.
			remove_submenu_page( 'themes.php', 'prisma-core-changelog' );

			// Set page title before admin-header.php runs strip_tags() on it (PHP 8.x compat).
			add_action( 'load-appearance_page_prisma-core-changelog', function() {
				global $title;
				$title = __( 'Changelog', 'prisma-core' );
			} );
		}

		/**
		 * Render dashboard page.
		 *
		 * @since 1.0.0
		 */
		public function render_dashboard() {

			// Render dashboard navigation.
			$this->render_navigation();

			?>
			<div class="pr-container">

				<div class="prisma-core-section-title">
					<h2 class="prisma-core-section-title"><?php esc_html_e( 'Getting Started', 'prisma-core' ); ?></h2>
				</div><!-- END .prisma-core-section-title -->

				<div class="prisma-core-section prisma-core-columns">

					<div class="prisma-core-column">
						<div class="prisma-core-box">
							<h4><i class="dashicons dashicons-admin-plugins"></i><?php esc_html_e( 'Install Plugins', 'prisma-core' ); ?></h4>
							<p><?php esc_html_e( 'Explore recommended plugins. These free plugins provide additional features and customization options.', 'prisma-core' ); ?></p>

							<div class="prisma-core-buttons">
								<a href="<?php echo esc_url( menu_page_url( 'prisma-core-plugins', false ) ); ?>" class="pr-btn secondary" role="button"><?php esc_html_e( 'Install Plugins', 'prisma-core' ); ?></a>
							</div><!-- END .prisma-core-buttons -->
						</div>
					</div>

					<div class="prisma-core-column">
						<div class="prisma-core-box">
							<h4><i class="dashicons dashicons-layout"></i><?php esc_html_e( 'Start with a Template', 'prisma-core' ); ?></h4>
							<p><?php esc_html_e( 'Don&rsquo;t want to start from scratch? Import a pre-built demo website in 1-click and get a head start.', 'prisma-core' ); ?></p>

							<div class="prisma-core-buttons plugins">

								<?php
								if ( file_exists( WP_PLUGIN_DIR . '/prisma-companion/prisma-companion.php' ) && is_plugin_inactive( 'prisma-companion/prisma-companion.php' ) ) {
									$class       = 'pr-btn secondary';
									$button_text = __( 'Activate Prisma Companion', 'prisma-core' );
									$link        = '#';
									$data        = ' data-plugin="prisma-companion" data-action="activate" data-redirect="' . esc_url( admin_url( 'admin.php?page=prisma-core-demo-library' ) ) . '"';
								} elseif ( ! file_exists( WP_PLUGIN_DIR . '/prisma-companion/prisma-companion.php' ) ) {
									$class       = 'pr-btn secondary';
									$button_text = __( 'Install Prisma Companion', 'prisma-core' );
									$link        = '#';
									$data        = ' data-plugin="prisma-companion" data-action="install" data-redirect="' . esc_url( admin_url( 'admin.php?page=prisma-core-demo-library' ) ) . '"';
								} else {
									$class       = 'pr-btn secondary active';
									$button_text = __( 'Browse Demos', 'prisma-core' );
									$link        = admin_url( 'admin.php?page=prisma-core-demo-library' );
									$data        = '';
								}

								printf(
									'<a class="%1$s" %2$s %3$s role="button"> %4$s </a>',
									esc_attr( $class ),
									isset( $link ) ? 'href="' . esc_url( $link ) . '"' : '',
									$data, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									esc_html( $button_text )
								);
								?>

							</div><!-- END .prisma-core-buttons -->
						</div>
					</div>

					<div class="prisma-core-column">
						<div class="prisma-core-box">
							<h4><i class="dashicons dashicons-palmtree"></i><?php esc_html_e( 'Upload Your Logo', 'prisma-core' ); ?></h4>
							<p><?php esc_html_e( 'Kick off branding your new site by uploading your logo. Simply upload your logo and customize as you need.', 'prisma-core' ); ?></p>

							<div class="prisma-core-buttons">
								<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ); ?>" class="pr-btn secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upload Logo', 'prisma-core' ); ?></a>
							</div><!-- END .prisma-core-buttons -->
						</div>
					</div>

					<div class="prisma-core-column">
						<div class="prisma-core-box">
							<h4><i class="dashicons dashicons-welcome-widgets-menus"></i><?php esc_html_e( 'Change Menus', 'prisma-core' ); ?></h4>
							<p><?php esc_html_e( 'Customize menu links and choose what&rsquo;s displayed in available theme menu locations.', 'prisma-core' ); ?></p>

							<div class="prisma-core-buttons">
								<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="pr-btn secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Go to Menus', 'prisma-core' ); ?></a>
							</div><!-- END .prisma-core-buttons -->
						</div>
					</div>

					<div class="prisma-core-column">
						<div class="prisma-core-box">
							<h4><i class="dashicons dashicons-art"></i><?php esc_html_e( 'Change Colors', 'prisma-core' ); ?></h4>
							<p><?php esc_html_e( 'Replace the default theme colors and make your website color scheme match your brand design.', 'prisma-core' ); ?></p>

							<div class="prisma-core-buttons">
								<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=prisma_core_section_colors' ) ); ?>" class="pr-btn secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Change Colors', 'prisma-core' ); ?></a>
							</div><!-- END .prisma-core-buttons -->
						</div>
					</div>

					<div class="prisma-core-column">
						<div class="prisma-core-box">
							<h4><i class="dashicons dashicons-editor-help"></i><?php esc_html_e( 'Need Help?', 'prisma-core' ); ?></h4>
							<p><?php esc_html_e( 'Head over to our site to learn more about the Prisma Core theme, read help articles and get support.', 'prisma-core' ); ?></p>

							<div class="prisma-core-buttons">
								<a href="https://www.wpzoom.com/documentation/prisma-core" target="_blank" rel="noopener noreferrer" class="pr-btn secondary"><?php esc_html_e( 'Help Articles', 'prisma-core' ); ?></a>
							</div><!-- END .prisma-core-buttons -->
						</div>
					</div>
				</div><!-- END .prisma-core-section -->

				<div class="prisma-core-section large-section">
					<div class="prisma-core-hero">
						<img src="<?php echo esc_url( PRISMA_CORE_THEME_URI . '/assets/images/pr-customize.svg' ); ?>" alt="<?php echo esc_html( 'Customize' ); ?>" />
					</div>

					<h2><?php esc_html_e( 'Let‘s customize your website', 'prisma-core' ); ?></h2>
					<p><?php esc_html_e( 'There are many changes you can make to customize your website. Explore Prisma Core customization options and make it unique.', 'prisma-core' ); ?></p>

					<div class="prisma-core-buttons">
						<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="pr-btn primary large-button"><?php esc_html_e( 'Start Customizing', 'prisma-core' ); ?></a>
					</div><!-- END .prisma-core-buttons -->

				</div><!-- END .prisma-core-section -->

				<?php do_action( 'prisma_core_about_content_after' ); ?>

			</div><!-- END .pr-container -->

			<?php
		}

		/**
		 * Render the recommended plugins page.
		 *
		 * @since 1.0.0
		 */
		public function render_plugins() {

			// Render dashboard navigation.
			$this->render_navigation();

			$plugins = prisma_core_plugin_utilities()->get_recommended_plugins();
			?>
			<div class="pr-container">

				<div class="prisma-core-section-title">
					<h2 class="prisma-core-section-title"><?php esc_html_e( 'Recommended Plugins', 'prisma-core' ); ?></h2>
				</div><!-- END .prisma-core-section-title -->

				<div class="prisma-core-section prisma-core-columns plugins">

					<?php if ( is_array( $plugins ) && ! empty( $plugins ) ) { ?>
						<?php foreach ( $plugins as $plugin ) { ?>

							<?php
							// Check plugin status.
							if ( prisma_core_plugin_utilities()->is_activated( $plugin['slug'] ) ) {
								$btn_class = 'pr-btn secondary';
								$btn_text  = esc_html__( 'Deactivate', 'prisma-core' );
								$action    = 'deactivate';
								$notice    = '<span class="pr-active-plugin"><span class="dashicons dashicons-yes"></span>' . esc_html__( 'Plugin activated', 'prisma-core' ) . '</span>';
							} elseif ( prisma_core_plugin_utilities()->is_installed( $plugin['slug'] ) ) {
								$btn_class = 'pr-btn primary';
								$btn_text  = esc_html__( 'Activate', 'prisma-core' );
								$action    = 'activate';
								$notice    = '';
							} else {
								$btn_class = 'pr-btn primary';
								$btn_text  = esc_html__( 'Install & Activate', 'prisma-core' );
								$action    = 'install';
								$notice    = '';
							}
							?>

							<div class="prisma-core-column column-6">
								<div class="prisma-core-box">

									<div class="plugin-image">
										<img src="<?php echo esc_url( $plugin['thumb'] ); ?>" alt="<?php echo esc_html( $plugin['name'] ); ?>"/>					
									</div>

									<div class="plugin-info">
										<h4><?php echo esc_html( $plugin['name'] ); ?></h4>
										<p><?php echo esc_html( $plugin['desc'] ); ?></p>
										<div class="prisma-core-buttons">
											<?php echo ( wp_kses_post( $notice ) ); ?>
											<a href="#" class="<?php echo esc_attr( $btn_class ); ?>" data-plugin="<?php echo esc_attr( $plugin['slug'] ); ?>" data-action="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $btn_text ); ?></a>
										</div>
									</div>

								</div>
							</div>
						<?php } ?>
					<?php } ?>

				</div><!-- END .prisma-core-section -->

				<?php do_action( 'prisma_core_recommended_plugins_after' ); ?>

			</div><!-- END .pr-container -->

			<?php
		}

		/**
		 * Render the changelog page.
		 *
		 * @since 1.0.0
		 */
		public function render_changelog() {

			// Render dashboard navigation.
			$this->render_navigation();

			$changelog = $this->get_changelog_from_readme();

			?>
			<div class="pr-container">

				<div class="prisma-core-section-title">
					<h2 class="prisma-core-section-title">
						<span><?php esc_html_e( 'Prisma Core Theme Changelog', 'prisma-core' ); ?></span>
						<span class="changelog-version"><?php echo esc_html( sprintf( 'v%1$s', PRISMA_CORE_THEME_VERSION ) ); ?></span>
					</h2>

				</div><!-- END .prisma-core-section-title -->

				<div class="prisma-core-section prisma-core-columns">

					<div class="prisma-core-column column-12">
						<div class="prisma-core-box prisma-core-changelog">
							<pre><?php echo esc_html( $changelog ); ?></pre>
						</div>
					</div>
				</div><!-- END .prisma-core-columns -->

				<?php do_action( 'prisma_core_after_changelog' ); ?>

			</div><!-- END .pr-container -->
			<?php
		}

		/**
		 * Extract the Changelog section from readme.txt.
		 *
		 * @since 1.4.0
		 * @return string Changelog text.
		 */
		private function get_changelog_from_readme() {
			$readme = PRISMA_CORE_THEME_PATH . '/readme.txt';

			if ( ! file_exists( $readme ) || ! is_readable( $readme ) ) {
				return esc_html__( 'Changelog not available.', 'prisma-core' );
			}

			$contents = file_get_contents( $readme ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Extract text between "== Changelog ==" and the next "== " section.
			if ( preg_match( '/== Changelog ==\s*\n(.*?)(?=\n== )/s', $contents, $matches ) ) {
				return trim( $matches[1] );
			}

			return esc_html__( 'Changelog not available.', 'prisma-core' );
		}

		/**
		 * Render admin page navigation tabs.
		 *
		 * @since 1.0.0
		 */
		public function render_navigation() {

			// Get navigation items.
			$menu_items = $this->get_navigation_items();

			?>
			<div class="pr-container">

				<div class="prisma-core-tabs">
					<ul>
						<?php
						// Determine current tab.
						$base = $this->get_current_page();

						// Display menu items.
						foreach ( $menu_items as $item ) {

							// Check if we're on a current item.
							$current = false !== strpos( $base, $item['id'] ) ? 'current-item' : '';
							?>

							<li class="<?php echo esc_attr( $current ); ?>">
								<a href="<?php echo esc_url( $item['url'] ); ?>">
									<?php echo esc_html( $item['name'] ); ?>

									<?php
									if ( isset( $item['icon'] ) && $item['icon'] ) {
										prisma_core_print_admin_icon( $item['icon'] );
									}
									?>
								</a>
							</li>

						<?php } ?>
					</ul>
				</div><!-- END .prisma-core-tabs -->

			</div><!-- END .pr-container -->
			<?php
		}

		/**
		 * Return the current Prisma Core Dashboard page.
		 *
		 * @since 1.0.0
		 * @return string $page Current dashboard page slug.
		 */
		public function get_current_page() {

			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = str_replace( 'prisma-core-', '', $page );
			$page = apply_filters( 'prisma_core_dashboard_current_page', $page );

			return esc_html( $page );
		}

		/**
		 * Print admin page navigation items.
		 *
		 * @since 1.0.0
		 * @return array $items Array of navigation items.
		 */
		public function get_navigation_items() {

			$items = array(
				'dashboard' => array(
					'id'   => 'dashboard',
					'name' => esc_html__( 'About', 'prisma-core' ),
					'icon' => '',
					'url'  => menu_page_url( 'prisma-core-dashboard', false ),
				),
				'plugins'   => array(
					'id'   => 'plugins',
					'name' => esc_html__( 'Recommended Plugins', 'prisma-core' ),
					'icon' => '',
					'url'  => menu_page_url( 'prisma-core-plugins', false ),
				),
				'changelog' => array(
					'id'   => 'changelog',
					'name' => esc_html__( 'Changelog', 'prisma-core' ),
					'icon' => '',
					'url'  => menu_page_url( 'prisma-core-changelog', false ),
				),
			);

			return apply_filters( 'prisma_core_dashboard_navigation_items', $items );
		}

		/**
		 * Activate plugin.
		 *
		 * @since 1.0.0
		 */
		public function activate_plugin() {

			// Security check.
			check_ajax_referer( 'prisma_core_nonce' );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( esc_html__( 'You do not have permission to activate plugins.', 'prisma-core' ) );
			}

			// Plugin data.
			$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

			if ( empty( $plugin ) ) {
				wp_send_json_error( esc_html__( 'Missing plugin data', 'prisma-core' ) );
			}

			if ( $plugin ) {

				$response = prisma_core_plugin_utilities()->activate_plugin( $plugin );

				if ( is_wp_error( $response ) ) {
					wp_send_json_error( $response->get_error_message(), $response->get_error_code() );
				}

				wp_send_json_success();
			}

			wp_send_json_error( esc_html__( 'Failed to activate plugin. Missing plugin data.', 'prisma-core' ) );
		}

		/**
		 * Deactivate plugin.
		 *
		 * @since 1.0.0
		 */
		public function deactivate_plugin() {

			// Security check.
			check_ajax_referer( 'prisma_core_nonce' );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( esc_html__( 'You do not have permission to deactivate plugins.', 'prisma-core' ) );
			}

			// Plugin data.
			$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

			if ( empty( $plugin ) ) {
				wp_send_json_error( esc_html__( 'Missing plugin data', 'prisma-core' ) );
			}

			if ( $plugin ) {
				$response = prisma_core_plugin_utilities()->deactivate_plugin( $plugin );

				if ( is_wp_error( $response ) ) {
					wp_send_json_error( $response->get_error_message(), $response->get_error_code() );
				}

				wp_send_json_success();
			}

			wp_send_json_error( esc_html__( 'Failed to deactivate plugin. Missing plugin data.', 'prisma-core' ) );
		}

		/**
		 * Highlight dashboard page for plugins page.
		 *
		 * @since 1.0.0
		 * @param string $submenu_file The submenu file.
		 */
		public function highlight_submenu( $submenu_file ) {

			global $pagenow;

			// Check if we're on prisma-core plugins or changelog page.
			if ( 'themes.php' === $pagenow ) {
				if ( isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 'prisma-core-plugins' === $_GET['page'] || 'prisma-core-changelog' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$submenu_file = 'prisma-core-dashboard';
					}
				}
			}

			return $submenu_file;
		}
	}
endif;

/**
 * The function which returns the one Prisma_Core_Dashboard instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $prisma_core_dashboard = prisma_core_dashboard(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function prisma_core_dashboard() {
	return Prisma_Core_Dashboard::instance();
}

prisma_core_dashboard();

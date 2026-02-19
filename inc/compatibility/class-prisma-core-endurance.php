<?php
/**
 * Prisma Core integration for Endurance.
 *
 * @package Prisma Core
 * @author  Prisma Core Team
 * @since   1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'BlueHost' !== get_option( 'mm_brand' ) ) {
	return;
}

if ( ! class_exists( 'Prisma_Core_Endurance' ) ) :

	/**
	 * Prisma Core integration for Bluehost
	 */
	class Prisma_Core_Endurance {

		/**
		 * Singleton instance of the class.
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Registered pointers.
		 *
		 * @var array
		 */
		private $pointers = array();

		/**
		 * Main Prisma_Core_Endurance Instance.
		 *
		 * @since 1.0.0
		 * @return Prisma_Core_Endurance
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Core_Endurance ) ) {

				self::$instance = new Prisma_Core_Endurance();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			/**
			 * Registers our custom options in Customizer.
			 */
			add_action( 'customize_register', array( $this, 'register_options' ), 11 );

			/**
			 * Add WP Pointers.
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'add_pointers' ) );

			/**
			 * Script to handle WP Pointers interaction.
			 */
			add_action( 'admin_print_footer_scripts', array( $this, 'add_pointers_script' ) );

			/**
			 * Ajax handler to restart the Customizer Tour.
			 */
			add_action( 'wp_ajax_prisma-core_start_customizer_tour', array( $this, 'start_customizer_tour' ) );

			/**
			 * Modify recommended plugins.
			 */
			add_filter( 'prisma_core_recommended_plugins', array( $this, 'recommended_plugins' ) );
		}

		/**
		 * Registers custom options in Customizer.
		 *
		 * @since 1.0.0
		 * @param WP_Customize_Manager $customizer Instance of WP_Customize_Manager class.
		 */
		public function register_options( $customizer ) {

			// Help section in Customizer.
			if ( class_exists( 'Prisma_Core_Customizer_Control_Button' ) ) {

				$customizer->add_section(
					'prisma_core_section_help',
					array(
						'title'    => esc_html__( 'Help', 'prisma-core' ),
						'priority' => 999,
					)
				);

				// Reset tour.
				$customizer->add_setting(
					'prisma_core_help_reset_tour',
					array(
						'sanitize_callback' => 'prisma_core_no_sanitize',
					)
				);

				$customizer->add_control(
					new Prisma_Core_Customizer_Control_Button(
						$customizer,
						'prisma_core_help_reset_tour',
						array(
							'label'       => esc_html__( 'Take a Tour', 'prisma-core' ),
							'section'     => 'prisma_core_section_help',
							'ajax_action' => 'prisma_core_start_customizer_tour',
							'button_text' => esc_html__( 'Start Tour', 'prisma-core' ),
							'settings'    => 'prisma_core_help_reset_tour',
							'priority'    => 10,
						)
					)
				);

				// Prisma Core docs.
				$customizer->add_setting(
					'prisma_core_help_prisma-core_docs',
					array(
						'sanitize_callback' => 'prisma_core_no_sanitize',
					)
				);

				$customizer->add_control(
					new Prisma_Core_Customizer_Control_Button(
						$customizer,
						'prisma_core_help_prisma-core_docs',
						array(
							'label'       => esc_html__( 'Prisma Core Theme Guide', 'prisma-core' ),
							'section'     => 'prisma_core_section_help',
							'button_text' => esc_html__( 'Help Articles', 'prisma-core' ),
							'button_url'  => 'https://www.wpzoom.com/documentation/prisma-core/',
							'settings'    => 'prisma_core_help_prisma-core_docs',
							'priority'    => 20,
						)
					)
				);

				// Customizer docs.
				$customizer->add_setting(
					'prisma_core_help_customizer_docs',
					array(
						'sanitize_callback' => 'prisma_core_no_sanitize',
					)
				);

				$customizer->add_control(
					new Prisma_Core_Customizer_Control_Button(
						$customizer,
						'prisma_core_help_customizer_docs',
						array(
							'label'       => esc_html__( 'WordPress Customizer Tutorial', 'prisma-core' ),
							'section'     => 'prisma_core_section_help',
							'button_text' => esc_html__( 'Customizer Guide', 'prisma-core' ),
							'button_url'  => 'https://github.com/ciorici/prisma-core/docs/the-ultimate-guide-to-the-wordpress-customizer/',
							'settings'    => 'prisma_core_help_customizer_docs',
							'priority'    => 30,
						)
					)
				);
			}
		}

		/**
		 * Create pointers for current screen.
		 *
		 * @since 1.0.0
		 */
		public function add_pointers() {

			$current_screen = get_current_screen();

			if ( ! $current_screen ) {
				return;
			}

			$pointers = array();

			// Get all pointers.
			switch ( $current_screen->id ) {
				case 'customize':
					$pointers = $this->customizer_pointers();
					break;
				default:
					break;
			}

			// Check if any pointers are to be displayed.
			if ( empty( $pointers ) ) {
				return;
			}

			// Filter out dismissed pointers.
			$dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
			$dismissed = explode( ',', (string) $dismissed );

			foreach ( $pointers as $pointer_id => $pointer ) {

				if ( in_array( $pointer_id, $dismissed, true ) ) {
					$this->pointers = array();
					break;
				}

				if ( empty( $pointer ) || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
					$this->pointers = array();
					break;
				}

				$this->pointers[ $pointer_id ] = $pointer;
			}

			// Check if any pointers are to be displayed.
			if ( empty( $this->pointers ) ) {
				return;
			}

			// Enqueue pointer script.
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}

		/**
		 * Customizer pointers.
		 *
		 * @since 1.0.0
		 */
		public function customizer_pointers() {

			$pointers = array(
				'prisma-core-pointer-01' => array(
					'id'      => 'prisma-core-pointer-01',
					'target'  => '#customize-info',
					'next'    => 'prisma-core-pointer-02',
					'options' => array(
						'content'  => '<h3><img src="' . PRISMA_CORE_THEME_URI . '/inc/customizer/assets/images/wpbh.svg" alt="WordPress + Bluehost" /></h3><p>' . esc_html__( 'Welcome to your WordPress Site! Let us give you a quick overview to help you customize your website&rsquo;s design. Or, come back to this tour at any time in the &ldquo;Help&rdquo; tab.', 'prisma-core' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'top',
						),
					),
					'focus'   => array(
						'type' => 'root',
					),
					'arrow'   => 'arrow-top',
				),

				'prisma-core-pointer-02' => array(
					'id'       => 'prisma-core-pointer-02',
					'target'   => '#accordion-section-themes',
					'previous' => 'prisma-core-pointer-01',
					'next'     => 'prisma-core-pointer-03',
					'options'  => array(
						'content'  => '<h3>' . esc_html__( 'Your website design and theme', 'prisma-core' ) . '</h3>' .
									'<p>' . sprintf( wp_kses( 'To help you get building as fast as possible, we pre-installed our favorite theme, %3$sPrisma Core%4$s, but you can change this theme at any time. %1$sRead more about Prisma Core.%2$s', prisma_core_get_allowed_html_tags() ), '<a href="https://github.com/wpzoom/prisma-core" rel="noopener noreferrer" target="_blank">', '</a>', '<strong>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'middle',
						),
					),
					'focus'    => array(
						'type' => 'root',
					),
				),

				'prisma-core-pointer-03' => array(
					'id'       => 'prisma-core-pointer-03',
					'target'   => '#accordion-panel-prisma_core_panel_general',
					'previous' => 'prisma-core-pointer-02',
					'next'     => 'prisma-core-pointer-04',
					'options'  => array(
						'content'  => '<h3>' . esc_html__( 'Customize your design', 'prisma-core' ) . '</h3>' .
									'<p>' . esc_html__( 'Give your site a personalized look. Here you can change your site layout, colors, fonts and more.', 'prisma-core' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'middle',
						),
					),
					'focus'    => array(
						'type' => 'root',
					),
				),

				'prisma-core-pointer-04' => array(
					'id'       => 'prisma-core-pointer-04',
					'target'   => '#accordion-panel-prisma_core_panel_blog',
					'previous' => 'prisma-core-pointer-03',
					'next'     => 'prisma-core-pointer-05',
					'options'  => array(
						'content'  => '<h3>' . esc_html__( 'Change your blog layout or location', 'prisma-core' ) . '</h3>' .
									'<p>' . esc_html__( 'Customize the look and feel of your blog and introduce yourself to your viewers, customers or the world.', 'prisma-core' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'middle',
						),
					),
					'focus'    => array(
						'type' => 'root',
					),
				),

				'prisma-core-pointer-05' => array(
					'id'       => 'prisma-core-pointer-05',
					'target'   => '#customize-header-actions',
					'previous' => 'prisma-core-pointer-04',
					'options'  => array(
						'content'  => '<h3>' . esc_html__( 'Save, preview, publish or exit', 'prisma-core' ) . '</h3>' .
									'<p>' . esc_html__( 'Done for now? Save, preview, publish or schedule when you want to publish the latest edits to your site. Or, close the customizer to go to your WordPress dashboard to modify your website&rsquo;s settings or preferences. ', 'prisma-core' ) . '</p>',
						'position' => array(
							'edge'  => 'top',
							'align' => 'left',
						),
					),
					'focus'    => array(
						'type' => 'root',
					),
				),
			);

			return $pointers;
		}

		/**
		 * Print JavaScript if pointers are available.
		 *
		 * @since 1.0.0
		 */
		public function add_pointers_script() {

			if ( empty( $this->pointers ) ) {
				return;
			}

			$pointers = wp_json_encode( $this->pointers );

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "
			<script type='text/javascript'>
				jQuery(document).ready( function($) {

					var prisma_core_pointers = {$pointers};

					setTimeout( init_prisma-core_pointers, 800 );

					function init_prisma-core_pointers() {
						$.each( prisma_core_pointers, function( i ) {
							prisma_core_pointer_open( i );
							return false;
						});

						jQuery( 'iframe' ).load( function() {
							$('iframe').contents().find('body').addClass( 'prisma-core-hide-shortcuts' );
						});
					}

					function prisma_core_pointer_open(id) {

						var pointer = prisma_core_pointers[id];
						var element = $(pointer.target);
						var arrow   = '';

						if ( pointer.arrow ) {
							arrow = ' ' + pointer.arrow;
						}

						var prisma_core_show_pointer = function() {

							if ( element.css('display') == 'none' || element.css('visibility') == 'hidden') {
							    return;
							}

							var options = $.extend( pointer.options, {
								pointerClass: 'wp-pointer prisma-core-pointer' + arrow,
								buttons: function( event, t ) {

									var close    = '" . esc_js( __( 'End Tour', 'prisma-core' ) ) . "',
										next     = '" . esc_js( __( 'Next', 'prisma-core' ) ) . "',
										previous = '" . esc_js( __( 'Back', 'prisma-core' ) ) . "',
										done     = '" . esc_js( __( 'Done', 'prisma-core' ) ) . "',
										button_dismiss  = $( '<a class=\"close\" href=\"#\">' + close + '</a>' ),
										button_next     = $( '<a class=\"button button-primary\" href=\"#\">' + next + '</a>' ),
										button_previous = $( '<a class=\"button button-secondary\" href=\"#\">' + previous + '</a>' ),
										wrapper  = $( '<div class=\"prisma-core-pointer-buttons\" />' );

									if ( ! pointer.next ) {
										button_next = $( '<a class=\"button button-primary\" href=\"#\">' + done + '</a>' );
									}

									button_dismiss.bind( 'click.pointer', function(e) {
										e.preventDefault();
										t.element.pointer('close');
										// t.element.pointer('destroy');

										// Enable shortcut buttons again.
										$( 'iframe' ).contents().find( 'body' ).removeClass( 'prisma-core-hide-shortcuts' );
									});

									button_next.bind( 'click.pointer', function(e) {
										e.preventDefault();
										t.element.pointer('close');

										if ( pointer.next ) {
											prisma_core_pointer_open( pointer.next );
										} else {
											$( 'iframe' ).contents().find( 'body' ).removeClass( 'prisma-core-hide-shortcuts' );
										}
									});

									button_previous.bind( 'click.pointer', function(e) {
										e.preventDefault();

										t.element.pointer('close');

										if ( pointer.previous ) {
											prisma_core_pointer_open( pointer.previous );
										}
									});

									wrapper.append( button_dismiss );

									if ( pointer.previous ) {
										wrapper.append( button_previous );
									}

									wrapper.append( button_next );

									return wrapper;
								},
								position: {
									edge: pointer.options.position.edge,
									align: pointer.options.position.align
								},
								close: function() {
									$.post( ajaxurl, 
									{
										pointer: pointer.id,
										action: 'dismiss-wp-pointer'
									});
								},
							});

							var current = $( pointer.target ).pointer( options );

							current.pointer('open');

							if ( pointer.next_trigger ) {
								$( pointer.next_trigger.target ).on( pointer.next_trigger.event, function() {
									setTimeout( function() { current.pointer( 'close' ); }, 400 );
								});
							}
						};

						if ( pointer.focus ) {

							if ( 'panel' === pointer.focus.type ) {
								wp.customize.panel( pointer.focus.id ).focus( { completeCallback: prisma_core_show_pointer } );
							} else if ( 'section' === pointer.focus.type ) {
								wp.customize.section( pointer.focus.id ).focus( { completeCallback: prisma_core_show_pointer } );
							} else if ( 'control' === pointer.focus.type ) {
								wp.customize.control( pointer.focus.id ).focus( { completeCallback: prisma_core_show_pointer } );
							} else if ( 'root' === pointer.focus.type ) {

								// Collapse any sibling sections/panels
								wp.customize.section.each( function ( section ) {
									section.collapse( { duration: 0 } );
								});
								wp.customize.panel.each( function ( otherPanel ) {
									otherPanel.collapse( { duration: 0 } );
								});

								setTimeout( prisma_core_show_pointer, 200 );
							}
						}
					}
				});
			</script>
			";
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * AJAX handler to reset WP Pointers for the customizer tour.
		 *
		 * @since 1.0.0
		 */
		public function start_customizer_tour() {

			// Security check.
			check_ajax_referer( 'prisma_core_customizer' );

			if ( ! current_user_can( 'customize' ) ) {
				wp_die( -1, 403 );
			}

			$customizer_pointers = $this->customizer_pointers();

			if ( ! empty( $customizer_pointers ) ) {

				$dismissed = array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) );

				foreach ( $customizer_pointers as $pointer => $config ) {

					$key = array_search( $pointer, $dismissed, true );

					if ( false !== $key ) {
						unset( $dismissed[ $key ] );
					}
				}

				$dismissed = implode( ',', $dismissed );
				update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );

				// Reload page and start the tour.
				wp_send_json_success( array( 'reload' => true ) );
			}

			// Error.
			wp_send_json_error();
		}

		/**
		 * Modify recommended plugins for Endurance users.
		 *
		 * @since 1.0.0
		 * @param Array $plugins Array of recommended plugins.
		 * @return Array         Modified array of recommended plugins.
		 */
		public function recommended_plugins( $plugins ) {

			if ( is_array( $plugins ) && ! empty( $plugins ) ) {
				foreach ( $plugins as $slug => $plugin ) {
					if ( isset( $plugin['endurance'] ) && false === $plugin['endurance'] ) {
						unset( $plugins[ $slug ] );
					}
				}
			}

			return $plugins;
		}
	}
endif;

/**
 * The function which returns the one Prisma_Core_Endurance instance.
 */
function prisma_core_endurance() {
	return Prisma_Core_Endurance::instance();
}

prisma_core_endurance();

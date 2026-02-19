<?php
/**
 * Prisma Core Transparent Header Settings section in Customizer.
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

if ( ! class_exists( 'Prisma_Core_Customizer_Transparent_Header' ) ) :
	/**
	 * Prisma Core Main Transparent section in Customizer.
	 */
	class Prisma_Core_Customizer_Transparent_Header {

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			/**
			 * Registers our custom options in Customizer.
			 */
			add_filter( 'prisma_core_customizer_options', array( $this, 'register_options' ) );
		}

		/**
		 * Registers our custom options in Customizer.
		 *
		 * @since 1.0.0
		 * @param array $options Array of customizer options.
		 */
		public function register_options( $options ) {

			// Transparent Header Section.
			$options['section']['prisma_core_section_transparent_header'] = array(
				'title'    => esc_html__( 'Transparent Header', 'prisma-core' ),
				'panel'    => 'prisma_core_panel_header',
				'priority' => 80,
			);

			// Enable Transparent Header.
			$options['setting']['prisma_core_tsp_header'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'    => 'prisma-core-toggle',
					'label'   => esc_html__( 'Enable Globally', 'prisma-core' ),
					'section' => 'prisma_core_section_transparent_header',
				),
			);

			// Disable choices.
			$disable_choices = array(
				'404' => array(
					'title' => esc_html__( '404 page', 'prisma-core' ),
				),
				'posts_page' => array(
					'title' => esc_html__( 'Blog / Posts page', 'prisma-core' ),
				),
				'archive' => array(
					'title' => esc_html__( 'Archive pages', 'prisma-core' ),
				),
				'search' => array(
					'title' => esc_html__( 'Search pages', 'prisma-core' ),
				),
				'post' => array(
					'title' => esc_html__( 'Posts', 'prisma-core' ),
				),
				'page' => array(
					'title' => esc_html__( 'Pages', 'prisma-core' ),
				),
			);

			// Get additionally registered post types.
			$post_types = get_post_types(
				array(
					'public'   => true,
					'_builtin' => false,
				),
				'objects'
			);

			if ( is_array( $post_types ) && ! empty( $post_types ) ) {
				foreach ( $post_types as $slug => $post_type ) {
					$disable_choices[ $slug ] = array(
						'title' => $post_type->label,
					);
				}
			}

			// Transparent header display on.
			$options['setting']['prisma_core_tsp_header_disable_on'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_checkbox_group',
				'control'           => array(
					'type'        => 'prisma-core-checkbox-group',
					'label'       => esc_html__( 'Disable On: ', 'prisma-core' ),
					'description' => esc_html__( 'Choose on which pages you want to disable Transparent Header. ', 'prisma-core' ),
					'section'     => 'prisma_core_section_transparent_header',
					'choices'     => $disable_choices,
					'required'    => array(
						array(
							'control'  => 'prisma_core_tsp_header',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Logo Settings Heading.
			$options['setting']['prisma_core_tsp_logo_heading'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'label'    => esc_html__( 'Logo Settings', 'prisma-core' ),
					'section'  => 'prisma_core_section_transparent_header',
				),
			);

			// Logo.
			$options['setting']['prisma_core_tsp_logo'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_background',
				'control'           => array(
					'type'        => 'prisma-core-background',
					'section'     => 'prisma_core_section_transparent_header',
					'label'       => esc_html__( 'Alternative Logo', 'prisma-core' ),
					'description' => esc_html__( 'Upload a different logo to be used with Transparent Header.', 'prisma-core' ),
					'advanced'    => false,
					'strings'     => array(
						'select_image' => __( 'Select logo', 'prisma-core' ),
						'use_image'    => __( 'Select', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_tsp_logo_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
				'partial'           => array(
					'selector'            => '.prisma-core-logo',
					'render_callback'     => 'prisma_core_logo',
					'container_inclusive' => false,
					'fallback_refresh'    => true,
				),
			);

			// Logo Retina.
			$options['setting']['prisma_core_tsp_logo_retina'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_background',
				'control'           => array(
					'type'        => 'prisma-core-background',
					'section'     => 'prisma_core_section_transparent_header',
					'label'       => esc_html__( 'Alternative Logo - Retina', 'prisma-core' ),
					'description' => esc_html__( 'Upload exactly 2x the size of your alternative logo to make your logo crisp on HiDPI screens. This options is not required if logo above is in SVG format.', 'prisma-core' ),
					'advanced'    => false,
					'strings'     => array(
						'select_image' => __( 'Select logo', 'prisma-core' ),
						'use_image'    => __( 'Select', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_tsp_logo_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
				'partial'           => array(
					'selector'            => '.prisma-core-logo',
					'render_callback'     => 'prisma_core_logo',
					'container_inclusive' => false,
					'fallback_refresh'    => true,
				),
			);

			// Logo Max Height.
			$options['setting']['prisma_core_tsp_logo_max_height'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_responsive',
				'control'           => array(
					'type'        => 'prisma-core-range',
					'label'       => esc_html__( 'Logo Height', 'prisma-core' ),
					'description' => esc_html__( 'Maximum logo image height on transparent header.', 'prisma-core' ),
					'section'     => 'prisma_core_section_transparent_header',
					'min'         => 0,
					'max'         => 1000,
					'step'        => 10,
					'unit'        => 'px',
					'responsive'  => true,
					'required'    => array(
						array(
							'control'  => 'prisma_core_tsp_logo_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Logo margin.
			$options['setting']['prisma_core_tsp_logo_margin'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_responsive',
				'control'           => array(
					'type'        => 'prisma-core-spacing',
					'label'       => esc_html__( 'Logo Margin', 'prisma-core' ),
					'description' => esc_html__( 'Specify spacing around logo on transparent header. Negative values are allowed. Leave empty to inherit from Logos & Site Title » Logo Margin.', 'prisma-core' ),
					'section'     => 'prisma_core_section_transparent_header',
					'choices'     => array(
						'top'    => esc_html__( 'Top', 'prisma-core' ),
						'right'  => esc_html__( 'Right', 'prisma-core' ),
						'bottom' => esc_html__( 'Bottom', 'prisma-core' ),
						'left'   => esc_html__( 'Left', 'prisma-core' ),
					),
					'responsive'  => true,
					'unit'        => array(
						'px',
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_tsp_logo_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Custom Colors Heading.
			$options['setting']['prisma_core_tsp_colors_heading'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'label'    => esc_html__( 'Main Header Colors', 'prisma-core' ),
					'section'  => 'prisma_core_section_transparent_header',
				),
			);

			// Background.
			$options['setting']['prisma_core_tsp_header_background'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'section'  => 'prisma_core_section_transparent_header',
					'label'    => esc_html__( 'Background', 'prisma-core' ),
					'space'    => true,
					'display'  => array(
						'background' => array(
							'color' => esc_html__( 'Solid Color', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_tsp_colors_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Text Color.
			$options['setting']['prisma_core_tsp_header_font_color'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'section'  => 'prisma_core_section_transparent_header',
					'label'    => esc_html__( 'Font Color', 'prisma-core' ),
					'space'    => true,
					'display'  => array(
						'color' => array(
							'text-color'       => esc_html__( 'Text Color', 'prisma-core' ),
							'link-color'       => esc_html__( 'Link Color', 'prisma-core' ),
							'link-hover-color' => esc_html__( 'Link Hover Color', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_tsp_colors_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Border.
			$options['setting']['prisma_core_tsp_header_border'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'section'  => 'prisma_core_section_transparent_header',
					'label'    => esc_html__( 'Border', 'prisma-core' ),
					'space'    => true,
					'display'  => array(
						'border' => array(
							'style'     => esc_html__( 'Style', 'prisma-core' ),
							'color'     => esc_html__( 'Color', 'prisma-core' ),
							'width'     => esc_html__( 'Width (px)', 'prisma-core' ),
							'positions' => array(
								'top'    => esc_html__( 'Top', 'prisma-core' ),
								'bottom' => esc_html__( 'Bottom', 'prisma-core' ),
							),
							'separator' => esc_html__( 'Separator Color', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_tsp_colors_heading',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			return $options;
		}
	}
endif;
new Prisma_Core_Customizer_Transparent_Header();

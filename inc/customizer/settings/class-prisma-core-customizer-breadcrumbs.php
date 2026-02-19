<?php
/**
 * Prisma Core Breadcrumbs Settings section in Customizer.
 *
 * @package     Prisma Core
 * @author      Prisma Core Team
 * @since       1.1.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Prisma_Core_Customizer_Breadcrumbs' ) ) :
	/**
	 * Prisma Core Breadcrumbs Settings section in Customizer.
	 */
	class Prisma_Core_Customizer_Breadcrumbs {

		/**
		 * Primary class constructor.
		 *
		 * @since 1.1.0
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
		 * @since 1.1.0
		 * @param array $options Array of customizer options.
		 */
		public function register_options( $options ) {

			// Main Navigation Section.
			$options['section']['prisma_core_section_breadcrumbs'] = array(
				'title'    => esc_html__( 'Breadcrumbs', 'prisma-core' ),
				'panel'    => 'prisma_core_panel_header',
				'priority' => 70,
			);

			// Breadcrumbs.
			$options['setting']['prisma_core_breadcrumbs_enable'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-toggle',
					'label'    => esc_html__( 'Enable Breadcrumbs', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
				),
			);

			// Hide breadcrumbs on.
			$options['setting']['prisma_core_breadcrumbs_hide_on'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_checkbox_group',
				'control'           => array(
					'type'        => 'prisma-core-checkbox-group',
					'label'       => esc_html__( 'Disable On: ', 'prisma-core' ),
					'description' => esc_html__( 'Choose on which pages you want to disable breadcrumbs. ', 'prisma-core' ),
					'section'     => 'prisma_core_section_breadcrumbs',
					'choices'     => prisma_core_get_display_choices(),
					'required'    => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Position.
			$options['setting']['prisma_core_breadcrumbs_position'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'     => 'prisma-core-select',
					'label'    => esc_html__( 'Position', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
					'choices'  => array(
						'in-page-header' => esc_html__( 'In Page Header', 'prisma-core' ),
						'below-header'   => esc_html__( 'Below Header (Separate Container)', 'prisma-core' ),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Alignment.
			$options['setting']['prisma_core_breadcrumbs_alignment'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'     => 'prisma-core-alignment',
					'label'    => esc_html__( 'Alignment', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
					'choices'  => 'horizontal',
					'icons'    => array(
						'left'   => 'dashicons dashicons-editor-alignleft',
						'center' => 'dashicons dashicons-editor-aligncenter',
						'right'  => 'dashicons dashicons-editor-alignright',
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_position',
							'value'    => 'below-header',
							'operator' => '==',
						),
					),
				),
			);

			// Spacing.
			$options['setting']['prisma_core_breadcrumbs_spacing'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_responsive',
				'control'           => array(
					'type'        => 'prisma-core-spacing',
					'label'       => esc_html__( 'Spacing', 'prisma-core' ),
					'description' => esc_html__( 'Specify top and bottom padding.', 'prisma-core' ),
					'section'     => 'prisma_core_section_breadcrumbs',
					'choices'     => array(
						'top'    => esc_html__( 'Top', 'prisma-core' ),
						'bottom' => esc_html__( 'Bottom', 'prisma-core' ),
					),
					'responsive'  => true,
					'unit'        => array(
						'px',
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Design options heading.
			$options['setting']['prisma_core_breadcrumbs_heading_design'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'label'    => esc_html__( 'Design Options', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
					'required' => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_position',
							'value'    => 'below-header',
							'operator' => '==',
						),
					),
				),
			);

			// Background design.
			$options['setting']['prisma_core_breadcrumbs_background'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'label'    => esc_html__( 'Background', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
					'display'  => array(
						'background' => array(
							'color'    => esc_html__( 'Solid Color', 'prisma-core' ),
							'gradient' => esc_html__( 'Gradient', 'prisma-core' ),
							'image'    => esc_html__( 'Image', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_heading_design',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_position',
							'value'    => 'below-header',
							'operator' => '==',
						),
					),
				),
			);

			// Text Color.
			$options['setting']['prisma_core_breadcrumbs_text_color'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'label'    => esc_html__( 'Font Color', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
					'display'  => array(
						'color' => array(
							'text-color'       => esc_html__( 'Text Color', 'prisma-core' ),
							'link-color'       => esc_html__( 'Link Color', 'prisma-core' ),
							'link-hover-color' => esc_html__( 'Link Hover Color', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_heading_design',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_position',
							'value'    => 'below-header',
							'operator' => '==',
						),
					),
				),
			);

			// Border.
			$options['setting']['prisma_core_breadcrumbs_border'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'label'    => esc_html__( 'Border', 'prisma-core' ),
					'section'  => 'prisma_core_section_breadcrumbs',
					'display'  => array(
						'border' => array(
							'style'     => esc_html__( 'Style', 'prisma-core' ),
							'color'     => esc_html__( 'Color', 'prisma-core' ),
							'width'     => esc_html__( 'Width (px)', 'prisma-core' ),
							'positions' => array(
								'top'    => esc_html__( 'Top', 'prisma-core' ),
								'bottom' => esc_html__( 'Bottom', 'prisma-core' ),
							),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_breadcrumbs_enable',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_heading_design',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_breadcrumbs_position',
							'value'    => 'below-header',
							'operator' => '==',
						),
					),
				),
			);

			return $options;
		}
	}
endif;
new Prisma_Core_Customizer_Breadcrumbs();

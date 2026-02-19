<?php
/**
 * Prisma Core Pre Footer section in Customizer.
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

if ( ! class_exists( 'Prisma_Core_Customizer_Pre_Footer' ) ) :
	/**
	 * Prisma Core Pre Footer section in Customizer.
	 */
	class Prisma_Core_Customizer_Pre_Footer {

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

			// Pre Footer.
			$options['section']['prisma_core_section_pre_footer'] = array(
				'title'    => esc_html__( 'Pre Footer', 'prisma-core' ),
				'panel'    => 'prisma_core_panel_footer',
				'priority' => 10,
			);

			// Pre Footer - Call to Action.
			$options['setting']['prisma_core_pre_footer_cta'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'    => 'prisma-core-heading',
					'label'   => esc_html__( 'Call to Action', 'prisma-core' ),
					'section' => 'prisma_core_section_pre_footer',
				),
			);

			// Enable Pre Footer CTA.
			$options['setting']['prisma_core_enable_pre_footer_cta'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-toggle',
					'label'    => esc_html__( 'Enable Call to Action', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
				'partial'           => array(
					'selector'            => '#pr-pre-footer',
					'render_callback'     => 'prisma_core_pre_footer',
					'container_inclusive' => true,
					'fallback_refresh'    => true,
				),
			);

			// Pre Footer visibility.
			$options['setting']['prisma_core_pre_footer_cta_visibility'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'        => 'prisma-core-select',
					'label'       => esc_html__( 'Device Visibility', 'prisma-core' ),
					'description' => esc_html__( 'Devices where the Top Bar is displayed.', 'prisma-core' ),
					'section'     => 'prisma_core_section_pre_footer',
					'choices'     => array(
						'all'                => esc_html__( 'Show on All Devices', 'prisma-core' ),
						'hide-mobile'        => esc_html__( 'Hide on Mobile', 'prisma-core' ),
						'hide-tablet'        => esc_html__( 'Hide on Tablet', 'prisma-core' ),
						'hide-mobile-tablet' => esc_html__( 'Hide on Mobile and Tablet', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer Hide on.
			$options['setting']['prisma_core_pre_footer_cta_hide_on'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_checkbox_group',
				'control'           => array(
					'type'        => 'prisma-core-checkbox-group',
					'label'       => esc_html__( 'Disable On: ', 'prisma-core' ),
					'description' => esc_html__( 'Choose on which pages you want to disable Pre Footer Call to Action. ', 'prisma-core' ),
					'section'     => 'prisma_core_section_pre_footer',
					'choices'     => prisma_core_get_display_choices(),
					'required'    => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer CTA Style.
			$options['setting']['prisma_core_pre_footer_cta_style'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'        => 'prisma-core-select',
					'label'       => esc_html__( 'Style', 'prisma-core' ),
					'description' => esc_html__( 'Choose CTA Style.', 'prisma-core' ),
					'section'     => 'prisma_core_section_pre_footer',
					'choices'     => array(
						'1' => esc_html__( 'Contained', 'prisma-core' ),
						'2' => esc_html__( 'Fullwidth', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer CTA Text.
			$options['setting']['prisma_core_pre_footer_cta_text'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_textarea',
				'control'           => array(
					'type'        => 'prisma-core-textarea',
					'label'       => esc_html__( 'Content', 'prisma-core' ),
					'description' => esc_html__( 'Shortcodes and basic html elements allowed.', 'prisma-core' ),
					'placeholder' => esc_html__( 'Call to Action Content', 'prisma-core' ),
					'section'     => 'prisma_core_section_pre_footer',
					'rows'        => '5',
					'required'    => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer CTA Button Text.
			$options['setting']['prisma_core_pre_footer_cta_btn_text'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_text_field',
				'control'           => array(
					'type'        => 'prisma-core-text',
					'label'       => esc_html__( 'Button Text', 'prisma-core' ),
					'description' => esc_html__( 'Label for the CTA button.', 'prisma-core' ),
					'section'     => 'prisma_core_section_pre_footer',
					'required'    => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer CTA Button URL.
			$options['setting']['prisma_core_pre_footer_cta_btn_url'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_text_field',
				'control'           => array(
					'type'        => 'prisma-core-text',
					'label'       => esc_html__( 'Button Link', 'prisma-core' ),
					'description' => esc_html__( 'Link for the CTA button.', 'prisma-core' ),
					'placeholder' => 'http://',
					'section'     => 'prisma_core_section_pre_footer',
					'required'    => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer CTA open in new tab.
			$options['setting']['prisma_core_pre_footer_cta_btn_new_tab'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-toggle',
					'label'    => esc_html__( 'Open link in new tab?', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer - Call to Action Design Options.
			$options['setting']['prisma_core_pre_footer_cta_design_options'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'label'    => esc_html__( 'Design Options', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer - Call to Action Background.
			$options['setting']['prisma_core_pre_footer_cta_background'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'label'    => esc_html__( 'Background', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'display'  => array(
						'background' => array(
							'color'    => esc_html__( 'Solid Color', 'prisma-core' ),
							'gradient' => esc_html__( 'Gradient', 'prisma-core' ),
							'image'    => esc_html__( 'Image', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_pre_footer_cta_design_options',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer - Call to Action Text Color.
			$options['setting']['prisma_core_pre_footer_cta_text_color'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'label'    => esc_html__( 'Font Color', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'display'  => array(
						'color' => array(
							'text-color'       => esc_html__( 'Text Color', 'prisma-core' ),
							'link-color'       => esc_html__( 'Link Color', 'prisma-core' ),
							'link-hover-color' => esc_html__( 'Link Hover Color', 'prisma-core' ),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_pre_footer_cta_design_options',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Pre Footer - Call to Action Border.
			$options['setting']['prisma_core_pre_footer_cta_border'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_design_options',
				'control'           => array(
					'type'     => 'prisma-core-design-options',
					'label'    => esc_html__( 'Border', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'display'  => array(
						'border' => array(
							'style'     => esc_html__( 'Style', 'prisma-core' ),
							'color'     => esc_html__( 'Color', 'prisma-core' ),
							'width'     => esc_html__( 'Width (px)', 'prisma-core' ),
							'positions' => array(
								'top'    => esc_html__( 'Top', 'prisma-core' ),
								'right'  => esc_html__( 'Right', 'prisma-core' ),
								'bottom' => esc_html__( 'Bottom', 'prisma-core' ),
								'left'   => esc_html__( 'Left', 'prisma-core' ),
							),
						),
					),
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_pre_footer_cta_design_options',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// CTA typography heading.
			$options['setting']['prisma_core_pre_footer_cta_typography'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'label'    => esc_html__( 'Typography', 'prisma-core' ),
					'section'  => 'prisma_core_section_pre_footer',
					'required' => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// CTA font size.
			$options['setting']['prisma_core_pre_footer_cta_font_size'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_responsive',
				'control'           => array(
					'type'       => 'prisma-core-range',
					'label'      => esc_html__( 'Font Size', 'prisma-core' ),
					'section'    => 'prisma_core_section_pre_footer',
					'min'        => 8,
					'max'        => 90,
					'step'       => 1,
					'responsive' => true,
					'unit'       => array(
						array(
							'id'   => 'px',
							'name' => 'px',
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
						),
						array(
							'id'   => 'em',
							'name' => 'em',
							'min'  => 0.5,
							'max'  => 5,
							'step' => 0.01,
						),
						array(
							'id'   => 'rem',
							'name' => 'rem',
							'min'  => 0.5,
							'max'  => 5,
							'step' => 0.01,
						),
					),
					'required'   => array(
						array(
							'control'  => 'prisma_core_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_enable_pre_footer_cta',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_pre_footer_cta_typography',
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
new Prisma_Core_Customizer_Pre_Footer();

<?php
/**
 * Prisma Core Sticky Header Settings section in Customizer.
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

if ( ! class_exists( 'Prisma_Core_Customizer_Sticky_Header' ) ) :
	/**
	 * Prisma Core Sticky Header section in Customizer.
	 */
	class Prisma_Core_Customizer_Sticky_Header {

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

			// Sticky Header Section.
			$options['section']['prisma_core_section_sticky_header'] = array(
				'title'    => esc_html__( 'Sticky Header', 'prisma-core' ),
				'panel'    => 'prisma_core_panel_header',
				'priority' => 80,
			);

			// Enable Transparent Header.
			$options['setting']['prisma_core_sticky_header'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'    => 'prisma-core-toggle',
					'label'   => esc_html__( 'Enable Sticky Header', 'prisma-core' ),
					'section' => 'prisma_core_section_sticky_header',
				),
			);

			// Responsive heading.
			$options['setting']['prisma_core_sticky_header_responsive'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'section'  => 'prisma_core_section_sticky_header',
					'label'    => esc_html__( 'Responsive', 'prisma-core' ),
					'required' => array(
						array(
							'control'  => 'prisma_core_sticky_header',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Hide sticky header on.
			$options['setting']['prisma_core_sticky_header_hide_on'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_checkbox_group',
				'control'           => array(
					'type'        => 'prisma-core-checkbox-group',
					'label'       => esc_html__( 'Hide on: ', 'prisma-core' ),
					'description' => esc_html__( 'Choose on which devices to hide Sticky Header on. ', 'prisma-core' ),
					'section'     => 'prisma_core_section_sticky_header',
					'choices'     => prisma_core_get_device_choices(),
					'required'    => array(
						array(
							'control'  => 'prisma_core_sticky_header',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_sticky_header_responsive',
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
new Prisma_Core_Customizer_Sticky_Header();

<?php
/**
 * Prisma Core Hero Section Settings section in Customizer.
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

if ( ! class_exists( 'Prisma_Core_Customizer_Hero' ) ) :
	/**
	 * Prisma Core Page Title Settings section in Customizer.
	 */
	class Prisma_Core_Customizer_Hero {

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

			// Hero Section.
			$options['section']['prisma_core_section_hero'] = array(
				'title'    => esc_html__( 'Hero', 'prisma-core' ),
				'priority' => 3,
			);

			// Hero enable.
			$options['setting']['prisma_core_enable_hero'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'    => 'prisma-core-toggle',
					'section' => 'prisma_core_section_hero',
					'label'   => esc_html__( 'Enable Hero Section', 'prisma-core' ),
				),
			);

			// Visibility.
			$options['setting']['prisma_core_hero_visibility'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'        => 'prisma-core-select',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Device Visibility', 'prisma-core' ),
					'description' => esc_html__( 'Devices where the Hero is displayed.', 'prisma-core' ),
					'choices'     => array(
						'all'                => esc_html__( 'Show on All Devices', 'prisma-core' ),
						'hide-mobile'        => esc_html__( 'Hide on Mobile', 'prisma-core' ),
						'hide-tablet'        => esc_html__( 'Hide on Tablet', 'prisma-core' ),
						'hide-mobile-tablet' => esc_html__( 'Hide on Mobile and Tablet', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Hero display on.
			$options['setting']['prisma_core_hero_enable_on'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_checkbox_group',
				'control'           => array(
					'type'        => 'prisma-core-checkbox-group',
					'label'       => esc_html__( 'Enable On: ', 'prisma-core' ),
					'description' => esc_html__( 'Choose on which pages you want to enable Hero. ', 'prisma-core' ),
					'section'     => 'prisma_core_section_hero',
					'choices'     => array(
						'home'       => array(
							'title' => esc_html__( 'Home Page', 'prisma-core' ),
						),
						'posts_page' => array(
							'title' => esc_html__( 'Blog / Posts Page', 'prisma-core' ),
						),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
					),
				),
			);

			// Hover Slider heading.
			$options['setting']['prisma_core_hero_hover_slider'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'section'  => 'prisma_core_section_hero',
					'label'    => esc_html__( 'Style', 'prisma-core' ),
					'required' => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
			);

			// Hover Slider container width.
			$options['setting']['prisma_core_hero_hover_slider_container'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'        => 'prisma-core-select',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Width', 'prisma-core' ),
					'description' => esc_html__( 'Stretch the container to full width, or match your site&rsquo;s content width.', 'prisma-core' ),
					'choices'     => array(
						'content-width' => esc_html__( 'Content Width', 'prisma-core' ),
						'full-width'    => esc_html__( 'Full Width', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_hover_slider',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
			);

			// Hover Slider height.
			$options['setting']['prisma_core_hero_hover_slider_height'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_range',
				'control'           => array(
					'type'        => 'prisma-core-range',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Height', 'prisma-core' ),
					'description' => esc_html__( 'Set the height of the container.', 'prisma-core' ),
					'min'         => 350,
					'max'         => 1000,
					'step'        => 1,
					'unit'        => 'px',
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_hover_slider',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
			);

			// Hover Slider overlay.
			$options['setting']['prisma_core_hero_hover_slider_overlay'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'        => 'prisma-core-select',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Overlay', 'prisma-core' ),
					'description' => esc_html__( 'Choose hero overlay style.', 'prisma-core' ),
					'choices'     => array(
						'none' => esc_html__( 'None', 'prisma-core' ),
						'1'    => esc_html__( 'Overlay 1', 'prisma-core' ),
						'2'    => esc_html__( 'Overlay 2', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_hover_slider',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
			);

			// Hover Slider Elements.
			$options['setting']['prisma_core_hero_hover_slider_elements'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_sortable',
				'control'           => array(
					'type'        => 'prisma-core-sortable',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Post Elements', 'prisma-core' ),
					'description' => esc_html__( 'Set order and visibility for post elements.', 'prisma-core' ),
					'sortable'    => false,
					'choices'     => array(
						'category'  => esc_html__( 'Categories', 'prisma-core' ),
						'meta'      => esc_html__( 'Post Details', 'prisma-core' ),
						'read_more' => esc_html__( 'Continue Reading', 'prisma-core' ),
					),
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_hover_slider',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
				'partial'           => array(
					'selector'            => '#hero',
					'render_callback'     => 'prisma_core_hero',
					'container_inclusive' => true,
					'fallback_refresh'    => true,
				),
			);

			// Post Settings heading.
			$options['setting']['prisma_core_hero_hover_slider_posts'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_toggle',
				'control'           => array(
					'type'     => 'prisma-core-heading',
					'section'  => 'prisma_core_section_hero',
					'label'    => esc_html__( 'Post Settings', 'prisma-core' ),
					'required' => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
			);

			// Post count.
			$options['setting']['prisma_core_hero_hover_slider_post_number'] = array(
				'transport'         => 'postMessage',
				'sanitize_callback' => 'prisma_core_sanitize_range',
				'control'           => array(
					'type'        => 'prisma-core-range',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Post Number', 'prisma-core' ),
					'description' => esc_html__( 'Set the number of visible posts.', 'prisma-core' ),
					'min'         => 1,
					'max'         => 4,
					'step'        => 1,
					'unit'        => '',
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_hover_slider_posts',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
				'partial'           => array(
					'selector'            => '#hero',
					'render_callback'     => 'prisma_core_hero',
					'container_inclusive' => true,
					'fallback_refresh'    => true,
				),
			);

			// Post category.
			$options['setting']['prisma_core_hero_hover_slider_category'] = array(
				'transport'         => 'refresh',
				'sanitize_callback' => 'prisma_core_sanitize_select',
				'control'           => array(
					'type'        => 'prisma-core-select',
					'section'     => 'prisma_core_section_hero',
					'label'       => esc_html__( 'Category', 'prisma-core' ),
					'description' => esc_html__( 'Display posts from selected category only. Leave empty to include all.', 'prisma-core' ),
					'is_select2'  => true,
					'data_source' => 'category',
					'multiple'    => true,
					'required'    => array(
						array(
							'control'  => 'prisma_core_enable_hero',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_hover_slider_posts',
							'value'    => true,
							'operator' => '==',
						),
						array(
							'control'  => 'prisma_core_hero_type',
							'value'    => 'hover-slider',
							'operator' => '==',
						),
					),
				),
			);

			return $options;
		}
	}
endif;
new Prisma_Core_Customizer_Hero();

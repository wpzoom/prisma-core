<?php
/**
 * Dynamically generate CSS code.
 * The code depends on options set in the Highend Options and Post/Page metaboxes.
 *
 * If possible, write the dynamically generated code into a .css file, otherwise return the code. The file is refreshed on each modification of metaboxes & theme options.
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

if ( ! class_exists( 'Prisma_Core_Dynamic_Styles' ) ) :
	/**
	 * Dynamically generate CSS code.
	 */
	class Prisma_Core_Dynamic_Styles {

		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance;

		/**
		 * URI for Dynamic CSS file.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $dynamic_css_uri;

		/**
		 * Path for Dynamic CSS file.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $dynamic_css_path;

		/**
		 * Main Prisma_Core_Dynamic_Styles Instance.
		 *
		 * @since 1.0.0
		 * @return Prisma_Core_Dynamic_Styles
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Prisma_Core_Dynamic_Styles ) ) {
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

			$upload_dir = wp_upload_dir();

			$this->dynamic_css_uri  = trailingslashit( set_url_scheme( $upload_dir['baseurl'] ) ) . 'prisma-core/';
			$this->dynamic_css_path = trailingslashit( set_url_scheme( $upload_dir['basedir'] ) ) . 'prisma-core/';

			if ( ! is_customize_preview() && wp_is_writable( trailingslashit( $upload_dir['basedir'] ) ) ) {
				add_action( 'prisma_core_enqueue_scripts', array( $this, 'enqueue_dynamic_style' ), 20 );
			} else {
				add_action( 'prisma_core_enqueue_scripts', array( $this, 'print_dynamic_style' ), 99 );
			}

			// Include button styles.
			add_filter( 'prisma_core_dynamic_styles', array( $this, 'get_button_styles' ), 6 );

			// Remove Customizer Custom CSS from wp_head, we will include it in our dynamic file.
			if ( ! is_customize_preview() ) {
				remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
			}

			// Generate new styles on Customizer Save action.
			add_action( 'customize_save_after', array( $this, 'update_dynamic_file' ) );

			// Generate new styles on theme activation.
			add_action( 'after_switch_theme', array( $this, 'update_dynamic_file' ) );

			// Delete the css stye on theme deactivation.
			add_action( 'switch_theme', array( $this, 'delete_dynamic_file' ) );

			// Generate initial dynamic css.
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Init.
		 *
		 * @since 1.0.0
		 */
		public function init() {

			// Ensure we have dynamic stylesheet generated.
			if ( false === get_transient( 'prisma_core_has_dynamic_css' ) ) {
				$this->update_dynamic_file();
			}
		}

		/**
		 * Enqueues dynamic styles file.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_dynamic_style() {

			$exists = file_exists( $this->dynamic_css_path . 'dynamic-styles.css' );

			// Generate the file if it's missing.
			if ( ! $exists ) {
				$exists = $this->update_dynamic_file();
			}

			// Enqueue the file if available.
			if ( $exists ) {
				wp_enqueue_style(
					'prisma-core-dynamic-styles',
					$this->dynamic_css_uri . 'dynamic-styles.css',
					false,
					filemtime( $this->dynamic_css_path . 'dynamic-styles.css' ),
					'all'
				);
			}
		}

		/**
		 * Prints inline dynamic styles if writing to file is not possible.
		 *
		 * @since 1.0.0
		 */
		public function print_dynamic_style() {
			$dynamic_css = $this->get_css();
			wp_add_inline_style( 'prisma-core-styles', $dynamic_css );
		}

		/**
		 * Generates dynamic CSS code, minifies it and cleans cache.
		 *
		 * @param  boolean $custom_css - should we include the wp_get_custom_css.
		 * @return string, minifed code
		 * @since  1.0.0
		 */
		public function get_css( $custom_css = false ) {

			// Refresh options.
			prisma_core()->options->refresh();

			// Delete google fonts enqueue transients.
			delete_transient( 'prisma_core_google_fonts_enqueue' );

			// Add our theme custom CSS.
			$css = '';

			// Accent color.
			$accent_color = prisma_core_option( 'accent_color' );

			$css .= '
				#pr-scroll-top:hover::before,
				.pr-btn,
				input[type=submit],
				input[type=reset],
				.comment-form input[type=checkbox]:checked,
				#comments .bypostauthor-badge,
				.single .post-tags a:hover,
				.single .post-category .cat-links a:hover,
				.tagcloud a:hover,
				#main .mejs-controls .mejs-time-rail .mejs-time-current,
				.pr-btn.prisma-core-read-more::after,
				.post_format-post-format-quote .pr-blog-entry-content .quote-post-bg::after,
				.pr-hover-slider .post-category a,
				.pr-single-title-in-page-header.single .page-header .post-category a,
				.entry-media > a:hover .entry-media-icon::before,
				.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::after,
				.pr-pre-footer-cta-style-2 #pr-pre-footer::after,
				.select2-container--default .select2-results__option--highlighted[aria-selected],
				.pr-input-supported input[type=radio]:checked::before,
				.pr-input-supported input[type=checkbox]:checked,
				.prisma-core-sidebar-style-2 #secondary .widget-title::before,
				.prisma-core-sidebar-style-2 .elementor-widget-sidebar .widget-title::before,
				.widget .cat-item a:hover + span,
				.widget_archive li a:hover + span,
				.widget .cat-item.current-cat a + span,
				#prisma-core-footer .widget .cat-item a:hover + span,
				#prisma-core-footer .widget_archive li a:hover + span,
				#prisma-core-footer .widget .cat-item.current-cat a + span,
				.pr-btn.btn-outline:hover,
				#infinite-handle span {
					background-color: ' . $accent_color . ';
				}

				.pr-btn:hover,
				#infinite-handle span:hover,
				input[type=submit]:hover,
				input[type=reset]:hover,
				input[type=reset]:focus,
				.pr-btn:focus,
				input[type=submit]:focus, 
				.pr-hover-slider .post-category a:hover, 
				.pr-single-title-in-page-header.single .page-header .post-category a:hover {
					background-color: ' . prisma_core_luminance( $accent_color, .15 ) . ';
				}
				
				mark,
				span.highlight,
				code,
				kbd,
				var,
				samp,
				tt {
					background-color: ' . prisma_core_hex2rgba( $accent_color, .09 ) . ';
				}

				code.block {
					background-color: ' . prisma_core_hex2rgba( $accent_color, .075 ) . ';
				}

				.content-area a:not(.pr-btn):not(.wp-block-button__link),
				#secondary .prisma-companion-custom-list-widget .pr-entry a:not(.pr-btn),
				#secondary a:not(.pr-btn):hover,
				.pr-header-widgets .pr-header-widget.prisma-core-active .pr-icon.pr-search,
				.prisma-core-logo .site-title a:hover,
				#prisma-core-header-inner .prisma-core-nav > ul > li > a:hover,
				#prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a, 
				#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a, 
				#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a,
				#prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a, 
				#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a, 
				#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a,
				#prisma-core-topbar .prisma-core-nav > ul > li > a:hover,
				#prisma-core-topbar .prisma-core-nav > ul > li.menu-item-has-children:hover > a, 
				#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-item > a, 
				#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-ancestor > a,
				.pr-topbar-widget__text a:hover,
				.pr-topbar-widget__text a,
				.prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon,
				.pr-header-widgets a:not(.pr-btn):hover,
				#prisma-core-header-inner .pr-header-widgets .prisma-core-active,
				.prisma-core-pagination .navigation .nav-links .page-numbers:hover,
				.widget .cat-item.current-cat > a,
				.widget ul li.current_page_item > a,
				#main .search-form .search-submit:hover,
				#colophon .search-form .search-submit:hover,
				#cancel-comment-reply-link:hover,
				.comment-form .required,
				.navigation .nav-links .page-numbers:hover,
				#main .entry-meta a:hover,
				#main .author-box-title a:hover,
				.single .post-category a,
				.page-links span:hover,
				.site-content .page-links span:hover,
				.navigation .nav-links .page-numbers.current,
				.page-links > span,
				.site-content .page-links > span,
				.pr-btn.btn-outline,
				code,
				kbd,
				var,
				samp,
				tt,
				.is-mobile-menu-active .pr-hamburger,
				.pr-hamburger:hover,
				.single #main .post-nav a:hover,
				#prisma-core-topbar .pr-topbar-widget__text .pr-icon {
					color: ' . $accent_color . ';
				}

				#page ::-moz-selection { background-color: ' . $accent_color . '; color: #FFF; }
				#page ::selection { background-color: ' . $accent_color . '; color: #FFF; }

				#comments .comment-actions .reply a:hover,
				.comment-form input[type=checkbox]:checked, 
				.comment-form input[type=checkbox]:focus,
				.comment-form input[type=radio]:checked, 
				.comment-form input[type=radio]:focus,
				.single .post-category a,
				#colophon,
				#secondary .widget-title,
				.elementor-widget-sidebar .widget-title,
				.pr-hover-slider .post-category a,
				.pr-single-title-in-page-header.single .page-header .post-category a,
				.pr-entry blockquote,
				.wp-block-quote.is-style-large, 
				.wp-block-quote.is-large,
				.wp-block-quote.has-text-align-right,
				.navigation .nav-links .page-numbers.current,
				.page-links > span,
				.site-content .page-links > span,
				.pr-input-supported input[type=radio]:checked,
				.pr-input-supported input[type=checkbox]:checked,
				.pr-btn.btn-outline {
					border-color: ' . $accent_color . ';
				}

				#masthead .pr-header-widgets .dropdown-item::after,
				.prisma-core-nav > ul .sub-menu::after,
				textarea:focus, input[type="text"]:focus, 
				input[type="email"]:focus, 
				input[type=password]:focus, 
				input[type=tel]:focus, 
				input[type=url]:focus, 
				input[type=search]:focus, 
				input[type=date]:focus {
					border-bottom-color: ' . $accent_color . ';
					outline: none !important;
				}

				.pr-header-widgets .dropdown-item,
				.preloader-1 > div,
				.prisma-core-nav .sub-menu {
					border-top-color: ' . $accent_color . ';
				}

				.prisma-core-animate-arrow:hover .arrow-handle,
				.prisma-core-animate-arrow:hover .arrow-bar,
				.prisma-core-animate-arrow:focus .arrow-handle,
				.prisma-core-animate-arrow:focus .arrow-bar,
				.prisma-core-pagination .navigation .nav-links .page-numbers.next:hover .prisma-core-animate-arrow .arrow-handle,
				.prisma-core-pagination .navigation .nav-links .page-numbers.prev:hover .prisma-core-animate-arrow .arrow-handle,
				.prisma-core-pagination .navigation .nav-links .page-numbers.next:hover .prisma-core-animate-arrow .arrow-bar,
				.prisma-core-pagination .navigation .nav-links .page-numbers.prev:hover .prisma-core-animate-arrow .arrow-bar {
					fill: ' . $accent_color . ';
				}

				.pr-input-supported input[type=checkbox]:focus:hover {
					box-shadow: inset 0 0 0 2px ' . $accent_color . ';
				}
			';

			$header_layout_3_additional_css = '';

			if ( 'layout-3' === prisma_core_option( 'header_layout' ) || is_customize_preview() ) {
				$header_layout_3_additional_css = '

					.prisma-core-header-layout-3 .pr-logo-container > .pr-container {
						flex-wrap: wrap;
					}

					.prisma-core-header-layout-3 .pr-logo-container .prisma-core-logo > .logo-inner {
						align-items: flex-start;
					}
					
					.prisma-core-header-layout-3 .pr-logo-container .prisma-core-logo {
						order: 0;
						align-items: flex-start;
						flex-basis: auto;
						margin-left: 0;
					}

					.prisma-core-header-layout-3 .pr-logo-container .pr-header-element {
						flex-basis: auto;
					}

					.prisma-core-header-layout-3 .pr-logo-container .pr-mobile-nav {
						order: 5;
					}

				';
			}

			/**
			 * Top Bar.
			 */

			// Background.
			$css .= $this->get_design_options_field_css( '#prisma-core-topbar', 'top_bar_background', 'background' );

			// Border.
			$css .= $this->get_design_options_field_css( '#prisma-core-topbar', 'top_bar_border', 'border' );
			$css .= $this->get_design_options_field_css( '.pr-topbar-widget', 'top_bar_border', 'separator_color' );

			// Top Bar colors.
			$topbar_color = prisma_core_option( 'top_bar_text_color' );

			// Top Bar text color.
			if ( isset( $topbar_color['text-color'] ) && $topbar_color['text-color'] ) {
				$css .= '#prisma-core-topbar { color: ' . $topbar_color['text-color'] . '; }';
			}

			// Top Bar link color.
			if ( isset( $topbar_color['link-color'] ) && $topbar_color['link-color'] ) {
				$css .= '
					.pr-topbar-widget__text a,
					.pr-topbar-widget .prisma-core-nav > ul > li > a,
					.pr-topbar-widget__socials .prisma-core-social-nav > ul > li > a,
					#prisma-core-topbar .pr-topbar-widget__text .pr-icon { 
						color: ' . $topbar_color['link-color'] . '; }
				';
			}

			// Top Bar link hover color.
			if ( isset( $topbar_color['link-hover-color'] ) && $topbar_color['link-hover-color'] ) {
				$css .= '
					#prisma-core-topbar .prisma-core-nav > ul > li > a:hover,
					#prisma-core-topbar .prisma-core-nav > ul > li.menu-item-has-children:hover > a,
					#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-item > a,
					#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-ancestor > a,
					#prisma-core-topbar .pr-topbar-widget__text a:hover,
					#prisma-core-topbar .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon { 
						color: ' . $topbar_color['link-hover-color'] . '; }
				';
			}

			/**
			 * Header.
			 */

			// Background.
			$css .= $this->get_design_options_field_css( '#prisma-core-header-inner', 'header_background', 'background' );

			// Font colors.
			$header_color = prisma_core_option( 'header_text_color' );

			// Header text color.
			if ( isset( $header_color['text-color'] ) && $header_color['text-color'] ) {
				$css .= '.prisma-core-logo .site-description { color: ' . $header_color['text-color'] . '; }';
			}

			// Header link color.
			if ( isset( $header_color['link-color'] ) && $header_color['link-color'] ) {
				$css .= '
					#prisma-core-header,
					.pr-header-widgets a:not(.pr-btn),
					.prisma-core-logo a,
					.pr-hamburger { 
						color: ' . $header_color['link-color'] . '; }
				';
			}

			// Header link hover color.
			if ( isset( $header_color['link-hover-color'] ) && $header_color['link-hover-color'] ) {
				$css .= '
					.pr-header-widgets a:not(.pr-btn):hover, 
					#prisma-core-header-inner .pr-header-widgets .prisma-core-active,
					.prisma-core-logo .site-title a:hover, 
					.pr-hamburger:hover, 
					.is-mobile-menu-active .pr-hamburger,
					#prisma-core-header-inner .prisma-core-nav > ul > li > a:hover,
					#prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a {
						color: ' . $header_color['link-hover-color'] . ';
					}
				';
			}

			// Header border.
			$css .= $this->get_design_options_field_css( '#prisma-core-header-inner', 'header_border', 'border' );

			// Header separator color.
			$css .= $this->get_design_options_field_css( '.pr-header-widget', 'header_border', 'separator_color' );

			// Main navigation breakpoint.
			$css .= '
				@media screen and (max-width: ' . intval( prisma_core_option( 'main_nav_mobile_breakpoint' ) ) . 'px) {

					#prisma-core-header-inner .prisma-core-nav {
						display: none;
						color: #000;
					}

					.pr-mobile-nav {
						display: inline-flex;
					}

					#prisma-core-header-inner {
						position: relative;
					}

					#prisma-core-header-inner .prisma-core-nav > ul > li > a {
						color: inherit;
					}

					#prisma-core-header-inner .pr-nav-container {
						position: static;
						border: none;
					}

					#prisma-core-header-inner .site-navigation {
						display: none;
						position: absolute;
						top: 100%;
						width: 100%;
						left: 0;
						right: 0;
						margin: -1px 0 0;
						background: #FFF;
						border-top: 1px solid #eaeaea;
						box-shadow: 0 15px 25px -10px  rgba(50, 52, 54, 0.125);
						z-index: 999;
						font-size: 1rem;
						padding: 0;
					}

					#prisma-core-header-inner .site-navigation > ul {
						max-height: initial;
						display: block;
					}

					#prisma-core-header-inner .site-navigation > ul > li > a {
						padding: 0 !important;
					}

					#prisma-core-header-inner .site-navigation > ul li {
						display: block;
						width: 100%;
						padding: 0;
						margin: 0;
						margin-left: 0 !important;
					}

					#prisma-core-header-inner .site-navigation > ul .sub-menu {
						position: static;
						display: none;
						border: none;
						box-shadow: none;
						border: 0;
						opacity: 1;
						visibility: visible;
						font-size: rem(14px);
						transform: none;
						background: #f8f8f8;
						pointer-events: all;
						min-width: initial;
						left: 0;
						padding: 0;
						margin: 0;
						border-radius: 0;
						line-height: inherit;
					}

					#prisma-core-header-inner .site-navigation > ul .sub-menu > li > a > span {
						padding-left: 50px !important;
					}

					#prisma-core-header-inner .site-navigation > ul .sub-menu .sub-menu > li > a > span {
						padding-left: 70px !important;
					}

					#prisma-core-header-inner .site-navigation > ul .sub-menu a > span {
						padding: 10px 30px 10px 50px;
					}

					#prisma-core-header-inner .site-navigation > ul a {
						padding: 0;
						position: relative;
						border-bottom: 1px solid #eaeaea;
						background: none;
					}

					#prisma-core-header-inner .site-navigation > ul a > span {
						padding: 10px 30px !important;
						width: 100%;
						display: block;
					}

					#prisma-core-header-inner .site-navigation > ul a > span::after,
					#prisma-core-header-inner .site-navigation > ul a > span::before {
						display: none !important;
					}

					#prisma-core-header-inner .site-navigation > ul a > span.description {
						display: none;
					}

					#prisma-core-header-inner .site-navigation > ul .menu-item-has-children > a > span {
						max-width: calc(100% - 50px);
					}

					#prisma-core-header-inner .prisma-core-nav .menu-item-has-children>a > span, 
					#prisma-core-header-inner .prisma-core-nav .page_item_has_children>a > span {
					    border-right: 1px solid rgba(0,0,0,.09);
					}

					#prisma-core-header-inner .prisma-core-nav .menu-item-has-children>a > .pr-icon, 
					#prisma-core-header-inner .prisma-core-nav .page_item_has_children>a > .pr-icon {
						transform: none;
						width: 50px;
					    margin: 0;
					    position: absolute;
					    right: 0;
					    pointer-events: none;
					    height: 1em;
					}

					#prisma-core-header-inner .site-navigation > ul .menu-item-has-children.pr-open > a > .pr-icon {
						transform: rotate(180deg);
					}

					.prisma-core-header-layout-3 .prisma-core-widget-location-left .dropdown-item {
						left: auto;
						right: -7px;
					}

					.prisma-core-header-layout-3 .prisma-core-widget-location-left .dropdown-item::after {
						left: auto;
						right: 8px;
					}

					.prisma-core-nav .sub-menu li.current-menu-item > a {
						font-weight: bold;
					}

					' . $header_layout_3_additional_css . '
				}
			';

			/**
			 * Main Navigation.
			 */

			// Font Color.
			$main_nav_font_color = prisma_core_option( 'main_nav_font_color' );

			if ( $main_nav_font_color['link-color'] ) {
				$css .= '#prisma-core-header-inner .prisma-core-nav > ul > li > a { color: ' . $main_nav_font_color['link-color'] . '; }';
			}

			if ( $main_nav_font_color['link-hover-color'] ) {
				$css .= '
					#prisma-core-header-inner .prisma-core-nav > ul > li > a:hover,
					#prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a,
					#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a {
						color: ' . $main_nav_font_color['link-hover-color'] . ';
					}
				';
			}

			if ( 'layout-3' === prisma_core_option( 'header_layout' ) ) {

				// Background.
				$css .= $this->get_design_options_field_css( '.prisma-core-header-layout-3 .pr-nav-container', 'main_nav_background', 'background' );

				// Border.
				$css .= $this->get_design_options_field_css( '.prisma-core-header-layout-3 .pr-nav-container', 'main_nav_border', 'border' );
			}

			// Font size.
			$css .= $this->get_range_field_css( '.prisma-core-nav.pr-header-element, .prisma-core-header-layout-1 .pr-header-widgets, .prisma-core-header-layout-2 .pr-header-widgets', 'font-size', 'main_nav_font_size', false );

			/**
			 * Hero Section.
			 */
			if ( prisma_core_option( 'enable_hero' ) ) {
				// Hero height.
				$css .= '#hero .pr-hover-slider .hover-slide-item { height: ' . prisma_core_option( 'hero_hover_slider_height' ) . 'px; }';
			}

			/**
			 * Pre Footer.
			 */
			if ( prisma_core_option( 'enable_pre_footer_cta' ) ) {

				// Call to Action.
				if ( prisma_core_option( 'enable_pre_footer_cta' ) ) {

					$cta_style = absint( prisma_core_option( 'pre_footer_cta_style' ) );

					// Background.
					$cta_background = prisma_core_option( 'pre_footer_cta_background' );

					if ( 1 === $cta_style || is_customize_preview() ) {
						$css .= $this->get_design_options_field_css( '.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::after', 'pre_footer_cta_background', 'background' );
					}

					if ( 2 === $cta_style || is_customize_preview() ) {
						$css .= $this->get_design_options_field_css( '.pr-pre-footer-cta-style-2 #pr-pre-footer::after', 'pre_footer_cta_background', 'background' );
					}

					if ( 'image' === $cta_background['background-type'] && isset( $cta_background['background-color-overlay'] ) && $cta_background['background-color-overlay'] ) {
						$css .= '
							.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::before,
			 				.pr-pre-footer-cta-style-2 #pr-pre-footer::before {
			 					background-color: ' . $cta_background['background-color-overlay'] . ';
			 				}
			 				';
					}

					// Text color.
					$css .= $this->get_design_options_field_css( '#pr-pre-footer .h2, #pr-pre-footer .h3, #pr-pre-footer .h4', 'pre_footer_cta_text_color', 'color' );

					// Border.
					if ( 1 === $cta_style || is_customize_preview() ) {
						$css .= $this->get_design_options_field_css( '.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::before', 'pre_footer_cta_border', 'border' );
					}

					if ( 2 === $cta_style || is_customize_preview() ) {
						$css .= $this->get_design_options_field_css( '.pr-pre-footer-cta-style-2 #pr-pre-footer::before', 'pre_footer_cta_border', 'border' );
					}

					// Font size.
					$css .= $this->get_range_field_css( '#pr-pre-footer .h3', 'font-size', 'pre_footer_cta_font_size', true );
				}
			}

			// Footer Background.
			if ( prisma_core_option( 'enable_footer' ) || prisma_core_option( 'enable_copyright' ) ) {

				// Background.
				$css .= $this->get_design_options_field_css( '#colophon', 'footer_background', 'background' );

				// Footer font color.
				$footer_font_color = prisma_core_option( 'footer_text_color' );

				// Footer text color.
				if ( isset( $footer_font_color['text-color'] ) && $footer_font_color['text-color'] ) {
					$css .= '
						#colophon { 
							color: ' . $footer_font_color['text-color'] . ';
						}
					';
				}

				// Footer link color.
				if ( isset( $footer_font_color['link-color'] ) && $footer_font_color['link-color'] ) {
					$css .= '
						#colophon a { 
							color: ' . $footer_font_color['link-color'] . '; 
						}
					';
				}

				// Footer link hover color.
				if ( isset( $footer_font_color['link-hover-color'] ) && $footer_font_color['link-hover-color'] ) {
					$css .= '
						#colophon a:hover,
						#colophon li.current_page_item > a,
						#colophon .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon { 
							color: ' . $footer_font_color['link-hover-color'] . ';
						}
					';
				}

				// Footer widget title.
				if ( isset( $footer_font_color['widget-title-color'] ) && $footer_font_color['widget-title-color'] ) {
					$css .= '
						#colophon .widget-title { 
							color: ' . $footer_font_color['widget-title-color'] . ';
						}
					';
				}
			}

			// Main Footer border.
			if ( prisma_core_option( 'enable_footer' ) ) {

				// Border.
				$footer_border = prisma_core_option( 'footer_border' );

				if ( $footer_border['border-top-width'] ) {
					$css .= '
						#colophon {
							border-top-width: ' . $footer_border['border-top-width'] . 'px;
							border-top-style: ' . $footer_border['border-style'] . ';
							border-top-color: ' . $footer_border['border-color'] . ';
						}
					';
				}

				if ( $footer_border['border-bottom-width'] ) {
					$css .= '
						#colophon {
							border-bottom-width: ' . $footer_border['border-bottom-width'] . 'px;
							border-bottom-style: ' . $footer_border['border-style'] . ';
							border-bottom-color: ' . $footer_border['border-color'] . ';
						}
					';
				}
			}

			// Sidebar.
			$css .= '
				#secondary {
					width: ' . intval( prisma_core_option( 'sidebar_width' ) ) . '%;
				}

				body:not(.prisma-core-no-sidebar) #primary {
					max-width: ' . intval( 100 - intval( prisma_core_option( 'sidebar_width' ) ) ) . '%;
				}
			';

			// Content background.
			$boxed_content_background_color = prisma_core_option( 'boxed_content_background_color' );

			// Boxed Separated Layout specific CSS.
			$css .= '
				.prisma-core-layout__boxed-separated.author .author-box, 
				.prisma-core-layout__boxed-separated #content, 
				.prisma-core-layout__boxed-separated.prisma-core-sidebar-style-3 #secondary .pr-widget, 
				.prisma-core-layout__boxed-separated.prisma-core-sidebar-style-3 .elementor-widget-sidebar .pr-widget, 
				.prisma-core-layout__boxed-separated.blog .prisma-core-article, 
				.prisma-core-layout__boxed-separated.search-results .prisma-core-article, 
				.prisma-core-layout__boxed-separated.category .prisma-core-article {
					background-color: ' . $boxed_content_background_color . ';
				}

				@media screen and (max-width: 960px) {
					.prisma-core-layout__boxed-separated #page {
						background-color: ' . $boxed_content_background_color . ';
					}
				}
			';

			$css .= '
				.prisma-core-layout__boxed #page {
					background-color: ' . $boxed_content_background_color . ';
				}
			';

			// Content text color.
			$content_text_color = prisma_core_option( 'content_text_color' );

			$css .= '
				body {
					color: ' . $content_text_color . ';
				}

				.comment-form .comment-notes,
				#comments .no-comments,
				#page .wp-caption .wp-caption-text,
				#comments .comment-meta,
				.comments-closed,
				.entry-meta,
				.pr-entry cite,
				legend,
				.pr-page-header-description,
				.page-links em,
				.site-content .page-links em,
				.single .entry-footer .last-updated,
				.single .post-nav .post-nav-title,
				#main .widget_recent_comments span,
				#main .widget_recent_entries span,
				#main .widget_calendar table > caption,
				.post-thumb-caption,
				.wp-block-image figcaption,
				.wp-block-embed figcaption {
					color: ' . prisma_core_hex2rgba( $content_text_color, 0.73 ) . ';
				}

				.navigation .nav-links .page-numbers svg {
					fill: ' . prisma_core_hex2rgba( $content_text_color, 0.73 ) . ';
				}
			';

			// Lightened or darkened background color for backgrounds, borders & inputs.
			$background_color = prisma_core_get_background_color();

			$content_text_color_offset = prisma_core_light_or_dark( $background_color, prisma_core_luminance( $background_color, -0.045 ), prisma_core_luminance( $background_color, 0.2 ) );

			// Only add for dark background color.
			if ( ! prisma_core_is_light_color( $background_color ) ) {
				$css .= '
					#content textarea,
					#content input[type="text"],
					#content input[type="number"],
					#content input[type="email"],
					#content input[type=password],
					#content input[type=tel],
					#content input[type=url],
					#content input[type=search],
					#content input[type=date] {
						background-color: ' . $background_color . ';
					}
				';

				// Offset border color.
				$css .= '
					.prisma-core-sidebar-style-3 #secondary .pr-widget {
						border-color: ' . $content_text_color_offset . ';
					}
				';

				// Offset background color.
				$css .= '
					.entry-meta .entry-meta-elements > span:before {
						background-color: ' . $content_text_color_offset . ';
					}
				';
			}

			// Content link hover color.
			$css .= '
				.content-area a:not(.pr-btn):not(.wp-block-button__link):hover,
				#secondary .prisma-companion-custom-list-widget .pr-entry a:not(.pr-btn):hover,
				.pr-breadcrumbs a:hover {
					color: ' . prisma_core_option( 'content_link_hover_color' ) . ';
				}
			';

			// Headings Color.
			$css .= '
				h1, h2, h3, h4, .h4, h5, h6,
				.h1, .h2, .h3,
				.prisma-core-logo .site-title,
				.error-404 .page-header h1 {
					color: ' . prisma_core_option( 'headings_color' ) . ';
				}
			';

			// Container width.
			$css .= '
				.pr-container,
				.alignfull.pr-wrap-content > div {
					max-width: ' . prisma_core_option( 'container_width' ) . 'px;
				}

				.prisma-core-layout__boxed #page,
				.prisma-core-layout__boxed.pr-sticky-header.prisma-core-is-mobile #prisma-core-header-inner,
				.prisma-core-layout__boxed.pr-sticky-header:not(.prisma-core-header-layout-3) #prisma-core-header-inner,
				.prisma-core-layout__boxed.pr-sticky-header:not(.prisma-core-is-mobile).prisma-core-header-layout-3 #prisma-core-header-inner .pr-nav-container > .pr-container {
					max-width: ' . ( intval( prisma_core_option( 'container_width' ) ) + 100 ) . 'px;
				}
			';

			// Adjust fullwidth sections for boxed layouts.
			if ( 'boxed' === prisma_core_option( 'site_layout' ) || is_customize_preview() ) {
				$css .= '
					@media screen and (max-width: ' . intval( prisma_core_option( 'container_width' ) ) . 'px) {
						body.prisma-core-layout__boxed.prisma-core-no-sidebar .elementor-section.elementor-section-stretched,
						body.prisma-core-layout__boxed.prisma-core-no-sidebar .pr-fw-section,
						body.prisma-core-layout__boxed.prisma-core-no-sidebar .entry-content .alignfull {
							margin-left: -50px !important;
							margin-right: -50px !important;
						}
					}
				';
			}

			// Logo max height.
			$css .= $this->get_range_field_css( '.prisma-core-logo img', 'max-height', 'logo_max_height' );
			$css .= $this->get_range_field_css( '.prisma-core-logo img.pr-svg-logo', 'height', 'logo_max_height' );

			// Logo margin.
			$css .= $this->get_spacing_field_css( '.prisma-core-logo .logo-inner', 'margin', 'logo_margin' );

			/**
			 * Transparent header.
			 */

			// Logo max height.
			$css .= $this->get_range_field_css( '.pr-tsp-header .prisma-core-logo img', 'max-height', 'tsp_logo_max_height' );
			$css .= $this->get_range_field_css( '.pr-tsp-header .prisma-core-logo img.pr-svg-logo', 'height', 'tsp_logo_max_height' );

			// Logo margin.
			$css .= $this->get_spacing_field_css( '.pr-tsp-header .prisma-core-logo .logo-inner', 'margin', 'tsp_logo_margin' );

			// Main Header custom background.
			$css .= $this->get_design_options_field_css( '.pr-tsp-header #prisma-core-header-inner', 'tsp_header_background', 'background' );

			/** Font Colors */

			$tsp_font_color = prisma_core_option( 'tsp_header_font_color' );

			// Header text color.
			if ( isset( $tsp_font_color['text-color'] ) && $tsp_font_color['text-color'] ) {
				$css .= '
					.pr-tsp-header .prisma-core-logo .site-description {
						color: ' . $tsp_font_color['text-color'] . ';
					}
				';
			}

			// Header link color.
			if ( isset( $tsp_font_color['link-color'] ) && $tsp_font_color['link-color'] ) {
				$css .= '
					.pr-tsp-header #prisma-core-header,
					.pr-tsp-header .pr-header-widgets a:not(.pr-btn),
					.pr-tsp-header .prisma-core-logo a,
					.pr-tsp-header .pr-hamburger,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li > a { 
						color: ' . $tsp_font_color['link-color'] . ';
					}
				';
			}

			// Header link hover color.
			if ( isset( $tsp_font_color['link-hover-color'] ) && $tsp_font_color['link-hover-color'] ) {
				$css .= '
					.pr-tsp-header .pr-header-widgets a:not(.pr-btn):hover, 
					.pr-tsp-header #prisma-core-header-inner .pr-header-widgets .prisma-core-active,
					.pr-tsp-header .prisma-core-logo .site-title a:hover, 
					.pr-tsp-header .pr-hamburger:hover, 
					.is-mobile-menu-active .pr-tsp-header .pr-hamburger,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li > a:hover,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a,
					.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a {
						color: ' . $tsp_font_color['link-hover-color'] . ';
					}
				';
			}

			/** Border Color */
			$css .= $this->get_design_options_field_css( '.pr-tsp-header #prisma-core-header-inner', 'tsp_header_border', 'border' );

			/** Separator Color */
			$css .= $this->get_design_options_field_css( '.pr-tsp-header .pr-header-widget', 'tsp_header_border', 'separator_color' );

			/**
			 * Page Header.
			 */
			if ( prisma_core_option( 'page_header_enable' ) ) {

				// Font size.
				$css .= $this->get_range_field_css( '#page .page-header .page-title', 'font-size', 'page_header_font_size', true );

				// Page Title spacing.
				$css .= $this->get_spacing_field_css( '.pr-page-title-align-left .page-header.pr-has-page-title, .pr-page-title-align-right .page-header.pr-has-page-title, .pr-page-title-align-center .page-header .pr-page-header-wrapper', 'padding', 'page_header_spacing' );

				// Page Header background.
				$css .= $this->get_design_options_field_css( '.pr-tsp-header:not(.pr-tsp-absolute) #masthead', 'page_header_background', 'background' );
				$css .= $this->get_design_options_field_css( '.page-header', 'page_header_background', 'background' );

				// Page Header font color.
				$page_header_color = prisma_core_option( 'page_header_text_color' );

				// Page Header text color.
				if ( isset( $page_header_color['text-color'] ) && $page_header_color['text-color'] ) {
					$css .= '
						.page-header .page-title { 
							color: ' . $page_header_color['text-color'] . '; }

						.page-header .pr-page-header-description {
							color: ' . prisma_core_hex2rgba( $page_header_color['text-color'], 0.75 ) . '; 
						}
					';
				}

				// Page Header link color.
				if ( isset( $page_header_color['link-color'] ) && $page_header_color['link-color'] ) {
					$css .= '
						.page-header .pr-breadcrumbs a { 
							color: ' . $page_header_color['link-color'] . '; }

						.page-header .pr-breadcrumbs span,
						.page-header .breadcrumb-trail .trail-items li::after, .page-header .pr-breadcrumbs .separator {
							color: ' . prisma_core_hex2rgba( $page_header_color['link-color'], 0.75 ) . '; 
						}
					';
				}

				// Page Header link hover color.
				if ( isset( $page_header_color['link-hover-color'] ) && $page_header_color['link-hover-color'] ) {
					$css .= '
						.page-header .pr-breadcrumbs a:hover { 
							color: ' . $page_header_color['link-hover-color'] . '; }
					';
				}

				// Page Header border color.
				$page_header_border = prisma_core_option( 'page_header_border' );

				$css .= $this->get_design_options_field_css( '.page-header', 'page_header_border', 'border' );
			}

			/**
			 * Breadcrumbs.
			 */
			if ( prisma_core_option( 'breadcrumbs_enable' ) ) {

				// Spacing.
				$css .= $this->get_spacing_field_css( '.pr-breadcrumbs', 'padding', 'breadcrumbs_spacing' );

				if ( 'below-header' === prisma_core_option( 'breadcrumbs_position' ) ) {

					// Background.
					$css .= $this->get_design_options_field_css( '.pr-breadcrumbs', 'breadcrumbs_background', 'background' );

					// Border.
					$css .= $this->get_design_options_field_css( '.pr-breadcrumbs', 'breadcrumbs_border', 'border' );

					// Text Color.
					$css .= $this->get_design_options_field_css( '.pr-breadcrumbs', 'breadcrumbs_text_color', 'color' );
				}
			}

			/**
			 * Copyright Bar.
			 */
			if ( prisma_core_option( 'enable_copyright' ) ) {
				$css .= $this->get_design_options_field_css( '#prisma-core-copyright', 'copyright_background', 'background' );

				// Copyright font color.
				$copyright_color = prisma_core_option( 'copyright_text_color' );

				// Copyright text color.
				if ( isset( $copyright_color['text-color'] ) && $copyright_color['text-color'] ) {
					$css .= '
						#prisma-core-copyright { 
							color: ' . $copyright_color['text-color'] . '; }
					';
				}

				// Copyright link color.
				if ( isset( $copyright_color['link-color'] ) && $copyright_color['link-color'] ) {
					$css .= '
						#prisma-core-copyright a { 
							color: ' . $copyright_color['link-color'] . '; }
					';
				}

				// Copyright link hover color.
				if ( isset( $copyright_color['link-hover-color'] ) && $copyright_color['link-hover-color'] ) {
					$css .= '
						#prisma-core-copyright a:hover,
						#prisma-core-copyright .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon,
						#prisma-core-copyright .prisma-core-nav > ul > li.current-menu-item > a,
						#prisma-core-copyright .prisma-core-nav > ul > li.current-menu-ancestor > a,
						#prisma-core-copyright .prisma-core-nav > ul > li:hover > a { 
							color: ' . $copyright_color['link-hover-color'] . '; }
					';
				}

				// Copyright separator color.
				$footer_text_color = prisma_core_option( 'footer_text_color' );
				$footer_text_color = $footer_text_color['text-color'];

				$copyright_separator_color = prisma_core_light_or_dark( $footer_text_color, 'rgba(255,255,255,0.1)', 'rgba(0,0,0,0.1)' );

				$css .= '
					#prisma-core-copyright.contained-separator > .pr-container::before {
						background-color: ' . $copyright_separator_color . ';
					}

					#prisma-core-copyright.fw-separator {
						border-top-color: ' . $copyright_separator_color . ';
					}
				';
			}

			/**
			 * Typography.
			 */

			// Base HTML font size.
			$css .= $this->get_range_field_css( 'html', 'font-size', 'html_base_font_size', true, 'px' );

			// Font smoothing.
			if ( prisma_core_option( 'font_smoothing' ) ) {
				$css .= '
					* {
						-moz-osx-font-smoothing: grayscale;
						-webkit-font-smoothing: antialiased;
					}
				';
			}

			// Body.
			$css .= $this->get_typography_field_css( 'body', 'body_font' );

			// Headings.
			$css .= $this->get_typography_field_css( 'h1, .h1, .prisma-core-logo .site-title, .page-header .page-title, h2, .h2, h3, .h3, h4, .h4, h5, h6', 'headings_font' );

			$css .= $this->get_typography_field_css( 'h1, .h1, .prisma-core-logo .site-title, .page-header .page-title', 'h1_font' );
			$css .= $this->get_typography_field_css( 'h2, .h2', 'h2_font' );
			$css .= $this->get_typography_field_css( 'h3, .h3', 'h3_font' );
			$css .= $this->get_typography_field_css( 'h4, .h4', 'h4_font' );
			$css .= $this->get_typography_field_css( 'h5', 'h5_font' );
			$css .= $this->get_typography_field_css( 'h6', 'h6_font' );
			$css .= $this->get_typography_field_css( 'h1 em, h2 em, h3 em, h4 em, h5 em, h6 em, .h1 em, .h2 em, .h3 em, .h4 em, .prisma-core-logo .site-title em, .error-404 .page-header h1 em', 'heading_em_font' );

			// Emphasized Heading.
			$css .= $this->get_typography_field_css( 'h1 em, h2 em, h3 em, h4 em, h5 em, h6 em, .h1 em, .h2 em, .h3 em, .h4 em, .prisma-core-logo .site-title em, .error-404 .page-header h1 em', 'heading_em_font' );

			// Site Title font size.
			$css .= $this->get_range_field_css( '#prisma-core-header .prisma-core-logo .site-title', 'font-size', 'logo_text_font_size', true );

			// Sidebar widget title.
			$css .= $this->get_range_field_css( '#main .widget-title', 'font-size', 'sidebar_widget_title_font_size', true );

			// Footer widget title.
			$css .= $this->get_range_field_css( '#colophon .widget-title', 'font-size', 'footer_widget_title_font_size', true );

			// Blog Single Post - Title Spacing.
			$css .= $this->get_spacing_field_css( '.pr-single-title-in-page-header #page .page-header .pr-page-header-wrapper', 'padding', 'single_title_spacing', true );

			// Blog Single Post - Content Font Size.
			$css .= $this->get_range_field_css( '.single-post .entry-content', 'font-size', 'single_content_font_size', true );

			// Blog Single Post - narrow container.
			if ( 'narrow' === prisma_core_option( 'single_content_width' ) ) {
				$css .= '
					.single-post.narrow-content .entry-content > :not([class*="align"]):not([class*="gallery"]):not(.wp-block-image):not(.quote-inner):not(.quote-post-bg), 
					.single-post.narrow-content .mce-content-body:not([class*="page-template-full-width"]) > :not([class*="align"]):not([data-wpview-type*="gallery"]):not(blockquote):not(.mceTemp), 
					.single-post.narrow-content .entry-footer, 
					.single-post.narrow-content .entry-content > .alignwide,
					.single-post.narrow-content p.has-background:not(.alignfull):not(.alignwide),
					.single-post.narrow-content .post-nav, 
					.single-post.narrow-content #prisma-core-comments-toggle, 
					.single-post.narrow-content #comments, 
					.single-post.narrow-content .entry-content .aligncenter, .single-post.narrow-content .pr-narrow-element, 
					.single-post.narrow-content.pr-single-title-in-content .entry-header, 
					.single-post.narrow-content.pr-single-title-in-content .entry-meta, 
					.single-post.narrow-content.pr-single-title-in-content .post-category,
					.single-post.narrow-content.prisma-core-no-sidebar .pr-page-header-wrapper,
					.single-post.narrow-content.prisma-core-no-sidebar .pr-breadcrumbs nav {
						max-width: ' . prisma_core_option( 'single_narrow_container_width' ) . 'px;
						margin-left: auto;
						margin-right: auto;
					}

					.single-post.narrow-content .author-box,
					.single-post.narrow-content .entry-content > .alignwide,
					.single.pr-single-title-in-page-header .page-header.pr-align-center .pr-page-header-wrapper {
						max-width: ' . ( intval( prisma_core_option( 'single_narrow_container_width' ) ) + 70 ) . 'px;
					}
				';
			}

			// Allow CSS to be filtered.
			$css = apply_filters( 'prisma_core_dynamic_styles', $css );

			// Add user custom CSS.
			if ( $custom_css || ! is_customize_preview() ) {
				$css .= wp_get_custom_css();
			}

			// Minify the CSS code.
			$css = $this->minify( $css );

			return $css;
		}

		/**
		 * Update dynamic css file with new CSS. Cleans caches after that.
		 *
		 * @return [Boolean] returns true if successfully updated the dynamic file.
		 */
		public function update_dynamic_file() {

			$css = $this->get_css( true );

			if ( empty( $css ) || '' === trim( $css ) ) {
				return;
			}

			// Load file.php file.
			require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php'; // phpcs:ignore

			global $wp_filesystem;

			// Check if the the global filesystem isn't setup yet.
			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			$wp_filesystem->mkdir( $this->dynamic_css_path );

			if ( $wp_filesystem->put_contents( $this->dynamic_css_path . 'dynamic-styles.css', $css ) ) {
				$this->clean_cache();
				set_transient( 'prisma_core_has_dynamic_css', true, 0 );
				return true;
			}

			return false;
		}

		/**
		 * Delete dynamic css file.
		 *
		 * @return void
		 */
		public function delete_dynamic_file() {

			// Load file.php file.
			require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php'; // phpcs:ignore

			global $wp_filesystem;

			// Check if the the global filesystem isn't setup yet.
			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			$wp_filesystem->delete( $this->dynamic_css_path . 'dynamic-styles.css' );

			delete_transient( 'prisma_core_has_dynamic_css' );
		}

		/**
		 * Simple CSS code minification.
		 *
		 * @param  string $css code to be minified.
		 * @return string, minifed code
		 * @since  1.0.0
		 */
		private function minify( $css ) {
			$css = preg_replace( '/\s+/', ' ', $css );
			$css = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $css );
			$css = preg_replace( '/(,|:|;|\{|}) /', '$1', $css );
			$css = preg_replace( '/ (,|;|\{|})/', '$1', $css );
			$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
			$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );

			return trim( $css );
		}

		/**
		 * Cleans various caches. Compatible with cache plugins.
		 *
		 * @since 1.0.0
		 */
		private function clean_cache() {

			// If W3 Total Cache is being used, clear the cache.
			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				w3tc_pgcache_flush();
			}

			// if WP Super Cache is being used, clear the cache.
			if ( function_exists( 'wp_cache_clean_cache' ) ) {
				global $file_prefix;
				wp_cache_clean_cache( $file_prefix );
			}

			// If SG CachePress is installed, reset its caches.
			if ( class_exists( 'SG_CachePress_Supercacher' ) ) {
				if ( method_exists( 'SG_CachePress_Supercacher', 'purge_cache' ) ) {
					SG_CachePress_Supercacher::purge_cache();
				}
			}

			// Clear caches on WPEngine-hosted sites.
			if ( class_exists( 'WpeCommon' ) ) {

				if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
					WpeCommon::purge_memcached();
				}

				if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
					WpeCommon::clear_maxcdn_cache();
				}

				if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
					WpeCommon::purge_varnish_cache();
				}
			}

			// Clean OpCache.
			if ( function_exists( 'opcache_reset' ) ) {
				opcache_reset(); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.opcache_resetFound
			}

			// Clean WordPress cache.
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
			}
		}

		/**
		 * Prints spacing field CSS based on passed params.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $css_selector CSS selector.
		 * @param  string $css_property CSS property, such as 'margin', 'padding' or 'border'.
		 * @param  string $setting_id The ID of the customizer setting containing all information about the setting.
		 * @param  bool   $responsive Has responsive values.
		 * @return string  Generated CSS.
		 */
		public function get_spacing_field_css( $css_selector, $css_property, $setting_id, $responsive = true ) {

			// Get the saved setting.
			$setting = prisma_core_option( $setting_id );

			// If setting doesn't exist, return.
			if ( ! is_array( $setting ) ) {
				return;
			}

			// Get the unit. Defaults to px.
			$unit = 'px';

			if ( isset( $setting['unit'] ) ) {
				if ( $setting['unit'] ) {
					$unit = $setting['unit'];
				}

				unset( $setting['unit'] );
			}

			// CSS buffer.
			$css_buffer = '';

			// Loop through options.
			foreach ( $setting as $key => $value ) {

				// Check if responsive options are available.
				if ( is_array( $value ) ) {

					if ( 'desktop' === $key ) {
						$mq_open  = '';
						$mq_close = '';
					} elseif ( 'tablet' === $key ) {
						$mq_open  = '@media only screen and (max-width: 768px) {';
						$mq_close = '}';
					} elseif ( 'mobile' === $key ) {
						$mq_open  = '@media only screen and (max-width: 480px) {';
						$mq_close = '}';
					} else {
						$mq_open  = '';
						$mq_close = '';
					}

					// Add media query prefix.
					$css_buffer .= $mq_open . $css_selector . '{';

					// Loop through all choices.
					foreach ( $value as $pos => $val ) {

						if ( empty( $val ) ) {
							continue;
						}

						if ( 'border' === $css_property ) {
							$pos .= '-width';
						}

						$css_buffer .= $css_property . '-' . $pos . ': ' . intval( $val ) . $unit . ';';
					}

					$css_buffer .= '}' . $mq_close;

				} else {

					if ( 'border' === $css_property ) {
						$key .= '-width';
					}

					$css_buffer .= $css_property . '-' . $key . ': ' . intval( $value ) . $unit . ';';
				}
			}

			// Check if field is has responsive values.
			if ( ! $responsive ) {
				$css_buffer = $css_selector . '{' . $css_buffer . '}';
			}

			// Finally, return the generated CSS code.
			return $css_buffer;
		}

		/**
		 * Prints range field CSS based on passed params.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $css_selector CSS selector.
		 * @param  string $css_property CSS property, such as 'margin', 'padding' or 'border'.
		 * @param  string $setting_id The ID of the customizer setting containing all information about the setting.
		 * @param  bool   $responsive Has responsive values.
		 * @param  string $unit Unit.
		 * @return string  Generated CSS.
		 */
		public function get_range_field_css( $css_selector, $css_property, $setting_id, $responsive = true, $unit = 'px' ) {

			// Get the saved setting.
			$setting = prisma_core_option( $setting_id );

			// If just a single value option.
			if ( ! is_array( $setting ) ) {
				return $css_selector . ' { ' . $css_property . ': ' . $setting . $unit . '; }';
			}

			// Resolve units.
			if ( isset( $setting['unit'] ) ) {
				if ( $setting['unit'] ) {
					$unit = $setting['unit'];
				}

				unset( $setting['unit'] );
			}

			// CSS buffer.
			$css_buffer = '';

			if ( is_array( $setting ) && ! empty( $setting ) ) {

				// Media query syntax wrap.
				$mq_open  = '';
				$mq_close = '';

				// Loop through options.
				foreach ( $setting as $key => $value ) {

					if ( empty( $value ) ) {
						continue;
					}

					if ( 'desktop' === $key ) {
						$mq_open  = '';
						$mq_close = '';
					} elseif ( 'tablet' === $key ) {
						$mq_open  = '@media only screen and (max-width: 768px) {';
						$mq_close = '}';
					} elseif ( 'mobile' === $key ) {
						$mq_open  = '@media only screen and (max-width: 480px) {';
						$mq_close = '}';
					} else {
						$mq_open  = '';
						$mq_close = '';
					}

					// Add media query prefix.
					$css_buffer .= $mq_open . $css_selector . '{';
					$css_buffer .= $css_property . ': ' . floatval( $value ) . $unit . ';';
					$css_buffer .= '}' . $mq_close;
				}
			}

			// Finally, return the generated CSS code.
			return $css_buffer;
		}

		/**
		 * Prints design options field CSS based on passed params.
		 *
		 * @since 1.0.0
		 * @param string       $css_selector CSS selector.
		 * @param string|mixed $setting The ID of the customizer setting containing all information about the setting.
		 * @param string       $type Design options field type.
		 * @return string      Generated CSS.
		 */
		public function get_design_options_field_css( $css_selector, $setting, $type ) {

			if ( is_string( $setting ) ) {
				// Get the saved setting.
				$setting = prisma_core_option( $setting );
			}

			// Setting has to be array.
			if ( ! is_array( $setting ) || empty( $setting ) ) {
				return;
			}

			// CSS buffer.
			$css_buffer = '';

			// Background.
			if ( 'background' === $type ) {

				// Background type.
				$background_type = $setting['background-type'];

				if ( 'color' === $background_type ) {
					if ( isset( $setting['background-color'] ) && ! empty( $setting['background-color'] ) ) {
						$css_buffer .= 'background: ' . $setting['background-color'] . ';';
					}
				} elseif ( 'gradient' === $background_type ) {

					$css_buffer .= 'background: ' . $setting['gradient-color-1'] . ';';

					if ( 'linear' === $setting['gradient-type'] ) {
						$css_buffer .= '
							background: -webkit-linear-gradient(' . $setting['gradient-linear-angle'] . 'deg, ' . $setting['gradient-color-1'] . ' ' . $setting['gradient-color-1-location'] . '%, ' . $setting['gradient-color-2'] . ' ' . $setting['gradient-color-2-location'] . '%);
							background: -o-linear-gradient(' . $setting['gradient-linear-angle'] . 'deg, ' . $setting['gradient-color-1'] . ' ' . $setting['gradient-color-1-location'] . '%, ' . $setting['gradient-color-2'] . ' ' . $setting['gradient-color-2-location'] . '%);
							background: linear-gradient(' . $setting['gradient-linear-angle'] . 'deg, ' . $setting['gradient-color-1'] . ' ' . $setting['gradient-color-1-location'] . '%, ' . $setting['gradient-color-2'] . ' ' . $setting['gradient-color-2-location'] . '%);

						';
					} elseif ( 'radial' === $setting['gradient-type'] ) {
						$css_buffer .= '
							background: -webkit-radial-gradient(' . $setting['gradient-position'] . ', circle, ' . $setting['gradient-color-1'] . ' ' . $setting['gradient-color-1-location'] . '%, ' . $setting['gradient-color-2'] . ' ' . $setting['gradient-color-2-location'] . '%);
							background: -o-radial-gradient(' . $setting['gradient-position'] . ', circle, ' . $setting['gradient-color-1'] . ' ' . $setting['gradient-color-1-location'] . '%, ' . $setting['gradient-color-2'] . ' ' . $setting['gradient-color-2-location'] . '%);
							background: radial-gradient(circle at ' . $setting['gradient-position'] . ', ' . $setting['gradient-color-1'] . ' ' . $setting['gradient-color-1-location'] . '%, ' . $setting['gradient-color-2'] . ' ' . $setting['gradient-color-2-location'] . '%);
						';
					}
				} elseif ( 'image' === $background_type ) {
					$css_buffer .= '
						background-image: url(' . esc_url( $setting['background-image'] ) . ');
						background-size: ' . esc_attr( $setting['background-size'] ) . ';
						background-attachment: ' . esc_attr( $setting['background-attachment'] ) . ';
						background-position: ' . intval( $setting['background-position-x'] ) . '% ' . intval( $setting['background-position-y'] ) . '%;
						background-repeat: ' . esc_attr( $setting['background-repeat'] ) . ';
					';
				}

				$css_buffer = ! empty( $css_buffer ) ? $css_selector . '{' . $css_buffer . '}' : '';

				if ( 'image' === $background_type && isset( $setting['background-color-overlay'] ) && $setting['background-color-overlay'] && isset( $setting['background-image'] ) && $setting['background-image'] ) {
					$css_buffer .= $css_selector . '::after { background-color: ' . esc_attr( $setting['background-color-overlay'] ) . '; }';
				}
			} elseif ( 'color' === $type ) {

				// Text color.
				if ( isset( $setting['text-color'] ) && ! empty( $setting['text-color'] ) ) {
					$css_buffer .= $css_selector . ' { color: ' . esc_attr( $setting['text-color'] ) . '; }';
				}

				// Link Color.
				if ( isset( $setting['link-color'] ) && ! empty( $setting['link-color'] ) ) {
					$css_buffer .= $css_selector . ' a { color: ' . esc_attr( $setting['link-color'] ) . '; }';
				}

				// Link Hover Color.
				if ( isset( $setting['link-hover-color'] ) && ! empty( $setting['link-hover-color'] ) ) {
					$css_buffer .= $css_selector . ' a:hover { color: ' . esc_attr( $setting['link-hover-color'] ) . ' !important; }';
				}
			} elseif ( 'border' === $type ) {

				// Color.
				if ( isset( $setting['border-color'] ) && ! empty( $setting['border-color'] ) ) {
					$css_buffer .= 'border-color:' . esc_attr( $setting['border-color'] ) . ';';
				}

				// Style.
				$allowed_border_styles = array( 'none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset' );
				if ( isset( $setting['border-style'] ) && ! empty( $setting['border-style'] ) && in_array( $setting['border-style'], $allowed_border_styles, true ) ) {
					$css_buffer .= 'border-style: ' . $setting['border-style'] . ';';
				}

				// Width.
				$positions = array( 'top', 'right', 'bottom', 'left' );

				foreach ( $positions as $position ) {
					if ( isset( $setting[ 'border-' . $position . '-width' ] ) && ! empty( $setting[ 'border-' . $position . '-width' ] ) ) {
						$css_buffer .= 'border-' . $position . '-width: ' . intval( $setting[ 'border-' . $position . '-width' ] ) . 'px;';
					}
				}

				$css_buffer = ! empty( $css_buffer ) ? $css_selector . '{' . $css_buffer . '}' : '';
			} elseif ( 'separator_color' === $type && isset( $setting['separator-color'] ) && ! empty( $setting['separator-color'] ) ) {

				// Separator Color.
				$css_buffer .= $css_selector . '::after { background-color:' . esc_attr( $setting['separator-color'] ) . '; }';
			}

			// Finally, return the generated CSS code.
			return $css_buffer;
		}

		/**
		 * Prints typography field CSS based on passed params.
		 *
		 * @since  1.0.0
		 * @param  string       $css_selector CSS selector.
		 * @param  string|mixed $setting The ID of the customizer setting containing all information about the setting.
		 * @return string       Generated CSS.
		 */
		public function get_typography_field_css( $css_selector, $setting ) {

			if ( is_string( $setting ) ) {
				// Get the saved setting.
				$setting = prisma_core_option( $setting );
			}

			// Setting has to be array.
			if ( ! is_array( $setting ) || empty( $setting ) ) {
				return;
			}

			// CSS buffer.
			$css_buffer = '';

			// Properties.
			$properties = array(
				'font-weight',
				'font-style',
				'text-transform',
				'text-decoration',
			);

			foreach ( $properties as $property ) {

				if ( 'inherit' !== $setting[ $property ] ) {
					$css_buffer .= $property . ':' . esc_attr( $setting[ $property ] ) . ';';
				}
			}

			// Font family.
			if ( 'inherit' !== $setting['font-family'] ) {
				$font_family = prisma_core()->fonts->get_font_family( $setting['font-family'] );

				$css_buffer .= 'font-family: ' . esc_attr( $font_family ) . ';';
			}

			// Letter spacing.
			if ( ! empty( $setting['letter-spacing'] ) ) {
				$css_buffer .= 'letter-spacing:' . floatval( $setting['letter-spacing'] ) . esc_attr( $setting['letter-spacing-unit'] ) . ';';
			}

			// Font size.
			if ( ! empty( $setting['font-size-desktop'] ) ) {
				$css_buffer .= 'font-size:' . floatval( $setting['font-size-desktop'] ) . esc_attr( $setting['font-size-unit'] ) . ';';
			}

			// Line Height.
			if ( ! empty( $setting['line-height-desktop'] ) ) {
				$css_buffer .= 'line-height:' . floatval( $setting['line-height-desktop'] ) . ';';
			}

			$css_buffer = $css_buffer ? $css_selector . '{' . $css_buffer . '}' : '';

			// Responsive options - tablet.
			$tablet = '';

			if ( ! empty( $setting['font-size-tablet'] ) ) {
				$tablet .= 'font-size:' . floatval( $setting['font-size-tablet'] ) . esc_attr( $setting['font-size-unit'] ) . ';';
			}

			if ( ! empty( $setting['line-height-tablet'] ) ) {
				$tablet .= 'line-height:' . floatval( $setting['line-height-tablet'] ) . ';';
			}

			$tablet = ! empty( $tablet ) ? '@media only screen and (max-width: 768px) {' . $css_selector . '{' . $tablet . '} }' : '';

			$css_buffer .= $tablet;

			// Responsive options - mobile.
			$mobile = '';

			if ( ! empty( $setting['font-size-mobile'] ) ) {
				$mobile .= 'font-size:' . floatval( $setting['font-size-mobile'] ) . esc_attr( $setting['font-size-unit'] ) . ';';
			}

			if ( ! empty( $setting['line-height-mobile'] ) ) {
				$mobile .= 'line-height:' . floatval( $setting['line-height-mobile'] ) . ';';
			}

			$mobile = ! empty( $mobile ) ? '@media only screen and (max-width: 480px) {' . $css_selector . '{' . $mobile . '} }' : '';

			$css_buffer .= $mobile;

			// Equeue google fonts.
			if ( prisma_core()->fonts->is_google_font( $setting['font-family'] ) ) {

				$params = array();

				if ( 'inherit' !== $setting['font-weight'] ) {
					$params['weight'] = $setting['font-weight'];
				}

				if ( 'inherit' !== $setting['font-style'] ) {
					$params['style'] = $setting['font-style'];
				}

				if ( $setting['font-subsets'] && ! empty( $setting['font-subsets'] ) ) {
					$params['subsets'] = $setting['font-subsets'];
				}

				prisma_core()->fonts->enqueue_google_font(
					$setting['font-family'],
					$params
				);
			}

			// Finally, return the generated CSS code.
			return $css_buffer;
		}

		/**
		 * Filters the dynamic styles to include button styles and makes sure it has the highest priority.
		 *
		 * @since  1.0.0
		 * @param  string $css The dynamic CSS.
		 * @return string Filtered dynamic CSS.
		 */
		public function get_button_styles( $css ) {

			/**
			 * Primary Button.
			 */

			$primary_button_selector = '
				.pr-btn, 
				body:not(.wp-customizer) input[type=submit], 
				.site-main .woocommerce #respond input#submit, 
				.site-main .woocommerce a.button, 
				.site-main .woocommerce button.button, 
				.site-main .woocommerce input.button, 
				.woocommerce ul.products li.product .added_to_cart, 
				.woocommerce ul.products li.product .button, 
				.woocommerce div.product form.cart .button, 
				.woocommerce #review_form #respond .form-submit input, 
				#infinite-handle span';

			$primary_button_bg_color      = prisma_core_option( 'primary_button_bg_color' );
			$primary_button_border_radius = prisma_core_option( 'primary_button_border_radius' );

			if ( '' !== $primary_button_bg_color ) {
				$css .= $primary_button_selector . ' {
					background-color: ' . $primary_button_bg_color . ';
				}';
			}

			// Primary button text color, border color & border width.
			$css .= $primary_button_selector . ' {
				color: ' . prisma_core_option( 'primary_button_text_color' ) . ';
				border-color: ' . prisma_core_option( 'primary_button_border_color' ) . ';
				border-width: ' . prisma_core_option( 'primary_button_border_width' ) . 'px;
				border-top-left-radius: ' . $primary_button_border_radius['top-left'] . 'px;
				border-top-right-radius: ' . $primary_button_border_radius['top-right'] . 'px;
				border-bottom-right-radius: ' . $primary_button_border_radius['bottom-right'] . 'px;
				border-bottom-left-radius: ' . $primary_button_border_radius['bottom-left'] . 'px;
			}';

			// Primary button hover.
			$primary_button_hover_selector = '
				.pr-btn:hover, 
				.pr-btn:focus, 
				body:not(.wp-customizer) input[type=submit]:hover,
				body:not(.wp-customizer) input[type=submit]:focus, 
				.site-main .woocommerce #respond input#submit:hover,
				.site-main .woocommerce #respond input#submit:focus, 
				.site-main .woocommerce a.button:hover,
				.site-main .woocommerce a.button:focus, 
				.site-main .woocommerce button.button:hover,
				.site-main .woocommerce button.button:focus, 
				.site-main .woocommerce input.button:hover, 
				.site-main .woocommerce input.button:focus, 
				.woocommerce ul.products li.product .added_to_cart:hover,
				.woocommerce ul.products li.product .added_to_cart:focus, 
				.woocommerce ul.products li.product .button:hover,
				.woocommerce ul.products li.product .button:focus, 
				.woocommerce div.product form.cart .button:hover,
				.woocommerce div.product form.cart .button:focus, 
				.woocommerce #review_form #respond .form-submit input:hover,
				.woocommerce #review_form #respond .form-submit input:focus, 
				#infinite-handle span:hover';

			$primary_button_hover_bg_color = prisma_core_option( 'primary_button_hover_bg_color' );

			// Primary button hover bg color.
			if ( '' !== $primary_button_hover_bg_color ) {
				$css .= $primary_button_hover_selector . ' {
					background-color: ' . $primary_button_hover_bg_color . ';
				}';
			}

			// Primary button hover color & border.
			$css .= $primary_button_hover_selector . '{
				color: ' . prisma_core_option( 'primary_button_hover_text_color' ) . ';
				border-color: ' . prisma_core_option( 'primary_button_hover_border_color' ) . ';
			}';

			// Primary button typography.
			$css .= $this->get_typography_field_css( $primary_button_selector, 'primary_button_typography' );

			/**
			 * Secondary Button.
			 */

			$secondary_button_selector = '
				.btn-secondary,
				.pr-btn.btn-secondary';

			$secondary_button_bg_color      = prisma_core_option( 'secondary_button_bg_color' );
			$secondary_button_border_radius = prisma_core_option( 'secondary_button_border_radius' );

			// Secondary button text color, border color & border width.
			$css .= $secondary_button_selector . ' {
				color: ' . prisma_core_option( 'secondary_button_text_color' ) . ';
				border-color: ' . prisma_core_option( 'secondary_button_border_color' ) . ';
				border-width: ' . prisma_core_option( 'secondary_button_border_width' ) . 'px;
				background-color: ' . $secondary_button_bg_color . ';
				border-top-left-radius: ' . $secondary_button_border_radius['top-left'] . 'px;
				border-top-right-radius: ' . $secondary_button_border_radius['top-right'] . 'px;
				border-bottom-right-radius: ' . $secondary_button_border_radius['bottom-right'] . 'px;
				border-bottom-left-radius: ' . $secondary_button_border_radius['bottom-left'] . 'px;
			}';

			// Secondary button hover.
			$secondary_button_hover_selector = '
				.btn-secondary:hover, 
				.btn-secondary:focus, 
				.pr-btn.btn-secondary:hover, 
				.pr-btn.btn-secondary:focus';

			$secondary_button_hover_bg_color = prisma_core_option( 'secondary_button_hover_bg_color' );

			// Secondary button hover color & border.
			$css .= $secondary_button_hover_selector . '{
				color: ' . prisma_core_option( 'secondary_button_hover_text_color' ) . ';
				border-color: ' . prisma_core_option( 'secondary_button_hover_border_color' ) . ';
				background-color: ' . $secondary_button_hover_bg_color . ';
			}';

			// Secondary button typography.
			$css .= $this->get_typography_field_css( $secondary_button_selector, 'secondary_button_typography' );

			// Text Button.
			$css .= '
				.pr-btn.btn-text-1, .btn-text-1 {
					color: ' . prisma_core_option( 'text_button_text_color' ) . ';
				}
			';

			$css .= '
				.pr-btn.btn-text-1:hover, .pr-btn.btn-text-1:focus, .btn-text-1:hover, .btn-text-1:focus {
					color: ' . prisma_core_option( 'accent_color' ) . ';
				}
			';

			$css .= '
				.pr-btn.btn-text-1 > span::before {
					background-color: ' . prisma_core_option( 'accent_color' ) . ';
				}
			';

			if ( prisma_core_option( 'text_button_hover_text_color' ) ) {
				$css .= '
					.pr-btn.btn-text-1:hover, .pr-btn.btn-text-1:focus, .btn-text-1:hover, .btn-text-1:focus {
						color: ' . prisma_core_option( 'text_button_hover_text_color' ) . ';
					}

					.pr-btn.btn-text-1 > span::before {
						background-color: ' . prisma_core_option( 'text_button_hover_text_color' ) . ';
					}
				';
			}

			// Secondary button typography.
			$css .= $this->get_typography_field_css( '.pr-btn.btn-text-1, .btn-text-1', 'text_button_typography' );

			// Return the filtered CSS.
			return $css;
		}

		/**
		 * Generate dynamic Block Editor styles.
		 *
		 * @since  1.0.9
		 * @return string
		 */
		public function get_block_editor_css() {

			// Current post.
			$post_id   = get_the_ID();
			$post_type = get_post_type( $post_id );

			// Layout.
			$site_layout          = prisma_core_get_site_layout( $post_id );
			$sidebar_position     = prisma_core_get_sidebar_position( $post_id );
			$container_width      = prisma_core_option( 'container_width' );
			$single_content_width = prisma_core_option( 'single_content_width' );

			$container_width = $container_width - 100;

			if ( prisma_core_is_sidebar_displayed( $post_id ) ) {

				$sidebar_width   = prisma_core_option( 'sidebar_width' );
				$container_width = $container_width * ( 100 - intval( $sidebar_width ) ) / 100;
				$container_width = $container_width - 50;

				if ( 'boxed-separated' === $site_layout ) {
					if ( 3 === intval( prisma_core_option( 'sidebar_style' ) ) ) {
						$container_width += 15;
					}
				}
			}

			if ( 'boxed-separated' === $site_layout ) {
				$container_width += 16;
			}

			if ( 'boxed' === $site_layout ) {
				$container_width = $container_width + 200;
			}

			$background_color = get_background_color();
			$accent_color     = prisma_core_option( 'accent_color' );
			$content_color    = prisma_core_option( 'boxed_content_background_color' );
			$text_color       = prisma_core_option( 'content_text_color' );
			$link_hover_color = prisma_core_option( 'content_link_hover_color' );
			$headings_color   = prisma_core_option( 'headings_color' );
			$font_smoothing   = prisma_core_option( 'font_smoothing' );

			$css = '';

			// Base HTML font size.
			$css .= $this->get_range_field_css( 'html', 'font-size', 'html_base_font_size', true, 'px' );

			// Accent color.
			$css .= '
				.editor-styles-wrapper .block-editor-rich-text__editable mark,
				.editor-styles-wrapper .block-editor-rich-text__editable span.highlight,
				.editor-styles-wrapper .block-editor-rich-text__editable code,
				.editor-styles-wrapper .block-editor-rich-text__editable kbd,
				.editor-styles-wrapper .block-editor-rich-text__editable var,
				.editor-styles-wrapper .block-editor-rich-text__editable samp,
				.editor-styles-wrapper .block-editor-rich-text__editable tt {
					background-color: ' . prisma_core_hex2rgba( $accent_color, .09 ) . ';
				}

				.editor-styles-wrapper .wp-block code.block,
				.editor-styles-wrapper .block code {
					background-color: ' . prisma_core_hex2rgba( $accent_color, .075 ) . ';
				}

				.editor-styles-wrapper .wp-block .block-editor-rich-text__editable a,
				.editor-styles-wrapper .block-editor-rich-text__editable code,
				.editor-styles-wrapper .block-editor-rich-text__editable kbd,
				.editor-styles-wrapper .block-editor-rich-text__editable var,
				.editor-styles-wrapper .block-editor-rich-text__editable samp,
				.editor-styles-wrapper .block-editor-rich-text__editable tt {
					color: ' . $accent_color . ';
				}

				#editor .editor-styles-wrapper ::-moz-selection { background-color: ' . $accent_color . '; color: #FFF; }
				#editor .editor-styles-wrapper ::selection { background-color: ' . $accent_color . '; color: #FFF; }

				
				.editor-styles-wrapper blockquote,
				.editor-styles-wrapper .wp-block-quote {
					border-color: ' . $accent_color . ';
				}
			';

			// Container width.
			if ( 'fw-stretched' === $site_layout ) {
				$css .= '
					.editor-styles-wrapper .wp-block {
						max-width: none;
					}
				';
			} elseif ( 'boxed-separated' === $site_layout || 'boxed' === $site_layout ) {

				$css .= '
					.editor-styles-wrapper {
						max-width: ' . $container_width . 'px;
						margin: 0 auto;
					}

					.editor-styles-wrapper .wp-block {
						max-width: none;
					}
				';

				if ( 'boxed' === $site_layout ) {
					$css .= '
						.editor-styles-wrapper {
							-webkit-box-shadow: 0 0 30px rgba(50, 52, 54, 0.06);
							box-shadow: 0 0 30px rgba(50, 52, 54, 0.06);
							padding-left: 42px;
							padding-right: 42px;
						}
					';
				} else {
					$css .= '
						.editor-styles-wrapper {
							border-radius: 3px;
							border: 1px solid rgba(0, 0, 0, 0.085);
						}
					';
				}
			} else {
				$css .= '
					.editor-styles-wrapper .wp-block {
						max-width: ' . $container_width . 'px;
					}
				';
			}

			if ( 'post' === $post_type && 'narrow' === $single_content_width ) {

				$narrow_container_width = intval( prisma_core_option( 'single_narrow_container_width' ) );

				$css .= '
					.editor-styles-wrapper .wp-block {
						max-width: ' . $narrow_container_width . 'px;
					}
				';
			}

			// Background color.
			if ( 'boxed-separated' === $site_layout || 'boxed' === $site_layout ) {
				$css .= '
					:root .edit-post-layout .interface-interface-skeleton__content {
						background-color: #' . trim( $background_color, '#' ) . ';
					}

					:root .editor-styles-wrapper {
						background-color: ' . $content_color . ';
					}
				';
			} else {
				$css .= '
					:root .editor-styles-wrapper {
						background-color: #' . trim( $background_color, '#' ) . ';
					}
				';
			}

			// Body.
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper, .editor-styles-wrapper .wp-block, .block-editor-default-block-appender textarea.block-editor-default-block-appender__content', 'body_font' );
			$css .= '
				:root .editor-styles-wrapper {
					color: ' . $text_color . ';
				}
			';

			// If single post, use single post font size settings.
			if ( 'post' === $post_type ) {
				$css .= $this->get_range_field_css( ':root .editor-styles-wrapper .wp-block', 'font-size', 'single_content_font_size', true );
			}

			// Headings typography.
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h1.wp-block, :root .editor-styles-wrapper h2.wp-block, :root .editor-styles-wrapper h3.wp-block, :root .editor-styles-wrapper h4.wp-block, :root .editor-styles-wrapper h5.wp-block, :root .editor-styles-wrapper h6.wp-block, :root .editor-styles-wrapper .editor-post-title__block .editor-post-title__input', 'headings_font' );

			// Heading em.
			$css .= $this->get_typography_field_css( '.editor-styles-wrapper h1.wp-block em, .editor-styles-wrapper h2.wp-block em, .editor-styles-wrapper h3.wp-block em, .editor-styles-wrapper h4.wp-block em, .editor-styles-wrapper h5.wp-block em, .editor-styles-wrapper h6.wp-block em', 'heading_em_font' );

			// Headings (H1-H6).
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h1.wp-block, :root .editor-styles-wrapper .h1, :root .editor-styles-wrapper .editor-post-title__block .editor-post-title__input', 'h1_font' );
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h2.wp-block, :root .editor-styles-wrapper .h2', 'h2_font' );
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h3.wp-block, :root .editor-styles-wrapper .h3', 'h3_font' );
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h4.wp-block', 'h4_font' );
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h5.wp-block', 'h5_font' );
			$css .= $this->get_typography_field_css( ':root .editor-styles-wrapper h6.wp-block', 'h6_font' );

			$css .= '
				:root .editor-styles-wrapper h1,
				:root .editor-styles-wrapper h2,
				:root .editor-styles-wrapper h3,
				:root .editor-styles-wrapper h4,
				:root .editor-styles-wrapper .h4,
				:root .editor-styles-wrapper h5,
				:root .editor-styles-wrapper h6,
				:root .editor-post-title__block .editor-post-title__input {
					color: ' . $headings_color . ';
				}
			';

			// Page header font size.
			$css .= $this->get_range_field_css( ':root .editor-styles-wrapper .editor-post-title__block .editor-post-title__input', 'font-size', 'page_header_font_size', true );

			// Link hover color.
			$css .= '
				.editor-styles-wrapper .wp-block .block-editor-rich-text__editable a:hover { 
					color: ' . $link_hover_color . '; 
				}
			';

			// Font smoothing.
			if ( $font_smoothing ) {
				$css .= '
					.editor-styles-wrapper {
						-moz-osx-font-smoothing: grayscale;
						-webkit-font-smoothing: antialiased;
					}
				';
			}

			return $css;
		}
	}
endif;

/**
 * The function which returns the one Prisma_Core_Dynamic_Styles instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $dynamic_styles = prisma_core_dynamic_styles(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function prisma_core_dynamic_styles() {
	return Prisma_Core_Dynamic_Styles::instance();
}

prisma_core_dynamic_styles();

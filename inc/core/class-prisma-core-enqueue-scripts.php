<?php
/**
 * Enqueue scripts & styles.
 *
 * @package     Prisma Core
 * @author      Prisma Core Team
 * @since       1.0.0
 */

/**
 * Enqueue and register scripts and styles.
 *
 * @since 1.0.0
 */
function prisma_core_enqueues() {

	// Script debug.
	$prisma_core_dir    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'dev/' : '';
	$prisma_core_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Enqueue theme stylesheet.
	wp_enqueue_style(
		'prisma-core-styles',
		PRISMA_CORE_THEME_URI . '/assets/css/style' . $prisma_core_suffix . '.css',
		false,
		PRISMA_CORE_THEME_VERSION,
		'all'
	);


	// Register ImagesLoaded library.
	wp_register_script(
		'imagesloaded',
		PRISMA_CORE_THEME_URI . '/assets/js/' . $prisma_core_dir . 'vendors/imagesloaded' . $prisma_core_suffix . '.js',
		array(),
		'4.1.4',
		true
	);

	// Register Prisma Core slider.
	wp_register_script(
		'prisma-core-slider',
		PRISMA_CORE_THEME_URI . '/assets/js/prisma-core-slider' . $prisma_core_suffix . '.js',
		array( 'imagesloaded' ),
		PRISMA_CORE_THEME_VERSION,
		true
	);

	// Load comment reply script if comments are open.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Enqueue main theme script.
	wp_enqueue_script(
		'prisma-core-js',
		PRISMA_CORE_THEME_URI . '/assets/js/prisma-core' . $prisma_core_suffix . '.js',
		array(),
		PRISMA_CORE_THEME_VERSION,
		true
	);

	// Comment count used in localized strings.
	$comment_count = get_comments_number();

	// Localized variables so they can be used for translatable strings.
	$localized = array(
		'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php' ) ),
		'nonce'                 => wp_create_nonce( 'prisma-core-nonce' ),
		'responsive-breakpoint' => intval( prisma_core_option( 'main_nav_mobile_breakpoint' ) ),
		'sticky-header'         => array(
			'enabled' => prisma_core_option( 'sticky_header' ),
			'hide_on' => prisma_core_option( 'sticky_header_hide_on' ),
		),
		'strings'               => array(
			/* translators: %s Comment count */
			'comments_toggle_show' => $comment_count > 0 ? esc_html( sprintf( _n( 'Show %s Comment', 'Show %s Comments', $comment_count, 'prisma-core' ), $comment_count ) ) : esc_html__( 'Leave a Comment', 'prisma-core' ),
			'comments_toggle_hide' => esc_html__( 'Hide Comments', 'prisma-core' ),
		),
	);

	wp_localize_script(
		'prisma-core-js',
		'prisma_core_vars',
		apply_filters( 'prisma_core_localized', $localized )
	);

	// Enqueue google fonts.
	prisma_core()->fonts->enqueue_google_fonts();

	// Add additional theme styles.
	do_action( 'prisma_core_enqueue_scripts' );
}
add_action( 'wp_enqueue_scripts', 'prisma_core_enqueues' );

/**
 * Skip link focus fix for IE11.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prisma_core_skip_link_focus_fix() {
	?>
	<script>
	!function(){var e=-1<navigator.userAgent.toLowerCase().indexOf("webkit"),t=-1<navigator.userAgent.toLowerCase().indexOf("opera"),n=-1<navigator.userAgent.toLowerCase().indexOf("msie");(e||t||n)&&document.getElementById&&window.addEventListener&&window.addEventListener("hashchange",function(){var e,t=location.hash.substring(1);/^[A-z0-9_-]+$/.test(t)&&(e=document.getElementById(t))&&(/^(?:a|select|input|button|textarea)$/i.test(e.tagName)||(e.tabIndex=-1),e.focus())},!1)}();
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'prisma_core_skip_link_focus_fix' );

/**
 * Enqueue assets for the Block Editor.
 *
 * @since 1.0.0
 *
 * @return void
 */
function prisma_core_block_editor_assets() {

	// RTL version.
	$rtl = is_rtl() ? '-rtl' : '';

	// Minified version.
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Enqueue block editor styles.
	wp_enqueue_style(
		'prisma-core-block-editor-styles',
		PRISMA_CORE_THEME_URI . '/inc/admin/assets/css/prisma-core-block-editor-styles' . $rtl . $min . '.css',
		false,
		PRISMA_CORE_THEME_VERSION,
		'all'
	);

	// Enqueue google fonts.
	prisma_core()->fonts->enqueue_google_fonts();

	// Add dynamic CSS as inline style.
	wp_add_inline_style(
		'prisma-core-block-editor-styles',
		apply_filters( 'prisma_core_block_editor_dynamic_css', prisma_core_dynamic_styles()->get_block_editor_css() )
	);


	// Script debug.
	$dev    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'dev/' : '';
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Enqueue block editor sidebar panel script.
	wp_enqueue_script(
		'prisma-core-page-options',
		PRISMA_CORE_THEME_URI . '/inc/admin/assets/js/' . $dev . 'prisma-core-page-options' . $suffix . '.js',
		array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ),
		PRISMA_CORE_THEME_VERSION,
		true
	);

	// Pass customizer option visibility flags and select choices to JS.
	wp_localize_script(
		'prisma-core-page-options',
		'prismaPageOptions',
		array(
			'visibility' => array(
				'topbar'       => (bool) prisma_core_option( 'top_bar_enable' ),
				'pageTitle'    => (bool) prisma_core_option( 'page_header_enable' ),
				'breadcrumbs'  => (bool) prisma_core_option( 'page_header_enable' )
				                  && (bool) prisma_core_option( 'breadcrumbs_enable' ),
				'prefooterCta' => (bool) prisma_core_option( 'enable_pre_footer_cta' ),
				'footer'       => (bool) prisma_core_option( 'enable_footer' ),
				'copyright'    => (bool) prisma_core_option( 'enable_copyright' ),
			),
			'choices'    => array(
				'sidebarPosition'   => array(
					array( 'value' => '', 'label' => __( 'Default (from Customizer)', 'prisma-core' ) ),
					array( 'value' => 'no-sidebar', 'label' => __( 'No Sidebar', 'prisma-core' ) ),
					array( 'value' => 'left-sidebar', 'label' => __( 'Left Sidebar', 'prisma-core' ) ),
					array( 'value' => 'right-sidebar', 'label' => __( 'Right Sidebar', 'prisma-core' ) ),
				),
				'contentLayout'     => array(
					array( 'value' => '', 'label' => __( 'Default (from Customizer)', 'prisma-core' ) ),
					array( 'value' => 'fw-contained', 'label' => __( 'Full Width: Contained', 'prisma-core' ) ),
					array( 'value' => 'fw-stretched', 'label' => __( 'Full Width: Stretched', 'prisma-core' ) ),
					array( 'value' => 'boxed-separated', 'label' => __( 'Boxed Content', 'prisma-core' ) ),
					array( 'value' => 'boxed', 'label' => __( 'Boxed', 'prisma-core' ) ),
				),
				'transparentHeader' => array(
					array( 'value' => '', 'label' => __( 'Default (from Customizer)', 'prisma-core' ) ),
					array( 'value' => 'enable', 'label' => __( 'Enable', 'prisma-core' ) ),
					array( 'value' => 'disable', 'label' => __( 'Disable', 'prisma-core' ) ),
				),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'prisma_core_block_editor_assets' );

/**
 * Register post meta for REST API / Block Editor support.
 *
 * @since 1.3.1
 *
 * @return void
 */
function prisma_core_register_post_meta() {

	// Select fields — stored as strings.
	$select_keys = array(
		'prisma_core_sidebar_position',
		'prisma_core_content_layout',
		'prisma_core_transparent_header',
	);

	foreach ( $select_keys as $key ) {
		register_post_meta(
			'',
			$key,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_key',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	// Checkbox fields — stored as booleans.
	$checkbox_keys = array(
		'prisma_core_disable_topbar',
		'prisma_core_disable_header',
		'prisma_core_disable_page_title',
		'prisma_core_disable_breadcrumbs',
		'prisma_core_disable_thumbnail',
		'prisma_core_disable_prefooter_cta',
		'prisma_core_disable_footer',
		'prisma_core_disable_copyright',
	);

	foreach ( $checkbox_keys as $key ) {
		register_post_meta(
			'',
			$key,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'prisma_core_register_post_meta' );

/**
 * Prisma Core — Block Editor Page Options sidebar panel.
 *
 * Registers a PluginDocumentSettingPanel that replaces the PHP
 * "Theme Page Options" metabox when the block editor is active.
 *
 * Uses wp.element.createElement (no JSX) since the theme has no
 * Babel/webpack transpiler.
 *
 * @package Prisma Core
 * @since   1.3.1
 */
( function () {
	'use strict';

	// Bail if PluginDocumentSettingPanel is unavailable (WP < 5.3).
	if ( ! wp.editPost || ! wp.editPost.PluginDocumentSettingPanel ) {
		return;
	}

	var el              = wp.element.createElement;
	var registerPlugin  = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var SelectControl   = wp.components.SelectControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var useSelect       = wp.data.useSelect;
	var useDispatch     = wp.data.useDispatch;
	var __              = wp.i18n.__;

	// Localized config from PHP (see prisma_core_block_editor_assets).
	var config     = window.prismaPageOptions || {};
	var visibility = config.visibility || {};
	var choices    = config.choices || {};

	/**
	 * Main panel component.
	 */
	function PrismaCorePageOptions() {

		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		function setMeta( key, value ) {
			var update = {};
			update[ key ] = value;
			editPost( { meta: update } );
		}

		// ── Select controls (always visible) ─────────────────────

		var selects = [
			el( SelectControl, {
				key:      'prisma_core_sidebar_position',
				label:    __( 'Sidebar', 'prisma-core' ),
				value:    meta.prisma_core_sidebar_position || '',
				options:  choices.sidebarPosition || [],
				onChange: function ( v ) { setMeta( 'prisma_core_sidebar_position', v ); },
			} ),
			el( SelectControl, {
				key:      'prisma_core_content_layout',
				label:    __( 'Content Layout', 'prisma-core' ),
				value:    meta.prisma_core_content_layout || '',
				options:  choices.contentLayout || [],
				onChange: function ( v ) { setMeta( 'prisma_core_content_layout', v ); },
			} ),
			el( SelectControl, {
				key:      'prisma_core_transparent_header',
				label:    __( 'Transparent Header', 'prisma-core' ),
				value:    meta.prisma_core_transparent_header || '',
				options:  choices.transparentHeader || [],
				onChange: function ( v ) { setMeta( 'prisma_core_transparent_header', v ); },
			} ),
		];

		// ── Checkbox controls (conditionally visible) ────────────

		var checkboxDefs = [
			[ 'prisma_core_disable_topbar',        __( 'Disable Top Bar', 'prisma-core' ),        'topbar' ],
			[ 'prisma_core_disable_header',        __( 'Disable Main Header', 'prisma-core' ),    null ],
			[ 'prisma_core_disable_page_title',    __( 'Disable Page Title', 'prisma-core' ),     'pageTitle' ],
			[ 'prisma_core_disable_breadcrumbs',   __( 'Disable Breadcrumbs', 'prisma-core' ),    'breadcrumbs' ],
			[ 'prisma_core_disable_thumbnail',     __( 'Disable Featured Image', 'prisma-core' ), null ],
			[ 'prisma_core_disable_prefooter_cta', __( 'Disable Pre Footer CTA', 'prisma-core' ), 'prefooterCta' ],
			[ 'prisma_core_disable_footer',        __( 'Disable Main Footer', 'prisma-core' ),    'footer' ],
			[ 'prisma_core_disable_copyright',     __( 'Disable Copyright Bar', 'prisma-core' ),  'copyright' ],
		];

		var checkboxes = [];

		checkboxDefs.forEach( function ( def ) {
			var metaKey = def[ 0 ];
			var label   = def[ 1 ];
			var visKey  = def[ 2 ];

			// Skip if the related customizer section is disabled.
			if ( visKey !== null && ! visibility[ visKey ] ) {
				return;
			}

			checkboxes.push(
				el( CheckboxControl, {
					key:      metaKey,
					label:    label,
					checked:  !! meta[ metaKey ],
					onChange: function ( v ) { setMeta( metaKey, v ); },
				} )
			);
		} );

		// ── Render ───────────────────────────────────────────────

		var children = selects.slice();

		if ( checkboxes.length ) {
			children.push(
				el( 'div', { key: 'checkboxes', style: { marginTop: '16px' } }, checkboxes )
			);
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name:  'prisma-core-page-options',
				title: __( 'Theme Page Options', 'prisma-core' ),
			},
			children
		);
	}

	// ── Register plugin ──────────────────────────────────────────

	registerPlugin( 'prisma-core-page-options', {
		icon:   null,
		render: PrismaCorePageOptions,
	} );
} )();

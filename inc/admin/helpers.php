<?php
/**
 * Contains various functions that may be potentially used throughout
 * the theme.
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

/**
 * Check if we're on a Prisma Core admin page.
 *
 * @since 1.0.0
 * @param boolean|string $base current screen base.
 * @param string         $slug page slug.
 * @return boolean
 */
function prisma_core_is_admin_page( $base = false, $slug = 'prisma-core' ) {

	if ( false === $base ) {
		$base = get_current_screen()->base;
	}

	return false !== strpos( $base, $slug );
}

/**
 * Print admin notice.
 *
 * @since 1.0.0
 * @param array $args array of options.
 * @return boolean|void
 */
function prisma_core_print_notice( $args ) {

	$defaults = array(
		'type'           => 'success',
		'message'        => '',
		'is_dismissible' => true,
		'message_id'     => '',
		'expires'        => 0,
		'display_on'     => array(),
		'action_link'    => '',
		'action_text'    => '',
		'dismiss_text'   => esc_html__( 'Dismiss', 'prisma-core' ),
	);

	$args = wp_parse_args( $args, $defaults );

	if ( prisma_core_is_notice_dismissed( $args['message_id'] ) ) {
		return false;
	}

	if ( ! empty( $args['display_on'] ) ) {

		$base    = get_current_screen()->base;
		$display = false;

		foreach ( $args['display_on'] as $page ) {
			if ( false !== strpos( $base, $page ) ) {
				$display = true;
			}
		}

		if ( ! $display ) {
			return false;
		}
	}

	$prisma_core_is_dismissible = $args['is_dismissible'] ? ' is-dismissible' : ''; ?>

	<div id="<?php echo esc_attr( $args['message_id'] ); ?>" class="notice prisma-core-notice notice-<?php echo esc_attr( $args['type'] ); ?><?php echo esc_attr( $prisma_core_is_dismissible ); ?>">
		<p><?php echo ( wp_kses( $args['message'], prisma_core_get_allowed_html_tags() ) ); ?></p>

		<?php
		if ( $args['action_link'] && $args['action_text'] ) {
			?>
			<p class="pr-notice-action">
				<a href="<?php echo esc_url( $args['action_link'] ); ?>" class="pr-btn primary button button-primary" role="button"><?php echo esc_html( $args['action_text'] ); ?></a>

				<?php
				if ( $args['dismiss_text'] ) {
					?>
					<a href="#" class="pr-btn secondary button button-secondary prisma-core-notice-dismiss" role="button"><?php echo esc_html( $args['dismiss_text'] ); ?></a>
					<?php
				}
				?>
			</p><!-- END .pr-notice-action -->
			<?php
		}
		?>
	</div>

	<script type="text/javascript">
		jQuery( document ).ready( function ( $ ) {

			var msgid = "<?php echo esc_attr( $args['message_id'] ); ?>";
			var $el   = $( '#' + msgid );

			$el.on( 'click', '.notice-dismiss, .prisma-core-notice-dismiss', function ( event ) {

				var expires = "<?php echo esc_attr( $args['expires'] ); ?>";
				var nonce = "<?php echo esc_attr( wp_create_nonce( 'prisma_core_dismiss_notice' ) ); ?>";

				$.post( ajaxurl, {
					action: 		'prisma_core_dismiss_notice',
					msgid: 			msgid,
					expires: 		expires,
					_ajax_nonce: 	nonce,
				} );

				$el.fadeTo( 100, 0, function() {
					$el.slideUp( 100, function() {
						$el.remove();
					});
				});
			} );
		} );
	</script>
	<?php
}

/**
 * Check if admin notice is dismissed.
 *
 * @since 1.0.0
 * @param array $id Notice ID.
 * @return boolean
 */
function prisma_core_is_notice_dismissed( $id ) {

	if ( false !== get_transient( 'prisma_core_notice_' . $id ) ) {
		return true;
	}

	return false;
}

/**
 * Ajax handler to dismiss admin notice.
 *
 * @since 1.0.0
 * @return void
 */
function prisma_core_dismiss_notice() {

	check_ajax_referer( 'prisma_core_dismiss_notice' );

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( -1, 403 );
	}

	if ( ! isset( $_POST['msgid'] ) ) {
		die;
	}

	$message_id = sanitize_text_field( wp_unslash( $_POST['msgid'] ) );
	$expires    = isset( $_POST['expires'] ) ? intval( $_POST['expires'] ) : 0;

	$message              = (array) get_transient( 'prisma_core_notice_' . $message_id );
	$message['time']      = time();
	$message['dismissed'] = true;

	set_transient( 'prisma_core_notice_' . $message_id, $message, $expires );
	die;
}
add_action( 'wp_ajax_prisma-core_dismiss_notice', 'prisma_core_dismiss_notice' );

/**
 * Print admin icon.
 *
 * @since 1.0.0
 * @param string $icon             Icon name.
 * @param string $tooltip          Tooltip text.
 * @param string $tooltip_position Position of the tooltip.
 * @return void
 */
function prisma_core_print_admin_icon( $icon = 'info', $tooltip = '', $tooltip_position = 'right-tooltip' ) {

	$svg_icon = '<svg height="18" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M13 9h-2V7h2v2zm0 2h-2v6h2v-6zm-1-7c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8m0-2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"></path></g></svg>';

	if ( '' !== $tooltip ) {
		$tooltip = '<span class="prisma-core-tooltip ' . esc_attr( $tooltip_position ) . '">' . esc_html( $tooltip ) . '</span>';
	}

	if ( 'warning' === $icon ) {
		echo '<i class="prisma-core-warning-icon">' . $svg_icon . $tooltip . '</i>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} elseif ( 'info' === $icon ) {
		echo '<i class="prisma-core-info-icon">' . $svg_icon . $tooltip . '</i>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Check if currently using block editor page.
 *
 * @since 1.0.0
 * @return boolean
 */
function prisma_core_is_block_editor() {

	if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
		// The Gutenberg plugin is on.
		return true;
	}

	$current_screen = get_current_screen();
	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		// Gutenberg page on 5+.
		return true;
	}

	return false;
}

/**
 * Print help icon with a link to documentation.
 *
 * @param  array $args Optional parameters.
 * @param  bool  $echo Return or print the link.
 * @since  1.0.0
 * @return void|string
 */
function prisma_core_help_link( $args = array(), $echo = true ) {

	if ( ! apply_filters( 'prisma_core_display_help_links', true ) ) {
		return;
	}

	$defaults = array(
		'link'  => '',
		'class' => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	$args['class']   = (array) $args['class'];
	$args['class'][] = 'pr-help-link';

	$class = trim( implode( ' ', $args['class'] ) );

	$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';

	$output = sprintf(
		'<a href="%1$s" rel="nofollow" target="_blank" class="%2$s"><span class="pr-help-icon">%4$s</span>%3$s</a>',
		esc_url( $args['link'] ),
		esc_attr( $class ),
		esc_html__( 'How to use', 'prisma-core' ),
		$icon
	);

	if ( $echo ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $output;
	}
}

<?php
/**
 * The administration dashboard menus, screens, etc.
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Things related to the WordPress Administrator Dashboard
 */
class WriteShare_Dashboard {

	/**
	 * @var WriteShare The bound WriteShare instance.
	 */
	public $writeshare;

	/**
	 * Early initialization.
	 * 
	 * @param WriteShare $writeshare The bound WriteShare instance.
	 *
	 * @return void
	 */
	public function __construct( $writeshare ) {
		$this->writeshare = $writeshare;
	}
	
	/**
	 * Main initialization.
	 *
	 * - Adds admin menu and submenu pages.
	 * - Adds user edit fields.
	 */
	public function init() {
		$_this = &$this;

		add_action( 'admin_menu', function() use ( $_this ) {
			add_menu_page(
				__( 'WriteShare', WriteShare::$TEXTDOMAIN ),
				__( 'WriteShare', WriteShare::$TEXTDOMAIN ),
				'manage_options',
				'writeshare',
				array( $_this, 'main_menu' ),
				'dashicons-book', '3.33114912'
			);

			add_submenu_page(
				'writeshare',
				__( 'WriteShare Settings', WriteShare::$TEXTDOMAIN ),
				__( 'Settings', WriteShare::$TEXTDOMAIN ),
				'manage_options',
				'writeshare-settings',
				array( $_this->writeshare->settings, 'settings_page' )
			);
		} );

		add_action( 'edit_user_profile', function( $user ) use ( $_this ) {
			?>
				<h3><?php _e( 'WriteShare', WriteShare::$TEXTDOMAIN ); ?></h3>

				<table class="form-table">
					<tbody>
						<tr>
							<th><label><?php _e( 'Writer Privileges' ); ?></label></th>
							<td>
								<input name="wpws_has_authorship" type="checkbox" <?php checked( $_this->writeshare->settings->get( 'authorship' ) || $user->wpws_has_authorship ); ?> <?php disabled( (bool)$_this->writeshare->settings->get( 'authorship' ) ); ?>/> <?php echo _e( 'User has writer privileges', WriteShare::$TEXTDOMAIN ); ?></label>
							</td>
						</tr>
					</tbody>
				</table>
			<?php
		} );

		add_action( 'profile_update', function( $user_id ) use ( $_this ) {
			if ( !$user_id || !current_user_can( 'edit_users' ) )
				return;

			if ( !$_this->writeshare->settings->get( 'authorship' ) ) {
				$grant_authorship = !empty( $_POST['wpws_has_authorship'] );

				if ( $grant_authorship && !get_user_meta( $user_id, 'wpws_has_authorship', true ) ) {
					$_this->writeshare->notifications->send( 'authorship_granted', get_userdata( $user_id )->user_email );
					update_user_meta( $user_id, 'wpws_authorship_requested', false ); 
				}

				update_user_meta( $user_id, 'wpws_has_authorship', $grant_authorship );
			}
		} );
	}

	/**
	 * The main WriteShare menu page
	 *
	 * Can contain quick links, stats, live status updates, etc.
	 *
	 * @return void
	 */
	public static function main_menu() {
		?>
			<div class="wrap">
				<h1><?php _e( 'WriteShare Administrator Dashboard', WriteShare::$TEXTDOMAIN ); ?></h1>

				<p>There's nothing to see here yet, but this section will contain quick links, statistics, etc.</p>
			</div>
		<?php
	}
}

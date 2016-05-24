<?php
/**
 * Housing for all the notifications logic and abstractions.
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Manages all the settings related to this plugin
 */
class WriteShare_Notifications {
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
	 * Send notifications out.
	 */
	public function send( $template, $to, $context=null ) {
		switch ( $template ):
			case 'authorship_requested':
				$subject = sprintf( '[%s] Writer Privileges Request', get_option( 'blogname' ) );
				$message = sprintf( 'A user has requested writer privileges on %s. To approve visit: %s', get_option( 'blogname' ), add_query_arg( 'user_id', $context['user']->ID, admin_url( 'user-edit.php' ) ) );
				break;
			case 'authorship_granted':
				$subject = sprintf( '[%s] Writer Privileges Granted', get_option( 'blogname' ) );
				$message = sprintf( 'Writer privileges  on %s has been granted. Please visit %s to start writing.', get_option( 'blogname' ), site_url() );
				break;
			default:
				return false;
		endswitch;
		
		foreach ( (array)$to as $to_email ) {
			wp_mail( $to_email, $subject, $message );
		}

		return true;
	}
}

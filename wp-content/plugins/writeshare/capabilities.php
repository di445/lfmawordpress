<?php
/**
 * WriteShare Capabilities
 *
 * Here we determine which capabilities to tack onto users
 * dynamically.
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Houses everything capabilities-related
 *
 * Here's a list of internal capabilities that we introduce:
 *
 * - wpws_see_admin_bar
 * - wpws_access_dashboard
 *
 */
class WriteShare_Capabilities {

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
	 * Tacks on special capabilites or removes them.
	 *
	 * This function must be called at 'init'
	 *
	 * @return array $caps The array of WordPress capabilities
	 */
	public function init() {
		$_this = $this;
		add_filter( 'map_meta_cap', function( $caps, $cap, $user_id, $args ) use ( $_this ) {
			switch ( $cap ):

				/** Display the Admin bar */
				/** ...and enter the Admin Dashboard */
				case 'wpws_access_dashboard':
					if ( !is_user_logged_in() )
						return $_this->disallow();
				case 'wpws_see_admin_bar':
					$user = get_user_by( 'id', $user_id );
					if ( array_intersect( apply_filters( 'wpws_can_access_dashboard', array( 'administrator', 'editor' ) ), $user ? $user->roles : array() ) )
						return $_this->allow();
					return $_this->disallow();
				/** Publish new content */
				case 'wpws_publish_content':
					$user = get_user_by( 'id', $user_id );
					if ( $user && in_array( 'administrator', $user->roles ) )
						return $_this->allow();
					return ( $user && array_intersect( array( 'subscriber', 'author' ), $user->roles )
						&& ( $_this->writeshare->settings->get( 'authorship' ) || $user->wpws_has_authorship ) ) ?
						$_this->allow() : $_this->disallow();
				/** Edit existing content */
				case 'wpws_edit_content':
					$user = get_user_by( 'id', $user_id );
					if ( $user && in_array( 'administrator', $user->roles ) )
						return $_this->allow();
					$post = array_shift( $args );
					return ( $user && $post && $post->post_author == $user->ID && $post->post_type == 'wpws_content' )
						? $_this->allow() : $_this->disallow();
			endswitch;
			return $caps;
		}, 10, 4 );
	}


	/**
	 * Returns the standard unconditional allow WordPress capability for all users
	 *
	 * @return array The standard prerequisite cap for this to pass
	 */
	public function allow() {
		return array( 'exist' );
	}


	/**
	 * Returns the standard unconditional disallow WordPress capability for all users
	 *
	 * @return array The standard prerequisite cap for this to fail
	 */
	public function disallow() {
		return array( 'do_not_allow' );
	}
}

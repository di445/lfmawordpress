<?php
/**
 * Housing for all the settings screens and functions
 */

defined( 'ABSPATH' ) || exit; /** Make sure the WordPress core is loaded */

/**
 * Manages all the settings related to this plugin
 */
class WriteShare_Settings {
	/**
	 * @var string Contains all the options
	 */
	private $options = array();

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
	 * Initializes all the necessary settings pages and their fields
	 *
	 * @return void
	 */
	public function init() {
		$this->options = get_option( 'wpws_settings' );

		$_this = &$this;

		add_action( 'admin_init', function() use ( $_this ) {
			/** General Settings */
			add_settings_section( 'wpws_settings-general', __( 'General Settings', WriteShare::$TEXTDOMAIN ), null, 'wpws_settings' );

			/**
			 * Membership.
			 */
			add_settings_field( 'wpws_membership', __( 'Membership', WriteShare::$TEXTDOMAIN ), function() {
				?>
					<input type="checkbox" name="wpws_settings[users_can_register]" <?php checked( get_option( 'users_can_register' ) ); ?>> <?php _e( 'Anyone can register', WriteShare::$TEXTDOMAIN ); ?>
				<?php

				if ( get_option( 'default_role' ) != 'subscriber' ) {
					?>
						<p><strong><?php _e( 'Warning', WriteShare::$TEXTDOMAIN ); ?></strong>: <?php _e( sprintf( 'The default role of new members is set to "%s".', esc_html( get_option( 'default_role' ) ) ) ); ?></p>
					<?php
				}

			}, 'wpws_settings', 'wpws_settings-general' );

			/**
			 * Role request.
			 */
			 add_settings_field( 'wpws_authorship', __( 'Writer Privileges', WriteShare::$TEXTDOMAIN ), function() use ( $_this ) {
				?>
					<input type="checkbox" name="wpws_settings[authorship]" <?php checked( (bool)$_this->get( 'authorship' ) ); ?>> <?php _e( 'New users are authors by default', WriteShare::$TEXTDOMAIN ); ?>
				<?php
			 }, 'wpws_settings', 'wpws_settings-general' );

			/** Content Settings */
			add_settings_section( 'wpws_settings-content', __( 'Content Settings', WriteShare::$TEXTDOMAIN ), null, 'wpws_settings' );

			/**
			 * Content Type.
			 *
			 * This is merely a set of label overrides for our wpws_content type.
			 *
			 * To add your own hook into `wpws_content_types` and tack on some labels.
			 *  See defaults.php $content_types variable for structure.
			 *
			 * The default content type on a new installation is controlled by the
			 *  `wpws_default_content_type` filter, defaulting to "content", the ID must exist
			 *  in `wpws_content_types`.
			 */
			$available_content_types = apply_filters( 'wpws_content_types', $_this->writeshare->defaults->content_types );
			$current_content_type = $_this->get( 'content_type', apply_filters( 'wpws_default_content_type', 'content' ) );
			add_settings_field( 'content_type', __( 'Writing Content Type', WriteShare::$TEXTDOMAIN ), function() use ( $available_content_types, $current_content_type ) {

				/** Flush rules when visiting this page, just like the Permalinks page */
				flush_rewrite_rules();

				?>
					<select name="wpws_settings[content_type]">
						<?php foreach ( $available_content_types as $content_type ) printf( '<option value="%s"%s>%s</option>', esc_attr( $content_type['id'] ), selected( $content_type['id'], $current_content_type ), esc_html( $content_type['label'] ) ); ?>
					</select>
					<p class="description"><?php _e( 'What your users will write on your site.', WriteShare::$TEXTDOMAIN ); ?></p>
				<?php
			}, 'wpws_settings', 'wpws_settings-content' );

			/** Taxonomy Settings */
			add_settings_section( 'wpws_settings-taxonomies', __( 'Taxonomy Settings', WriteShare::$TEXTDOMAIN ), function() {
				printf( '<p class="description">%s</p>', __( 'Taxonomies allow you to group content into various categories, genres, tags, and pretty much any sort of grouping you can imaging.', WriteShare::$TEXTDOMAIN ) );
				printf( '<p class="description">%s <a href="#" class="taxonomy-template">%s</a> :)', __( 'Not sure what taxonomies to create? Based on your selection above', WriteShare::$TEXTDOMAIN ), __( 'try these on for size', WriteShare::$TEXTDOMAIN ) );
			}, 'wpws_settings' );

			/**
			 * Taxonomies.
			 */
			add_settings_field( 'taxonomies', __( 'Taxonomies', WriteShare::$TEXTDOMAIN ), function() use ( $available_content_types, $_this ) {
				?>
					<div class="wpws-taxonomy-container template" style="display: none";>
						<table class="form-table">
							<tr>
								<th scope="row">ID</th>
								<td>
									<input type="text" name="wpws_settings[taxonomies][][id]" />
									<p class="description"><?php _e( 'An internal identifier for this taxonomy. Leave blank to auto-generate.', WriteShare::$TEXTDOMAIN ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">Singular Name</th>
								<td><input type="text" name="wpws_settings[taxonomies][][singular]" /></td>
							</tr>
							<tr>
								<th scope="row">Plural Name</th>
								<td><input type="text" name="wpws_settings[taxonomies][][plural]" /></td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Access', WriteShare::$TEXTDOMAIN ); ?></th>
								<td><input type="checkbox" name="wpws_settings[taxonomies][][access]" /> <?php _e( 'Only admins and editors can add terms to this taxonomy', WriteShare::$TEXTDOMAIN ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Exclusive', WriteShare::$TEXTDOMAIN ); ?></th>
								<td><input type="checkbox" name="wpws_settings[taxonomies][][owned]" /> <?php _e( 'Terms in this taxonomy are exclusively owned by an author', WriteShare::$TEXTDOMAIN ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Serial', WriteShare::$TEXTDOMAIN ); ?></th>
								<td><input type="checkbox" name="wpws_settings[taxonomies][][serial]" /> <?php _e( 'Content in a term of this taxonomy can be ordered manually', WriteShare::$TEXTDOMAIN ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Required', WriteShare::$TEXTDOMAIN ); ?></th>
								<td><input type="checkbox" name="wpws_settings[taxonomies][][required]" /> <?php _e( 'At least one term is required to be set by the author', WriteShare::$TEXTDOMAIN ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Limit to', WriteShare::$TEXTDOMAIN ); ?></th>
								<td><input type="number" name="wpws_settings[taxonomies][][limit]" /> <?php _e( 'term(s) per content (leave blank or 0 for unlimited)', WriteShare::$TEXTDOMAIN ); ?></td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td>
									<a href="#" style="color: #a00;" class="delete">delete</a>
								</td>
							</tr>
						</table>
						<hr />
					</div>
					<button class="add-taxonomy button button-secondary"><?php _e( 'Add Taxonomy', WriteShare::$TEXTDOMAIN ); ?></button>

					<script type="text/javascript">
						!!(function() {
							/**
							 * The list of all content types and their taxonomies.
							 *
							 * These are controlled by `wpws_content_types`.
							 */
							var available_content_types = <?php echo json_encode( $available_content_types ); ?>;

							/**
							 * Add a taxonomy fieldset.
							 */
							function add_taxonomy( taxonomy, access, limit, owned, serial, required ) {
								var template = jQuery( '.wpws-taxonomy-container.template' ).clone();
								template.toggleClass( 'template' );

								if ( taxonomy ) {
									var plural, singular;
									plural = singular = taxonomy.label;
									if ( taxonomy.labels && taxonomy.labels.singular_name )
										singular = taxonomy.labels.singular_name;

									template.find( 'input[name="wpws_settings[taxonomies][][id]"]').val( taxonomy.id );
									template.find( 'input[name="wpws_settings[taxonomies][][singular]"]').val( singular );
									template.find( 'input[name="wpws_settings[taxonomies][][plural]"]').val( plural );
								}

								template.find( 'input[name="wpws_settings[taxonomies][][access]"]').attr( 'checked', access );
								template.find( 'input[name="wpws_settings[taxonomies][][owned]"]').attr( 'checked', owned );
								template.find( 'input[name="wpws_settings[taxonomies][][serial]"]').attr( 'checked', serial );
								template.find( 'input[name="wpws_settings[taxonomies][][required]"]').attr( 'checked', required );
								template.find( 'input[name="wpws_settings[taxonomies][][limit]"]').val( limit );

								jQuery( 'button.add-taxonomy' ).before( template.show() );
							}

							/**
							 * Add the saved taxonomies from settings.
							 */
							jQuery( <?php echo json_encode( $_this->get( 'taxonomies' ) ); ?> ).map( function( i, e ) {
								add_taxonomy( { 'id': e.id, 'label': e.plural, 'labels': { 'singular_name': e.singular } }, e.access, e.limit, e.owned, e.serial, e.required );
							} );
								
							/**
							 * Hydrate taxnomies from a template.
							 */
							jQuery( 'a.taxonomy-template' ).on( 'click', function( e ) {
								e.preventDefault();

								var content_type = jQuery( 'select[name="wpws_settings[content_type]"]' ).val();

								if ( !available_content_types[content_type] || !available_content_types[content_type].taxonomies ) {
									alert( <?php echo json_encode( __( 'This content type has no taxonomy templates associated with it.', WriteShare::$TEXTDOMAIN ) ); ?> );
									return false;
								}

								jQuery( available_content_types[content_type].taxonomies ).map( function( i, e ) {
									add_taxonomy( e );
								} );

								return false;
							} );

							/**
							 * Add an empty taxonomy fieldset.
							 */
							jQuery( 'button.add-taxonomy' ).on( 'click', function( e ) {
								e.preventDefault();
								add_taxonomy();
								return false;
							} );

							/**
							 * Remove taxonomies.
							 */
							jQuery( document ).on( 'click', '.delete', function( e ) {
								e.preventDefault();
								if ( confirm( 'Are you sure you want to do this?' ) ) {
									jQuery( this ).parents( '.wpws-taxonomy-container' ).remove();
								}
								return false;
							} );

							/**
							 * Squash the taxonomies before submitting.
							 */
							jQuery( 'form' ).on( 'submit', function( e ) {
								jQuery( '.wpws-taxonomy-container' ).map( function( i, e ) {
									jQuery( e ).find( 'input' ).map( function( _, input ) {
										input = jQuery( input );
										input.attr( 'name', input.attr( 'name' ).replace( '[]', '[' + i + ']' ) );
									} );
								} );
							} );
						})();
					</script>
				<?php
			}, 'wpws_settings', 'wpws_settings-taxonomies' );

			register_setting( 'wpws_settings', 'wpws_settings', function( $options ) {
				if ( !empty ( $options['taxonomies'] ) ) {
					$taxonomies = array();
					/** Remove empty taxonomies */
					$seen = array();
					foreach ( $options['taxonomies'] as $index => $taxonomy ) {
						if ( empty( $taxonomy['singular'] ) || empty( $taxonomy['plural'] ) )
							continue;
						$taxonomy['id'] = sanitize_key( empty( $taxonomy['id'] ) ? $taxonomy['singular'] : $taxonomy['id'] );
						if ( in_array( $taxonomy['id'], $seen ) )
							continue;
						$taxonomies []= $taxonomy;
						$seen []= $taxonomy['id'];
					}
					$options['taxonomies'] = $taxonomies;
				}

				/** Update site users_can_register option */
				update_option( 'users_can_register', !empty( $options['users_can_register'] ) );
				unset( $options['users_can_register'] );

				return $options;
			} );
		} );
	}

	/**
	 * Renders the settings page
	 */
	public function settings_page() {
		
		?>
			<div class="wrap">
				<h1><?php _e( 'WriteShare Plugin Settings', WriteShare::$TEXTDOMAIN ); ?></h1>
				<form method="post" action="options.php">
					<?php
						settings_fields( 'wpws_settings' );
						do_settings_sections( 'wpws_settings' );
						submit_button();
					?>
				</form>
			</div>
		<?php
	}
	
	/**
	 * Gets a setting value by name
	 *
	 * All these settings are prefixed with wpws_ in the WordPress
	 *  options table automatically, so calling by plain name is
	 *  the way to go
	 *
	 * @param $name The setting key
	 * @param $default A default value if this is not found (default null)
	 *
	 * @return mixed The setting
	 */
	public function get( $name, $default = null) {
		return isset( $this->options[$name] ) ? $this->options[$name] : $default;
	}
}

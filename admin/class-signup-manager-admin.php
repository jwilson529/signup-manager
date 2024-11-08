<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Signup_Manager
 * @subpackage Signup_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and example hooks for how to enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Signup_Manager
 * @subpackage Signup_Manager/admin
 * @author     James Wilson <james@oneclickcontent.com>
 */
class Signup_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $signup_manager The ID of this plugin.
	 */
	private $signup_manager;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The name of the signups table.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $table_name The name of the database table for signups.
	 */
	private $table_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $signup_manager The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $signup_manager, $version ) {
		global $wpdb;
		$this->signup_manager = $signup_manager;
		$this->version        = $version;
		$this->table_name     = $wpdb->prefix . 'signups';
	}

	/**
	 * Register the admin page for managing pending signups.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_page() {
		add_menu_page(
			__( 'Pending Signups', 'signup-manager' ),
			__( 'Pending Signups', 'signup-manager' ),
			'manage_options',
			'pending-signups',
			array( $this, 'render_admin_page' ),
			'dashicons-groups',
			20
		);
	}

	/**
	 * Render the admin page for viewing and managing pending signups.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		global $wpdb;
		$cache_key = 'pending_signups';
		$signups   = wp_cache_get( $cache_key );

		if ( false === $signups ) {
			$table_name = esc_sql( $this->table_name ); // Sanitize the table name.
			$signups    = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$table_name} WHERE active = %d", 0 ),
				ARRAY_A
			);
			wp_cache_set( $cache_key, $signups );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Pending User Signups', 'signup-manager' ); ?></h1>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email', 'signup-manager' ); ?></th>
						<th><?php esc_html_e( 'Registered', 'signup-manager' ); ?></th>
						<th><?php esc_html_e( 'Last Email Sent', 'signup-manager' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'signup-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $signups ) : ?>
						<?php foreach ( $signups as $signup ) : ?>
							<?php
							$delete_url      = wp_nonce_url( admin_url( 'admin-post.php?action=delete_signup&signup_id=' . $signup['signup_id'] ), 'delete_signup_' . $signup['signup_id'] );
							$resend_url      = wp_nonce_url( admin_url( 'admin-post.php?action=resend_activation&signup_id=' . $signup['signup_id'] ), 'resend_activation_' . $signup['signup_id'] );
							$last_email_sent = ! empty( $signup['last_email_sent'] ) ? esc_html( gmdate( 'Y-m-d H:i:s', strtotime( $signup['last_email_sent'] ) ) ) : esc_html__( 'Never', 'signup-manager' );
							?>
							<tr>
								<td><strong><?php echo esc_html( $signup['user_email'] ); ?></strong></td>
								<td><?php echo esc_html( gmdate( 'Y-m-d H:i:s', strtotime( $signup['registered'] ) ) ); ?></td>
								<td><?php echo esc_html( $last_email_sent ); ?></td>
								<td>
									<a href="<?php echo esc_url( $delete_url ); ?>" title="<?php esc_attr_e( 'Delete', 'signup-manager' ); ?>" class="button button-small button-danger">
										<span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Delete', 'signup-manager' ); ?>
									</a>
									<a href="<?php echo esc_url( $resend_url ); ?>" title="<?php esc_attr_e( 'Resend Activation', 'signup-manager' ); ?>" class="button button-small button-primary">
										<span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Resend', 'signup-manager' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="4" class="text-center"><?php esc_html_e( 'No pending signups found.', 'signup-manager' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}


	/**
	 * Delete a signup entry.
	 *
	 * @since 1.0.0
	 */
	public function delete_signup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'signup-manager' ) );
		}

		// Check if the 'signup_id' index is set and sanitize it.
		$signup_id = isset( $_GET['signup_id'] ) ? intval( $_GET['signup_id'] ) : 0;
		if ( ! $signup_id ) {
			wp_die( esc_html__( 'Invalid signup ID.', 'signup-manager' ) );
		}

		check_admin_referer( 'delete_signup_' . $signup_id );

		global $wpdb;
		$table_name = esc_sql( $this->table_name ); // Sanitize the table name.

		// Attempt to retrieve the entry from the cache before deletion.
		$cache_key = 'signup_' . $signup_id;
		$signup    = wp_cache_get( $cache_key );

		if ( false === $signup ) {
			// If not in cache, retrieve from the database and cache it.
			$signup = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$table_name} WHERE signup_id = %d", $signup_id ),
				ARRAY_A
			);
			if ( $signup ) {
				wp_cache_set( $cache_key, $signup );
			}
		}

		// Proceed to delete the signup entry and clear the cache.
		$deleted = $wpdb->delete( $table_name, array( 'signup_id' => $signup_id ), array( '%d' ) );

		if ( false !== $deleted ) {
			// Invalidate the cache after successful deletion.
			wp_cache_delete( $cache_key );
		}

		// Redirect safely after deletion.
		wp_safe_redirect( admin_url( 'admin.php?page=pending-signups' ) );
		exit;
	}

	/**
	 * Resend the activation email for a signup.
	 *
	 * @since 1.0.0
	 */
	public function resend_activation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'signup-manager' ) );
		}

		$signup_id = isset( $_GET['signup_id'] ) ? intval( $_GET['signup_id'] ) : 0;
		if ( ! $signup_id ) {
			wp_die( esc_html__( 'Invalid signup ID.', 'signup-manager' ) );
		}

		check_admin_referer( 'resend_activation_' . $signup_id );

		// Attempt to get the signup data from the cache.
		$cache_key = 'signup_' . $signup_id;
		$signup    = wp_cache_get( $cache_key );

		if ( false === $signup ) {
			global $wpdb;
			$table_name = esc_sql( $this->table_name ); // Sanitize the table name.
			$signup     = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$table_name} WHERE signup_id = %d", $signup_id ),
				ARRAY_A
			);

			// Store the result in the cache.
			if ( $signup ) {
				wp_cache_set( $cache_key, $signup );
			}
		}

		if ( $signup ) {
			// Update the last email sent date.
			$wpdb->update(
				$this->table_name,
				array( 'last_email_sent' => current_time( 'mysql' ) ),
				array( 'signup_id' => $signup_id ),
				array( '%s' ),
				array( '%d' )
			);

			$this->send_activation_email( $signup );
			wp_safe_redirect( admin_url( 'admin.php?page=pending-signups&resend=success' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=pending-signups&resend=failed' ) );
		}
		exit;
	}



	/**
	 * Send an activation email.
	 *
	 * @since 1.0.0
	 * @param array $signup The signup data.
	 */
	private function send_activation_email( $signup ) {
		wp_mail(
			$signup['user_email'],
			esc_html__( 'Activate Your Account', 'signup-manager' ),
			sprintf(
				// Translators: %s is the activation link for the user to activate their account.
				esc_html__( 'Please activate your account by clicking the following link: %s', 'signup-manager' ),
				esc_url( site_url( '/wp-activate.php?key=' . $signup['activation_key'] ) )
			)
		);
	}
}

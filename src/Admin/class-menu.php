<?php
/**
 * Handles the admin menu and pages.
 *
 * @package ruleforge-for-woo-lite
 */

namespace RuleForgeLite\Admin;

/**
 * Sets up the admin menu and pages.
 */
class Menu {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register' ) );
		add_action( 'admin_notices', array( __CLASS__, 'upgrade_notice' ) );
		add_action( 'wp_ajax_ruleforge_lite_dismiss_upgrade', array( __CLASS__, 'dismiss_upgrade_notice' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_fee_rule', array( __CLASS__, 'save' ) );
	}

	/**
	 * Register the submenu page.
	 */
	public static function register(): void {
		add_submenu_page( 'woocommerce', 'RuleForge Lite Rules', 'RuleForge Lite', 'manage_woocommerce', 'ruleforge-lite', array( __CLASS__, 'render' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 */
	public static function enqueue( $hook ): void {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen && 'fee_rule' === $screen->post_type ) {
			wp_enqueue_style( 'ruleforge-lite', plugins_url( 'assets/css/admin.css', dirname( __DIR__, 1 ) ), array(), '0.9.0' );
			wp_enqueue_script( 'ruleforge-lite', plugins_url( 'assets/js/editor.js', dirname( __DIR__, 1 ) ), array( 'jquery' ), '0.9.0', true );
		}
	}

	/**
	 * Add meta boxes.
	 */
	public static function boxes(): void {
		add_meta_box( 'rf-lite-conditions', 'Conditions', array( __CLASS__, 'box_conditions' ), 'fee_rule', 'normal', 'high' );
		add_meta_box( 'rf-lite-actions', 'Actions', array( __CLASS__, 'box_actions' ), 'fee_rule', 'normal', 'default' );
		add_meta_box( 'rf-lite-settings', 'Rule Settings', array( __CLASS__, 'box_settings' ), 'fee_rule', 'side', 'default' );
	}

	/**
	 * Render the main page.
	 */
	public static function render(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'RuleForge for Woo Lite', 'ruleforge-lite' ) . '</h1><p>' .
			wp_kses_post( __( 'Create up to 3 active rules. <a href="https://example.com/ruleforge" target="_blank">Upgrade to Pro</a> for unlimited rules, import/export, and analytics.', 'ruleforge-lite' ) ) .
			'</p></div>';
	}

	/**
	 * Render the conditions meta box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function box_conditions( $post ): void {
		$conds = get_post_meta( $post->ID, '_rf_conditions', true );
		echo '<textarea id="rf-conditions" name="_rf_conditions" rows="8" class="large-text" placeholder="[ {\'type\':\'subtotal\', \'op\':\'>=\', \'value\':100} ]">' . esc_textarea( $conds ) . '</textarea>';
		echo '<p><button type="button" class="button" id="rf-add-cond">' . esc_html__( 'Add subtotal ≥ 100', 'ruleforge-lite' ) . '</button></p>';
	}

	/**
	 * Render the actions meta box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function box_actions( $post ): void {
		$acts = get_post_meta( $post->ID, '_rf_actions', true );
		echo '<textarea id="rf-actions" name="_rf_actions" rows="6" class="large-text" placeholder="[ {\'type\':\'fee_percent\', \'value\':2, \'label\':\'Surcharge\'} ]">' .
			esc_textarea( $acts ) . '</textarea>';
		echo '<p><button type="button" class="button" id="rf-add-action">' . esc_html__( 'Add 2% surcharge', 'ruleforge-lite' ) . '</button></p>';
	}

	/**
	 * Render the settings meta box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function box_settings( $post ): void {
		wp_nonce_field( 'ruleforge_lite_save_meta', 'ruleforge_lite_meta_nonce' );
		$enabled      = (bool) get_post_meta( $post->ID, '_rf_enabled', true );
		$priority_val = get_post_meta( $post->ID, '_rf_priority', true );
		$priority     = (int) ( ! empty( $priority_val ) ? $priority_val : 10 );
		echo '<p><label><input type="checkbox" name="_rf_enabled" value="1" ' . checked( $enabled, true, false ) . '> ' . esc_html__( 'Enabled', 'ruleforge-lite' ) . '</label></p>';
		echo '<p><label>' . esc_html__( 'Priority', 'ruleforge-lite' ) . '<br/><input type="number" name="_rf_priority" value="' . esc_attr( $priority ) . '" class="small-text"></label></p>';
		echo '<p class="description">' . wp_kses_post( __( '<a href="https://example.com/ruleforge" target="_blank">Upgrade to Pro</a> for import/export, preview, stacking controls, and more.', 'ruleforge-lite' ) ) . '</p>';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save( $post_id ): void {
		if ( ! isset( $_POST['ruleforge_lite_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ruleforge_lite_meta_nonce'] ), 'ruleforge_lite_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, '_rf_enabled', isset( $_POST['_rf_enabled'] ) ? 1 : 0 );

		if ( isset( $_POST['_rf_priority'] ) ) {
			update_post_meta( $post_id, '_rf_priority', intval( $_POST['_rf_priority'] ) );
		}

		if ( isset( $_POST['_rf_conditions'] ) ) {
			update_post_meta( $post_id, '_rf_conditions', wp_kses_post( stripslashes( $_POST['_rf_conditions'] ) ) );
		}

		if ( isset( $_POST['_rf_actions'] ) ) {
			update_post_meta( $post_id, '_rf_actions', wp_kses_post( stripslashes( $_POST['_rf_actions'] ) ) );
		}
	}

	/**
	 * Display an upgrade notice.
	 */
	public static function upgrade_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'woocommerce_page_ruleforge-lite' !== $screen->id ) {
			return;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'ruleforge_lite_dismiss_upgrade', true );
		if ( $dismissed ) {
			return;
		}

		$url = 'https://example.com/ruleforge';
		?>
		<div class="notice notice-info is-dismissible ruleforge-lite-upgrade">
			<p>
				<?php
				echo wp_kses_post( __( '<strong>Need more than 3 rules?</strong> Upgrade to RuleForge Pro for unlimited rules, import/export, recipe presets, and analytics.', 'ruleforge-lite' ) );
				?>
				<a class="button button-primary" target="_blank" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Upgrade to Pro', 'ruleforge-lite' ); ?></a>
			</p>
		</div>
		<script>
			jQuery(function($){
				$(document).on("click", ".ruleforge-lite-upgrade .notice-dismiss", function() {
					jQuery.post(ajaxurl, {
						action: "ruleforge_lite_dismiss_upgrade",
						_ajax_nonce: "<?php echo esc_js( wp_create_nonce( 'rf_lite_ajax' ) ); ?>"
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * Handle the AJAX request to dismiss the upgrade notice.
	 */
	public static function dismiss_upgrade_notice() {
		check_ajax_referer( 'rf_lite_ajax' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Forbidden' );
		}
		update_user_meta( get_current_user_id(), 'ruleforge_lite_dismiss_upgrade', 1 );
		wp_die( 'ok' );
	}
}

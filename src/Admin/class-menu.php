<?php
/**
 * Admin Menu.
 *
 * @package ruleforge-lite
 */

namespace RuleForgeLite\Admin;

/**
 * Menu class.
 */
class Menu {

	/**
	 * Init hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register' ) );
		add_action( 'admin_notices', array( __CLASS__, 'upgrade_notice' ) );
		add_action(
			'wp_ajax_ruleforge_lite_dismiss_upgrade',
			function () {
				check_ajax_referer( 'rf_lite_ajax' );
				update_user_meta( get_current_user_id(), 'ruleforge_lite_dismiss_upgrade', 1 );
				wp_die( 'ok' );
			}
		);
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_fee_rule', array( __CLASS__, 'save' ) );
	}

	/**
	 * Register menu.
	 */
	public static function register(): void {
		add_submenu_page( 'woocommerce', 'RuleForge Lite Rules', 'RuleForge Lite', 'manage_woocommerce', 'ruleforge-lite', array( __CLASS__, 'render' ) );
	}

	/**
	 * Enqueue scripts.
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
	 * Render page.
	 */
	public static function render(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'RuleForge for Woo Lite', 'ruleforge-lite' ); ?></h1>
			<p>
				<?php
				printf(
					/* translators: %s: Pro link */
					esc_html__( 'Create up to 3 active rules. %s for unlimited rules, import/export, and analytics.', 'ruleforge-lite' ),
					'<a href="https://example.com/ruleforge" target="_blank">' . esc_html__( 'Upgrade to Pro', 'ruleforge-lite' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render conditions box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function box_conditions( $post ): void {
		$conds = get_post_meta( $post->ID, '_rf_conditions', true );
		wp_nonce_field( 'ruleforge_lite_save', '_rf_nonce' );
		?>
		<textarea id="rf-conditions" name="_rf_conditions" rows="8" class="large-text" placeholder="<?php echo esc_attr( '[{"type":"subtotal","op":">=","value":100}]' ); ?>"><?php echo esc_textarea( $conds ); ?></textarea>
		<p><button type="button" class="button" id="rf-add-cond"><?php esc_html_e( 'Add subtotal ≥ 100', 'ruleforge-lite' ); ?></button></p>
		<?php
	}

	/**
	 * Render actions box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function box_actions( $post ): void {
		$acts = get_post_meta( $post->ID, '_rf_actions', true );
		?>
		<textarea id="rf-actions" name="_rf_actions" rows="6" class="large-text" placeholder="<?php echo esc_attr( '[{"type":"fee_percent","value":2,"label":"Surcharge"}]' ); ?>"><?php echo esc_textarea( $acts ); ?></textarea>
		<p><button type="button" class="button" id="rf-add-action"><?php esc_html_e( 'Add 2% surcharge', 'ruleforge-lite' ); ?></button></p>
		<?php
	}

	/**
	 * Render settings box.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public static function box_settings( $post ): void {
		$enabled  = (bool) get_post_meta( $post->ID, '_rf_enabled', true );
		$priority = get_post_meta( $post->ID, '_rf_priority', true );
		$priority = $priority ? (int) $priority : 10;
		?>
		<p><label><input type="checkbox" name="_rf_enabled" value="1" <?php checked( $enabled, true ); ?>> <?php esc_html_e( 'Enabled', 'ruleforge-lite' ); ?></label></p>
		<p><label><?php esc_html_e( 'Priority', 'ruleforge-lite' ); ?><br/><input type="number" name="_rf_priority" value="<?php echo esc_attr( $priority ); ?>" class="small-text"></label></p>
		<p class="description">
			<?php
			printf(
				/* translators: %s: Pro link */
				esc_html__( '%s for import/export, preview, stacking controls, and more.', 'ruleforge-lite' ),
				'<a href="https://example.com/ruleforge" target="_blank">' . esc_html__( 'Upgrade to Pro', 'ruleforge-lite' ) . '</a>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Save post.
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save( $post_id ): void {
		if ( ! isset( $_POST['_rf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_rf_nonce'] ) ), 'ruleforge_lite_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce', $post_id ) ) {
			return;
		}

		$enabled = isset( $_POST['_rf_enabled'] ) ? 1 : 0;
		update_post_meta( $post_id, '_rf_enabled', $enabled );

		if ( isset( $_POST['_rf_priority'] ) ) {
			update_post_meta( $post_id, '_rf_priority', intval( $_POST['_rf_priority'] ) );
		}
		if ( isset( $_POST['_rf_conditions'] ) ) {
			update_post_meta( $post_id, '_rf_conditions', wp_kses_post( wp_unslash( $_POST['_rf_conditions'] ) ) );
		}
		if ( isset( $_POST['_rf_actions'] ) ) {
			update_post_meta( $post_id, '_rf_actions', wp_kses_post( wp_unslash( $_POST['_rf_actions'] ) ) );
		}
	}

	/**
	 * Upgrade notice.
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
				<strong><?php esc_html_e( 'Need more than 3 rules?', 'ruleforge-lite' ); ?></strong>
				<?php
				printf(
					/* translators: %s: Pro link */
					esc_html__( 'Upgrade to RuleForge Pro for unlimited rules, import/export, recipe presets, and analytics. %s', 'ruleforge-lite' ),
					'<a class="button button-primary" target="_blank" href="' . esc_url( $url ) . '">' . esc_html__( 'Upgrade to Pro', 'ruleforge-lite' ) . '</a>'
				);
				?>
			</p>
		</div>
		<script>
			jQuery( function( $ ) {
				$( document ).on( 'click', '.ruleforge-lite-upgrade .notice-dismiss', function() {
					$.post( ajaxurl, {
						action: 'ruleforge_lite_dismiss_upgrade',
						_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'rf_lite_ajax' ) ); ?>'
					} );
				} );
			} );
		</script>
		<?php
	}
}

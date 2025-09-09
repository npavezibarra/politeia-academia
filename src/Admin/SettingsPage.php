<?php
namespace Politeia\Academia\Admin;

class SettingsPage {
    public function register(): void {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_init', [ $this, 'settings' ] );
    }

    public function menu(): void {
        add_options_page( __( 'Politeia Academia', 'politeia-academia' ), __( 'Politeia Academia', 'politeia-academia' ), 'polilms_manage_settings', 'polilms-settings', [ $this, 'render' ] );
    }

    public function settings(): void {
        register_setting( 'polilms_settings', 'polilms_settings' );
        add_settings_section( 'polilms_main', '', '__return_false', 'polilms-settings' );
        add_settings_field( 'revoke_on_refund', __( 'Revoke on refund', 'politeia-academia' ), [ $this, 'field_revoke' ], 'polilms-settings', 'polilms_main' );
        add_settings_field( 'enable_buddyboss_activity', __( 'BuddyBoss activity', 'politeia-academia' ), [ $this, 'field_buddyboss' ], 'polilms-settings', 'polilms_main' );
        add_settings_field( 'default_visibility', __( 'Default visibility', 'politeia-academia' ), [ $this, 'field_visibility' ], 'polilms-settings', 'polilms_main' );
    }

    protected function get_settings(): array {
        $defaults = [
            'revoke_on_refund' => false,
            'enable_buddyboss_activity' => true,
            'default_visibility' => 'open_registered',
        ];
        return wp_parse_args( get_option( 'polilms_settings', [] ), $defaults );
    }

    public function field_revoke(): void {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="polilms_settings[revoke_on_refund]" value="1" <?php checked( $settings['revoke_on_refund'] ); ?> />
        <?php
    }

    public function field_buddyboss(): void {
        $settings = $this->get_settings();
        ?>
        <input type="checkbox" name="polilms_settings[enable_buddyboss_activity]" value="1" <?php checked( $settings['enable_buddyboss_activity'] ); ?> />
        <?php
    }

    public function field_visibility(): void {
        $settings = $this->get_settings();
        ?>
        <select name="polilms_settings[default_visibility]">
            <option value="open_registered" <?php selected( $settings['default_visibility'], 'open_registered' ); ?>><?php _e( 'Open (registered)', 'politeia-academia' ); ?></option>
            <option value="closed_paid" <?php selected( $settings['default_visibility'], 'closed_paid' ); ?>><?php _e( 'Closed (paid)', 'politeia-academia' ); ?></option>
        </select>
        <?php
    }

    public function render(): void {
        if ( ! current_user_can( 'polilms_manage_settings' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Politeia Academia Settings', 'politeia-academia' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'polilms_settings' ); ?>
                <?php do_settings_sections( 'polilms-settings' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

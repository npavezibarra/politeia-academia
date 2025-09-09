<?php
namespace Politeia\Academia\Modules\WooCommerce;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;
use Politeia\Academia\Modules\Enrollment\Module as Enrollment;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }
        add_action( 'add_meta_boxes_product', [ $this, 'add_product_meta_box' ] );
        add_action( 'save_post_product', [ $this, 'save_product_meta' ], 10, 2 );
        add_action( 'woocommerce_order_status_processing', [ $this, 'enroll_from_order' ] );
        add_action( 'woocommerce_order_status_completed', [ $this, 'enroll_from_order' ] );
        add_action( 'woocommerce_order_refunded', [ $this, 'maybe_revoke_on_refund' ] );
        add_filter( 'woocommerce_checkout_registration_required', [ $this, 'force_account_creation' ], 10, 2 );
    }

    public function migrations(): array {
        return [];
    }

    public function add_product_meta_box(): void {
        add_meta_box( 'polilms_product_course', __( 'Linked Course', 'politeia-academia' ), [ $this, 'render_product_meta_box' ], 'product', 'side' );
    }

    public function render_product_meta_box( $post ): void {
        wp_nonce_field( 'polilms_product_course', 'polilms_product_course_nonce' );
        $course_id = get_post_meta( $post->ID, '_polilms_course_id', true );
        $courses = get_posts( [ 'post_type' => 'course', 'numberposts' => -1 ] );
        echo '<select name="polilms_course_id" id="polilms_course_id">';
        echo '<option value="">' . esc_html__( 'None', 'politeia-academia' ) . '</option>';
        foreach ( $courses as $course ) {
            printf( '<option value="%d" %s>%s</option>', $course->ID, selected( $course_id, $course->ID, false ), esc_html( $course->post_title ) );
        }
        echo '</select>';
    }

    public function save_product_meta( $post_id, $post ): void {
        if ( ! isset( $_POST['polilms_product_course_nonce'] ) || ! wp_verify_nonce( $_POST['polilms_product_course_nonce'], 'polilms_product_course' ) ) {
            return;
        }
        $course_id = isset( $_POST['polilms_course_id'] ) ? intval( $_POST['polilms_course_id'] ) : 0;
        update_post_meta( $post_id, '_polilms_course_id', $course_id );
        if ( $course_id ) {
            update_post_meta( $course_id, '_polilms_wc_product_id', $post_id );
        }
    }

    public function enroll_from_order( $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            $course_id = get_post_meta( $product_id, '_polilms_course_id', true );
            if ( $course_id ) {
                Enrollment::enroll_user( $order->get_user_id(), intval( $course_id ), 'woocommerce', (string) $order_id );
            }
        }
    }

    public function maybe_revoke_on_refund( $order_id ): void {
        $settings = get_option( 'polilms_settings', [] );
        $revoke = $settings['revoke_on_refund'] ?? false;
        $revoke = apply_filters( 'polilms_wc_should_revoke_on_refund', $revoke );
        if ( ! $revoke ) {
            return;
        }
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            $course_id = get_post_meta( $product_id, '_polilms_course_id', true );
            if ( $course_id ) {
                Enrollment::revoke_enrollment( $order->get_user_id(), intval( $course_id ) );
            }
        }
    }

    public function force_account_creation( $required, $checkout ): bool {
        if ( is_user_logged_in() ) {
            return $required;
        }
        foreach ( $checkout->get_cart()->get_cart() as $cart_item ) {
            $course_id = get_post_meta( $cart_item['product_id'], '_polilms_course_id', true );
            if ( $course_id ) {
                return true;
            }
        }
        return $required;
    }
}

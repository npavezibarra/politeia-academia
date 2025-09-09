<?php
namespace Politeia\Academia\Modules\Courses;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'add_meta_boxes_polilms_course', [ $this, 'meta_box' ] );
        add_action( 'save_post_polilms_course', [ $this, 'save_meta' ], 10, 2 );
    }

    public function migrations(): array {
        return [ Migrations\Init_2025_09_09_000001::class ];
    }

    public function register_cpt(): void {
        register_post_type( 'polilms_course', [
            'label' => __( 'Courses', 'politeia-academia' ),
            'public' => true,
            'supports' => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'show_in_rest' => true,
        ] );
    }

    public function meta_box( $post ): void {
        add_meta_box(
            'polilms_course_meta',
            __( 'Course Settings', 'politeia-academia' ),
            [ $this, 'render_meta_box' ],
            'polilms_course'
        );
    }

    public function render_meta_box( $post ): void {
        wp_nonce_field( 'polilms_course_meta', 'polilms_course_nonce' );
        $visibility = get_post_meta( $post->ID, '_polilms_visibility', true ) ?: 'open_registered';
        $product_id = get_post_meta( $post->ID, '_polilms_wc_product_id', true );
        ?>
        <p>
            <label for="polilms_visibility"><?php _e( 'Visibility', 'politeia-academia' ); ?></label>
            <select name="polilms_visibility" id="polilms_visibility">
                <option value="open_registered" <?php selected( $visibility, 'open_registered' ); ?>><?php _e( 'Open (registered users)', 'politeia-academia' ); ?></option>
                <option value="closed_paid" <?php selected( $visibility, 'closed_paid' ); ?>><?php _e( 'Closed (paid)', 'politeia-academia' ); ?></option>
            </select>
        </p>
        <p>
            <label for="polilms_wc_product_id"><?php _e( 'WooCommerce Product ID', 'politeia-academia' ); ?></label>
            <input type="number" name="polilms_wc_product_id" id="polilms_wc_product_id" value="<?php echo esc_attr( $product_id ); ?>" />
        </p>
        <?php
    }

    public function save_meta( $post_id, $post ): void {
        if ( ! isset( $_POST['polilms_course_nonce'] ) || ! wp_verify_nonce( $_POST['polilms_course_nonce'], 'polilms_course_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'polilms_manage_courses', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['polilms_visibility'] ) ) {
            $visibility = sanitize_text_field( $_POST['polilms_visibility'] );
            update_post_meta( $post_id, '_polilms_visibility', $visibility );
        }

        if ( isset( $_POST['polilms_wc_product_id'] ) ) {
            $product_id = intval( $_POST['polilms_wc_product_id'] );
            update_post_meta( $post_id, '_polilms_wc_product_id', $product_id );
        }
    }
}

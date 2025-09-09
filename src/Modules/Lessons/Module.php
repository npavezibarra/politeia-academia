<?php
namespace Politeia\Academia\Modules\Lessons;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'add_meta_boxes_polilms_lesson', [ $this, 'meta_box' ] );
        add_action( 'save_post_polilms_lesson', [ $this, 'save_meta' ], 10, 2 );
    }

    public function migrations(): array {
        return [ Migrations\Init::class ];
    }

    public function register_cpt(): void {
        register_post_type( 'polilms_lesson', [
            'label' => __( 'Lessons', 'politeia-academia' ),
            'public' => true,
            'supports' => [ 'title', 'editor', 'thumbnail' ],
            'show_in_rest' => true,
        ] );
    }

    public function meta_box( $post ): void {
        add_meta_box(
            'polilms_lesson_meta',
            __( 'Lesson Settings', 'politeia-academia' ),
            [ $this, 'render_meta_box' ],
            'polilms_lesson'
        );
    }

    public function render_meta_box( $post ): void {
        wp_nonce_field( 'polilms_lesson_meta', 'polilms_lesson_nonce' );
        $course_id = get_post_meta( $post->ID, '_polilms_course_id', true );
        $order = get_post_meta( $post->ID, '_polilms_lesson_order', true );
        $required = get_post_meta( $post->ID, '_polilms_required', true );
        ?>
        <p>
            <label for="polilms_course_id"><?php _e( 'Course ID', 'politeia-academia' ); ?></label>
            <input type="number" name="polilms_course_id" id="polilms_course_id" value="<?php echo esc_attr( $course_id ); ?>" />
        </p>
        <p>
            <label for="polilms_lesson_order"><?php _e( 'Order', 'politeia-academia' ); ?></label>
            <input type="number" name="polilms_lesson_order" id="polilms_lesson_order" value="<?php echo esc_attr( $order ); ?>" />
        </p>
        <p>
            <label><input type="checkbox" name="polilms_required" value="1" <?php checked( $required, '1' ); ?> /> <?php _e( 'Required', 'politeia-academia' ); ?></label>
        </p>
        <?php
    }

    public function save_meta( $post_id, $post ): void {
        if ( ! isset( $_POST['polilms_lesson_nonce'] ) || ! wp_verify_nonce( $_POST['polilms_lesson_nonce'], 'polilms_lesson_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'polilms_manage_lessons', $post_id ) ) {
            return;
        }

        $course_id = isset( $_POST['polilms_course_id'] ) ? intval( $_POST['polilms_course_id'] ) : 0;
        update_post_meta( $post_id, '_polilms_course_id', $course_id );
        if ( $course_id ) {
            wp_update_post( [ 'ID' => $post_id, 'post_parent' => $course_id ] );
        }
        $order = isset( $_POST['polilms_lesson_order'] ) ? intval( $_POST['polilms_lesson_order'] ) : 0;
        update_post_meta( $post_id, '_polilms_lesson_order', $order );
        $required = isset( $_POST['polilms_required'] ) ? '1' : '0';
        update_post_meta( $post_id, '_polilms_required', $required );
    }
}

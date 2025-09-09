<?php
namespace Politeia\Academia\Modules\Quizzes;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;
use Politeia\Academia\Core\ServiceContainer;

class Module implements ModuleContract {
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function register(): void {
        add_shortcode( 'polilms_quiz', [ $this, 'render_quiz_shortcode' ] );
    }

    public function migrations(): array {
        return [ Migrations\Init_2025_09_09_000004_quizzes_attempts::class ];
    }

    public function render_quiz_shortcode( $atts, $content = '' ): string {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts );
        $quiz_id = intval( $atts['id'] );
        if ( ! $quiz_id ) {
            return '';
        }
        ob_start();
        include locate_template( 'politeia-academia/quiz.php', false, false ) ?: POLIAC_DIR . 'templates/quiz.php';
        return ob_get_clean();
    }
}

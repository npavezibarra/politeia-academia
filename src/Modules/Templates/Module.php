<?php
namespace Politeia\Academia\Modules\Templates;

use Politeia\Academia\Core\Contracts\Module as ModuleContract;

class Module implements ModuleContract {
  public function register(): void {
    add_filter('archive_template', [ $this, 'archive_course_template' ], 20);
  }
  public function migrations(): array { return []; }

  public function archive_course_template($template) {
    if (is_post_type_archive('course')) {
      $theme_tpl  = WP_CONTENT_DIR . '/politeia-academia/archive-course.php';
      $plugin_tpl = POLIAC_DIR . 'templates/archive-course.php';
      if (file_exists($theme_tpl))  return $theme_tpl;
      if (file_exists($plugin_tpl)) return $plugin_tpl;
    }
    return $template;
  }
}


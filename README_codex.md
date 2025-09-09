# Politeia Academia (LMS) — Codex Build Brief
Goal

Complete a lean LMS plugin (politeia-academia) using the existing structure. Keep CPTs for Courses and Lessons; use custom tables for Enrollments, Progress, Quizzes, Questions, Attempts. Integrate with WooCommerce for paid access and play nicely with BuddyBoss.

Constraints & Standards

PHP ≥ 7.4, PSR-4 via Composer (Politeia\Academia\ → src/).

WordPress Coding Standards; escape/sanitize; nonces on writes.

Tables prefix: ${$wpdb->prefix}politeia_lms_* (already defined as POLIAC_TABLE_PREFIX).

Modular architecture: each module owns hooks + migrations.

Current Structure (must keep)
politeia-academia/
├─ politeia-academia.php
├─ composer.json
├─ assets/js/{lesson.js,quiz.js}
├─ templates/{single-lesson.php,quiz.php,parts/}
├─ src/
│  ├─ Core/{Plugin.php,Activator.php,ServiceContainer.php}
│  │          Contracts/{Module.php,Migration.php}
│  │          Migrations/MigrationRunner.php
│  └─ Modules/
│     ├─ Courses/{Module.php, Migrations/2025_09_09_000001_init.php}
│     ├─ Lessons/{Module.php, Migrations/2025_09_09_000002_init.php}
│     ├─ Enrollment/{Module.php, Migrations/2025_09_09_000003_enrollments_progress.php}
│     ├─ Quizzes/{Module.php, Migrations/2025_09_09_000004_quizzes_attempts.php}
│     ├─ WooCommerce/Module.php
│     ├─ BuddyBoss/Module.php
│     ├─ REST/Module.php
│     └─ Templates/Module.php
└─ uninstall.php (to add)

Deliverables (Definition of Done)
1) Helpers

Create these files:

src/Core/Helpers/DB.php

static function table(string $suffix): string → returns POLIAC_TABLE_PREFIX . $suffix.

src/Core/Helpers/Access.php

API:

static function has_access(int $user_id, int $course_id): bool

static function is_enrolled(int $user_id, int $course_id): bool

static function course_visibility(int $course_id): string // open_registered|closed_paid

Logic:

open_registered → must be logged in.

closed_paid → must have active enrollment (in table) or filter polilms_has_access true.

Add filter points:

polilms_has_access($bool, $user_id, $course_id)

polilms_course_visibility($visibility, $course_id)

2) Course & Lesson Meta

Course meta keys: _polilms_visibility (open_registered default, closed_paid), _polilms_wc_product_id (int).

Lesson meta keys: _polilms_course_id (int), _polilms_lesson_order (int), _polilms_required (bool).

Add simple meta boxes to edit these in Courses\Module and Lessons\Module (save with sanitization & nonce).

Ensure lesson post_parent mirrors course ID when set.

3) Enrollment API (server-side)

In Enrollment/Module.php:

Functions:

static enroll_user(int $user_id, int $course_id, string $source='manual', ?string $ref=null): bool

static revoke_enrollment(int $user_id, int $course_id): bool

static progress_mark_complete(int $user_id, int $course_id, int $lesson_id): bool

Use prepared statements on tables enrollments and progress (unique keys already set).

Action hooks:

do_action('polilms_course_enrolled', $user_id, $course_id, $source, $ref)

do_action('polilms_lesson_completed', $user_id, $course_id, $lesson_id)

4) WooCommerce Integration

Complete WooCommerce/Module.php:

Product link: Add a meta box on product to select a Course (1:1). Save to course _polilms_wc_product_id.

Auto-enroll on order status → processing|completed:

For each line item product linked to a course, call Enrollment::enroll_user( buyer_id, course_id, 'woocommerce', $order_id ).

Revoke on refund (behind setting toggle; default off):

On woocommerce_order_refunded, revoke.

Checkout behavior: if product is course and user is not logged in, enforce account creation or filter woocommerce_checkout_registration_required.

Filters:

polilms_wc_should_revoke_on_refund (bool).

5) Access/Gating Middleware

On template load for single lesson and single course:

If ! Access::has_access( current_user, course_id ), show a gate partial:

For closed_paid: show product price/button (link to product).

For open_registered: show login/register CTA.

Implement a reusable function polilms_render_gate( $course_id ) in Templates/Module.php and include from templates.

6) REST API (read/write)

Fill REST/Module.php:

Namespace: polilms/v1

Routes:

GET /courses → list (id, title, excerpt, visibility, thumbnail, lessons_count).

GET /courses/(?P<id>\d+)/lessons → lessons (id, title, order, completed bool if logged in).

GET /progress/(?P<course_id>\d+) → completed lesson IDs for current user.

POST /progress/complete → body: { lesson_id } → marks complete (nonce required; verify).

GET /quiz/(?P<id>\d+) → quiz with questions (no correct answers).

POST /quiz/submit → body: { quiz_id, answers } → grade + store attempt (reuse AJAX logic; return score/passed).

Permission:

Read endpoints: public for course/lessons; progress & submit require logged in.

Nonces:

Accept X-WP-Nonce for POSTs; use wp_create_nonce('wp_rest') client-side.

7) Shortcodes & Blocks

Already have [polilms_quiz]. Add:

[polilms_course_list] → grid of published courses with CTA (Buy/Start).

[polilms_lesson_list course_id="123"] → ordered list with completion ticks.

Provide equivalent Gutenberg blocks (optional if time): server-rendered blocks wrapping those shortcodes.

8) Templates (BuddyBoss-friendly)

Keep templates/single-lesson.php minimal; inject gate UI when no access.

Add templates/single-course.php:

Show course summary, lessons list, progress %, and CTA according to visibility.

Allow overrides at /wp-content/politeia-academia/{single-lesson.php, single-course.php, quiz.php} (already set for lesson & quiz; add filter for course).

Add partial templates/parts/gate.php.

9) BuddyBoss Integration

Complete BuddyBoss/Module.php:

If BuddyBoss active, on:

polilms_course_enrolled → post activity “enrolled in {course}”.

polilms_lesson_completed → post activity “completed {lesson}”.

Add setting toggle to enable/disable activity posts.

10) Settings Page

Create src/Admin/SettingsPage.php and wire from Core\Plugin::boot() (admin-only):

Page: Politeia Academia under Settings.

Options (store in polilms_settings):

revoke_on_refund (bool, default false)

enable_buddyboss_activity (bool, default true)

default_visibility (open_registered default)

Use register_setting + add_settings_section/field.

11) Security & Caps

Capabilities (map to roles later):

polilms_manage_courses, polilms_manage_lessons, polilms_manage_enrollments, polilms_manage_settings.

Check caps on admin pages & meta boxes.

Sanitize all meta; escape all outputs; verify nonces on writes.

12) Uninstall

Create uninstall.php:

If option polilms_settings['purge_on_uninstall'] is true, drop politeia_lms_* tables and delete options/meta your plugin added. Otherwise, leave data intact.

Key Function Signatures (to implement)
// src/Core/Helpers/Access.php
namespace Politeia\Academia\Core\Helpers;
class Access {
  public static function has_access(int $user_id, int $course_id): bool {}
  public static function is_enrolled(int $user_id, int $course_id): bool {}
  public static function course_visibility(int $course_id): string {}
}

// src/Modules/Enrollment/Module.php
namespace Politeia\Academia\Modules\Enrollment;
class Module implements \Politeia\Academia\Core\Contracts\Module {
  public static function enroll_user(int $user_id, int $course_id, string $source='manual', ?string $ref=null): bool {}
  public static function revoke_enrollment(int $user_id, int $course_id): bool {}
  public static function progress_mark_complete(int $user_id, int $course_id, int $lesson_id): bool {}
}

Manual Test Plan (acceptance)

Activate plugin; DB version increments via migrations; tables exist.

Create a Course (visibility open). Add Lessons linked via meta; visit a lesson:

Mark complete works (row in *_progress).

Link Course ⇄ Product; buy product (processing/completed):

Enrollment row created; access allowed when visibility is closed_paid.

Quiz attached to a lesson:

Renders; submit grades; attempt stored; pass/fail respected.

Gate UI:

Logged-out user sees login/register for open courses; buy CTA for closed courses.

REST:

GET /polilms/v1/courses returns list.

POST /polilms/v1/progress/complete with nonce marks a lesson.

BuddyBoss (if active):

Enrollment/Completion create activity items (if enabled).

Settings page:

Toggles take effect; refund revokes when enabled.

Nice-to-Have (if time remains)

Course completion when all required lessons done (+ activity).

Progress % badge in course header.

Simple reports (per-course completion rate).

Block variations for course cards.

Notes for Codex

Prefer dbDelta for schema changes; add new migration files with higher versions.

Use apply_filters('polilms_modules', $modules) (already in Core\Plugin) to keep module list extensible.

Keep template HTML lean; no heavy CSS—inherit BuddyBoss styles.

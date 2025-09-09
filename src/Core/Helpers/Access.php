<?php
namespace Politeia\Academia\Core\Helpers;

class Access {
    public static function has_access(int $user_id, int $course_id): bool {
        $visibility = self::course_visibility( $course_id );
        $has = false;

        if ( 'open_registered' === $visibility ) {
            $has = $user_id > 0;
        } elseif ( 'closed_paid' === $visibility ) {
            $has = self::is_enrolled( $user_id, $course_id );
        }

        return (bool) apply_filters( 'polilms_has_access', $has, $user_id, $course_id );
    }

    public static function is_enrolled(int $user_id, int $course_id): bool {
        global $wpdb;
        $table = DB::table( 'enrollments' );
        $sql = $wpdb->prepare( "SELECT 1 FROM {$table} WHERE user_id = %d AND course_id = %d AND status = 'active' LIMIT 1", $user_id, $course_id );
        return (bool) $wpdb->get_var( $sql );
    }

    public static function course_visibility(int $course_id): string {
        $visibility = get_post_meta( $course_id, '_polilms_visibility', true );
        if ( ! $visibility ) {
            $visibility = 'open_registered';
        }
        return apply_filters( 'polilms_course_visibility', $visibility, $course_id );
    }
}

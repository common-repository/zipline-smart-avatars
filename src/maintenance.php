<?php
namespace Zipline\ZLSmartAvatars;

/**
 * Maintenance tools.
 *
 * @since 1.0.0
 */
class Maintenance {
    /**
     * Run maintenance tasks.
     *
     * @param int $version
    */
    public static function run( int $version ) {
        switch ( $version ) {
            case 1:
                self::example_task();
                break;
        }
    }

    /**
     * An example.
     *
     * @since 1.0.0
     */
    public static function example_task() {
    }
}

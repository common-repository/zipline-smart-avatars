<?php
namespace Zipline\ZLSmartAvatars;

use Redux;

/**
 * Functionality for storing and retrieving settings.
 *
 * @since 1.0.0
 */
class Settings {
    /**
     * Save setting.
     *
     * @since 1.0.0
     *
     * @param string $name
     * @param mixed  $value
     * @return bool
     */
    public static function set( string $name, $value ): bool {
        return Redux::set_option( Main::REDUX_OPTIONS_NAME, $name, $value );
    }

    /**
     * Get setting.
     *
     * @since 1.0.0
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed|string
     */
    public static function get( string $name, $default = false ) {
        $value = Redux::get_option( Main::REDUX_OPTIONS_NAME, $name );

        if ( $value === null ) {
            return $default;
        }

        return $value;
    }
}

<?php
    namespace Zipline\ZLSmartAvatars\Admin\Redux;

    use Redux;
    use Redux_Core;
    use ReduxFramework;
    use Zipline\ZLSmartAvatars\Main;

    /**
     * Redux framework utilities.
     *
     * @since 0.49.0
     */
    class Utilities {
        /**
         * @since 0.49.0
         * @var string|null Cache of active version of Redux.
         */
        private static $active_version_cache = null;

        /**
         * @since 0.49.0
         * @var string|null Cache of whether version 4.3 or above is being used.
         */
        private static $is_version43_cache = null;

        /**
         * Get current version of Redux being used.
         *
         * @since 0.49.0
         *
         * @return string
         */
        public static function get_version(): string {
            // Check cache
            if ( self::$active_version_cache !== null ) {
                return self::$active_version_cache;
            }

            // Version 4
            if ( class_exists( '\Redux_Core') ) {
                self::$active_version_cache = Redux_Core::$version;

                return self::$active_version_cache;
            }

            // Version 3
            if ( class_exists( '\ReduxFramework') ) {
                self::$active_version_cache = ReduxFramework::$_version;

                return self::$active_version_cache;
            }

            // Try plugin
            if ( ! defined( 'REDUX_PLUGIN_FILE' ) ) {
                self::$active_version_cache = '';

                return self::$active_version_cache;
            }

            $path = REDUX_PLUGIN_FILE;

            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if ( ! function_exists( 'get_plugin_data' ) || ! file_exists( $path ) ) {
                self::$active_version_cache = '';

                return self::$active_version_cache;
            }

            $data = get_plugin_data( $path );

            if ( empty( $data ) || ! isset( $data['Version'] ) || $data['Version'] === '' ) {
                self::$active_version_cache = '';

                return self::$active_version_cache;
            }

            return $data['Version'];
        }

        /**
         * Determine if version 4.3 of Redux or above is being used.
         *
         * @since 0.49.0
         */
        public static function is_version43(): bool {
            if ( self::$is_version43_cache !== null ) {
                return self::$is_version43_cache;
            }

            self::$is_version43_cache = version_compare( self::get_version(), '4.3', '>=' );

            return self::$is_version43_cache;
        }

        /**
         * Set Redux global arguments.
         *
         * @since 0.49.0
         *
         * @param array  $args
         */
        public static function set_args( array $args ) {
            if ( self::is_version43() ) {
                Redux::set_args( Main::REDUX_OPTIONS_NAME, $args );
            } else {
                Redux::setArgs( Main::REDUX_OPTIONS_NAME, $args );
            }
        }

        /**
         * Set Redux section.
         *
         * @since 0.49.0
         *
         * @param array $args
         * @param bool  $replace Only applies to Redux v4 or above.
         */
        public static function set_section( array $args, bool $replace = false ) {
            if ( self::is_version43() ) {
                Redux::set_section( Main::REDUX_OPTIONS_NAME, $args, $replace );
            } else {
                Redux::setSection( Main::REDUX_OPTIONS_NAME, $args );
            }
        }

        /**
         * Remove Redux section.
         *
         * @since 0.49.0
         *
         * @param string $section_id
         * @param bool   $remove_fields
         */
        public static function remove_section( string $section_id, bool $remove_fields = false ) {
            if ( self::is_version43() ) {
                Redux::remove_section( Main::REDUX_OPTIONS_NAME, $section_id, $remove_fields );
            } else {
                Redux::removeSection( Main::REDUX_OPTIONS_NAME, $section_id, $remove_fields );
            }
        }

        /**
         * Get Redux field.
         *
         * @since 0.49.0
         *
         * @param string $field_id
         * @return false|array
         */
        public static function get_field( string $field_id ) {
            if ( self::is_version43() ) {
                return Redux::get_field( Main::REDUX_OPTIONS_NAME, $field_id );
            } else {
                return Redux::getField( Main::REDUX_OPTIONS_NAME, $field_id );
            }
        }

        /**
         * Set Redux field.
         *
         * @since 0.49.0
         *
         * @param string $section_id
         * @param array  $field
         */
        public static function set_field( string $section_id, array $field ) {
            // In version 4 of Redux plugin, the method signature has changed
            if ( self::is_version43() ) {
                Redux::set_field( Main::REDUX_OPTIONS_NAME, $section_id, $field );
            } elseif ( version_compare( self::get_version(), '4', '>=' ) ) {
                Redux::setField( Main::REDUX_OPTIONS_NAME, $section_id, $field );
            } else {
                Redux::setField( Main::REDUX_OPTIONS_NAME, $field );
            }
        }

        /**
         * Get option via Redux.
         *
         * @since 1.0.0
         *
         * @param string $name
         * @param mixed  $default
         * @return mixed
         */
        public static function get_option( string $name, $default = false ) {
            if ( self::is_version43() ) {
                $value = Redux::get_option( Main::REDUX_OPTIONS_NAME, $name, $default );
            } else {
                $value = Redux::getOption( Main::REDUX_OPTIONS_NAME, $name );

                if ( $value === null ) {
                    $value = $default;
                }
            }

            return $value;
        }

        /**
         * Set option value via Redux.
         *
         * @since 0.49.0
         *
         * @param string $name
         * @param mixed  $value
         * @return bool
         */
        public static function set_option( string $name, $value ): bool {
            if ( self::is_version43() ) {
                return Redux::set_option( Main::REDUX_OPTIONS_NAME, $name, $value );
            } else {
                return Redux::setOption( Main::REDUX_OPTIONS_NAME, $name, $value );
            }
        }
    }

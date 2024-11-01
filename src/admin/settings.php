<?php
namespace Zipline\ZLSmartAvatars\Admin;

use Zipline\ZLSmartAvatars\Main;
use Zipline\ZLSmartAvatars\Redux_Configuration;

/**
 * Functionality for WP admin settings.
 *
 * @since 1.0.0
 */
class Admin_Settings {
    /**
     * Slug for settings page.
     *
     * @since 1.0.0
     */
    const PAGE_SLUG = 'zl-smart-avatars';

    /**
     * The main plugin object.
     *
     * @var Main
     * @since 1.0.0
     */
    public $parent = null;

    /**
     * @since NEXT
     * @var string Redux plugin version.
     */
    private static $redux_version;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param Main $parent
     */
    public function __construct( Main $parent ) {
        $this->parent = $parent;

        Redux_Configuration::init();

        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ), [ $this, 'add_settings_link' ] );

        // Display redux version warning if required
        add_action( 'redux/' . Main::REDUX_OPTIONS_NAME . '/panel/before', [ __CLASS__, 'maybe_display_redux_version_notice' ] );
    }

    /**
     * Add settings link to plugin list table.
     *
     * @since 1.0.0
     *
     * @param array $links Existing links.
     * @return array Modified links.
     */
    public function add_settings_link( array $links ): array {
        $settings_link = '<a href="options-general.php?page=' . self::PAGE_SLUG . '">' .
            __( 'Settings', 'zl-smart-avatars' ) .
            '</a>';

        $links[] = $settings_link;

        return $links;
    }

    /**
     * Display a notice to upgrade Redux plugin if less than version 4 or higher is not being used.
     *
     * @since NEXT
     */
    public static function maybe_display_redux_version_notice() {
        if ( version_compare( self::get_redux_version(), 4, '>=' ) ) {
            return;
        }

        $url = '<a href="https://wordpress.org/plugins/redux-framework/" target="_blank">Redux Framework</a>';
        ?>

        <div class="notice notice-warning">
            <p><?php echo sprintf( __(
                    'Some options will be missing. Please install or upgrade %s plugin.',
                    'zl-smart-avatars'
                ), $url ); ?></p>
        </div>

        <?php
    }

    /**
     * Get current version of redux being used.
     *
     * @since NEXT
     *
     * @return string
     */
    private static function get_redux_version(): string {
        // Version 4
        if ( class_exists( '\Redux_Core') ) {
            return \Redux_Core::$version;
        }

        // Version 3
        if ( class_exists( '\ReduxFramework') ) {
            return \ReduxFramework::$_version;
        }

        // Try plugin
        if ( ! defined( 'REDUX_PLUGIN_FILE' ) ) {
            return '';
        }

        $path = REDUX_PLUGIN_FILE;

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ! function_exists( 'get_plugin_data' ) || ! file_exists( $path ) ) {
            self::$redux_version = '';

            return self::$redux_version;
        }

        $data = get_plugin_data( $path );

        if ( empty( $data ) || ! isset( $data['Version'] ) || $data['Version'] === '' ) {
            self::$redux_version = '';

            return self::$redux_version;
        }

        return $data['Version'];
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
    }
}


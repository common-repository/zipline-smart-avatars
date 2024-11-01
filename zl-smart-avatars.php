<?php
/**
 * Plugin Name:  Zipline Smart Avatars
 * Plugin URI:   https://ziplinecommunities.com
 * Description:  A plugin to help select an avatar for users.
 * Version:      1.1.1
 * Requires PHP: 7.3
 * Author:       Zipline
 * Author URI:   https://ziplinecommunities.com
 * Donate link:  https://ziplinecommunities.com
 * License:      See license.txt
 * Text Domain:  zl-smart-avatars
 * Domain Path:  /languages
 *
 * @link    https://ziplinecommunities.com
 *
 * @version 1.1.1
 */
namespace Zipline\ZLSmartAvatars;

use Exception;
use Zipline\ZLSmartAvatars\Admin\Admin_Settings;
use Zipline\ZLSmartAvatars\Admin\Admin_Tools;
use Zipline\ZLSmartAvatars\Features\User_Choose_Avatar;
use Zipline\ZLSmartAvatars\Features\ZL_Default_Avatar;
use Zipline\ZLSmartAvatars\Features\ZL_Random_Avatar;
use Zipline\ZLSmartAvatars\Features\ZL_Admin_Upload_Avatar;

/**
 * Copyright (c) 2022 Zipline (email : hello@wearezipline.com)
 */

// Use composer autoload.
require_once __DIR__ . '/vendor/autoload_packages.php';
require_once __DIR__ . '/shortcut-function.php';

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main initiation class.
 *
 * @since 1.0.0
 */
class Main {
    /**
     * Current version.
     *
     * @var    string
     * @since 1.0.0
     */
    const VERSION = '1.1.1';

    /**
     * What version of maintenance upgrades we are at.
     */
    const MAINTENANCE_VERSION = 1;

    /**
     * Name for redux options.
     */
    const REDUX_OPTIONS_NAME = 'zipline_smart_avatars';

    /**
     * Avatars path
     */
    const AVATARS_PATH = 'assets/images/avatars/';

    /**
     * Demo avatars directory name.
     *
     * @since NEXT
     * @const string
     */
    const DEMO_AVATARS_DIR_NAME = 'demo-avatars';

    /**
     * Nature avatars directory name.
     *
     * @since NEXT
     * @const string
     */
    const NATURE_AVATARS_DIR_NAME = 'nature';

    /**
     * Cover images path
     */
    const COVER_IMAGES_PATH = 'assets/images/cover-images/';

    /**
     * Settings Page slug
     */
    const PAGE_SLUG = 'zl-smart-avatars';


    /**
     * The token, used to prefix values in DB.
     *
     * @var   string
     * @since 1.0.0
     */
    public $_token = 'zipline_smart_avatars';

    /**
     * URL of plugin directory with trailing slash.
     *
     * @var    string
     * @since 1.0.0
     */
    public $url = '';

    /**
     * Path of plugin directory with trailing slash.
     *
     * @var    string
     * @since 1.0.0
     */
    public $path = '';

    /**
     * Plugin basename.
     *
     * @var    string
     * @since 1.0.0
     */
    protected $basename = '';

    /**
     * The main plugin file.
     *
     * @var string
     * @since 1.0.0
     */
    public $file;

    /**
     * Detailed activation error messages.
     *
     * @var    array
     * @since 1.0.0
     */
    protected $activation_errors = [];

    /**
     * Singleton instance of plugin.
     *
     * @since 1.0.0
     * @var    Main
     */
    protected static $instance = null;

    /**
     * @var Admin_Settings Only initialised in WP admin.
     * @since 1.0.0
     */
    public $settings;

    /**
     * REST API endpoints.
     *
     * @since 1.1.0
     * @var REST_API|null
     */
    public $rest_api = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @since 1.0.0
     * @return Main A single instance of this class.
     */
    public static function instance(): Main {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Sets up our plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->file     = basename( __FILE__ );
        $this->basename = plugin_basename( __FILE__ );
        $this->url      = plugin_dir_url( __FILE__ );
        $this->path     = plugin_dir_path( __FILE__ );
     }

    /**
     * Initialise BP features.
     *
     * @since 1.0.0
     * @when 'bp_init'
     */
    public function init_buddypress() {
        // Default Avatar
        ZL_Default_Avatar::init();

        // Random Avatar
        ZL_Random_Avatar::init();

        // Admin create bank of Random Avatars
        ZL_Admin_Upload_Avatar::init();

        // Allow users to change their randomly assigned avatar
        User_Choose_Avatar::init();
    }

    /**
     * Hooks run at plugins_loaded level 4
     *
     * Level 4 chosen to be before BuddyPress and Taxonomy_Core and to allow other plugins to come before us.
     *
     * @since 1.0.0
     */
    public function early_hooks() {
        // Redux Framework
        if (
            ! class_exists( 'ReduxFramework' ) &&
            file_exists( dirname( __FILE__ ) . '/vendor-lib/redux-core/framework.php' )
        ) {
            require_once( dirname( __FILE__ ) . '/vendor-lib/redux-core/framework.php' );
        }

        if ( is_admin() ) {
            $this->settings = new Admin_Settings( $this );

            Admin_Tools::init_hooks();
        }

        add_action( 'bp_init', [ $this, 'init_early_buddypress' ], 1 );
        add_action( 'bp_init', [ $this, 'init_buddypress' ] );
    }

    /**
     * Add hooks and filters.
     *
     * Priority needs to be
     * < 10 for CPT_Core,
     * < 5 for Taxonomy_Core,
     * and 0 for Widgets because widgets_init runs at init priority 1.
     *
     * @since 1.0.0
     */
    public function hooks() {
        // Initialise features early
        add_action( 'init', [ $this, 'early_init' ], 0 );

        // Initialise REST API
        add_action( 'bp_rest_api_init', [ $this, 'api_init' ] );

        // Scripts and styles
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ], 999 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 999 );
        add_action( 'bp_init',               [ $this, 'enqueue_frontend_styles' ], 999 );

        // Disable gravatars
        add_filter( 'bp_core_fetch_avatar_no_grav', '__return_true' );

        // Remove redux menu item from tools.php
        add_action( 'admin_init', [ $this, 'remove_tools_submenu_item' ], 12 );

        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'add_settings_link' ] );
    }

    /**
     * Initialise early BuddyPress features.
     *
     * @return void
     * @since 1.1.0
     */
    public function init_early_buddypress() {
        $this->register_buddypress_template_stack();
    }

    /**
     * Registers custom buddypress templates
     *
     * @return void
     * @since 1.1.0
     */
    public function register_buddypress_template_stack(): void {
        if ( ! function_exists( '\bp_register_template_stack' ) ) {
            return;
        }

        $template_stack = '';
        $legacy_path = $this->path . 'src/templates/bp-legacy';
        $nouveau_path = $this->path . 'src/templates/bp-nouveau';

        if ( function_exists( 'bp_get_theme_package_id' ) ) {
            $theme_package_id = bp_get_theme_package_id();

            if ( $theme_package_id === 'nouveau' ) {
                bp_register_template_stack( function() use( $nouveau_path ) {
                    return $nouveau_path;
                } );

            } elseif ( $theme_package_id === 'legacy' ) {
                bp_register_template_stack( function() use( $legacy_path ) {
                    return $legacy_path;
                } );
            }
        }
    }

    /**
     * Activate the plugin.
     *
     * @since 1.0.0
     */
    public function _activate() {
        // Bail early if requirements aren't met.
        if ( ! $this->check_requirements() ) {
            return;
        }

        // Make sure any rewrite functionality has been loaded.
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin.
     * Uninstall routines should be in uninstall.php.
     *
     * @since 1.0.0
     */
    public function _deactivate() {
        // Add deactivation cleanup functionality here.
    }

    /**
     * Init hooks before other plugins have initialised.
     *
     * Hooked onto 'init' at priority 0.
     *
     * @since 1.0.0
     */
    public function early_init() {
        // Bail early if requirements aren't met.
        if ( ! $this->check_requirements() ) {
            return;
        }

        // Load translated strings for plugin.
        load_plugin_textdomain( 'zl-smart-avatars', false, dirname( $this->basename ) . '/languages/' );

        // Perform maintenance
        $this->maybe_run_maintenance();
    }

    /**
     * Enqueue admin CSS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_styles() {
        wp_register_style(
            $this->_token . '-admin',
            esc_url( $this->url ) . 'assets/css/admin.min.css',
            [],
            self::VERSION
        );

        wp_enqueue_style( $this->_token . '-admin' );
    }

    /**
     * Is the Pro Version active.
     *
     * @return bool
     * @since 1.1.0
     */
    public function is_pro_active(): bool {
        $pro_enabled = false;

        if ( class_exists( 'Zipline\ZLSmartAvatarsPro\Main_Pro' ) ) {
            $pro_enabled = true;
        }

        return $pro_enabled;
    }

    /**
     * Enqueue admin JS.
     *
     * @since NEXT
     */
    public function enqueue_admin_scripts() {
        wp_register_script(
            $this->_token . '-admin',
            esc_url( $this->url ) . 'assets/js/admin.min.js',
            [],
            self::VERSION,
            true
        );

        $pro_enabled = $this->is_pro_active();

        wp_localize_script( $this->_token . '-admin', 'zl_smart_avatars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'zl_smart_avatars' ),
            'pro_enabled' => $pro_enabled,
        ) );

        wp_enqueue_script( $this->_token . '-admin' );
    }

    /**
     * Enqueue frontend CSS.
     *
     * @since 1.1.0
     */
    public function enqueue_frontend_styles() {
        wp_register_style(
            $this->_token . '-frontend',
            esc_url( $this->url ) . 'assets/css/frontend.min.css',
            [],
            self::VERSION
        );

        wp_enqueue_style( $this->_token . '-frontend' );
    }

    /**
     * Check if the plugin meets requirements and
     * disable it if they are not present.
     *
     * @since 1.0.0
     *
     * @return bool True if requirements met, false if not.
     */
    public function check_requirements(): bool {
        // Bail early if plugin meets requirements.
        if ( $this->meets_requirements() ) {
            return true;
        }

        // Add a dashboard notice.
        add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

        // Deactivate our plugin.
        add_action( 'admin_init', array( $this, 'deactivate_me' ) );

        // Didn't meet the requirements.
        return false;
    }

    /**
     * Deactivates this plugin, hook this function on admin_init.
     *
     * @since 1.0.0
     */
    public function deactivate_me() {
        // We do a check for deactivate_plugins before calling it, to protect
        // any developers from accidentally calling it too early and breaking things.
        if ( function_exists( 'deactivate_plugins' ) ) {
            deactivate_plugins( $this->basename );
        }
    }

    /**
     * Check that all plugin requirements are met.
     *
     * @since 1.0.0
     *
     * @return bool True if requirements are met.
     */
    public function meets_requirements(): bool {
        $valid = true;

         if ( ! function_exists( 'buddypress' ) ) {
             $this->activation_errors[] = __( 'BuddyPress is required.', 'zl-smart-avatars' );

             $valid = false;
         }

        return $valid;
    }

    /**
     * Adds a notice to the dashboard if the plugin requirements are not met.
     *
     * @since 1.0.0
     */
    public function requirements_not_met_notice() {
        // Compile default message.
        $default_message = sprintf(
            __(
                'Zipline Smart Avatars is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.',
                'zl-smart-avatars'
            ),
            admin_url( 'plugins.php' )
        );

        // Default details to null.
        $details = null;

        // Add details if any exist.
        if ( $this->activation_errors ) {
            $details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
        }

        // Output errors.
        ?>
        <div id="message" class="error">
            <p><?php echo wp_kses_post( $default_message ); ?></p>
            <?php echo wp_kses_post( $details ); ?>
        </div>
        <?php
    }

    /**
     * Magic getter for our object.
     *
     * @since 1.0.0
     *
     * @param string $field Field to get.
     * @return mixed         Value of the field.
     * @throws Exception    Throws an exception if the field is invalid.
     */
    public function __get( string $field ) {
        switch ( $field ) {
            case 'version':
                return self::VERSION;
            case 'basename':
            case 'url':
            case 'path':
                return $this->$field;
            default:
                throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
        }
    }

    /**
     * Check if any necessary maintenance tasks need to be run and execute them.
     *
     * @since 1.0.0
     */
    public function maybe_run_maintenance() {
        if ( ! is_admin() ) {
            return;
        }

        $maintenance_version = (int) get_option( $this->_token . '_maint_version' );

        if ( $maintenance_version < self::MAINTENANCE_VERSION ) {
            for ( $version = $maintenance_version + 1; $version <= self::MAINTENANCE_VERSION; $version++ ) {
                Maintenance::run( $version );
            }
        }

        update_option( $this->_token . '_maint_version', self::MAINTENANCE_VERSION );
    }

    /**
     * Removes the tool's menu from the redux options page.
     *
     * @return void
     * @since 1.0.0
     */
    public function remove_tools_submenu_item() {
        remove_submenu_page( 'tools.php', 'zipline-smart-avatar-tools' );
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
     * Init REST API.
     *
     * @since 1.1.0
     */
    public function api_init() {
        $this->rest_api = new REST_API();
        $this->rest_api->init_hooks();
        $this->rest_api->register_endpoints();
    }
}

// Kick it off.
add_action( 'plugins_loaded', [ Main::instance(), 'early_hooks' ], 4 );
add_action( 'plugins_loaded', [ Main::instance(), 'hooks' ] );

// Activation and deactivation.
register_activation_hook( __FILE__,   [ Main::instance(), '_activate' ] );
register_deactivation_hook( __FILE__, [ Main::instance(), '_deactivate' ] );

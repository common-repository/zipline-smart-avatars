<?php
namespace Zipline\ZLSmartAvatars\Features;

use Zipline\ZLSmartAvatars\Main;
use Zipline\ZLSmartAvatars\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( 'ZL_Default_Avatar' ) || ! class_exists( 'Main' ) ) {
    return;
}

/**
 * Assigns all users the same default avatar if main plugin option is off
 *
 * @since 1.0.0
 */
class ZL_Default_Avatar {
    /**
     * Init hooks.
     *
     * @since 1.0.0
     */
    public static function init() {
        add_filter( 'bp_core_default_avatar', [ self::class, 'set_default_avatar' ] );
    }

    /**
     * Gets the default avatar choice from the Redux options panel.
     *
     * @return string
     * @since 1.1.0
     */
    public static function get_default_avatar_choice(): string {
        $plugin = Main::instance();
        $basename    = 'Avatar';
        $source_of_default_avatar = Settings::get('source-of-default-avatar');

        if ( $source_of_default_avatar == "1" ) {
            $suffix      = '.png';
            $default_avatar_choice = $basename . Settings::get( 'choose-default-demo-avatar' ) . $suffix;
            $default_avatar_choice_url = $plugin->url . Main::AVATARS_PATH . Main::DEMO_AVATARS_DIR_NAME . '/' . $default_avatar_choice;

        } elseif ( $source_of_default_avatar === '2' ) {
            $suffix      = '.jpg';
            $default_avatar_choice = $basename . '0' . Settings::get( 'choose-default-nature-avatar' ) . $suffix;
            $default_avatar_choice_url = $plugin->url . Main::AVATARS_PATH . Main::NATURE_AVATARS_DIR_NAME . '/' . $default_avatar_choice;

        } else {
            $default_avatar_choice = Settings::get( 'choose-custom-default-avatar' );
            $default_avatar_choice_url = $default_avatar_choice[ 'url' ];

        }

        return $default_avatar_choice_url;
    }

    /**
     * Sets the BuddyPress default avatar to the one specified in the plugin settings.
     *
     * @return string
     *
     * @since 1.2.0
     */
    public static function set_default_avatar(): string {
        return self::get_default_avatar_choice();
    }
}

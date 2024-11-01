<?php
namespace Zipline\ZLSmartAvatars\Features;

use Zipline\ZLSmartAvatars\Main;
use Zipline\ZLSmartAvatars\Settings;
use Zipline\ZLSmartAvatars\ZL_Shared_Functions;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( 'ZL_Random_Avatar' ) || ! class_exists( 'Main' ) ) {
    return;
}

/**
 * Optionally assign users a random default avatar when activated.
 *
 * @since 1.0.0
 */
class ZL_Random_Avatar {
    /**
     * Init hooks.
     *
     * @since 1.0.0
     */
    public static function init() {
        add_action( 'bp_core_activated_user', [ ZL_Shared_Functions::class, 'assign' ], 10 );
    }

    /**
     * Get avatars location details.
     *
     * @since 1.0.0
     *
     * @return array{directory: string, url: string} {
     *     Directory path and URL.
     *
     *     @type string $directory Base directory path including trailing slash.
     *     @type string $url       Base URL including trailing slash.
     * }
     */
    public static function get_avatars_location(): array {
        $folder = '';

        if ( Settings::get( 'source-of-avatar-library' ) === '1' ) {
            $folder = 'demo-avatars';
        } elseif ( Settings::get( 'source-of-avatar-library' ) === '2' ) {
            $folder = 'nature';
        }

        $plugin = Main::instance();

        return [
            'directory' => $plugin->path . Main::AVATARS_PATH . $folder . '/',
            'url'       => $plugin->url . Main::AVATARS_PATH . $folder . '/',
        ];
    }
}

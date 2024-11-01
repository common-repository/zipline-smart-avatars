<?php
namespace Zipline\ZLSmartAvatars\Features;

use Zipline\ZLSmartAvatars\ZL_Shared_Functions;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ZL_Admin_Upload_Avatar {
    /**
     * Init hooks.
     *
     * @since NEXT
     */
    public static function init() {
        add_action( 'bp_core_activated_user', [ ZL_Shared_Functions::class, 'assign' ], 10 );
    }
}

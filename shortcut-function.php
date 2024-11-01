<?php
use Zipline\ZLSmartAvatars\Main;

/**
 * Grab the Main object and return it.
 * Wrapper for Main::instance().
 *
 * @since 1.0.0
 * @return Main Singleton instance of plugin class.
 */
function zl_smart_avatars(): Main {
    return Main::instance();
}

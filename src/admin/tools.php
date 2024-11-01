<?php
namespace Zipline\ZLSmartAvatars\Admin;

use Closure;
use WP_Error;

/**
 * Admin tools.
 *
 * @since 1.0.0
 */
class Admin_Tools {
    /**
     * Init hooks.
     *
     * @since 1.0.0
     */
    public static function init_hooks() {
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menus' ] );
    }

    /**
     * Add our admin menus.
     *
     * @since 1.0.0
     *
     */
    public static function add_admin_menus() {
        // Adds a submenu page to the tools main menu
        add_management_page(
            __( 'Zipline Smart Avatars', 'zl-smart-avatars' ),
            __( 'Zipline Smart Avatars', 'zl-smart-avatars' ),
            'manage_options',
            'zipline-smart-avatar-tools',
            [ __CLASS__, 'tools_screen' ]
        );
    }

    /**
     * Display tools screen
     *
     * @since 1.0.0
     */
    public static function tools_screen() {
    ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Smart Avatars Settings', 'zl-smart-avatars' ); ?></h1>

            <form class="settings" method="post" action="">

                <fieldset>
                    <legend><strong><?php esc_html_e( '', '' ) ?></strong></legend>

                    <p class="submit">
                        <input class="button-primary" type="submit" name="zl-smart-avatars-tools-submit"
                               value="<?php esc_attr_e( 'Submit', 'zl-smart-avatars' ); ?>" />

                        <?php wp_nonce_field( 'zna-tool' ); ?>
                    </p>
                </fieldset>

            </form>

        </div>

    <?php
    }

    /**
     * Display settings page.
     */
    public function display() {
        // Execute
        $token = zl_smart_avatars()->_token;
        $page  = filter_input( INPUT_GET, 'page' );

    }
}

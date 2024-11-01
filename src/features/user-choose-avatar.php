<?php
namespace Zipline\ZLSmartAvatars\Features;

use Zipline\ZLSmartAvatars\Main;
use Zipline\ZLSmartAvatars\ZL_Shared_Functions;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( 'User_Choose_Avatar' ) || ! class_exists( 'Main' ) ) {
    return;
}

/**
 * Allow users to change their avatar choice after registration.
 * Choosing from the library selected in the admin panel.
 *
 * @since 1.1.0
 */
class User_Choose_Avatar {
    /**
     * Init hooks.
     *
     * @since 1.1.0
     * @return void
     */
    public static function init() {
        add_action( 'wp_ajax_user_change_avatar', [ __CLASS__, 'ajax_set_users_avatar' ] );

        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_ajax' ] );
    }

    /**
     * Loop through the avatars in the folder and output each one inside a div.
     *
     * @return void
     * @since 1.1.0
     */
    public static function output_avatars() {
        $avatars = ZL_Shared_Functions::get_available_avatars();

        foreach ( $avatars as $id => $avatar ) {
            // Array will be zero indexed, add one so we can distinguish 1st selected item and no selected item
            echo '<div class="avatar-wrap"><img src="' . esc_url( $avatar['url'] ) . '"' .
                ' alt="default avatar" width="100" height="100" data-avatar-id="' . ( (int) $id + 1 ) . '" /></div>';
        }
    }

    /**
     * Add a button for the user to confirm their choice.
     *
     * @since 1.1.0
     * @return void
     */
    public static function output_avatar_choose_button() {
        ?>
        <form action="" method="post" class="ajax" enctype="multipart/form-data" id="zl-smart-avatars-choice">
            <input type="submit" name="user-choose-avatar-button" id="user-choose-avatar" value="<?php esc_attr_e( 'Change Profile Photo', 'zl-smart-avatars' ); ?>" />
        </form>
        <?php
    }

    /**
     * Output everything needed for the avatars to be displayed.
     *
     * @since 1.1.0
     * @return void
     */
    public static function output_avatars_container() {
        $message = esc_html_x(
            'Click one of the images below to select your profile photo and then "Change Profile Photo" to confirm your choice.',
            '',
            'zl-smart-avatars'
        );
     ?>

        <p><?php echo $message ? $message : 'Click one of the images below to select your profile photo and then "Change Profile Photo" to confirm your choice.'; ?></p>

        <div class="avatars-wrapper"><?php self::output_avatars(); ?></div>

        <?php
        self::output_avatar_choose_button();
    }

    /**
     * Enqueue the ajax script
     *
     * @since 1.1.0
     */
    public static function enqueue_ajax() {
        wp_enqueue_script(
            'zl-smart-avatars-ajax',
            Main::instance()->url . 'assets/js/frontend.min.js',
            array( 'jquery' ),
            Main::instance()->version,
            true
        );

        wp_localize_script(
            'zl-smart-avatars-ajax',
            'zl_smart_avatars_ajax_object',
            array (
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'zl-smart-avatars-ajax-nonce' ),
            )
        );
    }

    /**
     * Set the avatar the user has chosen.
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_set_users_avatar() {
        $nonce     = filter_input( INPUT_POST, 'nonce' );
        $avatar_id = (int) filter_input( INPUT_POST, 'avatar_id', FILTER_SANITIZE_NUMBER_INT );
        $avatar_url = filter_input( INPUT_POST, 'avatar_url', FILTER_SANITIZE_URL );
        $user_id   = get_current_user_id();
        $avatars   = ZL_Shared_Functions::get_available_avatars();
        $avatar_array = [];

        foreach ( $avatars as $avatar ) {
            if ( $avatar['url'] === $avatar_url ) {
                $avatar_array = $avatar;
            }
        }

        if ( ! wp_verify_nonce( $nonce, 'zl-smart-avatars-ajax-nonce' ) ) {
            wp_send_json_error( _x( 'Invalid nonce.', 'ajax error', 'zl-smart-avatars' ), 403 );
        }

        if ( ! $avatar_id ) {
            wp_send_json_error( _x( 'Invalid avatar identifier.', 'ajax error', 'zl-smart-avatars' ), 404 );
        }

        // Get avatar
        // One is added to avatar ID in template so that we can distinguish between first item and no selection
        $avatar = ZL_Shared_Functions::get_avatar_by_id( $avatar_id - 1 );

        if ( ! $avatar ) {
            wp_send_json_error( _x( 'Invalid avatar identifier.', 'ajax error', 'zl-smart-avatars' ), 404 );
        }

        ZL_Shared_Functions::set_users_avatar( $user_id, $avatar_array );

        $args = array(
            'item_id' => $user_id,
            'type'    => 'full',
            'html'    => false,
        );

        $new_avatar_url = bp_core_fetch_avatar( $args );

        wp_send_json_success( array(
            'avatar_url' => $new_avatar_url,
        ) );
        die();
    }
}


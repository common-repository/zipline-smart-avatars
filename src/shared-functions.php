<?php
namespace Zipline\ZLSmartAvatars;

use WP_Error;
use Zipline\ZLSmartAvatars\Features\ZL_Default_Avatar;
use Zipline\ZLSmartAvatars\Features\ZL_Random_Avatar;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * A class to store functionality used across classes
 *
 * @since 1.0.0
 */
class ZL_Shared_Functions {
    /**
     * Assign the avatar.
     *
     * @param int $user_id
     *
     * @since 1.0.0
     *
     */
    public static function assign( int $user_id ) {
        // Pick random avatar
        $avatars = ZL_Shared_Functions::get_available_avatars();

        if ( ! $avatars ) {
            return;
        }

        $avatar = $avatars[ array_rand( $avatars ) ];

        // Assign avatar
        ZL_Shared_Functions::set_users_avatar( $user_id, $avatar );

        /**
         * Fires when user has been assigned a random avatar.
         *
         * @since 1.0.0
         *
         * @param int   $user_id
         * @param array $avatar {
         *     The avatar that was assigned.
         *     @type string $path     File path for avatar.
         *     @type string $url      URL of avatar.
         *     @type string $filename Filename.
         * }
         */
        do_action_deprecated(
            'zlc_user_assigned_random_avatar',
            [ $user_id, $avatar ],
            '1.0.0',
            'zl_smart_avatars_user_assigned_avatar',
            __( 'Avatars functionality has been removed from the Zipline Community Plugin', 'zl-smart-avatars' )
        );
    }

    /**
     * Get available avatars.
     *
     * @since 1.0.0
     * @since NEXT Return URL of each avatar.
     *
     * @return array<string, array> {
     *     List of avatars keyed by identifier.
     *
     *     @type string $path     Full file path for avatar.
     *     @type string $url      URL of avatar.
     *     @type string $filename Filename, unless an attachment then 'attachment:<attachment-id>'
     * }
     */
    public static function get_available_avatars(): array {
        $avatars = [];

//        if ( Settings::get( 'smart-avatar' ) === '0' ) {
//            // Smart avatars is off, the only choice of avatar is the admin selected avatar
//            $location    = ZL_Default_Avatar::get_avatars_location();
//            $basename    = 'Avatar';
//            $suffix      = '.png';
//            $avatar_name = $basename . Settings::get( 'choose-default-avatar' ) . $suffix;
//
//            $avatars[] = [
//                'path'     => $location['directory'] . $avatar_name,
//                'url'      => $location['url'] . $avatar_name,
//                'filename' => $avatar_name,
//            ];
//
//            return $avatars;
//        }

        if ( Settings::get( 'smart-avatar' ) === '1' ) {
            // Assign a random admin uploaded avatar
            if ( Settings::get( 'source-of-avatar-library' ) === '3' ) {
                $admin_avatars_attachment_ids_str = Settings::get( 'create-gallery' );
                $admin_avatars_attachment_ids     = explode( ',', $admin_avatars_attachment_ids_str );

                foreach ( $admin_avatars_attachment_ids as $avatar_attachment_id ) {
                    $attachment_file     = get_attached_file( $avatar_attachment_id );
                    $attachment_url      = wp_get_attachment_url( $avatar_attachment_id );
                    $attachment_filename = pathinfo( $attachment_file, PATHINFO_BASENAME );

                    $avatars[] = [
                        'path'     => $attachment_file,
                        'url'      => $attachment_url,
                        'filename' => $attachment_filename,
                    ];
                }

                return $avatars;
            }

            // Assign a random avatar from the pre-installed bank that comes with the plugin
            $location = ZL_Random_Avatar::get_avatars_location();

            if ( ! $location ) {
                return [];
            }

            $filenames = ZL_Shared_Functions::get_filenames_in_directory( $location['directory'] );

            foreach ( $filenames as $filename ) {
                $avatars[] = [
                    'path'     => $location['directory'] . $filename,
                    'url'      => $location['url'] . $filename,
                    'filename' => $filename,
                ];
            }

            return $avatars;
        }

        return $avatars;
    }

    /**
     * Get an avatar by its identifier.
     *
     * @since NEXT
     *
     * @param int $id The identifier (which is really the array key).
     * @return array|null Returns array if found, otherwise null.
     */
    public static function get_avatar_by_id( int $id ): ?array {
        $avatars = self::get_available_avatars();

        return $avatars[ $id ] ?? null;
    }

    /**
     * Get names of files in directory with valid extension.
     *
     * @param string   $path             Directory path to check.
     * @param string[] $valid_extensions Valid filename extensions.
     *
     * @return string[] List of filenames.
     * @since 1.0.0
     *
     */
    public static function get_filenames_in_directory( string $path, array $valid_extensions = [ 'png', 'jpg', 'jpeg', 'gif' ] ): array {
        // Ensure valid directory
        if ( ! file_exists( $path ) || ! is_dir( $path ) ) {
            return [];
        }

        /**
         * Filters the valid file extensions for a default avatar.
         *
         * @since 1.0.0
         *
         * @param string[] $extensions
         */
        $valid_extensions = apply_filters( 'zl_default_avatar_file_extensions', $valid_extensions );

        // Get files in directory with valid extension
        $extensions = array_map( 'strtolower', $valid_extensions );
        $filenames  = [];

        $d = dir( $path );

        while ( false !== ( $entry = $d->read() ) ) {
            if ( $entry != '.' && $entry != '..' && $entry[0] != '.' ) {
                $extension = pathinfo( $path . $entry, PATHINFO_EXTENSION );

                if ( in_array( strtolower( $extension ), $extensions ) ) {
                    $filenames[] = $entry;
                }
            }
        }

        $d->close();

        return $filenames;
    }

    /**
     * Set avatar for user.
     *
     * @param int   $user_id
     * @param array $avatar {
     *    Details regarding avatar.
     *
     *     @type string $filename Filename.
     *     @type string $path     Full file path.
     * }
     * @return WP_Error|true
     * @since 1.0.0
     *
     */
    public static function set_users_avatar( int $user_id, array $avatar ) {
        if ( ! $user_id || ! $avatar['filename'] || ! $avatar['path'] ) {
            return new WP_Error(
                'invalid_parameters',
                _x( 'Invalid parameters.', 'error', 'zl-smart-avatars' ),
                [ 'status' => 500 ]
            );
        }

        // Copy avatar to user directory
        $item_id           = $user_id;
        $avatar_dir        = 'avatars';
        $object            = 'user';
        $avatar_dir        = apply_filters( 'bp_core_avatar_dir', $avatar_dir, $object );
        $avatar_folder_dir = apply_filters(
            'bp_core_avatar_folder_dir',
            bp_core_avatar_upload_path() . '/' . $avatar_dir . '/' . $item_id, $item_id, $object, $avatar_dir
        );
        $avatar_filename   = pathinfo( $avatar['path'], PATHINFO_BASENAME );
        $avatar_path       = $avatar_folder_dir . '/' . $avatar_filename;

        if ( file_exists( $avatar_path ) && is_file( $avatar_path ) ) {
            if ( ! unlink( $avatar_path ) ) {
                return new WP_Error(
                    'delete_avatar_failed',
                    __( 'Unable to delete existing avatar.', 'zl-smart-avatars' ),
                    [ 'status' => 500 ]
                );
            }
        }

        if ( ! file_exists( $avatar_folder_dir ) ) {
            if ( ! mkdir( $avatar_folder_dir, 0777, true ) ) {
                return new WP_Error(
                    'create_avatar_dir_failed',
                    __( 'Unable to create avatar directory.', 'zl-smart-avatars' ),
                    [ 'status' => 500 ]
                );
            }
        }

        if ( ! copy( $avatar['path'], $avatar_path ) ) {
            return new WP_Error(
                'copy_avatar_failed',
                __( 'Unable to copy avatar into place.', 'zl-smart-avatars' ),
                [ 'status' => 500 ]
            );
        }

        // Crop args.
        $data = getimagesize( $avatar_path );

        $r = array(
            'item_id'       => $item_id,
            'original_file' => '/' . $avatar_dir . '/' . $item_id . '/' . $avatar_filename,
            'crop_w'        => $data[0],
            'crop_h'        => $data[1],
        );

        add_filter( 'bp_attachments_current_user_can', [ __CLASS__, 'allow_avatar_crop' ], 10, 3 );

        $result = bp_core_avatar_handle_crop( $r );

        remove_filter( 'bp_attachments_current_user_can', [ __CLASS__, 'allow_avatar_crop' ] );

        if ( ! $result ) {
            return new WP_Error(
                'crop_avatar_failed',
                __( 'BuddyPress was unable to crop avatar.', 'zl-smart-avatars' ),
                [ 'status' => 500 ]
            );
        }

        return true;
    }

    /**
     * Allow non-logged in user to crop their avatar (during activation).
     *
     * @param bool   $can
     * @param string $capability
     *
     * @return bool
     * @since 1.0.0
     *
     */
    public static function allow_avatar_crop( bool $can, string $capability ): bool {
        if ( $capability !== 'edit_avatar' ) {
            return $can;
        }

        return true;
    }
}

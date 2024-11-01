<?php
namespace Zipline\ZLSmartAvatars;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API.
 *
 * Provides API endpoints for getting available avatars, and setting user avatar to an available avatar.
 *
 * @since NEXT
 */
class REST_API {
    /**
     * REST API namespace.
     *
     * @since NEXT
     * @const string
     */
    const NAMESPACE = 'zl-smart-avatars';

    /**
     * Init hooks.
     *
     * @since NEXT
     */
    public function init_hooks() {
        if ( Settings::get( 'smart-avatar' ) === '1' && Settings::get( 'user-choose-avatar' ) === '1' ) {
            // Enable avatar selection in app
            add_filter( 'zna_bp_app_settings', [ $this, 'enable_avatar_selection' ] );
        }
    }

    /**
     * Adds 'default_avatar_selection' feature flag to ZippApp BP settings.
     *
     * @since NEXT
     *
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function enable_avatar_selection( array $settings ): array {
        if ( ! isset( $settings['features'] ) ) {
            $settings['features'] = [];
        }

        $settings['features']['default_avatar_selection'] = true;

        return $settings;
    }

    /**
     * Get namespace and version.
     *
     * @param string $version API version.
     * @return string Returns namespace and version.
     */
    public function get_versioned_namespace( string $version = 'v1' ): string {
        return self::NAMESPACE . '/' . $version;
    }

    /**
     * Register endpoints.
     *
     * @since NEXT
     */
    public function register_endpoints() {
        $namespace = $this->get_versioned_namespace();

        register_rest_route( $namespace, '/avatars', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_avatars' ],
                'permission_callback' => '__return_true',
            ],
            'schema' => [ $this, 'get_avatars_schema' ],
        ] );

        register_rest_route( $namespace, '/set', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'set_avatar' ],
                'permission_callback' => [ $this, 'set_avatar_permissions_check' ],
                'args'                => [
                    'id' => [
                        'description'       => _x( 'Avatar identifier.', 'rest api argument', 'zl-smart-avatars' ),
                        'type'              => 'integer',
                        'required'          => true,
                        'validate_callback' => [ $this, 'validate_avatar_id' ],
                    ],
                ],
            ],
            'schema' => [ $this, 'get_set_avatar_schema' ],
        ] );
    }

    /**
     * Get list of avatars.
     *
     * @since NEXT
     *
     * @param WP_REST_Request $request
     * @return WP_Rest_Response|WP_Error
     * @noinspection PhpUnusedParameterInspection
     */
    public static function get_avatars( WP_REST_Request $request ) {
        $avatars = ZL_Shared_Functions::get_available_avatars();
        $result  = [];

        foreach ( $avatars as $id => $avatar ) {
            $result[] = [
                'id'  => $id + 1,
                'url' => $avatar['url'],
            ];
        }

        return rest_ensure_response( $result );
    }

    /**
     * Check if a given request has access to set avatar.
     *
     * @since NEXT
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     * @noinspection PhpUnusedParameterInspection
     */
    public static function set_avatar_permissions_check( WP_REST_Request $request ) {
        $retval = true;

        if ( ! is_user_logged_in() ) {
            $retval = new WP_Error(
                'rest_authorization_required',
                _x( 'Sorry, you need to be logged in to perform this action.', 'rest api error', 'zl-smart-avatars' ),
                [ 'status' => rest_authorization_required_code() ]
            );
        }

        if (
            $retval === true &&
            ! bp_attachments_current_user_can( 'edit_avatar', [
                'object'        => 'user',
                'avatar_dir'    => 'avatars',
                'item_id'       => get_current_user_id(),
                'original_file' => false,
            ] )
        ) {
            $retval = new WP_Error(
                'rest_no_permission',
                _x( 'Sorry, you do not have permission to perform this action.', 'rest api error', 'zl-smart-avatars' ),
                [ 'status' => 403 ]
            );
        }

        return $retval;
    }

    /**
     * Validate avatar identifier request argument.
     *
     * @since NEXT
     *
     * @param  mixed            $value      Avatar identifier.
     * @param  WP_REST_Request  $request    REST request.
     * @param  string           $param_name Name of parameter.
     * @return true|WP_Error
     * @noinspection PhpUnusedParameterInspection
     */
    public static function validate_avatar_id( $value, WP_REST_Request $request, string $param_name ) {
        if ( ! ZL_Shared_Functions::get_avatar_by_id( $value - 1 ) ) {
            return new WP_Error(
                'rest_invalid_avatar_id',
                _x( 'Invalid avatar identifier.', 'rest api error', 'zl-smart-avatars' ),
                [ 'status' => 400 ]
            );
        }

        return true;
    }

    /**
     * Set users avatar.
     *
     * @since NEXT
     *
     * @param  WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     */
    public static function set_avatar( WP_REST_Request $request ) {
        $avatar_id = $request->get_param( 'id' );
        $avatar    = ZL_Shared_Functions::get_avatar_by_id( $avatar_id - 1 );

        if ( ! $avatar ) {
            return new WP_Error(
                'rest_invalid_avatar_id',
                _x( 'Invalid avatar identifier.', 'rest api error', 'zl-smart-avatars' ),
                [ 'status' => 400 ]
            );
        }

        $user_id = get_current_user_id();
        $result  = ZL_Shared_Functions::set_users_avatar( $user_id, $avatar );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Get full URL
        $url = bp_core_fetch_avatar( [
            'item_id' => $user_id,
            'type'    => 'full',
            'html'    => false,
        ] );

        // Get thumbnail URL
        $thumbnail_url = bp_core_fetch_avatar( [
            'item_id' => $user_id,
            'type'    => 'thumb',
            'html'    => false,
        ] );

        return rest_ensure_response( [
            'url'           => $url,
            'thumbnail_url' => $thumbnail_url,
        ] );
    }

    /**
     * Builds the avatars response schema.
     *
     * @since NEXT
     *
     * @return array
     */
    public static function get_avatars_schema(): array {
        return [
            'description' => _x( 'Avatars schema.', 'rest api desc', 'zl-smart-avatars' ),
            'type'        => 'array',
            'context'     => [ 'view', 'edit' ],
            'items'       => [
                'type'       => 'object',
                'properties' => [
                    'id'  => [
                        'context'     => [ 'view', 'edit' ],
                        'description' => _x( 'Avatar identifier.', 'rest api desc', 'zl-smart-avatars' ),
                        'type'        => 'int',
                        'readonly'    => true,
                    ],
                    'url' => [
                        'context'     => [ 'view', 'edit' ],
                        'description' => _x( 'Avatar URL.', 'rest api desc', 'zl-smart-avatars' ),
                        'type'        => 'string',
                        'readonly'    => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the response schema for setting avatar.
     *
     * @since NEXT
     *
     * @return array
     */
    public static function get_set_avatar_schema(): array {
        return [
            'description' => _x( 'Avatar schema.', 'rest api desc', 'zl-smart-avatars' ),
            'type'        => 'object',
            'context'     => [ 'view', 'edit' ],
            'properties'  => [
                'url'           => [
                    'context'     => [ 'view', 'edit' ],
                    'description' => _x( 'Avatar URL.', 'rest api desc', 'zl-smart-avatars' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ],
                'thumbnail_url' => [
                    'context'     => [ 'view', 'edit' ],
                    'description' => _x( 'Avatar thumbnail URL.', 'rest api desc', 'zl-smart-avatars' ),
                    'type'        => 'string',
                    'readonly'    => true,
                ],
            ],
        ];
    }
}

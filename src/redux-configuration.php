<?php
namespace Zipline\ZLSmartAvatars;

use Redux;
use Zipline\ZLSmartAvatars\Admin\Admin_Settings;
use Zipline\ZLSmartAvatars\Admin\Redux\Utilities;

/**
 * Redux framework configuration.
 *
 * @since 1.0.0
 */
class Redux_Configuration {
    /**
     * @since 1.0.0
     */
    public static function init() {
        if ( ! class_exists( '\Redux' ) ) {
            return;
        }

        $menu_icon = '';

        $args = [
            // TYPICAL -> Change these values as you need/desire
            'opt_name'             => Main::REDUX_OPTIONS_NAME,
            // This is where your data is stored in the database and also becomes your global variable name.
            'display_name'         => __( 'Smart Avatars', 'zl-smart-avatars' ),
            // Name that appears at the top of your panel
            'display_version'      => Main::VERSION,
            // Version that appears at the top of your panel
            'menu_type'            => 'submenu',
            //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
            'allow_sub_menu'       => true,
            // Show the sections below the admin menu item or not
            'menu_title'           => __( 'Smart Avatars', 'zl-smart-avatars' ),
            'page_title'           => __( 'Smart Avatars', 'zl-smart-avatars' ),
            // You will need to generate a Google API key to use this feature.
            // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
            'google_api_key'       => '',
            // Set it you want google fonts to update weekly. A google_api_key value is required.
            'google_update_weekly' => false,
            // Must be defined to add google fonts to the typography module
            'async_typography'     => true,
            // Use an asynchronous font on the front end or font string
            //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own Google fonts loader
            'admin_bar'            => false,
            // Show the panel pages on the admin bar
            'admin_bar_icon'       => $menu_icon,
            // Choose an icon for the admin bar menu
            'admin_bar_priority'   => 50,
            // Choose a priority for the admin bar menu
            'global_variable'      => '',
            // Set a different name for your global variable other than the opt_name
            'dev_mode'             => defined( 'zl_smart_avatars_REDUX_DEV_MODE' ) && zl_smart_avatars_REDUX_DEV_MODE,
            // Show the time the page took to load, etc
            'update_notice'        => true,
            // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
            'customizer'           => false,
            // Enable basic customizer support
            //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
            //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

            // OPTIONAL -> Give you extra features
            'page_priority'        => null,
            // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
            'page_parent'          => 'options-general.php',
            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
            'page_permissions'     => 'manage_options',
            // Permissions needed to access the options panel.
            'menu_icon'            => $menu_icon,
            // Specify a custom URL to an icon
            'last_tab'             => '',
            // Force your panel to always open to a specific tab (by id)
            'page_icon'            => $menu_icon,
            // Icon displayed in the admin panel next to your menu_title
            'page_slug'            => Admin_Settings::PAGE_SLUG,
            // Page slug used to denote the panel
            'save_defaults'        => true,
            // On load save the defaults to DB before user clicks save or not
            'default_show'         => false,
            // If true, shows the default value next to each field that is not the default value.
            'default_mark'         => '',
            // What to print by the field's title if the value shown is default. Suggested: *
            'show_import_export'   => true,
            // Shows the Import/Export panel when not used as a field.

            // CAREFUL -> These options are for advanced use only
            'transient_time'       => 60 * MINUTE_IN_SECONDS,
            'output'               => true,
            // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
            'output_tag'           => false,
            // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
            'footer_credit'        => ' ',
            // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
            'database'             => '',
            // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!

            'use_cdn' => true,
            // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.

            //'compiler'             => true,

            // HINTS
            'hints'   => [
                'icon'          => 'el el-question-sign',
                'icon_position' => 'right',
                'icon_color'    => 'lightgray',
                'icon_size'     => 'normal',
                'tip_style'     => [
                    'color'   => 'light',
                    'shadow'  => true,
                    'rounded' => false,
                    'style'   => '',
                ],
                'tip_position'  => [
                    'my' => 'top left',
                    'at' => 'bottom right',
                ],
                'tip_effect'    => [
                    'show' => [
                        'effect'   => '',
                        'duration' => '500',
                        'event'    => 'mouseover',
                    ],
                    'hide' => [
                        'effect'   => 'fade',
                        'duration' => '250',
                        'event'    => 'click mouseleave',
                    ],
                ],
            ],
        ];

        Utilities::set_args( $args );

        // Remove unwanted section created by 'custom_fonts' extension
        add_filter(
            'redux/options/' . Main::REDUX_OPTIONS_NAME . '/section/redux_dynamic_font_control',
            [ self::class, 'remove_unwanted_custom_font_section' ],
            11,
            0
        );

        self::avatars_setup();
        self::cover_images_setup();
        self::group_avatars_setup();
        self::group_cover_images_setup();
    }

    /**
     * Configure options for randomly assigning avatars
     *
     * @since 1.0.0
     */
    protected static function avatars_setup() {
        /**
         * Output for the RAW field to display the jodie's bank of avatars.
         *
         * @since 1.0.0
         */
        ob_start( );

        $avatars_path = 'assets/images/avatars/';
        $folder = 'demo-avatars/';
        $path = plugin_dir_path( __DIR__ ) . $avatars_path . $folder;
        $files = scandir( $path );

        foreach ( $files as $file ) {
            if ( $file === '.' || $file === '..' ) {
                unset( $file );
            } else {
                echo '<div class="avatar-wrap"><img src="' .
                     plugin_dir_url( __DIR__ ) . $avatars_path . $folder . $file .
                '" alt="default avatar" width="100" height="100" ></div>';
            }
        }

        $output_theme_one = ob_get_clean();

        /**
         * Output for the RAW field to display the nature bank of avatars.
         *
         * @since 1.0.0
         */
        ob_start( );

        $path = plugin_dir_path( __DIR__ ) . $avatars_path . 'nature/';
        $files = scandir( $path );

        foreach ( $files as $file ) {
            if ( $file === '.' || $file === '..' ) {
                unset( $file );
            } else {
                echo '<div class="avatar-wrap"><img src="' .
                     plugin_dir_url( __DIR__ ) . $avatars_path . 'nature/' . $file .
                '" alt="default avatar" width="100" height="100" ></div>';
            }
        }

        $output_theme_two = ob_get_clean();

        /**
         * Setting up the fields for our admin panel
         *
         * @since 1.0.0
         */
        Redux::set_section( Main::REDUX_OPTIONS_NAME, [
            'title'    => esc_html__( 'Avatars', 'zl-smart-avatars' ),
            'heading'  => esc_html__( 'Avatar Assignment', 'zl-smart-avatars' ),
            'id'       => 'avatars_setup',
            'icon'     => 'el el-user',
            'fields'   => array(
                [
                    'id'           => 'choose-default-demo-avatar',
                    'type'         => 'image_select',
                    'title'        => esc_html__( 'Default Avatar', 'zl-smart-avatars' ),
                    'subtitle'     => esc_html__( 'This avatar will be assigned to every new user and any added through the admin panel.', 'zipline-smart-avatars' ),
                    'desc'         => esc_html__( "For a better looking community, enable 'Random Avatars'.", 'zipline-smart-avatars' ),
                    'options'      => [
                        '1' => [
                            'alt' => 'First default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar1.png",
                        ],
                        '2' => [
                            'alt' => 'Second default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar2.png"
                        ],
                        '3' => [
                            'alt' => 'Third default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar3.png"
                        ],
                        '4' => [
                            'alt' => 'Fourth default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar4.png"
                        ],
                        '5' => [
                            'alt' => 'Fifth default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar5.png",
                        ],
                        '6' => [
                            'alt' => 'Sixth default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar6.png"
                        ],
                        '7' => [
                            'alt' => 'Seventh default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar7.png"
                        ],
                        '8' => [
                            'alt' => 'Eighth default demo avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar8.png"
                        ],
                    ],
                    'default'  => '1',
                    'height'   => '100',
                    'width'    => '100',
                    'required' => [
                        'source-of-default-avatar',
                        'equals',
                        '1'
                    ],
                ],[
                    'id'           => 'choose-default-nature-avatar',
                    'type'         => 'image_select',
                    'title'        => esc_html__( 'Default Avatar', 'zl-smart-avatars' ),
                    'subtitle'     => esc_html__( 'This avatar will be assigned to every new user and any added through the admin panel.', 'zipline-smart-avatars' ),
                    'desc'         => esc_html__( "For a better looking community, enable 'Random Avatars'.", 'zipline-smart-avatars' ),
                    'options'      => [
                        '1' => [
                            'alt' => 'First default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar01.jpg",
                        ],
                        '2' => [
                            'alt' => 'Second default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar02.jpg"
                        ],
                        '3' => [
                            'alt' => 'Third default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar03.jpg"
                        ],
                        '4' => [
                            'alt' => 'Fourth default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar04.jpg"
                        ],
                        '5' => [
                            'alt' => 'Fifth default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar05.jpg",
                        ],
                        '6' => [
                            'alt' => 'Sixth default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar06.jpg"
                        ],
                        '7' => [
                            'alt' => 'Seventh default nature avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "nature/Avatar07.jpg"
                        ]
                    ],
                    'default'  => '1',
                    'height'   => '100',
                    'width'    => '100',
                    'required' => [
                        'source-of-default-avatar',
                        'equals',
                        '2'
                    ],
                ],
                [
                    'id'         => 'choose-custom-default-avatar',
                    'type'       => 'media',
                    'title'    => esc_html__( 'Default Avatar', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Upload an avatar image to be set as a default when users are added.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'We recommend uploading in JPEG, PNG or GIF format. File size should be around 80px by 80px.', 'zl-smart-avatars' ),
                    'required'   => array(
                        'source-of-default-avatar',
                        'equals',
                        '3'
                    )
                ],
                [
                    'id'       => 'source-of-default-avatar',
                    'type'     => 'radio',
                    'title'    => esc_html__( 'Default Avatar Library', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Users will be assigned a default avatar of your choosing.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( "Choose from one of the inbuilt banks or upload your own.", 'zipline-smart-avatars' ),
                    'options'  => array(
                        '1' => 'Colours',
                        '2' => 'Nature',
                        '3' => 'Custom',
                    ),
                    'default'  => '1'
                ],
                [
                    'id'       => 'smart-avatar',
                    'type'     => 'switch',
                    'title'    => esc_html__('Random Avatar', 'zl-smart-avatars'),
                    'subtitle' => esc_html__('Assign a random avatar from a library of images.', 'zipline-smart-avatars'),
                    'default'  => false,
                ],
                [
                    'id'       => 'source-of-avatar-library',
                    'type'     => 'radio',
                    'title'    => esc_html__( 'Avatar Library', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Users will be assigned a random avatar from the library.', 'zl-smart-avatars' ),
                    'options'  => array(
                        '1' => 'Colours',
                        '2' => 'Nature',
                        '3' => 'Custom',
                    ),
                    'default'  => '1',
                    'required' => array(
                        'smart-avatar',
                        'equals',
                        true
                    )
                ],
                [
                    'id'         => 'display-theme-one',
                    'type'       => 'raw',
                    'title'      => 'Colours Library',
                    'content'    => $output_theme_one,
                    'full_width' => 'false',
                    'required'   => array(
                        'source-of-avatar-library',
                        'equals',
                        '1'
                    )
                ],
                [
                    'id'         => 'display-theme-two',
                    'type'       => 'raw',
                    'title'      => 'Nature Library',
                    'content'    => $output_theme_two,
                    'full_width' => 'false',
                    'required'   => array(
                        'source-of-avatar-library',
                        'equals',
                        '2'
                    )
                ],
                [
                    'id'       => 'create-gallery',
                    'type'     => 'gallery',
                    'title'    => esc_html__( 'Custom Library', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Avatars will be randomly assigned so you may wish to make them gender neutral.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'We recommend uploading a minimum of 6 images in JPEG, PNG or GIF format. File size should be around 80px by 80px. To delete an avatar from the gallery click "add/edit gallery" button.', 'zl-smart-avatars' ),
                    'required' => array(
                        'source-of-avatar-library',
                        'equals',
                        '3'
                    )
                ],
                [
                    'id'       => 'user-choose-avatar',
                    'type'     => 'switch',
                    'title'    => esc_html__( 'User Chooses Avatar', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Allow users to change their avatar after registration.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'The user will be able to choose an avatar from the library selected below.', 'zl-smart-avatars' ),
                    'default'  => false,
                    'required' => [
                        'smart-avatar',
                        'equals',
                        true
                    ],
                ],
                [
                    'id'       => 'user-uploads-avatar',
                    'type'     => 'switch',
                    'title'    => esc_html__( 'User Can Upload an Avatar', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Allow users to upload an avatar after registration.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'The user will be able to upload an avatar of their choice after they have completed registration.', 'zl-smart-avatars' ),
                    'default'  => false,
                    'required' => [
                        'user-choose-avatar',
                        'equals',
                        true
                    ],
                ],
            )
        ]);
    }

    /**
     * Configure options for assigning cover images
     *
     * @since 1.2.0
     */
    protected static function cover_images_setup() {
        /**
         * Setting up the fields for our admin panel
         *
         * @since 1.2.0
         */
        Redux::set_section( Main::REDUX_OPTIONS_NAME, [
            'title'    => esc_html__( 'Cover Images', 'zl-smart-avatars' ),
            'heading'  => esc_html__( 'Cover Image Assignment', 'zl-smart-avatars' ),
            'id'       => 'cover_images_setup',
            'icon'     => 'el el-photo',
            'fields'   => array(
                [
                    'id'       => 'smart-cover-image',
                    'type'     => 'switch',
                    'title'    => esc_html__('Default User Cover Image', 'zl-smart-avatars'),
                    'subtitle' => esc_html__('Assign a cover image from a library of images.', 'zipline-smart-avatars'),
                    'default'  => false,
                ],
                [
                    'id'       => 'source-of-cover-image-library',
                    'type'     => 'radio',
                    'title'    => esc_html__( 'Cover Image Library', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Users will be assigned a cover image from this library.', 'zl-smart-avatars' ),
                    'options'  => array(
                        '1' => 'Space',
                        '2' => 'Custom',
                    ),
                    'default'  => '1',
                    'required' => array(
                        'smart-cover-image',
                        'equals',
                        true
                    )
                ],
                [
                    'id'           => 'choose-default-cover-image',
                    'type'         => 'image_select',
                    'title'        => esc_html__( 'Default Cover Image', 'zl-smart-avatars' ),
                    'subtitle'     => esc_html__( 'This cover image will be assigned to every new user.', 'zipline-smart-avatars' ),
                    'desc'         => esc_html__( "For a better looking community, enable 'Random Cover Images'.", 'zipline-smart-avatars' ),
                    'options'      => [
                        '1' => [
                            'alt' => 'First default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image1.jpg",
                        ],
                        '2' => [
                            'alt' => 'Second default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image2.jpg"
                        ],
                        '3' => [
                            'alt' => 'Third default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image3.jpg"
                        ],
                        '4' => [
                            'alt' => 'Fourth default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image4.jpg"
                        ],
                        '5' => [
                            'alt' => 'Fifth default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image5.jpg"
                        ],
                        '6' => [
                            'alt' => 'Sixth default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image6.jpg"
                        ],
                        '7' => [
                            'alt' => 'Seventh default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image7.jpg"
                        ],
                        '8' => [
                            'alt' => 'Eighth default cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image8.jpg"
                        ],
                    ],
                    'default'  => '1',
                    'height'   => '100',
                    'width'    => '100',
                    'required' => [
                        'source-of-cover-image-library', 'equals', '1'
                    ],
                ],
                [
                    'id'         => 'cover-image-uploader',
                    'type'       => 'media',
                    'title'    => esc_html__( 'Custom Cover Image', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Upload a cover image of your choosing to assign to you users at registration.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'We recommend uploading images in JPEG, PNG or GIF format. File size should be around 1800px by 500px.', 'zl-smart-avatars' ),
                    'required'   => array(
                        'source-of-cover-image-library',
                        'equals',
                        '2'
                    )
                ],
            )
        ]);
    }

    /**
     * Configure options for assigning group avatars
     *
     * @since 1.2.0
     */
    protected static function group_avatars_setup() {
        /**
         * Setting up the fields for our admin panel
         *
         * @since 1.1.0
         */
        Redux::set_section( Main::REDUX_OPTIONS_NAME, [
            'title'    => esc_html__( 'Group Avatars', 'zl-smart-avatars' ),
            'heading'  => esc_html__( 'Group Avatar Assignment', 'zl-smart-avatars' ),
            'id'       => 'group_avatars_setup',
            'icon'     => 'el el-group',
            'fields'   => array(
                [
                    'id'       => 'smart-group-avatar',
                    'type'     => 'switch',
                    'title'    => esc_html__('Default Group Avatar', 'zl-smart-avatars'),
                    'subtitle' => esc_html__('Assign a group avatar from a library of images.', 'zipline-smart-avatars'),
                    'default'  => false,
                ],
                [
                    'id'       => 'source-of-group-avatar-library',
                    'type'     => 'radio',
                    'title'    => esc_html__( 'Group Avatar Library', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Groups will be assigned a default avatar from this library, this can be changed during group creation.', 'zl-smart-avatars' ),
                    'options'  => array(
                        '1' => 'Demo',
                        '2' => 'Custom',
                    ),
                    'default'  => '1',
                    'required' => array(
                        'smart-group-avatar',
                        'equals',
                        true
                    )
                ],
                [
                    'id'           => 'choose-default-group-avatar',
                    'type'         => 'image_select',
                    'title'        => esc_html__( 'Default Group Avatar', 'zl-smart-avatars' ),
                    'subtitle'     => esc_html__( 'This group avatar will be assigned to every new user.', 'zipline-smart-avatars' ),
                    'desc'         => esc_html__( "For a better looking community, enable 'Random Cover Images'.", 'zipline-smart-avatars' ),
                    'options'      => [
                        '1' => [
                            'alt' => 'First default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar1.png",
                        ],
                        '2' => [
                            'alt' => 'Second default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar2.png",
                        ],
                        '3' => [
                            'alt' => 'Third default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar3.png",
                        ],
                        '4' => [
                            'alt' => 'Fourth default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar4.png",
                        ],
                        '5' => [
                            'alt' => 'Fifth default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar5.png",
                        ],
                        '6' => [
                            'alt' => 'Sixth default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar6.png",
                        ],
                        '7' => [
                            'alt' => 'Seventh default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar7.png",
                        ],
                        '8' => [
                            'alt' => 'Eighth default group avatar choice',
                            'img' => Main::instance()->url . Main::AVATARS_PATH . "demo-avatars/Avatar8.png",
                        ],
                    ],
                    'default'  => '1',
                    'height'   => '100',
                    'width'    => '100',
                    'required' => [
                        'source-of-group-avatar-library', 'equals', '1'
                    ],
                ],
                [
                    'id'         => 'group-avatar-image-uploader',
                    'type'       => 'media',
                    'title'    => esc_html__( 'Custom Group Avatar Image', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Upload a group avatar image to be set as a default when groups are being created.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'We recommend uploading in JPEG, PNG or GIF format. File size should be around 80px by 80px.', 'zl-smart-avatars' ),
                    'required'   => array(
                        'source-of-group-avatar-library',
                        'equals',
                        '2'
                    )
                ],
            )
        ]);
    }

    /**
     * Configure options for assigning group cover images.
     *
     * @since 1.2.0
     */
    protected static function group_cover_images_setup() {
        /**
         * Setting up the fields for our admin panel
         *
         * @since 1.1.0
         */
        Redux::set_section( Main::REDUX_OPTIONS_NAME, [
            'title'    => esc_html__( 'Group Cover Images', 'zl-smart-avatars' ),
            'heading'  => esc_html__( 'Group Cover Image Assignment', 'zl-smart-avatars' ),
            'id'       => 'group_cover_images_setup',
            'icon'     => 'el el-photo-alt',
            'fields'   => array(
                [
                    'id'       => 'smart-group-cover-image',
                    'type'     => 'switch',
                    'title'    => esc_html__('Default Group Cover Image', 'zl-smart-avatars'),
                    'subtitle' => esc_html__('Assign a group cover image from a library of images.', 'zipline-smart-avatars'),
                    'default'  => false,
                ],
                [
                    'id'       => 'source-of-group-cover-image-library',
                    'type'     => 'radio',
                    'title'    => esc_html__( 'Group Cover Image Library', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Groups will be assigned a cover image from the library, this can be changed during group creation.', 'zl-smart-avatars' ),
                    'options'  => array(
                        '1' => 'Space',
                        '2' => 'Custom',
                    ),
                    'default'  => '1',
                    'required' => array(
                        'smart-group-cover-image',
                        'equals',
                        true
                    )
                ],
                [
                    'id'           => 'choose-default-group-cover-image',
                    'type'         => 'image_select',
                    'title'        => esc_html__( 'Default Group Cover Image', 'zl-smart-avatars' ),
                    'subtitle'     => esc_html__( 'This group cover image will be assigned to every new group.', 'zipline-smart-avatars' ),
                    'options'      => [
                        '1' => [
                            'alt' => 'First default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image1.jpg",
                        ],
                        '2' => [
                            'alt' => 'Second default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image2.jpg",
                        ],
                        '3' => [
                            'alt' => 'Third default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image3.jpg",
                        ],
                        '4' => [
                            'alt' => 'Fourth default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image4.jpg",
                        ],
                        '5' => [
                            'alt' => 'Fifth default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image5.jpg",
                        ],
                        '6' => [
                            'alt' => 'Sixth default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image6.jpg",
                        ],
                        '7' => [
                            'alt' => 'Seventh default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image7.jpg",
                        ],
                        '8' => [
                            'alt' => 'Eighth default group cover image choice',
                            'img' => Main::instance()->url . Main::COVER_IMAGES_PATH . "cover-image8.jpg",
                        ],
                    ],
                    'default'  => '1',
                    'height'   => '100',
                    'width'    => '100',
                    'required'   => array(
                        'source-of-group-cover-image-library',
                        'equals',
                        '1'
                    )
                ],
                [
                    'id'         => 'group-cover-image-uploader',
                    'type'       => 'media',
                    'title'    => esc_html__( 'Custom Group Cover Image', 'zl-smart-avatars' ),
                    'subtitle' => esc_html__( 'Upload a group cover image to be set as a default when groups are being created.', 'zl-smart-avatars' ),
                    'desc'     => esc_html__( 'We recommend uploading in JPEG, PNG or GIF format. File size should be around 1800px by 500px.', 'zl-smart-avatars' ),
                    'required'   => array(
                        'source-of-group-cover-image-library',
                        'equals',
                        '2'
                    )
                ],
            )
        ]);
    }

    /**
     * Remove unwanted section created by 'custom_fonts' extensions.
     *
     * Redux loads all extensions found in the extensions' directory, some such as the custom fonts' extension create
     * a section we don't want to display to the user, remove it.
     *
     * @since NEXT
     */
    public static function remove_unwanted_custom_font_section(): array {
        return [];
    }

    /**
     * Add a button for the user to confirm their choice.
     *
     * @since 1.2.0
     * @return void
     */
    public static function output_choose_button() {
        ?>
        <form action="" method="post" class="ajax" enctype="multipart/form-data" id="zl-smart-avatars-choice-btn">
            <input type="submit" name="user-choose-image-button" id="user-choose-image" class="button button-primary" value="<?php esc_attr_e( 'Choose Image', 'zl-smart-avatars' ); ?>" />
        </form>
        <?php
    }
}

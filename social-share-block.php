<?php

    /**
     * Plugin Name:     Social Share Block
     * Description:     Share your posts & pages instantly on popular social platforms in one click from your website.
     * Version:         2.0.3
     * Author:          WPDeveloper
     * Author URI:         https://wpdeveloper.net
     * License:         GPL-3.0-or-later
     * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
     * Text Domain:     social-share-block
     *
     * @package         social-share-block
     */
    /**
     * Registers all block assets so that they can be enqueued through the block editor
     * in the corresponding context.
     *
     * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
     */

    require_once __DIR__ . '/includes/font-loader.php';
    require_once __DIR__ . '/includes/post-meta.php';
    require_once __DIR__ . '/includes/helpers.php';
    require_once __DIR__ . '/lib/style-handler/style-handler.php';

    function create_block_social_share_block_init() {

        define( 'SOCIAL_SHARE_BLOCKS_VERSION', "2.0.3" );
        define( 'SOCIAL_SHARE_BLOCKS_ADMIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'SOCIAL_SHARE_BLOCKS_ADMIN_PATH', dirname( __FILE__ ) );

        $script_asset_path = SOCIAL_SHARE_BLOCKS_ADMIN_PATH . "/dist/index.asset.php";
        if ( ! file_exists( $script_asset_path ) ) {
            throw new Error(
                'You need to run `npm start` or `npm run build` for the "social-share-block/social-share" block first.'
            );
        }

        $index_js         = SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'dist/index.js';
        $script_asset     = require $script_asset_path;
        $all_dependencies = array_merge( $script_asset['dependencies'], [
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-block-editor',
            'eb-social-share-blocks-controls-util',
            'essential-blocks-eb-animation'
        ] );

        wp_register_script(
            'eb-social-share-block-editor',
            $index_js,
            $all_dependencies,
            $script_asset['version']
        );

        $load_animation_js = SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'assets/js/eb-animation-load.js';
        wp_register_script(
            'essential-blocks-eb-animation',
            $load_animation_js,
            [],
            SOCIAL_SHARE_BLOCKS_VERSION,
            true
        );

        $animate_css = SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'assets/css/animate.min.css';
        wp_register_style(
            'essential-blocks-animation',
            $animate_css,
            [],
            SOCIAL_SHARE_BLOCKS_VERSION
        );

        $fontpicker_theme = 'assets/css/fonticonpicker.base-theme.react.css';
        wp_register_style(
            'fontpicker-default-theme',
            plugins_url( $fontpicker_theme, __FILE__ ),
            []
        );

        $fontpicker_material_theme = 'assets/css/fonticonpicker.material-theme.react.css';
        wp_register_style(
            'fontpicker-matetial-theme',
            plugins_url( $fontpicker_material_theme, __FILE__ ),
            []
        );

        $hover_css = 'assets/css/hover-min.css';
        wp_enqueue_style(
            'essential-blocks-hover-css',
            plugins_url( $hover_css, __FILE__ ),
            [ 'wp-editor' ]
        );

        $editor_css = 'dist/style.css';
        wp_register_style(
            'eb-social-share-block-editor-style',
            plugins_url( $editor_css, __FILE__ ),
            [ 'fontpicker-default-theme', 'fontpicker-matetial-theme', 'essential-blocks-hover-css' ],
            filemtime( SOCIAL_SHARE_BLOCKS_ADMIN_PATH . "/$editor_css" )
        );

        $fontawesome_css = 'assets/css/fontawesome/css/all.min.css';
        wp_register_style(
            'fontawesome-frontend-css',
            plugins_url( $fontawesome_css, __FILE__ ),
            []
        );

        $style_css = SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'dist/style.css';
        wp_register_style(
            'create-block-social-share-block',
            $style_css,
            [ 'fontawesome-frontend-css', 'essential-blocks-animation', 'essential-blocks-hover-css' ],
            filemtime( SOCIAL_SHARE_BLOCKS_ADMIN_PATH . '/dist/style.css' )
        );

        //Frontend Style
        $frontend_js    = SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'dist/frontend/index.js';
        $frontend_asset = require SOCIAL_SHARE_BLOCKS_ADMIN_PATH . '/dist/frontend/index.asset.php';
        wp_register_script(
            'social-share-block-frontend-js',
            $frontend_js,
            $frontend_asset['dependencies'],
            $frontend_asset['version'],
            true
        );

        if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'essential-blocks/social-share' ) ) {
            register_block_type(
                Social_Share_Helper::get_block_register_path( 'social-share-block/social-share', SOCIAL_SHARE_BLOCKS_ADMIN_PATH ),
                [
                    'editor_script'   => 'eb-social-share-block-editor',
                    'editor_style'    => 'eb-social-share-block-editor-style',
                    'style'           => 'create-block-social-share-block',
                    'render_callback' => 'eb_social_share_render_callback'
                ]
            );
        }
    }
    add_action( 'init', 'create_block_social_share_block_init' );

    /**
     * Render Callback Function for Social Share Block.
     *
     * @param array $attributes attributes of block
     * @param string $content
     *
     * @return string content of the block
     */
    function eb_social_share_render_callback( $attributes, $content ) {
        ob_start();
        if ( ! is_admin() ) {
            wp_enqueue_style( 'fontawesome-frontend-css' );
            wp_enqueue_script( 'social-share-block-frontend-js' );
            wp_enqueue_script( 'essential-blocks-eb-animation' );
        }

        global $post;
        $profilesOnly = ! empty( $attributes['profilesOnly'] ) ? $attributes['profilesOnly'] : [];
        $iconEffect   = ! empty( $attributes['icnEffect'] ) ? $attributes['icnEffect'] : '';
        $blockId      = $attributes['blockId'];
        $classHook    = ! empty( $attributes['classHook'] ) ? $attributes['classHook'] : '';
        $showTitle    = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
        $isFloating   = isset( $attributes['isFloating'] ) ? $attributes['isFloating'] : false;
        $iconShape    = isset( $attributes['iconShape'] ) ? $attributes['iconShape'] : '';

    ?>
<div<?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
    <div class="eb-parent-wrapper eb-parent-<?php echo esc_attr( $blockId ); ?><?php echo esc_attr( $classHook ); ?>">
        <div
            class="<?php echo esc_attr( $blockId ); ?> eb-social-share-wrapper<?php echo $isFloating ? esc_attr( ' eb-social-share-floating' ) : ''; ?><?php echo $isFloating && 'circular' == $iconShape ? esc_attr( ' eb-social-share-circular' ) : "" ?>">
            <ul class="eb-social-shares">
                <?php
                        foreach ( $profilesOnly as $profile ) {
                                preg_match( '/fa-([\w\-]+)/', $profile['icon'], $matches );
                                $iconClass = is_array( $matches ) && ! empty( $matches[1] ) ? $matches[1] . '-original' : '';
                            ?>
                <li>
                    <a class="<?php echo esc_attr( $iconClass ); ?><?php echo " " . esc_attr( $iconEffect ); ?>"
                        href=<?php echo Social_Share_Helper::eb_social_share_name_link( $post->ID, $profile['icon'] ); ?>
                        target="_blank" rel="nofollow noopener noreferrer">
                        <i
                            class="hvr-icon eb-social-share-icon								                                        <?php echo esc_attr( $profile['icon'] ); ?>"></i>
                        <?php
                                if ( ! empty( $showTitle && ! empty( $profile['iconText'] ) ) ) {?>
                        <span class="eb-social-share-text"><?php echo esc_html( $profile['iconText'] ); ?></span>
                        <?php }?>
                    </a>
                </li>
                <?php }?>
            </ul>
        </div>
    </div>
    </div>
    <?php
    return ob_get_clean();
}

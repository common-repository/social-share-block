<?php

/**
 * Load google fonts.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Social_Share_Helper
{

    private static $instance;

    /**
     * Registers the plugin.
     */
    public static function register()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * The Constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueues'));
    }

    /**
     * Load fonts.
     *
     * @access public
     */
    public function enqueues($hook)
    {
        global $pagenow;
        /**
         * Only for Admin Add/Edit Pages
         */
        if ($hook == 'post-new.php' || $hook == 'post.php' || $hook == 'site-editor.php' || ($pagenow == 'themes.php' && !empty($_SERVER['QUERY_STRING']) && str_contains($_SERVER['QUERY_STRING'], 'gutenberg-edit-site'))) {

            $controls_dependencies = include_once SOCIAL_SHARE_BLOCKS_ADMIN_PATH . '/dist/modules.asset.php';

            wp_register_script(
                "eb-social-share-blocks-controls-util",
                SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'dist/modules.js',
                array_merge($controls_dependencies['dependencies'],['lodash']),
                $controls_dependencies['version'],
                true
            );

            wp_localize_script('eb-social-share-blocks-controls-util', 'EssentialBlocksLocalize', array(
                'eb_wp_version' => (float) get_bloginfo('version'),
                'rest_rootURL' => get_rest_url(),
            ));

            if ($hook == 'post-new.php' || $hook == 'post.php') {
                wp_localize_script('eb-social-share-blocks-controls-util', 'eb_conditional_localize', array(
                    'editor_type' => 'edit-post'
                ));
            } else if ($hook == 'site-editor.php' || $pagenow == 'themes.php') {
                wp_localize_script('eb-social-share-blocks-controls-util', 'eb_conditional_localize', array(
                    'editor_type' => 'edit-site'
                ));
            }

			wp_register_style(
				'essential-blocks-iconpicker-css',
				SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'dist/style-modules.css',
				[],
				SOCIAL_SHARE_BLOCKS_ADMIN_URL,
				'all'
			);


            wp_enqueue_style(
                'essential-blocks-editor-css',
                SOCIAL_SHARE_BLOCKS_ADMIN_URL . 'dist/modules.css',
                array('essential-blocks-iconpicker-css','fontawesome-frontend-css'),
                $controls_dependencies['version'],
                'all'
            );
        }
    }
    /**
     * Get Social Shareable link
     *
     * @param int $id current post/page id
     * @param string $icon_text icon text to find the icon name
     *
     * @return string shareable link
     */
    public static function eb_social_share_name_link($id, $icon_text)
    {
        if (empty($icon_text)) {
            return;
        }

        $post_title = get_the_title($id);
        $post_link = get_the_permalink($id);

        if (preg_match('/facebook/', $icon_text)) {
            return esc_url('https://www.facebook.com/sharer/sharer.php?u=' . $post_link);
        } elseif (preg_match('/linkedin/', $icon_text)) {
            return esc_url('https://www.linkedin.com/shareArticle?title=' . $post_title . "&url=" . $post_link . '&mini=true');
        } elseif (preg_match('/twitter/', $icon_text)) {
            return esc_url("https://twitter.com/share?text=" . $post_title . "&url=" . $post_link);
        } elseif (preg_match('/pinterest/', $icon_text)) {
            return esc_url('https://pinterest.com/pin/create/button/?url=' . $post_link);
        } elseif (preg_match('/reddit/', $icon_text)) {
            return esc_url('https://www.reddit.com/submit?url=' . $post_link . "&title=" . $post_title);
        } elseif (preg_match('/tumblr/', $icon_text)) {
            return esc_url('https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . $post_link);
        } elseif (preg_match('/whatsapp/', $icon_text)) {
            return esc_url('https://api.whatsapp.com/send?text=' . $post_title . " " . $post_link);
        } elseif (preg_match('/telegram/', $icon_text)) {
            return esc_url('https://telegram.me/share/url?url=' . $post_link . '&text=' . $post_title);
        } elseif (preg_match('/pocket/', $icon_text)) {
            return esc_url('https://getpocket.com/edit?url=' . $post_link);
        } elseif (preg_match('/envelope/', $icon_text)) {
            return esc_url('mailto:?subject=' . $post_title . '&body=' . $post_link);
        } elseif (preg_match('/xing/', $icon_text)) {
            return esc_url('https://www.xing.com/spi/shares/new?url=' . $post_link);
        } elseif (preg_match('/vk/', $icon_text)) {
            return esc_url('https://vk.com/share.php?url=' . $post_link);
        }
    }
    public static function get_block_register_path($blockname, $blockPath)
    {
        if ((float) get_bloginfo('version') <= 5.6) {
            return $blockname;
        } else {
            return $blockPath;
        }
    }
}
Social_Share_Helper::register();
<?php
/*
Plugin Name: Social Auto Poster
Plugin URI: https://github.com/vestrainteractive/social-auto-poster
Description: A plugin to cross-post WordPress posts to Reddit, Bluesky, and possibly Facebook.
Version: 1.0
Author: Vestra Interactive
Author URI: https://vestrainteractive.com
*/

if (!defined('ABSPATH')) exit;

class SocialAutoPoster {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function add_settings_page() {
        add_menu_page(
            'Social Auto Poster Settings',
            'Social Auto Poster',
            'manage_options',
            'social-auto-poster',
            [$this, 'render_settings_page'],
            'dashicons-share-alt'
        );
    }

    public function enqueue_styles($hook) {
        if ($hook != 'toplevel_page_social-auto-poster') return;
        wp_enqueue_style('social-auto-poster-styles', plugin_dir_url(__FILE__) . 'css/styles.css');
    }

    public function render_settings_page() {
        ?>
        <div class="wrap social-auto-poster">
            <h1>Social Auto Poster Settings</h1>
            
            <div class="tabs">
                <button class="tab-button" onclick="openTab(event, 'reddit')"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/reddit.png'; ?>" alt="Reddit"> Reddit</button>
                <button class="tab-button" onclick="openTab(event, 'bluesky')"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/bluesky.png'; ?>" alt="Bluesky"> Bluesky</button>
                <button class="tab-button" onclick="openTab(event, 'facebook')"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/facebook.png'; ?>" alt="Facebook"> Facebook</button>
            </div>

            <div id="reddit" class="tab-content">
                <h2>Reddit Settings</h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('social_auto_poster_reddit');
                    do_settings_sections('social-auto-poster-reddit');
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="reddit_client_id">Client ID</label></th>
                            <td><input type="text" id="reddit_client_id" name="reddit_client_id" value="<?php echo esc_attr(get_option('reddit_client_id')); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="reddit_client_secret">Client Secret</label></th>
                            <td><input type="text" id="reddit_client_secret" name="reddit_client_secret" value="<?php echo esc_attr(get_option('reddit_client_secret')); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="reddit_autopost_categories">Auto-Post Categories</label></th>
                            <td><input type="text" id="reddit_autopost_categories" name="reddit_autopost_categories" value="<?php echo esc_attr(get_option('reddit_autopost_categories')); ?>" placeholder="Category:subreddit1,subreddit2" /></td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div id="bluesky" class="tab-content" style="display:none;">
                <h2>Bluesky Settings</h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('social_auto_poster_bluesky');
                    do_settings_sections('social-auto-poster-bluesky');
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="bluesky_user_id">User ID</label></th>
                            <td><input type="text" id="bluesky_user_id" name="bluesky_user_id" value="<?php echo esc_attr(get_option('bluesky_user_id')); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bluesky_api_key">API Key</label></th>
                            <td><input type="text" id="bluesky_api_key" name="bluesky_api_key" value="<?php echo esc_attr(get_option('bluesky_api_key')); ?>" /></td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div id="facebook" class="tab-content" style="display:none;">
                <h2>Facebook Settings</h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('social_auto_poster_facebook');
                    do_settings_sections('social-auto-poster-facebook');
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="facebook_app_id">App ID</label></th>
                            <td><input type="text" id="facebook_app_id" name="facebook_app_id" value="<?php echo esc_attr(get_option('facebook_app_id')); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="facebook_app_secret">App Secret</label></th>
                            <td><input type="text" id="facebook_app_secret" name="facebook_app_secret" value="<?php echo esc_attr(get_option('facebook_app_secret')); ?>" /></td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div class="external-cron">
                <h3>WP-Cron Control</h3>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('social_auto_poster_cron');
                    do_settings_sections('social-auto-poster-cron');
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="disable_wp_cron">Disable WP-Cron for Publishing</label></th>
                            <td><input type="checkbox" id="disable_wp_cron" name="disable_wp_cron" value="1" <?php checked(1, get_option('disable_wp_cron'), true); ?> /></td>
                        </tr>
                        <tr>
                            <td colspan="2">Cron URL: <?php echo site_url('/?social_auto_poster_cron=true'); ?></td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <script>
            function openTab(evt, tabName) {
                var i, tabcontent, tabbuttons;
                tabcontent = document.getElementsByClassName("tab-content");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                tabbuttons = document.getElementsByClassName("tab-button");
                for (i = 0; i < tabbuttons.length; i++) {
                    tabbuttons[i].className = tabbuttons[i].className.replace(" active", "");
                }
                document.getElementById(tabName).style.display = "block";
                evt.currentTarget.className += " active";
            }
            document.getElementsByClassName("tab-button")[0].click();
        </script>
        <?php
    }
}

new SocialAutoPoster();

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
        add_action('admin_init', [$this, 'register_settings']);
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
        wp_enqueue_script('social-auto-poster-scripts', plugin_dir_url(__FILE__) . 'js/scripts.js', array('jquery'), null, true);
    }

    public function register_settings() {
        register_setting('social_auto_poster_reddit', 'reddit_client_id');
        register_setting('social_auto_poster_reddit', 'reddit_client_secret');
        register_setting('social_auto_poster_reddit', 'reddit_token');
        
        register_setting('social_auto_poster_bluesky', 'bluesky_user_id');
        register_setting('social_auto_poster_bluesky', 'bluesky_api_key');
        register_setting('social_auto_poster_bluesky', 'bluesky_token');
        
        register_setting('social_auto_poster_facebook', 'facebook_app_id');
        register_setting('social_auto_poster_facebook', 'facebook_app_secret');
        register_setting('social_auto_poster_facebook', 'facebook_token');
        register_setting('social_auto_poster_facebook', 'facebook_selected_page');

        register_setting('social_auto_poster_wpcron', 'disable_wp_cron');
    }

    public function render_settings_page() {
        ?>
        <div class="wrap social-auto-poster">
            <h1>Social Auto Poster Settings</h1>
            
            <div class="tab-container">
                <div class="tabs">
                    <div class="tab-button" onclick="openTab(event, 'reddit')" data-tab="reddit"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/reddit.png'; ?>" alt="Reddit"> Reddit</div>
                    <div class="tab-button" onclick="openTab(event, 'bluesky')" data-tab="bluesky"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/bluesky.png'; ?>" alt="Bluesky"> Bluesky</div>
                    <div class="tab-button" onclick="openTab(event, 'facebook')" data-tab="facebook"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/facebook.png'; ?>" alt="Facebook"> Facebook</div>
                </div>

                <!-- Reddit Settings -->
                <div id="reddit" class="tab-content" data-bg="rgba(255,77,0,0.3)">
                    <h2>Reddit Settings</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields('social_auto_poster_reddit'); ?>
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
                                <th colspan="2">
                                    <button type="button" onclick="oauthConnect('reddit')">Connect to Reddit</button>
                                    <span id="reddit-status"><?php echo get_option('reddit_token') ? 'Connected' : 'Not Connected'; ?></span>
                                </th>
                            </tr>
                        </table>
                        <?php submit_button(); ?>
                    </form>
                </div>

                <!-- Bluesky Settings -->
                <div id="bluesky" class="tab-content" data-bg="rgba(0,133,255,0.3)" style="display:none;">
                    <h2>Bluesky Settings</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields('social_auto_poster_bluesky'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="bluesky_user_id">User ID</label></th>
                                <td><input type="text" id="bluesky_user_id" name="bluesky_user_id" value="<?php echo esc_attr(get_option('bluesky_user_id')); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bluesky_api_key">API Key</label></th>
                                <td><input type="text" id="bluesky_api_key" name="bluesky_api_key" value="<?php echo esc_attr(get_option('bluesky_api_key')); ?>" /></td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    <button type="button" onclick="oauthConnect('bluesky')">Connect to Bluesky</button>
                                    <span id="bluesky-status"><?php echo get_option('bluesky_token') ? 'Connected' : 'Not Connected'; ?></span>
                                </th>
                            </tr>
                        </table>
                        <?php submit_button(); ?>
                    </form>
                </div>

                <!-- Facebook Settings -->
                <div id="facebook" class="tab-content" data-bg="rgba(8,102,255,0.3)" style="display:none;">
                    <h2>Facebook Settings</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields('social_auto_poster_facebook'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="facebook_app_id">App ID</label></th>
                                <td><input type="text" id="facebook_app_id" name="facebook_app_id" value="<?php echo esc_attr(get_option('facebook_app_id')); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="facebook_app_secret">App Secret</label></th>
                                <td><input type="text" id="facebook_app_secret" name="facebook_app_secret" value="<?php echo esc_attr(get_option('facebook_app_secret')); ?>" /></td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    <button type="button" onclick="oauthConnect('facebook')">Connect to Facebook</button>
                                    <span id="facebook-status"><?php echo get_option('facebook_token') ? 'Connected' : 'Not Connected'; ?></span>
                                </th>
                            </tr>
                            <tr id="facebook_pages_dropdown" style="display: none;">
                                <th scope="row"><label for="facebook_selected_page">Select Page to Post To</label></th>
                                <td>
                                    <select id="facebook_selected_page" name="facebook_selected_page">
                                        <option value="">-- Select a Page --</option>
                                        <!-- Options will populate upon successful authentication -->
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(); ?>
                    </form>
                </div>

                <!-- WP-Cron Settings -->
                <h2>WP-Cron Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('social_auto_poster_wpcron'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="disable_wp_cron">Disable WP-Cron for Publishing</label></th>
                            <td>
                                <input type="checkbox" id="disable_wp_cron" name="disable_wp_cron" value="1" <?php checked(1, get_option('disable_wp_cron'), true); ?> />
                                <label for="disable_wp_cron">Disable WP-Cron (use external cron)</label>
                            </td>
                        </tr>
                        <tr id="external-cron-url" style="display: none;">
                            <th scope="row">External Cron URL</th>
                            <td>
                                <input type="text" value="<?php echo esc_url(admin_url('admin-ajax.php?action=run_social_auto_poster_cron')); ?>" readonly />
                                <p class="description">Use this URL in an external cron service like Uptime Kuma or Uptime Monitor.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
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
                        tabbuttons[i].classList.remove("active");
                    }
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.classList.add("active");

                    var bgColor = document.getElementById(tabName).getAttribute('data-bg');
                    document.getElementById(tabName).style.backgroundColor = bgColor;
                }

                // Automatically open the first tab
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelector('.tab-button').click();
                });

                jQuery('#disable_wp_cron').change(function() {
                    if (this.checked) {
                        jQuery('#external-cron-url').show();
                    } else {
                        jQuery('#external-cron-url').hide();
                    }
                }).change();
            </script>
        </div>
        <?php
    }
}

new SocialAutoPoster();

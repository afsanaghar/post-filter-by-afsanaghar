<?php
/*
Plugin Name: Elementor Post Filter
Description: A plugin to filter posts on Elementor-powered websites. Supports multiple languages like English and Urdu.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EPF_VERSION', '1.0');
define('EPF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EPF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load text domain for translations
function epf_load_textdomain() {
    load_plugin_textdomain('elementor-post-filter', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'epf_load_textdomain');

function epf_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style('epf-style', EPF_PLUGIN_URL . 'assets/css/style.css', array(), EPF_VERSION);

    // Enqueue JS
    wp_enqueue_script('epf-script', EPF_PLUGIN_URL . 'assets/js/script.js', array('jquery'), EPF_VERSION, true);

    // Localize script for translations and AJAX URL
    wp_localize_script('epf-script', 'epf_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'filter_label' => __('Filter', 'elementor-post-filter'),
        'reset_label' => __('Reset', 'elementor-post-filter'),
    ));
}
add_action('wp_enqueue_scripts', 'epf_enqueue_scripts');

function epf_filter_form() {
    ob_start(); // Start output buffering
    ?>
    <form id="epf-filter-form">
        <input type="text" name="search" placeholder="<?php _e('Search...', 'elementor-post-filter'); ?>">
        <select name="category">
            <option value=""><?php _e('All Categories', 'elementor-post-filter'); ?></option>
            <?php
            $categories = get_categories(); // Get all WordPress categories
            foreach ($categories as $category) {
                echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
            }
            ?>
        </select>
        <button type="submit"><?php _e('Filter', 'elementor-post-filter'); ?></button>
        <button type="reset"><?php _e('Reset', 'elementor-post-filter'); ?></button>
    </form>
    <div id="epf-filter-results"></div> <!-- Results will be displayed here -->
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('epf_filter', 'epf_filter_form'); // Register the shortcode [epf_filter]

function epf_filter_posts() {
    $args = array(
        'post_type' => 'post', // Filter only posts
        'posts_per_page' => -1, // Show all posts
        's' => sanitize_text_field($_POST['search']), // Search term
        'cat' => intval($_POST['category']), // Selected category
    );

    $query = new WP_Query($args); // Run the query

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="epf-post-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<div>' . get_the_excerpt() . '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>' . __('No posts found.', 'elementor-post-filter') . '</p>';
    }

    wp_die(); // End the AJAX request
}
add_action('wp_ajax_epf_filter_posts', 'epf_filter_posts'); // For logged-in users
add_action('wp_ajax_nopriv_epf_filter_posts', 'epf_filter_posts'); // For non-logged-in users

// Include the Plugin Update Checker library
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Set up the update checker
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/afsanaghar/post-filter-by-afsanaghar.git', // GitHub repository URL
    __FILE__, // Path to the main plugin file
    'elementor-post-filter' // Plugin slug (must match the folder name)
);

// Optional: Set the branch for updates (default is 'main' or 'master')
$myUpdateChecker->setBranch('main');


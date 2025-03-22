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

// Enqueue scripts and styles
function epf_enqueue_scripts() {
    wp_enqueue_style('epf-style', EPF_PLUGIN_URL . 'assets/css/style.css', array(), EPF_VERSION);
    wp_enqueue_script('epf-script', EPF_PLUGIN_URL . 'assets/js/script.js', array('jquery'), EPF_VERSION, true);

    // Localize script for translations and AJAX URL
    wp_localize_script('epf-script', 'epf_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'filter_label' => __('Filter', 'elementor-post-filter'),
        'reset_label' => __('Reset', 'elementor-post-filter'),
    ));
}
add_action('wp_enqueue_scripts', 'epf_enqueue_scripts');

// Filter form shortcode
function epf_filter_form() {
    ob_start();
    ?>
    <form id="epf-filter-form">
        <input type="text" name="search" placeholder="<?php _e('Search...', 'elementor-post-filter'); ?>">
        <select name="category">
            <option value=""><?php _e('All Categories', 'elementor-post-filter'); ?></option>
            <?php
            $categories = get_categories();
            foreach ($categories as $category) {
                echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
            }
            ?>
        </select>
        <select name="tag">
            <option value=""><?php _e('All Tags', 'elementor-post-filter'); ?></option>
            <?php
            $tags = get_tags();
            foreach ($tags as $tag) {
                echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';
            }
            ?>
        </select>
        <select name="sort">
            <option value="date_desc"><?php _e('Newest First', 'elementor-post-filter'); ?></option>
            <option value="date_asc"><?php _e('Oldest First', 'elementor-post-filter'); ?></option>
            <option value="title_asc"><?php _e('Title (A-Z)', 'elementor-post-filter'); ?></option>
            <option value="title_desc"><?php _e('Title (Z-A)', 'elementor-post-filter'); ?></option>
        </select>
        <button type="submit"><?php _e('Filter', 'elementor-post-filter'); ?></button>
        <button type="reset"><?php _e('Reset', 'elementor-post-filter'); ?></button>
    </form>
    <div id="epf-filter-results"></div>
    <button id="epf-load-more" style="display: none;"><?php _e('Load More', 'elementor-post-filter'); ?></button>
    <?php
    return ob_get_clean();
}
add_shortcode('epf_filter', 'epf_filter_form');

// AJAX handler for filtering posts
function epf_filter_posts() {
    $search = sanitize_text_field($_POST['search']);
    $category = intval($_POST['category']);
    $tag = intval($_POST['tag']);
    $sort = sanitize_text_field($_POST['sort']);
    $page = intval($_POST['page']);

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 6,
        'paged' => $page,
        's' => $search,
        'cat' => $category,
        'tag_id' => $tag,
    );

    switch ($sort) {
        case 'date_desc':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'date_asc':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'title_asc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'title_desc':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="epf-post-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<div>' . get_the_excerpt() . '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>' . __('No more posts found.', 'elementor-post-filter') . '</p>';
    }

    if ($query->max_num_pages > $page) {
        echo '<script>jQuery("#epf-load-more").show();</script>';
    } else {
        echo '<script>jQuery("#epf-load-more").hide();</script>';
    }

    wp_die();
}
add_action('wp_ajax_epf_filter_posts', 'epf_filter_posts');
add_action('wp_ajax_nopriv_epf_filter_posts', 'epf_filter_posts');

// Add a professional settings page to the WordPress admin menu
function epf_add_pro_settings_page() {
    add_menu_page(
        __('Post Filter Pro', 'elementor-post-filter'), // Page title
        __('Post Filter Pro', 'elementor-post-filter'), // Menu title
        'manage_options', // Capability required to access
        'epf-pro-settings', // Menu slug
        'epf_render_pro_settings_page', // Callback function to render the page
        'dashicons-filter', // Icon (optional)
        100 // Position in the menu (optional)
    );
}
add_action('admin_menu', 'epf_add_pro_settings_page');

// Render the professional settings page
function epf_render_pro_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Post Filter Pro Settings', 'elementor-post-filter'); ?></h1>

        <!-- Tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'elementor-post-filter'); ?></a>
            <a href="#filter-groups" class="nav-tab"><?php _e('Filter Groups', 'elementor-post-filter'); ?></a>
            <a href="#product-filters" class="nav-tab"><?php _e('Product Filters', 'elementor-post-filter'); ?></a>
        </h2>

        <!-- General Settings Tab -->
        <div id="general" class="tab-content">
            <form method="post" action="options.php">
                <?php
                settings_fields('epf_pro_general_group'); // Settings group name
                do_settings_sections('epf-pro-general'); // Page slug
                submit_button(); // Save changes button
                ?>
            </form>
        </div>

        <!-- Filter Groups Tab -->
        <div id="filter-groups" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php
                settings_fields('epf_pro_filter_groups_group'); // Settings group name
                do_settings_sections('epf-pro-filter-groups'); // Page slug
                submit_button(); // Save changes button
                ?>
            </form>
        </div>

        <!-- Product Filters Tab -->
        <div id="product-filters" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php
                settings_fields('epf_pro_product_filters_group'); // Settings group name
                do_settings_sections('epf-pro-product-filters'); // Page slug
                submit_button(); // Save changes button
                ?>
            </form>
        </div>
    </div>

    <!-- Tab Switching Script -->
    <script>
        jQuery(document).ready(function ($) {
            $('.nav-tab-wrapper a').click(function (e) {
                e.preventDefault();
                $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });
        });
    </script>
    <?php
}

// Register plugin settings
function epf_register_pro_settings() {
    // General Settings
    register_setting('epf_pro_general_group', 'epf_posts_per_page', array(
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 6,
    ));

    register_setting('epf_pro_general_group', 'epf_default_sort', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'date_desc',
    ));

    add_settings_section(
        'epf_pro_general_section',
        __('General Settings', 'elementor-post-filter'),
        'epf_pro_general_section_callback',
        'epf-pro-general'
    );

    add_settings_field(
        'epf_posts_per_page',
        __('Posts per page', 'elementor-post-filter'),
        'epf_posts_per_page_callback',
        'epf-pro-general',
        'epf_pro_general_section'
    );

    add_settings_field(
        'epf_default_sort',
        __('Default sorting', 'elementor-post-filter'),
        'epf_default_sort_callback',
        'epf-pro-general',
        'epf_pro_general_section'
    );

    // Filter Groups Settings
    register_setting('epf_pro_filter_groups_group', 'epf_filter_groups', array(
        'type' => 'array',
        'sanitize_callback' => 'epf_sanitize_filter_groups',
    ));

    add_settings_section(
        'epf_pro_filter_groups_section',
        __('Filter Groups', 'elementor-post-filter'),
        'epf_pro_filter_groups_section_callback',
        'epf-pro-filter-groups'
    );

    add_settings_field(
        'epf_filter_groups',
        __('Filter Groups', 'elementor-post-filter'),
        'epf_filter_groups_callback',
        'epf-pro-filter-groups',
        'epf_pro_filter_groups_section'
    );

    // Product Filters Settings (if WooCommerce is active)
    if (class_exists('WooCommerce')) {
        register_setting('epf_pro_product_filters_group', 'epf_product_filters', array(
            'type' => 'array',
            'sanitize_callback' => 'epf_sanitize_product_filters',
        ));

        add_settings_section(
            'epf_pro_product_filters_section',
            __('Product Filters', 'elementor-post-filter'),
            'epf_pro_product_filters_section_callback',
            'epf-pro-product-filters'
        );

        add_settings_field(
            'epf_product_filters',
            __('Product Filters', 'elementor-post-filter'),
            'epf_product_filters_callback',
            'epf-pro-product-filters',
            'epf_pro_product_filters_section'
        );
    }
}
add_action('admin_init', 'epf_register_pro_settings');

// Callback functions for settings sections and fields
function epf_pro_general_section_callback() {
    echo '<p>' . __('Configure the general settings for the Post Filter Pro plugin.', 'elementor-post-filter') . '</p>';
}

function epf_posts_per_page_callback() {
    $posts_per_page = get_option('epf_posts_per_page', 6);
    echo '<input type="number" name="epf_posts_per_page" value="' . esc_attr($posts_per_page) . '" min="1" />';
}

function epf_default_sort_callback() {
    $default_sort = get_option('epf_default_sort', 'date_desc');
    ?>
    <select name="epf_default_sort">
        <option value="date_desc" <?php selected($default_sort, 'date_desc'); ?>><?php _e('Newest First', 'elementor-post-filter'); ?></option>
        <option value="date_asc" <?php selected($default_sort, 'date_asc'); ?>><?php _e('Oldest First', 'elementor-post-filter'); ?></option>
        <option value="title_asc" <?php selected($default_sort, 'title_asc'); ?>><?php _e('Title (A-Z)', 'elementor-post-filter'); ?></option>
        <option value="title_desc" <?php selected($default_sort, 'title_desc'); ?>><?php _e('Title (Z-A)', 'elementor-post-filter'); ?></option>
    </select>
    <?php
}

function epf_pro_filter_groups_section_callback() {
    echo '<p>' . __('Create and manage filter groups for your posts.', 'elementor-post-filter') . '</p>';
}

function epf_filter_groups_callback() {
    $filter_groups = get_option('epf_filter_groups', array());
    ?>
    <div id="epf-filter-groups">
        <?php foreach ($filter_groups as $index => $group) : ?>
            <div class="epf-filter-group">
                <input type="text" name="epf_filter_groups[<?php echo $index; ?>][name]" value="<?php echo esc_attr($group['name']); ?>" placeholder="<?php _e('Group Name', 'elementor-post-filter'); ?>" />
                <textarea name="epf_filter_groups[<?php echo $index; ?>][filters]" placeholder="<?php _e('Filters (comma-separated)', 'elementor-post-filter'); ?>"><?php echo esc_textarea($group['filters']); ?></textarea>
                <button class="button epf-remove-group"><?php _e('Remove', 'elementor-post-filter'); ?></button>
            </div>
        <?php endforeach; ?>
    </div>
    <button id="epf-add-group" class="button"><?php _e('Add Group', 'elementor-post-filter'); ?></button>
    <script>
        jQuery(document).ready(function ($) {
            $('#epf-add-group').click(function (e) {
                e.preventDefault();
                var index = $('#epf-filter-groups .epf-filter-group').length;
                $('#epf-filter-groups').append(
                    '<div class="epf-filter-group">' +
                    '<input type="text" name="epf_filter_groups[' + index + '][name]" placeholder="<?php _e('Group Name', 'elementor-post-filter'); ?>" />' +
                    '<textarea name="epf_filter_groups[' + index + '][filters]" placeholder="<?php _e('Filters (comma-separated)', 'elementor-post-filter'); ?>"></textarea>' +
                    '<button class="button epf-remove-group"><?php _e('Remove', 'elementor-post-filter'); ?></button>' +
                    '</div>'
                );
            });

            $(document).on('click', '.epf-remove-group', function (e) {
                e.preventDefault();
                $(this).closest('.epf-filter-group').remove();
            });
        });
    </script>
    <?php
}

function epf_pro_product_filters_section_callback() {
    echo '<p>' . __('Configure product filters for WooCommerce.', 'elementor-post-filter') . '</p>';
}

function epf_product_filters_callback() {
    $product_filters = get_option('epf_product_filters', array());
    ?>
    <div id="epf-product-filters">
        <?php foreach ($product_filters as $index => $filter) : ?>
            <div class="epf-product-filter">
                <input type="text" name="epf_product_filters[<?php echo $index; ?>][name]" value="<?php echo esc_attr($filter['name']); ?>" placeholder="<?php _e('Filter Name', 'elementor-post-filter'); ?>" />
                <textarea name="epf_product_filters[<?php echo $index; ?>][options]" placeholder="<?php _e('Options (comma-separated)', 'elementor-post-filter'); ?>"><?php echo esc_textarea($filter['options']); ?></textarea>
                <button class="button epf-remove-filter"><?php _e('Remove', 'elementor-post-filter'); ?></button>
            </div>
        <?php endforeach; ?>
    </div>
    <button id="epf-add-filter" class="button"><?php _e('Add Filter', 'elementor-post-filter'); ?></button>
    <script>
        jQuery(document).ready(function ($) {
            $('#epf-add-filter').click(function (e) {
                e.preventDefault();
                var index = $('#epf-product-filters .epf-product-filter').length;
                $('#epf-product-filters').append(
                    '<div class="epf-product-filter">' +
                    '<input type="text" name="epf_product_filters[' + index + '][name]" placeholder="<?php _e('Filter Name', 'elementor-post-filter'); ?>" />' +
                    '<textarea name="epf_product_filters[' + index + '][options]" placeholder="<?php _e('Options (comma-separated)', 'elementor-post-filter'); ?>"></textarea>' +
                    '<button class="button epf-remove-filter"><?php _e('Remove', 'elementor-post-filter'); ?></button>' +
                    '</div>'
                );
            });

            $(document).on('click', '.epf-remove-filter', function (e) {
                e.preventDefault();
                $(this).closest('.epf-product-filter').remove();
            });
        });
    </script>
    <?php
}

// Sanitize filter groups
function epf_sanitize_filter_groups($input) {
    $sanitized = array();
    foreach ($input as $group) {
        $sanitized[] = array(
            'name' => sanitize_text_field($group['name']),
            'filters' => sanitize_text_field($group['filters']),
        );
    }
    return $sanitized;
}

// Sanitize product filters
function epf_sanitize_product_filters($input) {
    $sanitized = array();
    foreach ($input as $filter) {
        $sanitized[] = array(
            'name' => sanitize_text_field($filter['name']),
            'options' => sanitize_text_field($filter['options']),
        );
    }
    return $sanitized;
}

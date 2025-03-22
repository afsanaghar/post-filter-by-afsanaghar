if (!defined('ABSPATH')) {
    exit;
}

class EPF_Elementor_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'epf_post_filter';
    }

    public function get_title() {
        return __('Post Filter', 'elementor-post-filter');
    }

    public function get_icon() {
        return 'eicon-filter';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        // Add controls if needed
    }

    protected function render() {
        echo do_shortcode('[epf_filter]');
    }
}

function register_epf_elementor_widget($widgets_manager) {
    require_once(EPF_PLUGIN_DIR . 'includes/elementor-widget.php');
    $widgets_manager->register(new EPF_Elementor_Widget());
}
add_action('elementor/widgets/register', 'register_epf_elementor_widget');
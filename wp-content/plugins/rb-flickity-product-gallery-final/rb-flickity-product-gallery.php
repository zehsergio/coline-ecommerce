<?php
/**
 * Plugin Name: RB Flickity Product Gallery (Elementor Widget)  — Final (Main + Side Previews)
 * Description: Versão mínima focada em fazer as imagens deslizarem (só galeria principal). Usa imagem destacada + galeria do WooCommerce.
 * Version: 1.0.0-final
 * Author: Você + ChatGPT
 * License: GPLv2 or later
 * Text Domain: rb-flickity-pdp
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'RB_FPG_VERSION', '1.0.0-final' );
define( 'RB_FPG_PLUGIN_FILE', __FILE__ );
define( 'RB_FPG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RB_FPG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function rb_fpg_enqueue_assets_min() {
    wp_register_style('flickity','https://cdn.jsdelivr.net/npm/flickity@2/dist/flickity.min.css',[], '2.3.0');
    wp_register_script('flickity','https://cdn.jsdelivr.net/npm/flickity@2/dist/flickity.pkgd.min.js',[], '2.3.0', true);
    wp_register_style('rb-fpg', RB_FPG_PLUGIN_URL.'assets/css/gallery.css', ['flickity'], RB_FPG_VERSION );
    wp_register_script('rb-fpg', RB_FPG_PLUGIN_URL.'assets/js/gallery.js', ['jquery','flickity'], RB_FPG_VERSION, true );
}
add_action('wp_enqueue_scripts','rb_fpg_enqueue_assets_min');
add_action('elementor/editor/before_enqueue_scripts','rb_fpg_enqueue_assets_min');

function rb_fpg_register_elementor_widget_final( $widgets_manager ) {
    require_once RB_FPG_PLUGIN_DIR . 'includes/class-rb-flickity-elementor-widget.php';
    $widgets_manager->register( new \RB_Flickity_PDP_Gallery_Widget_Final() );
}
add_action( 'elementor/widgets/register', 'rb_fpg_register_elementor_widget_final' );

function rb_fpg_render_template_min( $template, $vars = array() ) {
    $template_file = RB_FPG_PLUGIN_DIR . 'templates/' . $template . '.php';
    if ( file_exists( $template_file ) ) {
        extract( $vars );
        include $template_file;
    }
}

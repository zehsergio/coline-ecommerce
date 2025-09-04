<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class RB_Flickity_PDP_Gallery_Widget_Final extends Widget_Base {

    public function get_name(){ return 'rb_flickity_pdp_gallery_final'; }
    public function get_title(){ return __('RB Flickity Product Gallery (Final)', 'rb-flickity-pdp'); }
    public function get_icon(){ return 'eicon-slider-push'; }
    public function get_categories(){ return [ 'woocommerce-elements', 'general' ]; }
    public function get_script_depends(){ return [ 'rb-fpg' ]; }
    public function get_style_depends(){ return [ 'rb-fpg' ]; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label'=>__('Conteúdo','rb-flickity-pdp')] );
        $this->add_control('product_id',[
            'label'=>__('ID do produto (opcional)','rb-flickity-pdp'),
            'type'=>Controls_Manager::NUMBER,
            'default'=>0,
            'description'=>__('Deixe 0 para usar o produto atual.','rb-flickity-pdp')
        ]);
        $this->end_controls_section();

        $this->start_controls_section('ui', ['label'=>__('UI','rb-flickity-pdp')] );
        $this->add_control('max_height_desktop',[
            'label'=>__('Altura máxima (desktop, px)','rb-flickity-pdp'),
            'type'=>Controls_Manager::NUMBER,
            'default'=>560,
            'min'=>300,
            'max'=>1200,
            'step'=>10
        ]);
        $this->add_control('max_height_mobile',[
            'label'=>__('Altura máxima (mobile, px)','rb-flickity-pdp'),
            'type'=>Controls_Manager::NUMBER,
            'default'=>420,
            'min'=>240,
            'max'=>900,
            'step'=>10
        ]);
        
        $this->add_control('show_side_previews',[
            'label'=>__('Mostrar pré-visualizações laterais (desktop)','rb-flickity-pdp'),
            'type'=>Controls_Manager::SWITCHER,
            'label_on'=>__('Sim','rb-flickity-pdp'),
            'label_off'=>__('Não','rb-flickity-pdp'),
            'return_value'=>'yes',
            'default'=>'yes'
        ]);
        $this->end_controls_section();
    }

    protected function render(){
        $settings = $this->get_settings_for_display();
        $product_id = absint( $settings['product_id'] );

        if ( ! $product_id && function_exists('is_product') && is_product() ) {
            global $product;
            if ( $product && $product->get_id() ) $product_id = $product->get_id();
        }
        if ( ! $product_id && function_exists('wc_get_products') ) {
            $products = wc_get_products([ 'limit' => 1 ]);
            if ( ! empty($products) ) $product_id = $products[0]->get_id();
        }
        if ( ! $product_id ) { echo '<div class="rb-fpg--notice">Selecione um produto válido.</div>'; return; }

        $product = wc_get_product( $product_id );
        if ( ! $product ) { echo '<div class="rb-fpg--notice">Produto não encontrado.</div>'; return; }

        // Pegar imagem destacada + galeria
        $image_ids = $product->get_gallery_image_ids();
        $thumb_id  = $product->get_image_id();
        if ( $thumb_id ) array_unshift( $image_ids, $thumb_id );
        $image_ids = array_values( array_unique( array_filter( $image_ids ) ) );

        wp_enqueue_style('rb-fpg');
        wp_enqueue_script('rb-fpg');

        rb_fpg_render_template_min('gallery', [
            'product' => $product,
            'image_ids' => $image_ids,
            'widget_id' => $this->get_id(),
            'show_side_previews' => true,
        ]);
    }
}

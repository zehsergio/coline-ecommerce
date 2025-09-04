<?php
/**
 * Plugin Name: RB FPG Anti-FOUC
 * Description: Esconde empilhamento antes do Flickity e melhora o preview no editor do Elementor (sem marcar --ready).
 * Version: 1.0.1
 * Author: Você
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'RB_FPG_AFX_URL', plugin_dir_url( __FILE__ ) );
define( 'RB_FPG_AFX_VER', '1.0.1' );

// Front-end
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style( 'rb-fpg-anti-fouc', RB_FPG_AFX_URL . 'assets/css/anti-fouc.css', [], RB_FPG_AFX_VER );
  wp_enqueue_script('rb-fpg-anti-fouc', RB_FPG_AFX_URL . 'assets/js/anti-fouc.js', ['jquery'], RB_FPG_AFX_VER, true );
});

// Editor do Elementor: enfileira os MESMOS assets no editor
add_action('elementor/editor/before_enqueue_scripts', function(){
  wp_enqueue_style( 'rb-fpg-anti-fouc', RB_FPG_AFX_URL . 'assets/css/anti-fouc.css', [], RB_FPG_AFX_VER );
  wp_enqueue_script('rb-fpg-anti-fouc', RB_FPG_AFX_URL . 'assets/js/anti-fouc.js', ['jquery'], RB_FPG_AFX_VER, true );
});


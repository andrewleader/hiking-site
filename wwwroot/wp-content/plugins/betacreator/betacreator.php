<?php
/*
Plugin Name: Betacreator
*/
function registerBlock() {
    
    // Thickbox is for popups https://www.exratione.com/2018/02/the-easiest-javascript-modal-for-administrative-pages-in-wordpress-4/
    wp_enqueue_style('thickbox');
    wp_enqueue_script('thickbox');
    
    wp_enqueue_style(
        'betacreator-css',
        plugins_url('betacreator.css', __FILE__),
        array(), // dependencies
        filemtime(dirname(__FILE__) . '/betacreator.css') // version number
    );
    
    wp_enqueue_style(
        'betacreator-tool-css',
        plugins_url('tool/css/betacreator.css', __FILE__),
        array(), // dependencies
        filemtime(dirname(__FILE__) . '/tool/css/betacreator.css') // version number
    );
    
    wp_enqueue_script(
        'betacreator-tool',
        plugins_url('tool/js/betacreator.js', __FILE__),
        array(), // dependencies
        filemtime(dirname(__FILE__) . '/tool/js/betacreator.js') // version number
    );
    
    wp_register_script(
        'betacreator',
        plugins_url( 'betacreator.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ), // Dependencies
        filemtime(dirname(__FILE__) . '/betacreator.css') // version number
    );
 
    register_block_type( 'andrewleader/betacreator', array(
        'editor_script' => 'betacreator',
    ) );
 
}
add_action( 'init', 'registerBlock' );
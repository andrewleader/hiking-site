<?php
/*
Plugin Name: Leader Map
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/model/area.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/model/route.php');

function leadermap_init() {
    
    add_shortcode('leadermap', 'leadermap_handler');
    
    wp_enqueue_style(
        'leader-map-css',
        plugins_url('leader-map.css', __FILE__),
        array(), // dependencies
        filemtime(dirname(__FILE__) . '/leader-map.css') // version number
    );
    
    wp_enqueue_script(
        'leader-map-js',
        plugins_url('leader-map.js', __FILE__),
        array(), // dependencies
        filemtime(dirname(__FILE__) . '/leader-map.js') // version number
    );
    
    wp_enqueue_script(
        'leader-map-js-google',
        'https://maps.googleapis.com/maps/api/js?key=AIzaSyAouPWaETNwNjBoDgzqp18lGcf-7AdZlMc&callback=googleMapJsReady',
        array() // dependencies
    );
 
}

function leadermap_handler($attrs, $content, $tag) {
    // https://kinsta.com/blog/wordpress-shortcodes/
    // attrs -> Array of attributes or an empty string
    // content -> the enclosed content (available for enclosing shortcodes like [gist]5698283[/gist] only)
    // tag -> the name of the shortcode, useful for shared callback functions
    // Must return a string of HTML
    
    // Meta filters: https://metabox.io/get-posts-by-custom-fields-in-wordpress/
    $areas = get_posts(array(
			'post_type' => 'areas',
            'numberposts' => 10000,
            'meta_query' => array(
                array(
                    'key' => 'summit',
                    'value' => '',
                    'compare' => '!='
                )
            )
        ));
        
    $routesNotInArea = get_posts(array(
        'post_type' => 'routes',
        'numberposts' => 10000,
        'meta_query' => array(
            array(
                'key' => 'summit',
                'value' => '',
                'compare' => '!='
            ),
            array(
                'key' => 'parent',
                'value' => NULL
            )
        )
    ));
        
    $jsData = array();
    foreach ($areas as $area) {
        $area = new Area($area);
        $fields = $area->getFields();
        if ($fields->summit->hasValue()) {
            $yds_class = 2;
            $htmlPreview = "";
            $childRoutes = $area->getRoutes();
            if (sizeof($childRoutes) > 0) {
                foreach ($childRoutes as $child) {
                    $childFields = $child->getFields();
                    if ($childFields->yds_class->hasValue()) {
                        $childYdsClass = intval($childFields->yds_class->value);
                        if ($childYdsClass > $yds_class) {
                            $yds_class = $childYdsClass;
                        }
                    }
                }
            }
            $htmlPreview = getHtml($area, 'details.php');
            $jsData[] = array(
                'name' => $area->post->post_title,
                'position' => array(
                    'lat' => floatval($fields->summit->value['lat']),
                    'lng' => floatval($fields->summit->value['lng'])
                ),
                'htmlPreview' => $htmlPreview,
                'windowHtmlPreview' => getHtml($area, 'window-preview.php'),
                'yds_class' => $yds_class
            );
        }
    }
    
    foreach ($routesNotInArea as $route) {
        $route = new Route($route);
        $fields = $route->getFields();
        if ($fields->summit->hasValue()) {
            $yds_class = 2;
            $htmlPreview = "";
            if ($fields->yds_class->hasValue()) {
                $yds_class = intval($fields->yds_class->value);
            }
            $htmlPreview .= getHtml($route, 'details.php');
            $jsData[] = array(
                'name' => $route->getListTitle(),
                'position' => array(
                    'lat' => floatval($fields->summit->value['lat']),
                    'lng' => floatval($fields->summit->value['lng'])
                ),
                'htmlPreview' => $htmlPreview,
                'windowHtmlPreview' => getHtml($route, 'window-preview.php'),
                'yds_class' => $yds_class
            );
        }
    }
    
    $jsDataJson = json_encode($jsData);

    $answer = '<div class="leader-map-container"><div id="leaderMap"></div><div id="leaderMapDetails">Select an item from the map to view it</div></div><script>initLeaderMap('.$jsDataJson.');</script>';
    return $answer;
}

function getHtmlPreview($postItem) {
    global $post;
    $originalPost = $post;
    $post = $postItem;
    ob_start();
    require $_SERVER['DOCUMENT_ROOT']."/wp-content/themes/blogslog/template-parts/content.php";
    $htmlPreview = ob_get_clean();
    $post = $originalPost;
    return $htmlPreview;
}

function getHtml($entityItem, $template) {
    global $entity;
    $originalEntity = $entity;
    $entity = $entityItem;
    ob_start();
    require 'template-parts/'.$template;
    $htmlPreview = ob_get_clean();
    $entity = $originalEntity;
    return $htmlPreview;
}

add_action( 'init', 'leadermap_init' );
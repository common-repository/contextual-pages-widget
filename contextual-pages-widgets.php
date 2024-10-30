<?php
/*
Plugin Name: Contextual Pages Widget
Plugin URI: http://yelotofu.com/labs/wp/plugins/contextual-pages
Description: The Contextual Pages Widget allows you to display pages in a target depth within the current context tree.
Author: Ca-Phun Ung
Version: 1
Author URI: http://yelotofu.com/
*/

class ContextualPagesWidget extends WP_Widget {

    // widget actual process
    function ContextualPagesWidget() {
        $widget_ops = array('classname' => 'widget_submenus', 'description' => __( 'Use this widget to add pages at a target depth as a widget.') );
        $this->WP_Widget(false, __('Contextual Pages'), $widget_ops);
    }

    // output the content of the widget
    function widget($args, $instance) {
        global $wp_query;

        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $level = intval($instance['level']);
        $container_id = $instance['container_id'];
        $container_class = $instance['container_class'];
        $menu_id = $instance['menu_id'];
        $menu_class = $instance['menu_class'];
        
        if (empty($menu_id)) $menu_id = 'menu-secondary';
        if (empty($menu_class)) $menu_class = 'menu';
        if (empty($container_class)) $container_class = 'menu-secondary-container';

        $current_page = ( is_page() || is_attachment() || $wp_query->is_posts_page ) ? $wp_query->get_queried_object_id() : 0;

        $page = get_page( $current_page );
        $parentId = $page->ancestors[count($page->ancestors) - max($level-1,0)];
        if ($parentId === null) {
            $parentId = $page->ID;
        }
        
        $pages = get_pages('sort_column=menu_order&hierarchical=0&sort_order=asc&child_of='.$parentId.'&parent='.$parentId);
        $menu = '';
        $i = 0;
        
        foreach ($pages as $page) {
            $is_current = $page->ID == $current_page || $this->has_descendant($page, $current_page);
            
            $menu .= '<li id="menu-item-'.++$i.'" class="menu-item"><a href="'. get_page_link($page->ID) .'"'. ($is_current ? ' class="selected"':'') .'>';
            $menu .= $is_current ? '<strong>':'<span>';
            $menu .= $page->post_title;
            $menu .= $is_current ? '</strong>':'</span>';
            $menu .= '</a></li>';
        }

        // render widget
        if ($menu) {
            echo $before_widget;
            echo '<div '. (!empty($container_id) ? 'id="'.$container_id.'"':'') .' class="'.$container_class.'">';
            if ( $title ) {
                echo $before_title . $title . $after_title;
            }
            echo '<ul '. (!empty($menu_id) ? 'id="'.$menu_id.'"':'') .' class="'.$menu_class.'">'. $menu . '</ul>';
            echo '</div>';
            echo $after_widget; 
        }
    }

    // outputs the options form on admin
    function form($instance) {

        $key = 'title';
        print $this->input_tag( array(
            'key' => $key,
            'value' => esc_attr($instance[$key]),
            'text' => _e(ucwords($key).':')
        ) );

        $key = 'level';
        print $this->input_tag( array(
            'key' => $key,
            'value' => esc_attr($instance[$key]),
            'text' => _e('Show pages at depth:'),
        ) );

/*        
        $key = 'container_id';
        print $this->input_tag( array(
            'key' => $key,
            'value' => esc_attr($instance[$key]),
            'text' => _e(ucwords($key).':')
        ) );

        $key = 'container_class';
        print $this->input_tag( array(
            'key' => $key,
            'value' => esc_attr($instance[$key]),
            'text' => _e(ucwords($key).':')
        ) );

        $key = 'menu_id';
        print $this->input_tag( array(
            'key' => $key,
            'value' => esc_attr($instance[$key]),
            'text' => _e(ucwords($key).':')
        ) );

        $key = 'menu_class';
        print $this->input_tag( array(
            'key' => $key,
            'value' => esc_attr($instance[$key]),
            'text' => _e(ucwords($key).':')
        ) );
*/
    }

    function update($new_instance, $old_instance) {
        // process widget options to be saved
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['level'] = intval(strip_tags($new_instance['level']));
        $instance['container_id'] = strip_tags($new_instance['container_id']);
        $instance['container_class'] = strip_tags($new_instance['container_class']);
        $instance['menu_id'] = strip_tags($new_instance['menu_id']);
        $instance['menu_class'] = strip_tags($new_instance['menu_class']);
        return $instance;
    }
    
    // private functions
    
    private function input_tag( $options = array() ) {
        $options = array_merge(array('css_class' => 'widefat'), $options);
        return sprintf (
            '<p><label for="%s">%s</label> <input class="%s" id="%s" name="%s" type="text" value="%s" /></p>', 
            $this->get_field_id($options['key']),
            $options['text'],
            $options['css_class'],
            $this->get_field_id($options['key']),
            $this->get_field_name($options['key']),
            $options['value']
        );
    }
    
    private function has_descendant( $post, $post_id ) {
        $pages = get_pages('child_of='.$post->ID);
        foreach ($pages as $page) {
            if ($page->ID == $post_id) {
                return true;
            }
        }
        return false;
    }

}

add_action('widgets_init', create_function('', 'return register_widget("ContextualPagesWidget");'));
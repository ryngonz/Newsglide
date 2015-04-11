<?php
/*
Plugin Name: Newsglide
Plugin URI: http://www.ryandev.rocks
Description: News ticker with vertical infinite scroll.
Author: Ryan G. Gonzales
Version: 1.0 beta
Author URI: http://www.ryandev.rocks
License: GNU GPL2
*/
/*  
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('wp_enqueue_scripts', 'newsglide_script');
add_action('wp_head','newsglide_script');

function newsglide_script( $template_path ) {
    wp_enqueue_style( 'newsglide-simplyscroll-css', plugin_dir_url( __FILE__ ).'css/jquery.simplyscroll.css' );
    wp_enqueue_style( 'newsglide-css', plugin_dir_url( __FILE__ ).'css/style.css' );
    wp_enqueue_script( 'newsglide-simplyscroll-js', plugin_dir_url( __FILE__ ).'js/jquery.simplyscroll.min.js', array() , '1.0.0', true );
}

// Creating the widget 
class newsglide_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        'newsglide_widget', 
        __('Newsglide Widget', 'newsglide_widget_domain'), 
        array( 'description' => __( 'News ticker with vertical infinite scroll', 'newsglide_widget_domain' ), ) 
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        $newsglide_title = apply_filters( 'widget_title', $instance['newsglide_title'] );
        $newsglide_category = $instance['newsglide_category'];
        $newsglide_height = $instance['newsglide_height'];
        $newsglide_post_num = $instance['newsglide_post_num'];
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $newsglide_title ) ) echo $args['before_title'] . $newsglide_title . $args['after_title'];
        if ( empty( $newsglide_height ) ) $newsglide_height = "auto"; else $newsglide_height = $newsglide_height."px";
        if ( empty( $newsglide_post_num ) ) $newsglide_post_num = 10;

        $style = "height:".$newsglide_height.";'";
        echo '<ul id="newsglide_scroller">';

        query_posts('cat='. $newsglide_category .'&numberposts='.$newsglide_post_num);
        while (have_posts()) : the_post();
            echo '<li>'.get_the_title().'</a>';
        endwhile;
        
        echo "</ul>";
        echo $args['after_widget'];

        echo '<script type="text/javascript">
            (function($) {
                $(function() {
                    $("#newsglide_scroller").simplyScroll({orientation:"vertical",customClass:"vert"});
                });
            })(jQuery);
            </script>';
        echo "<style>.vert{".$style."}</style>";
    }
            
    // Widget Backend 
    public function form( $instance ) {
        $newsglide_title = $instance['newsglide_title'];
        $newsglide_category = $instance['newsglide_category'];
        $newsglide_height = $instance['newsglide_height'];
        $newsglide_post_num = $instance['newsglide_post_num'];

        $args = array(
            'orderby' => 'id',
            'hide_empty'=> 0,
        );

        $categories = get_categories($args);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'newsglide_title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'newsglide_title' ); ?>" name="<?php echo $this->get_field_name( 'newsglide_title' ); ?>" type="text" value="<?php echo esc_attr( $newsglide_title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'newsglide_category' ); ?>"><?php _e( 'Category:' ); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id( 'newsglide_category' ); ?>" name="<?php echo $this->get_field_name( 'newsglide_category' ); ?>">
                <?php 
                    foreach ($categories as $cat) : 
                        $selected = "";
                        if($cat->cat_ID == $newsglide_category) $selected = "selected";
                ?>
                        <option value="<?=$cat->cat_ID?>" <?=$selected?>><?=$cat->name?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'newsglide_post_num' ); ?>"><?php _e( 'Number of Posts:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'newsglide_post_num' ); ?>" name="<?php echo $this->get_field_name( 'newsglide_post_num' ); ?>" type="text" value="<?php echo esc_attr( $newsglide_post_num ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'newsglide_height' ); ?>"><?php _e( 'Widget Height:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'newsglide_height' ); ?>" name="<?php echo $this->get_field_name( 'newsglide_height' ); ?>" type="text" value="<?php echo esc_attr( $newsglide_height ); ?>" />
        </p>
        <?php 
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['newsglide_title'] = ( ! empty( $new_instance['newsglide_title'] ) ) ? strip_tags( $new_instance['newsglide_title'] ) : '';
        $instance['newsglide_category'] = ( ! empty( $new_instance['newsglide_category'] ) ) ? strip_tags( $new_instance['newsglide_category'] ) : '';
        $instance['newsglide_height'] = ( ! empty( $new_instance['newsglide_height'] ) ) ? strip_tags( $new_instance['newsglide_height'] ) : '';
        $instance['newsglide_post_num'] = ( ! empty( $new_instance['newsglide_post_num'] ) ) ? strip_tags( $new_instance['newsglide_post_num'] ) : '';
        return $instance;
    }
}

function newsglide_load_widget() {
    register_widget( 'newsglide_widget' );
}
add_action( 'widgets_init', 'newsglide_load_widget' );

?>
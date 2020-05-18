<?php
/**
 * Plugin Name: Envato Recent Items
 * Description: A widget that displays recent or popular Envato marketplace items. 
 * Version: 1.0
 * Plugin URI: http://me.georgeholmesii.com/freebies/envato-marketplace-widget/
 * Author: George Holmes II
 * Author URI: http://georgeholmesii.com
 */

//License: GPLv2 or later

//  Copyright 2012  George Holmes II  (email : georgeholmesii@gmail.com)

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_action( 'widgets_init', 'envato_widget' );


function envato_widget() {
	register_widget( 'Envato_Widget' );
}

class Envato_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget that displays recent or popular Envato marketplace items ', 'Envato Widget') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'envato-widget' );
		
		parent::__construct( 'envato-widget', __('Envato Widget', 'Envato Widget'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		?>
        <style>
        .envato-thumbnail {
       		width: 100%;	
        }
        
        .envato-link {
            text-align:left;
        }
		
		.envato-row {
			padding-bottom:10px;
			padding-top:10px;	
		}
		
		.envato-title {
			float:left;
			width:30%;
		}
		.envato-thumbnail-container {
			float:left;
			width:40%;
		}
        </style> 
        <?php
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		$marketplace = $instance['marketplace'];
		$envato_category = $instance['envato_category'];
		$envato_username = $instance['envato_username'];
		$item_limit = $instance['item_limit'];

		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;

		//Display the name 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://marketplace.envato.com/api/v1/' . $envato_category . ':' . $marketplace . '.json');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		?>
        	<script>
				console.log("<?php echo 'http://marketplace.envato.com/api/v1/' . $envato_category . ':' . $marketplace . '.json'; ?>");
			</script>
        <?php
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$ch_data = curl_exec($ch);
		curl_close($ch);
		
		if(!empty($ch_data))
		{
			$json_data = json_decode($ch_data, true);
			//print_r($json_data);
			
			$item_limit = $item_limit - 1;
			if($envato_category == "popular")
			{
				$json_short = $json_data['popular']['items_last_week'];
				for($i = 0; $i <= $item_limit; $i++)
				{
					echo '<div class="envato-row" style="">
					<div class="envato-thumbnail-container" align="center" style="padding:10px;"><img class="envato-thumbnail"  src="',$json_short[$i]['thumbnail'],'" alt=""></div>
					<div class="envato-title envato-link" style="padding:10px;"><a target="_blank" href="',$json_short[$i]['url'],'?ref=' . $envato_username . '">',$json_short[$i]['item'],'</a></div>
			
					</div>';
				}
			}
			if($envato_category == "random-new-files")
			{
				for($i = 0; $i <= $item_limit; $i++)
				{
					echo '<div class="envato-row" style="">
					<div class="envato-thumbnail-container" align="center" style="padding:10px;"><img class="envato-thumbnail"  src="',$json_data['random-new-files'][$i]['thumbnail'],'" alt=""></div>
					<div class="envato-title envato-link" style="padding:10px;"><a target="_blank" href="',$json_data['random-new-files'][$i]['url'],'?ref=' . $envato_username . '">',$json_data['random-new-files'][$i]['item'],'</a></div>
			
					</div>';
				}
			}
			
		
		}
		else
		{
			echo 'Sorry, but there was a problem connecting to the API.';
		}


		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['marketplace'] = strip_tags( $new_instance['marketplace'] );
		$instance['envato_username'] = strip_tags( $new_instance['envato_username'] );
		$instance['envato_category'] = strip_tags( $new_instance['envato_category'] );
		$instance['item_limit'] = strip_tags( $new_instance['item_limit'] );

		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 
			'title' => __('Envato Marketplace', 'Envato Marketplace'), 
			'marketplace' => __('themeforest', 'themeforest'), 
			'envato_username' => __('username', 'username'),
			'envato_category' => __('recent', 'recent'),
			'item_limit' => __('3', '3'),
			'show_info' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'Envato Marketplace'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
        
        <p>
			<label for="<?php echo $this->get_field_id( 'envato_username' ); ?>">Envato Username</label>
			<input id="<?php echo $this->get_field_id( 'envato_username' ); ?>" name="<?php echo $this->get_field_name( 'envato_username' ); ?>" value="<?php echo $instance['envato_username']; ?>" style="width:100%;" />
		</p>
        

		<p>
            <label for="<?php echo $this->get_field_id( 'marketplace' ); ?>">
           		Marketplace
            </label>
            <select id="<?php echo $this->get_field_id('marketplace'); ?>" name="<?php echo $this->get_field_name('marketplace'); ?>" class="widefat" style="width:100%;">
                <option <?php selected( $instance['marketplace'], 'themeforest'); ?> value="themeforest">Themeforest</option>
                <option <?php selected( $instance['marketplace'], 'codecanyon'); ?> value="codecanyon">Codecanyon</option>
                <option <?php selected( $instance['marketplace'], 'graphicriver'); ?> value="graphicriver">Graphicriver</option>
                <option <?php selected( $instance['marketplace'], 'activeden'); ?> value="activeden">Activeden</option>
                <option <?php selected( $instance['marketplace'], 'audiojungle'); ?> value="audiojungle">Audiojungle</option>
                <option <?php selected( $instance['marketplace'], 'videohive'); ?> value="videohive">Videohive</option>
                <option <?php selected( $instance['marketplace'], '3docean'); ?> value="3docean">3Docean</option>
                <option <?php selected( $instance['marketplace'], 'photodune'); ?> value="photodune">Photodune</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'envato_category' ); ?>">
           		Recent or Popular
            </label>
            <select id="<?php echo $this->get_field_id('envato_category'); ?>" name="<?php echo $this->get_field_name('envato_category'); ?>" class="widefat" style="width:100%;">
                <option <?php selected( $instance['envato_category'], 'random-new-files'); ?> value="random-new-files">Recent</option>
                <option <?php selected( $instance['envato_category'], 'popular'); ?> value="popular">Popular</option>
            </select>
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'item_limit' ); ?>">Amount of Items to Display</label>
			<input id="<?php echo $this->get_field_id( 'item_limit' ); ?>" name="<?php echo $this->get_field_name( 'item_limit' ); ?>" value="<?php echo $instance['item_limit']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

?>
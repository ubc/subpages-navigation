<?php
/**
 * Plugin Name: Subpages Navigation
 * Plugin URI: http://ctlt.ubc.ca
 * Description: Create subpages navigation menu with sidebar widgets and shortcodes.
 * Version: 2.0
 * Author: Michael Kam / Enej Bajgoric / Michael Ha / CTLT
 * Author URI: http://blogs.ubc.ca/
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */


/**
 * Add function to widgets_init that'll load our widget.
 * @since 1.0
 */
add_action( 'widgets_init', 'olt_subpages_navigation_load_widgets' );
add_action( 'init', 'init_subpages_navigation_plugin' );


define( 'SUBPAGES_NAVIGATION_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'SUBPAGES_NAVIGATION_BASENAME', plugin_basename( __FILE__ ) );
define( 'SUBPAGES_NAVIGATION_BASE_FILE', __FILE__ );
define( 'SUBPAGES_NAVIGATION_DIR_URL', plugins_url( '', SUBPAGES_NAVIGATION_BASENAME ) );

/**
 * Register our widget.
 * 'olt_subpages_navigation_Widget' is the widget class used below.
 *
 * @since 1.0
 */
function olt_subpages_navigation_load_widgets() {
	register_widget( 'OLT_Subpages_Navigation_Widget' );
}

/**
 * OLT Subpages Navigation Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 1.0
 */
class OLT_Subpages_Navigation_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_subpages_navigation', 'description' => __('A widget that creates a subpages navigation menu.', 'olt_subpages_navigation') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 400, 'height' => 350, 'id_base' => 'olt-subpages-navigation-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'olt-subpages-navigation-widget', __('Subpages Navigation', 'olt_subpages_navigation'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		global $post;
		$using_menu = false;

		if(is_page()):
			/* Extract the arguments  */
			extract( $args );
			extract( $instance);

    	    /* Find the root post */
    	    if($root === '0'): #all pages
    	        $root_id = "0";
    	        $pages = get_pages("sort_column=menu_order");

    		elseif($root == 1): # subpages of the top-level page
    		    $rootPost = $post;
    		    while ($rootPost->post_parent != 0):
    			    $rootPost = get_post($rootPost->post_parent);
    			endwhile;

    			$pages = get_pages("child_of=".$rootPost->ID."&sort_column=menu_order");

    		    if($top_title):
    		    	$title = $rootPost->post_title;
    			endif;
    			if($title_link):
    				$title_link = get_permalink($rootPost->ID);
    			endif;

    		elseif($root == -1): # subpages of the current page


    		    if( !$siblings ) :
    		    	$pages = get_pages("child_of=".$post->ID."&sort_column=menu_order");
    		    	$title_link = get_permalink($post->ID);
    		    else:
    		    	$pages = get_pages("child_of=".$post->post_parent."&sort_column=menu_order");
    		    	$title_link = get_permalink($post->post_parent);
    		    endif;

    		    if($top_title):
    		    	$title = $post->post_title;
    		    	$title_link = get_permalink($post->ID);
    		    endif;
    		else:

    		    if(function_exists('wp_get_nav_menu_items')):
    		    	$pages = wp_get_nav_menu_items(substr($root,5));
    		    	$using_menu=true;
    		    endif;
    		endif;

			if($top_title):
				if($root_id):
				$root_page = get_page($root_id);
				$title = $root_page->post_title;
				$title_link = get_permalink($root_page->ID);
				endif;
			endif;

			/* Our variables from the widget settings. */
			$title = apply_filters('widget_title', $title );

			/* Prepare the walker */

			//$pages = wp_get_nav_menu_items('test-menu'); $menu=true;

			if(is_array($pages) && !empty($pages)):
				/* Before widget (defined by themes). */
				echo $before_widget;

				/* Display the widget title if one was specified (before and after defined by themes). */
				if ( $title ) {

					echo $before_title;

					if ( $title_link ) {

						echo "<a href='". $title_link ."'>". $title ."</a>";

					} else {

						echo $title;

					}

					echo $after_title;
				}

				$theme_accordion_support = '';
				if (is_array(get_theme_support('accordions'))) {
					$theme_accordion_support = get_theme_support( 'accordions' );
					$theme_accordion_support = reset( $theme_accordion_support );
				}

				// UBC CLF style side navigation
				if ( $theme_accordion_support == 'twitter-bootstrap' ) {
	    			$walker = new CLFSubpagesNavigationPageList($using_menu, $post->ID );

					$classes = 'accordion sidenav simple subpages-navi subpages-navi-widget';

		    		if($exclusive) {
		    			$classes .= ' subpages-navi-exclusive';
						$walker->set_exclusive(true);
					}

		    		if($collapsible) {
		    			$classes .= ' subpages-navi-collapsible';
						$walker->set_collapsible(true);
					}

		    		if($expand) {
		    			$classes .= ' subpages-navi-auto-expand';
						$walker->set_expand(true);
					}

		    		$depth = ($nested)? '3' : '-1';

		    		$unique_id = $args['widget_id'];
		    		?>

		            <div class="<?php echo $classes; ?>" id="parent-<?php echo $unique_id; ?>0">
		                <?php echo $walker->walk($pages, $depth, array('current_level' => $post->ID, 'unique_key' => $unique_id)); ?>
		            </div>
		            <?php
				}
				else {
					$walker = new SubpagesNavigationPageList($using_menu);

					$classes = 'subpages-navi subpages-navi-widget';

		    		if($exclusive)
		    			$classes .= ' subpages-navi-exclusive';

		    		if($collapsible)
		    			$classes .= ' subpages-navi-collapsible';

		    		if($expand)
		    			$classes .= ' subpages-navi-auto-expand';

		    		$depth = ($nested)? '0' : '-1';

		    		?>
		            <ul class="<?php echo $classes; ?>">
		                <?php echo $walker->walk($pages, $depth, array('current_level' => $post->ID)); ?>
		            </ul>

				<?php
				}
				/* After widget (defined by themes). */
				echo $after_widget;

				 wp_enqueue_script( 'subpages-navigation' );
			endif;

		endif;

	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title to remove HTML . */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* No need to strip tags  */
		$instance['top_title'] = $new_instance['top_title'];
		$instance['root'] = $new_instance['root'];
		//$instance['menu'] = $new_instance['menu'];
		$instance['title_link'] = $new_instance['title_link'];
		$instance['siblings'] = $new_instance['siblings'];
		$instance['nested'] = $new_instance['nested'];

		$instance['collapsible'] = $new_instance['collapsible'];
		$instance['exclusive'] = $new_instance['exclusive'];
		$instance['expand'] = $new_instance['expand'];


		return $instance;

	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

	/* Our Variables set by the form */
		$defaults = array(
		'title' => 'Navigation',
		'title_link' =>false,
		'top_title'=>true,
		'root' => -1,
		'siblings' => false,
		'nested' => true,
		'collapsible' => true,
		'exclusive' =>true,
		'expand' =>true,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );



		$dt = ' disabled="disabled" style="background-color: #ccc"';
		$dm = ' disabled="disabled" style="color: #999"';
		 ?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" <?php if($instance['top_title']) echo $dt; ?> class="olt-subpages-title" />

			<input type="checkbox" name="<?php echo $this->get_field_name( 'top_title' ); ?>" id="<?php echo $this->get_field_id( 'top_title' ); ?>" value="true" <?php checked($instance['top_title'], "true"); ?> class="olt-subpages-top-title" />
                <label for="<?php echo $this->get_field_id( 'top_title' ); ?>"><?php _e('Use title of root page', 'olt_subpages_navigation'); ?></label>

			<input class="olt-subpages-title-link" type="checkbox" name="<?php echo $this->get_field_name( 'title_link'); ?>" id="<?php echo $this->get_field_id('title_link'); ?>" <?php if(!$instance['top_title']) echo $dm; ?>   value="true" <?php checked($instance['title_link'], "true"); ?> />
			<label class="olt-subpages-title-link" for="<?php echo $this->get_field_id('title_link'); ?>" <?php if(!$instance['top_title']) echo $dm; ?>  ><?php _e('Link the title page?', 'olt_subpages_navigation'); ?></label>

		</p>


		<!-- Root: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'root' ); ?>"><?php _e('Show:', 'olt_subpages_navigation'); ?></label>
			<select id="<?php echo $this->get_field_id( 'root' ); ?>" name="<?php echo $this->get_field_name( 'root' ); ?>" class="olt-subpages-root">
			<option value="-1" <?php selected($instance['root'], -1); ?>><?php _e('subpages of the current page', 'olt_subpages_navigation'); ?></option>
			<option value="0"  <?php selected($instance['root'], 0); ?>><?php _e('all pages', 'olt_subpages_navigation'); ?></option>
			<option value="1" <?php selected($instance['root'], 1); ?>><?php _e('subpages of the top-level page', 'olt_subpages_navigation'); ?></option>

			<?php
				$menus = get_terms('nav_menu');
				if(count($menus)):

					echo '<option value="" disabled="disabled">-- Custom Nav Menus--</option> \n';

					foreach($menus as $menu):
					  echo '<option value="menu_' . $menu->name . '"' . selected($instance['root'], 'menu_'.$menu->name) . '>' . $menu->name . '</option> \n';
					endforeach;
				endif;
			?>

			</select>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'siblings' ); ?>" id="<?php echo $this->get_field_id( 'siblings' ); ?>" value="true" <?php checked($instance['siblings'], "true"); ?> <?php if($instance['root'] != -1) echo $dm; ?> class="olt-subpages-siblings"/>
            <label for="<?php echo $this->get_field_id( 'siblings' ); ?>" <?php if($instance['root'] != -1) echo $dm; ?> class="olt-subpages-siblings"><?php _e('and its siblings', 'olt_subpages_navigation'); ?></label>

		</p>


		<!-- Children nested? Checkbox -->
		<p>
			<input class="checkbox olt-subpages-nested" type="checkbox" <?php checked( $instance['nested'], "on" ); ?> id="<?php echo $this->get_field_id( 'nested' ); ?>" name="<?php echo $this->get_field_name( 'nested' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'nested' ); ?>"><?php _e('Nest children pages under their parents?', 'olt_subpages_navigation'); ?></label>
		</p>

		<!-- List Collapsible? Checkbox -->
		<p>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input class="checkbox olt-subpages-nested-options" type="checkbox" <?php checked( $instance['collapsible'], "on" ); ?> <?php if(!$instance['nested']) echo $dm; ?> id="<?php echo $this->get_field_id( 'collapsible' ); ?>" name="<?php echo $this->get_field_name( 'collapsible' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'collapsible' ); ?>" <?php if(!$instance['nested']) echo $dm; ?> class="olt-subpages-nested-options"><?php _e('Make the list collapsible?', 'olt_subpages_navigation'); ?></label>
		</p>


		<!-- Exclusive selection? Checkbox -->
		<p>
		    &nbsp;&nbsp;&nbsp;&nbsp;
			<input class="checkbox olt-subpages-nested-options" type="checkbox" <?php checked( $instance['exclusive'], "on" ); ?> id="<?php echo $this->get_field_id( 'exclusive' ); ?>" name="<?php echo $this->get_field_name( 'exclusive' ); ?>" class="olt-subpages-nested-options" <?php if(!$instance['nested']) echo $dm; ?> />
			<label for="<?php echo $this->get_field_id( 'exclusive' ); ?>" <?php if(!$instance['nested']) echo $dm; ?> class="olt-subpages-nested-options"><?php _e('Exclusive selection (Accordion style)', 'olt_subpages_navigation'); ?></label>
		</p>

		<!-- Auto expand? Checkbox -->
		<p>
		    &nbsp;&nbsp;&nbsp;&nbsp;
			<input class="checkbox olt-subpages-nested-options" type="checkbox" <?php if(!$instance['nested']) echo $dm; ?> <?php checked( $instance['expand'], "on" ); ?> id="<?php echo $this->get_field_id( 'expand' ); ?>" name="<?php echo $this->get_field_name( 'expand' ); ?>" class="olt-subpages-nested-options" />
			<label for="<?php echo $this->get_field_id( 'expand' ); ?>" <?php if(!$instance['nested']) echo $dm; ?> class="olt-subpages-nested-options"><?php _e('Automatically expand the current level', 'olt_subpages_navigation'); ?></label>
		</p>
	<?php





	}
}


add_shortcode('subpages', 'subpages_navigation_shortcode');
/*
 * Subpage navigation shortcode
 *********************************************/
function subpages_navigation_shortcode($atts) {
        global $post;

        wp_enqueue_script( 'subpages-navigation' );

        $using_menu=false;
        extract(shortcode_atts(array(
		    'depth' => '0',
		    'siblings' => false,
		    'collapsible' => true,
		    'exclusive' => false,
		    'expand' => false,
		    'menu' => '0',
	    ), $atts));

	    // Get all subpages of the current page
	    $root = ($siblings == 'true')? $post->post_parent : $post->ID;
	    if($menu):
	    	$using_menu=true;
	    	$pages = wp_get_nav_menu_items($menu);

	    else:
	    	$pages = get_pages("child_of={$root}&sort_column=menu_order");
	    endif;

	    // Prepare the walker
	    $theme_accordion_support = get_theme_support( 'accordions' );
	    $theme_accordion_support = reset( $theme_accordion_support );

		// UBC CLF style side navigation
		if ( $theme_accordion_support == 'twitter-bootstrap' ) {
			$walker = new CLFSubpagesNavigationPageList( $using_menu );

			$classes = 'accordion sidenav simple subpages-navi subpages-navi-widget';


    		if ( (is_bool($exclusive) && $exclusive) || (is_string($exclusive) && strcasecmp($exclusive, "true") == 0) ){
    			$classes .= ' subpages-navi-exclusive';
				$walker->set_exclusive(true);
			}

    		if ( (is_bool($collapsible) && $collapsible) || (is_string($collapsible) && strcasecmp($collapsible, "true") == 0) ) {
    			$nested = true;
    			$classes .= ' subpages-navi-collapsible';
				$walker->set_collapsible(true);
			}

    		if ( (is_bool($expand) && $expand) || (is_string($expand) && strcasecmp($expand, "true") == 0) ) {
    			$classes .= ' subpages-navi-auto-expand';
				$walker->set_expand(true);
			}

			// Check if table should be collapsible
    		$depth = ($nested)? '3' : '-1';

			$unique_key = mt_rand();

			$output = '<div class="'.$classes.'" id="parent-'.$unique_key.'0">';
            $output .= $walker->walk($pages, $depth, array('current_level' => $post->ID, 'unique_key' => $unique_key));
            $output .= "</div>\n";
		} else {
	        $walker = new SubpagesNavigationPageList($using_menu);

	        $output  = '<ul class="subpages-navi subpages-navi-shortcode';
	        if($collapsible == 'true')
	            $output .= ' subpages-navi-collapsible';
	        if($exclusive == 'true')
	            $output .= ' subpages-navi-exclusive';
	        if($expand == 'true')
	            $output .= ' subpages-navi-auto-expand';
	        $output .= "\">\n";
	        $output .= $walker->walk($pages, (int) $depth, array('current_level' => $post->ID));
	        $output .= "</ul>\n";
		}
	    return $output;
    }
add_shortcode('subpages-next', 'subpages_navigation_next_shortcode');
/**
 * Subpages next shortcode displays the next and previous links of the siblings childpages.
 *
 * @param  array $atts Array of attributes that are passed into the shortcode.
 * @return string      html
 */
function subpages_navigation_next_shortcode( $atts ) {

	if( !is_page() )
		return;

	global $post;

	extract( shortcode_atts( array(
		'exclude' => '',
	), $atts ) );


	if( $post->post_parent ) {
		$current_id = $post->ID;
		$args = array(
			'post_parent'  => $post->post_parent,
			'post_type' => array( 'page' ),
			//Order & Orderby Parameters
			'order'               => 'ASC',
			'orderby'             => 'menu_order',
		);

			if( !empty($exclude) )
				$args['post__not_in'] = explode( ',', $exclude );


		$query_subpages = new WP_Query( $args );
		$counter = 0;

		while ( $query_subpages->have_posts() ) : $query_subpages->the_post();

			$subpages_data[] = array(
				'id' 		=> get_the_id(),
				'permalink'	=> get_permalink(),
				'title'		=> get_the_title( ),
				''
			);
			if( $current_id == get_the_id() )
   				$current_counter = $counter;

   			$counter++;

  		endwhile;
  		wp_reset_postdata();
		$previous_link = $next_link = false;

  		if( isset( $current_counter ) ) {
  			if( isset( $subpages_data[$current_counter - 1 ]  ) ) {
  				$link = $subpages_data[$current_counter - 1 ];
  				$previous_link = '<a rel="prev" href="'. esc_url( $link['permalink'] ) .'"><i class="icon-chevron-left icon"></i> '.$link['title'].'</a>';
  			}

  			if( isset( $subpages_data[$current_counter + 1 ]  ) ) {
  				$link = $subpages_data[$current_counter + 1 ];
  				$next_link = '<a rel="next" href="'. esc_url( $link['permalink'] ) .'">'.$link['title'].' <i class="icon-chevron-right icon"></i></a>';
  			}

  		}

  		if( $previous_link || $next_link ) {
  			$html = '<ul class="pager">';
  			if( $previous_link )
  				$html .= '<li class="previous">'.$previous_link.'</li>';
  			if( $next_link )
  				$html .= '<li class="next">'.$next_link.'</li>';
  			$html .= '</ul>';
  		}

		return $html;
	}

	return ;

}
add_shortcode('subpages-progress', 'subpages_navigation_progressbar_shortcode');
/**
 * Subpages progressbar shortcode displays the next and previous links of the siblings childpages.
 *
 * @param  array $atts Array of attributes that are passed into the shortcode.
 * @return string      html
 */
function subpages_navigation_progressbar_shortcode( $atts ) {

	if( !is_page() )
		return;

	global $post;

	extract( shortcode_atts( array(
		'exclude' => '',
		'type'	  => '',
	), $atts ) );

	$in_array = array();

	if( is_array($atts) ) {
		foreach( $atts as $key => $attr_value ) {
			if( is_numeric( $key ) )
				$in_array[] = $attr_value;
		}
	}


		$collapsed  	= in_array( 'collapsed', $in_array   ) ? true : false;

	if( !empty( $type ) )
		$type = 'progress-'.$type;

	if( $post->post_parent ) {
		$current_id = $post->ID;
		$args = array(
			'post_parent'  => $post->post_parent,
			'post_type' => array( 'page' ),
			'order'               => 'ASC',
			'orderby'             => 'menu_order',
		);

			if( !empty($exclude) )
				$args['post__not_in'] = explode( ',', $exclude );


		$query_subpages = new WP_Query( $args );
		$counter = 0;

		while ( $query_subpages->have_posts() ) : $query_subpages->the_post();
   			$counter++;
   			if( $current_id == get_the_id() )
   				$current_counter = $counter;



  		endwhile;
  		wp_reset_postdata();

		if( isset( $current_counter ) ) {
  			$progress = $current_counter / $counter * 100;
  			$counter_text = $inner_text = '';

  			if( 100 != $progress && in_array( 'include_text', $in_array ) )
  				$counter_text = '&nbsp;'.$current_counter.' / '.$counter;
  			else if( in_array( 'include_text' , $in_array ) )
  				$inner_text = '&nbsp;'.$current_counter.' / '.$counter;

  			$html = '<div class="progress '.esc_attr( $type ).'"><div class="bar" style="width: '.$progress.'%">'.$inner_text.'</div>'.$counter_text.'</div>';

  		}

		return $html;
	}

	return ;

}
/**
 * init_subpages_navigation_plugin function.
 *
 * @access public
 * @return void
 */
function init_subpages_navigation_plugin()
{
	$collab_script = false;

    // Check if script needed
    if (is_array(get_theme_support('accordions'))) {
    	$theme_accordtion_support = get_theme_support('accordions');
    	$theme_accordion_support = reset($theme_accordtion_support);
	}

    if ($theme_accordion_support == "twitter-bootstrap") {
        $collab_script = true;
	}

	if (!is_admin()) {
        if ($collab_script) {
            wp_register_script('subpages-navigation', SUBPAGES_NAVIGATION_DIR_URL.'/subpages-navigation-ubc-collab.js', array('jquery'), 1, true );
		} else {
			wp_register_script('subpages-navigation', SUBPAGES_NAVIGATION_DIR_URL.'/subpages-navigation.js', array('jquery'), 1, true );
		}

		if( defined('SUBPAGE_NAVIGATION_STYLE') && SUBPAGE_NAVIGATION_STYLE ){
			if (file_exists(STYLESHEETPATH."/subpages-navigation.css") )
			{
				wp_enqueue_style('subpages-navigation', get_bloginfo('stylesheet_directory').'/subpages-navigation.css');

			}else{
				wp_enqueue_style('subpages-navigation', SUBPAGES_NAVIGATION_DIR_URL.'/subpage-navigation.css');
			}
		}
	}


	load_plugin_textdomain( 'olt_subpages_navigation', false , basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action("admin_print_scripts-widgets.php","subpages_navigation_plugin_admin");
function subpages_navigation_plugin_admin(){
	wp_enqueue_script('subpages-navigation-admin', SUBPAGES_NAVIGATION_DIR_URL.'/subpages-navigation-admin.js' ,array('jquery'));

}

class SubpagesNavigationPageList extends Walker {
    var $tree_type = 'page';
    //var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');
    var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');
    var $menu;

    function __construct($menu = false){

    	if($menu):
    		//If we're using a menu we need to retrieve slightly different info from the object we're traversing.
    		$this->menu=true;
    		$this->db_fields['parent'] = 'menu_item_parent';
    	endif;
    }

    public function start_lvl(&$output, $depth = 0, $args=array()) {

        $indent  = str_repeat("    ", $depth+1);
        $output .= $indent."<ul class='children'>\n";
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent  = str_repeat("    ", $depth+1);
        $output .= $indent."</ul>\n";
    }

    function start_el(&$output, $page, $depth = 0, $args = array(), $current_object_id = 0) {
        extract($args);

        if($this->menu):
        	$title = esc_html($page->title);
        	$link = $page->url;
        else:
        	$title = esc_html($page->post_title);
        	$link  = get_permalink($page->ID);
        endif;

        $indent  = str_repeat("    ", $depth)."  ";
        $output .= $indent."<li class=\"subpages-navi-node subpages-navi-level-$depth";
        if( $this->menu ):
			if ($current_level == $page->object_id):
				$output .= ' subpages-navi-current-level';
			endif;
		else:
			if ($current_level == $page->ID):
				$output .= ' subpages-navi-current-level';
			endif;
		endif;

        $output .= "\">\n";
        $output .= $indent."  <a href=\"$link";
        if ($lightbox == true)
            $output .= "?iframe=true&amp;width=600&amp;height=400\" rel=\"prettyPhoto[iframes]";
        $output .= "\">$title</a>\n";
    }

    function end_el(&$output, $pages, $depth = 0, $args = array()) {
        $indent  = str_repeat("    ", $depth)."  ";
        $output .= $indent."</li>\n";
    }
}

class CLFSubpagesNavigationPageList extends Walker {

	var $tree_type = 'page';
    //var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');
    var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');
    var $menu;
	var $parentID, $main_parentID, $pre_parentID;
	var $level = 1;
	var $prev_level = 1;
	var $exclusive = false;
	var $collapsible = false;
	var $expand = false;

    function __construct($menu = false, $parentID = null){
    	global $post;
    	$parentID = ( is_null($parentID) ? $post->ID : $parentID);
    	if($menu):
    		//If we're using a menu we need to retrieve slightly different info from the object we're traversing.
    		$this->menu=true;
    		$this->db_fields['parent'] = 'menu_item_parent';
    	endif;
		$this->parentID = $this->main_parentID = 0;
    }

    function start_lvl(&$output, $depth = 0, $args = array()) {
    	$in = "";
		extract($args);

        $indent  = str_repeat("    ", $depth+1);
        // Force open on parameter collapsible and open if parent is selected
        if (!$this->collapsible || (isset($this->opened_parent) && ($this->parentID == $this->opened_parent)) )
			$in = " in";

        $output .= $indent."<div id='accordion-".$unique_key.$this->parentID."' class='accordion-body collapse".$in."'>\n";
		$output .= $indent."<div class='accordion-inner'>\n";
    }

    function end_lvl(&$output, $depth = 0, $args = array()) {
        $indent  = str_repeat("    ", $depth+1);

        $output .= $indent."</div><!-- end_inner -->\n</div><!-- end_body -->\n";
    }

    function start_el(&$output, $page, $depth = 0, $args = array(), $current_object_id = 0) {
    	$end_div = false;

        extract($args);

        if($this->menu):
        	$title = esc_html($page->title);
        	$link = $page->url;
			$current_id = $page->object_id;
			if (!isset($this->current_id_list)) {
				$this->current_id_list = array();
			}
			while (in_array($current_id, $this->current_id_list)) {
				$current_id += rand(0, 100);
			}
			array_push($this->current_id_list, $current_id);
        else:
        	$title = esc_html($page->post_title);
        	$link  = get_permalink($page->ID);
			$current_id = $page->ID;
        endif;

		// Prepare class if expand is set to true
		$expand_parameter = "";
		if ($this->expand) {
			if ($current_level==$current_id) {
				$expand_parameter = " opened";
				$this->opened_parent = $current_id;
			}
		}

        $indent  = str_repeat("    ", $depth)."  ";
		if ($has_children && $depth < 2) {

			if ($this->level == 1) {
				$accordion_group = 0;
			} elseif (empty($this->last_entry) || ($this->level != $this->last_entry)) {
				// NEW ID
				$rand = rand(0,100);
				if (!isset($this->rand_list)) {
					$this->rand_list = array();
				}
				while (in_array($rand, $this->rand_list)) {
					$rand = rand(0, 100);
				}

				array_push($this->rand_list, $rand);

				$this->current_unique = $rand;
				$this->last_entry = $this->level;
				$accordion_group = $this->current_unique;
			}
			else {
				$accordion_group = $this->current_unique;
			}
			// Set the right accordion group ID
			// if ($page->post_parent == 0)
				// $accordion_group = $this->main_parentID;
			// else {
				// $accordion_group = $page->post_parent;
			// }



			// Parent tag
			$id_tag = "parent-";

			// Set parent class, require for exclusivity
			if ($this->level > 1) {

				//if ($this->parentID == $page->post_parent) {
				if ($this->level != $this->prev_level) {
					$output .= $indent."<!-- New Accordion ".$accordion_group." -->\n";

					$output .= $indent."<div class='accordion' id='".$id_tag.$unique_key.$accordion_group."'>";

					$this->parentID = $current_id;
				}
			}
			else {
				// Update parentID to reflect main parent
				$this->parentID = $this->main_parentID;
			}

			$output .= $indent."<!-- Parent $current_id -->\n";
		 	$output .= $indent."<div class='accordion-group'>\n";
			$output .= $indent. "<div class='accordion-heading subpages-navi-node supages-navi-level-$depth".$expand_parameter."'>\n";
			$this->level++ ;

			// Set parameter for exclusivity option
			$exclusive_parameter = ($this->exclusive)? "data-parent='#".$id_tag.$unique_key.$accordion_group."' ":"";

			$output .= $indent."<a class='accordion-toggle' data-toggle='collapse' ".$exclusive_parameter."href='#accordion-".$unique_key.$current_id."'><div class='ubc7-arrow down-arrow'></div></a>\n";

			// Set new parent to current page
			if ($this->level > 1) {
				$this->parentID = $current_id;
			}

			$arrow = "";
			$link_class = "link";
			$end_div = true;
		}
		else {
			$arrow = "<div class='ubc7-arrow right-arrow'></div> ";
			$link_class = "";
		}
        // $output .= $indent."<li class=\"subpages-navi-node subpages-navi-level-$depth";
        // if( $this->menu ):
			// if ($current_level == $page->object_id):
				// $output .= ' subpages-navi-current-level';
			// endif;
		// else:
			// if ($current_level == $page->ID):
				// $output .= ' subpages-navi-current-level';
			// endif;
		// endif;
//
        // $output .= "\">\n";

        if ($this->level == 1) {
        	$output .= $indent. "<div class='single'>\n";
			$end_div = true;
		}

        $output .= $indent. "<a ";
        if (!empty($link_class) || !empty($expand_parameter)) {
        	$output .= "class='".$link_class.$expand_parameter."' ";
		}
        $output .= "href='".$link."'>".$arrow.$title."</a>\n";
		if ($end_div) {
			$output .= $indent. "</div>\n<!-- Close of single/Head -->";
		}
        // $output .= $indent."  <a href=\"$link";
        // if ($lightbox == true)
            // $output .= "?iframe=true&amp;width=600&amp;height=400\" rel=\"prettyPhoto[iframes]";
        // $output .= "\">$title</a>\n";

    }

    function end_el(&$output, $page, $depth = 0, $args = array()) {
        $indent  = str_repeat("    ", $depth)."  ";
        //$output .= $indent."</li>\n";
        // Max depth of Three Levels;

		if ($args['has_children'] && $depth < 2) {
				$output .= $indent."</div>\n<!-- close level -->";

			$this->level--;

			if ($this->prev_level > $this->level ) {
				$output .= $indent."</div>";
				$output .= $indent. "<!-- close of accordion-group -->";
			}
			$this->prev_level = $this->level;
		}
    }

	function set_exclusive($on) {
		$this->exclusive = $on;
	}
	function set_expand($on) {
		$this->expand = $on;
	}
	function set_collapsible($on) {
		$this->collapsible = $on;
	}
}

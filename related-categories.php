<?php
/**
 * Post related categories
 */
class Post_Related_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'Post_Related_categories', 'description' => __( "Displays Post Related Categories" ) );
		parent::__construct('post_related_categories', __('Post Related Categories'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base);
		
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'hierarchical' => false);

?>

		<ul>
<?php
		$cat_args['title_li'] = '';
		if(is_category()){
			$queried_object = get_queried_object();
			//var_dump($queried_object);
			$categories = $this->get_related_categories($queried_object);
			foreach($categories as $category => $link) :
?>
				<li class="cat-item"> <a href="<?php echo $link?>"> <?php echo $category; ?> </a> </li>
<?php				
			endforeach;
		}
		elseif(is_single()){
			global $post;
			$categories = get_the_category($post->ID);
			//var_dump($categories);
			if($categories){
				$top_level_parents = array();
				$children = array();
				foreach($categories as $cat){
					$top_level_parents[] = $this->pa_category_top_parent_id($cat);
				}
				
				//var_dump($top_level_parents);
				
				$top_level_parents = array_unique($top_level_parents);
				
				foreach($top_level_parents as $parent){
					$children_bulk = get_term_children($parent, 'category');
					foreach($children_bulk as $ch){
						$children[] = $ch;
					}
					$children[] = $parent;
				}
				
				//var_dump($children);
				
				$details = array();
				foreach($children as $child){
					$child_cat = get_category($child);
					$details[$child_cat->name] = get_category_link($child); 
				}	
				
				ksort($details);
				foreach($details as $key => $d){
					echo "<li class='cat-item'> <a href='$d'> $key </a> </li>" ;
				}			
			}
			
		}
		else{
			wp_list_categories(apply_filters('widget_categories_args', $cat_args));
		}
?>
		</ul>
<?php
		

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);		
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
<?php
	}
	
	
	/*
	 * get the parent category
	 * */
	function get_related_categories($term){
		//$categories = array();
		$term_id = $term->term_id;
		$top_level_parent = $this->pa_category_top_parent_id($term_id);
		//$ancestors = get_ancestors($term_id, 'category');
		$children = get_term_children($top_level_parent, 'category');
		//var_dump($children);
		$categories = array_merge(array($top_level_parent), $children);
		//var_dump($categories);
		
		/*
		var_dump($categories);
		var_dump($ancestors);
		var_dump($children);
		
		$cat_details = get_category(6);
		var_dump($cat_details);
		*/ 
		$details = array();
		
		foreach($categories as $cat_id){
			$cat = get_category($cat_id);
			$details[$cat->name] = get_category_link($cat_id); 
		}
		
		ksort($details);
		return $details;
	}
	
	
	/**
	* Returns ID of top-level parent category, or current category if you are viewing a top-level
	*
	* @param    string      $catid      Category ID to be checked
	* @return   string      $catParent  ID of top-level parent category
	*/
	function pa_category_top_parent_id ($catid) {
	 while ($catid) {
	  $cat = get_category($catid); // get the object for the catid
	  $catid = $cat->category_parent; // assign parent ID (if exists) to $catid
	  // the while loop will continue whilst there is a $catid
	  // when there is no longer a parent $catid will be NULL so we can assign our $catParent
	  $catParent = $cat->cat_ID;
	 }
	return $catParent;
	}
}

add_action('widgets_init', 'initialize_custom_widgets');
function initialize_custom_widgets(){
	register_widget('Post_Related_Categories');
}

<?php
	function smartform_setup_post_types()
		{
		    /**
		     * Post types State
		     */
		 $labels = array(
			'name'               => _x( 'States', 'post type general name', 'smartform' ),
			'singular_name'      => _x( 'State', 'post type singular name', 'smartform' ),
			'menu_name'          => _x( 'States', 'admin menu', 'smartform' ),
			'name_admin_bar'     => _x( 'State', 'add new on admin bar', 'smartform' ),
			'add_new'            => _x( 'Add New', 'state', 'smartform' ),
			'add_new_item'       => __( 'Add New State', 'smartform' ),
			'new_item'           => __( 'New State', 'smartform' ),
			'edit_item'          => __( 'Edit State', 'smartform' ),
			'view_item'          => __( 'View State', 'smartform' ),
			'all_items'          => __( 'All States', 'smartform' ),
			'search_items'       => __( 'Search States', 'smartform' ),
			'parent_item_colon'  => __( 'Parent States:', 'smartform' ),
			'not_found'          => __( 'No states found.', 'smartform' ),
			'not_found_in_trash' => __( 'No states found in Trash.', 'smartform' )
		);

		$args = array(
			'labels'             => $labels,
		            'description'        => __( 'Description.', 'smartform' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'state' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title')
		);

		register_post_type( 'state', $args );
		
		$labels = array(
			'name'               => _x( 'Area', 'post type general name', 'smartform' ),
			'singular_name'      => _x( 'Area', 'post type singular name', 'smartform' ),
			'menu_name'          => _x( 'Area', 'admin menu', 'smartform' ),
			'name_admin_bar'     => _x( 'Area', 'add new on admin bar', 'smartform' ),
			'add_new'            => _x( 'Add New', 'area', 'smartform' ),
			'add_new_item'       => __( 'Add New Area', 'smartform' ),
			'new_item'           => __( 'New Area', 'smartform' ),
			'edit_item'          => __( 'Edit Area', 'smartform' ),
			'view_item'          => __( 'View Area', 'smartform' ),
			'all_items'          => __( 'All Areas', 'smartform' ),
			'search_items'       => __( 'Search Areas', 'smartform' ),
			'parent_item_colon'  => __( 'Parent Areas:', 'smartform' ),
			'not_found'          => __( 'No areas found.', 'smartform' ),
			'not_found_in_trash' => __( 'No areas found in Trash.', 'smartform' )
		);

		$args = array(
			'labels'             => $labels,
		            'description'        => __( 'Description.', 'smartform' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'area' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( 'area', $args );

		$labels = array(
			'name'               => _x( 'AC Equipment', 'post type general name', 'smartform' ),
			'singular_name'      => _x( 'AC Equipment', 'post type singular name', 'smartform' ),
			'menu_name'          => _x( 'AC Equipment', 'admin menu', 'smartform' ),
			'name_admin_bar'     => _x( 'AC Equipment', 'add new on admin bar', 'smartform' ),
			'add_new'            => _x( 'Add New', 'acequipment', 'smartform' ),
			'add_new_item'       => __( 'Add New AC Equipment', 'smartform' ),
			'new_item'           => __( 'New AC Equipment', 'smartform' ),
			'edit_item'          => __( 'Edit AC Equipment', 'smartform' ),
			'view_item'          => __( 'View AC Equipment', 'smartform' ),
			'all_items'          => __( 'All AC Equipments', 'smartform' ),
			'search_items'       => __( 'Search AC Equipments', 'smartform' ),
			'parent_item_colon'  => __( 'Parent AC Equipments:', 'smartform' ),
			'not_found'          => __( 'No AC equipments found.', 'smartform' ),
			'not_found_in_trash' => __( 'No AC equipments found in Trash.', 'smartform' )
		);

		$args = array(
			'labels'             => $labels,
		            'description'        => __( 'Description.', 'smartform' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'acequipment' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title','editor')
		);

		register_post_type( 'acequipment', $args );
		$labels = array(
			'name'               => _x( 'Furnace Equipment', 'post type general name', 'smartform' ),
			'singular_name'      => _x( 'Furnace Equipment', 'post type singular name', 'smartform' ),
			'menu_name'          => _x( 'Furnace Equipment', 'admin menu', 'smartform' ),
			'name_admin_bar'     => _x( 'Furnace Equipment', 'add new on admin bar', 'smartform' ),
			'add_new'            => _x( 'Add New', 'funrnace', 'smartform' ),
			'add_new_item'       => __( 'Add New Furnace Equipment', 'smartform' ),
			'new_item'           => __( 'New Furnace Equipment', 'smartform' ),
			'edit_item'          => __( 'Edit Furnace Equipment', 'smartform' ),
			'view_item'          => __( 'View Furnace Equipment', 'smartform' ),
			'all_items'          => __( 'All Furnace Equipments', 'smartform' ),
			'search_items'       => __( 'Search Furnace Equipments', 'smartform' ),
			'parent_item_colon'  => __( 'Parent Furnace Equipments:', 'smartform' ),
			'not_found'          => __( 'No Furnace equipments found.', 'smartform' ),
			'not_found_in_trash' => __( 'No Furnace equipments found in Trash.', 'smartform' )
		);

		$args = array(
			'labels'             => $labels,
				    'description'        => __( 'Description.', 'smartform' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'funrnace' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title','editor')
		);

		register_post_type( 'funrnace', $args );

		$labels = array(
			'name'               => _x( 'Packaged Equipment', 'post type general name', 'smartform' ),
			'singular_name'      => _x( 'Packaged Equipment', 'post type singular name', 'smartform' ),
			'menu_name'          => _x( 'Packaged Equipment', 'admin menu', 'smartform' ),
			'name_admin_bar'     => _x( 'Packaged Equipment', 'add new on admin bar', 'smartform' ),
			'add_new'            => _x( 'Add New', 'packaged', 'smartform' ),
			'add_new_item'       => __( 'Add New Packaged Equipment', 'smartform' ),
			'new_item'           => __( 'New Packaged Equipment', 'smartform' ),
			'edit_item'          => __( 'Edit Packaged Equipment', 'smartform' ),
			'view_item'          => __( 'View Packaged Equipment', 'smartform' ),
			'all_items'          => __( 'All Packaged Equipments', 'smartform' ),
			'search_items'       => __( 'Search Packaged Equipments', 'smartform' ),
			'parent_item_colon'  => __( 'Parent Packaged Equipments:', 'smartform' ),
			'not_found'          => __( 'No Packaged equipments found.', 'smartform' ),
			'not_found_in_trash' => __( 'No Packaged equipments found in Trash.', 'smartform' )
		);

		$args = array(
			'labels'             => $labels,
				    'description'        => __( 'Description.', 'smartform' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'packaged' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title','editor')
		);

		register_post_type( 'packaged', $args );
		$labels = array(
			'name'               => _x( 'Default AC & Furnace', 'post type general name', 'smartform' ),
			'singular_name'      => _x( 'Default AC & Furnace', 'post type singular name', 'smartform' ),
			'menu_name'          => _x( 'Default AC & Furnace', 'admin menu', 'smartform' ),
			'name_admin_bar'     => _x( 'Default AC & Furnace', 'add new on admin bar', 'smartform' ),
			'add_new'            => _x( 'Add New', 'AC & Furnace', 'smartform' ),
			'add_new_item'       => __( 'Add New AC & Furnace', 'smartform' ),
			'new_item'           => __( 'New AC & Furnace', 'smartform' ),
			'edit_item'          => __( 'Edit AC & Furnace', 'smartform' ),
			'view_item'          => __( 'View AC & Furnace', 'smartform' ),
			'all_items'          => __( 'All AC & Furnaces', 'smartform' ),
			'search_items'       => __( 'Search AC & Furnaces', 'smartform' ),
			'parent_item_colon'  => __( 'Parent AC & Furnaces:', 'smartform' ),
			'not_found'          => __( 'No Packaged equipments found.', 'smartform' ),
			'not_found_in_trash' => __( 'No Packaged equipments found in Trash.', 'smartform' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'smartform' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'acaf' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title','editor')
		);

		register_post_type( 'acaf', $args );	
	

	  }

add_action('init', 'smartform_setup_post_types', 1);


function my_acaf_columns($columns)
{
	$columns = array(
		'cb'	 	=> '<input type="checkbox" />',
		'title' 	=> 'Title',
		'model' 	=> 'Model',
		'zone_type'	=>	'Zone',
		'type'	=>	'Type',
		'seer_afue'	=>	'Seer',
		'area'	=>	'Area',
		
		'date'		=>	'Date',
	);
	return $columns;
}

function my_acaf_custom_columns($column)
{
	global $post;
	if($column == 'model' )
	{
		echo strtoupper(str_replace("_",' ',get_field('model', $post->ID)));
	}

	if($column == 'zone_type' )
	{
		echo strtoupper(str_replace("_",' ',get_field('zone_type', $post->ID)));
	}
	if($column == 'type' )
	{
		echo strtoupper(str_replace("_",' ',get_field('type', $post->ID)));
	}	
	if($column == 'seer_afue' )
	{
		echo strtoupper(str_replace("_",' ',get_field('seer_afue', $post->ID)));
	}	
	
	if($column == 'area' )
	{
		echo get_the_title(get_field('area', $post->ID));
	}
}

add_action("manage_posts_custom_column", "my_acaf_custom_columns");
add_filter("manage_edit-acaf_columns", "my_acaf_columns");

function acaf_column_register_sortable( $columns )
{
	$columns['seer_afue'] = 'seer_afue';
	$columns['zone_type'] = 'zone_type';
	$columns['area'] = 'area';
	$columns['type'] = 'type';
	return $columns;
}

add_filter("manage_edit-acaf_sortable_columns", "acaf_column_register_sortable" );


?>

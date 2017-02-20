<?php 
	function get_post_by_slug($slug, $post_type){
	
		$posts = get_posts(array(
		        'name' => $slug,
		        'posts_per_page' => 1,
		        'post_type' => $post_type,
		        'post_status' => 'publish'
		));

		if( !$posts ) {
		    throw new Exception("NoSuchPostBySpecifiedID---".$slug);
		}

		return $posts[0];
	}

	function createPath($path) {
		if (is_dir($path)) return true;
		$prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
		$return = createPath($prev_path);
		return ($return && is_writable($prev_path)) ? mkdir($path) : false;
	}


    function asm_custom_post_column_head($defaults) {
        $defaults['post_slug'] = "Short Form";
        return $defaults;
    }
    add_filter('manage_posts_columns','asm_custom_post_column_head');

    function asm_custom_post_column_content($column_name,$post_ID) {
        if($column_name == "post_slug"){
            $custom_post = get_post($post_ID);
            if( $custom_post ) {
                echo $custom_post->post_name;
            }
            
        }

    }

    add_filter('manage_posts_custom_column','asm_custom_post_column_content',10,1);


	add_action('wp_ajax_nopriv_default_ac_furnace_model','default_ac_furnace_model');
	add_action( 'wp_ajax_default_ac_furnace_model','default_ac_furnace_model');

	function default_ac_furnace_model() {
		$args = array();
		$type = $_POST['type'];
		$state = strtolower($_POST['state']);
		$area = $_POST['area'];
		$question_4 = $_POST['question_4'];
		$question_5 = $_POST['question_5'];
		$question_7 = $_POST['question_7'];
		$question_8 = $_POST['question_8'];
		$areas_footage = array("000-600","600-800","801-1000","1001-1200","1201-1400","1401-1700",
			"1701-2100","2101-2400","2401-2800","2801-3200","3201-3600","3601-4000");
        
        $states_decreased = array('la','ms','al','ga','sc','fl');
        
         $index = array_search($area, $areas_footage);
        if (in_array($state, $states_decreased)) {
            if($index!= 0) {
                $index = $index-1;
            }
            $area = $areas_footage[$index];
        }

        if($question_8 == 'Yes') {
            if($area != '3601-4000' ) {
                if($index) {
                    $index = $index+1;
                }
            }
			
			$area = $areas_footage[$index];
        } else {
            //$index = array_search($area, $areas_footage);
        }
        
         $area = $areas_footage[$index];
        
                


		
		$choices = array();
		// type should be SPLIT SYSTEMS or PACKAGED SYSTEMS
		$equip_type = "";
		//area is is the post ID
		$area_id = 0;
		$state_id = 0;
		//AC Zone 1/2 or Furnace zone 
		$zone_type = "";#gf_1
		// Seer
		$seer_afue = "";


		$area_args = array(
		  'name'        => $area,
		  'post_type'   => 'area',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$area_post = get_posts($area_args);
		if($area_post) {
			$area_id = $area_post[0]->ID;
		}
		

		$state_args = array(
		  'name'        => $state,
		  'post_type'   => 'state',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$state_post = get_posts($state_args);
		if($state_post) {
			$state_id = $state_post[0]->ID;
		}

        
		

		if($type == 'AC') {
			$ac_zone = get_field('ac_zone', $state_id);
			if($question_7 == 'packaged') {
				if($ac_zone == 1) {
					$zone_type = 'default';
					$seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$zone_type = 'default';
					$seer_afue = 14;	
				}	

			} else {

				if($ac_zone == 1) {
					$zone_type = 'ac_zone1';
					$seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$zone_type = 'ac_zone2';
					$seer_afue = 14;	
				}
			}
            
            if($question_5 == 'Yes') {
				$seer_afue = 16;
                $zone_type = 'ac_zone2';
  		    }

		} else if($type == 'Furnace') {
			$furnace_zone = get_field('furnace_zone', $state_id);
			if($question_4 == 'Yes') {
				$furnace_zone = 2;
			}

			if($question_7 == 'packaged') {
				if($furnace_zone == 1) {
					$zone_type = 'default';
					$seer_afue = 14;	
				} else if($furnace_zone == 2) { 
					$zone_type = 'default';
					$seer_afue = 14;	
				}	

			} else {
				if($furnace_zone == 1) {
					$zone_type = 'furnace_zone1';
					$seer_afue = '80.00%';	
				} else if($furnace_zone == 2) { 
					$zone_type = 'furnace_zone2';	
					$seer_afue = '96.00%';
				}
			}
		} else if($type == 'Both') {
			$zone_type = 'default';
			$seer_afue = 14;
            
            
            //ac_zone_type,ac_seer_afue
            
            $ac_zone = get_field('ac_zone', $state_id);
			if($question_7 == 'packaged') {
				if($ac_zone == 1) {
					$ac_zone_type = 'default';
					$ac_seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$ac_zone_type = 'default';
					$ac_seer_afue = 14;	
				}	

			} else {

				if($ac_zone == 1) {
					$ac_zone_type = 'ac_zone1';
					$ac_seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$ac_zone_type = 'ac_zone2';
					$ac_seer_afue = 14;	
				}
			}
            
           /*if($question_5 == 'Yes') {
				$ac_seer_afue = 16;
  		    }*/
            
            $furnace_zone = get_field('furnace_zone', $state_id);
			if($question_4 == 'Yes') {
				$furnace_zone = 2;
			}

			if($question_7 == 'packaged') {
				if($furnace_zone == 1) {
					$furnace_zone_type = 'default';
					$furnace_seer_afue = 14;	
				} else if($furnace_zone == 2) { 
					$furnace_zone_type = 'default';
					$furnace_seer_afue = 14;	
				}	

			} else {
				if($furnace_zone == 1) {
					$furnace_zone_type = 'furnace_zone1';
					$furnace_seer_afue = '80.00%';	
				} else if($furnace_zone == 2) { 
					$furnace_zone_type = 'furnace_zone2';	
					$furnace_seer_afue = '96.00%';
				}
			}
            
            
		}
		if($type == 'Both'){
			//$question_7 = 'packaged';
        } 
        if($question_7 == 'split' || $question_7 == 'dontknow') {
                $equip_type = 'SPLIT SYSTEMS';
                //$zone_type = 'default';	
            } else if($question_7 == 'packaged') {
                $equip_type = 'PACKAGED SYSTEMS';
                //$zone_type = 'default';
        }
		
        
        if(in_array($type,array('Furnace','AC'))) {
            if($question_7 == 'packaged') {
                $args = array(
                    'numberposts'	=> -1,
                    'post_type'		=> 'acaf',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'area',
                            'value'   => $area_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'type',
                            'value'   => $equip_type,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'zone_type',
                            'value'   => $zone_type,
                            'compare' => '='
                        )
                    )
                );

            } else {
                $args = array(
                    'numberposts'	=> -1,
                    'post_type'		=> 'acaf',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'area',
                            'value'   => $area_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'type',
                            'value'   => $equip_type,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'seer_afue',
                            'value'   => $seer_afue,
                            'compare' => '='
                        ),

                        array(
                            'key'     => 'zone_type',
                            'value'   => $zone_type,
                            'compare' => '='
                        )
                    )
                );
            }
        } else {
            
            if($question_7 == 'packaged') {
                
                    if($question_5 == 'Yes') {

                        $args = array(
                        'numberposts'	=> -1,
                        'post_type'		=> 'acaf',
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'key'     => 'area',
                                'value'   => $area_id,
                                'compare' => '='
                            ),
                            array(
                                'key'     => 'type',
                                'value'   => $equip_type,
                                'compare' => '='
                            ),
                           /* array(
                                'key'     => 'seer_afue',
                                'value'   => array($ac_seer_afue,$furnace_seer_afue),
                                'compare' => 'IN'
                            ),*/
                            array(
                                'key'     => 'zone_type',
                                'value'   => $zone_type,
                                'compare' => '='
                            )
                        )
                    );
                
                
                    } else {
                    
                        $args = array(
                            'numberposts'	=> -1,
                            'post_type'		=> 'acaf',
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key'     => 'area',
                                    'value'   => $area_id,
                                    'compare' => '='
                                ),
                                array(
                                    'key'     => 'type',
                                    'value'   => $equip_type,
                                    'compare' => '='
                                ),
                                array(
                                    'key'     => 'zone_type',
                                    'value'   => $zone_type,
                                    'compare' => '='
                                )
                            )
                        );
                    
                    }
            } else {
                $args = array(
                    'numberposts'	=> -1,
                    'post_type'		=> 'acaf',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'area',
                            'value'   => $area_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'type',
                            'value'   => $equip_type,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'seer_afue',
                            'value'   => array($ac_seer_afue,$furnace_seer_afue),
                            'compare' => 'IN'
                        ),

                        array(
                            'key'     => 'zone_type',
                            'value'   => array($ac_zone_type,$furnace_zone_type),
                            'compare' => 'IN'
                        )
                    )
                );
            }
        
        
        
        
        }
		
		$result = array();

		$the_query = new WP_Query( $args );
			$choices = array();

            $the_query->post_count;

			if( $the_query->have_posts() ) {

				$choices = array();
                $temp = array( 'text' => '', 'value' => '','title'=> '');
                    while ( $the_query->have_posts() ) : $the_query->the_post();
                        $values = array();
                        $value = get_the_ID();
                        $title = get_post_meta(get_the_ID(),'model',true);
                        if($temp['text'] == "") {
                            $temp['text']  =  get_the_content();    
                        } else {
                            $temp['text']  =  $temp['text'].' , '.get_the_content();
                        } 
                        if($temp['title'] == "") {
                            $temp['title']  =  get_post_meta(get_the_ID(),'model',true);    
                        } else {
                            $temp['title']  =  $temp['text'].' , '.get_post_meta(get_the_ID(),'model',true);
                        }
                        if($temp['value'] == "") {
                            $temp['value']  =  get_the_ID();    
                        } else {
                            $temp['value']  =  $temp['value'].' , '.get_the_ID();
                        }    
                        
                /*
                if($temp['title'] == "") {
                            $temp['title']  =  get_post_meta(get_the_ID(),'model',true);    
                        } else {
                            $temp['title']  =  $temp['text'].' , '.get_post_meta(get_the_ID(),'model',true);
                        }
                        
                         */
                     //array_push($temp['value'],get_the_ID());
                    endwhile;
                
                        $choices[] = $temp;
                
				
				$result = array();
				$result['status'] = "success";
				$result['result'] = $choices;
			} else {
				$result = array();
				$result['status'] = "fail";
				$result['Message'] = "Brand do not have any models";
			}
		echo json_encode($result);
		die();	
	}	



	add_action( 'wp_ajax_nopriv_brand_ac_model','brand_ac_model');
	add_action( 'wp_ajax_brand_ac_model','brand_ac_model');

	function brand_ac_model() {
		$args = array();
		$brand = $_POST['brand'];
		$type = $_POST['type'];
		$state = strtolower($_POST['state']);
		$area = $_POST['area'];
		$question_4 = $_POST['question_4'];
		$question_5 = $_POST['question_5'];
		$question_7 = $_POST['question_7'];
		$question_8 = $_POST['question_8'];
		$areas_footage = array("000-600","600-800","801-1000","1001-1200","1201-1400","1401-1700",
			"1701-2100","2101-2400","2401-2800","2801-3200","3201-3600","3601-4000");
        
        $states_decreased = array('la','ms','al','ga','sc','fl');
         $index = array_search($area, $areas_footage);
        if (in_array($state, $states_decreased)) {
            if($index!= 0) {
                $index = $index-1;
            }
            $area = $areas_footage[$index];
        }

        if($question_8 == 'Yes') {
          //  $index = array_search($area, $areas_footage);
            if($area != '3601-4000' ) {
                if($index) {
                    $index = $index+1;
                }
            }
			
			$area = $areas_footage[$index];
        } else {
            //$index = array_search($area, $areas_footage);
        }
        $area = $areas_footage[$index];
        
	
		$choices = array();
		$area_id = 0;
		$state_id = 0;
		$zone_type = "";
		$seer_afue = "";
		$area_args = array(
		  'name'        => $area,
		  'post_type'   => 'area',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$area_post = get_posts($area_args);
		if($area_post) {
			$area_id = $area_post[0]->ID;
		}
		$state_args = get_post_by_slug($state,'state');
		if($state_args) {
			$state_id = $state_args->ID; 
		}

		if($type == 'AC') {
			$ac_zone = get_field('ac_zone', $state_id);
			$ac_size = get_field('ac_zone'.$ac_zone, $area_id);
			
			if($question_7 == 'packaged') {
				if($ac_zone == 1) {
					$zone_type = 'default';
					$seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$zone_type = 'default';
					$seer_afue = 14;	
				}	

			} else {

				if($ac_zone == 1) {
					$zone_type = 'ac_zone1';
					$seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$zone_type = 'ac_zone2';
					$seer_afue = 14;	
				}
			}
            if($question_5 == 'Yes') {
				$seer_afue = 16;
			}
            
            $args = array(
				'posts_per_page'	=> -1,
				'post_type'		=> 'acequipment',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'Manufacturer',
						'value'   => strtoupper($brand),
						'compare' => '='
					),
					/*
                        //for Seer backup
                    array(
						'key'     => 'seer',
						'value'   => $seer_afue,
						'compare' => '='
					)*/
				)
			);
		} else if($type == 'Furnace') {
            
            
			$furnace_zone = get_field('furnace_zone', $state_id);
			if($question_4 == 'Yes') {
				$furnace_zone = 2;
			}
			$furnace_size = get_field('furnace_zone'.$furnace_zone, $area_id);
			if($question_7 == 'packaged') {
				if($furnace_zone == 1) {
					$zone_type = 'default';
					$seer_afue = '80%';	
				} else if($furnace_zone == 2) { 
					$zone_type = 'default';
					$seer_afue = '96%';
				}	
			} else {
				if($furnace_zone == 1) {
					$zone_type = 'furnace_zone1';
					$seer_afue = '80%';	
				} else if($furnace_zone == 2) { 
					$zone_type = 'furnace_zone2';	
					$seer_afue = '96%';
				}
			}
            
            if($furnace_zone == 1) {
				$compare = '=<';
			} else if($furnace_zone == 2) {
				$compare= '>=';
			}
            
            if($brand == 'day and night') {
                $brand = 'Day & NIGHT';
            } 
			$args = array(
				'posts_per_page'	=> -1,
				'post_type'		=> 'funrnace',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'manufacturer',
						'value'   => strtoupper($brand),
						'compare' => '='
					),
					/*
                    
                     //for Seer backup
                    
                    array(
						'key'     => 'afue',
						'value'   => $seer_afue,
						'compare' => $compare
					)*/
				)
			);
		} else if($type == 'Both') {
			$zone_type = 'default';
			$seer_afue = 14;
            $ac_zone = get_field('ac_zone', $state_id);
			$ac_size = get_field('package_unit', $area_id);
            
             if($question_5 == 'Yes') {
				$seer_afue = 16;
			}
            
            $furnace_zone = get_field('furnace_zone', $state_id);
			if($question_4 == 'Yes') {
				$furnace_zone = 2;
			} 
			$furnace_size = get_field('price', $area_id);
            
            $args = array(
				'posts_per_page'	=> -1,
				'post_type'		=> 'packaged',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'manufacturer',
						'value'   => strtoupper($brand),
						'compare' => '='
					),
					/*array(
						'key'     => 'seer',
						'value'   => $seer_afue,
						'compare' => '='
					)*/
				)
			);
            
        } else if($type == 'PACKAGEAC') {
            
            $ac_zone = 1;
			$ac_size = get_field('package_unit', $area_id);
            
            $ac_zone = get_field('ac_zone', $state_id);
			$ac_size = get_field('ac_zone'.$ac_zone, $area_id);
            
			
			if($question_7 == 'packaged') {
				if($ac_zone == 1) {
					$zone_type = 'default';
					$seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$zone_type = 'default';
					$seer_afue = 14;	
				}	

			} else {

				if($ac_zone == 1) {
					$zone_type = 'ac_zone1';
					$seer_afue = 14;	
				} else if($ac_zone == 2) { 
					$zone_type = 'ac_zone2';
					$seer_afue = 14;	
				}
			}
            
             if($question_5 == 'Yes') {
				$seer_afue = 16;
  		    }
            
            
            $args = array(
				'posts_per_page'	=> -1,
				'post_type'		=> 'acequipment',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'Manufacturer',
						'value'   => strtoupper($brand),
						'compare' => '='
					),
					/*array(
						'key'     => 'seer',
						'value'   => $seer_afue,
						'compare' => '='
					)*/
				)
			);
        
        
        } else if($type == 'PACKAGEFURNACE') {
            
            
			$furnace_zone = get_field('furnace_zone', $state_id);
			if($question_4 == 'Yes') {
				$furnace_zone = 2;
			}
			$furnace_size = get_field('furnace_zone'.$furnace_zone, $area_id);
			if($question_7 == 'packaged') {
				if($furnace_zone == 1) {
					$zone_type = 'default';
					$seer_afue = '80%';	
				} else if($furnace_zone == 2) { 
					$zone_type = 'default';
					$seer_afue = '96%';
				}	
			} else {
				if($furnace_zone == 1) {
					$zone_type = 'furnace_zone1';
					$seer_afue = '80%';	
				} else if($furnace_zone == 2) { 
					$zone_type = 'furnace_zone2';	
					$seer_afue = '96%';
				}
			}
            
            if($furnace_zone == 1) {
				$compare = '=<';
			} else if($furnace_zone == 2) {
				$compare= '>=';
			}
            
            if($brand == 'day and night') {
                $brand = 'Day & NIGHT';
            } 
			$args = array(
				'posts_per_page'	=> -1,
				'post_type'		=> 'funrnace',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'manufacturer',
						'value'   => strtoupper($brand),
						'compare' => '='
					),
					/*array(
						'key'     => 'afue',
						'value'   => $seer_afue,
						'compare' => $compare
					)*/
				)
			);
		} 
		// query
		$the_query = new WP_Query( $args );
        $choices = array();
			

			if( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) : $the_query->the_post();
					if($type == 'AC') {
						$values = array();
						$total_records = intval(get_post_meta(get_the_ID(),'ac_equipment',true));
                        if($ac_size < 6.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                                $temp = array();
                                $temp['content'] = get_the_content();
                                $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                $temp['size'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true); 
                                $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); ; 
                                $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                if($temp['size'] == $ac_size) {
                                    array_push($values,$temp);
                                }
                            }
                        } else if($ac_size == 6.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 3) {
                                    $temp = array();
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true).','.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(6 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).'ton +'.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).'ton' ; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) * 2;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                
                                    array_push($values,$temp);
                                    
                                }
                            }
                        
                        } else if($ac_size == 7.0){
                            
                            $temp = array();
                            $check = 0;
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 3) {
                                    
                                    $check = $check+1;
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(7 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                } else if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 4) {
                                    $check = $check+1;
                                    $temp['size'] =  $temp['size'].''.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true)." ton"; 
                                    $temp['price'] = $temp['price']+floatval(str_replace(",","",get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true))) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                }
                            }
                            if($check == 2) {
                                array_push($values,$temp);
                            }
                            
                        
                        } else if($ac_size == 8.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 4) {
                                    $temp = array();
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true).','.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(8 tons ) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton'; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) * 2;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                
                                    array_push($values,$temp);
                                    
                                }
                            }
                        
                        } else if($ac_size == 9.0){
                            
                            $temp = array();
                            $check = 0;
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 4) {
                                    $check = $check +1; 
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(9 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                } else if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 5) {
                                    $check = $check +1;
                                    $temp['size'] =  $temp['size'].' , '.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton '; 
                                    $temp['price'] = $temp['price']+floatval(str_replace(",","",get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true))) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                }
                            }
                            if($check == 2) {
                                array_push($values,$temp);
                            }
                            
                        
                        } else if($ac_size == 10.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 5) {
                                    $temp = array();
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true).','.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(10 tons)  ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true)+' ton'; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) * 2;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                
                                    array_push($values,$temp);
                                    
                                }
                            }
                        
                        }
						
						if(count($values) > 0) {
							$choices[] = array( 'text' => get_the_title(), 'value' => get_post_field( 'post_name', get_post()),'values'=>$values);
						}					
		
					} else if($type == 'Furnace') {
                        
                        $furnace_size = '0K'; 
                        if($furnace_zone == 1) {
                            $furnace_size = get_field('furnace_zone1_size', $area_id);
                        } else if($furnace_zone == 2) {
                            $furnace_size = get_field('furnace_zone2_size', $area_id);
                        }
 						$total_records = intval(get_post_meta(get_the_ID(),'furnace_equipment_models',true));	
						$values = array();
                        
                        if($areas_footage[$index] == '2101-2400') {
                            for($i=0; $i<$total_records; $i++) {
                                $furnace_size = 0.0;
                                $record_furnace_size = 0.0;
                                $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                if($furnace_zone == 1) {
                                    $furnace_size = get_field('furnace_zone1_size', $area_id);
                                    $furnace_size = intval(str_replace("K","",$furnace_size));
                                    $record_furnace_size = intval(str_replace("K","",$record_furnace_size));

                                    if($furnace_size <= $record_furnace_size) {
                                        $temp = array();
                                        $temp['content'] = get_the_content();
                                        $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                        $temp['_size_(btus)'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                        $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                        $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                        array_push($values,$temp);

                                    }
                                    
                                } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));

                                        if($record_furnace_size == 80) {
                                            $temp = array();
                                            $temp['content'] = get_the_content();
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            array_push($values,$temp);
                                        }
                                    }
                                } // for for ending
                                
                                
                            } else if($areas_footage[$index] == '2401-2800'){
                            
                                $temp = array();
                                $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 60) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                        }

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 80) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                            //array_push($values,$temp);
                                        } else if($record_furnace_size == 100) {
                                            $temp['_size_(btus)'] =  $temp['_size_(btus)']." +  ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['price'] =$temp['price'] + floatval(str_replace(",","",get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true))) ;
                                            //array_push($values,$temp);

                                        }
                                    }
                                }
                                array_push($values,$temp);
                            } else if($areas_footage[$index] == '2801-3200'){
                                $temp = array();
                                $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 80) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                        }

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 100) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 
                                    }
                                }
                                array_push($values,$temp);

                            } else if($areas_footage[$index] == '3201-3600'){
                                 $temp = array();
                                $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        
                                        if($record_furnace_size == 90) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 100) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                            //array_push($values,$temp);
                                        } else if($record_furnace_size == 120) {
                                            $temp['_size_(btus)'] =  $temp['_size_(btus)']." +  ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['price'] =$temp['price'] + floatval(str_replace(",","",get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true))) ;
                                            //array_push($values,$temp);

                                        }
                                    }
                                }
                                array_push($values,$temp);
                            } else if($areas_footage[$index] == '3601-4000'){
                                $temp = array();
                                $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        
                                        if($record_furnace_size == 110) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 120) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 
                                    }
                                }
                                array_push($values,$temp);
                            } else {
                                    for($i=0; $i<$total_records; $i++) {

                                        $furnace_size = 0.0;
                                        $record_furnace_size = 0.0;

                                        if($furnace_zone == 1) {
                                            $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        } else if($furnace_zone == 2) {
                                            $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        }
                                         $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);

                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($furnace_size == $record_furnace_size) {
                                            $temp = array();
                                            $temp['content'] = get_the_content();
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            array_push($values,$temp);

                                        }
                                    }
                                }
						
						if(count($values) > 0) {
							$choices[] = array( 'text' => get_the_title(), 'value' => get_post_field( 'post_name', get_post()),'values'=>$values);
						}					
	
					} else if($type == 'Both') {
                        
                        $total_records = intval(get_post_meta(get_the_ID(), 'furnace_equipment_models', true));	
						$values = array();
                        
                        //if($areas_footage[$index] == '2101-2400') {
                        
                        if( is_array(get_post_meta(get_the_ID(),'packaged_models', true))) {
                        
                            $total_records = intval(count(get_field('packaged_models')));
                        } else {
                        
                            $total_records = intval(get_post_meta(get_the_ID(),'packaged_models', true));
                        }
 						
                            
						
						$values = array();
						
                            
                            
                            if($ac_size <= 5.0) {
                                
                                for($i=0; $i<$total_records; $i++) {
                                    $acsize = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_ac_size_(tons)',true);
                                    $furnacesize =  get_post_meta(get_the_ID(),'packaged_models_'.$i.'_heat_size_(btus)',true);
                                    //&& (trim($furnacesize) ==  trim($furnace_size) || trim($furnacesize) == 'N/A')
                                    if($acsize == $ac_size ) {
                                        $temp = array();
                                        $temp['content'] = get_the_content();
                                        $temp['type'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_type',true); 
                                        $temp['model'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_model',true); 
                                        $temp['ac_size_(tons)'] = $acsize.' ton'; 
                                        $temp['heat_size_(btus)'] = $furnacesize; 
                                        $temp['price'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_price',true); 
                                        array_push($values,$temp);
                                    }
                                }
                            } else if($ac_size == 7.0)  {
                                
                                $temp = array();
                                
                                $check = 0;
                                
                                $temp['content'] = get_the_content();
                                
                                for($i=0; $i<$total_records; $i++) {
                                    $acsize = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_ac_size_(tons)',true);
                                    $furnacesize =  get_post_meta(get_the_ID(),'packaged_models_'.$i.'_heat_size_(btus)',true);
                                    
                                   $price = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_price',true);
                                    
                                    $price = 	str_replace('$','',$price);
                                    $price = str_replace(',', '.', $price);
                                    $price = preg_replace("/[^0-9\.]/", "", $price);
                                    $price = str_replace('.', '',substr($price, 0, -3)) . substr($price, -3);
                                    $price = (float) $price;
                                    //&& (trim($furnacesize) == '60K' || trim($furnacesize) == 'N/A')
                                    //&& (trim($furnacesize) == '90K' || trim($furnacesize) == 'N/A') 
                                
                                    if($acsize == 3 ) {
                                        
                                        
                                         $check = $check +1;
                                        $temp['type'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_type',true); 
                                        $temp['model'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_model',true); 
                                        $temp['ac_size_(tons)'] = $acsize.''; 
                                        $temp['heat_size_(btus)'] = $furnacesize; 
                                        $temp['price'] = $price; 
                                        
                                    }
                                    if($acsize == 4 ) {
                                        $check = $check +1;
                                        
                                        $temp['ac_size_(tons)'] = $temp['ac_size_(tons)'].' + '.$acsize.' ton'; 
                                        $temp['heat_size_(btus)'] = $temp['heat_size_(btus)'] .' + '. $furnacesize; 
                                        $temp['price'] = $temp['price']+$price; 
                                      
                                    }
                                    
                                   
                                }
                                
                                if(count($temp)>0 && $check == 2) {
                                    
                                     array_push($values,$temp);
                                
                                }
                            
                               
                            
                            } else if($ac_size == 8.0)  {
                                
                                $temp = array();
                                $temp['content'] = get_the_content();
                                
                                for($i=0; $i<$total_records; $i++) {
                                    $acsize = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_ac_size_(tons)',true);
                                    $furnacesize =  get_post_meta(get_the_ID(),'packaged_models_'.$i.'_heat_size_(btus)',true);
                                    
                                    //&& (trim($furnacesize) == '90K'  || trim($furnacesize) == 'N/A') 
                                    
                                    $price = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_price',true);
                                    $price = 	str_replace('$','',$price);
                                    $price = str_replace(',', '.', $price);
                                    $price = preg_replace("/[^0-9\.]/", "", $price);
                                    $price = str_replace('.', '',substr($price, 0, -3)) . substr($price, -3);
                                    $price = (float) $price;
                                
                                    if($acsize == 4) {
                                        
                                        $temp['type'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_type',true); 
                                        $temp['model'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_model',true); 
                                        $temp['ac_size_(tons)'] = $acsize.' + '.$acsize.' ton' ; 
                                        $temp['heat_size_(btus)'] = $furnacesize.' + '.$furnacesize ; 
                                        $temp['price'] = $price+$price; 
                                        
                                    }
                                    
                                }
                                
                                if(count($temp)>0) {
                                    array_push($values,$temp);
                                }
                            
                            } else if($ac_size == 9.0)  {
                                
                                $temp = array();
                                $temp['content'] = get_the_content();
                                $check = 0;
                                
                                for($i=0; $i<$total_records; $i++) {
                                    $acsize = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_ac_size_(tons)',true);
                                    $furnacesize =  get_post_meta(get_the_ID(),'packaged_models_'.$i.'_heat_size_(btus)',true);
                                    
                                   $price = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_price',true);
                                    
                                    $price = 	str_replace('$','',$price);
                                    $price = str_replace(',', '.', $price);
                                    $price = preg_replace("/[^0-9\.]/", "", $price);
                                    $price = str_replace('.', '',substr($price, 0, -3)) . substr($price, -3);
                                    $price = (float) $price;
                                    //&& (trim($furnacesize) == '90K' || trim($furnacesize) == 'N/A')
                                    //&& (trim($furnacesize) == '115K' || trim($furnacesize) == 'N/A')
                                
                                    if($acsize == 4) {
                                        
                                        
                                        $check = $check+1;
                                        $temp['type'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_type',true); 
                                        $temp['model'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_model',true); 
                                        $temp['ac_size_(tons)'] = $acsize.' '; 
                                        $temp['heat_size_(btus)'] = $furnacesize; 
                                        $temp['price'] = $price; 
                                        
                                    }
                                    if($acsize == 5 ) {
                                        $check = $check+1;
                                        $temp['ac_size_(tons)'] = $temp['ac_size_(tons)'].' + '.$acsize.' ton'; 
                                        $temp['heat_size_(btus)'] = $temp['heat_size_(btus)'] .' + '. $furnacesize; 
                                        $temp['price'] = $temp['price']+$price; 
                                      
                                    }
                                    
                                   
                                }
                                
                                if(count($temp)>0 && $check == 2) {
                                    
                                     array_push($values,$temp);
                                
                                }
                            
                               
                            
                            }else if($ac_size == 10.0)  {
                                
                                $temp = array();
                                $temp['content'] = get_the_content();
                                
                                for($i=0; $i<$total_records; $i++) {
                                    $acsize = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_ac_size_(tons)',true);
                                    $furnacesize =  get_post_meta(get_the_ID(),'packaged_models_'.$i.'_heat_size_(btus)',true);
                                    //&& (trim($furnacesize) == '115K'  || trim($furnacesize) == 'N/A')
                                    
                                    $price = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_price',true);
                                    $price = 	str_replace('$','',$price);
                                    $price = str_replace(',', '.', $price);
                                    $price = preg_replace("/[^0-9\.]/", "", $price);
                                    $price = str_replace('.', '',substr($price, 0, -3)) . substr($price, -3);
                                    $price = (float) $price;
                                
                                
                                    if($acsize == 5 ) {
                                        
                                        $temp['type'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_type',true); 
                                        $temp['model'] = get_post_meta(get_the_ID(),'packaged_models_'.$i.'_model',true); 
                                        $temp['ac_size_(tons)'] = $acsize.' + '.$acsize.' ton' ; 
                                        $temp['heat_size_(btus)'] = $furnacesize.' + '.$furnacesize ; 
                                        $temp['price'] =$price+$price; 
                                        
                                    }
                                    
                                }
                                
                                if(count($temp)>0) {
                                    array_push($values,$temp);
                                }
                            
                            }
                             
                            
							
						if(count($values) > 0) {
							$choices[] = array( 'text' => get_the_title(), 'value' => get_post_field( 'post_name', get_post()),'values'=>$values);
						}						
		
                    } else if($type == 'PACKAGEAC') {
						$values = array();
						$total_records = intval(get_post_meta(get_the_ID(),'ac_equipment', true));
                        
                        if($ac_size < 6.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                                $temp = array();
                                $temp['content'] = get_the_content();
                                $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                $temp['size'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true); 
                                $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); ; 
                                $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                if($temp['size'] == $ac_size) {
                                    array_push($values,$temp);
                                }
                            }
                        } else if($ac_size == 6.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 3) {
                                    $temp = array();
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true).','.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(6 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton'; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) * 2;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                
                                    array_push($values,$temp);
                                    
                                }
                            }
                        
                        } else if($ac_size == 7.0){
                            
                            $temp = array();
                            $temp['content'] = get_the_content();
                            $check = 0;
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 3) {
                                
                                    $check = $check+1;
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(7 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                } else if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 4) {
                                    $check = $check+1;
                                    $temp['size'] =  $temp['size'].get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton'; 
                                    $temp['price'] = $temp['price']+floatval(str_replace(",","",get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true))) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                }
                            }
                            if($check == 2) {
                                array_push($values,$temp);
                            }
                            
                        
                        } else if($ac_size == 8.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 4) {
                                    $temp = array();
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true).','.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(8 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton'; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) * 2;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                
                                    array_push($values,$temp);
                                    
                                }
                            }
                        
                        } else if($ac_size == 9.0){
                            
                            $temp = array();
                            $check = 0;
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 4) {
                                    
                                    $check = $check+1;
                                    
                                    $temp['content'] = get_the_content();
                                   
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(9 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) .' ton + '; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                } else if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 5) {
                                    $check = $check+1;
                                    $temp['size'] =  $temp['size'].get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton'; 
                                    $temp['price'] = $temp['price']+floatval(str_replace(",","",get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true))) ;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                }
                            }
                            if($check == 2) {
                                
                                array_push($values,$temp);
                            
                            }
                            
                        
                        } else if($ac_size == 10.0){
                            
                            for($i=0; $i<$total_records; $i++) {
                               
                                if(get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true) == 5) {
                                    $temp = array();
                                    $temp['content'] = get_the_content();
                                    $temp['type'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_type',true); 
                                    $temp['model'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true).','.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_model',true); 
                                    $temp['size'] =  "(10 tons) ".get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton + '.get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_size',true).' ton'; 
                                    $temp['price'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true); 
                                    $temp['complete_ac_equipment_cost_'] = get_post_meta(get_the_ID(),'ac_equipment_'.$i.'_complete_ac_equipment_cost_',true) ; 
                                    
                                    $temp['price'] = floatval(str_replace(",","",$temp['price'])) * 2;
                                    $temp['complete_ac_equipment_cost_'] = $temp['price'];
                                
                                    array_push($values,$temp);
                                    
                                }
                            }
                        
                        }
						
						if(count($values) > 0) {
							$choices[] = array( 'text' => get_the_title(), 'value' => get_post_field( 'post_name', get_post()),'values'=>$values);
						}					
                    }else if($type == 'PACKAGEFURNACE') {
                        
                        $furnace_size = '0K'; 
                        $furnace_size = get_field('price', $area_id);
 						$total_records = intval(get_post_meta(get_the_ID(),'furnace_equipment_models',true));	
						$values = array();
                        if($areas_footage[$index] == '2101-2400') {
                            for($i=0; $i<$total_records; $i++) {
                                $furnace_size = 0.0;
                                $record_furnace_size = 0.0;
                                $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                if($furnace_zone == 1) {
                                    $furnace_size = get_field('furnace_zone1_size', $area_id);
                                    $furnace_size = intval(str_replace("K","",$furnace_size));
                                    $record_furnace_size = intval(str_replace("K","",$record_furnace_size));

                                    if($furnace_size <= $record_furnace_size) {
                                        $temp = array();
                                        $temp['content'] = get_the_content();
                                        $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                        $temp['_size_(btus)'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                        $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                        $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                        array_push($values,$temp);

                                    }
                                    
                                } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));

                                        if($record_furnace_size == 80) {
                                            $temp = array();
                                            $temp['content'] = get_the_content();
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            array_push($values,$temp);
                                        }
                                    }
                                } // for for ending
                                
                                
                            } else if($areas_footage[$index] == '2401-2800'){
                            
                                $temp = array();
                                $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 60) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                        }

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 80) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                            //array_push($values,$temp);
                                        } else if($record_furnace_size == 100) {
                                            $temp['_size_(btus)'] =  $temp['_size_(btus)']." +  ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['price'] =$temp['price'] + floatval(str_replace(",","",get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true))) ;
                                            //array_push($values,$temp);

                                        }
                                    }
                                }
                                array_push($values,$temp);
                            } else if($areas_footage[$index] == '2801-3200'){
                                $temp = array();
                                $temp['content'] = get_the_content();
                                
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 80) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                        }

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 100) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 
                                    }
                                }
                                array_push($values,$temp);

                            } else if($areas_footage[$index] == '3201-3600'){
                                 $temp = array();
                                 $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        
                                        if($record_furnace_size == 90) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 100) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price'])) ;
                                            //array_push($values,$temp);
                                        } else if($record_furnace_size == 120) {
                                            $temp['_size_(btus)'] =  $temp['_size_(btus)']." +  ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['price'] =$temp['price'] + floatval(str_replace(",","",get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true))) ;
                                            //array_push($values,$temp);

                                        }
                                    }
                                }
                                array_push($values,$temp);
                            } else if($areas_footage[$index] == '3601-4000'){
                                $temp = array();
                                $temp['content'] = get_the_content();
                                for($i=0; $i<$total_records; $i++) {
                                    $furnace_size = 0.0;
                                    $record_furnace_size = 0.0;
                                    $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);
                                    if($furnace_zone == 1) {
                                        $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        
                                        if($record_furnace_size == 110) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 

                                    } else if($furnace_zone == 2) {
                                        $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($record_furnace_size == 120) {
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = "2 units of ".get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            $temp['price'] = floatval(str_replace(",","",$temp['price']))*2 ;
                                            //array_push($values,$temp);
                                        } 
                                    }
                                }
                                array_push($values,$temp);
                            } else {
                                    for($i=0; $i<$total_records; $i++) {

                                        $furnace_size = 0.0;
                                        $record_furnace_size = 0.0;

                                        if($furnace_zone == 1) {
                                            $furnace_size = get_field('furnace_zone1_size', $area_id);
                                        } else if($furnace_zone == 2) {
                                            $furnace_size = get_field('furnace_zone2_size', $area_id);
                                        }
                                         $record_furnace_size = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true);

                                        $furnace_size = intval(str_replace("K","",$furnace_size));
                                        $record_furnace_size = intval(str_replace("K","",$record_furnace_size));
                                        if($furnace_size <= $record_furnace_size) {
                                            $temp = array();
                                            $temp['content'] = get_the_content();
                                            $temp['model_#'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_model_#',true); 
                                            $temp['_size_(btus)'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_size_(btus)',true); 
                                            $temp['type'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_type',true); 
                                            $temp['price'] = get_post_meta(get_the_ID(),'furnace_equipment_models_'.$i.'_price',true); 
                                            array_push($values,$temp);

                                        }
                                    }
                                }
						
						if(count($values) > 0) {
							$choices[] = array( 'text' => get_the_title(), 'value' => get_post_field( 'post_name', get_post()),'values'=>$values);
						}					
	
					}
				endwhile;
				$result = array();
				if(count($choices) >0) {
					$result['status'] = "success";
					$result['result'] = $choices;
					$result['brand'] = $brand;
				} else {

					$result = array();
					$result['status'] = "fail";
					$result['Message'] = "Brand do not have any suggested models";

				}
			} else {
				$result = array();
				$result['status'] = "fail";
				$result['Message'] = "Brand do not have any models";
			}
		wp_reset_query();
		echo json_encode($result);
		die();
	}


add_filter( 'gform_pre_render', 'populate_posts' );
add_filter( 'gform_admin_pre_render', 'populate_posts' );
function populate_posts( $form ) {
    foreach ( $form['fields'] as &$field ) {
		
		/*if($field["id"] == 9) {
		    if ( $field->type != 'select' ) {
		        continue;
		    }
		    $posts = get_posts( 'post_type=area&numberposts=-1&order=ASC&post_status=publish' );
		    $choices = array();
		    foreach ( $posts as $post ) {
		        $choices[] = array( 'text' => $post->post_title, 'value' => $post->post_name );
		    }
	        $field->placeholder = 'Select Area';
	        $field->choices = $choices;
		}*/

		/*if($field["id"] == 20) {
			 if ( $field->type != 'select' ) {
		        continue;
		    }
		    $posts = get_posts( 'post_type=acequipment&numberposts=-1&order=ASC&post_status=publish' );
		    $choices = array();
		    foreach ( $posts as $post ) {
		        $choices[] = array( 'text' => $post->post_title, 'value' => $post->post_name );
		    }
	        $field->placeholder = 'Select Ac Model';
	        $field->choices = $choices;
		}*/


			
    }
    return $form;
}
	
	function post_submission($lead, $form) {

		$upload_dir = wp_upload_dir();
		$user_dirname = $upload_dir['basedir'].'/smartform/';
		if ( ! file_exists( $user_dirname ) ) {

			echo "not Existed";
			exit();
			wp_mkdir_p( $user_dirname );
		}

			$path = $user_dirname;
			$filename = rand(0,1000)."_file.pdf";

	$html =  '<h1></h1><h6>The purpose of this report is simple - to empower you with the basic knowledge required to make a well-informed decision on your heating and air conditioning project. Below, this
	information will be provided in two parts: first, the consultation report itself, which includes
	important information that pertains to your specific heating and air conditioning project, and
	second, we have provided you with a brief explanation of the report\'s components, what this
	information means to you and your project, and most importantly, how to use this information to
	negotiate a fair price and make an informed decision.</h6>
	<h1></h1>
	<sub><h3><u>PART I - The Report</u></h3></sub>
	<h6>Based on your geographical area, the size of your home, and the answers to the questions you
	provided, we made the following observations and recommendations:</h6>

	<h4>1. Project Completion Time</h4>

	<h6><sub>Based on the information you provided, it is our estimate that your project should take around</sub> <span>1 day</span> <sub>for your project to be completed. This information will be important later, as it will help
	us calculate the estimated labor costs for your installation.</sub></h6>
	<h4>2. Recommended System Size, SEER Rating, and AFUE Rating</h4>
	<h6>Based on the information you provided, we recommend you choose a heating and air<pLeft10>-------<sub> conditioning system that is of the following size:</sub></pLeft10></h6>
	<h6>
	<sub>Air Conditioner (size and recommended SEER rating):</sub> <span>3-ton, 14 SEER Air Conditioner</span>
	</h6>
	<h6>
	<sub>Furnace (minimum BTU and recommended AFUE rating):</sub> <span> 80,000 BTU, 80% AFUE Furnace</span>
	</h6>
	<h6>
	Important notes based on your selections:
	</h6>
	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>Although the reason why this is so will be discussed in more detail later, it is important to</sub></pLeft10>
	<pLeft10>----<sub>properly size your air conditioner, both for energy usage and proper cooling.</sub></pLeft10>
	</h6>


	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>Based on the answers you provided, as well as our experience and observations, if an air</sub></pLeft10>
	<pLeft10>----<sub>conditioner is purchased,we do not feel that it is worth the additional money to purchase an</sub></pLeft10>
	<pLeft10>----<sub>air conditioner higher than a value of 16 SEER in your area. We do not think it will provide</sub></pLeft10>	
	<pLeft10>----<sub>an adequate return on your investment in the long-term.</sub></pLeft10>
	</h6>
	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>The furnace recommendations are based on a gas furnace. <b>Tip:</b> If natural gas is not</sub><pLeft10>------</pLeft10></pLeft10>
	<pLeft10>----<sub>available in your area,you can have your HVAC contractor install a "propane conversion</sub></pLeft10>
	<pLeft10>----<sub>kit" to modify a gas furnace for use with propane. This kit typically costs around $45 - $55,</sub></pLeft10>
	<pLeft10>----<sub>and takes minutes to install. It will include a new expansion valve that is made for use with</sub></pLeft10>
	<pLeft10>----<sub>propane.</sub></pLeft10>
	</h6>
	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>If an oil burning is desired, these will typically be of comparable price to the gas furnaces</sub><pLeft10>---</pLeft10></pLeft10>
	<pLeft10>----<sub>listed above.</sub></pLeft10>
	</h6>
	<h4>3. Wholesale Price for Your Requested, Particular Brand and Model of Equipment:</h4>

	<h6>Per your request, here is the current wholesale pricing for the equipment you requested (this is what the contractor pays)
	</h6>
	<h4><span>None Selected</span></h4>
	<h6>Price information for selected model.</h6>
	<h4><span>Bryant Legacy, 3-ton, 14 SEER air conditioner:    $1,270.80</span></h4>

	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>For contractors,the price of purchasing equipment actually changes, and is based on</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>----<sub>several factors, including the volume of units sold in a year, and whether or not you are a</sub></pLeft10>
	<pLeft10>----<sub>" Licensed Distributor " for that particular brand. As such, the pricing information provided</sub></pLeft10>
	<pLeft10>----<sub>to you is an average, constructed using the prices paid by contractors around the country</sub></pLeft10>
	<pLeft10>----<sub>(including ourselves), and is calculated using a "low multiplier." What this means is that</sub><pLeft10>-----</pLeft10></pLeft10><pLeft10>----<sub>some contractors will be able to get this equipment for even less than this, and some</sub><pLeft10>-----</pLeft10></pLeft10><pLeft10>----<sub>will pay slightly more. The difference in price, however, can be thought of as only a few</sub><pLeft10>--</pLeft10></pLeft10><pLeft10>----<sub> hundred dollars (i.e. +/- $200). In other words, it is possible that a contractor might pay</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>----<sub> $150 less thanthe price provided above, but highly unlikely that they paid $150 more</sub><pLeft10>-----</pLeft10></pLeft10><pLeft10>----<sub> This is important for calculating a fair price for your new air conditioner.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>
	<h6><input type="button" value="Generate PDF"></input></h6>
	<h4>4. Our Recommendation on Equipment for Your Home:</h4>
	<h6>Based on our experience, as well as the information you have provided, we would<pLeft10>----</pLeft10>
	<sub>recommend the following equipment for your project:</sub>
	</h6>
	<h4><span>3-ton, 14 SEER Day & Night condenser with
	matching evaporative coil ($1,222)</span><pLeft10>--------</pLeft10><sub><span> and an 88,000, 80% AFUE Day & Night furnace ($506.11)</sub></span></h4>
	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>We have recommended Day & Night equipment for your project. This is based on our</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>----<sub>experience installing all major brands. We are in no way beholden to Day & Night, but base</sub></pLeft10>
	<pLeft10>----<sub>this assessment simply on our own recommendation. In other words, we were not paid to</sub></pLeft10>
	<pLeft10>----<sub>say this or promote this product in any way. Next year, based on our experience, we might</sub></pLeft10>
	<pLeft10>----<sub>recommend a completely different company.</sub></pLeft10>
	</h6>

	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>If Day & Night is not available in your area, then Goodman or Bryant are also reliable and</sub><pLeft10>---</pLeft10></pLeft10>
	<pLeft10>----<sub>reasonably priced brands that you can use.</sub></pLeft10>
	</h6>
	<h6>  <img src="'.plugins_url('smartform/images/download.png').'" height="5" width="5"><pLeft10>--<sub>Day & Night is made by United Technologies, which also makes Carrier,Bryant, Payne.</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>----<sub>However, Day & Night is more reasonably priced than Carrier, despite featuring most of the</sub></pLeft10>
	<pLeft10>----<sub>same internal components, including Aspen coils. In our experience, they are rugged,</sub><pLeft10>----</pLeft10></pLeft10>
	<pLeft10>----<sub>reliable, and their customer service (for later down the line) is top notch.</sub></pLeft10>
	</h6>
	<h4>5. Average Labor Cost in Your Area.</h5>
	<h6>Based on the location you have selected, the average price of labor for an experienced HVAC
	<sub>Technician in your area is approximately:</sub>
	</h6>
	<h4><span>Average hourly rate for a HVAC Technician in California: $25.55/hr.</span></h5>
	<h4><span>Average cost for a 2-man HVAC team, per day: $408.80</span></h5>
	<h4>6. Total Cost to a Contractor for the Completion of Your Project<pLeft10>------</pLeft10><br><br><br><br><sub> (i.e. what the contractor
	is paying to complete your job):</sub></h5>
	<h6>In this section, we will take the information previously discussed, add supplemental information
	based on the answers you provided, and incorporate it into an equation that will allow us to
	come up with a fair price range for your heating and air conditioning installation. It will factor in
	the price of equipment, labor costs, additional materials, as well as other factors such as
	general liability and workman\'s compensation insurance. Let\'s get started:
	</h6>

	<h6>a.<pLeft10>--<sub>Price of the equipment you have selected:	</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Bryant Legacy, 3-ton, 14 SEER air conditioner:        $1,270.80</span></h6>
	<h6><span>- Bryant Legacy 80%, 90,000 BTU furnace:                $556.16</span></h6>

	<h6>b.<pLeft10>--<sub>Estimated price of labor for your project:</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Total estimated labor cost for your project:       		   $1,226.40</span></h6>
	<h6>c.<pLeft10>--<sub>Price of ductwork, based on square footage and region (including additional labor and
	material requirements</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Ductwork Cost:                              		          $1,845.00</span></h6>
	<h6>d.<pLeft10>--<sub>Estimated miscellaneous materials, gas, and other expenses</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Cost of the  work:                           		          $912.00</span></h6>
	<h6>e.<pLeft10>--<sub>Estimated General Liability Insurance, Workman\'s Compensation Insurance, overhead, and
	other miscellaneous administrative fees, etc.:</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Cost of the  work:                           		          $681.00</span></h6>
	<h6>f.<pLeft10>--<sub>Estimated total cost to contractor for your project:</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Total cost to contractor:                           		          $6,491.36</span></h6>
	<h1></h1>
	<h1></h1>
	<h4><sub>7. Fair Price Calculation for Your Heating and Air Conditioning Project:</sub></h4>
	<h6>Here is where you are going to lose your temper, but don\'t worry - I\'ll explain exactly why it costs
	so much right afterwards. If you have blood pressure medication, please take it before reading
	further...
	</h6>
	<h6><table>
	<tr><td>Fair Price Calculations (Based on the Information You Provided)</td></tr>
	<tr><td>Profit Margin for Contractor</td><td>Proposed Price</td><td>Fair Price?</td></tr>
	<tr><td>10%</td><td>$7,140</td><td>Too LOW; look elsewhere.</td></tr>
	</table></h6>

	<h4><sub>8. Why So High?</sub></h4>
	<h6>In order to understand how these prices were calculated, it is important to understand how the
	contracting industry works - specifically the heating and air conditioning industry. Remember,
	the profit margins above are profit to the company, not the guy who owns it...there are still other
	expenses that have to be accounted for, such as taxes, administrative employees, etc.</h6>

	<h6>These expenses, however, are variables that depend on the corporate culture of each individual
	business, and cannot be accounted for in a calculator. Therefore, to keep your results
	completely accurate, I am giving you the total profit margin for your job. For instance, a really
	efficient company might only spend 10% of that profit on other company expenses (admin, etc.)
	and pocket the rest! A really inefficient company might live in near poverty at a 60% profit
	margin. That isn\'t your problem, of course, and I\'d urge you to stick with he guidelines above,
	but just realize that just because they are very high, does NOT mean that they are necessarily
	dishonest (although they could be). Here are a couple of other things to keep in mind as to why
	the industry keeps such a high profit margin:
	</h6>
	<h6>1.<pLeft10>--<sub>If you are an honest contractor, which is all I can speak for, there is no money in service</sub></pLeft10>
	<pLeft10>-----<sub>and repair. Think about it - the only way for me to make money by repairing your air</sub><pLeft10>----</pLeft10></pLeft10>
	<pLeft10>-----<sub>conditioner or heater is by ripping you off. That is because the majority of repairs are</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>$50 or less. So, to make money doing it, you need to do one of two things: a. lie,</sub><pLeft10>--------</pLeft10></pLeft10>
	<pLeft10>-----<sub>and make-up an outrageous price and replace things that don\'t need to be repaired;</sub><pLeft10>----</pLeft10></pLeft10>
	<pLeft10>-----<sub>or b., you increase your profit on other projects.</sub></pLeft10>	</h6>

	<h6>2.<pLeft10>--<sub>Remember, the company isn\'t replacing an air conditioner every single day, so this</sub><pLeft10>--------</pLeft10></pLeft10>
	<pLeft10>-----<sub>might be their entire profit to run the company for a week or two. Put in those terms,</sub><pLeft10>----</pLeft10></pLeft10>
	<pLeft10>-----<sub>it isn\'t all that outrageous.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>

	<h6>3.<pLeft10>--<sub>Although the team that is instilling your hair conditioner or furnace is getting paid from this</sub></pLeft10>
	<pLeft10>-----<sub>job, remember that if the company is honest, there is also at least one other team of guys,</sub></pLeft10>
	<pLeft10>-----<sub>costing the labor price listed above, roaming around on service calls making only</sub><pLeft10>----------</pLeft10></pLeft10>
	<pLeft10>-----<sub>$50 - $300 for the company per day on repair calls. So, the profits have to support</sub><pLeft10>--------</pLeft10></pLeft10>
	<pLeft10>-----<sub>the company as a whole.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>

	<h6>4.<pLeft10>--<sub>Some people are better at business than others. Unfortunately, many of the guys who start</sub></pLeft10>
	<pLeft10>-----<sub>heating and air conditioning businesses are fantastic #gf_1technicians, but are shooting from</sub></pLeft10>
	<pLeft10>-----<sub>the hip, and learning as they go when it comes to business. Often times, they expand too</sub></pLeft10>
	<pLeft10>-----<sub>quickly, hire too many people, and then they have to charge ridiculous amounts of money</sub></pLeft10>
	<pLeft10>-----<sub>just to get by. If they aren\'t careful, they won\'t sell enough jobs at this price, and they\'ll go</sub></pLeft10>
	<pLeft10>-----<sub>under.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>

	<h6>5.<pLeft10>--<sub>Why is ASM on the high side of fair? Simple, we hire U.S. Veterans, and also employ</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>some of the best HVAC technicians in the industry. But, people like that don\'t come</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>cheap! the hip, and learning as they go when it comes to business. Often times, they</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>expand too quickly, hire too many people, and then they have to charge ridiculous</sub><pLeft10>-------</pLeft10></pLeft10>
	<pLeft10>-----<sub>amounts of money just to get by. If they aren\'t careful, they won\'t sell enough jobs at </sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>this price, and they\'ll go under.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>

	<h6>6.<pLeft10>--<sub>In the results above, we mentioned that there is a such thing as "too low." How so?</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>Remember: you get what you pay for. If a company is in this range, I\'d be wary of them.</sub></pLeft10>
	<pLeft10>-----<sub>That money is coming from somewhere. Are they installing used equipment </sub><pLeft10>----------------</pLeft10></pLeft10>
	<pLeft10>-----<sub>(a big problem in California right now)? They might be using unskilled labor that could</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub> jeopardize the quality of the installation, or even failing to carry proper insurance. </sub><pLeft10>--------</pLeft10></pLeft10>
	<pLeft10>-----<sub>and other requirements. But, if someone is injured on your property and the company</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>does not carry proper Workman\'s Compensation Insurance if required, that employee can</sub></pLeft10>
	<pLeft10>-----<sub>actually come after you financially, as well as the company, for reparations in some</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>-----<sub>instances. Cover yourself, and don\'t skimp on the contractor you choose it will cost you </sub></pLeft10>
	<pLeft10>-----<sub>less in the long run, I promise.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>';

	$pdf = new createPDF(
		$html,  
		"ASM Personal Consultation Report",  
		$_POST['url'],    
		$_POST['author'], 
		time()
	);
			$pdf->run($path.$filename);
			chmod($path.$filename,0777);

		/*$file_url = $upload_dir['baseurl'].'/smartform/'.$filename;
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',false);
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Connection: close');
		readfile( $file_url );
		*/		

	}
//add_action( '', 'log_form_saved', 10, 2 );
//function log_form_saved( $form, $is_new ) {
	//print_r($form);
	//print_r($is_new);
//}

function smartform_custom_template($page_template) {
	if (is_page('thank-you-page')) {
		$page_template = SF_PLUGIN_DIR . 'templates/thankyou.php';
	} else if (is_page('thankyou-new')) {
		$page_template = SF_PLUGIN_DIR . 'templates/thankyou_new.php';
	}
	return $page_template;
}
add_filter( 'page_template', 'smartform_custom_template' );






//add_filter('gform_validation', 'custom_validation');

function custom_validation($validation_result){
    $form = $validation_result["form"];
     
    if($form['title'] == 'ASM Matrix') {
        
        foreach($form['fields'] as &$field){
            
            if($field['id'] == 8){
            
            echo "State ".rgpost("input_{$field['id']}");
            
            }
            
            
            
            
            if($field['id'] == 8 && rgpost("input_{$field['id']}") == 'state' || rgpost("input_{$field['id']}") == '') {
                
                
                $form["is_valid"] = true;

                    $field["failed_validation"] = true;

                    $field["validation_message"] = "State field is invalid!";
                
                return;
                  // 
                    //return false;    
            }
        //echo '<br>'.$field['id'].'------'.rgpost("input_{$field['id']}");
       }
        $validation_result["form"] = $form;
        return $validation_result;
    }
   // 
    
}

?>
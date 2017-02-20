<?php
    ini_set("display_errors",1);

    define("_JPGRAPH_PATH", '../jpgraph_5/jpgraph/'); 

    $JpgUseSVGFormat = true;

    define('_MPDF_URI','../'); 	

    function ton2tons($str) {
        $str = str_replace('ton(s)','ton',str_replace('-',' ',$str));
        $str = str_replace('ton','ton(s)',str_replace('-',' ',$str));
        $str = str_replace('ton(s)s','tons',str_replace('-',' ',$str));
        $str = str_replace('ton(s) ton(s)','ton(s)',str_replace('-',' ',$str));
        return $str;
    }

    /* $seer_afue_pre is variable is used for variable pre defined value like 14 Seer or 16 Seer
       $furnace_seer_tonnage_pre is variable is used pre defined value 80% 92%
    */

    if ( isset($_GET['entry_id']) && $_GET['entry_id']!= 0 && $_GET['formId'] != 0 && isset($_GET['formId'])) {
        
        /* Take Gravity form Entry ID and Form Id and collecting Saved data to process */
        
            $entry_id = $_GET['entry_id'];
            $form_id = $_GET['formId'];
            $lead_id = $_GET['entry_id'];
            $lead = RGFormsModel::get_lead( $lead_id ); 
            $form = GFFormsModel::get_form_meta( $lead['form_id'] ); 
            $values= array();
            $answer_filter = array();
            foreach( $form['fields'] as $field ) {
                $values[$field['id']] = array(
                    'id'    => $field['id'],
                    'label' => $field['label'],
                    'value' => $lead[ $field['id'] ],
                );
            }

        /* ################# Extraction of Submitted Data From Gravity form  Start #################### */
        
            $number_of_days = 1; 
            $answer_filter['equipment_type'] = $values[2]['value'];
            $answer_filter['location_state'] = $values[8]['value'];
            $answer_filter['area_size'] = $values[9]['value'];
            $answer_filter['above_3000'] = $values[10]['value'];
            $answer_filter['summmer_temp_above_100'] = $values[12]['value'];
            $answer_filter['new_duct'] = $values[13]['value'];
            $answer_filter['conditioner_type'] = $values[15]['value'];
            $answer_filter['ceilings_height'] = $values[16]['value'];
            $answer_filter['customer_interest'] = $values[18]['value'];
            $answer_filter['topbrand'] = $values[19]['value'];
            $answer_filter['ac_model'] = $values[34]['value'];
            $answer_filter['furnace_model'] = $values[33]['value'];
            $answer_filter['packaged_model'] = $values[35]['value'];
            $answer_filter['default_model'] = $values[36]['value'];
            $answer_filter['package_split_ac_model'] = $values[40]['value'];
            $answer_filter['package_split_furnace_model'] = $values[41]['value'];
        
        /* ################# Extraction of Submitted Data From Gravity form End  #################### */ 
        
        //Initialize the Price 
        
            $total_price = 0.0;
            $model_price = "";
            $default_price = "";
            $furnace_model_price = 0.0;
            $furnace_model_price_added = 0;  
        
        //Price Initialize end

        // Totals Days of working Days based on AREA 
        
            $areas_days = array(
                                "000-600"=>0,"600-800"=>0,"801-1000"=>0,
                                "1001-1200"=>0,"1201-1400"=>0,"1401-1700"=>0,
                                "1701-2100"=>0,"2101-2400"=>2,"2401-2800"=>2,
                                "2801-3200"=>2,"3201-3600"=>2,"3601-4000"=>2
                            );

            $number_of_days = $number_of_days + $areas_days[$answer_filter['area_size']];
        
        

            if($answer_filter['new_duct'] == 'Yes') {
                $number_of_days = $number_of_days + 2;
            }
            $number_of_days_number = $number_of_days;
            $number_of_days = $number_of_days . " Day";
            if($number_of_days > 1) {
                $number_of_days = $number_of_days ."s";
                
            }
        
        // Total Days Coutn End 

        // For Question 2 High Humidity States 
            $area_exception_states = array('LA','MS','AL','GA','SC','FL');
        
            $areas_footage = array("000-600","600-800","801-1000","1001-1200","1201-1400","1401-1700",
            "1701-2100","2101-2400","2401-2800","2801-3200","3201-3600","3601-4000");
        
            $area_position = array_search($answer_filter['area_size'], $areas_footage);
            if(in_array($answer_filter['location_state'],$area_exception_states)) {
                if($area_position!= 0) {
                    $area_position = $area_position-1;
                }
            }
            if($answer_filter['ceilings_height'] == 'Yes' && $area_position !=count($areas_footage)-1)	 {
                    $area_position = $area_position+1;
            }

            // below is the  desired area post type slug like 000-600
        
        //Area
            $area_id = 0;
            $state_id = 0;
            $area_args = array(
                  'name'        => $areas_footage[$area_position],
                  'post_type'   => 'area',
                  'post_status' => 'publish',
                  'numberposts' => 1
                );
            $area_post = get_posts($area_args);
            if($area_post) {
                $area_id = $area_post[0]->ID;
            }
        //State
            $state_args = array(
              'name'  => strtolower($answer_filter['location_state']),
              'post_type'   => 'state',
              'post_status' => 'publish',
              'numberposts' => 1
            );
            $state_post = get_posts($state_args);
            if($state_post) {
                $state_id = $state_post[0]->ID;
            }
        //For Zone and Seer calculations
            if($answer_filter['equipment_type'] == 'AC') {
                $ac_zone = get_field('ac_zone', $state_id);
                if($ac_zone == 1) {
                    $zone_type = 'ac_zone1';
                    $seer_afue = 14;	
                    if($answer_filter['summmer_temp_above_100'] == 'Yes') {
                        $seer_afue = 16;
                        if($answer_filter['customer_interest'] == 'No') {
                            $zone_type = 'ac_zone2';	

                        } else {
                            $zone_type = 'ac_zone1';	
                        }
                        //Changed for Default ac for  the homes having  hight ceilings from zone1 ot zone2 site and seer chang
                        //$zone_type = 'ac_zone2';	
                    } else {
                        $seer_afue = 14;
                    }
                } else if($ac_zone == 2) { 
                    $zone_type = 'ac_zone2';
                    if($answer_filter['summmer_temp_above_100'] == 'Yes') {
                        $seer_afue = 16;
                        if($answer_filter['customer_interest'] == 'No') {
                            $zone_type = 'ac_zone2';	

                        } else {
                            $zone_type = 'ac_zone2';	
                        }

                    } else {
                        $seer_afue = 14;
                    }
                    //$seer_afue = 16;	
                }
            } else if($answer_filter['equipment_type'] == 'Furnace') {
                $furnace_zone = get_field('furnace_zone', $state_id);
                 

                if($furnace_zone == 1) {
                    $zone_type = 'furnace_zone1';
                    $btu_pre_zone = "furnace_zone1_size";
                    
                    
                    $seer_afue = 80;	
                } else if($furnace_zone == 2) { 
                    $zone_type = 'furnace_zone2';
                    $btu_pre_zone = "furnace_zone2_size";
                    $seer_afue = 96;
                }
                
                if($answer_filter['above_3000'] == 'Yes') {
                    $zone_type = 'furnace_zone2';
                    $btu_pre_zone = "furnace_zone2_size";
                }
            }  else if($answer_filter['equipment_type'] == 'Both') {
                    $zone_type = 'package_unit';
                    $btu_pre_zone = "price";
            }
        
       
            $btu_pre =  get_field($btu_pre_zone, $area_id);
        
            if($btu_pre == '120K') {
                $btu_pre = "60K + 60K";
            }
            if($btu_pre == '160K') {
                $btu_pre = "80K + 80K";
            }
            if($btu_pre == '180K') {
                $btu_pre = "90K + 90K";
            }
            if($btu_pre == '200K') {
                $btu_pre = "100K + 100K";
            }
            if($btu_pre == '220K') {
                $btu_pre = "100K + 120K";
            }
            if($btu_pre == '240K') {
                $btu_pre = "120K + 120K";
            }
            
        
            $btu_pre = $btu_pre ." BTU";
        // Based On Zone calculating AC Size in ton
            $tonnage = get_field($zone_type, $area_id);
            if($tonnage == "6.0") {
                $tonnage = "(6 tons) [3 ton(s) + 3 ton(s)]";
            }
            if($tonnage == "7.0") {
                $tonnage = "(7 tons) [3 ton(s) + 4 ton(s)]";
            }
            if($tonnage == "8.0") {
                $tonnage = "(8 tons) [4 ton(s) + 4 ton(s)]";
            }
            if($tonnage == "9.0") {
                $tonnage = "(9 tons) [4 ton(s) + 5 ton(s)]";
            }
            if($tonnage == "10.0") {
                $tonnage = "(10 tons) [5 ton(s) + 5 ton(s)]";
            }
        
        
            if($answer_filter['equipment_type'] == 'AC') {
        //AC Equipment Model,Price and Seer calcualtion and price calculation 
                $ac_model_details  = explode('-',$answer_filter['ac_model']);
                $ac_model_multi_units = explode(',',$ac_model_details[0]);

                $ac_model_details_info = get_posts(array('name'=>sanitize_title(trim($ac_model_multi_units[0])),'post_type' => 'acequipment'));

                //default 14 or 16 SEER check correct 
                $seer_afue_pre = $seer_afue;
                $seer_afue = get_post_meta(trim($ac_model_details_info[0]->ID),'seer',true);
                
                if($answer_filter['conditioner_type'] == 'packaged') {
                    if(get_field($zone_type, $area_id) >5) {
                        $ac_seer_tonnage_pre  =  $tonnage.' and '.$seer_afue_pre. ' SEER Packaged Air Conditioner'; 
                        $ac_seer_tonnage  =  $tonnage.' and '.$seer_afue. ' SEER Packaged Air Conditioner'; 
                    } else {
                        $ac_seer_tonnage_pre  =  $tonnage.' ton(s) and '.$seer_afue_pre. ' SEER Packaged Air Conditioner'; 
                        $ac_seer_tonnage  = $tonnage.' ton(s) and '.$seer_afue. ' SEER Packaged Air Conditioner'; 
                    }
                } else {
                    if(get_field($zone_type, $area_id) >5) {
                        $ac_seer_tonnage_pre  = $tonnage.', and '. $seer_afue_pre. ' SEER Air Conditioner'; 
                        $ac_seer_tonnage = $tonnage.', and '. $seer_afue. ' SEER Air Conditioner'; 
                    } else {
                        if (strpos($tonnage, 'ton') === false) {
                            $tonnage = $tonnage.' ton(s) ';
                        }
                        $ac_seer_tonnage_pre = $tonnage.' and '. $seer_afue_pre. ' SEER Air Conditioner'; 
                        $ac_seer_tonnage  = $tonnage.' and '. $seer_afue. ' SEER Air Conditioner'; 
                    }
                }
            } else {
                $ac_seer_tonnage = 'Not requested';
                $ac_seer_tonnage_pre = "Not requested";
            }
            if($answer_filter['equipment_type'] == 'Furnace') {
        // Furnace Equipment Model,Price and Seer calcualtion and price calculation         
                if($answer_filter['conditioner_type'] == 'packaged') {
                    $furnace_model_details  = explode('-',$answer_filter['furnace_model']);
                    $furnace_model_details_info = get_posts(array('name'=>sanitize_title($furnace_model_details[0]),'post_type' => 'funrnace'));
                    $afue = get_post_meta(trim($furnace_model_details_info[0]->ID),'afue',true);
                    $furnace_seer_tonnage = str_replace('Furnace Size : ','',$furnace_model_details[1]).
                                    ' BTU and '.$afue.' AFUE Furnace';
                    
                                       
                    $furnace_seer_tonnage_pre = $btu_pre.' and '.$seer_afue.'% AFUE Furnace';
                } else {
                    $furnace_model_details  = explode('-',$answer_filter['furnace_model']);
                    $furnace_model_details_info = get_posts(array('name'=>sanitize_title($furnace_model_details[0]),'post_type' => 'funrnace'));

                    $afue = get_post_meta(trim($furnace_model_details_info[0]->ID),'afue',true);
                    $furnace_seer_tonnage = str_replace('Furnace Size : ','',$furnace_model_details[1]).
                                    ' BTU and '.$afue.' AFUE Furnace';
                    $furnace_seer_tonnage_pre = $btu_pre.' and '.$seer_afue.'% AFUE Furnace';

                }
            } else {

                $furnace_seer_tonnage = 'Not requested';
                $furnace_seer_tonnage_pre = 'Not requested';
                
            }

            if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' ||                                                                              $answer_filter['conditioner_type'] == 'dontknow' )) {
        // Both AC and Furnace Equipment Model,Price and Seer calcualtion and price calculation          

                $ac_seer_tonnage_details = explode("-",$answer_filter['package_split_ac_model']);
                $ac_seer_tonnage_model =  get_posts(array('name'=>sanitize_title($ac_seer_tonnage_details[0]),'post_type' => 'acequipment'));
                $ac_model_id = $ac_seer_tonnage_model[0]->ID;

                

                if($answer_filter['summmer_temp_above_100'] == 'Yes') {
                        $seer = 16;
                } else {
                        $seer = 14;
                }
                
                $furnace_zone = get_field('furnace_zone', $state_id);
                 
                if($furnace_zone == 1) {
                    $zone_type = 'furnace_zone1';
                    $btu_pre_zone = "furnace_zone1_size";
                    $furnace_afue = "80%";	
                } else if($furnace_zone == 2) { 
                    $zone_type = 'furnace_zone2';	
                    $furnace_afue = "95%";
                    $btu_pre_zone = "furnace_zone2_size";
                }
                if($answer_filter['above_3000'] == 'Yes') {
                     $furnace_afue = "96%";
                    $btu_pre_zone = "furnace_zone2_size";
                }

                $btu_pre =  get_field($btu_pre_zone, $area_id);
                
                if($btu_pre == '120K') {
                    $btu_pre = "60K + 60K";
                }
                if($btu_pre == '160K') {
                    $btu_pre = "80K + 80K";
                }
                if($btu_pre == '180K') {
                    $btu_pre = "90K + 90K";
                }
                if($btu_pre == '200K') {
                    $btu_pre = "100K + 100K";
                }
                if($btu_pre == '220K') {
                    $btu_pre = "100K + 120K";
                }
                if($btu_pre == '240K') {
                    $btu_pre = "120K + 120K";
                }
                $btu_pre = $btu_pre ." BTU";
                
               
                //temporary variable; quick fix for no ton
                $tons_temp = str_replace('ton ton','ton',str_replace('AC Size : ','',$ac_seer_tonnage_details[1])); 

                if (strpos($tons_temp, 'ton') == false) {
                    $tons_temp = $tons_temp.' ton(s)';
                }

                $ac_seer_tonnage_pre = $tons_temp.' and '.$seer.' SEER  '.' Air Conditioner';
                $seer = get_post_meta($ac_model_id,'seer',true);
                $ac_seer_tonnage = $tons_temp.' and '.$seer.' SEER  '.' Air Conditioner';
                $furnace_seer_tonnage_details = explode("-",$answer_filter['package_split_furnace_model']);
                
               // echo "Details----".$furnace_seer_tonnage_details[0];
                
                $furnace_seer_tonnage_model =get_posts(array('name'=>sanitize_title(trim($furnace_seer_tonnage_details[0])),'post_type' => 'funrnace'));
                $furnace_model_id = $furnace_seer_tonnage_model[0]->ID;

                $afue = get_post_meta($furnace_seer_tonnage_model[0]->ID,'afue',true);
                $furnace_seer_tonnage = str_replace('Furnace Size : ','',$furnace_seer_tonnage_details[1]).
                                            ' BTU and '.$afue.' AFUE Furnace';
                
                 $furnace_seer_tonnage_pre = $btu_pre.' and '.$furnace_afue.' AFUE Furnace';

            } else if($answer_filter['equipment_type'] == 'Both' && $answer_filter['conditioner_type'] == 'packaged') {
                if($answer_filter['summmer_temp_above_100'] == 'Yes') {
                        $seer = 16;
                } else {
                        $seer = 14;
                }
                
                $furnace_zone = get_field('furnace_zone', $state_id);
                 

                if($furnace_zone == 1) {
                    $zone_type = 'furnace_zone1';
                    $furnace_afue = 80;	
                } else if($furnace_zone == 2) { 
                    $zone_type = 'furnace_zone2';	
                    $furnace_afue = 95;
                }
                
                if($answer_filter['above_3000'] == 'Yes') {
                     $furnace_afue = 96;
                     $btu_pre_zone = "furnace_zone2_size";
                }

                if($answer_filter['packaged_model']!= "") {
                    
                    $btu_pre_zone = "price";
                    $btu_pre =  get_field($btu_pre_zone, $area_id).' BTU';
                    
                    
                    $ac_seer_tonnage_details = explode("-",$answer_filter['packaged_model']);
                    $ac_seer_tonnage_model = get_posts(array('name'=>sanitize_title($ac_seer_tonnage_details[0]),'post_type' => 'packaged'));
                    $ac_model_id = $ac_seer_tonnage_model[0]->ID;
                    $ac_seer_tonnage_pre = $seer.' SEER  '.' Air Conditioner';
                    $seer = get_post_meta($ac_model_id,'seer',true);
                    $size_temp =   str_replace('AC Size : ','',$ac_seer_tonnage_details[1]);
                    if (strpos($size_temp, 'ton') === false) {
                            $size_temp = $size_temp.' ton(s) ';
                        }
                    
                    $ac_seer_tonnage_pre = $size_temp.' and '.$ac_seer_tonnage_pre;
                    $ac_seer_tonnage =  $size_temp.' and '.$seer.' SEER  '.' Air Conditioner';
                    $furnace_seer_tonnage = str_replace('Heater Size : ','',
                                                str_replace('Furnace Size : ','',$ac_seer_tonnage_details[2])).' BTU ';

                    if($afue != '') {
                        $furnace_seer_tonnage = $furnace_seer_tonnage.' and '.$afue.' Furnace';
                        
                        $furnace_seer_tonnage_pre = $btu_pre.' and '.$afue.' Furnace';
                    } else {
                        if($answer_filter['above_3000'] == 'Yes') {
                               $furnace_seer_tonnage = $furnace_seer_tonnage.' and 96% AFUE  Furnace';
                               $furnace_seer_tonnage_pre = $btu_pre.' and 96% AFUE  Furnace';
                        } else {
                                $furnace_zone = get_field('furnace_zone', $state_id);
                                if($furnace_zone == 1) {
                                     $furnace_seer_tonnage = $furnace_seer_tonnage.' and 80% AFUE Furnace';
                                     $furnace_seer_tonnage_pre = $btu_pre.' and 80% AFUE Furnace';
                                } else if($furnace_zone == 2) { 
                                     $furnace_seer_tonnage = $furnace_seer_tonnage.' and 95% AFUE Furnace';
                                     $furnace_seer_tonnage_pre = $btu_pre.' and 95% AFUE Furnace';
                                }
                        }
                    }
                } 
            }

            if($answer_filter['customer_interest'] == 'Yes') {

                $model_chosen = $answer_filter['topbrand'];

                if($answer_filter['equipment_type'] == 'AC') {

                    $model_details = explode("-",$answer_filter['ac_model']);
                    $model_chosen = $model_chosen." Model#: ".$model_details[0].' '.$ac_seer_tonnage.' ';
                    $model_price = 	str_replace('Price : $','',$model_details[2]);
                    $model_price = str_replace(',', '.', $model_price);
                    $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                    $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                    $model_price = (float) $model_price;

                } else if($answer_filter['equipment_type'] == 'Furnace') {
                    $model_details = explode("-",$answer_filter['furnace_model']);
                    $model_chosen = $model_chosen." Model#:".$answer_filter['furnace_model'];
                    $model_price = 	str_replace('Price : $','',$model_details[2]);
                    $model_price = str_replace(',', '.', $model_price);
                    $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                    $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                    $model_price = (float) $model_price;
                } else if($answer_filter['equipment_type'] == 'Both') {

                    if($answer_filter['conditioner_type'] == 'split' || 
                                    $answer_filter['conditioner_type'] == 'dontknow' ) {

                        $model_details = explode("-",$answer_filter['package_split_ac_model']);
                        $model_price = 	str_replace('Price : $','',$model_details[count($model_details)-1]);
                        $model_chosen = $model_chosen."-".$model_details[0];
                        $model_price = str_replace(',', '.', $model_price);
                        $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                        $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                        $model_price = (float) $model_price;


                        $furnace_model_details = explode("-",$answer_filter['package_split_furnace_model']);
                        $furnace_model_price = str_replace('Price : $','',
                                                            $furnace_model_details[count($furnace_model_details)-1]);
                        $furnace_model_chosen = $answer_filter['topbrand']."-".$furnace_model_details[0];
                        $furnace_model_price = str_replace(',', '.', $furnace_model_price);
                        $furnace_model_price = preg_replace("/[^0-9\.]/", "", $furnace_model_price);
                        $furnace_model_price = str_replace('.', '',substr($furnace_model_price, 0, -3)) .                                                                              substr($furnace_model_price, -3);
                        $furnace_model_price = (float) $furnace_model_price;
                        //$model_price = $model_price + $furnace_model_price;
                        //$furnace_model_price_added = 1;


                    } else {

                        $model_details = explode("-",$answer_filter['packaged_model']);
                        $model_price = 	str_replace('Price : $','',$model_details[count($model_details)-1]);
                        $model_chosen = $model_chosen." Model#:".$answer_filter['packaged_model'];
                        $model_price = str_replace(',', '.', $model_price);
                        $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                        $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                        $model_price = (float) $model_price;
                    }

                } 
                $total_price = $total_price+ $model_price;
            } else {
                $model_chosen = $answer_filter['default_model'];
            }
            if($answer_filter['customer_interest'] == 'No') {

                        $model_details = explode(",",$answer_filter['default_model']);
                        $defalut_model_price = 0.0;
                        $defalut_model_chosen = 'Not Requested';
                        $defalut_furnace_model_price = 0.0;
                        $defalut_furnace_model_chosen = 'Not Requested';
                        $ac_zone_det = array('ac_zone1','ac_zone2');
                        $furnace_zone_det = array('furnace_zone1','furnace_zone2');

                        for($i = 0; $i< count($model_details);$i++) {

                          $zone_type = get_post_meta(trim($model_details[$i]), 'zone_type', true);

                            if(in_array($zone_type,$ac_zone_det)) {

                                if($defalut_model_chosen == 'Not Requested'){

                                    $defalut_model_chosen =  get_post_meta(trim($model_details[$i]), 'model', true).
                                                ' '.get_post_field('post_content',trim($model_details[$i]));

                                } else {

                                    $defalut_model_chosen = $defalut_model_chosen.' '.                                                                                     get_post_meta(trim($model_details[$i]), 'model', true).' '.
                                                get_post_field('post_content',trim($model_details[$i]));

                                }

                                $model_price = get_post_meta(trim($model_details[$i]), 'complete_equipment_cost', true);
                                $model_price = str_replace(',', '.', $model_price);
                                $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                                $model_price = str_replace('.', '',substr($model_price, 0, -3)) . 
                                                                        substr($model_price, -3);
                                $model_price = (float) $model_price;

                                $defalut_model_price = $defalut_model_price+$model_price;

                                //added jan 31 for condition Both with Split or Dont know tonnage choosing package zone from area to fix that  */ 
                                if($answer_filter['equipment_type'] == 'Both' && 
                                                ($answer_filter['conditioner_type'] == 'split' ||                                                                          $answer_filter['conditioner_type'] == 'dontknow') ) {
                                     $tonnage = get_field($zone_type, $area_id);

                                        if($tonnage == "6.0") {
                                            $tonnage = "(6 tons) [3 ton(s) + 3 ton(s)]";
                                        }
                                        if($tonnage == "7.0") {
                                            $tonnage = "(7 tons) [3 ton(s) + 4 ton(s)]";
                                        }
                                        if($tonnage == "8.0") {
                                            $tonnage = "(8 tons) [4 ton(s) + 4 ton(s)]";
                                        }
                                        if($tonnage == "9.0") {
                                            $tonnage = "(9 tons) [4 ton(s) + 5 ton(s)]";
                                        }
                                        if($tonnage == "10.0") {
                                            $tonnage = "(10 tons) [5 ton(s) + 5 ton(s)]";
                                        }


                                }


                                if (strpos($tonnage, 'ton') === false) {

                                    if(in_array($tonnage,array('4.0','5.0','3.0','2.0','1.0'))) {
                                        
                                        $tonnage = str_replace(".0",'',$tonnage);

                                    }

                                        $tonnage = $tonnage.' ton(s)';
                                } else {

                                    if(in_array($tonnage,array('4.0','5.0','3.0','2.0','1.0'))) {
                                        
                                        $tonnage = str_replace(".0",'',$tonnage);

                                    }

                                }

                                $ac_seer_tonnage = ton2tons($tonnage).'  and '.
                                        get_post_meta(trim($model_details[$i]), 'seer_afue', true).
                                        ' SEER Air Conditioner';
                                
                                $ac_seer_tonnage_pre = $ac_seer_tonnage;


                            } else if(in_array($zone_type,$furnace_zone_det)) {
                                if($defalut_furnace_model_chosen == 'Not Requested') {
                                     $defalut_furnace_model_chosen = get_post_meta(trim($model_details[$i]), 'model',                                                           true).' '.get_post_field('post_content',trim($model_details[$i]));
                                } else {
                                        $defalut_furnace_model_chosen = $defalut_furnace_model_chosen.' '.                                                                      get_post_meta(trim($model_details[$i]), 'model', true).'                                                                    '.get_post_field('post_content',trim($model_details[$i]));
                                }



                                $furnace_model_price= get_post_meta(trim($model_details[$i]), 'complete_equipment_cost', true);
                                $furnace_model_price = str_replace(',', '.', $furnace_model_price);
                                $furnace_model_price = preg_replace("/[^0-9\.]/", "", $furnace_model_price);
                                $furnace_model_price = str_replace('.', '',substr($furnace_model_price, 0, -3)) . substr($furnace_model_price, -3);
                                $furnace_model_price = (float) $furnace_model_price;
                                $model_price =  $furnace_model_price;
                                $defalut_furnace_model_price = $defalut_furnace_model_price+$furnace_model_price;
                              $furnace_seer_tonnage = get_post_meta(trim($model_details[$i]), 'seer_afue', true);


                                $furnace_seer_size = get_post_meta(trim($model_details[$i]), 'size', true);

                                if($answer_filter['above_3000'] == 'Yes') {
                                       $furnace_seer_tonnage_pre = $furnace_seer_size.' BTU and 96%+ AFUE Furnace';
                                } else {
                                       $furnace_seer_tonnage_pre = $furnace_seer_size.' BTU and 80% AFUE Furnace';
                                }

                              $furnace_seer_tonnage = $furnace_seer_size.' BTU and '.$furnace_seer_tonnage.
                                                                                        ' AFUE Furnace';

                            } else if($zone_type == 'default') {

                                if($defalut_model_chosen != 'Not Requested') {

                                    $defalut_model_chosen = $defalut_model_chosen.' '.                                                                                      get_post_meta(trim($model_details[$i]), 'model', true).'                                                                    '.get_post_field('post_content',trim($model_details[$i]));


                                } else {

                                    $defalut_model_chosen = get_post_meta(trim($model_details[$i]), 'model', true).
                                                ' '.get_post_field('post_content',trim($model_details[$i]));

                                }


                                $model_price = get_post_meta(trim($model_details[$i]), 'complete_equipment_cost', true);
                                $model_price = str_replace(',', '.', $model_price);
                                $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                                $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                                $model_price = (float) $model_price;
                                $defalut_model_price = $defalut_model_price+$model_price;

                                $seer = get_post_meta(trim($model_details[$i]), 'seer_afue', true);
                                $furnace_seer_tonnage = get_post_meta(trim($model_details[$i]), 'size', true);



                                 if($seer == '3 + 4'){

                                    $ac_seer_tonnage = '(7 tons) 3 ton(s) + 4 ton(s) and  14 SEER Air Conditioner';
                                     
                                     $ac_seer_tonnage_pre = $ac_seer_tonnage; 

                                } else if($seer == '4 + 4'){

                                    $ac_seer_tonnage = '(8 tons) 4 ton(s) + 4 ton(s) and 14 SEER Air Conditioner';
                                     $ac_seer_tonnage_pre = $ac_seer_tonnage; 

                                } else if($seer == '4 + 5'){

                                    $ac_seer_tonnage = '(9 tons) 4 ton(s) + 5 ton(s) and 14 SEER Air Conditioner';
                                     $ac_seer_tonnage_pre = $ac_seer_tonnage;

                                } else if($seer == '5 + 5'){

                                    $ac_seer_tonnage = '(10 tons) 5 ton(s) + 5 ton(s) and 14 SEER Air Conditioner';
                                     $ac_seer_tonnage_pre = $ac_seer_tonnage;

                                } else if(in_array($seer,array(2.5,3,3.5,4,5))) {
                                     $ac_seer_tonnage = $seer.' ton(s) and 14 SEER  '.' Air Conditioner';
                                     $ac_seer_tonnage_pre = $ac_seer_tonnage;

                                } else {
                                    $ac_seer_tonnage = $seer.' ton(s) and 14 SEER  '.' Air Conditioner';
                                     $ac_seer_tonnage_pre = $ac_seer_tonnage; 
                                }

                                if($answer_filter['above_3000'] == 'Yes') {
                                       $furnace_seer_tonnage = $furnace_seer_tonnage.' BTU and 96% AFUE Furnace';
                                } else {

                                        $furnace_zone = get_field('furnace_zone', $state_id);
                                        if($furnace_zone == 1) {
                                             $furnace_seer_tonnage = $furnace_seer_tonnage.' and 80% AFUE Furnace';
                                        } else if($furnace_zone == 2) { 
                                             $furnace_seer_tonnage = $furnace_seer_tonnage.' and 95% AFUE Furnace';
                                        }
                                       //$furnace_seer_tonnage = $furnace_seer_tonnage.' BTU and 80% AFUE Furnace';
                                }
                                
                                $furnace_seer_tonnage_pre = $furnace_seer_tonnage;


                            }
                            $total_price = $total_price + $model_price ;
                        }



                /*$model_details = explode(",",$answer_filter['default_model']);
                print_r($model_details);
                exit();
                $default_price = str_replace('Price : $','',$model_details[count($model_details)-1]);
                $default_model_chosen = $answer_filter['default_model'] ;
                $default_price_temp = str_replace("$",'',$default_price);
                $model_price = str_replace(',', '.', $default_price_temp);
                $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                $model_price = (float) $model_price;*/

            }


                $upload_dir = wp_upload_dir();
                $user_dirname = $upload_dir['basedir'].'/smartform/';
                if ( ! file_exists( $user_dirname ) ) {
                    wp_mkdir_p( $user_dirname );
                }
                $path = $user_dirname;
                $filename = rand(0,100000)."_report.pdf";



        $html = '
        <html><head>
            <meta http-equiv="Content-Language" content="en-EN">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
        body{
        margin:auto;
        padding:0px;
        font-family:arial;
        }
        h2{
            text-decoration:underline;
            text-align:center;
        }
        .alignleft{
            float:left;
            margin-right:10px;
            margin-bottom:10px;
        }
        .alignright{
            float:right;
            margin-left:10px;
            margin-bottom:10px;
        }
        .left{
            float:left;
            width:49%;
            margin-right:1%;	
        }
        .right{
            float:right;
            width:49%;
            margin-left:1%;
        }
        .indent{
            text-indent:40px;
        }
        .clearfix, .container {
          zoom: 1;
          clear: both;
        }
        .clearfix:before , .container:before , .clearfix:after, .container:after {
          content: "";
          display: table;
        }
        .clearfix:after, .container:after {
          clear: both;
        }
        .red {
            color:red;
            font-weight:bold;
        }

        .detailed_table {
            border:solid 4px #cccccc;
            width:100%;
            border-collapse: collapse;


        }

        .td,.td_left,.td_right {
         border:solid 4px #cccccc;

        }
        hr {
            margin:15px 0px;

            height:3px;

        }
        </style>
        </head>
        <body style="background-color: #FFFFFF;">
        <div>
        <div class="center" style="text-align:center" >
            <img src="'.SF_PLUGIN_DIR.'/images/asmlogo.png" height="110"/>
        </div>
            <h2>ASM’s Home HVAC Design and Consultation Program℠</h2> 


            <p>The purpose of this program is simple - to empower you with the basic, insider-information required to make                  a well-informed, educated decision on your heating and air conditioning project.</p>

            <p>Unfortunately, the HVAC industry can be ripe with dishonesty and mistrust - it is our hope that we can                    change this. To do that, our goal is to empower you - the consumer - with the same information that we                    have as professional contractors, leveling the playing field, and hopefully building a more trustworthy,                  enjoyable industry for both sides.
            </p>

            <p>This information will be provided in two parts: first, the consultation report itself, which includes                      important information that pertains to your specific heating and air conditioning project, and second, we                  have provided you with a brief explanation of the report’s components, what this information means to you                  and your project, and most importantly, tips on how to use this information to negotiate a fair price, and                make a more informed decision on your HVAC project.
            </p>
        <br>
        <br>
        <h2>PART I - The Report</h2>
        <p>Based on your geographical area, the size of your home, and the answers to the questions you have provided,                we’ve made the following observations and/or recommendations:</p>
        <h4>1.Project Completion Time</h4>
        <p class="indent">Based on the information you have provided, it is our estimate that your project should take                  around <span class="red">'.$number_of_days.' </span>for your project to be completed.  This information will              be important later, as it will help us calculate the estimated labor costs for your installation.
        </p>
        <hr/>
        <h4>2. Estimated System Size, SEER Rating, and AFUE Rating</h4>

        <p class="indent">Based on the information that you provided, we estimate that a heating and air conditioning                   system that is of the following size and efficiency rating would work best for your project:</p>';

            if($ac_seer_tonnage_pre != 'Not requested') {
                $html = $html.'	<p>Air Conditioner (size in tonnage and recommended SEER rating): 
                    <span class="red">'.ton2tons($ac_seer_tonnage_pre).'</span></p>';
                
                
            }

            if($furnace_seer_tonnage_pre != 'Not requested') { 
                $html = $html.'	<p>Furnace (minimum BTU and recommended AFUE rating): 
                    <span class="red">'.$furnace_seer_tonnage_pre.'</span></p>';
            }

            //for removeing extra price from  Title 

         $model_chosen = str_replace($model_price,'',$model_chosen);
          $model_chosen = ucfirst($model_chosen);    

        $html = $html.'	    
        <p>Important notes based on your selections:</p>
        <ul>
            <li>Although the reason why this is so will be discussed in more detail later, it is important to properly      
                    size your air conditioner or furnace, both for energy usage and proper cooling and heating.  
            </li>
            <li>Based on the answers you provided, as well as our experience and observations, if an air conditioner is 
                installed, it may not be worth the additional money to purchase an air conditioner higher than a value of                 16 SEER in your area.  Purchasing a high-SEER unit may not provide an adequate return on your investment                   in the long-term.
            </li>
            <li>Any furnace recommendations are based on a gas furnace.  <b>Tip:</b> If natural gas is not available in                     your area, you can have your HVAC contractor install a “propane conversion kit” to modify a gas furnace                   for use with propane.  This kit typically costs around $45 - $55, and takes just minutes to install.  
                It will include a new expansion valve that is made for use with the propane molecule.
            </li>
        </ul>
        <hr/>
        <h4>3. Wholesale Contractor’s Price for Your Requested Brand and Model of Equipment: </h4>
        <p>Per your request, here is the current wholesale contractor’s pricing for the equipment you requested:</p>';

            if($answer_filter['customer_interest'] == 'Yes') {

                if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' ||                              $answer_filter['conditioner_type'] == 'dontknow') ) {

                    if($ac_seer_tonnage != 'Not requested' && $model_chosen != 'Not requested' ) {

                         $html = $html.' <span class="red">'.ton2tons($model_chosen.'  '.$ac_seer_tonnage).'</span>                                         <span class="alignright red"> $'.number_format($model_price,2).'</span><br>';

                    }


                    if($ac_seer_tonnage != 'Not requested' && $furnace_seer_tonnage != 'Not requested' ) {

                        $html = $html.'	<span class="red">'
                                            .ucfirst($furnace_model_chosen).' '.$furnace_seer_tonnage.'</span>   
                                            <span class="alignright red">$'.number_format($furnace_model_price,2).
                                        '</span>';
                    }

                } else  if($answer_filter['equipment_type'] == 'Both' && 
                           ($answer_filter['conditioner_type'] == 'packaged' ) ) {

                        $html = $html.'	<span class="red">'.
                                        ton2tons(ucfirst(str_replace(number_format($model_price,2),'',$model_chosen)))
                                        .number_format($model_price,2).'</span>';


                } else if($answer_filter['equipment_type'] == 'AC' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     
                        $html = $html.' <span class="red">'.ton2tons(ucfirst(str_replace(number_format($model_price,2),'',
                                                            $model_chosen))).' $'.number_format($model_price,2).'</span>';
                } else if($answer_filter['equipment_type'] == 'Furnace' && 
                                                            $answer_filter['conditioner_type'] == 'packaged' ) {

                        $html = $html.' <span class="red">'.
                                ton2tons(ucfirst(str_replace(number_format($model_price,2),'',$model_chosen))).
                                number_format($model_price,2).'</span>';

                } else if($answer_filter['equipment_type'] == 'AC'  && 
                                ($answer_filter['conditioner_type'] == 'split' || 
                                $answer_filter['conditioner_type'] == 'dontknow') ) {
                    
                    $html = $html.'<span class="red">
                                '.ton2tons(str_replace(number_format($model_price,2),'  $',ucfirst($model_chosen))).'
                                </span> <span class="alignright red"> $'.number_format($model_price,2).'</span><br>';
                    
                    
                } else {

                    if($model_price!= "") {
                        //$answer_filter['default_model']
                        $html = $html.'	<span class="red">'.ton2tons(str_replace(number_format($model_price,2),'',
                                                                                 ucfirst($model_chosen))).'</span>
                                                        <span class="alignright red">'.number_format($model_price,2).
                                    '</span><br>';
                    } else {
                        $html = $html.'	<span class="red">'.ucfirst($model_chosen).'</span>';
                    }
                }
                
                
            } else {

                if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' ||      $answer_filter['conditioner_type'] == 'dontknow') ) {

                    if($ac_seer_tonnage != 'Not requested' && $defalut_model_chosen != 'Not requested' ) {

                    $html = $html.'	<span class="red">'.ton2tons(ucfirst($defalut_model_chosen).'  '.$ac_seer_tonnage).'</span>   <span class="alignright red"> $'.number_format($defalut_model_price,2).'</span><br>';

                    }

                    if( $defalut_furnace_model_chosen != 'Not requested' ) {

                        $html = $html.'	<span class="red">'.ton2tons(ucfirst($defalut_furnace_model_chosen)).'</span>   <span class="alignright red"> $'.number_format($defalut_furnace_model_price,2).'</span>';

                    }




                } else {

                    if($defalut_model_chosen != 'Not Requested' or $defalut_model_price != 0) {


                        if($model_price!= "") {
                            //$answer_filter['default_model']
                            $html = $html.'	<span class="red">'.ton2tons(ucfirst($defalut_model_chosen)).'</span>      <span class="alignright red">$'.number_format($defalut_model_price,2).'</span><br>';
                        } else {
                            $html = $html.'<span class="red">'.ton2tons(ucfirst($defalut_model_chosen)).'</span>';
                        }


                    } else {

                        if( $defalut_furnace_model_chosen != 'Not requested' ) {

                            $html = $html.'<span class="red"> '.ucfirst($defalut_furnace_model_chosen).'  '.$furnace_seer_tonnage.'</span>   <span class="alignright red"> $'.number_format($furnace_model_price,2).'</span>';
                        }
                    }
                }

            }


            $ac_seer_tonnage_prev = $ac_seer_tonnage;

            $defalut_furnace_model_chosen_prev = $defalut_furnace_model_chosen;

            $furnace_seer_tonnage_prev = $furnace_seer_tonnage;

            $html = $html.'	<hr/><h4>4. ASM’s Recommendation on Equipment for Your Home:  </h4>
                        <p>Based on our experience, as well as the information you have provided, 
                            we would recommend the following equipment for your project:</p>';   
            
        /* ########### ASM Recommended Systems  Start ############### */

                $type = $answer_filter['equipment_type'];
                $state = strtolower($answer_filter['location_state']);
                $area = $answer_filter['area_size'];
                $question_4 = $answer_filter['above_3000'] ;
                $question_5 = $answer_filter['summmer_temp_above_100'];
                $question_7 = $answer_filter['conditioner_type'];
                $question_8 = $answer_filter['ceilings_height'];
                $areas_footage = array("000-600","600-800","801-1000","1001-1200","1201-1400","1401-1700",
                    "1701-2100","2101-2400","2401-2800","2801-3200","3201-3600","3601-4000");


                $choices = array();
                // type should be SPLIT SYSTEMS or PACKAGED SYSTEMS
                $equip_type = "";
                //area is is the post ID
                //AC Zone 1/2 or Furnace zone 
                $zone_type = "";#gf_1
                // Seer
                $seer_afue = "";
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

                    if($question_5 == 'Yes') {
                        $ac_seer_afue = 16;
                    }

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


                if(in_array($type,array('AC'))) {


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

                        /* */
                       
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
                                ),array(
                                    'key'     => 'zone_type',
                                    'value'   => $zone_type,
                                    'compare' => '='
                                ),array(
                                    'key'     => 'seer_afue',
                                    'value'   => 14,
                                    'compare' => '='
                                ),



                            )
                        );
                    }
                } else if(in_array($type,array('Furnace'))) {


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
                                ),array(
                                    'key'     => 'zone_type',
                                    'value'   => $zone_type,
                                    'compare' => '='
                                )



                            )
                        );
                    }
                } else {

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
                                    'value'   => array('14',$furnace_seer_afue),
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
                                    get_post_meta(get_the_ID(),'zone_type',true);
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



                $model_details = array();
                for($i=0; $i<count($result['result']);$i++) {

                    if (strpos($result['result'][$i]['value'], ',') !== false) {
                         $model_ids = explode(" , ",$result['result'][$i]['value']);
                        for($model_ids_count= 0; $model_ids_count < count($model_ids); $model_ids_count++ ) {


                            array_push($model_details,trim($model_ids[$model_ids_count]));

                        }
                    } else {

                    array_push($model_details,$result['result'][$i]['value']);
                    }
                }
                    $show_defalut_model_price = 0.0;
                    $show_defalut_model_chosen = 'Not Requested';
                    $show_defalut_furnace_model_price = 0.0;
                    $show_defalut_furnace_model_chosen = 'Not Requested';
                    $ac_zone_det = array('ac_zone1','ac_zone2');
                    $furnace_zone_det = array('furnace_zone1','furnace_zone2');
                for($i = 0; $i< count($model_details);$i++) {

                   $zone_type = get_post_meta(trim($model_details[$i]), 'zone_type', true);

                    if(in_array($zone_type,$ac_zone_det)) {

                        if($show_defalut_model_chosen == 'Not Requested'){

                            $show_defalut_model_chosen =  get_post_meta(trim($model_details[$i]), 'model', true).' '.get_post_field('post_content',trim($model_details[$i]));

                        } else {

                            $show_defalut_model_chosen = $show_defalut_model_chosen.' '. get_post_meta(trim($model_details[$i]), 'model', true).' '.get_post_field('post_content',trim($model_details[$i]));

                        }




                        $model_price_temp = get_post_meta(trim($model_details[$i]), 'complete_equipment_cost', true);
                        $model_price_temp = str_replace(',', '.', $model_price_temp);
                        $model_price_temp = preg_replace("/[^0-9\.]/", "", $model_price_temp);
                        $model_price_temp = str_replace('.', '',substr($model_price_temp, 0, -3)) . substr($model_price_temp, -3);
                        $model_price_temp = (float) $model_price_temp;




                        $show_defalut_model_price = $show_defalut_model_price+$model_price_temp;

                        //get_post_meta(trim($model_details[$i]), 'size', true)

                        $ac_seer_tonnage = get_post_meta(trim($model_details[$i]), 'size', true).' '.get_post_meta(trim($model_details[$i]), 'seer_afue', true).' SEER Air Conditioner';

                          $ac_seer_tonnage = ton2tons(str_replace('ton','',get_post_meta(trim($model_details[$i]), 'size', true)).' ton(s) and '.get_post_meta(trim($model_details[$i]), 'seer_afue', true)).' SEER Air Conditioner';


                    } else if(in_array($zone_type,$furnace_zone_det)) {

                        if($show_defalut_furnace_model_chosen == 'Not Requested') {
                             $show_defalut_furnace_model_chosen = get_post_meta(trim($model_details[$i]), 'model', true).' '.get_post_field('post_content',trim($model_details[$i]));
                        } else {
                                $show_defalut_furnace_model_chosen = $show_defalut_furnace_model_chosen.' '. get_post_meta(trim($model_details[$i]), 'model', true).' '.get_post_field('post_content',trim($model_details[$i]));
                        }



                        $sfurnace_model_price= get_post_meta(trim($model_details[$i]), 'complete_equipment_cost', true);
                        $sfurnace_model_price = str_replace(',', '.', $sfurnace_model_price);
                        $sfurnace_model_price = preg_replace("/[^0-9\.]/", "", $sfurnace_model_price);
                        $sfurnace_model_price = str_replace('.', '',substr($sfurnace_model_price, 0, -3)) . substr($sfurnace_model_price, -3);
                        $sfurnace_model_price = (float) $sfurnace_model_price;

                        $show_defalut_furnace_model_price = $show_defalut_furnace_model_price+$sfurnace_model_price;
                        $furnace_seer_tonnage = get_post_meta(trim($model_details[$i]), 'seer_afue', true);
                        $furnace_seer_tonnage = get_post_meta(trim($model_details[$i]), 'size', true).' BTU, and '.$furnace_seer_tonnage.' AFUE Furnace';

                    } else if($zone_type == 'default') {


                        if($show_defalut_model_chosen != 'Not Requested' ) {
                            $show_defalut_model_chosen = $show_defalut_model_chosen.' '. get_post_meta(trim($model_details[$i]), 'model', true).' '.get_post_field('post_content',trim($model_details[$i]));
                        } else {

                                                $show_defalut_model_chosen =  get_post_meta(trim($model_details[$i]), 'model', true).' '.get_post_field('post_content',trim($model_details[$i]));

                        }


                        $model_price_temp = get_post_meta(trim($model_details[$i]), 'complete_equipment_cost', true);
                        $model_price_temp = str_replace(',', '.', $model_price_temp);
                        $model_price_temp = preg_replace("/[^0-9\.]/", "", $model_price_temp);
                        $model_price_temp = str_replace('.', '',substr($model_price_temp, 0, -3)) . substr($model_price_temp, -3);
                        $model_price_temp = (float) $model_price_temp;
                        $show_defalut_model_price = $show_defalut_model_price+$model_price_temp;
                        
                         $default_ac_seer_tonnage = get_post_meta(trim($model_details[$i]), 'size', true).' 14 SEER Air Conditioner';
                        
                        $default_furnace_seer_tonnage = get_post_meta(trim($model_details[$i]), 'size', true).' BTU, and '.$furnace_seer_tonnage.' AFUE Furnace';


                    }

                        $model_price_temp = 0.0;

                }



            if($show_defalut_model_chosen != 'Not Requested') {
                
                if($zone_type == 'default') {
                    
                    $html = $html.'	<span class="red">'.ton2tons($show_defalut_model_chosen.'  '. $default_ac_seer_tonnage ).'</span>   <span class="alignright red"> $'.number_format($show_defalut_model_price,2).'</span><br>';
                
                } else {
                
                    $html = $html.'	<span class="red">'.ton2tons($show_defalut_model_chosen.'  '.$ac_seer_tonnage).'</span>   <span class="alignright red"> $'.number_format($show_defalut_model_price,2).'</span><br>';
                }

                

            }

            if($show_defalut_furnace_model_chosen != 'Not Requested') {
                
                if($zone_type == 'default') {
                     $html = $html.'	<span class="red">'.$show_defalut_furnace_model_chosen.'</span>   <span class="alignright red"> $'.number_format($show_defalut_furnace_model_price,2).'</span>';

                    
                } else {
                
                     $html = $html.'	<span class="red">'.$show_defalut_furnace_model_chosen.'</span>   <span class="alignright red"> $'.number_format($show_defalut_furnace_model_price,2).'</span>';
                
                
                }

            }




    /* ########### ASM Recommended Systems End ################# */
            if($model_price!= "") {
                //$answer_filter['default_model']
               // $html = $html.'	<span class="red">'.$defalut_model_chosen.'</span>      <span class="alignright red">'.$model_price.'</span><br>';
            } else {
                //$html = $html.'	<span class="red">'.$defalut_model_chosen.'</span>';
            }




                
        $html = $html.'<ul>
        <li>We have recommended Day & Night equipment for your project.  This is based solely on our experience - feel free to use other brands as you see fit.  We are in no way beholden to Day & Night, but base this assessment simply on our own experience.  In other words, we were not paid to say this or promote this product in any way. </li> 
        <li>If Day & Night is not available in your area, then Goodman or Daiken are also reliable, reasonably priced brands that you can use.</li>
        <li>Day & Night is made by United Technologies, which also makes Carrier, Bryant, and Payne.  However, Day & Night is more reasonably priced than Carrier, despite featuring most of the same internal components, including Aspen coils.  In our experience, they are rugged, reliable, and their customer service is top notch (for later down the line).</li>
        </ul>
        <hr/>
        <h4>5. Average Labor Costs in Your Area. </h4>
        <p>Based on the location you have selected, the average price of labor for an experienced HVAC Technician in your area is estimated at <span class="red">'.get_the_title($state_id).': $'. number_format(get_field('hvac_wage_for_1_men_team', $state_id),2).' / hr</span>
        </p>
        <p><span>Average cost for a 2-man HVAC team, per day: <span class="red">$'.get_field('for_1_day_job', $state_id).'</span></span></p>
        <hr/>
        <h4>6. Estimated Total Cost to a Contractor for the Completion of Your Project <br/>(i.e. what the contractor’s expenses are to complete your job):
        </h4>
        <p>In this section, we will take the information previously discussed, add supplemental information based on some of the answers you provided, and incorporate it into an equation that will allow us to come up with an estimated cost of installation for your heating and air conditioner.  It will factor in the price of equipment, estimated labor costs, additional materials, as well as other factors, such as general liability and workman’s compensation insurance.  In short, this section will be an estimate of what the contractor is actually paying to complete your project.  Let’s get started:
        </p>
        <ol type="a">
        <li style="margin-bottom:10px;">Price of the equipment you have selected:<br/>';

             //$html = $html. "<br/>Model Price Testing2".$model_price;

        if($answer_filter['customer_interest'] == 'Yes') {

                if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' || $answer_filter['conditioner_type'] == 'dontknow') ) {


                    //Modified in  jan 31;



                    $html = $html.'	<span class="red">  -'.ton2tons(ucfirst($model_chosen).'  '.$ac_seer_tonnage_prev).' </span><span class="alignright red">$'.number_format($model_price,2).'</span><br/>';

                    $html = $html.'	<span  class="red"> -'.ton2tons(ucfirst($furnace_model_chosen).'  '.$furnace_seer_tonnage_prev).'</span><span class="alignright red"> $'.number_format($furnace_model_price,2).'</span>';

                    //if($furnace_model_price_added == 0) {
                        $total_price = $total_price+$furnace_model_price;
                    //}



                } else if($answer_filter['equipment_type'] == 'Both' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     $html = $html.'	<span  class="red"> -'.ton2tons(ucfirst(str_replace(number_format($model_price,2),'',$model_chosen))).'</span><span class="alignright red">'.number_format($model_price,2).'</span>';

                } else if($answer_filter['equipment_type'] == 'AC' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     $html = $html.'	<span  class="red"> -'.ton2tons(ucfirst(str_replace(number_format($model_price,2),'',$model_chosen))).' </span><span class="alignright red">$'.number_format($model_price,2).'</span>';

                } else if($answer_filter['equipment_type'] == 'Furnace' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     $html = $html.'	<span  class="red"> -'.ton2tons(ucfirst(str_replace(number_format($model_price,2),'',$model_chosen))).'</span><span class="alignright red">'.number_format($model_price,2).'</span>';

                }  else {

                    if($model_price!= "") {
                        //$answer_filter['default_model']
                        $html = $html.'	<span  class="red"> -'.ton2tons(str_replace(number_format($model_price,2),'',ucfirst($model_chosen))).' </span><span class="alignright red">  $'.number_format(str_replace('$','',$model_price),2).'</span>';
                    } else {
                        $html = $html.'	<span  class="red"> -'.ucfirst($model_chosen).'</span>';
                    }
                }
            } 

        if($answer_filter['customer_interest'] == 'No') {
        /* ################# */
            if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' || $answer_filter['conditioner_type'] == 'dontknow') ) {

                if($ac_seer_tonnage != 'Not requested' && $defalut_model_chosen != 'Not requested' ) {

                     $html = $html.'<span class="red"> -'.ton2tons(ucfirst($defalut_model_chosen).'  '.$ac_seer_tonnage).'</span><span class="alignright red"> $'.number_format($model_price,2).'</span><br>';


                }

                if( $defalut_furnace_model_chosen != 'Not requested' ) {


                    $html = $html.'<span class="red"> -'.ucfirst($defalut_furnace_model_chosen).'  '.$furnace_seer_tonnage.'</span><span class="alignright red"> $'.number_format($furnace_model_price,2).'</span>';

                }
            } else if ($answer_filter['equipment_type'] == 'Both' && $answer_filter['conditioner_type'] == 'packaged' ) {



                if($ac_seer_tonnage != 'Not requested' && $defalut_model_chosen != 'Not requested' ) {

                     $html = $html.'<span class="red"> -'.ton2tons(ucfirst($defalut_model_chosen).'  '.$ac_seer_tonnage).'</span><span class="alignright red"> $'.number_format($model_price,2).'</span><br>';


                }

                if( $defalut_furnace_model_chosen != 'Not requested' ) {


                   // $html = $html.'<span class="red">'.ucfirst($defalut_furnace_model_chosen).'  '.$furnace_seer_tonnage.'</span><span class="alignright red"> $'.$furnace_model_price.'</span>';

                }


            } else  if( strtolower($defalut_furnace_model_chosen) != strtolower('Not requested') ) {


                    $html = $html.'<span class="red"> -'.ton2tons(ucfirst($defalut_furnace_model_chosen).'  '.$furnace_seer_tonnage).'</span><span class="alignright red"> $'.number_format($furnace_model_price,2).'</span>';

                }else {

                if($ac_seer_tonnage != 'Not requested' && $defalut_model_chosen != 'Not requested' ) {

                     $html = $html.'<span class="red" -> '.ton2tons(ucfirst($defalut_model_chosen).'  '.$ac_seer_tonnage).'</span><span class="alignright red"> $'.number_format($model_price).'</span><br>';


                }



            }




        /* ################# */    




                /*if($default_price!= "") {
                    $html = $html.'	<h4><span  class="red">'.ucfirst($default_model_chosen).':    '.$default_price.'</span></h4>';
                } else if($answer_filter['equipment_type'] == 'Both' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     $html = $html.'	<span  class="red">'.ucfirst($model_chosen).'</span>';

                } else if($answer_filter['equipment_type'] == 'AC' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     $html = $html.'	<span  class="red">'.ucfirst($model_chosen).'</span>';

                } else if($answer_filter['equipment_type'] == 'Furnace' && $answer_filter['conditioner_type'] == 'packaged' ) {

                     $html = $html.'	<span  class="red">'.ucfirst($model_chosen).'</span>';

                }  */
            }
            $html = $html.'</li>';
        $total_price = $total_price+(get_field('for_1_day_job', $state_id) * $number_of_days_number);
        if($answer_filter['new_duct'] == 'Yes' ) {
            $duct_cost = get_field('duct_work_price', $area_id);	
            $total_price = $total_price+$duct_cost;

        } else {
        $duct_cost = 0.0;
        }
        $total_price = $total_price+912;
        $total_price = $total_price+681;
        $total_price = $total_price; 


        $html = $html.'
        <li style="margin-bottom:10px;">
        <div class="red clearfix">Total estimated labor cost for your project:</div>
                         <span style="display:block;width:100%;text-align:right;" class="red alignright"> $'.number_format(get_field('for_1_day_job', $state_id) * $number_of_days_number,2).'</span></li>

        <li style="margin-bottom:10px;">
         Estimated price of ductwork (including additional labor and material requirements):<br> 
        <span style="display:block;width:100%;text-align:right;" class="red alignright"> $'.number_format($duct_cost,2).'</span></li>
        <li>
        Estimated cost of miscellaneous materials (gas, materials, and other expenses):<br><span style="display:block;width:100%;text-align:right;" class="red alignright"> $912.00</span>           
        </li>
        <li style="margin-bottom:10px;">
        Estimated General Liability Insurance, Workman’s Compensation Insurance, and other miscellaneous administrative fees, etc.: <br>
        <span style="display:block;width:100%;text-align:right;" class="red alignright">$681.00</span>           
        </li>
        <li style="margin-bottom:10px;">
        Estimated total cost to a contractor for your project:<br>
        <span style="display:block;width:100%;text-align:right;" class="red alignright">$'.number_format($total_price,2).'</span>           
        </li>
        </ol>
        <div class="clearfix" style="height:80px">&nbsp;</div>
        <table border="4" class="detailed_table clearfix">
            <tr><td colspan="3">Fair Price Calculation (Based on the Information You Provided)</td></tr>
            <tr>
                <td style="background-color:#E2E4E3;width:250px;" class="td_left">Profit Margin for Contractor</td>
                <td class="td">Proposed Price</td>
                <td class="td_right">Fair Price ?</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">10%</td>
                <td class="td">$'.number_format($total_price+($total_price*0.10),2).'</td>
                <td style="background-color:#FF2C21;" class="td_right">Too Low; look elsewhere</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">20%</td>
                <td class="td">$'.number_format($total_price+($total_price*0.20),2).'</td>
                <td style="background-color:#FFE061;" class="td_right">Too LOW; quality may suffer.</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">30%</td>
                <td class="td">$'.number_format($total_price+($total_price*0.30),2).'</td>
                <td style="background-color:#6DC037;" class="td_right">LOW, but fair</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">40%</td>
                <td>$'.number_format($total_price+($total_price*0.40),2).'</td>
                <td sytle="background-color:#9CE159;" class="td_right">FAIR</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">50%</td>
                <td class="td">$'.number_format($total_price+($total_price*0.50),2).'</td>
                <td style="background-color:#6DC037;" class="td_right">HIGH FAIR(ASM typical price)</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">60%</td>
                <td class="td">$'.number_format($total_price+($total_price*0.60),2).'</td>
                <td style="background-color:#FFE061;" class="td_right">SLIGHTLY HIGH.</td>
            </tr>
            <tr>
                <td style="background-color:#E2E4E3;" class="td_left">70%</td>
                <td>$'.number_format($total_price+($total_price*0.70),2).'</td>
                <td style="background-color:#FF2C21;">Too HIGH; look elsewhere.</td>
            </tr>
        </table>

        <div class="clearfix" style="height:180px"> &nbsp;</div>
        <h3 style="text-align:center;">Description of Fair Price Ranges</h3>

        <p><b>TOO LOW:</b>  Contractors who bid in this price range should be taken with a decent dose of caution.  Although you are looking for the best price, to use a cliché, you get what you pay for.  Why are they able to bid so low?  What are they skimping on…materials?  Labor?  Is it possible that they are just extremely efficient?  Yes, it’s possible…but a betting man would use caution.</p>

        <p><b>LOW:</b>  Contractors who bid in this range can be taken a bit more seriously, but caution should still be used.  We’d recommend that you ensure that they have proper workman’s compensation insurance and other requirements before signing.</p>

        <p><b>LOW but FAIR:</b>  These contractors might just be efficient at what they do, or they may be a small, one-man company.  Contractors in this range should be taken more seriously.</p>

        <p><b>FAIR PRICE:</b>  This is your target, and is a fair price for your project.  Many legitimate contractors will fall into this price range, but still ensure that you perform your due diligence in researching any contractor.</p>

        <p><b>HIGH-FAIR:</b>  This is the price range where many of the high-quality contractors with solid workmanship and an established reputation will typically fall.  Better (more expensive) employees and a large pool of prospective clients will cause them to charge a little bit more.  If you are extremely anxious about the work to be done, and want to ensure that it is done right, it may be worth examining contractors in this price range.  You’ll pay a little bit extra, but it should be done right.</p>

        <p><b>SLIGHTLY HIGH:</b>  These contractors are a bit pricy, but are still within the range of reasonable.  If you like a contractor, and this is their price range, then give them some serious consideration…after you negotiate with them.</p>

        <p><b>TOO HIGH:</b>  Typically, the only time you would consider this price range is if you can’t find anyone else who will do it.  We’d recommend shopping around if a contractor falls within this price range or higher. </p> 

        <h3 style="text-align:center;"> Further Discussion</h3>

         <p class="indent">In order to understand how these prices were calculated, it is important to understand how the contracting industry works - specifically the heating and air conditioning industry.  Remember, the profit margins above are profit to the company, not the guy who owns it…there are still other expenses that have to be accounted for, such as taxes, administrative employees, advertising, etc.  That is why the profit margin is so high - these expenses will still have to come out of the profit margins listed above, and are part of running a legitimate business (these prices are estimates for legitimate, licensed contractors)  </p>  

        <p class="indent">The expenses that the contractor has to pay out of his profit margin, are variables that depend on the corporate culture of each individual business, and cannot be accounted for in a calculator, nor should they be.  It may sound cold, but it is not your problem, it is their problem.</p>  

        <p class="indent">Therefore, to keep your results as accurate as possible, we are giving you the total profit margin for your job.  For instance, a really efficient company might only spend 10% of that profit on other company expenses (admin, etc.) and pocket the rest - good for them.  A really inefficient company might live in poverty even at a 60% profit margin.  Again, that isn’t your problem, and I’d urge you to stick with the estimated price guidelines above.  All I want you to do is realize that just because a company’s prices are high, does NOT mean that they are necessarily dishonest or trying to scam you (although they certainly could be).  They may still be learning how to run their business efficiently.</p> 


        <p class="indent"><b>At this point, it is recommended that you take a 15 minute break, and allow your brain to process the some of the information provided above.</b></p>

        <h2>PART II - The Explanation</h2>
        <p class="indent">In this section, we will break down the information provided in the Home HVAC Design and Consultation Program Report.  Although many of you are familiar with ASM through reading our online articles, it is still important that everyone reading this has a basic understanding of some of the key terms concepts used in the industry, as well as how some of these features will affect the final price that you pay.  This section will allow you to gain more from the information reported above.</p>

        <h3>1.Important Terms and Concepts</h3>
        <h4>SEER Rating</h4>
        <div class="clearfix">
        <div class="left">

        SEER stands for Seasonal Energy Efficiency Ratio and is the most common way to evaluate an air conditioner’s efficiency.  It is important to understand that this is only a measure of cooling power, and applies to air conditioning only.  Simply put, an air conditioner’s SEER rating is the ratio of the cooling output of an AC unit over a typical cooling season (measured in British Thermal Units, or “BTUs”), divided by the energy consumed in Watt-Hours.  Generally, the higher a unit’s SEER value, the more efficient it is.
        </div>
        <div class="right" >
            <img src="'.SF_PLUGIN_DIR.'/images/energy_16.png"/>
        </div>
        </div>
        <blockquote>
        <b>Tips:</b> There are only a few occasions in which we recommend installing a unit that is higher than 16 SEER.  This is because the technology required to make these gains isn’t quite there yet, in our opinion, and sacrifices an element of reliability.  We have just encountered too many problems with “high-SEER” units on repair calls.  As such, we highly recommend sticking to 16 SEER, single-speed units and below for your air conditioner.
        </blockquote>

        <h4>AFUE Rating</h4>
        <div class="clearfix">
        <div class="left">
        AFUE stands for Annual Fuel Utilization Efficiency, and can be thought of as being something similar to SEER ratings for your air conditioner, only these are used to measure the efficiency of your furnace.  Unlike SEER, which has an arbitrary rating associated with it (like SEER-18, etc.), AFUE is actually far simpler to read and understand.  A furnace’s AFUE rating is listed as a percentage of how much fuel it can convert into usable heat, with a scale ranging from around 30% - 100% AFUE (anything less than 30 would be useless). 
        </div>
        <div class="right" >
            <img src="'.SF_PLUGIN_DIR.'images/energyguide_96.png"/>
        </div>
        </div>
        <p>For instance, a furnace with an AFUE rating of 85%, would mean that 85% of its fuel is transferred directly into usable energy that is utilized to heat your home, and the remaining 15% is lost through the exhaust flue as thermal waste.</p>

        <h4>Tonnage (“Tons”) - Proper Air Conditioner Sizing</h4>
        <p>
            <img src="'.SF_PLUGIN_DIR.'images/usmap.png" alt="us" class="alignleft"/>
        Central air conditioners come in a variety of sizes, and the size is measured in “tons.”  Now, contrary to what you might think, the tonnage of an air conditioning unit is not actually based on its weight.  A “ton” is a measure of an air conditioner’s ability to cool your home. </p> 

        <p>For instance, one ton is the ability of your air conditioner to cool 12,000 BTUs (British Thermal Units) in an hour.  Likewise, a “2-ton” central air conditioner is able to cool 24,000 BTUs per hour.  So, this then begs the question: what is a BTU?  A BTU is the amount of energy required to heat or cool one pound of water by one degree Fahrenheit.  So, a 1-ton air conditioner can cool 12,000 pounds of water by one degree Fahrenheit, every hour.  The higher the tonnage, the more cooling ability that your air conditioner will have.  </p>

        <p>Selecting an air conditioner that is properly sized for your home-size and region is one of the most important things that you will do on this project.  Although getting into sizing calculations is outside the scope of this report, it is still important to have a decent estimate on size, and to know some things about a properly sized air conditioner.  First, an undersized air conditioner will run continuously, increasing your electric bill and putting undue wear and tear on your unit - decreasing its service life.  Second, an oversized air conditioner will not run long enough, and will “short-cycle,” meaning that it never completes a full cooling cycle, as it was designed to do.  This will also decrease the efficiency of the unit, and increase electricity usage.  As such, it is important to get the size of your air conditioner just right. </p> 
        <blockquote>
        <b>Tips:</b> If you live in a humid area, it might be advantageous to size your air conditioner slightly small.  This will allow your AC to run a little bit longer per cycle, which will, in turn, remove more humidity, making it more comfortable in your house at a higher temperature. <b> This has already been factored into the estimated size calculations in your report, based on the location you selected.</b>  However, the estimate provided is a guideline to point you in the right direction - it would be a good idea to consult with your chosen contractor before making your final decision.
        </blockquote>	
        <div class="clearfix" style="height:150px"></div>
        <h4>British Thermal Units (“BTUs”) - Proper Furnace Sizing</h4>
        <div class="clearfix">
        <div class="left">
        <p>Unlike air conditioners where the sizing measurements are converted from BTUs to tonnage, furnaces (for whatever reason) are not converted.  They keep their BTU value as a measure of their heating ability.  However, it is not uncommon in the industry to use tonnage as a slang for furnaces as well.

        Proper furnace sizing is less important than proper air conditioner sizing (to be clear: that is not to say that furnace sizing is unimportant).  This is because thermodynamically, it is significantly easier for a system to heat a house than it is to properly cool it.  That being said, getting a proper size is still important.
        </p>
        </div>
        <div class="right">
        <img src="'.SF_PLUGIN_DIR.'images/equipment.png" class="alignright" width="170">
        </div>
        </div>
        <blockquote><b>Tips:</b> The BTUs listed in the above report are minimum heating recommendations based on the answers you provided to your questions.  Anything over that BTU will suffice, as will a furnace that is slightly smaller.  Just be aware that a furnace that is undersized will use more fuel to heat your home than a properly sized unit.  An oversized furnace, within reason, is not as big of a consideration as it is in air conditioning.  Be sure to consult your chosen contractor before making your final decision.</blockquote>

        <h3>2. Tips on How to Use The Home HVAC Design and Consultation Program Report.</h3>
        <p>This section should be self explanatory - we have provided you with an abundance of information, and it’s time that you used it. The wholesale contractor prices provided above are meant to be used at your discretion, but there are a couple of important things that you need to keep in mind: first, a contractor needs to make a profit, and since people don’t buy air conditioners and furnaces every day, this will organically drive up the profit margins for the industry, so just realize that you’re going to have to pay for their services - just accept that now.  Second, the prices provided are actually based on the prices charged by wholesale distributors, but a contractor may still get a better price if they produce a high volume of sales for the year. In other words, the prices provided would be the high-end of what a contractor would pay, which is good for you in the negotiation.  Let’s take a look:</p>

        <h4>a. Negotiating 101 - Basic Principles for Your Negotiation</h4>
        <ul>
            <li><b>Business Definition of Negotiation:</b> A negotiation is an attempt to influence or persuade someone to think or act differently, preferably for your own individual gain.</li>
        </ul>
        <p class="indent">Negotiating for the best price on your heating and air conditioning project is not as intimidating as it seems.  It is not a confrontation, it is a casual conversation.  Below are some recommended principles that will help you use the information provided in the Home HVAC Design and Consultation Program Report to negotiate the best possible deal.
        </p>

        <ol type="a">
            <li>
                <b>Everything is negotiable.</b>  They may bluff, they may walk away…let them.  Everything is negotiable, including the price of your units, and whether or not you pay for those little extra fees.
            </li>
            <li><div class="clearfix">
                <div class="left">
                A positive outcome of a negotiation is best achieved when both sides walk away happy.  This is known as a “win-win” negotiation, and it should be your goal.  A negotiation is a compromise, not a one-way transaction.  The idea that you are going to get everything that you want, and the contractor is going to go home penniless is just not going to happen.  First of all, the contractor negotiates several times a day, and unless you negotiate for your job, you do not.  So, going into this, it is best to be friendly, polite and most importantly, realistic.  Besides, even if you did talk him down to a price that is completely below where he needs it to be, do you really want a disgruntled contractor working on your system?</div>
                <div class="right"> <img src="'.SF_PLUGIN_DIR.'images/win_win.png" class="alignright"/>
                </div>
                </div>
            </li>
            <li>
                <b>You get more flies with honey than you do with vinegar.</b>  Telling the contractor, “this is a fair price, and that’s just how it’s going to be,” is not only contrary to what we designed this program for, but it isn’t going to get you very good results.  Our recommendation would be to act friendly, and treat the contractor with respect and dignity.  I know that everyone hates dealing with contractors, but remember that not all contractors are bad!  I can’t tell you how many times people have yelled at one of our guys (who was a combat Navy SEAL, by the way) because they assumed that he was just another, stupid contractor.  Be nice.  Always…it will help you in the negotiation, I promise.
            </li>
            <li>
                <b>Never show a contractor the Home HVAC Design and Consultation Program Report.</b>  There are a couple of reasons for this: first, it’s proprietary and for your use only as stated in the Terms of Service you agreed to, but second, and more importantly, the contractor would then be empowered to argue with every single one of your results.  For instance, if a fair price for your project is $5,500…if he sees that on the report, then he might agree that it is a fair price.  The problem is that had you not shown him the report, he may have offered $5,000 for your job to be competitive - you will have just shot yourself in the foot.  Keep your cards close to your chest, tell them only what they need to know, and never show the report results to a contractor - it’s for you, not for them.

            </li>
            <li>
                <b>Always tell the truth…<i>slowly</i>.</b>  Although it is important to be friendly, this person is not your friend.  This is a business transaction.  He will likely be friendly to you so he can get a better price, but that doesn’t mean that you have to offer him anything he doesn’t need.  For instance, he might ask you, “what did the other contractors bid this job at?”  This is him fishing for information…if you tell him, “$8,000,” he will come in at $7,800 if he can.  If, however, you politely tell him, “Actually we are taking bids form multiple contractors - we’d appreciate it if you’d give us your best price before we make a decision,” he might be inclined to go lower.
            </li>
            <li>
                <b>Negotiating is not a confrontation.</b>  In fact, some of the best negotiators in the world are friendly, affable, and even enjoyable to negotiate with.  Forget what you see about playing “hard-ball” in the movies.
            </li>
            <li>
                <b>EVERYTHING IS NEGOTIABLE!</b>  “But what about…”  No, that is negotiable too.  Everything is negotiable.  Period.
            </li>
        </ol>
        <h4>b. The Power of Leverage</h4>
        <p class="indent">Let&#180;s talk negotiation - all negotiations are based on leverage.  In its simplest definition, leverage in a negotiation is defined as such:</p>
        <ul>
            <li>
                   <b>The person who is more willing to walk away from the negotiation, has the most leverage.</b>
            </li>
        </ul>
        <p>
            <img src="'.SF_PLUGIN_DIR.'images/leverage.png" class="alignright"/>
            So, you never, ever want to put yourself into a situation where you HAVE to do business with someone.  That&#180;s why the best time to buy an air conditioner or furnace is in the off season.  If you read our article about buying off-season, then you might have realized that what we were doing was showing you how to put yourself in a position where you have the most leverage that you possibly can.  

        Unfortunately, many of the dishonest contractors instinctively know this, so they push you to sign the contract on the spot, before you think about it.  But, if you sit back and think about it, who has the most leverage when you are buying a new air conditioner or furnace?
        </p>
        <h4>Your Leverage:</h4>
        <ol type="1">
            <li>You don&#180;t need a new air conditioner, you&#180;d like one.  The HVAC contractor needs business to stay afloat.

            </li>
            <li>You don&#180;t need to use this contractor, there are others.  He wants you to use him.

            </li>
        </ol>
        <h4>Example</h4>
        <p>Let&#180;s look at an example of how we could apply what we&#180;ve learned to decrease our installation price.  Some of you might have already stumbled across this example in some of our articles, but refresh yourself anyway.  </p>

        <p>In this case, let&#180;s say you don&#180;t need a new thermostat because you have one that you like.  Here&#180;s how the negotiation might go, first from an amateur standpoint, then from a professional negotiator:</p>

        <h5>Amateur Negotiator:</h5>
        <ul>
            <li><b>You</b> - "I don&#180;t need a new thermostat, will that discount my price?"</li>
            <li><b>Contractor</b> - "Well, we included a basic thermostat for free in our bid, so why don&#180;t you just install a new one for free?"</li>
            <li><b>You</b> - ...awkward silence…”OK.”</li>
        </ul>
        <h5>Professional Negotiator:</h5>
        <ul>
            <li>You - "I like the thermostat you included; is it expensive?  How much would it cost to have it installed separately later on?
        </li>
            <li> Contractor - "Yea, they are fantastic.  They typically cost around $250 for us to install them by themselves, but we are including it for free in your bid today!  Sign now!!!"</li>
            <li>You - "I do like it, but I think I&#180;m ok with the thermostat that I have.  Since we don&#180;t need a new thermostat, would you mind deducting $250 from the final price?"</li>
            <li> Contractor - "Uhm...well...uh, (scratch head, avert eyes), yea, I guess…"</li>
        </ul>
        <o>
        Notice, in the first example, you tipped your hand and told them that you didn&#180;t need the new thermostat, so they reacted appropriately for their end-game.  In the second negotiation, however, you asked them for the price of that component before tipping your hand, and gave yourself an advantage in the negotiation, aka leverage.</p>
        <br>
        <h4>c. Handling the Prices</h4>
            <div class="clearfix">
                <div class="left">
                    It is important to understand that not every company is run the same way.  The prices they quote you may very well be their best price, so realize that just because you know what a fair price looks like for you - the consumer - does not mean that they will be able to offer you that price.  <b>That is why it is so important to receive bids from multiple contractors. </b> 

        <b>Start with getting at least three bids from reputable contractors in your area,</b> and see what prices they come in at.  If they are high, then receive two more bids, again, from contractors that have a decent reputation.  Reputations can be discovered through your own meticulous research of online reviews via Google, and other review sites.
                </div>
                <div class="right">
                    <img src="'.SF_PLUGIN_DIR.'images/negotiation.png"/>
                </div>
            </div>
            <p>Before you speak with anybody, it is recommended that you do the following things while receiving bids from contractors.
        </p>
        <ol type="1">
            <li>Try to get bids in the off season for contractors, when they aren’t as busy.  For air conditioners, this is in the spring and fall (sometimes the winter, but remember that they do furnaces then too).  For furnaces, this time is typically in the summer for the colder states up north, or in the spring and fall if you live in a warmer climate.</li>
            <li>Always tell them directly that you are receiving bids from multiple contractors, that you like them, and that you will use them if they give you a competitive price bid.  This will immediately ensure that they are opening with a competitive bid.
                <blockquote>
                Tip: If a contractor asks you, “How many other contractors are bidding this project?” or, “Who else is bidding this project?”, what they are really asking you is: “Am I going to give you my marked-up price, or a competitive price to compete with the other contractors?”

                </blockquote>	
            </li>
            <li>Never, ever, sign on-the-spot.  Always tell them, “I’d like a day or two to think about it.”  If they keep pressuring you to sign after that, then they are high-pressure and I wouldn’t recommend using them as a company.  I’ve never found a high-pressure sales company that doesn’t also have hidden fees and expenses, so if you find one, please let me know.</li>

        </ol>
        <h4 style="color:#9D64DE;">• What to do if a contractor offers you a price that is within the GREEN ZONE (fair price range) on your Home HVAC Design and Consultation Program Report.</h4>
        <p>Ideally, a contractor will offer you a fair price that is within the GREEN ZONE on your Program Report to start with.  If this happens, congrats!  But the deal isn’t over yet:
        </p>
        <ul>
        <li>
        Thank the contractor for their time, tell them that you are taking bids from multiple contractors, and that you will contact him in a few days if you decide to use their company.</li>
        <li>Ask them if the price they submitted is the best they can do - you will be shocked at how often they can knock a few hundred dollars off of the price.</li>
        <li>Call him in a few days, if you decide to move forward, and ask him one last time if this is the best price that they can give, or if they can offer any further discounts, etc.  Regardless of their answer, they have proven to be fair in their pricing.</li>

        </ul>

        <h4 style="color:#9D64DE;">• What to do if NONE of the contractors are within the GREEN ZONE (fair price range) on your Home HVAC Design and Consultation Program Report.</h4>
        <p>If none of the contractors are offering prices within the GREEN ZONE, then you will have to begin to negotiate with them.</p>
        <ul>
            <li>Call the contractor and tell him that you received bids from multiple contractors, and that you liked him.  Tell him you’d like to use him, but that his prices are too high.  Then zone-out in your mind and think about something else why he gives you reason, after reason for his prices being so high.  The point is, it doesn’t matter what he says, and if you are new to negotiation, you may want to consider not even listening.</li>
            <li>When he is done, ask him politely if this is the best that he can do.  <br>
            <blockquote>
                <b>Tip:</b> If he asks you what his competitors have offered, tell him that you don’t feel             comfortable sharing that information (it would only work against you to tell him),             but that you’d like his best possible price because he is high in price.

            </blockquote>
            </li>
            <li>If the new price is in the GREEN ZONE, great - move up to the previous section.  If not, then you will have to make him a counteroffer.  Counteroffer him by telling him that you have a number based on his competitors’ offers, that you think is fair (use the “LOW, but FAIR” price from your Program Report above).  He will likely reject this price outright, in which case you will go to another contractor and try the same thing, or he will offer something more reasonable.
            </li>
            <li>The goal of your negotiation should be to pay no more than the HIGH-FAIR price on the Program Report.  Continue to counteroffer until he reaches this number, or refuses to go lower, and at that point you may just have to make a hard decision.  Remember that there are many different contractors out there, and you empower yourself by getting multiple bids.</li>
            <li>If he still refuses to go any lower, and the price is still in the HIGH range on your Program Report, then make one last offer: tell him that you’d like to use him, but that he’s just too high.  Then offer him the HIGH-FAIR price from the Program Report, and tell him that this is the absolute best that you could do.  Tell him that if he comes in at that price, or changes his mind and wants the work, then you’ll use him.  Also tell him that if he doesn’t, then you’d like to thank him for his time and wish him the best of luck!</li>
            <li>Give him a day or two to think about it, and in the meantime, try your luck with the other contractors.</li>
            <li>In the end, you may not be able to get anyone in your area to come down to the fair price range, although this is usually rare.  This is typically only true if you live in a rural area where there aren’t that many contractors.  If you can’t get them to come down in price, and there aren’t any competitors to go to, then you might just have to bite the bullet and pay a higher price, but at least you will have done everything that you can.</li>
        </ul>
        <div class="clearfix" style="height:20px"></div>        
        <ul>

                <li>

                   <div class="clearfix">
                        <div class="left">
                            <h4>d. Additional Tips for Your Price Negotiation</h4>
                            If the contractor comes down a lot in his price (such as $5,000), this may sound fantastic, but it&#180;s not. Reconsider using them...if they came down that much, then they were overcharging you to begin with - not an honest trait - choose someone else.
                        </div>
                        <div class=""right>
                            <img src="'.SF_PLUGIN_DIR.'images/export.png" class="alignright" width="300"/>
                        </div>
                    </div>
                </li>
                <li>If you can, use a contractor that installs all major makes and models of air conditioners, not just one (i.e. "your local Lennox Dealer," etc...).  People who push one brand typically have lines for everything, and it is in their best interest to press their product on you, not the best product for your home.</li>
                <li>In a negotiation, you can tell when they are getting closer to their bottom-line price when the increments in which they are decreasing their price get significantly smaller (i.e. price reduced by $400, then price reduced by another $400, then only by $50...the negotiation is over - that&#180;s their best price, and pushing further will possibly insult them or jeopardize your progress).</li>
                <li>Consideration should be given towards not signing any maintenance plans with the contractor that installs your unit, as this can be an additional, unneeded expense, unless you are elderly and cannot change the air filters yourself.</li>
                <li>Many contractors have a clause in the contract that requires you to use them in case of a repair on your new unit.  We would recommend that you consider against doing this.  Regardless of what they say, unless the manufacturer specifically states it in their warranty, not utilizing the company’s mandatory repair service should not void a warranty.  Remember that everything is negotiable!  Make them take it off of the contract if you don’t want to do it!  </li>
                <li>Get everything in writing!  Do NOT agree to a “handshake” deal with a contractor.  That is actually why we are referred to as “contractors;” because we write contracts!  Always get it in writing; for your protection, and for theirs.</li>
            </ul>

        <h4>THANK YOU!</h4>

        <p>Thank you for utilizing the ASM Home Design and Consultation Program℠.  Please let us know how we did in an online review or via email at <a href="mailto:Service@ASM-air.com">Service@ASM-air.com</a>.  We may not be able to respond to every email, but assure you that every email will be read for insight and feedback into what we can do better in subsequent versions.  </p>

        <p>Thank you for your business, we hope that this helps you to make an informed decision, and good luck on your project!</p>

        <p style="text-align:center;">With kind regards,<br>
        <b>The ASM Team</b></p>

        <div style="text-align:center;">Copyright © 2017 All Systems Mechanical HVAC, Inc. All Rights Reserved</div>
        </div>

        </body></html>';

        //echo $html;


        //==============================================================
        //==============================================================
        //==============================================================

         
        
       $mpdf=new mPDF(); 
        //$mpdf->showImageErrors = true;


        //$mpdf->SetWatermarkText('ALL SYSTEMS');
        //$mpdf->watermark_font = 'DejaVuSansCondensed';
        //$mpdf->showWatermarkText = true;


        $mpdf->SetHTMLFooter($footer_image_html);

        $mpdf->WriteHTML($html);	// Separate Paragraphs  defined by font


        $upload_dir = wp_upload_dir();
                $user_dirname = $upload_dir['basedir'].'/smartform/';
                if ( ! file_exists( $user_dirname ) ) {
                    wp_mkdir_p( $user_dirname );
                }
                $path = $user_dirname;
                $time = time();






                $filename = $_GET['entry_id'].'_'. $answer_filter['equipment_type'].'_'.$answer_filter['conditioner_type'].'_'.$answer_filter['location_state'].'_'.date('m_d_Y_h_i_s',$time)."_report.pdf";
        $pdf_name = $user_dirname.'/'.$filename;

        $mpdf->Output($filename,'I'); 

        chmod($path.$filename,0777);
                $file_url = $path.$filename;
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private',false);
                header('Content-Length: ' . filesize($file_url));
                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header('Content-Transfer-Encoding: binary');
                header('Connection: close');
                readfile( $file_url );

        } else {
            echo "Some thing wrong from your option";
        }

?>

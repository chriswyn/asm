<?php
if ( isset($_GET['entry_id']) && $_GET['entry_id']!= 0 && $_GET['formId'] != 0 && isset($_GET['formId'])) {
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

$total_price = 0.0;
$model_price = "";
$default_price = "";
$furnace_model_price = 0.0;
//Answer1 Number of Working Days

	$areas_days = array("000-600"=>0,"600-800"=>0,"801-1000"=>0,"1001-1200"=>0,"1201-1400"=>0,"1401-1700"=>0,
	"1701-2100"=>0,"2101-2400"=>2,"2401-2800"=>2,"2801-3200"=>2,"3201-3600"=>2,"3601-4000"=>2);

	$number_of_days = $number_of_days + $areas_days[$answer_filter['area_size']];

	if($answer_filter['new_duct'] == 'Yes') {

		$number_of_days = $number_of_days + 2;
	}

	$number_of_days_number = $number_of_days;	

	$number_of_days = $number_of_days . " Day";
	if($number_of_days > 1) {
		$number_of_days = $number_of_days ."s";
	}

//Answer2
	//$area_exception_states = array('LA','MS','AL','GA','SC','FL');
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
			}
		} else if($ac_zone == 2) { 
			$zone_type = 'ac_zone2';
			$seer_afue = 16;	
		}
	} else if($answer_filter['equipment_type'] == 'Furnace') {
		$furnace_zone = get_field('furnace_zone', $state_id);
		if($answer_filter['above_3000'] == 'Yes') {
			$zone_type = 'furnace_zone2';	
		}
		if($furnace_zone == 1) {
			$zone_type = 'furnace_zone1';
			$seer_afue = 80;	
		} else if($furnace_zone == 2) { 
			$zone_type = 'furnace_zone2';	
			$seer_afue = 96;
		}
		
	}  else if($answer_filter['equipment_type'] == 'Both') {

			$zone_type = 'package_unit';
	}
	$tonnage = get_field($zone_type, $area_id);
	
	if($tonnage == "6.0") {
		$tonnage = "6 ton [ 3 ton + 3 ton]";
	}
	if($tonnage == "7.0") {
		$tonnage = "7 ton [ 3 ton + 4 ton]";
	}
	if($tonnage == "8.0") {
		$tonnage = "8 ton [ 4 ton + 4 ton]";
	}
	if($tonnage == "9.0") {
		$tonnage = "9 ton [ 4 ton + 5 ton]";
	}
	if($tonnage == "10.0") {
		$tonnage = "10 ton [ 5 ton + 5 ton]";
	}





	if($answer_filter['equipment_type'] == 'AC') {

		if($answer_filter['conditioner_type'] == 'packaged') {

			$ac_seer_tonnage  = $tonnage.' ton Packaged Air Conditioner'; 


		} else {

			$ac_seer_tonnage  = $tonnage.' ton,'. $seer_afue. 'SEER Air Conditioner'; 

		}
	} else {
		$ac_seer_tonnage = 'Not requested';
	}
	if($answer_filter['equipment_type'] == 'Furnace') {
		if($answer_filter['conditioner_type'] == 'packaged') {
			$furnace_seer_tonnage = $tonnage.' Packaged Furnace';
		} else {
			$furnace_seer_tonnage = $tonnage.' Furnace';
		}
	} else {

		$furnace_seer_tonnage = 'Not requested';
	}
    
    if($answer_filter['equipment_type'] == 'Both' && $answer_filter['conditioner_type'] == 'split') {
        $ac_seer_tonnage_details = explode("-",$answer_filter['package_split_ac_model']);
        $ac_seer_tonnage_model =  get_posts(array('name'=>sanitize_title($ac_seer_tonnage_details[0]),'post_type' => 'acequipment'));
        
        $ac_model_id = $ac_seer_tonnage_model[0]->ID;
        
        $seer = get_post_meta($ac_model_id,'seer',true);
                
        $ac_seer_tonnage = $seer.' SEER  '.str_replace('AC Size : ','',$ac_seer_tonnage_details[1]).' Air Conditioner';
        $furnace_seer_tonnage_details = explode("-",$answer_filter['package_split_furnace_model']);
                $furnace_seer_tonnage_model =  get_posts(array('name'=>sanitize_title($furnace_seer_tonnage_details[0]),'post_type' => 'funrnace'));
        
       $furnace_model_id = $furnace_seer_tonnage_model[0]->ID;
        
        $afue = get_post_meta($furnace_seer_tonnage_model[0]->ID,'afue',true);
        $furnace_seer_tonnage = str_replace('Furnace Size : ','',$furnace_seer_tonnage_details[1]).' BTU, '.$afue.' AFUE Furnace';
                                           //.', '.$furnace_seer_tonnage_model[0]->post_content);
       
    }


	if($answer_filter['customer_interest'] == 'Yes') {

		$model_chosen = $answer_filter['topbrand'];

		if($answer_filter['equipment_type'] == 'AC') {

			$model_details = explode("-",$answer_filter['ac_model']);
			$model_chosen = $model_chosen." Model#:". $answer_filter['ac_model'];
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
            
            if($answer_filter['conditioner_type'] == 'split' || $answer_filter['conditioner_type'] == 'dontknow' ) {
                
                $model_details = explode("-",$answer_filter['package_split_ac_model']);
                $model_price = 	str_replace('Price : $','',$model_details[count($model_details)-1]);
                $model_chosen = $model_chosen."-".$model_details[0];
                $model_price = str_replace(',', '.', $model_price);
                $model_price = preg_replace("/[^0-9\.]/", "", $model_price);
                $model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
                $model_price = (float) $model_price;
                
                
                $furnace_model_details = explode("-",$answer_filter['package_split_furnace_model']);
                $furnace_model_price = 	str_replace('Price : $','',$furnace_model_details[count($furnace_model_details)-1]);
                $furnace_model_chosen = $answer_filter['topbrand']."-".$furnace_model_details[0];
                $furnace_model_price = str_replace(',', '.', $furnace_model_price);
                $furnace_model_price = preg_replace("/[^0-9\.]/", "", $furnace_model_price);
                $furnace_model_price = str_replace('.', '',substr($furnace_model_price, 0, -3)) . substr($furnace_model_price, -3);
                $furnace_model_price = (float) $furnace_model_price;
                $model_price = $model_price + $furnace_model_price;
            
            
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
		$model_details = explode("-",$answer_filter['default_model']);
		$default_price = str_replace('Price : $','',$model_details[count($model_details)-1]);
		$default_model_chosen = $answer_filter['default_model'] ;
		$default_price_temp = str_replace("$",'',$default_price);
		$model_price = str_replace(',', '.', $default_price_temp);
		$model_price = preg_replace("/[^0-9\.]/", "", $model_price);
		$model_price = str_replace('.', '',substr($model_price, 0, -3)) . substr($model_price, -3);
		$model_price = (float) $model_price;
		$total_price = $total_price + $model_price ;
	}
		$upload_dir = wp_upload_dir();
		$user_dirname = $upload_dir['basedir'].'/smartform/';
		if ( ! file_exists( $user_dirname ) ) {
			wp_mkdir_p( $user_dirname );
		}
		$path = $user_dirname;
		$filename = rand(0,1000)."_report.pdf";
    
    /*
    <h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>If an oil burning is desired, these will typically be of comparable price to the gas furnaces</sub><pLeft10>---</pLeft10></pLeft10>
	<pLeft10>---<sub>listed above.</sub></pLeft10>
	</h6>
    
    <h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>For contractors,the price of purchasing equipment actually changes, and is based on</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>----<sub>several factors, including the volume of units sold in a year, and whether or not you are a</sub></pLeft10>
	<pLeft10>----<sub>" Licensed Distributor " for that particular brand. As such, the pricing information provided</sub></pLeft10>
	<pLeft10>----<sub>to you is an average, constructed using the prices paid by contractors around the country</sub></pLeft10>
	<pLeft10>----<sub>(including ourselves), and is calculated using a "low multiplier." What this means is that</sub><pLeft10>-----</pLeft10></pLeft10><pLeft10>----<sub>some contractors will be able to get this equipment for even less than this, and some</sub><pLeft10>-----</pLeft10></pLeft10><pLeft10>----<sub>will pay slightly more. The difference in price, however, can be thought of as only a few</sub><pLeft10>--</pLeft10></pLeft10><pLeft10>----<sub>hundred dollars (i.e. +/- $200). In other words, it is possible that a contractor might pay</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>---<sub>$150 less thanthe price provided above, but highly unlikely that they paid $150 more</sub><pLeft10>------</pLeft10></pLeft10><pLeft10>----<sub>This is important for calculating a fair price for your new air conditioner.</sub><pLeft10>-----</pLeft10></pLeft10>
	</h6>
	<h6><input type="button" value="Generate PDF"></input></h6>
	
    */

	$html =  '<h1></h1>
    <h6>The purpose of this program is simple - to empower you with the basic, insider-information required to make a well-informed, educated decision on your heating and air conditioning project.</h6>  
<h1></h1>
<h6>
	Unfortunately, the HVAC industry can be ripe with dishonesty and mistrust - it is our hope that we can change this.  To do that, our goal is to empower you - the consumer - with the same information that we have as professional contractors, leveling the playing field, and hopefully building a more trustworthy, enjoyable industry for both sides.
</h6>    
<h6>
	This information will be provided in two parts: first, the consultation report itself, which includes important information that pertains to your specific heating and air conditioning project, and second, we have provided you with a brief explanation of the report\'s components, what this information means to you and your project, and most importantly, tips on how to use this information to negotiate a fair price, and make a more informed decision on your HVAC project.
</h6> 
    
	<h1></h1>
	<sub><h3 align="center"><u>PART I - The Report</u></h3></sub>
	<h6>Based on your geographical area, the size of your home, and the answers to the questions you
	provided, we made the following observations and recommendations:</h6>

	<h4>1. Project Completion Time</h4>

	<h6><sub>Based on the information you provided, it is our estimate that your project should take around</sub> <span>'.$number_of_days .' </span> <sub>for your project to be completed. This information will be important later, as it will help us calculate the estimated labor costs for your installation.</sub></h6>
	<h4>2. Estimated System Size, SEER Rating, and AFUE Rating</h4>
	<h6> Based on the information that you provided, we estimate that a heating and air conditioning <sub>  </sub> system that is of the following size and efficiency rating would work best for your project:</h6>
	<h6>
	<sub>Air Conditioner (size in tonnage and recommended SEER rating):</sub> <span>'.$ac_seer_tonnage.'</span>
	</h6>
	<h6>
	<sub>Furnace (minimum BTU and recommended AFUE rating):</sub> <span>'.$furnace_seer_tonnage.'</span>
	</h6>
    <h1></h1>
    <h6>
	Important notes based on your selections:
	</h6>

	<h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>Although the reason why this is so will be discussed in more detail later, it is important to  </sub></pLeft10>
	<pLeft10>----<sub>properly size your air conditioner or furnace, both for energy usage and proper cooling </sub></pLeft10>
    <pLeft10>----<sub>and heating.</sub></pLeft10>
	</h6>


	<h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>Based on the answers you provided, as well as our experience and observations, if an air</sub></pLeft10>
	<pLeft10>----<sub>conditioner is installed, it may not be worth the additional money to purchase an air condi-</sub></pLeft10>
	<pLeft10>----<sub>tioner higher than a value of 16 SEER in your area.  Purchasing a high-SEER unit may not</sub></pLeft10>	
	<pLeft10>---<sub>provide an adequate return on your investment in the long-term.</sub></pLeft10>
	</h6>
	<h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>Any furnace recommendations are based on a gas furnace. Tip: If natural gas is not</sub><pLeft10>------</pLeft10></pLeft10>
	<pLeft10>----<sub>available in your area,you can have your HVAC contractor install a "propane conversion</sub></pLeft10>
	<pLeft10>----<sub>kit" to modify a gas furnace for use with propane. This kit typically costs around $45 - $55,</sub></pLeft10>
	<pLeft10>----<sub>and takes minutes to install. It will include a new expansion valve that is made for use with</sub></pLeft10>
	<pLeft10>---<sub>propane molecule.</sub></pLeft10>
	</h6>
	<h4>3. Wholesale Price for Your Requested, Particular Brand and Model of Equipment:</h4>

	<h6>Per your request, here is the current wholesale pricing for the equipment you requested (this is what the contractor pays)
	</h6>
	<h6>Price information for selected model.</h6>';
    
    if($answer_filter['customer_interest'] == 'Yes') {
        
        if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' || $answer_filter['conditioner_type'] == 'dontknow') ) {
            
            $html = $html.'	<h6><span>'.$model_chosen.'  '.$ac_seer_tonnage.'</span></h6>';
            
            $html = $html.'	<h6><span>'.$furnace_model_chosen.'  '.$furnace_seer_tonnage.'</span></h6>';
            
            
            
        } else {
            
            if($model_price!= "") {
                //$answer_filter['default_model']
                $html = $html.'	<h6><span>'.$model_chosen.'</span></h6>';
            } else {
                $html = $html.'	<h6><span>'.$model_chosen.'</span></h6>';
            }
        
        
        
        
        }
                
        
        
    
    } else {
        
        if($model_price!= "") {
			//$answer_filter['default_model']
			$html = $html.'	<h6><span>'.$model_chosen.'</span></h6>';
		} else {
			$html = $html.'	<h6><span>'.$model_chosen.'</span></h6>';
		}
    
    }
		
	$html = $html. '
	<h4>4. ASM'."'".'s Recommendation on Equipment for Your Home:</h4>
	<h6>Based on our experience, as well as the information you have provided, we would<pLeft10>----</pLeft10>
	<sub>recommend the following equipment for your project:</sub>
	</h6>
	<h4><span>'.$default_model_chosen.'    '.$default_price.'</span><pLeft10>--------</pLeft10><sub><span></sub></span></h4>
	<h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>We have recommended Day & Night equipment for your project. This is based on our</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>----<sub>experience installing all major brands. We are in no way beholden to Day & Night, but base</sub></pLeft10>
	<pLeft10>---<sub>this assessment simply on our own recommendation. In other words, we were not paid to</sub></pLeft10>
	<pLeft10>----<sub>say this or promote this product in any way. Next year, based on our experience, we might</sub></pLeft10>
	<pLeft10>---<sub>recommend a completely different company.</sub></pLeft10>
	</h6>

	<h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>If Day & Night is not available in your area, then Goodman or Daiken are also reliable and</sub><pLeft10>---</pLeft10></pLeft10>
	<pLeft10><sub>reasonably priced brands that you can use.</sub></pLeft10>
	</h6>
	<h6>  <img src="'.$user_dirname.'images/download.png" height="5" width="5"><pLeft10>--<sub>Day & Night is made by United Technologies, which also makes Carrier,Bryant, Payne.</sub><pLeft10>-----</pLeft10></pLeft10>
	<pLeft10>---<sub>However, Day & Night is more reasonably priced than Carrier, despite featuring most of</sub></pLeft10>
	<pLeft10>----<sub>the same internal components, including Aspen coils. In our experience, they are,</sub><pLeft10>--------</pLeft10></pLeft10>
	<pLeft10>----<sub>rugged, reliable, and their customer service is top notch(for later down the line).</sub></pLeft10>
	</h6>
	<h4>5. Average Labor Cost in Your Area.</h5>
	<h6>Based on the location you have selected, the average price of labor for an experienced HVAC Technician in your area is estimated at:</h6><h4><span>Average hourly rate for a HVAC Technician in '.get_the_title($state_id).': $'. get_field('hvac_wage_for_1_men_team', $state_id).' / hr.</span></h5>
	<h4><span>Average cost for a 2-man HVAC team, per day: $'.get_field('for_1_day_job', $state_id).' / hr.</span></h5>
	<h4>6. Total Cost to a Contractor for the Completion of Your Project<pLeft10>------</pLeft10><br><br><br><br><sub> (i.e. what the contractor
	is paying to complete your job):</sub></h5>
	<h6>In this section, we will take the information previously discussed, add supplemental information based on some of the answers you provided, and incorporate it into an equation that will allow us to come up with an estimated cost of installation for your heating and air conditioner.  It will factor in the price of equipment, estimated labor costs, additional materials, as well as other factors, such as general liability and workman\'s compensation insurance.  In short, this section will be an estimate of what the contractor is actually paying to complete your project.  Let\'s get started:

	</h6>';
	$html = $html. '<h6>a.<pLeft10>--<sub>Price of the equipment you have selected:	</sub><pLeft10>-----</pLeft10></pLeft10></h6>';
    
    if($answer_filter['customer_interest'] == 'Yes') {
        
        if($answer_filter['equipment_type'] == 'Both' && ($answer_filter['conditioner_type'] == 'split' || $answer_filter['conditioner_type'] == 'dontknow') ) {

            
            $html = $html.'	<h6><span>'.$model_chosen.'  '.$ac_seer_tonnage.'          $'.number_format($model_price,2).'</span></h6>';
            
            $html = $html.'	<h6><span>'.$furnace_model_chosen.'  '.$furnace_seer_tonnage.'         $'.number_format($furnace_model_price,2).'</span></h6>';
          
            
            
            
        } else {
            
            if($model_price!= "") {
                //$answer_filter['default_model']
                $html = $html.'	<h6><span>'.$model_chosen.'</span></h6>';
            } else {
                $html = $html.'	<h6><span>'.$model_chosen.'</span></h6>';
            }
        
        
        
        
        }
                
        
        
    
    } if($answer_filter['customer_interest'] == 'No') {
		if($default_price!= "") {
			$html = $html.'	<h4><span>'.$default_model_chosen.':    '.$default_price.'</span></h4>';
		} else {
		}
	}
	
$total_price = $total_price+(get_field('for_1_day_job', $state_id) * $number_of_days_number);
if($answer_filter['new_duct'] == 'Yes' ) {
	$duct_cost = get_field('duct_work_price', $area_id);	
	$total_price = $total_price+$duct_cost;

} else {

	$duct_cost = 0.0;
}
$total_price = $total_price+912;
$total_price = $total_price+681;
$total_price = $total_price+$furnace_model_price;    



	$html = $html. '<h6>b.<pLeft10>--<sub>Estimated price of labor for your project:</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Total estimated labor cost for your project:       		        $'.get_field('for_1_day_job', $state_id) * $number_of_days_number.'</span></h6>
	<h6>c.<pLeft10>--<sub>Estimated Price of ductwork, based on square footage and region (including additional labor and
	material requirements</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Ductwork Cost:                                		               		         $'.$duct_cost.'</span></h6>
	<h6>d.<pLeft10>--<sub>Estimated miscellaneous materials, gas, and other expenses</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Cost of the  work:                                		               		     $912.00</span></h6>
	<h6>e.<pLeft10>--<sub>Estimated General Liability Insurance, Workman\'s Compensation Insurance, overhead, and
	other miscellaneous administrative fees, etc.:</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Cost of the  work:                           		                           $681.00</span></h6>
	<h6>f.<pLeft10>--<sub>Estimated total cost to contractor for your project:</sub><pLeft10>-----</pLeft10></pLeft10></h6>
	<h6><span>- Total cost to contractor:                           		                  $'.number_format($total_price,2).'</span></h6>
	<h1></h1>
	<h1></h1>
	<h4><sub>7. Fair Price Estimation for Your Heating and Air Conditioning Project:</sub></h4>
	<h6>In this section, we will evaluate fair price ranges for your complete HVAC project, and the profit a contractor will be making off of you for such a project.  Here is where you are going to have your mind blown, but don\'t worry - I\'ll explain exactly why it costs so much right after.  If you have blood pressure medication, please take it before reading further.... 
	</h6>
<p>
<h1></h1>
<h1></h1>
<LEFTIMAGE>
		
		<img src="'.$user_dirname.'images/chart.png" height="147" width="325">
<h1></h1>
<h1></h1>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.10),2).'</sub></h6>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.20),2).'</sub></h6>
<h7></h7>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.30),2).'</sub></h6>
<h7></h7>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.40),2).'</sub></h6>
<h7></h7>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.50),2).'</sub></h6>
<h7></h7>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.60),2).'</sub></h6>
<h7></h7>
 <h1><pLeft10>                          </pLeft10><sub1>$'.number_format($total_price+($total_price*0.70),2).'</sub></h6>
<h1></h1>
<h1></h1>
<h1></h1>
</LEFTIMAGE>	
</p>
	<h2 align="center">Description of Fair Price Ranges</h2>
    
    <pLeft10>  dsfdfdsfdsfdsfdsfdsfdsfds   </pLeft10><sub1><img src="'.$user_dirname.'images/energy_16.png" width="200" ALIGN="RIGHT" height="150">
<TABLE>    
<TR>    
<TD WIDTH="150" HEIGHT="200"><STRONG>TOO LOW:</STRONG></TD><TD></TD><TD  HEIGHT="200" WIDTH="200" >
Contractors who bid in this price range <br>should be taken with a decent dose of caution.Although you are looking for the best price,to use a cliche, you get what you pay for.Why are they able to bid so low? What are they skimping on\.\.\. materials? Labor?  Is it possible that they are just extremely efficient?  Yes, it\'s possible\.\.\.but a betting man would use caution.   </TD><TD>dfdfd</TD></TR>
</TABLE>

<h1></h1><h1></h1>
<h1></h1><h1></h1>
<h1></h1><h1></h1>
<h1></h1><h1></h1>
<h1></h1><h1></h1>
<h1></h1><h1></h1>
<h1></h1><h1></h1>
<TR ><TD  HEIGHT="250"><STRONG>LOW:</STRONG></TD><TD>   </TD><TD>Contractors who bid in this range can be taken a bit more seriously, but caution should still be used.  We\'d recommend that you ensure that they have proper workman\'s compensation insurance and other requirements before signing.</TD><h1></h1><h1></h1><hr></hr>
<TD><STRONG>LOW but FAIR:</STRONG></TD><TD>   </TD><TD>These contractors might just be efficient at what they do, or they may be a small, one-man company.  Contractors in this range should be taken more seriously.</TD><h1></h1><h1></h1><hr></hr>
<TD><STRONG>FAIR PRICE: </STRONG></TD><TD>   </TD><TD>This is your target, and is a fair price for your project.  Many legitimate contractors will fall into this price range, but still ensure that you perform your due diligence in researching any contractor. </TD>


<TD><STRONG>HIGH-FAIR:</STRONG></TD><TD>   </TD><TD>This is the price range where many of the high-quality contractors with solid workmanship and an established reputation will typically fall.  Better (more expensive) employees and a large pool of prospective clients will cause them to charge a little bit more.  If you are extremely anxious about the work to be done, and want to ensure that it is done right, it may be worth examining contractors in this price range.  You\'ll pay a little bit extra, but it should be done right.
</TD><h1></h1><hr></hr>
<TD><STRONG>SLIGHTLY HIGH:</STRONG></TD><TD>   </TD><TD>These contractors are a bit pricy, but are still within the range of reasonable.  If you like a contractor, and this is their price range, then give them some serious consideration\.\.\.after you negotiate with them.</TD><h1></h1><h1></h1><hr></hr>
<TD><STRONG>TOO HIGH:</STRONG></TD><TD>   </TD><TD>Typically, the only time you would consider this price range is if you can\'t find anyone else who will do it.  We\'d recommend shopping around if a contractor falls within this price range or higher.  </TD>
</TABLE><h1></h1><h1></h1>
<h2 align="center">Further Discussion</h2>
<h6>    In order to understand how these prices were calculated, it is important to understand how the contracting industry works - specifically the heating and air conditioning industry.  Remember, the profit margins above are profit to the company, not the guy who owns it\.\.\.there are still other expenses that have to be accounted for, such as taxes, administrative employees, advertising, etc.  That is why the profit margin is so high - these expenses will still have to come out of the profit margins listed above, and are part of running a legitimate business (these prices are estimates for legitimate, licensed contractors) </h6>
    <h6>The expenses that the contractor has to pay out of his profit margin, are variables that depend on the corporate culture of each individual business, and cannot be accounted for in a calculator, nor should they be.  It may sound cold, but it is not your problem, it is their problem.</h6>
    <h6>Therefore, to keep your results as accurate as possible, we are giving you the total profit margin for your job.  For instance, a really efficient company might only spend 10% of that profit on other company expenses (admin, etc.) and pocket the rest - good for them.  A really inefficient company might live in poverty even at a 60% profit margin.  Again, that isn\'t your problem, and I\'d urge you to stick with the estimated price guidelines above.  All I want you to do is realize that just because a company\'s prices are high, does NOT mean that they are necessarily dishonest or trying to scam you (although they certainly could be).  They may still be learning how to run their business efficiently. </h6>
<h1></h1>
<h6><STRONG>At this point, it is recommended that you take a 15 minute break, and allow your brain to process the some of the information provided above.</STRONG></h6>
<h1></h1>
<h1></h1>
<h1></h1>
<sub><h3 align="center"><u>PART II - The Explanation</u></h3></sub>
<h6>
	In this section, we will break down the information provided in the Home HVAC Design and Consultation Program Report.  Although many of you are familiar with ASM through reading our online articles, it is still important that everyone reading this has a basic understanding of some of the key terms concepts used in the industry, as well as how some of these features will affect the final price that you pay.  This section will allow you to gain more from the information reported above.
</h6>
<h1></h1>
<h2><sub>1.Important Terms and Concepts</sub></h2><h4>SEER Rating</<h4>
<h1></h1>
<h6>SEER stands for Seasonal Energy Efficiency Ratio and is the most common way to evaluate an air conditioner\'s efficiency.  It is important to understand that this is only a measure of cooling power, and applies to air conditioning only.  Simply put, an air conditioner\'s SEER rating is the ratio of the cooling output of an AC unit over a typical cooling season (measured in British Thermal Units, or "BTUs"), divided by the energy consumed in Watt-Hours.  Generally, the higher a unit\'s SEER value, the more efficient it is. </h6>
<h1></h1>

    
    
    
    
    
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
	<pLeft10>-----<sub>heating and air conditioning businesses are fantastic technicians, but are shooting from</sub></pLeft10>
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

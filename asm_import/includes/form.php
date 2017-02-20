<?php 
	function asm_forms() {

		global $wpdb;
		$link 	= get_bloginfo('url')."/wp-admin/admin.php?page=asm_settings";
	
		if(isset($_REQUEST['action'])) {
			$task  = $_REQUEST['action'];
		}

		global $wpdb;
		$status			= 'no';
		$count			= 0;
		$row			= 0;

		//submit a file to upload the entries
		if (isset($_POST['submit'])) {
			$status			= 'no';
			$count			= 0;
			$row			= 0;

			if (is_uploaded_file($_FILES['defaultcsv']['tmp_name'])) {
				$handle = fopen($_FILES['defaultcsv']['tmp_name'], "r");
		  		while (($data = fgetcsv($handle, 1000, "\t",'"')) !== FALSE) {
						$row++;
						if ($row != 1 && $data[11]=='acaf') {
							if(count($data) > 0) {
								$post_title	= $data[9].' '.$data[3].' Seer '.$data[2];
								if($data[1] == 'ac_zone1') {
									$post_title = $post_title.' Zone 1';
								} else {
									$post_title = $post_title.' Zone 2';
								}
								$post_title = $post_title.' '.$data[0];
								$query = $wpdb->prepare(
									'SELECT ID FROM ' . $wpdb->posts . '
									WHERE post_title = %s
									AND post_type = \'acaf\'',
									$post_title
								);
								$wpdb->query( $query );
								$acaf_id = 0;
								if ( $wpdb->num_rows ) {
									 $acaf_id = $wpdb->get_var( $query );
								} else {
									$data_in            = array(
										'post_title'    => sanitize_text_field($post_title),
										'post_status'   => 'publish',
										'post_author'   => 1,
										'comment_status' => 'closed',
										'ping_status' => 'closed',
										'post_type' 	=> 'acaf'
									);	
									$acaf_id = wp_insert_post( $data_in );									
								}
								if(intval($acaf_id) != 0) {
									$append = true ;
									$default_record = array(
									  'ID'           => $acaf_id,
									  'post_title'   => sanitize_text_field($post_title),
									  'post_content' => sanitize_text_field($data[10]),
									);
									// Update the post into the database
									wp_update_post( $default_record );
									//area
									$queried_area = get_page_by_path($data[0],OBJECT,'area');
									update_field( 'area',$queried_area->ID, $acaf_id );
									//zone_type
									update_field( 'zone_type', sanitize_text_field($data[1]), $acaf_id );
									//type spit or packaged
									update_field( 'type', sanitize_text_field($data[2]), $acaf_id );
									//seer
									update_field( 'seer_afue', sanitize_text_field($data[3]), $acaf_id );
									//Model
									update_field( 'model', sanitize_text_field($data[4]), $acaf_id );
									//Model
									update_field( 'size', sanitize_text_field($data[5]), $acaf_id );
									//Price
									update_field( 'price', sanitize_text_field(number_format($data[6],2)), $acaf_id );
									//matching_coil_price
									update_field( 'matching_coil_price', sanitize_text_field(number_format($data[7],2)), $acaf_id );
									//complete Equipment Price
									update_field( 'complete_equipment_cost', sanitize_text_field(number_format($data[8],2)), $acaf_id );
								}
							} // if data row has some values 
							$count++;
						}//Skip first row 
					} //for rows 
				$status = "yes";
				fclose($handle);
			} // For File uploaded to Default Entries

############################# For AC Equipment start  #####################################################
			if (is_uploaded_file($_FILES['accsv']['tmp_name'])) {
				$handle = fopen($_FILES['accsv']['tmp_name'], "r");
				$subrow = -1;
				$previous_title = "";
				$repeater_rows = array();
		  		while (($data = fgetcsv($handle, 1000, "\t",'"')) !== FALSE) {
					$row++;
					if ($row != 1 && $data[10]=='acequipment' ) {
						if(count($data) > 0) {

							if($previous_title != $data['8']) {
								$subrow = -1;
								$previous_title = $data['8'];	
							}
							$post_title	= sanitize_text_field($data['8']);
							$query = $wpdb->prepare(
								'SELECT ID FROM ' . $wpdb->posts . '
								WHERE post_title = %s
								AND post_type = \'acequipment\'',
								$post_title
							);
							$wpdb->query( $query );
							$ac_id = 0;
							if ( $wpdb->num_rows ) {
								$ac_id = $wpdb->get_var( $query );
							} else {
								$data_in            = array(
									'post_title'    => sanitize_text_field($post_title),
									'post_status'   => 'publish',
									'post_author'   => 1,
									'comment_status' => 'closed',
									'ping_status' => 'closed',
									'post_type' 	=> 'acequipment'
								);	
								$ac_id = wp_insert_post( $data_in );
								$subrow = -1;			
							}
							if(intval($ac_id) != 0) {
								++$subrow;
								$ac_record = array(
								  'ID'           => $ac_id,
								  'post_title'   => sanitize_text_field($post_title),
								  'post_content' => sanitize_text_field($data[9]),
								);
								// Update the post into the database
								wp_update_post( $ac_record );
								update_field( 'Manufacturer',$data[1], $ac_id );
								update_field( 'seer',$data[3], $ac_id );
								$row = array('type'=>$data[2],'model'=> $data[0],'size'=> $data[4],'price'=> $data[5],'matching_coil_price'=> $data[6],'complete_ac_equipment_cost_' => $data[7]);
								update_post_meta( $ac_id,"ac_equipment_".$subrow."_type",$data[2]);
								update_post_meta( $ac_id,"ac_equipment_".$subrow."_model", $data[0]);
								update_post_meta( $ac_id,"ac_equipment_".$subrow."_size",$data[4]);
								update_post_meta( $ac_id,"ac_equipment_".$subrow."_price",number_format($data[5],2));
								update_post_meta( $ac_id,"ac_equipment_".$subrow."_matching_coil_price",number_format($data[6],2));
								update_post_meta( $ac_id,"ac_equipment_".$subrow."_complete_ac_equipment_cost_",number_format($data[7],2));
								update_post_meta( $ac_id,"ac_equipment",$subrow+1);
							}
						}
						$count++;
					}
				}
				$status = "yes";
				fclose($handle);	
			}
			############################# For Furnace Equipment Start  #####################################################
			if (is_uploaded_file($_FILES['furnacecsv']['tmp_name'])) {
				$handle = fopen($_FILES['furnacecsv']['tmp_name'], "r");
				$subrow = -1;
				$previous_title = "";
				$repeater_rows = array();
		  		while (($data = fgetcsv($handle, 1000, "\t",'"')) !== FALSE) {
					$row++;
					if ($row != 1 && $data[8]=='funrnace' ) {
						if(count($data) > 0) {

							if($previous_title != $data['6']) {
								$subrow = -1;
								$previous_title = $data['6'];	
							}
							$post_title	= sanitize_text_field($data['6']);
							$query = $wpdb->prepare(
								'SELECT ID FROM ' . $wpdb->posts . '
								WHERE post_title = %s
								AND post_type = \'funrnace\'',
								$post_title
							);
							$wpdb->query( $query );
							$furnace_id = 0;
							if ( $wpdb->num_rows ) {
								$furnace_id = $wpdb->get_var( $query );
							} else {
								$data_in            = array(
									'post_title'    => sanitize_text_field($post_title),
									'post_status'   => 'publish',
									'post_author'   => 1,
									'comment_status' => 'closed',
									'ping_status' => 'closed',
									'post_type' 	=> 'funrnace'
								);	
								$furnace_id = wp_insert_post( $data_in );
								$subrow = -1;			
							}
							if(intval($furnace_id) != 0) {
								++$subrow;
								$furnace_record = array(
								  'ID'           => $furnace_id,
								  'post_title'   => sanitize_text_field($post_title),
								  'post_content' => sanitize_text_field($data[7]),
								);
								// Update the post into the database
								wp_update_post( $furnace_record );
								update_field( 'manufacturer',$data[1], $furnace_id );
								update_field( 'afue',$data[3], $furnace_id );
								update_post_meta( $furnace_id,"furnace_equipment_models_".$subrow."_type",$data[2]);
								update_post_meta( $furnace_id,"furnace_equipment_models_".$subrow."_model_#", $data[0]);
								update_post_meta( $furnace_id,"furnace_equipment_models_".$subrow."_size_(btus)",$data[4]);
								update_post_meta( $furnace_id,"furnace_equipment_models_".$subrow."_price",number_format($data[5],2));
								update_post_meta( $furnace_id,"furnace_equipment_models",$subrow+1);
							}
						}
						$count++;
					}
				}
				$status = "yes";
				fclose($handle);
			}
			############################# For Packaged Equipment Start  #####################################################
			if (is_uploaded_file($_FILES['packagedcsv']['tmp_name'])) {
				$handle = fopen($_FILES['packagedcsv']['tmp_name'], "r");
				$subrow = -1;
				$previous_title = "";
				$repeater_rows = array();
		  		while (($data = fgetcsv($handle, 1000, "\t",'"')) !== FALSE) {
					$row++;
					if ($row != 1 && $data[9] == 'packaged') {
						if(count($data) > 0) {

							if($previous_title != $data['7']) {
								$subrow = -1;
								$previous_title = $data['7'];	
							}
							$post_title	= sanitize_text_field($data['7']);
							$query = $wpdb->prepare(
								'SELECT ID FROM ' . $wpdb->posts . '
								WHERE post_title = %s
								AND post_type = \'packaged\'',
								$post_title
							);
							$wpdb->query( $query );
							$package_id = 0;
							if ( $wpdb->num_rows ) {
								$package_id = $wpdb->get_var( $query );
							} else {
								$data_in            = array(
									'post_title'    => sanitize_text_field($post_title),
									'post_status'   => 'publish',
									'post_author'   => 1,
									'comment_status' => 'closed',
									'ping_status' => 'closed',
									'post_type' 	=> 'packaged'
								);	
								$package_id = wp_insert_post( $data_in );
								$subrow = -1;			
							}
							if(intval($package_id) != 0) {
								++$subrow;
								$package_record = array(
								  'ID'           => $package_id,
								  'post_title'   => sanitize_text_field($post_title),
								  'post_content' => sanitize_text_field($data[8]),
								);
								// Update the post into the database
								wp_update_post( $package_record );
								update_field( 'manufacturer',$data[1], $package_id);
								update_field( 'seer',$data[3], $package_id );
								update_post_meta( $package_id,"packaged_models_".$subrow."_type",$data[2]);
								update_post_meta( $package_id,"packaged_models_".$subrow."_model", $data[0]);
								update_post_meta( $package_id,"packaged_models_".$subrow."_ac_size_(tons)",$data[4]);
								update_post_meta( $package_id,"packaged_models_".$subrow."_heat_size_(btus)",$data[5]);			
								update_post_meta( $package_id,"packaged_models_".$subrow."_price",number_format($data[6],2));
								update_post_meta( $package_id,"packaged_models",$subrow+1);
							}
						}
						$count++;
					}
				}
				$status = "yes";
				fclose($handle);
			}

	
			
		} // SUbmit
?>
<div class="wrap nosubsub">
<div class="add_new"><div id="icon-plugins" class="icon32"><br></div><h2>ASM Import CSV</h2></div>
	<br>
	<?php if($status == 'yes') { ?>
		<div class="updated fade"><p><strong><?php echo $count.' Record(s) imported.'; ?></strong></p></div>
	<?php } ?>
	<br>
		<h3>For Default AC and Furnace Equipment</h3>
	<br>
	<form method="post" action="<?php echo $link; ?>" enctype="multipart/form-data">
		<input size="50" type="file" name="defaultcsv" require>
		<input type="submit" class="button-primary" name="submit" value="<?php _e( 'Import'); ?>" />
		<p>Select csv file and click import records for the Default Equipment .</p>
	</form>

	<br>
		<h3>For AC Equipment Only </h3>
	<br>
	<form method="post" action="<?php echo $link; ?>" enctype="multipart/form-data">
		<input size="50" type="file" name="accsv" require>
		<input type="submit" class="button-primary" name="submit" value="<?php _e( 'Import'); ?>" />
		<p>Select csv file and click import records for the AC Equipment .</p>
	</form>
	<br>
		<h3>For FURNACE Equipment Only </h3>
	<br>
	<form method="post" action="<?php echo $link; ?>" enctype="multipart/form-data">
		<input size="50" type="file" name="furnacecsv" require>
		<input type="submit" class="button-primary" name="submit" value="<?php _e( 'Import'); ?>" />
		<p>Select csv file and click import records for the Furnace Equipment .</p>
	</form>
	<br>
		<h3>For Packaged Equipment Only </h3>
	<br>
	<form method="post" action="<?php echo $link; ?>" enctype="multipart/form-data">
		<input size="50" type="file" name="packagedcsv" require>
		<input type="submit" class="button-primary" name="submit" value="<?php _e( 'Import'); ?>" />
		<p>Select csv file and click import records for the Packaged Equipment .</p>
	</form>	

</div>
<?php		
	}
?>

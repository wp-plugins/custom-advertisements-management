<?php
/*
Plugin Name: Custom Advertisements Management
Description: This plugin is used to easily add custom advertisements to websites.
Author: Arsh Sharma
Version: 1.0
Author URI: http://fitnessnit.com/
*/

/**
* Callback funciton to add ad link in menu
**/
function cam_advertisementlink() {
	add_menu_page(__('Custom Advertisements Management Plugin'), __('Custom Ad Management'), 'publish_posts', 'cam_advertisements', 'cam_advertisements', '', 78.6); 
}

/**
* Callback funciton to add ad advertisements
**/
function cam_advertisements() { 
	
	wp_enqueue_style('thickbox');
	wp_enqueue_script('jquery');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('media-upload');
	wp_register_script("custom-advert-js", plugins_url('js/advertisements-functions.js', __FILE__));
	wp_enqueue_script("custom-advert-js"); 

	wp_register_style("custom-advert-css", plugins_url('css/advertisemnets-style.css', __FILE__));
	wp_enqueue_style( "custom-advert-css"); 
	
	if (isset($_POST['submitadvertisement'])) {
		// Save advertisements
		foreach($_POST['advertisementimage'] as $key => $val){
			$post_id = intval($val);
			if($post_id){
				update_post_meta($post_id, "ad_image", true);
			}
		}
	}
?>
	<div class="advertisements-outer-container">
		<h2>Custom Advertisements : </h2>	
		<form enctype="multipart/form-data" method="POST" id="advertisements-form">
			<div class="advertisemnets-fields">
				<i>Click on this icon to upload image advertisements.</i>
				<div class = 'uploaded_images'></div>
				<input type="submit" value="Upload Ad(s)" name="submitadvertisement" />
				<div class="advertisement_media_upload_field">
					<label for="upload_image">
						<input class="upload_button" type="button" value="Upload Media" rel = 'advertisement' title="Upload Media" />
					</label>
				</div>
			</div>
		</form>
		<?php  // Fetch all advertisements
		
		global $wpdb;
		$ad_posts = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='ad_image'");
		 ?>
			<div class="advertisements">
				<table border="1" cellpadding="10" cellspacing = "0" class="custom-advert-cont">
					<thead>
						<tr>
							<th>ID</th>
							<th>Image</th>
							<th>Link</th>
							<th>Visit Count</th> 
							<th>Shortcode</th>
							<th>Actions</th>
						</tr>
					</thead>
					
					<tbody>
						<?php
							if (!empty($ad_posts)) {
								foreach ($ad_posts as $advertisement) { 
									$post_detail = get_post($advertisement->post_id); ?>
									<tr>
										<td><strong><?php echo $advertisement->post_id; ?></strong></td>
										<td>
											<img src="<?php echo wp_get_attachment_url($advertisement->post_id); ?>" width="200" />
										</td>
										<td>
											<input type="text" value="<?php echo $post_detail->post_excerpt; ?>" />
											<a href="javascript:void(0);" class="edit-advertisement" id="<?php echo $advertisement->post_id; ?>">Save</a>
										</td>
										<td>
											<strong class = 'visit_count'>
												<?php
													$visit_count = get_post_meta($advertisement->post_id,'ad_counter',true);
													echo  $visit_count != null ? $visit_count : '0';
												?>
											</strong>
										</td>
										<td>
											<strong>[CUSTOM_ADVERT ID='<?php echo $advertisement->post_id; ?>']</strong>
										</td>
										<td>
											<a href="javascript:void(0);" class="delete-advertisement" id="<?php echo $advertisement->post_id; ?>">Delete Ad</a> /
											 
											<a href="javascript:void(0);" class="clear-count" rel="<?php echo $advertisement->post_id; ?>">Clear Ad Vists</a>
										</td>
									</tr>
							<?php } ?>
							<?php }else { ?>
									<tr><td colspan = 6>No Advertisements uploaded yet.</td></tr>
							<?php } ?>
					</tbody>
				</table>
			</div>
			<div class = 'ad_info'>
				<p>You can use these advertisements by using shortcode <strong>[CUSTOM_ADVERT ID='{ID}']</strong>. This id is your advertisement image id. shown in the table above.  </p>
				<p>For example, if image id is <strong>2</strong>, Shortcode will be like : <strong>[CUSTOM_ADVERT ID='2']</strong></p>
			</div>
	</div>
	
	
<?php }

/**
* Funciton to delete advertisement
**/
function cam_delete_advertisement() {
	$ad_id = intval($_POST['advertisement_id']);
	if($ad_id){
		delete_post_meta($ad_id, 'ad_image');
		delete_post_meta($ad_id, 'ad_counter');
		echo "Successfully Deleted";
	}else{
		echo "Something wrong occured, Please try again.";
	}
	exit;
}

/**
* Funciton to clear visits
**/
function cam_clear_visit() {
	$ad_id = intval($_POST['advertisement_id']);
	if($ad_id){
		delete_post_meta($ad_id, 'ad_counter');
		echo "Successfully Deleted";
	}else{
		echo "Something wrong occured, Please try again.";
	}
	exit;
}

/**
** Function to edit advertisement
**/
function cam_edit_advertisement() {
	$ad_id = intval($_POST['advertisement_id']);
	if($ad_id && !filter_var($_POST['image_link'], FILTER_VALIDATE_URL) === false){
		$args = array( 'ID'=> $ad_id,
	  'post_excerpt' => $_POST['image_link']);
		wp_update_post( $args );
		$return = "Advertisement updated successfully";
	}else{
		$return = "Something wrong occured, Please try again.";
	}
	echo $return;
	exit;
}

/*
* Function used to upload advertisement images
*/
function cam_adv_media_upload(){
	$response = array();
	if(!filter_var($_REQUEST['attachementUrl'], FILTER_VALIDATE_URL) === false){
		$attachment_Id = cam_get_image_id_from_url($_REQUEST['attachementUrl']);
		$file_attachment = get_post($attachment_Id);
		
		if (!empty($file_attachment)) {
			if($file_attachment->post_mime_type == 'image/jpeg' || $file_attachment->post_mime_type == 'image/pjpeg' || $file_attachment->post_mime_type == 'image/png' || $file_attachment->post_mime_type == 'image/gif'){
				$attachement_url = wp_get_attachment_url($attachment_Id);
			}
		}
	
		$response['attachment_id'] = $attachment_Id;
		$response['attachement_url'] = $attachement_url;
		$response['mime_type'] = $file_attachment->post_mime_type;
		$response['link'] = $file_attachment->post_excerpt;
		echo json_encode($response);
	}else{
		$response['message'] = "Something wrong occured, Please try again.";
		echo json_encode($response);
	}
	exit;
}

/*
* Function is used to get attachement Id
*/
function cam_get_image_id_from_url($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
    return $attachment[0]; 
}

/*
* Function is used to remove unused media tabs from media popup
*/
function cam_remove_media_upload_fields( $form_fields, $post ) {
	unset($form_fields['align']);
	unset($form_fields['image-size']);
	unset($form_fields['menu_order']);
	unset($form_fields['post_content']);
	$form_fields['post_title']['label']   = 'Image Name';
	$form_fields['post_title']['required']   = 0;
	$form_fields['post_excerpt']['label'] = 'Advertisement Link';
    ?>
	<style>tr.url{display:none;}div.imgedit-settings{display:none;}</style>
	<?php
    return $form_fields;
}

/*
* Function is used to integrate ap on front end by shortcode
*/
function cam_ad_integrate( $atts ) {
	$advertisementCheck = get_post_meta($atts['id'], 'ad_image', true);
	if($advertisementCheck){
		$ad_detail = get_post($atts['id']);
		$imageUrl = wp_get_attachment_url($ad_detail->ID);
		$ajax_url = admin_url('admin-ajax.php');
		echo $html = "
		<script type = 'text/javascript'> 
			function addAdCount(adID){
				var ajaxurl = '$ajax_url';
				var data = {};
				data['post_id'] = adID;
				data['action'] = 'cam_addAdCounter';
				jQuery.post(ajaxurl, data);
			}
		</script>
		<a class = 'custom_advert' target='_blank' onclick='javascript:addAdCount($ad_detail->ID);' href = '$ad_detail->post_excerpt'><img src= $imageUrl style='width: 100%;'></a>";
	}
	return;
}
add_shortcode( 'CUSTOM_ADVERT', 'cam_ad_integrate' );


/*
* Function is used to save advertisement visits
*/
function cam_addAdCounter(){
	$adID = intval($_REQUEST['post_id']);
	if( get_post_meta($adID,'ad_counter',true) != '' ){
		$count = get_post_meta($adID,'ad_counter',true);
		$count++;
		update_post_meta($adID,'ad_counter',$count);
	}else{
		add_post_meta($adID,'ad_counter',1);
	}
	echo 'done';
	exit;
}

if(is_admin()) {
	add_action( 'admin_menu', 'cam_advertisementlink' ); // Hook to add advertisements
	add_action( 'wp_ajax_cam_delete_advertisement', 'cam_delete_advertisement' ); // Hook for ajax
	add_action( 'wp_ajax_cam_clear_visit', 'cam_clear_visit' ); // Hook for ajax
	add_action( 'wp_ajax_cam_edit_advertisement', 'cam_edit_advertisement' ); // Hook for ajax
	add_action( 'wp_ajax_cam_adv_media_upload', 'cam_adv_media_upload' ); 
}
add_filter('attachment_fields_to_edit', 'cam_remove_media_upload_fields', 10000, 2);
add_action( 'wp_ajax_cam_addAdCounter', 'cam_addAdCounter');
add_action( 'wp_ajax_nopriv_cam_addAdCounter', 'cam_addAdCounter');
?>

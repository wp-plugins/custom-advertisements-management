jQuery(document).ready(function() {
	
	/**
	* Add More advertisements
	**/
	jQuery(".add-new-advertisement").click(function() {
		var count = parseInt(jQuery("#advertisementscount").val());
		jQuery("#advertisementscount").val(++count);
		var clone = jQuery("#advertisements-form .advertisemnets-fields").first().clone();
		clone.find('div:eq(0)').addClass('uploaded_images'+jQuery("#advertisementscount").val());
		clone.find("input").each(function() {
			//jQuery(this).val('').attr('name', jQuery(this).attr('name').replace('0',jQuery("#advertisementscount").val()));
		});
		clone.insertBefore("#advertisements-form input[type='submit']");
	});
	
	/**
	* Delete Advertisement
	**/
	jQuery(".delete-advertisement").click(function() {
		if (confirm("Are you sure you want to delete this?")) {
			$this = jQuery(this);
			var data = {
				'action': 'cam_delete_advertisement',
				'advertisement_id'    : jQuery(this).attr("id")
			};
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				jQuery($this).parents("tr").remove();
			});
		}
	});
	
	
	/**
	* clear visit count
	**/
	jQuery(".clear-count").click(function() {
		if (confirm("Are you sure you want to clear visit count? it can not be undo.")) {
			$this = jQuery(this);
			var data = {
				'action': 'cam_clear_visit',
				'advertisement_id'    : jQuery(this).attr("rel")
			};
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				jQuery($this).parents("tr").find('strong.visit_count').html(0);
			});
		}
	});
	
	
	/**
	* Edit Advertisement
	**/
	jQuery(".edit-advertisement").click(function() {
		var URL = jQuery(this).prev("input").val();
		if (URL != '' && isValidURL(URL)) {
			var data = {
				'action'            : 'cam_edit_advertisement',
				'advertisement_id'  : jQuery(this).attr("id"),
				'image_link'        : jQuery(this).prev("input").val()
			};
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				alert(response);
			});
		} else {
			alert('Please enter a valid URL')
		}
	});
	
	jQuery('.upload_button').live('click', function() {
		tb_show('', 'media-upload.php?TB_iframe=true');
	});
	
	window.send_to_editor = function(html) {
		var htmlDv = '<div class = "hdAttData" style = "display:none;">'+html+'</div>';
		jQuery('body').append(htmlDv);
		var mediaUrl = jQuery('body').find('div.hdAttData').last().find('a').attr('href');
		var data = {
			'attachementUrl': mediaUrl,
			'action'        : 'cam_adv_media_upload'
		};
		jQuery.post(ajaxurl, data, function(response) {
			if(response){
				var data = jQuery.parseJSON(response);
				if(!data.message){
					if(data.mime_type == 'image/jpeg' || data.mime_type == 'image/pjpeg' || data.mime_type == 'image/png') {
						var imgURL = data.attachement_url;
						var img = jQuery('<img src="'+imgURL+'"/>').load(function(){
								appendAddAttachment(response);
						});
					}else {
						alert('You can only add an image for advertisements section.');
					}
				}else{
					alert('Something wrong occured, Please try again.');
				}
			}
		});	
		tb_remove();	
	}
	
	function appendAddAttachment(response){
		var data = jQuery.parseJSON(response);
		jQuery('.uploaded_images').append("<div class='add'><a href='javascript:void(0);'><img src="+data.attachement_url+" width=100></a><a href=javascript:void(0) class=delete-banner-item onclick=\'deleteAvdImg(this);'\></a><input type=hidden name=advertisementimage[] value="+data.attachment_id+ "></div>");
		jQuery('.advertisemnets-fields input[type=submit]').fadeIn();
		tb_remove();
	}
});

/**
* Valid URL function
**/
function isValidURL(url){
	var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

	if(RegExp.test(url)){
		return true;
	}else{
		return false;
	}
}

/*
* Function used to delete uploaded image
*/

function deleteAvdImg(obj){
	jQuery(obj).parent().remove();
	if(!(jQuery('.uploaded_images .add').length > 0)){
		jQuery('.advertisemnets-fields input[type=submit]').fadeOut();
	}
}

/**
 * @link http://www.clashdesign.net/
 * @copyright clashdesign.net 2008
 * @author : Gérits Aurélien
 * @package : clashdesign framework and Magix CMS
 * @license  : Copyright private domaine
 * Framework for Magix CMS is protected software and is licensed under the 
 * Private License.
*/
function makeSublist(parent,child,isSubselectOptional,childVal)
{
	jQuery("body").append("<select style='display:none' id='"+parent+child+"'></select>");
	jQuery('#'+parent+child).html(jQuery("#"+child+" option"));
	
		var parentValue = jQuery('#'+parent).attr('value');
		jQuery('#'+child).html(jQuery("#"+parent+child+" .sub_"+parentValue).clone());
	
	childVal = (typeof childVal == "undefined")? "" : childVal ;
	jQuery("#"+child+' option[@value="'+ childVal +'"]').attr('selected','selected');
	
	jQuery('#'+parent).change( 
		function()
		{
			var parentValue = jQuery('#'+parent).attr('value');
			jQuery('#'+child).html(jQuery("#"+parent+child+" .sub_"+parentValue).clone());
			if(isSubselectOptional) jQuery('#'+child).prepend("<option value='none'> -- Select -- </option>");
			jQuery('#'+child).trigger("change");
                        jQuery('#'+child).focus();
		}
	);
}

	jQuery(document).ready(function()
	{
		//makeSublist('child','grandsun', true, '');	
		makeSublist('google_ad_width','google_ad_height', false, '1');	
	});

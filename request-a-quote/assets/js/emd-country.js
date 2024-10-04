jQuery(document).ready(function($){
	$('.emd-country').change(function(){
		var state_id = $(this).data('state');
		$.ajax({
			type: 'GET',
			url: ajaxurl,
			cache: false,
			async: false,
			data: {action:'emd_get_ajax_states',country:$(this).val()},
			success: function(response)
			{
				$('#'+state_id).html(response);
			},
		});
	});
});

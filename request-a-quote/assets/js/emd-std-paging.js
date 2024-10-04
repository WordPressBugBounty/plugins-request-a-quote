jQuery(document).ready(function($){
		page ={};
		$.fn.processPagination = function(page_click){
			var div_id = $(page_click).closest('.emd-view-results').attr('id');
			has_sync_class = $('#'+div_id).closest('.emd-container').hasClass('emd-paginate-sync');
			var other_divs = [];
			if(has_sync_class){
				parent_div = $('#'+div_id).closest('.emd-container').parents('.emd-integration-wrap');
				parent_div.find('.emd-view-results').each(function (){
					other_divs.push($(this).attr('id'));
				});
			}
			else {
				other_divs.push(div_id);
			}
			other_divs.forEach(function(div_id) {
				if(page[div_id] == undefined){
					page[div_id] = 1;	
				}
				if($(page_click).hasClass('prev')){
					page[div_id] --;
				}  
				else if($(page_click).hasClass('next')){
					page[div_id] ++;
				}  
				else{  
					page[div_id] = $(page_click).text();
				}
				var entity = $('#'+div_id).find('#emd_entity').val();
				var view = $('#'+div_id).find('#emd_view').val();
				var view_count = $('#'+div_id).find('#emd_view_count').val();
				var app = $('#'+div_id).find('#emd_app').val();
				var atts = $('#'+div_id).find('#atts_filter').val();
				var emd_paginate = $('#'+div_id).find('#emdpaginate').val();
				load_posts(div_id,entity,view,view_count,app,atts,emd_paginate);
			});
		}
		$('.pagination-bar a').click(function(){
			$(this).processPagination($(this));
			return false;
		}); 
		var load_posts = function(div_id,entity,view,view_count,app,atts,emd_paginate){
		$.ajax({
			type: 'GET',
			url: emd_std_paging_vars.ajax_url,
			cache: false,
			async: false,
			data: {action:'emd_get_std_pagenum',pageno: page[div_id],entity:entity,view:view,view_count:view_count,app:app,atts:atts,emd_paginate:emd_paginate},
			success: function(response){
				$('#'+ div_id).closest('.emd-container').replaceWith(response);
				if(emd_std_paging_vars.int_cdn_js_url != undefined){
					$.getScript(emd_std_paging_vars.int_cdn_js_url);
				}
				if(emd_std_paging_vars.int_js_url != undefined){
					$.getScript(emd_std_paging_vars.int_js_url);
				}
				if(emd_std_paging_vars.cdn_js_url != undefined){
					$.getScript(emd_std_paging_vars.cdn_js_url);
				}
				if(emd_std_paging_vars.js_url != undefined){
					$.getScript(emd_std_paging_vars.js_url);
				}
				if(emd_std_paging_vars.ratings_stats_vars_arr != undefined){
					ratings_stats_vars_arr = emd_std_paging_vars.rating_stats_arr;
				}
				if(emd_std_paging_vars.emd_rating_url != undefined){
					$.getScript(emd_std_paging_vars.emd_rating_url);
				} 
				if(emd_std_paging_vars.emd_rating_stats != undefined){
					$.getScript(emd_std_paging_vars.emd_rating_stats);
				}
				$('#'+div_id+' .pagination-bar a').click(function(){
					$(this).processPagination($(this));
					return false;
				});
			},
		}); 
	}
});

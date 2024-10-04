<?php
/**
 * Entity Related Shortcode Functions
 *
 * @package REQUEST_A_QUOTE
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Shortcode function
 *
 * @since WPAS 4.0
 * @param array $atts
 * @param array $args
 * @param string $form_name
 * @param int $pageno
 *
 * @return html
 */
function request_a_quote_contact_list_set_shc($atts, $args = Array() , $form_name = '', $pageno = 1, $shc_page_count = 0) {
	global $shc_count;
	if ($shc_page_count != 0) {
		$shc_count = $shc_page_count;
	} else {
		if (empty($shc_count)) {
			$shc_count = 1;
		} else {
			$shc_count++;
		}
	}
	$fields = Array(
		'app' => 'request_a_quote',
		'class' => 'emd_quote',
		'shc' => 'contact_list',
		'shc_count' => $shc_count,
		'form' => $form_name,
		'has_pages' => true,
		'pageno' => $pageno,
		'pgn_class' => '',
		'theme' => 'na',
		'hier' => 0,
		'hier_type' => 'ul',
		'hier_depth' => - 1,
		'hier_class' => '',
		'has_json' => 0,
	);
	$args_default = array(
		'posts_per_page' => '10',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'filter' => ''
	);
	return emd_shc_get_layout_list($atts, $args, $args_default, $fields);
}
add_shortcode('contact_list', 'contact_list_list');
function contact_list_list($atts) {
	$show_shc = 0;
	if (current_user_can('view_contact_list')) {
		$show_shc = 1;
	}
	$show_shc = apply_filters('emd_show_shc', $show_shc, 'contact_list');
	if ($show_shc == 1) {
		wp_enqueue_script('jquery');
		$emd_std_paging_vars['ajax_url'] = admin_url('admin-ajax.php');
		wp_enqueue_script('emd-std-paging-js');
		wp_localize_script('emd-std-paging-js', 'emd_std_paging_vars', $emd_std_paging_vars);
		wp_enqueue_style('emd-pagination');
		add_action('wp_footer', 'request_a_quote_enq_allview');
		request_a_quote_enq_custom_css_js();
		$list = request_a_quote_contact_list_set_shc($atts);
	} else {
		$list = '<div class="alert alert-info not-authorized">You are not authorized to access this content.</div>';
		$list = apply_filters('emd_no_access_msg_shc', $list, 'contact_list');
	}
	return $list;
}
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode', 11);
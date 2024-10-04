<?php
/**
 * Entity Class
 *
 * @package REQUEST_A_QUOTE
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Quote Class
 * @since WPAS 4.0
 */
class Emd_Quote extends Emd_Entity {
	protected $post_type = 'emd_quote';
	protected $app = 'request_a_quote';
	protected $sing_label;
	protected $plural_label;
	protected $menu_entity;
	protected $id;
	/**
	 * Initialize entity class
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function __construct() {
		add_action('init', array(
			$this,
			'set_filters'
		) , 1);
		add_action('admin_init', array(
			$this,
			'set_metabox'
		));
		add_action('save_post', array(
			$this,
			'change_title'
		) , 99, 2);
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		add_action("load-{$GLOBALS['pagenow']}", array(
			$this,
			'emd_quote_help_tabs'
		));
		add_action('admin_menu', array(
			$this,
			'add_menu_link'
		));
		add_action('admin_head-edit.php', array(
			$this,
			'add_opt_button'
		));
		$is_adv_filt_ext = apply_filters('emd_adv_filter_on', 0);
		if ($is_adv_filt_ext === 0) {
			add_action('manage_emd_quote_posts_custom_column', array(
				$this,
				'custom_columns'
			) , 10, 2);
			add_filter('manage_emd_quote_posts_columns', array(
				$this,
				'column_headers'
			));
		}
		add_filter('post_row_actions', array(
			$this,
			'duplicate_link'
		) , 10, 2);
		add_action('admin_action_emd_duplicate_entity', array(
			$this,
			'duplicate_entity'
		));
	}
	public function change_title_disable_emd_temp($title, $id) {
		$post = get_post($id);
		if ($this->post_type == $post->post_type && (!empty($this->id) && $this->id == $id)) {
			return '';
		}
		return $title;
	}
	/**
	 * Get column header list in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function column_headers($columns) {
		$ent_list = get_option($this->app . '_ent_list');
		if (!empty($ent_list[$this->post_type]['featured_img'])) {
			$columns['featured_img'] = __('Featured Image', 'request-a-quote');
		}
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if (!in_array($fkey, Array(
					'wpas_form_name',
					'wpas_form_submitted_by',
					'wpas_form_submitted_ip'
				)) && !in_array($mybox_field['type'], Array(
					'textarea',
					'wysiwyg'
				)) && $mybox_field['list_visible'] == 1) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');
		if (!empty($taxonomies)) {
			$tax_list = get_option($this->app . '_tax_list');
			foreach ($taxonomies as $taxonomy) {
				if (!empty($tax_list[$this->post_type][$taxonomy->name]) && $tax_list[$this->post_type][$taxonomy->name]['list_visible'] == 1) {
					$columns[$taxonomy->name] = $taxonomy->label;
				}
			}
		}
		$rel_list = get_option($this->app . '_rel_list');
		if (!empty($rel_list)) {
			foreach ($rel_list as $krel => $rel) {
				if ($rel['from'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'from'
				))) {
					$columns[$krel] = $rel['from_title'];
				} elseif ($rel['to'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'to'
				))) {
					$columns[$krel] = $rel['to_title'];
				}
			}
		}
		return $columns;
	}
	/**
	 * Get custom column values in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param int $column_id
	 * @param int $post_id
	 *
	 * @return string $value
	 */
	public function custom_columns($column_id, $post_id) {
		if (taxonomy_exists($column_id) == true) {
			$terms = get_the_terms($post_id, $column_id);
			$ret = array();
			if (!empty($terms)) {
				foreach ($terms as $term) {
					$url = add_query_arg(array(
						'post_type' => $this->post_type,
						'term' => $term->slug,
						'taxonomy' => $column_id
					) , admin_url('edit.php'));
					$a_class = preg_replace('/^emd_/', '', $this->post_type);
					$ret[] = sprintf('<a href="%s"  class="' . esc_attr($a_class) . '-tax ' . esc_attr($term->slug) . '">%s</a>', esc_url($url) , esc_html($term->name));
				}
			}
			echo wp_kses_post(implode(', ', $ret));
			return;
		}
		$rel_list = get_option($this->app . '_rel_list');
		if (!empty($rel_list) && !empty($rel_list[$column_id])) {
			$rel_arr = $rel_list[$column_id];
			if ($rel_arr['from'] == $this->post_type) {
				$other_ptype = $rel_arr['to'];
			} elseif ($rel_arr['to'] == $this->post_type) {
				$other_ptype = $rel_arr['from'];
			}
			$column_id = str_replace('rel_', '', $column_id);
			if (function_exists('p2p_type') && p2p_type($column_id)) {
				$rel_args = apply_filters('emd_ext_p2p_add_query_vars', array(
					'posts_per_page' => - 1
				) , Array(
					$other_ptype
				));
				$connected = p2p_type($column_id)->get_connected($post_id, $rel_args);
				$ptype_obj = get_post_type_object($this->post_type);
				$edit_cap = $ptype_obj->cap->edit_posts;
				$ret = array();
				if (empty($connected->posts)) return '&ndash;';
				foreach ($connected->posts as $myrelpost) {
					$rel_title = get_the_title($myrelpost->ID);
					$rel_title = apply_filters('emd_ext_p2p_connect_title', $rel_title, $myrelpost, '');
					$url = get_permalink($myrelpost->ID);
					$url = apply_filters('emd_ext_connected_ptype_url', $url, $myrelpost, $edit_cap);
					$ret[] = sprintf('<a href="%s" title="%s" target="_blank">%s</a>', esc_url($url) , esc_attr($rel_title) , esc_html($rel_title));
				}
				echo wp_kses_post(implode(', ', $ret));
				return;
			}
		}
		$value = get_post_meta($post_id, $column_id, true);
		$type = "";
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($fkey == $column_id) {
					$type = $mybox_field['type'];
					break;
				}
			}
		}
		if ($column_id == 'featured_img') {
			$type = 'featured_img';
		}
		switch ($type) {
			case 'featured_img':
				$thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id) , 'thumbnail');
				if (!empty($thumb_url)) {
					$value = "<img style='max-width:100%;height:auto;' src='" . esc_url($thumb_url[0]) . "' >";
				}
			break;
			case 'plupload_image':
			case 'image':
			case 'thickbox_image':
				$image_list = emd_mb_meta($column_id, 'type=image');
				$value = "";
				if (!empty($image_list)) {
					$myimage = current($image_list);
					$value = "<img style='max-width:100%;height:auto;' src='" . esc_url($myimage['url']) . "' >";
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = emd_mb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = emd_mb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$fsrc = wp_mime_type_icon($myfile['ID']);
						$value.= "<a style='margin:5px;' href='" . esc_url($myfile['url']) . "' target='_blank'><img src='" . esc_url($fsrc) . "' title='" . esc_attr($myfile['name']) . "' width='20' /></a>";
					}
				}
			break;
			case 'radio':
			case 'checkbox_list':
			case 'select':
			case 'select_advanced':
				$value = emd_get_attr_val($this->app, $post_id, $this->post_type, $column_id);
			break;
			case 'checkbox':
				if ($value == 1) {
					$value = '<span class="dashicons dashicons-yes"></span>';
				} elseif ($value == 0) {
					$value = '<span class="dashicons dashicons-no-alt"></span>';
				}
			break;
			case 'rating':
				$value = apply_filters('emd_get_rating_value', $value, Array(
					'meta' => $column_id
				) , $post_id);
			break;
		}
		if (is_array($value)) {
			$value = "<div class='clonelink'>" . implode("</div><div class='clonelink'>", $value) . "</div>";
		}
		echo wp_kses_post($value);
	}
	function emd_quote_help_tabs() {
		global $current_screen;
		$sales_quote_definition_list = __('<p>A sales quote allows a prospective buyer to see what costs would be involved for the work they would like to have done. Many businesses provide services that cannot have an upfront price, as the costs involved can vary. This can be due to the materials that would be used (which can differ depending on the individual needs of the customer), and the manpower that would be necessary. Therefore, it is common practice for these companies to provide the potential customer with a quote (or estimate) of how much it should cost. </p>
<p>This quotation will be made by the company using the information that the potential customer provides, regarding the relevant elements that may affect the price. A quote can help the prospective buyer when deciding which company to use, and which services they are looking for.</p>', 'request-a-quote');
		$criteria_for_selection_list = __('<p>When considering the information provided on a quote, not only the price should be important; it is also advisable to look at quality and professionalism within a company. This is why it is common practice for most companies to provide prospective customers with portfolios and examples of previous work. This can also help the potential customer when deciding which company to choose for the work they need to have done.</p>', 'request-a-quote');
		$preparing_quotes_list = __('<p>Detailed, well-considered quotes win work. Knowing how to write winning quotes is an important part of growing your business. But getting your quotes right can be a big challenge. You want to win the job with a competitive price but you need to make sure your costs are covered and you make a decent profit.</p>', 'request-a-quote');
		switch ($current_screen->id) {
			case 'edit-emd_quote':
				$current_screen->set_help_sidebar("");
				$current_screen->add_help_tab(array(
					'id' => 'emd_quote_sales_quote_definition_list',
					'title' => __('Sales Quote Definition', 'request-a-quote') ,
					'content' => $sales_quote_definition_list,
				));
				$current_screen->add_help_tab(array(
					'id' => 'emd_quote_criteria_for_selection_list',
					'title' => __('Criteria for selection', 'request-a-quote') ,
					'content' => $criteria_for_selection_list,
				));
				$current_screen->add_help_tab(array(
					'id' => 'emd_quote_preparing_quotes_list',
					'title' => __('Preparing quotes', 'request-a-quote') ,
					'content' => $preparing_quotes_list,
				));
			break;
		}
	}
	/**
	 * Register post type and taxonomies and set initial values for taxs
	 *
	 * @since WPAS 4.0
	 *
	 */
	public static function register() {
		$labels = array(
			'name' => __('Quotes', 'request-a-quote') ,
			'singular_name' => __('Quote', 'request-a-quote') ,
			'add_new' => __('Add New', 'request-a-quote') ,
			'add_new_item' => __('Add New Quote', 'request-a-quote') ,
			'edit_item' => __('Edit Quote', 'request-a-quote') ,
			'new_item' => __('New Quote', 'request-a-quote') ,
			'all_items' => __('All Quotes', 'request-a-quote') ,
			'view_item' => __('View Quote', 'request-a-quote') ,
			'search_items' => __('Search Quotes', 'request-a-quote') ,
			'not_found' => __('No Quotes Found', 'request-a-quote') ,
			'not_found_in_trash' => __('No Quotes Found In Trash', 'request-a-quote') ,
			'menu_name' => __('Quotes', 'request-a-quote') ,
		);
		$ent_map_list = get_option('request_a_quote_ent_map_list', Array());
		$myrole = emd_get_curr_usr_role('request_a_quote');
		if (!empty($ent_map_list['emd_quote']['rewrite'])) {
			$rewrite = $ent_map_list['emd_quote']['rewrite'];
		} else {
			$rewrite = 'emd_quote';
		}
		$supports = Array();
		register_post_type('emd_quote', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('A sales quote allows a prospective buyer to see what costs would be involved for the work they would like to have done.', 'request-a-quote') ,
			'show_in_menu' => true,
			'menu_position' => 6,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-portfolio',
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_quote',
			'supports' => false,
		));
		$tax_settings = get_option('request_a_quote_tax_settings', Array());
		$myrole = emd_get_curr_usr_role('request_a_quote');
		$raq_services_nohr_labels = array(
			'name' => __('Services', 'request-a-quote') ,
			'singular_name' => __('Service', 'request-a-quote') ,
			'search_items' => __('Search Services', 'request-a-quote') ,
			'popular_items' => __('Popular Services', 'request-a-quote') ,
			'all_items' => __('All', 'request-a-quote') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Service', 'request-a-quote') ,
			'update_item' => __('Update Service', 'request-a-quote') ,
			'add_new_item' => __('Add New Service', 'request-a-quote') ,
			'new_item_name' => __('Add New Service Name', 'request-a-quote') ,
			'separate_items_with_commas' => __('Seperate Services with commas', 'request-a-quote') ,
			'add_or_remove_items' => __('Add or Remove Services', 'request-a-quote') ,
			'choose_from_most_used' => __('Choose from the most used Services', 'request-a-quote') ,
			'menu_name' => __('Services', 'request-a-quote') ,
		);
		if (empty($tax_settings['raq_services']['hide']) || (!empty($tax_settings['raq_services']['hide']) && $tax_settings['raq_services']['hide'] != 'hide')) {
			if (!empty($tax_settings['raq_services']['rewrite'])) {
				$rewrite = $tax_settings['raq_services']['rewrite'];
			} else {
				$rewrite = 'raq_services';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $raq_services_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'show_in_rest' => false,
				'capabilities' => array(
					'manage_terms' => 'manage_raq_services',
					'edit_terms' => 'edit_raq_services',
					'delete_terms' => 'delete_raq_services',
					'assign_terms' => 'assign_raq_services'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['raq_services']['edit'][$myrole]) && $tax_settings['raq_services']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('raq_services', array(
				'emd_quote'
			) , $targs);
		}
		$raq_quote_status_nohr_labels = array(
			'name' => __('Quote Statuses', 'request-a-quote') ,
			'singular_name' => __('Quote Status', 'request-a-quote') ,
			'search_items' => __('Search Quote Statuses', 'request-a-quote') ,
			'popular_items' => __('Popular Quote Statuses', 'request-a-quote') ,
			'all_items' => __('All', 'request-a-quote') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Quote Status', 'request-a-quote') ,
			'update_item' => __('Update Quote Status', 'request-a-quote') ,
			'add_new_item' => __('Add New Quote Status', 'request-a-quote') ,
			'new_item_name' => __('Add New Quote Status Name', 'request-a-quote') ,
			'separate_items_with_commas' => __('Seperate Quote Statuses with commas', 'request-a-quote') ,
			'add_or_remove_items' => __('Add or Remove Quote Statuses', 'request-a-quote') ,
			'choose_from_most_used' => __('Choose from the most used Quote Statuses', 'request-a-quote') ,
			'menu_name' => __('Quote Statuses', 'request-a-quote') ,
		);
		if (empty($tax_settings['raq_quote_status']['hide']) || (!empty($tax_settings['raq_quote_status']['hide']) && $tax_settings['raq_quote_status']['hide'] != 'hide')) {
			if (!empty($tax_settings['raq_quote_status']['rewrite'])) {
				$rewrite = $tax_settings['raq_quote_status']['rewrite'];
			} else {
				$rewrite = 'raq_quote_status';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $raq_quote_status_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'show_in_rest' => false,
				'capabilities' => array(
					'manage_terms' => 'manage_raq_quote_status',
					'edit_terms' => 'edit_raq_quote_status',
					'delete_terms' => 'delete_raq_quote_status',
					'assign_terms' => 'assign_raq_quote_status'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['raq_quote_status']['edit'][$myrole]) && $tax_settings['raq_quote_status']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('raq_quote_status', array(
				'emd_quote'
			) , $targs);
		}
		$tax_list = get_option('request_a_quote_tax_list');
		$init_tax = get_option('request_a_quote_init_tax', Array());
		if (!empty($tax_list['emd_quote'])) {
			foreach ($tax_list['emd_quote'] as $keytax => $mytax) {
				if (!empty($mytax['init_values']) && (empty($init_tax['emd_quote']) || (!empty($init_tax['emd_quote']) && !in_array($keytax, $init_tax['emd_quote'])))) {
					$set_tax_terms = Array();
					foreach ($mytax['init_values'] as $myinit) {
						$set_tax_terms[] = $myinit;
					}
					self::set_taxonomy_init($set_tax_terms, $keytax);
					$init_tax['emd_quote'][] = $keytax;
				}
			}
			update_option('request_a_quote_init_tax', $init_tax);
		}
	}
	/**
	 * Set metabox fields,labels,filters, comments, relationships if exists
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function set_filters() {
		do_action('emd_ext_class_init', $this);
		$search_args = Array();
		$filter_args = Array();
		$this->sing_label = __('Quote', 'request-a-quote');
		$this->plural_label = __('Quotes', 'request-a-quote');
		$this->menu_entity = 'emd_quote';
		$this->boxes['emd_quote_info_emd_quote_0'] = array(
			'id' => 'emd_quote_info_emd_quote_0',
			'title' => __('Quote Info', 'request-a-quote') ,
			'app_name' => 'request_a_quote',
			'pages' => array(
				'emd_quote'
			) ,
			'context' => 'normal',
		);
		list($search_args, $filter_args) = $this->set_args_boxes();
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
		do_action('emd_set_adv_filtering', $this->post_type, $search_args, $this->boxes, $filter_args, $this->app, $this->plural_label);
		add_action('admin_notices', array(
			$this,
			'show_lite_filters'
		));
		$ent_map_list = get_option($this->app . '_ent_map_list');
	}
	/**
	 * Initialize metaboxes
	 * @since WPAS 4.5
	 *
	 */
	public function set_metabox() {
		if (class_exists('EMD_Meta_Box') && is_array($this->boxes)) {
			foreach ($this->boxes as $meta_box) {
				new EMD_Meta_Box($meta_box);
			}
		}
	}
	/**
	 * Change content for created frontend views
	 * @since WPAS 4.0
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function change_content($content) {
		global $post;
		$layout = "";
		$this->id = $post->ID;
		$tools = get_option('request_a_quote_tools');
		if (!empty($tools['disable_emd_templates'])) {
			add_filter('the_title', array(
				$this,
				'change_title_disable_emd_temp'
			) , 10, 2);
		}
		if (get_post_type() == $this->post_type && is_single()) {
			ob_start();
			do_action('emd_single_before_content', $this->app, $this->post_type);
			emd_get_template_part($this->app, 'single', 'emd-quote');
			do_action('emd_single_after_content', $this->app, $this->post_type);
			$layout = ob_get_clean();
		}
		if ($layout != "") {
			$content = $layout;
		}
		if (!empty($tools['disable_emd_templates'])) {
			remove_filter('the_title', array(
				$this,
				'change_title_disable_emd_temp'
			) , 10, 2);
		}
		return $content;
	}
	/**
	 * Add operations and add new submenu hook
	 * @since WPAS 4.4
	 */
	public function add_menu_link() {
		add_submenu_page(null, __('CSV Import/Export', 'request-a-quote') , __('CSV Import/Export', 'request-a-quote') , 'manage_operations_emd_quotes', 'operations_emd_quote', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_quotes')) {
			if (!function_exists('emd_operations_entity')) {
				emd_lite_get_operations('opr', $this->plural_label, $this->app);
			} else {
				do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $this->app, $this->menu_entity);
			}
		}
	}
	public function show_lite_filters() {
		if (class_exists('EMD_AFC')) {
			return;
		}
		global $pagenow;
		if (get_post_type() == $this->post_type && $pagenow == 'edit.php') {
			emd_lite_get_filters();
		}
	}
}
new Emd_Quote;
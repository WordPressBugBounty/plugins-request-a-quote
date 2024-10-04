<?php
/**
 * Install and Deactivate Plugin Functions
 * @package REQUEST_A_QUOTE
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (!class_exists('Request_A_Quote_Install_Deactivate')):
	/**
	 * Request_A_Quote_Install_Deactivate Class
	 * @since WPAS 4.0
	 */
	class Request_A_Quote_Install_Deactivate {
		private $option_name;
		/**
		 * Hooks for install and deactivation and create options
		 * @since WPAS 4.0
		 */
		public function __construct() {
			$this->option_name = 'request_a_quote';
			add_action('admin_init', array(
				$this,
				'check_update'
			));
			register_activation_hook(REQUEST_A_QUOTE_PLUGIN_FILE, array(
				$this,
				'install'
			));
			register_deactivation_hook(REQUEST_A_QUOTE_PLUGIN_FILE, array(
				$this,
				'deactivate'
			));
			add_action('wp_head', array(
				$this,
				'version_in_header'
			));
			add_action('admin_init', array(
				$this,
				'setup_pages'
			));
			add_action('admin_notices', array(
				$this,
				'install_notice'
			));
			add_action('generate_rewrite_rules', 'emd_create_rewrite_rules');
			add_filter('query_vars', 'emd_query_vars');
			add_action('admin_init', array(
				$this,
				'register_settings'
			) , 0);
			add_action('before_delete_post', array(
				$this,
				'delete_post_file_att'
			));
			add_action('wp_ajax_emd_get_std_pagenum', 'emd_get_std_pagenum');
			add_action('wp_ajax_nopriv_emd_get_std_pagenum', 'emd_get_std_pagenum');
			add_action('wp_ajax_emd_load_file', 'emd_load_file');
			add_action('wp_ajax_nopriv_emd_load_file', 'emd_load_file');
			add_action('wp_ajax_emd_delete_file', 'emd_delete_file');
			add_action('wp_ajax_nopriv_emd_delete_file', 'emd_delete_file');
			add_action('init', array(
				$this,
				'init_extensions'
			) , 99);
			do_action('emd_ext_actions', $this->option_name);
			add_filter('tiny_mce_before_init', array(
				$this,
				'tinymce_fix'
			));
		}
		public function check_update() {
			$curr_version = get_option($this->option_name . '_version', 1);
			$new_version = constant(strtoupper($this->option_name) . '_VERSION');
			if (version_compare($curr_version, $new_version, '<')) {
				$this->set_options();
				$this->set_roles_caps();
				$this->set_notification();
				if (!get_option($this->option_name . '_activation_date')) {
					$triggerdate = mktime(0, 0, 0, date('m') , date('d') + 7, date('Y'));
					add_option($this->option_name . '_activation_date', $triggerdate);
				}
				set_transient($this->option_name . '_activate_redirect', true, 30);
				do_action($this->option_name . '_upgrade', $new_version);
				update_option($this->option_name . '_version', $new_version);
			}
		}
		public function version_in_header() {
			$version = constant(strtoupper($this->option_name) . '_VERSION');
			$name = constant(strtoupper($this->option_name) . '_NAME');
			echo '<meta name="generator" content="' . esc_attr($name) . ' v' . esc_attr($version) . ' - https://emdplugins.com" />' . "\n";
		}
		public function init_extensions() {
			do_action('emd_ext_init', $this->option_name);
		}
		/**
		 * Runs on plugin install to setup custom post types and taxonomies
		 * flushing rewrite rules, populates settings and options
		 * creates roles and assign capabilities
		 * @since WPAS 4.0
		 *
		 */
		public function install() {
			$this->set_options();
			$this->set_notification();
			Emd_Quote::register();
			flush_rewrite_rules();
			$this->set_roles_caps();
			set_transient($this->option_name . '_activate_redirect', true, 30);
			do_action('emd_ext_install_hook', $this->option_name);
		}
		/**
		 * Runs on plugin deactivate to remove options, caps and roles
		 * flushing rewrite rules
		 * @since WPAS 4.0
		 *
		 */
		public function deactivate() {
			flush_rewrite_rules();
			$this->remove_caps_roles();
			$this->reset_options();
			do_action('emd_ext_deactivate', $this->option_name);
		}
		/**
		 * Register notification and/or license settings
		 * @since WPAS 4.0
		 *
		 */
		public function register_settings() {
			$notif_settings = new Emd_Notifications($this->option_name);
			$notif_settings->register_settings();
			do_action('emd_ext_register', $this->option_name);
			if (!get_transient($this->option_name . '_activate_redirect')) {
				return;
			}
			// Delete the redirect transient.
			delete_transient($this->option_name . '_activate_redirect');
			$query_args = array(
				'page' => $this->option_name
			);
			wp_safe_redirect(add_query_arg($query_args, admin_url('admin.php')));
		}
		/**
		 * Sets caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function set_roles_caps() {
			global $wp_roles;
			$cust_roles = Array();
			update_option($this->option_name . '_cust_roles', $cust_roles);
			$add_caps = Array(
				'edit_others_emd_quotes' => Array(
					'administrator'
				) ,
				'delete_emd_quotes' => Array(
					'administrator'
				) ,
				'edit_raq_services' => Array(
					'administrator'
				) ,
				'edit_private_emd_quotes' => Array(
					'administrator'
				) ,
				'delete_raq_quote_status' => Array(
					'administrator'
				) ,
				'delete_raq_services' => Array(
					'administrator'
				) ,
				'delete_private_emd_quotes' => Array(
					'administrator'
				) ,
				'edit_emd_quotes' => Array(
					'administrator'
				) ,
				'manage_operations_emd_quotes' => Array(
					'administrator'
				) ,
				'view_single_quote' => Array(
					'administrator',
					'subscriber'
				) ,
				'read_private_emd_quotes' => Array(
					'administrator'
				) ,
				'view_contact_list' => Array(
					'administrator'
				) ,
				'assign_raq_services' => Array(
					'administrator'
				) ,
				'manage_raq_quote_status' => Array(
					'administrator'
				) ,
				'edit_raq_quote_status' => Array(
					'administrator'
				) ,
				'assign_raq_quote_status' => Array(
					'administrator'
				) ,
				'manage_raq_services' => Array(
					'administrator'
				) ,
				'delete_others_emd_quotes' => Array(
					'administrator'
				) ,
				'publish_emd_quotes' => Array(
					'administrator'
				) ,
				'view_request_a_quote_dashboard' => Array(
					'administrator'
				) ,
				'delete_published_emd_quotes' => Array(
					'administrator'
				) ,
				'edit_published_emd_quotes' => Array(
					'administrator'
				) ,
			);
			update_option($this->option_name . '_add_caps', $add_caps);
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				if (!empty($cust_roles)) {
					foreach ($cust_roles as $krole => $vrole) {
						$myrole = get_role($krole);
						if (empty($myrole)) {
							$myrole = add_role($krole, $vrole);
						}
					}
				}
				$this->set_reset_caps($wp_roles, 'add');
			}
		}
		/**
		 * Removes caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function remove_caps_roles() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'remove');
			}
		}
		/**
		 * Set  capabilities
		 *
		 * @since WPAS 4.0
		 * @param object $wp_roles
		 * @param string $type
		 *
		 */
		public function set_reset_caps($wp_roles, $type) {
			$caps['enable'] = get_option($this->option_name . '_add_caps', Array());
			$caps['enable'] = apply_filters('emd_ext_get_caps', $caps['enable'], $this->option_name);
			foreach ($caps as $stat => $role_caps) {
				foreach ($role_caps as $mycap => $roles) {
					foreach ($roles as $myrole) {
						if (($type == 'add' && $stat == 'enable') || ($stat == 'disable' && $type == 'remove')) {
							$wp_roles->add_cap($myrole, $mycap);
						} else if (($type == 'remove' && $stat == 'enable') || ($type == 'add' && $stat == 'disable')) {
							$wp_roles->remove_cap($myrole, $mycap);
						}
					}
				}
			}
		}
		/**
		 * Sets notification options
		 * @since WPAS 4.0
		 *
		 */
		private function set_notification() {
			$notify_list['quote_notify'] = Array(
				'label' => __('Quote Receipt', 'request-a-quote') ,
				'active' => 1,
				'level' => 'entity',
				'entity' => 'emd_quote',
				'ev_front_add' => 1,
				'user_msg' => Array(
					'subject' => 'Thank you for requesting a quote',
					'message' => 'Hi {emd_contact_first_name} {emd_contact_last_name}

We have received a quote request. We will get back to you as soon as possible.

Thanks,
Site Owner',
					'send_to' => Array(
						Array(
							'active' => 1,
							'entity' => 'emd_quote',
							'attr' => 'emd_contact_email',
							'label' => __('Quote Email', 'request-a-quote')
						)
					) ,
					'reply_to' => '',
					'cc' => '',
					'bcc' => ''
				) ,
				'admin_msg' => Array(
					'subject' => 'A quote request has been recieved',
					'message' => 'Hi There,
A quote request with the following info has been received:
<hr>
First Name: {emd_contact_first_name}
Last Name: {emd_contact_last_name}
Email: {emd_contact_email}
Address: {emd_contact_address}
City: {emd_contact_city}
Zipcode: {emd_contact_zip}
State: {emd_contact_state}
Phone: {emd_contact_phone}
Callback Time: {emd_contact_callback_time}	

Thanks',
					'send_to' => '',
					'reply_to' => '',
					'cc' => '',
					'bcc' => ''
				)
			);
			update_option($this->option_name . '_notify_init_list', $notify_list);
			if (get_option($this->option_name . '_notify_list') === false) {
				update_option($this->option_name . '_notify_list', $notify_list);
			}
		}
		/**
		 * Set app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function set_options() {
			$access_views = Array();
			if (get_option($this->option_name . '_setup_pages', 0) == 0) {
				update_option($this->option_name . '_setup_pages', 1);
			}
			$access_views['single'] = Array(
				Array(
					'name' => 'single_quote',
					'obj' => 'emd_quote'
				) ,
			);
			update_option($this->option_name . '_access_views', $access_views);
			$ent_list = Array(
				'emd_quote' => Array(
					'label' => __('Quotes', 'request-a-quote') ,
					'rewrite' => 'emd_quote',
					'archive_view' => 0,
					'rest_api' => 0,
					'sortable' => 0,
					'searchable' => 1,
					'class_title' => Array(
						'emd_contact_id'
					) ,
					'unique_keys' => Array(
						'emd_contact_id'
					) ,
				) ,
			);
			update_option($this->option_name . '_ent_list', $ent_list);
			$shc_list['app'] = 'Request a quote';
			$shc_list['has_gmap'] = 0;
			$shc_list['has_form_lite'] = 1;
			$shc_list['has_lite'] = 1;
			$shc_list['has_bs'] = 0;
			$shc_list['has_autocomplete'] = 0;
			$shc_list['remove_vis'] = 0;
			$shc_list['forms']['request_a_quote'] = Array(
				'name' => 'request_a_quote',
				'type' => 'submit',
				'ent' => 'emd_quote',
				'targeted_device' => 'desktops',
				'label_position' => 'top',
				'element_size' => 'medium',
				'display_inline' => '1',
				'noaccess_msg' => 'You are not allowed to access to this area. Please contact the site administrator.',
				'disable_submit' => '0',
				'submit_status' => 'publish',
				'visitor_submit_status' => 'publish',
				'submit_button_type' => 'btn-primary',
				'submit_button_label' => 'Send a request',
				'submit_button_size' => 'btn-large',
				'submit_button_block' => '0',
				'submit_button_fa' => '',
				'submit_button_fa_size' => '',
				'submit_button_fa_pos' => 'left',
				'show_captcha' => 'never-show',
				'disable_after' => '0',
				'confirm_method' => 'text',
				'confirm_url' => '',
				'confirm_success_txt' => 'Thanks for your submission.',
				'confirm_error_txt' => 'There has been an error when submitting your entry. Please contact the site administrator.',
				'enable_ajax' => '1',
				'after_submit' => 'show',
				'schedule_start' => '',
				'schedule_end' => '',
				'enable_operators' => '0',
				'ajax_search' => '0',
				'result_templ' => '',
				'result_fields' => '',
				'noresult_msg' => '',
				'view_name' => '',
				'honeypot' => '1',
				'login_reg' => 'none',
				'page_title' => __('Request a quote', 'request-a-quote')
			);
			$shc_list['shcs']['contact_list'] = Array(
				"class_name" => "emd_quote",
				"type" => "std",
				'page_title' => __('Contact List', 'request-a-quote') ,
			);
			if (!empty($shc_list)) {
				update_option($this->option_name . '_shc_list', $shc_list);
			}
			$attr_list['emd_quote']['emd_contact_first_name'] = Array(
				'label' => __('First Name', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_last_name'] = Array(
				'label' => __('Last Name', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_address'] = Array(
				'label' => __('Address', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_city'] = Array(
				'label' => __('City', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_zip'] = Array(
				'label' => __('Zipcode', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_country'] = Array(
				'label' => __('Country', 'request-a-quote') ,
				'display_type' => 'select',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'select_list' => 'country',
				'dependent_state' => 'emd_contact_state',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_state'] = Array(
				'label' => __('State', 'request-a-quote') ,
				'display_type' => 'select',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'select_list' => 'state',
				'dependent_country' => 'emd_contact_country',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_email'] = Array(
				'label' => __('Email', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
				'email' => true,
			);
			$attr_list['emd_quote']['emd_contact_phone'] = Array(
				'label' => __('Phone', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_pref'] = Array(
				'label' => __('Contact Preference', 'request-a-quote') ,
				'display_type' => 'radio',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
				'options' => array(
					'email' => esc_attr(__('Email', 'request-a-quote')) ,
					'telephone' => esc_attr(__('Telephone', 'request-a-quote'))
				) ,
				'std' => 'Email',
				'conditional' => Array(
					'attr_rules' => Array(
						'emd_contact_callback_time' => Array(
							'type' => 'select',
							'view' => 'show',
							'depend_check' => 'is',
							'depend_value' => 'telephone'
						) ,
					) ,
					'start_hide_attr' => Array(
						'emd_contact_callback_time'
					) ,
				) ,
			);
			$attr_list['emd_quote']['emd_contact_callback_time'] = Array(
				'label' => __('Callback Time', 'request-a-quote') ,
				'display_type' => 'select',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
				'options' => array(
					'' => __('Please Select', 'request-a-quote') ,
					'em' => esc_attr(__('5 to 8am', 'request-a-quote')) ,
					'lm' => esc_attr(__('11am to 12pm', 'request-a-quote')) ,
					'ea' => esc_attr(__('1 to 3pm', 'request-a-quote')) ,
					'la' => esc_attr(__('4 to 5pm', 'request-a-quote')) ,
					'ee' => esc_attr(__('5 to 7 pm', 'request-a-quote')) ,
					'le' => esc_attr(__('9pm to 4am', 'request-a-quote'))
				) ,
			);
			$attr_list['emd_quote']['emd_contact_budget'] = Array(
				'label' => __('Budget', 'request-a-quote') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'decimal',
				'number' => true,
			);
			$attr_list['emd_quote']['emd_contact_extrainfo'] = Array(
				'label' => __('Additional Details', 'request-a-quote') ,
				'display_type' => 'textarea',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
			);
			$attr_list['emd_quote']['emd_contact_id'] = Array(
				'label' => __('ID', 'request-a-quote') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'desc' => __('Unique identifier for a quote request.', 'request-a-quote') ,
				'type' => 'char',
				'hidden_func' => 'unique_id',
				'uniqueAttr' => true,
			);
			$attr_list['emd_quote']['emd_contact_attachment'] = Array(
				'label' => __('Attachments', 'request-a-quote') ,
				'display_type' => 'file',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_quote_info_emd_quote_0',
				'desc' => __('Attach related files to the quote.', 'request-a-quote') ,
				'type' => 'char',
			);
			$attr_list['emd_quote']['wpas_form_name'] = Array(
				'label' => __('Form Name', 'request-a-quote') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
				'options' => array() ,
				'no_update' => 1,
				'std' => 'admin',
			);
			$attr_list['emd_quote']['wpas_form_submitted_by'] = Array(
				'label' => __('Form Submitted By', 'request-a-quote') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
				'options' => array() ,
				'hidden_func' => 'user_login',
				'no_update' => 1,
			);
			$attr_list['emd_quote']['wpas_form_submitted_ip'] = Array(
				'label' => __('Form Submitted IP', 'request-a-quote') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'emd_quote_info_emd_quote_0',
				'type' => 'char',
				'options' => array() ,
				'hidden_func' => 'user_ip',
				'no_update' => 1,
			);
			$attr_list = apply_filters('emd_ext_attr_list', $attr_list, $this->option_name);
			if (!empty($attr_list)) {
				update_option($this->option_name . '_attr_list', $attr_list);
			}
			update_option($this->option_name . '_glob_init_list', Array());
			$glob_forms_list['request_a_quote']['captcha'] = 'never-show';
			$glob_forms_list['request_a_quote']['noaccess_msg'] = 'You are not allowed to access to this area. Please contact the site administrator.';
			$glob_forms_list['request_a_quote']['error_msg'] = 'There has been an error when submitting your entry. Please contact the site administrator.';
			$glob_forms_list['request_a_quote']['success_msg'] = 'Thanks for your submission.';
			$glob_forms_list['request_a_quote']['login_reg'] = 'none';
			$glob_forms_list['request_a_quote']['csrf'] = 1;
			$glob_forms_list['request_a_quote']['raq_services'] = Array(
				'show' => 1,
				'row' => 1,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_first_name'] = Array(
				'show' => 1,
				'row' => 2,
				'req' => 1,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_last_name'] = Array(
				'show' => 1,
				'row' => 3,
				'req' => 1,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_address'] = Array(
				'show' => 1,
				'row' => 4,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_city'] = Array(
				'show' => 1,
				'row' => 5,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_zip'] = Array(
				'show' => 1,
				'row' => 6,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_country'] = Array(
				'show' => 1,
				'row' => 7,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_state'] = Array(
				'show' => 1,
				'row' => 8,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_pref'] = Array(
				'show' => 1,
				'row' => 9,
				'req' => 1,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_email'] = Array(
				'show' => 1,
				'row' => 10,
				'req' => 1,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_phone'] = Array(
				'show' => 1,
				'row' => 11,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_callback_time'] = Array(
				'show' => 1,
				'row' => 12,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_budget'] = Array(
				'show' => 1,
				'row' => 13,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_extrainfo'] = Array(
				'show' => 1,
				'row' => 14,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_attachment'] = Array(
				'show' => 1,
				'row' => 15,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['request_a_quote']['emd_contact_id'] = Array(
				'show' => 1,
				'row' => 17,
				'req' => 0,
				'size' => 12,
			);
			if (!empty($glob_forms_list)) {
				update_option($this->option_name . '_glob_forms_init_list', $glob_forms_list);
				if (get_option($this->option_name . '_glob_forms_list') === false) {
					update_option($this->option_name . '_glob_forms_list', $glob_forms_list);
				}
			}
			$tax_list['emd_quote']['raq_services'] = Array(
				'archive_view' => 0,
				'label' => __('Services', 'request-a-quote') ,
				'single_label' => __('Service', 'request-a-quote') ,
				'default' => '',
				'type' => 'multi',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'raq_services',
				'init_values' => Array(
					Array(
						'name' => __('Service A', 'request-a-quote') ,
						'slug' => sanitize_title('Service A')
					) ,
					Array(
						'name' => __('Service B', 'request-a-quote') ,
						'slug' => sanitize_title('Service B')
					) ,
					Array(
						'name' => __('Service C', 'request-a-quote') ,
						'slug' => sanitize_title('Service C')
					)
				)
			);
			$tax_list['emd_quote']['raq_quote_status'] = Array(
				'archive_view' => 0,
				'label' => __('Quote Statuses', 'request-a-quote') ,
				'single_label' => __('Quote Status', 'request-a-quote') ,
				'default' => Array(
					__('Pending', 'request-a-quote')
				) ,
				'type' => 'multi',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'raq_quote_status',
				'init_values' => Array(
					Array(
						'name' => __('Pending', 'request-a-quote') ,
						'slug' => sanitize_title('Pending')
					) ,
					Array(
						'name' => __('Cancelled', 'request-a-quote') ,
						'slug' => sanitize_title('Cancelled')
					) ,
					Array(
						'name' => __('Expired', 'request-a-quote') ,
						'slug' => sanitize_title('Expired')
					) ,
					Array(
						'name' => __('Accepted', 'request-a-quote') ,
						'slug' => sanitize_title('Accepted')
					) ,
					Array(
						'name' => __('Declined', 'request-a-quote') ,
						'slug' => sanitize_title('Declined')
					)
				)
			);
			$tax_list = apply_filters('emd_ext_tax_list', $tax_list, $this->option_name);
			if (!empty($tax_list)) {
				update_option($this->option_name . '_tax_list', $tax_list);
			}
			$emd_activated_plugins = get_option('emd_activated_plugins');
			if (!$emd_activated_plugins) {
				update_option('emd_activated_plugins', Array(
					'request-a-quote'
				));
			} elseif (!in_array('request-a-quote', $emd_activated_plugins)) {
				array_push($emd_activated_plugins, 'request-a-quote');
				update_option('emd_activated_plugins', $emd_activated_plugins);
			}
			//conf parameters for incoming email
			//conf parameters for inline entity
			//conf parameters for calendar
			//conf parameters for mailchimp
			$has_mailchimp = Array(
				'request_a_quote' => Array(
					'entity' => 'emd_quote',
					'tax' => Array(
						'raq_services'
					)
				)
			);
			update_option($this->option_name . '_has_mailchimp', $has_mailchimp);
			//action to configure different extension conf parameters for this plugin
			do_action('emd_ext_set_conf', 'request-a-quote');
		}
		/**
		 * Reset app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function reset_options() {
			delete_option($this->option_name . '_shc_list');
			delete_option($this->option_name . '_has_mailchimp');
			do_action('emd_ext_reset_conf', 'request-a-quote');
		}
		/**
		 * Show admin notices
		 *
		 * @since WPAS 4.0
		 *
		 * @return html
		 */
		public function install_notice() {
			if (current_user_can('manage_options') && get_option($this->option_name . '_setup_pages') == 1) {
				echo "<div id=\"message\" class=\"updated\"><p><strong>" . esc_html__('Welcome to Request a quote', 'request-a-quote') . "</strong></p>
           <p class=\"submit\"><a href=\"" . add_query_arg('setup_request_a_quote_pages', 'true', admin_url('index.php')) . "\" class=\"button-primary\">" . __('Setup Request a quote Pages', 'request-a-quote') . "</a> <a class=\"skip button-primary\" href=\"" . add_query_arg('skip_setup_request_a_quote_pages', 'true', admin_url('index.php')) . "\">" . __('Skip setup', 'request-a-quote') . "</a></p>
         </div>";
			}
		}
		/**
		 * Setup pages for components and redirect to dashboard
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function setup_pages() {
			if (!is_admin()) {
				return;
			}
			if (!empty($_GET['setup_' . $this->option_name . '_pages'])) {
				$shc_list = get_option($this->option_name . '_shc_list');
				emd_create_install_pages($this->option_name, $shc_list);
				update_option($this->option_name . '_setup_pages', 2);
				wp_redirect(admin_url('admin.php?page=' . $this->option_name . '_settings&request-a-quote-installed=true'));
				exit;
			}
			if (!empty($_GET['skip_setup_' . $this->option_name . '_pages'])) {
				update_option($this->option_name . '_setup_pages', 2);
				wp_redirect(admin_url('admin.php?page=' . $this->option_name . '_settings'));
				exit;
			}
		}
		/**
		 * Delete file attachments when a post is deleted
		 *
		 * @since WPAS 4.0
		 * @param $pid
		 *
		 * @return bool
		 */
		public function delete_post_file_att($pid) {
			$entity_fields = get_option($this->option_name . '_attr_list');
			$post_type = get_post_type($pid);
			if (!empty($entity_fields[$post_type])) {
				//Delete fields
				foreach (array_keys($entity_fields[$post_type]) as $myfield) {
					if (in_array($entity_fields[$post_type][$myfield]['display_type'], Array(
						'file',
						'image',
						'plupload_image',
						'thickbox_image'
					))) {
						$pmeta = get_post_meta($pid, $myfield);
						if (!empty($pmeta)) {
							foreach ($pmeta as $file_id) {
								//check if this file is used for another post
								$fargs = array(
									'meta_query' => array(
										array(
											'key' => $myfield,
											'value' => $file_id,
											'compare' => '=',
										)
									) ,
									'fields' => 'ids',
									'post_type' => $post_type,
									'posts_per_page' => - 1,
								);
								$fquery = new WP_Query($fargs);
								if (empty($fquery->posts)) {
									wp_delete_attachment($file_id);
								}
							}
						}
					}
				}
			}
			return true;
		}
		public function tinymce_fix($init) {
			global $post;
			$ent_list = get_option($this->option_name . '_ent_list', Array());
			if (!empty($post) && in_array($post->post_type, array_keys($ent_list))) {
				$init['wpautop'] = false;
				$init['indent'] = true;
			}
			return $init;
		}
	}
endif;
return new Request_A_Quote_Install_Deactivate();
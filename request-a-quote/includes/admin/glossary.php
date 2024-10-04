<?php
/**
 * Settings Glossary Functions
 *
 * @package REQUEST_A_QUOTE
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('request_a_quote_settings_glossary', 'request_a_quote_settings_glossary');
/**
 * Display glossary information
 * @since WPAS 4.0
 *
 * @return html
 */
function request_a_quote_settings_glossary() {
	global $title;
?>
<div class="wrap">
<h2><?php echo esc_html($title); ?></h2>
<p><?php esc_html_e('Request a quote provides an easy to use request a quote form, stores and displays quote requests from customers.', 'request-a-quote'); ?></p>
<p><?php esc_html_e('The below are the definitions of entities, attributes, and terms included in Request a quote.', 'request-a-quote'); ?></p>
<div id="glossary" class="accordion-container">
<ul class="outer-border">
<li id="emd_quote" class="control-section accordion-section open">
<h3 class="accordion-section-title hndle" tabindex="1"><?php esc_html_e('Quotes', 'request-a-quote'); ?></h3>
<div class="accordion-section-content">
<div class="inside">
<table class="form-table"><p class"lead"><?php esc_html_e('A sales quote allows a prospective buyer to see what costs would be involved for the work they would like to have done.', 'request-a-quote'); ?></p><tr><th style='font-size: 1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php esc_html_e('Attributes', 'request-a-quote'); ?></div></th></tr>
<tr>
<th><?php esc_html_e('First Name', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' First Name is a required field. First Name is filterable in the admin area. First Name does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Last Name', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Last Name is a required field. Last Name is filterable in the admin area. Last Name does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Address', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Address does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('City', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' City is filterable in the admin area. City does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Zipcode', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Zipcode is filterable in the admin area. Zipcode does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Email', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Email is a required field. Email is filterable in the admin area. Email does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Phone', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Phone is filterable in the admin area. Phone does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Contact Preference', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Contact Preference is a required field. Contact Preference is filterable in the admin area. Contact Preference has a default value of <b>\'Email\'</b>.', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Callback Time', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Callback Time is filterable in the admin area. Callback Time does not have a default value. Callback Time is displayed as a dropdown and has predefined values of: em, lm, ea, la, ee, le.', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Budget', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Budget is filterable in the admin area. Budget does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Additional Details', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Additional Details does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('ID', 'request-a-quote'); ?></th>
<td><?php esc_html_e('Unique identifier for a quote request. Being a unique identifier, it uniquely distinguishes each instance of Quote entity. ID does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Attachments', 'request-a-quote'); ?></th>
<td><?php esc_html_e('Attach related files to the quote. Attachments does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Form Name', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Form Name is filterable in the admin area. Form Name has a default value of <b>admin</b>.', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Form Submitted By', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Form Submitted By is filterable in the admin area. Form Submitted By does not have a default value. ', 'request-a-quote'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Form Submitted IP', 'request-a-quote'); ?></th>
<td><?php esc_html_e(' Form Submitted IP is filterable in the admin area. Form Submitted IP does not have a default value. ', 'request-a-quote'); ?></td>
</tr><tr><th style='font-size:1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php esc_html_e('Taxonomies', 'request-a-quote'); ?></div></th></tr>
<tr>
<th><?php esc_html_e('Service', 'request-a-quote'); ?></th>

<td><?php esc_html_e(' Service accepts multiple values like tags', 'request-a-quote'); ?>. <?php esc_html_e('Service does not have a default value', 'request-a-quote'); ?>.<div class="taxdef-block"><p><?php esc_html_e('The following are the preset values for <b>Service:</b>', 'request-a-quote'); ?></p><p class="taxdef-values"><?php esc_html_e('Service A', 'request-a-quote'); ?>, <?php esc_html_e(' Service B', 'request-a-quote'); ?>, <?php esc_html_e('Service C', 'request-a-quote'); ?></p></div></td>
</tr>

<tr>
<th><?php esc_html_e('Quote Status', 'request-a-quote'); ?></th>

<td><?php esc_html_e(' Quote Status accepts multiple values like tags', 'request-a-quote'); ?>. <?php esc_html_e('Quote Status has a default value of:', 'request-a-quote'); ?> <?php esc_html_e(' pending', 'request-a-quote'); ?>. <div class="taxdef-block"><p><?php esc_html_e('The following are the preset values for <b>Quote Status:</b>', 'request-a-quote'); ?></p><p class="taxdef-values"><?php esc_html_e('Pending', 'request-a-quote'); ?>, <?php esc_html_e('Cancelled', 'request-a-quote'); ?>, <?php esc_html_e('Expired', 'request-a-quote'); ?>, <?php esc_html_e('Accepted', 'request-a-quote'); ?>, <?php esc_html_e('Declined', 'request-a-quote'); ?></p></div></td>
</tr>
</table>
</div>
</div>
</li>
</ul>
</div>
</div>
<?php
}
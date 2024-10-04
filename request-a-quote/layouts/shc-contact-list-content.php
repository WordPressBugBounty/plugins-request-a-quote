<?php if (!defined('ABSPATH')) exit;
global $contact_list_count, $contact_list_filter, $contact_list_set_list;
$real_post = $post;
$ent_attrs = get_option('request_a_quote_attr_list');
?>
<tr>
<?php if (empty(emd_mb_meta('emd_contact_id'))) {
	$quote_id = get_the_ID();
} else {
	$quote_id = emd_mb_meta('emd_contact_id');
}
?>
<td><a title="<?php echo esc_html(emd_mb_meta('emd_contact_first_name')); ?>
 <?php echo esc_html(emd_mb_meta('emd_contact_last_name')); ?>
 msg#:<?php echo $quote_id; ?>" href="<?php echo esc_url(get_permalink()); ?>">
<?php echo $quote_id; ?>
</a></td>
<?php if (emd_is_item_visible('ent_contact_first_name', 'request_a_quote', 'attribute', 1)) { ?>
<td><?php echo esc_html(emd_mb_meta('emd_contact_first_name')); ?>
</td>
<?php
} ?>
<?php if (emd_is_item_visible('ent_contact_last_name', 'request_a_quote', 'attribute', 1)) { ?>
<td><?php echo esc_html(emd_mb_meta('emd_contact_last_name')); ?>
</td>
<?php
} ?>
<?php if (emd_is_item_visible('ent_contact_email', 'request_a_quote', 'attribute', 1)) { ?>
<td><?php echo esc_html(emd_mb_meta('emd_contact_email')); ?>
</td>
<?php
} ?>
<?php if (emd_is_item_visible('ent_contact_phone', 'request_a_quote', 'attribute', 1)) { ?>
<td><?php echo esc_html(emd_mb_meta('emd_contact_phone')); ?>
</td>
<?php
} ?>
<?php if (emd_is_item_visible('ent_contact_pref', 'request_a_quote', 'attribute', 1)) { ?>
<td><?php echo emd_get_attr_val('request_a_quote', $post->ID, 'emd_quote', 'emd_contact_pref'); ?></td>
<?php
} ?>
<td><?php echo get_the_date(); ?></td>
</tr>
<?php if (!defined('ABSPATH')) exit;
global $contact_list_shc_count; ?><table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <?php if (emd_is_item_visible('ent_contact_first_name', 'request_a_quote', 'attribute', 1)) { ?>

            <th>First Name</th>
            <?php
} ?>
<?php if (emd_is_item_visible('ent_contact_last_name', 'request_a_quote', 'attribute', 1)) { ?>

            <th>Last Name</th>
            <?php
} ?>
<?php if (emd_is_item_visible('ent_contact_email', 'request_a_quote', 'attribute', 1)) { ?>

            <th>Email</th>
            <?php
} ?>
<?php if (emd_is_item_visible('ent_contact_phone', 'request_a_quote', 'attribute', 1)) { ?>

            <th>Phone</th>
            <?php
} ?>
<?php if (emd_is_item_visible('ent_contact_pref', 'request_a_quote', 'attribute', 1)) { ?>

            <th>Contact Preference</th>
            <?php
} ?>

            <th>Date</th>
        </tr>
    </thead>
    <tbody>
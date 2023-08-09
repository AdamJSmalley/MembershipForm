<?php
// Handle the form submission
add_action('wp_ajax_membership_form_plugin', 'membership_form_plugin_handler');
add_action('wp_ajax_nopriv_membership_form_plugin', 'membership_form_plugin_handler');

function membership_form_plugin_handler()
{

    global $wpdb;

    // Fetch fields from the database
    $fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}membership_form_fields", ARRAY_A);

    // Check nonce for security
    check_ajax_referer('membership_form_plugin', 'nonce');

    // Prepare user data, validate and sanitize form data
    $userdata = array();
    foreach ($fields as $field) {
        if (isset($_POST[$field['name']])) {
            $validate = '/' . $field['validate'] . '/';
            $check = preg_match($validate, $_POST[$field['name']]);
            if (!preg_match($validate, $_POST[$field['name']])) {
                wp_send_json_error('Invalid value for ' . $field['label'] . 'u' . $_POST[$field['name']] . 'u' . ' ' . $validate . ' ' . $check);
            }
            $userdata[$field['name']] = sanitize_text_field($_POST[$field['name']]);
        }
    }

    // Ensure required WP fields are present
    if (!isset($userdata['user_email'])) {
        wp_send_json_error('Email is required.' . json_encode($userdata));
    }



    //create a username from the email address
    $userdata['user_login'] = explode('@', $userdata['user_email'])[0];

    // Randomly generate a password
    $userdata['user_pass'] = wp_generate_password();

    // Create a new user
    $user_id = wp_insert_user($userdata);

    // Handle errors
    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
    }

    // Update custom user meta fields
    foreach ($userdata as $key => $value) {
        // If this isn't a default WP user column, save it in usermeta
        if (!in_array($key, array('user_login', 'user_pass', 'user_email'))) {
            update_user_meta($user_id, $key, $value);
        }
    }

    // Send a response
    wp_send_json_success('You are now a member.');
}
?>
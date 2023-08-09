<?php
function custom_user_profile_fields($user) {
    global $wpdb;
    
    // Fetch fields from the database
    $fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}membership_form_fields WHERE name != 'user_email'", ARRAY_A);
    ?>
    <h3>Member Information</h3>
    <table class="form-table">
        <?php foreach ($fields as $field): ?>
            <tr>
                <th><label for="<?php echo $field['name']; ?>"><?php echo ucfirst($field['name']); ?></label></th>
                <td>
                    <input type="text" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo esc_attr(get_the_author_meta($field['name'], $user->ID)); ?>" class="regular-text" data-validate="<?php echo $field['validate']; ?>" /><br />
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}

add_action('show_user_profile', 'custom_user_profile_fields');
add_action('edit_user_profile', 'custom_user_profile_fields');

function save_custom_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    global $wpdb;
    // Fetch fields from the database
    $fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}membership_form_fields", ARRAY_A);
    
    foreach ($fields as $field) {
        if (isset($_POST[$field['name']]) && preg_match($field['validate'], $_POST[$field['name']])) {
            update_user_meta($user_id, $field['name'], $_POST[$field['name']]);
        }
    }
}

add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');
?>
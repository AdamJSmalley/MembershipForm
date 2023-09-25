<?php
function custom_user_profile_fields($user)
{
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
                    <input type="text" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>"
                        value="<?php echo esc_attr(get_the_author_meta($field['name'], $user->ID)); ?>" class="regular-text"
                        data-validate="<?php echo $field['validate']; ?>" /><br />
                </td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <th><label for="id_validated">ID Validated</label></th>
            <td><input type="checkbox" name="id_validated" id="id_validated" <?php  if(get_the_author_meta("id_validated", $user->ID)) echo "checked"; ?> value="1"></td>
        </tr>
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
        if (isset($_POST[$field['name']])) {
            $result = preg_match('/' . $field['validate'] . '/', $_POST[$field['name']]);
            if ($result === false) {
                // Error in preg_match
                add_action('admin_notices', function() use ($field) {
                    echo '<div class="error"><p>Error in validation for field ' . $field['name'] . '.</p></div>';
                });
                continue;
            } elseif ($result === 0) {
                // Pattern did not match
                error_log('Invalid value for field ' . $field['name'] . '.');
                add_action('admin_notices', function() use ($field) {
                    echo '<div class="error"><p>Invalid value for field ' . $field['name'] . '.</p></div>';
                });
                continue;
            }
            update_user_meta($user_id, $field['name'], $_POST[$field['name']]);
        }
    }

    // Handling the "ID Validated" checkbox
    $id_validated_value = isset($_POST['id_validated']) ? 1 : 0;
    update_user_meta($user_id, 'id_validated', $id_validated_value);
}

add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');
?>
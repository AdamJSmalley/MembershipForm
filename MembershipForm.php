<?php
/*
Plugin Name: Membership Form Plugin
Description: Allows people to sign up for a membership
Version: 1.0
Author: Adam Smalley
*/

// Function to generate the form
function membership_form_plugin_form()
{
    global $wpdb;

    // Start output buffering
    ob_start();

    // Fetch fields from the database
    $fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}membership_form_fields", ARRAY_A);

    ?>
    <form id="membership_form" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
        <input type="hidden" name="action" value="membership_form_plugin">
        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('membership_form_plugin')); ?>">
        <?php foreach ($fields as $field): ?>
            <div class="membership_form-field">
                <label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label>
                <?php if ($field['type'] === 'select'): ?>
                    <select id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>"
                        data-validate="<?php echo $field['validate']; ?>" data-label="<?php echo $field['label']; ?>">
                        <?php foreach (explode(',', $field['field_values']) as $option): ?>
                            <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="<?php echo $field['type']; ?>" id="<?php echo $field['name']; ?>"
                        name="<?php echo $field['name']; ?>" data-validate="<?php echo $field['validate']; ?>"
                        data-label="<?php echo $field['label']; ?>">
                <?php endif; ?>
                <div class="membership_form-error"></div>
            </div>
        <?php endforeach; ?>
        <button type="submit">Submit</button>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get form elements
            var form = document.getElementById('membership_form');
            var inputs = form.querySelectorAll('input:not([type="hidden"]), select');

            // Add blur event listeners to validate input fields
            inputs.forEach(function (input, index) {
                input.addEventListener('blur', function () {
                    var error = '';

                    // Validate input fields
                    if (new RegExp(this.dataset.validate).test(this.value)) {
                        error = '';
                    } else {
                        error = `Invalid ${this.dataset.label}`;
                    }

                    // Display error message
                    var correspondingErrorDiv = this.parentNode.querySelector('.membership_form-error');
                    correspondingErrorDiv.innerText = error;

                    // Change field and label color based on validation
                    this.style.color = error ? 'red' : '';
                    
                    if (!this.previousElementSibling) {
                        console.log('No previous sibling for:', this);
                    }
                    this.previousElementSibling.style.color = error ? 'red' : '';
                });
            });

            // Add submit event listener to handle form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Validate all input fields
                inputs.forEach(function (input) {
                    var event = new Event('blur');
                    input.dispatchEvent(event);
                });

                // Check if there are any validation errors
                var errorDivs = form.querySelectorAll('.membership_form-error');
                console.log(JSON.stringify(errorDivs));
                if (Array.from(errorDivs).some(function (div) { return div.innerText; })) {
                    return;
                }

                // Submit form via AJAX
                var xhr = new XMLHttpRequest();
                console.log(this.action);
                xhr.open('POST', this.getAttribute('action'), true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (this.status >= 200 && this.status < 400) {
                        var response = JSON.parse(this.response);
                        if (response.success) {
                            console.log("Success");
                            form.outerHTML = '<p>' + response.data + '</p>';
                        } else {
                            console.log("Failure");
                            alert(response.data);
                        }
                    }
                };
                xhr.send(new URLSearchParams(new FormData(form)).toString());
            });
        });
    </script>
    <?php

    $output = ob_get_clean();
    return $output;
}
add_shortcode('membership_form_plugin', 'membership_form_plugin_form'); // This allows you to place the form anywhere using the [membership_form_plugin] shortcode

// Include the form handler and user page modifications
include_once plugin_dir_path(__FILE__) . 'form-handler.php';
include_once plugin_dir_path(__FILE__) . 'user-profile-fields.php';
?>
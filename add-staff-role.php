<?php
add_action('init', 'add_staff_role');
function add_staff_role() {
  add_role(
    'staff',
    'Staff',
    array(
      'read'         => true,  // Basic capability to access the dashboard
      'list_users'   => true,  // Allows listing of users
      'edit_users'   => true,  // Allows editing of users
      'create_users' => false, // If you want to prevent user creation
      // Add any other capabilities you want for this role
    )
  );
}
?>
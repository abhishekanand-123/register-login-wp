<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://avologypro.com/
 * @since             1.0.0
 * @package           Login_Registration
 *
 * @wordpress-plugin
 * Plugin Name:       login-registration
 * Plugin URI:        https://http://login-registration.com
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Abhishek Anand
 * Author URI:        https://https://avologypro.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       login-registration
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LOGIN_REGISTRATION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-login-registration-activator.php
 */
function activate_login_registration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-login-registration-activator.php';
	Login_Registration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-login-registration-deactivator.php
 */
function deactivate_login_registration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-login-registration-deactivator.php';
	Login_Registration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_login_registration' );
register_deactivation_hook( __FILE__, 'deactivate_login_registration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-login-registration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_login_registration() {

	$plugin = new Login_Registration();
	$plugin->run();

}
run_login_registration();



add_action('admin_menu', 'my_custom_menu_setup');

function my_custom_menu_setup() {


    
    // Add a top-level menu item
    add_menu_page(
        NULL, // Page title
        'Login-Registration', // Menu title (empty string)
        'manage_options', // Capability
        'custom-menu-slug', // Menu slug
        'my_custom_menu_page', // Callback function
        'dashicons-admin-generic', // Icon URL
        6 // Position
    );

    // Add sub-menu items under the top-level menu
    add_submenu_page(
        'custom-menu-slug', // Parent slug
        'Registration', // Page title
        'Registration', // Menu title
        'manage_options', // Capability
        'registration-submenu-slug', // Menu slug
        'my_registration_submenu_page' // Callback function
    );

    add_submenu_page(
        'custom-menu-slug', // Parent slug
        'Login', // Page title
        'Login', // Menu title
        'manage_options', // Capability
        'login-submenu-slug', // Menu slug
        'my_login_submenu_page' // Callback function
    );
}

function my_custom_menu_page() {
    echo '<h1>Custom Menu Page</h1>';
    echo '<p>Welcome to the custom menu page.</p>';
}

function my_registration_submenu_page() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
        $errors = array();

        // Sanitize and validate input fields
        $first_name = sanitize_text_field($_POST['first_name']);
        if (strlen($first_name) < 4 || !ctype_alpha($first_name)) {
            $errors[] = 'First name must be at least 4 alphabetic characters.';
        }

        $last_name = sanitize_text_field($_POST['last_name']);
        if (strlen($last_name) < 4 || !ctype_alpha($last_name)) {
            $errors[] = 'Last name must be at least 4 alphabetic characters.';
        }

        $mobile_no = sanitize_text_field($_POST['mobile_no']);
        if (!preg_match('/^\d{10}$/', $mobile_no)) {
            $errors[] = 'Mobile number must be 10 digits.';
        }

        $email = sanitize_email($_POST['email']);
        if (!is_email($email)) {
            $errors[] = 'Invalid email address.';
        }

        $password = sanitize_text_field($_POST['password']);
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if (empty($errors)) {
            // Store user data
            $user_data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'mobile_no' => $mobile_no,
                'email' => $email,
                'password' => wp_hash_password($password)
            );

            // Optionally, you can store the user data in the database
            update_option('custom_registration_user', $user_data);
            echo '<p>Registration successful!</p>';
        } else {
            foreach ($errors as $error) {
                echo '<p style="color: red;">' . $error . '</p>';
            }
        }
    }

    echo '<h1>Registration Page</h1>';
    echo '<form method="POST">';
    echo '<p><label for="first_name">First Name: </label><input type="text" name="first_name" required></p>';
    echo '<p><label for="last_name">Last Name: </label><input type="text" name="last_name" required></p>';
    echo '<p><label for="mobile_no">Mobile No: </label><input type="text" name="mobile_no" required></p>';
    echo '<p><label for="email">Email: </label><input type="email" name="email" required></p>';
    echo '<p><label for="password">Password: </label><input type="password" name="password" required></p>';
    echo '<p><input type="submit" name="register" value="Register"></p>';
    echo '</form>';
}

function my_login_submenu_page() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        $user_data = get_option('custom_registration_user');

        if ($user_data && $user_data['email'] === $email && wp_check_password($password, $user_data['password'])) {
            // If login is successful, display user data and edit button
            display_user_data($user_data);
            echo '<form method="POST">';
            echo '<input type="hidden" name="email" value="' . esc_attr($user_data['email']) . '">';
            echo '<input type="hidden" name="action" value="edit">';
            echo '<input type="submit" name="edit" value="Edit">';
            echo '</form>';
        } else {
            echo '<p style="color: red;">Invalid email or password.</p>';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
        // If edit action is triggered, display registration form with pre-filled data
        display_registration_form($_POST['email']);
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
        // If update action is triggered, update user data and display updated information
        update_user_data($_POST);
    } else {
        // Display login form
        echo '<h1>Login Page</h1>';
        echo '<form method="POST">';
        echo '<p><label for="email">Email: </label><input type="email" name="email" required></p>';
        echo '<p><label for="password">Password: </label><input type="password" name="password" required></p>';
        echo '<p><input type="submit" name="login" value="Login"></p>';
        echo '</form>';
    }
}

function display_user_data($user_data) {
    echo '<p>Login successful!</p>';
    echo '<h2>User Information</h2>';
    echo '<style>
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
          </style>';
    echo '<table>';
    echo '<tr><th>First Name</th><td>' . esc_html($user_data['first_name']) . '</td></tr>';
    echo '<tr><th>Last Name</th><td>' . esc_html($user_data['last_name']) . '</td></tr>';
    echo '<tr><th>Mobile No</th><td>' . esc_html($user_data['mobile_no']) . '</td></tr>';
    echo '<tr><th>Email</th><td>' . esc_html($user_data['email']) . '</td></tr>';
    echo '</table>';
}

function display_registration_form($email) {
    $user_data = get_option('custom_registration_user');
    echo '<h1>Edit User Information</h1>';
    echo '<form method="POST">';
    echo '<input type="hidden" name="email" value="' . esc_attr($email) . '">';
    echo '<p><label for="first_name">First Name: </label><input type="text" name="first_name" value="' . esc_attr($user_data['first_name']) . '" required></p>';
    echo '<p><label for="last_name">Last Name: </label><input type="text" name="last_name" value="' . esc_attr($user_data['last_name']) . '" required></p>';
    echo '<p><label for="mobile_no">Mobile No: </label><input type="text" name="mobile_no" value="' . esc_attr($user_data['mobile_no']) . '" required></p>';
    echo '<p><input type="submit" name="action" value="update"></p>';
    echo '</form>';
}

function update_user_data($data) {
    $email = sanitize_email($data['email']);
    $first_name = sanitize_text_field($data['first_name']);
    $last_name = sanitize_text_field($data['last_name']);
    $mobile_no = sanitize_text_field($data['mobile_no']);

    $user_data = array(
        'email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'mobile_no' => $mobile_no
    );

    update_option('custom_registration_user', $user_data);
    echo '<p>User information updated successfully!</p>';
    display_user_data($user_data);
}

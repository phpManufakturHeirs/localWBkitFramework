<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

require_once '../../../../../../../config.php';

/**
 * Authenticate the user with the given username and given MD5 password
 *
 * @param string $username
 * @param string $password MD5 checksum of the password
 * @return username if success
 */
function authenticate_wb_user($username, $password)
{
    global $database;

    $query = sprintf("SELECT * FROM %susers WHERE username='%s' AND password='%s' AND active = '1'", TABLE_PREFIX, $username, $password);
    $results = $database->query($query);
    if ($database->is_error()) {
        return $database->get_error();
    }

    $results_array = $results->fetchRow();

    $num_rows = $results->numRows();
    if ($num_rows) {
        $user_id = $results_array['user_id'];
        $_SESSION['USER_ID'] = $user_id;
        $_SESSION['GROUP_ID'] = $results_array['group_id'];
        $_SESSION['GROUPS_ID'] = $results_array['groups_id'];
        $_SESSION['USERNAME'] = $results_array['username'];
        $_SESSION['DISPLAY_NAME'] = $results_array['display_name'];
        $_SESSION['EMAIL'] = $results_array['email'];
        $_SESSION['HOME_FOLDER'] = $results_array['home_folder'];

        // Set language
        if ($results_array['language'] != '') {
            $_SESSION['LANGUAGE'] = $results_array['language'];
        }
        // Set timezone
        if ($results_array['timezone'] != '-72000') {
            $_SESSION['TIMEZONE'] = $results_array['timezone'];
        }
        else {
            // Set a session var so apps can tell user is using default tz
            $_SESSION['USE_DEFAULT_TIMEZONE'] = true;
        }
        // Set date format
        if ($results_array['date_format'] != '') {
            $_SESSION['DATE_FORMAT'] = $results_array['date_format'];
        }
        else {
            // Set a session var so apps can tell user is using default date
            // format
            $_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
        }
        // Set time format
        if ($results_array['time_format'] != '') {
            $_SESSION['TIME_FORMAT'] = $results_array['time_format'];
        }
        else {
            // Set a session var so apps can tell user is using default time
            // format
            $_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
        }
        $_SESSION['SYSTEM_PERMISSIONS'] = array();
        $_SESSION['MODULE_PERMISSIONS'] = array();
        $_SESSION['TEMPLATE_PERMISSIONS'] = array();
        $_SESSION['GROUP_NAME'] = array();

        $first_group = true;
        foreach (explode(",", $_SESSION['GROUPS_ID']) as $cur_group_id) {
            $query = sprintf("SELECT * FROM %sgroups WHERE group_id='%s'", TABLE_PREFIX, $cur_group_id);
            $results = $database->query($query);
            $results_array = $results->fetchRow();
            $_SESSION['GROUP_NAME'][$cur_group_id] = $results_array['name'];
            // Set system permissions
            if ($results_array['system_permissions'] != '') {
                $_SESSION['SYSTEM_PERMISSIONS'] = array_merge($_SESSION['SYSTEM_PERMISSIONS'], explode(',', $results_array['system_permissions']));
            }
            // Set module permissions
            if ($results_array['module_permissions'] != '') {
                if ($first_group) {
                    $_SESSION['MODULE_PERMISSIONS'] = explode(',', $results_array['module_permissions']);
                }
                else {
                    $_SESSION['MODULE_PERMISSIONS'] = array_intersect($_SESSION['MODULE_PERMISSIONS'], explode(',', $results_array['module_permissions']));
                }
            }
            // Set template permissions
            if ($results_array['template_permissions'] != '') {
                if ($first_group) {
                    $_SESSION['TEMPLATE_PERMISSIONS'] = explode(',', $results_array['template_permissions']);
                }
                else {
                    $_SESSION['TEMPLATE_PERMISSIONS'] = array_intersect($_SESSION['TEMPLATE_PERMISSIONS'], explode(',', $results_array['template_permissions']));
                }
            }
            $first_group = false;
        }
        // Update the users table with current ip and timestamp
        $get_ts = time();
        $get_ip = $_SERVER['REMOTE_ADDR'];
        $query = sprintf("UPDATE %susers SET login_when= '%s', login_ip='%s' WHERE user_id='%s'", TABLE_PREFIX, $get_ts, $get_ip, $user_id);
        $database->query($query);
    }
    // Return if the user exists or not

    return $num_rows;
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    exit(0);
}
if (authenticate_wb_user($_POST['username'], $_POST['password'])) {
    exit($_POST['username']);
}
else {
    exit(0);
}

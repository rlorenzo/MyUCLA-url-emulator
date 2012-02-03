<?php
/*
 * Configuration file for MyUCLA updater.
 * 
 */

define('STATUS_SUCCESS', 'Status:  Update Successful.');
define('STATUS_CONNECT_ERROR', 'Status:  Unable to Connect to SQL Servers!');
define('STATUS_ACCESS_DENIED', 'Status:  Unauthorized Access!');
define('STATUS_UPDATE_FAILED', 'Status:  Status:  Update Unsuccessful. SQL Update Failed.');
define('STATUS_INVALID_COURSE', 'Status:  Update Unsuccessful. Invalid Course.');

define('MYSQL_URL_VIEW', 1);
define('MYSQL_URL_EDIT', 2);

$dsn = array(
    'phptype'  => 'mysqli',
    'username' => '',   // user account that can read/edit mysql_updater
    'password' => '',
    'hostspec' => 'localhost',
    'database' => 'mysql_updater',
);

$test_server_url = '';  // must be set if you wish to run the PHPunit test suite
?>

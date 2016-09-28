<?php
/**
 * update.php
 *
 * When someone is making an update it expects the following GET parameters:
 * 
 * name - name of person making update
 * email - email of person making update
 * term - course term
 * srs - course srs
 * url - course url, expects no http:// or https://
 * 
 * Returns an html document with one of the following messages:
 *
 * Status:  Update Successful.
 * Status:  Unable to Connect to SQL Servers!
 * Status:  Unauthorized Access!
 * Status:  Update Unsuccessful. SQL Update Failed. 
 * Status:  Update Unsuccessful. Invalid Course.
 * 
 * When someone wants to find the url for a given course it expects the 
 * following GET parameters:
 * 
 * term - course term
 * srs - course srs
 * 
 * Returns url back (will not have http:// or https://)
 */
require_once(dirname(__FILE__) . '/config.php');

// only respond if there is a GET parameter
if (!empty($_GET)) {
    $params = setup_script();   // dies on error
    // connect to database
    $mysqli = new mysqli($dsn['hostspec'], $dsn['username'], $dsn['password'], $dsn['database']);
    if ($mysqli->connect_errno) {
        print_status(STATUS_CONNECT_ERROR);
    }

    if (MYUCLA_URL_VIEW == $params['mode']) {
        unset($params['mode']); // mode isn't one of the binded parameters
        // query and return url for given term/srs
        $sql = 'SELECT  url
                FROM    iei_urls
                WHERE   term=? AND
                        srs=?';
        $statement = $mysqli->prepare($sql);
        $statement->bind_param('ss', $params['term'], $params['srs']);
        $statement->execute();
        $statement->bind_result($url);
        $statement->fetch();

        echo $url;   // output URL
    } elseif (MYUCLA_URL_EDIT == $params['mode']) {
        unset($params['mode']); // mode isn't one of the binded parameters
        $sql = 'INSERT INTO iei_urls
                SET         term=?,
                            srs=?,
                            url=?,
                            name=?,
                            email=?
                ON DUPLICATE KEY UPDATE
                            url=?,
                            name=?,
                            email=?';
        $statement = $mysqli->prepare($sql);
        $statement->bind_param('ssssssss', $params['term'],
            $params['srs'], $params['url'], $params['name'], $params['email'],
            $params['url'], $params['name'], $params['email']);
        $result = $statement->execute();

        if (empty($result)) {            
            print_status(STATUS_UPDATE_FAILED);
        } else {
            print_status(STATUS_SUCCESS);
        }
    }
}

/** SCRIPT FUNCTIONS * */

/**
 * Displays given status message.
 * 
 * @param string $status_message 
 */
function print_status($status_message) {
    echo sprintf("<HTML>\n<BODY>\n$status_message\n</BODY>\n</HTML>", $status_message);
}

/**
 * Checks the GET parameters and returns a cleaned parameter array that also 
 * indicates what the user is trying to do. 
 * 
 * @return array    Returns an array with the following keys: term, srs, name,
 *                  email, url (decoded), mode (MYUCLA_URL_VIEW, MYUCLA_URL_EDIT)
 */
function setup_script() {
    $params = array();

    // first make sure that term/srs are valid and present
    if (!preg_match('/^[0-9]{2}[FWS1]$/', $_GET['term']) ||
            !preg_match('/^[0-9]{9}$/', $_GET['srs'])) {
        // if trying to update a url, then give error
        if (isset($_GET['url'])) {
            die(print_status(STATUS_INVALID_COURSE));
        } else {
            // else just return blank
            die();
        }
    }

    $params['term'] = $_GET['term'];
    $params['srs'] = $_GET['srs'];

    if (isset($_GET['url'])) {  // allow empty url so that you can clear the URL
        // user wants to update an url (don't do any real validate on url,
        // because the MyUCLA service doesn't seem to)
        $params['url'] = trim(urldecode($_GET['url']));

        // see if there is a name or email, but they aren't required
        $params['name'] = trim($_GET['name']);
        $params['email'] = trim($_GET['email']);

        // set correct mode
        $params['mode'] = MYUCLA_URL_EDIT;
    } else {
        // user wants to view url for a given term/srs
        $params['mode'] = MYUCLA_URL_VIEW;
    }

    return $params;
}

/**
 * @param string $type   
 *      Type can be 'term', 'srs', 'uid'
 * @param mixed $value   
 *      term: DDC (two digit number with C being either F, W, S, 1)
 *      SRS/UID: (9 digit number, can have leading zeroes)
 * @return boolean      true if the value matches the type, false otherwise.
 * @throws moodle_exception When the input type is invalid.
 */
function ucla_validator($type, $value) {
    $result = 0;

    switch ($type) {
        case 'term':
            $result = preg_match('/^[0-9]{2}[FWS1]$/', $value);
            break;
        case 'srs':
        case 'uid':
            $result = preg_match('/^[0-9]{9}$/', $value);
            break;
        default:
            throw new moodle_exception('invalid type', 'ucla_validator');
            break;
    }

    return $result == 1;
}

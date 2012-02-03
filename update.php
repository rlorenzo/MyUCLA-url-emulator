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
require_once 'MDB2.php';

// only respond if there is a GET parameter
if (!empty($_GET)) {
    $params = setup_script();   // dies on error
 
    // connect to database
    $mdb2 =& MDB2::connect($dsn);
    if (PEAR::isError($mdb2)) {
        print_status(STATUS_CONNECT_ERROR);
    }
    $mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);    
    
    if (MYUCLA_URL_VIEW == $params['mode']) {
        // query and return url for given term/srs
        $sql = 'SELECT  url
                FROM    iei_urls
                WHERE   term=:term AND 
                        srs=:srs';
        $statement = $mdb2->prepare($sql);
        $result = $statement->execute($params);

        if (PEAR::isError($result)) {
            print_status(STATUS_CONNECT_ERROR);
        }        
        
        echo $result->fetchRow();   // output URL
        
    } elseif (MYUCLA_URL_EDIT == $params['mode']) {
        $sql = 'INSERT  iei_urls
                SET     term=:term,
                        srs=:srs,
                        url=:url,
                        name=:name,
                        email=:email
                ON DUPLICATE KEY UPDATE
                        url=:url,
                        name=:name,
                        email=:email';
        $statement = $mdb2->prepare($sql);
        $result = $statement->execute($params);
        
        if (PEAR::isError($result)) {
            print_status(STATUS_UPDATE_FAILED);
        } else {
            print_status(STATUS_SUCCESS);
        }
    }    
}

/** SCRIPT FUNCTIONS **/
/**
 * Displays given status message.
 * 
 * @param string $status_message 
 */
function print_status($status_message) 
{
    echo sprintf("<HTML>\n<BODY>\n$status_message\n</BODY>\n</HTML>", $status_message);
}

/**
 * Checks the GET parameters and returns a cleaned parameter array that also 
 * indicates what the user is trying to do. 
 * 
 * @return array    Returns an array with the following keys: term, srs, name,
 *                  email, url (decoded), mode (MYUCLA_URL_VIEW, MYUCLA_URL_EDIT)
 */
function setup_script() 
{
    $params = array();
    
    // first make sure that term/srs are valid and present
    if (!preg_match('/^[0-9]{2}[FWS1]$/', $_GET['term']) || 
            !preg_match('/^[0-9]{9}$/', $_GET['srs'])) {
        die(print_status(STATUS_INVALID_COURSE));
    }
    
    $params['term'] = $_GET['term'];
    $params['srs'] = $_GET['srs'];
    
    if (!empty($_GET['url'])) {
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
    
    switch($type) {
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

?>

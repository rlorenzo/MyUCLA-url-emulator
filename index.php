<?php
/*
 * index.php
 *
 * Displays the 50 most recent url updates.
 */

require_once(dirname(__FILE__) . '/config.php');
require_once 'MDB2.php';

// connect to database
$mdb2 = MDB2::connect($dsn);
if (PEAR::isError($mdb2)) {
    print_status(STATUS_CONNECT_ERROR);
}
$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

$sql = "SELECT      term,
                    srs,
                    url,
                    name,
                    email,
                    updated_on
        FROM        iei_urls
        WHERE       1
        ORDER BY    updated_on DESC
        LIMIT       100";
$records =& $mdb2->query($sql);

$num_rows = $records->numRows();
if (empty($num_rows)) {
    die('No records found');
}

// display results in a table
echo "<table border='1'>";

$header = array('term', 'srs', 'url', 'name', 'email', 'updated_on');
echo '<thead><tr>';
foreach ($header as $head) {
    echo '<th>' . $head . '</th>';
}
echo '</tr></thead>';

echo '<tbody>';
while (($record = $records->fetchRow())) {
    echo '<tr>';
    foreach ($header as $head) {
        echo '<td>' . $record[$head] . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
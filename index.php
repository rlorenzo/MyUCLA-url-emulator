<?php
/*
 * index.php
 *
 * Displays the 50 most recent url updates.
 */

require_once(dirname(__FILE__) . '/config.php');

// connect to database
$mysqli = new mysqli($dsn['hostspec'], $dsn['username'], $dsn['password'], $dsn['database']);
if ($mysqli->connect_errno) {
    print_status(STATUS_CONNECT_ERROR);
}

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
$records = $mysqli->query($sql);

$numrows = $records->num_rows;
if (empty($numrows)) {
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
while (($record = $records->fetch_assoc())) {
    echo '<tr>';
    foreach ($header as $head) {
        echo '<td>' . htmlentities($record[$head]) . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';

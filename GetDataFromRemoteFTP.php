<?php
require_once 'functions_ftp.php';

$start_time = time(); // start timer
// get ftp connection
create_ftp_conn();
// get array :: list of content from dir "regions"
$DIR_REGIONS = "fcs_regions/"; // constanta path to dir "regions" on ftp server
$list_all = ftp_nlist($ftp_conn, $DIR_REGIONS); // get ist of content from dir "regions"

// we need list of folders - region. array [region => folder, ... , ]
$list_regions = array_slice($list_all, 0, 87); // !!! need 87
$size = count($list_regions);

$end_time = time(); // kill timer
$time = $end_time - $start_time; // count execution time
// display execution time
echo "execution time" . " " . "$time" . " " . "seconds";
echo "<br>" . "<br>";


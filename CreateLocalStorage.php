<?php
require_once 'GetDataFromRemoteFTP.php';
require_once 'functions_DB.php';
// $end_list (core) - assoc array, with index = title of region.
//creating local folders by index of $end_list
$start_time = time(); // start timer
$time_start_plangraphs = time(); // time counter
foreach ($list_regions as $region) // for every regions folder on ftp, we get items from $DIR_PLANGR
{
    $DIR_PLANGR = "$region/plangraphs2020/currMonth/"; // go to every regions path - /plangraphs2020/currMonth/
    $list_plangr = ftp_nlist($ftp_conn, $DIR_PLANGR); // get list of content from every regions path
//    $end_list = [$end_list_index => $list_plangr]   ; // define array and put result. !!! not work, create one array element, one folder, one DB table
    $end_list_index = str_replace("fcs_regions/", "", $region); // from $region (it is string path to file on FTP server) delete no need part of path
    $end_list["$end_list_index"] = $list_plangr; // add results to array
}

$count_end_list = count($end_list); // number items in array with list off all content in folders "plangraphs2020/currMonth"
var_dump($count_end_list);

// below we get array with only not empty .zip files from all folders   $region/plangraphs2020/currMonth/
foreach ($end_list as $index => $value) {
    foreach ($value as $key => $element) {
        $check_size = ftp_size($ftp_conn, $element);
        if ($check_size === 22 || $check_size === -1) // -1 - folder, 22 - empty zip file.
        {
            unset($end_list[$index][$key]);
        }
    }
}
chdir("ftp_plangr"); // go to parent local folder
foreach ($end_list as $key => $value) {
    mkdir($key); // create local folder
}
// cycle create local files and load files from FTP to local files
foreach ($end_list as $key => $array) // start first level iteration of $end_list
{
    foreach ($array as $index => $file_path) // start second level iteration of $end_list. Get elements (path to FTP file) of second level array.
    {
        $local_file = tempnam("$key", "FTP"); // create local file with unique title.
        $local_file_handle = fopen($local_file, 'w'); // create function handler, which opens local files for write.
        ftp_fget($ftp_conn, $local_file_handle, $file_path, FTP_ASCII, 0); // load file from FTP and save in prev opened file
        rename($local_file, "$local_file.zip"); // add extension .zip for local file
    }
}
$end_time = time(); // kill timer
$time = $end_time - $start_time; // count execution time
// display execution time
echo "execution time" . " " . "$time" . " " . "seconds";
echo "<br>" . "<br>";

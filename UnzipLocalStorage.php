<?php
$start_time = time(); // start timer
$local_storage_zip = "ftp_plangr"; // get var contains local storage dir with loaded dirs and files
$list_local_dirs = scandir($local_storage_zip); // get arr with contains local storage dir
// kill "." and ".." elements in $list_local_dirs
foreach ($list_local_dirs as $index => $value) {
    if ($value === ".." || $value === ".") {
        unset($list_local_dirs[$index]);
    }
}
chdir($local_storage_zip);

$multi_arr_zip_files[] = ""; // define multidimensional array for save list of zip files
foreach ($list_local_dirs as $key => $value) {
    $file = scandir("$value" . "/");
    $multi_arr_zip_files[$value] = $file; // index == region
}
// kill first element in  $multi_arr_zip_files
// because it equal "" and it could not iterate in foreach cycle
foreach ($multi_arr_zip_files as $index => $value) {
    if ($value === "") unset($multi_arr_zip_files[$index]);
}
// kill "." ".." elements in all sub arrays
foreach ($multi_arr_zip_files as $index_reg => $list_files) {
    foreach ($list_files as $index => $value) {
        if ($value === "." || $value === "..") {
            unset($multi_arr_zip_files[$index_reg][$index]);
        }
    }
}
// unzip func. call in unzip core
function unzip($file, $path) // $path maybe CONST
{
    $z = new ZipArchive; // create instance of ZipArchive
    $z->open($file); // open instance of ZipArchive
    // delete .sig files in zip
    for ($i = 0; $i < $z->numFiles; $i++) // numFiles - count files in a zip file
    {
        $file_name = $z->getNameIndex($i); // get file inside zip name
        $file_info = pathinfo($file_name); // get info about file inside zip
        if ($file_info['extension'] === 'sig') // find .sig files inside zip
        {
            $z->deleteName($file_name); // delete .sig file from zip
        }
    }
    $z->extractTo($path); // unzip files
    $z->close(); // close instance of ZipArchive
}
// unzip core
foreach ($multi_arr_zip_files as $reg => $val) {
    if ($reg !== "Adygeja_Resp") { // for all region index except first because compilator stay in first dir
        chdir(".."); // go to ftp_plangr dir
        chdir($reg); // go to region index dir
        foreach ($val as $key => $zip_file)// start sec foreach for unzip
        {            // NEED try catch
            unzip($zip_file, './UNZIP' . "_" . "$reg");// call  function unzip ($file, $path). see above code
        }
    } else // only for first region index
    {
        chdir(".//$reg"); // in first iteration we stay in ftp_plangr and go to first region index
        foreach ($val as $key => $zip_file) // start sec foreach for unzip
        {
            // try catch
            unzip($zip_file, './UNZIP' . "_" . "$reg"); // call  function unzip ($file, $path). see above code
        }
    }
}
$end_time = time(); // kill timer
$time = $end_time - $start_time; // count execution time
// display execution time
echo "execution time" . " " . "$time" . " " . "seconds";
echo "<br>" . "<br>";


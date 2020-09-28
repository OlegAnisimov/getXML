<?php
function count_files($dir)
{
    if ($dir == ".") {
        echo "Numbers of files in  current dir: " . "   \n";
        $cur_dir = getcwd();
        echo $cur_dir;
    } else {
        echo "Numbers of files in  : " . " ";
        echo $dir;
    }
    // get list of files and change $dir
    $dir = scandir($dir);
    // count items in the list of files
    $count = count($dir);
    // get item extensions in list of files
    echo " " . ": " . $count . ".xml files";
}


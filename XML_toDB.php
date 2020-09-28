<?php
require_once 'functions_DB.php'; // for DB connection, call of function db_connection
$start_time = time(); // start timer
// 1. scandir ftp_plangr
$dir_list = scandir('ftp_plangr');
/*
 define multidimensional array - $xml_files_list for list of .xml files
[$region => [
    UNZIP_region => [
        => '.'
        => '..'
        => .xml
        ...
                ]
        ]
]
  */
$xml_files_list = [];
// delete '.' and '..' folders
foreach ($dir_list as $index => $dir) {
    if ($dir === '.' || $dir === '..') {
        unset($dir_list[$index]);
    }
}
/*
define $scan_arr = []
in foreach cycle:
    iterate by folders in $dir_list
    full $scan_arr
*/

chdir('ftp_plangr'); // go to dir ftp_plangr. Without it scandir doesnt work
$scan_arr = [];
foreach ($dir_list as $dir_region => $content) {
    $scan_item = scandir($content); // scandir every dir
    $scan_arr [$content] = $scan_item; // push to array $scan_arr
}
// delete all elements except 'UNZIP' folders, i.e. all .zip files
foreach ($scan_arr as $reg => $sub_arr) {
    foreach ($sub_arr as $index => $item)
        if ($item !== 'UNZIP' . "_" . "$reg") {
            unset($scan_arr[$reg][$index]);
        }
}
/*
full array  $xml_files_list:
    contains all files  (without checking extension
    and two items '.', '..' in all UNZIP_region dirs
*/
foreach ($scan_arr as $reg => $unzip_dir) {
    foreach ($unzip_dir as $index => $xml_dir) {
        chdir($reg); // go to region dir
        $result_item = scandir($xml_dir);               // scandir folder UNZIP_$reg in every $reg folder
        $xml_files_list[$reg][$xml_dir] = $result_item; // push $result_item to array
        chdir('..');                            // go to parent dit , that next step chdir to next $reg dir
    }
}

/*
 There are three level in multidimensional array $xml_files_list
create triple foreach cycle for iteration by material dir catalog:
    ftp_plangr -> region -> UNZIP_region -> .xml file
 * */
$all_region_xml_value = []; // define result array
// define arrays for contact info and orders titles
$contacts = []; // contact info
$orders = []; // orders titles
$address_arr = [];
$okpd = [];
// start foreach
/* region_key => title organisation
                                    => ["orders" => [OKPDName tags->nodeValue,  ...]
                                        "contacts" => [phone => [phone tags->nodeValue],
                                                       email => [email tags->nodeValue],
                                                       address => [address tags->nodeValue],
                                ]
                   ]
* */
foreach ($xml_files_list as $region_key => $region_arr) // first level  [$region_key => $region_arr], i.e. in material it is dir region with dir UNZIP_region
{
    foreach ($region_arr as $unzip_dir => $item)       // second level [$unzip_dir => $item], i.e. in material it is dir UNZIP_region with content
    {
        foreach ($item as $key => $file)                // third level [$key => $file], i.e. in material it is content of dir UNZIP_region, i.e. list of files and two items '.', '..'
        {
            if ($file === ".." || $file === ".") // find '.' and '..' items in UNZIP_region dir
            {
//                echo $xml_files_list[$region_key][$unzip_dir][$key];     // debug
                unset($xml_files_list[$region_key][$unzip_dir][$key]); // delete '.' and '..' items in array $xml_files_list
            } else                                  // logic for all another items in UNZIP_region dir, i.e. all files except '.' and '..' items
            {
                chdir($region_key);     // go to region dir
                chdir($unzip_dir);      // go to UNZIP_region dir
                /* there is DOMDocument instance
                 * which used for get xml tags
                 * and value of xml tags                           * */
                $dom_doc = new DOMDocument;     // create DOMDocument instance
                $load = $dom_doc->load($file); // load DOMDocument instance
// logic for get nodeValue. getElementsByTagName return DOMNodeList instanse
                $fullName = $dom_doc->getElementsByTagName('fullName');

                $phone_tag = $dom_doc->getElementsByTagName("phone");
                $email_tag = $dom_doc->getElementsByTagName("email");
                $address_tag = $dom_doc->getElementsByTagName("factAddress");
                // need foreach
                $fio_firstName_tag = $dom_doc->getElementsByTagName("firstName");
                $fio_middleName_tag = $dom_doc->getElementsByTagName("middleName");
                $fio_lastName_tag = $dom_doc->getElementsByTagName("lastName");
                $fio_position_tag = $dom_doc->getElementsByTagName("position");

                $OKPDName_tag = $dom_doc->getElementsByTagName("OKPDName"); // for $okpd [] and next $orders []

                foreach ($fullName as $fullName_item) // 4 level foreach
                {
                    $test_item = $fullName_item->parentNode;

                    if ($test_item->tagName === "ns5:customerInfo") // condition for every xml tag parentNode->nodeName === ns5:customerInfo
                    {
                        foreach ($phone_tag as $phone_item) {
                            if ($phone_item->parentNode->tagName === "ns5:customerInfo") {
                                $phone_arr[] = $phone_item->nodeValue;
                            }
                        }
                        foreach ($email_tag as $email_item) {
                            if ($email_item->parentNode->tagName === "ns5:customerInfo") {
                                $email_arr[] = $email_item->nodeValue;
                            }
                        }
                        foreach ($address_tag as $address_item) {
                            if ($address_item->parentNode->tagName === "ns5:customerInfo") {
                                $address_arr[] = $address_item->nodeValue;
                            }
                        }

                        foreach ($fio_firstName_tag as $fio_firstName_tag_item) {
                            $fio_firstName_arr [] = $fio_firstName_tag_item->nodeValue;
                        }
                        foreach ($fio_middleName_tag as $fio_middleName_tag_item) {
                            $fio_middleName_arr [] = $fio_middleName_tag_item->nodeValue;
                        }
                        foreach ($fio_lastName_tag as $fio_lastName_tag_item) {
                            $fio_lastName_arr [] = $fio_lastName_tag_item->nodeValue;
                        }
                        foreach ($fio_position_tag as $fio_position_tag_item) {// need condition for parentNode-nodeName === ns5:confirmContactInfo
                            if ($fio_position_tag_item->parentNode->tagName === "ns5:confirmContactInfo") {
                                $fio_position_arr [] = $fio_position_tag_item->nodeValue;
                            }
                        } // create fio array
                        $fio = [
                            "name" => $fio_firstName_arr,
                            "middleName" => $fio_middleName_arr,
                            "lastName" => $fio_lastName_arr,
                            "position" => $fio_position_arr,
                        ];
                        $fio_firstName_arr = [];
                        $fio_middleName_arr = [];
                        $fio_lastName_arr = [];
                        $fio_position_arr = [];

                        foreach ($OKPDName_tag as $OKPD_item) // for $orders array
                        {
                            $okpd [] = $OKPD_item->nodeValue;
                        }
                        // creating result arrays
                        $contacts = [
                            "phone" => $phone_arr,
                            "email" => $email_arr,
                            "address" => $address_arr,
                            "fio" => $fio,
                        ];
                        // clean result array with contact info in every iteration
                        $phone_arr = [];
                        $email_arr = [];
                        $address_arr = [];
                        $fio = [];

                        $orders = [
                            "OKPDnames" => $okpd];
                        // clean result array with orders titles in every iteration
                        $okpd = [];
                        $result[$fullName_item->nodeValue] = [ // it is $fullName->nodeValue (title of organisation) like index for assoc array. See 4 level foreach
                            "orders" => $orders,
                            "contacts" => $contacts,
                        ];
                    }
                } // change dir to correct load DOMDocument instance
                chdir('..');    // up one level by dir catalog to region dir
                chdir('..');    // up one level by dir catalog to ftp_plangr dir
            }
        }
    }
    $all_region_xml_value [$region_key] = $result;
// clean result array
    $result = []; // clean $result array in every iteration
    $contacts = []; // clean $result array in every iteration
    // clean results array for contact info
}

foreach ($all_region_xml_value as $region => $region_content) {
    // insert to regions table
    $sql_regions = "INSERT INTO $tbl_title_region (RegionTitle) VALUES ('$region')";
    $db_conn->query($sql_regions);
    $last_insert_id_reg = $db_conn->lastInsertId(); // IMPORTANT get last insert id for FOREIGN KEY in table customers

    foreach ($region_content as $customer_title => $customer_content) {
        // get arrays with emails and phones
        $test_email = $region_content[$customer_title]["contacts"]["email"];
        $test_phone = $region_content[$customer_title]["contacts"]["phone"];
        // Get Zip code of customer
        $forZip = $region_content[$customer_title]["contacts"]["address"]; // get array content string with full address customer
        // iterate arrays with full address to get zip value
        foreach ($forZip as $key => $value) { // start iterate array content string with full address customer
            $zipPattern = '/[0-9]{6}/'; // make string pattern
            preg_match($zipPattern, $value, $matches, PREG_OFFSET_CAPTURE, 41); // check pattern of zip in string full addr
            $zipResult = $matches[0][0];
            // for ZIP code mask
            $service = str_split($zipResult, 3); // get arr[0] - first three numbers by ZIP code [1] second three numbers by ZIP code
            unset($service[1]);  // del second three numbers by ZIP code
            $zipMask = $service[0]; // first three numbers by ZIP code
            $servMaskArr[] = ['regionId' => $last_insert_id_reg, 'zipMask' => $zipMask];
        }

        // iterate arrays with emails and phones
        foreach ($test_phone as $arr => $phone_num)
        foreach ($test_email as $arr => $email_value)

                // INSERT INTO ZIP_maskReg
//         $sqlInsertZIP_maskReg = "INSERT INTO $table_ZIP_mask_region (ZIPMask, RegionId) VALUES ('$uniqueZipMask', '$last_insert_id_reg')";
//        $db_conn->query($sqlInsertZIP_maskReg); // run sql query
                // create sql INSERT requests for email and phone to $table_customers_title
                $sql_customers = "INSERT INTO $table_customers_title (OrgName, Phone, Email, Zip,  RegionId)  
VALUES ('$customer_title', '$phone_num', '$email_value', '$zipResult', '$last_insert_id_reg')"; //
//        $db_conn->query($sql_customers); // run sql query
        $last_insert_id_customer = $db_conn->lastInsertId(); // IMPORTANT get last insert id for FOREIGN KEY in tables fio and orders
        // get arrays with fio info
        $fio_firstName = $region_content[$customer_title]["contacts"]["fio"]["name"];
        $fio_midName = $region_content[$customer_title]["contacts"]["fio"]["middleName"];
        $fio_lastName = $region_content[$customer_title]["contacts"]["fio"]["lastName"];
        $fio_pos = $region_content[$customer_title]["contacts"]["fio"]["position"];
        // iterate arrays with fio info
        foreach ($fio_firstName as $arr => $firstName_val)
        foreach ($fio_midName as $arr => $midName_val)
        foreach ($fio_lastName as $arr => $lastName_val)
        foreach ($fio_pos as $arr => $pos_val)
                        $sql_fio = "INSERT INTO $table_fio_title (FirstName, MiddleName, LastName, Pos, CustomerId)
                                    VALUES ('$firstName_val', '$midName_val', '$lastName_val', '$pos_val', '$last_insert_id_customer')";
//        $db_conn->query($sql_fio); // run sql query
        // get arrays with orders
        $orders = $region_content[$customer_title]["orders"]["OKPDnames"];
        //iterate arrays with orders
        foreach ($orders as $arr => $orders_val) {
            $sql_orders = "INSERT INTO $table_orders_title (TitleOrder, CustomerId)
                           VALUES ('$orders_val', '$last_insert_id_customer')";
//            $db_conn->query($sql_orders); // run sql queries
        }
    }
}
$db_conn = null; // kill db connection
$end_time = time(); // kill timer
$time = $end_time - $start_time; // count execution time
// display execution time
echo "execution time" . " " . "$time" . " " . "seconds";
echo "<br>" . "<br>";



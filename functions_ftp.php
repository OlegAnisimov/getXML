<?php
// create ftp connection
function create_ftp_conn()
{
    global $ftp_conn; // for using in all project
    $ftp_server = "ftp.zakupki.gov.ru"; // FTP target server
    $ftp_conn = ftp_connect($ftp_server) or die ("FTP server not work now");
    $login = ftp_login($ftp_conn, 'free', 'free');
    // display info to user
    echo "<span>There is connection to : </span>" . "<span> $ftp_server </span> ";
    return $ftp_conn;
}

function number_need_files($ftp_conn, array $array)  // get array with
{
    foreach ($array as $file) {
        $file_size = ftp_size($ftp_conn, $file);
        $zip_check = stripos($file, ".zip"); // find in title of file extension .zip
        if ($file_size == -1 || $file_size == 22 || $zip_check == false) ; // -1 - folder, 22 - empty zip file
        else {
            $res_array[] = $file;
        }
    }
    $number_need_files = count($res_array);
    return $number_need_files;
}

// write data to target file
function write_to_file($file, $data)
{
    $handle_write = fopen($file, 'w+');
    fwrite($handle_write, $data);
    fclose($handle_write);
}

// get extensions of file for next change to .xml
function getExtension($filename)
{
    $path_info = pathinfo($filename);
    return $path_info['extension'];
}

function changeExtension($file)
{
    rename($file, "$file.xml");
}

function display_XML_test()
{
    echo <<<FROM_XML_DISPLAY
<html>
<head></head>
<body>
<button onclick="loadDoc()">PUSH</button>
<div id="div xml_display">
<label for="tag_name">Цель закупки</label>
<span id="tag_name"></span> <br>
<label for="tag_maxPrice">Цена закупки</label>
<span id="tag_maxPrice"></span> <span class="span rub">Руб</span>
</div>

<script>
  function loadDoc() {
        let xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                myFunction(this);
            }
        };
        xhttp.open("GET", "from_ftp/FTP402B.tmp.xml", true);
        xhttp.send();
    }
    function myFunction(xml) {
        let xmlDoc = xml.responseXML;
        console.log(xmlDoc);
        let xml_el = xmlDoc.getElementsByTagName('id')[0];
        
        // tag
        let tag_name = xmlDoc.getElementsByTagName('name')[3].childNodes[0].nodeValue; // get name tag 
         let tag_maxPrice = xmlDoc.getElementsByTagName('maxPrice')[0].childNodes[0].nodeValue;  // get maxPrice tag
          
                  
        // display value of XML tag
        let xml_el_val = xml_el.childNodes[0].nodeValue;
        console.log(xml_el_val);
        // where display on web page
         let display_tag_name = document.getElementById('tag_name');
         let display_tag_maxPrice = document.getElementById('tag_maxPrice');
         // display on web page
             display_tag_name.innerText =  tag_name;
             display_tag_maxPrice.innerText =  tag_maxPrice;
                 }
</script>
</body>
</html>
FROM_XML_DISPLAY;
}

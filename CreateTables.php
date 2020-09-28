<?php
require_once 'functions_DB.php'; // for DB connection, call of function db_connection
/* create tables * */
db_connection(); // create DB PDO connection with call func from "functions_DB.php"
$tbl_title_region = "regions"; // table title. Table with title region and id
$sql_create_regions = "CREATE TABLE $tbl_title_region 
                                (
                                Id INT PRIMARY KEY AUTO_INCREMENT,
                                RegionTitle VARCHAR(100) NOT NULL
                                )";
$table_customers_title = "customers";
$sql_create_customers = "CREATE TABLE $table_customers_title
                                (
                                Id INT PRIMARY KEY AUTO_INCREMENT,
                                OrgName     VARCHAR(250) NOT NULL,  
                                Phone       VARCHAR(50) NOT NULL,
                                Email       VARCHAR(50) NOT NULL,
                                Zip       VARCHAR(6) NOT NULL,
                                RegionId    INT,
                                CONSTRAINT FOREIGN KEY (RegionId) REFERENCES $tbl_title_region(Id)
                                )";
$table_orders_title = "orders"; // create title for orders table
$sql_create_orders = "CREATE TABLE $table_orders_title
                                (
                                    Id INT PRIMARY KEY AUTO_INCREMENT,
                                    CustomerId INT,
                                    TitleOrder VARCHAR(250),
                                    CONSTRAINT FOREIGN KEY (CustomerId) REFERENCES $table_customers_title(Id)
                                   
                                )";
$table_fio_title = "fio";
$sql_create_customer_fio = "CREATE TABLE $table_fio_title
                                    (
                                        Id         INT PRIMARY KEY AUTO_INCREMENT,
                                        FirstName  VARCHAR(30) NOT NULL,
                                        MiddleName VARCHAR(30) NOT NULL,
                                        LastName   VARCHAR(20) NOT NULL,
                                        Pos        VARCHAR(50) NOT NULL,  
                                        CustomerId INT,
                                        CONSTRAINT FOREIGN KEY (CustomerId) REFERENCES $table_customers_title(Id) 
                                     )";

// Service tables
$table_ZIP_mask_region = "ZIP_maskReg";
$sql_create_ZIP_maskReg = "CREATE TABLE $table_ZIP_mask_region 
                                    (
                                         Id INT PRIMARY KEY AUTO_INCREMENT,
                                         ZIPMask VARCHAR(3) NOT NULL,
                                         RegionId INT,
                                          CONSTRAINT FOREIGN KEY (RegionId) REFERENCES $tbl_title_region(Id)
                                )";

// run sql crete tables
//$db_conn->query($sql_create_regions); // create table regions
//$db_conn->query($sql_create_customers);
//$db_conn->query($sql_create_customer_fio);
//$db_conn->query($sql_create_orders);
//$db_conn->query($sql_create_ZIP_maskReg);

<?php
@session_start();
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'agent_view_'.$_SESSION['user']['org'];
 
// Table's primary key
$primaryKey = 'uid';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'uid', 'dt' => 0 ),
	array(
        'db'        => 'admin',
        'dt'        => 1,
        'formatter' => function( $d, $row ) {
            return (($d)?'<i class="icon-checkmark"></i>':'');
        }
	),
	array(
        'db'        => 'realname',
        'dt'        => 2,
        'formatter' => function( $d, $row ) {
            return ((is_null($d))?'N/A':$d);
        }
	),
    array( 'db' => 'email',  'dt' => 3 ),
    array( 'db' => 'node',  'dt' => 4 ),
    array( 'db' => 'submissions',  'dt' => 5 ),
    array(
        'db'        => 'last_active',
        'dt'        => 6,
        'formatter' => function( $d, $row ) {
            return ((is_null($d))?'New account':$d);
        }
	)
);
 
// SQL server connection information
include_once('db_params.php');
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);
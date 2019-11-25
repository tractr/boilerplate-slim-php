<?php

require_once __DIR__ . '/../../../public/initialize.php';
require_once __DIR__ . '/../inc/helper.php';

use Illuminate\Support\Facades\Schema;

const INDEX_PATH =  __DIR__ . '/indexes.json';

$_model;
$_field;
$_index_name;
$_table_name;
$schema;

/**
 * Create indexes for a collection
 *
 * @param server
 * @param collection
 * @param indexes
 * @return {Promise<void>}
 */
function createIndexes($collection, $index_list) {

	global $schema;
	global $_table_name;
	global $_index_name;
	global $_model;

	$_table_name = $collection;

	foreach ($index_list as $key => $value) {
		$_index_name = $key;
		$_model = $value;

		//check if index already exist
		$schema->table($_table_name, function ($table) {
			global $db;
			global $schema;
			global $_index_name;
			global $_table_name;
			global $_model;

            $sm = $schema->getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails($_table_name);

            if (! $doctrineTable->hasIndex($_index_name)) {

            	$column_list = array();
            	foreach ($_model['fields'] as $key => $value) {
            		//If index is text, we create full text index
            		if ($value == 'text') {
            			$schema->getConnection()->statement('ALTER TABLE '. $_table_name .' ADD FULLTEXT ('. $key .')');
            		}else if ($schema->hasColumn($_table_name, $key)){
            			array_push($column_list, $key);
            		}
            		
            	}

            	Helpers::output('', 'Creating index ' . $_index_name . ' on collection ' . $_table_name);

            	if (count($column_list) > 0) {
            		if (isset($_model['options']) && isset($_model['options']['unique'])) {
	            		$table->unique($column_list, $_index_name);
	            	}
	            	else{
	            		$table->index($column_list, $_index_name);
	            	}
            	}
            	
            }
        });
	}
}

// process
function _exec($db)
{
	global $_field;
	global $_model;
	global $schema;
	Helpers::output('success', 'Start database setup.');

	$index_list = file_get_contents(INDEX_PATH);
	$index_list = json_decode($index_list, true);

	$schema = $db::schema();

	
	//create collection if doesnt exist

	//create index
	foreach ($index_list as $collection => $value) {
		createIndexes($collection, $value);
	}

	Helpers::output('success', 'Did finished database setup.');
}

_exec($capsule);

// $app->run();
<?php

require_once __DIR__ . '/../../../public/initialize.php';
require_once __DIR__ . '/../inc/helper.php';
require_once __DIR__ . '/lorem.php';

use Illuminate\Support\Facades\Schema;

const MODEL_PATH =  __DIR__ . '/../../models.json';
const SECOND = 1000;
const MINUTE = 60 * SECOND;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;
const PopulationFactor = 150;
const TempId = 0;

/**
 * Returns a random sentence
 *
 * @param {boolean} short
 *  Should return a short word - Default: false
 * @returns {string}
 */
function generateString($short=false)
{
	global $Lorem;
	$words = array(pickOne($Lorem));
	if (randomNumber(0, 2) >= 1) array_push($words, pickOne($Lorem));
	if (!$short && randomNumber(0, 3) >= 2) array_push($words, pickOne($Lorem));

	foreach ($words as $key => $value) {
		$words[$key] = ucfirst($words[$key]);
	}

	return implode(' ', $words);
}

/**
 * Returns a random element of the array
 *
 * @param a
 * @return {*}
 */
function pickOne($a)
{
	return $a[rand(0, count($a) - 1)];
}

/**
 * Generate an item with random value
 *
 * @param field
 */
function generateField($field)
{
	// Primary ?
	if ($field['primary']) return "undefined";
	// Nullable ?
	if ($field['nullable'] && randomNumber(0, 2) >= 1) return null;

	if ($field['type'] === 'number') {
		if ($field['subtype'] === 'latitude') return randomLatitude();
		if ($field['subtype'] === 'longitude') return randomLongitude();

		return randomNumber();
	} else if ($field['type'] === 'datetime') {
		return date_create('@' . (date('U') + rand(-2, 2) * DAY))->format('Y-m-d H:i:s');
	} else if ($field['type'] === 'string') {
		if ($field['subtype'] === 'email') {
			$name = generateString(true);
			$name = strtolower($name);
			$name = explode(' ', $name);
			$name = implode('.', $name);
			$domain = pickOne(array('gmail.com', 'hotmail.com', 'tractr.net'));

			return $name . '.' . randomString(8) . '@' . $domain;
		} else if ($field['subtype'] === 'password') {
			return randomString();
		} else if ($field['subtype'] === 'text') {
			return generateString() . "\n" . generateString();
		} else if ($field['subtype'] === 'rich') {
			return '<h2>' . generateString() . '</h2>\n<p>' . generateString() . generateString() . '.</p>\n<p>' . generateString() . '.</p>';
		} else if ($field['unique']) {
			return  generateString() . generateString();
		}

		return generateString();
	} else if ($field['type'] === 'boolean') {
		return pickOne(array(false, true));
	} else if ($field['type'] === 'object') {
		return $field['multiple']
			? array( array('foo' => generateString(), 'bar' => randomNumber()), array('foo' => generateString(), 'bar' => randomNumber()) )
			: array('foo' => generateString(), 'bar' => randomNumber());
	} else if ($field['type'] === 'entity') {
		return TempId;
	}

	return null;
}

/**
 * Generate an item with random value
 *
 * @param model
 */
function generateItems($field_list= array())
{
	$result = array();
	foreach ($field_list as $field) {
		$value = generateField($field);

		if ($value !== 'undefined' && !($field['type'] === 'entity' && $field['multiple'])) {
			$result[$field['name']] = $value;
		}
	}

	return $result;
}


/**
 * Generate a random string
 *
 * @param {Number} length
 * @returns {String}
 * @private
 */
function randomString($length = 12) {
	$text = '';
	$possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	for ($i = 0; $i < $length; $i++) {
		//Select char_at
		$text .=  $possible{rand(0, strlen($possible) - 1)};
	}

	return $text;
}
/**
 * Generate a random latitude
 *
 * @param {Number} min
 * @param {Number} max
 * @returns {Number}
 * @private
 */
function randomNumber($min = 0, $max = 1000) {
	return floor(rand() * ($max - $min)) + $min;
}
/**
 * Generate a random latitude
 *
 * @returns {Number}
 * @private
 */
function randomLatitude() {
	$factor = 1000;

	return rand(-90 * $factor, 90 * $factor) / $factor;
}
/**
 * Generate a random longitude
 *
 * @returns {Number}
 * @private
 */
function randomLongitude() {
	$factor = 1000;

	return rand(-180 * $factor, 180 * $factor) / $factor;
}

$_model;
$_field;
$schema;

// process
function _exec($db)
{
	global $_field;
	global $_model;
	global $schema;
	Helpers::output('success', 'Start database population.');

	$model_list = file_get_contents(MODEL_PATH);
	$model_list = json_decode($model_list, true);

	$schema = $db::schema();

	//create column
	foreach ($model_list as $model) {
		$_model = $model;
		// check if table exist, create one if not
		if (!$schema->hasTable($model['collection'])) {
		    $schema->create($model['collection'], function($table){
	            $table->engine = 'InnoDB';
	            $table->increments('_id');
	            $table->timestamps();
		    });
		}

		foreach ($model['fields'] as $field) {
			$_field = $field;

			//create field if doesn't exist
			if (!$schema->hasColumn($model['collection'], $_field['name'])) {

				$schema->table($model['collection'], function ($table) { 

					global $_field;
					global $_model;
					global $schema;
					$col = null;
					
					switch ($_field['type']) {
						case 'string':
							if ($_field['subtype'] == 'rich' || $_field['subtype'] == 'text') {
								$col = $table->text($_field['name']);
							}
							else{
								$col = $table->string($_field['name']);
							}
							break;

						case 'datetime':
							$col = $table->dateTime($_field['name']);
							break;

						case 'number':
							if ($_field['subtype'] == 'latitude' || $_field['subtype'] == 'longitude') {
								$col = $table->decimal($_field['name'], 9, 6);
							}
							else if ($_field['subtype'] == 'float') {
								$col = $table->float($_field['name'], 8, 2);
							} else {
								$col = $table->integer($_field['name']);
							}
							
							break;

						case 'boolean':
							$col = $table->boolean($_field['name']);
							break;

						case 'entity':
							if ($_field['multiple']) {

								//collection for many to many relationship
								$collection_name = $_model['collection'] . '_' . $_field['reference'];

								if (!$schema->hasTable($collection_name)) {
								    $schema->create($collection_name, function($_table){
								    	global $_field;
								    	global $_model;
							            $_table->engine = 'InnoDB';
							            $_table->unsignedInteger($_field['reference'] . '_id');
							            $_table->unsignedInteger($_model['collection'] . '_id');
								    });
								}
							}else{
								//collection for one to many relationship
								$col = $table->unsignedInteger($_field['name']);
							}
							break;
						
					}

					if ($_field['nullable'] && !$_field['multiple']) {
						$col->nullable();
					}
				});
			}

			
		}

		$insert = array();
		$total = rand(PopulationFactor, 2 * PopulationFactor);

		for ($i = 0; $i < $total; $i++) {
			array_push($insert, generateItems($model['fields']));
		}

		Helpers::output('', 'Inserting '. count($insert) .' documents in ' . $model['collection']);

		try {
			$db::table($model['collection'])->insert($insert);
		} catch (Exception $e) {
			Helpers::output('error', 'Error while inserting '. $model['collection'] .' : ' . $e->getMessage());
		}
	}

	//populate table
	foreach ($model_list as $model) {

		//Relation
		$item_list = $db::table($model['collection'])->select('_id')->get();
		$id_list = array();
		$ref_update_list = array();
		foreach ($model['fields'] as $field) {
			$_field = $field;

			if ($_field['reference'] != null) {
				$result = $db::table($_field['reference'])->select('_id')->get();
				$id_list[$_field['reference']] = $result;

				array_push($ref_update_list, $id_list);
			}
			
		}

		if (count($ref_update_list) == 0) {
			break;
		}

		foreach ($item_list as $item) {
			$set = array();
			$set_multiple = array();
			$unique_col_list = array();
			$should_update = true;

			foreach ($model['fields'] as $field) {
				$_field = $field;

				if ($_field['reference'] != null) {

					if ($_field['multiple']) {
						$min = $_field['nullable'] ? 0 : 1;
						$max = $_field['nullable'] ? 3 : 4;
						$l = rand($min, $max);

						$set_multiple[$_field['name']] = array();
						for ($i = 0; $i < $l; $i++) {
							array_push($set_multiple[$_field['name']], pickOne($id_list[$_field['reference']])->_id);
						}

						foreach ($set_multiple[$_field['name']] as $_id) {
							$collection_name = $model['collection'] . '_' . $_field['reference'];
							$db::table($collection_name)->insert(array(
								$model['collection'] . '_id' => $item->_id, 
								$_field['reference'] . '_id' => $_id
							));
						}
					}
					else{
						$set[$_field['name']] = pickOne($id_list[$_field['reference']])->_id;						
					}
				}

				if ($_field['unique']) {
					array_push($unique_col_list, $_field['name']);
				}
			}

			//We remove duplated entries
			foreach ($unique_col_list as $col) {
				$row = $db::table($model['collection'])->where($col, $set[$col])->get();
				if ($row != null) {
					$db::table($model['collection'])->where($col, $set[$col])->delete();
				}
			}

			$db::table($model['collection'])->where('_id', $item->_id)->update($set);
		}
	}

	Helpers::output('success', 'Did finished database population.');
	Helpers::output('success', 'Start foreign key constraint.');

	//foreign key
	foreach ($model_list as $model) {
		$_model = $model;

		foreach ($model['fields'] as $field) {
			$_field = $field;

			if ($_field['type'] == 'entity') {
				//create index then foreign key

				if ($_field['multiple']) {
					$collection_name = $model['collection'] . '_' . $_field['reference'];
					$schema->table($collection_name, function ($table) {
						global $_field;
				    	global $_model;
				    	$table->index($_field['reference'] . '_id');
				    	$table->foreign($_field['reference'] . '_id')->references('_id')->on($_field['reference']);

				    	$table->index($_model['collection'] . '_id');
				    	$table->foreign($_model['collection'] . '_id')->references('_id')->on($_model['collection']);
					});
				}else{
					$schema->table($model['collection'], function ($table) {
						global $_field;
						$table->index($_field['name']);
						$table->foreign($_field['name'])->references('_id')->on($_field['reference']);
					});
				}
			}

			if ($_field['unique']) {
				
				$schema->table($model['collection'], function ($table) {
					global $_field;
					$table->unique($_field['name']);
				});
			}
		}
	}

	Helpers::output('success', 'Foreign key updated.');
}

_exec($capsule);

// $app->run();
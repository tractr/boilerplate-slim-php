<?php

require_once __DIR__ . '/../../../public/initialize.php';
require_once __DIR__ . '/../inc/helper.php';
require_once __DIR__ . '/lorem.php';

const MODEL_PATH = __DIR__ . '/../../models.json';
const SECOND = 1000;
const MINUTE = 60 * SECOND;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;
const PopulationFactor = 150;

use App\Library\Encryption;

$TempId = 0;

/**
 * Returns a random sentence
 *
 * @param boolean short
 *  Should return a short word - Default: false
 * @return string
 */
function generateString($short = false)
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
 * @param array $a
 * @return mixed
 */
function pickOne($a)
{
    return $a[rand(0, count($a) - 1)];
}

/**
 * Generate an item with random value
 *
 * @param array $field
 * @return mixed
 */
function generateField($field)
{
    global $TempId;

    // Primary ?
    if ($field['primary']) return 'undefined';
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
            $domain = pickOne(array('mail.com', 'example.com'));

            return $name . '.' . randomString(8) . '@' . $domain;
        } else if ($field['subtype'] === 'password') {
            return Encryption::hash(randomString());
        } else if ($field['subtype'] === 'text') {
            return generateString() . "\n" . generateString();
        } else if ($field['subtype'] === 'rich') {
            return '<h2>' . generateString() . '</h2><p>' . generateString() . generateString() . '.</p><p>' . generateString() . '.</p>';
        } else if ($field['unique']) {
            return generateString() . generateString();
        }

        return generateString();
    } else if ($field['type'] === 'boolean') {
        return pickOne(array(false, true));
    } else if ($field['type'] === 'object') {
        return json_encode(
            $field['multiple']
            ? array(array('foo' => generateString(), 'bar' => randomNumber()), array('foo' => generateString(), 'bar' => randomNumber()))
                : array('foo' => generateString(), 'bar' => randomNumber())
        );
    } else if ($field['type'] === 'entity') {
        $TempId++;
        return $TempId;
    }

    return null;
}

/**
 * Generate an item with random value
 *
 * @param array $field_list
 * @return array
 */
function generateRow($field_list = array())
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
 * @param int length
 * @return string
 */
function randomString($length = 12)
{
    $text = '';
    $possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ($i = 0; $i < $length; $i++) {
        //Select char_at
        $text .= $possible{rand(0, strlen($possible) - 1)};
    }

    return $text;
}

/**
 * Generate a random latitude
 *
 * @param int min
 * @param int max
 * @return int
 * @private
 */
function randomNumber($min = 0, $max = 1000)
{
    return rand(floor($min), floor($max));
}

/**
 * Generate a random latitude
 *
 * @return float
 */
function randomLatitude()
{
    $factor = 1000;

    return randomNumber(-90 * $factor, 90 * $factor) / $factor;
}

/**
 * Generate a random longitude
 *
 * @return float
 */
function randomLongitude()
{
    $factor = 1000;

    return randomNumber(-180 * $factor, 180 * $factor) / $factor;
}

// process
function _exec(Illuminate\Database\Capsule\Manager $db)
{
    Helpers::output('success', 'Start database population.');

    $model_list = file_get_contents(MODEL_PATH);
    $model_list = json_decode($model_list, true);

    $schema = $db::schema();
    $schema->disableForeignKeyConstraints();

    // =======================================================
    // Populate scalar fields
    foreach ($model_list as $model) {

        $table = $model['collection'];
        $numberOfRows = randomNumber(PopulationFactor * 0.5, PopulationFactor * 1.5);

        for ($i = 0; $i < $numberOfRows; $i++) {
            $row = generateRow($model['fields']);
            $db::table($table)->insert($row);
        }

        Helpers::output('', "Did populate table {$table}.");
    }

    // =======================================================
    // Get ids for all models
    $ids = [];
    foreach ($model_list as $model) {
        $table = $model['collection'];
        $ids[$table] = array_map(function ($value) {
            return $value->_id;
        }, $db::table($table)->select('_id')->get()->toArray());
    }

    // =======================================================
    // Populate single relations
    foreach ($model_list as $model) {
        $table = $model['collection'];
        $done = false;

        foreach ($model['fields'] as $field) {

            if ($field['multiple'] || $field['reference'] === null) {
                continue;
            }

            $target = $field['reference'];
            $fieldName = $field['name'];
            // For each row of this model, set the field
            for ($i = 0; $i < count($ids[$table]); $i++) {
                $targetId = pickOne($ids[$target]);
                try {
                    $db::table($table)
                        ->where('_id', '=', $ids[$table][$i])
                        ->whereNull($fieldName, 'and', true)// Leave NULL values
                        ->update([$fieldName => $targetId]);
                } catch (Exception $exception) {
                    Helpers::output('warning', "Could not set relation for {$table}.{$fieldName}: {$exception->getMessage()}");
                }
            }

            $done = true;
        }

        if ($done) {
            Helpers::output('', "Did link single relations for table {$table}.");
        }
    }

    // =======================================================
    // Populate many-to-many relations
    foreach ($model_list as $model) {
        $table = $model['collection'];
        $done = false;

        foreach ($model['fields'] as $field) {

            if (!$field['multiple'] || $field['reference'] === null) {
                continue;
            }

            $target = $field['reference'];
            $fieldName = $field['name'];
            $relationTable = "{$table}__{$fieldName}";
            $min = $field['nullable'] ? 0 : 1;
            $max = $field['nullable'] ? 3 : 4;
            // For each row of this model, add a few relations
            for ($i = 0; $i < count($ids[$table]); $i++) {
                $length = rand($min, $max);

                for ($j = 0; $j < $length; $j++) {
                    $targetId = pickOne($ids[$target]);
                    try {
                        $db::table($relationTable)
                            ->insert([
                                $table => $ids[$table][$i],
                                $target => $targetId
                            ]);
                    } catch (Exception $exception) {
                        Helpers::output('warning', "Could not set relation for {$relationTable}: {$exception->getMessage()}");
                    }

                }
            }

            $done = true;
        }

        if ($done) {
            Helpers::output('', "Did link multiple relations for table {$table}.");
        }
    }

    $schema->enableForeignKeyConstraints();

    Helpers::output('success', "Did populate database.");
}

_exec($capsule);

<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @param string|DateTime $value
     * @return int|null
     */
    protected function dateToTimestamp($value)
    {
        if ($value instanceof DateTime) {
            return $value->getTimestamp() * 1000;
        }
        if (is_string($value)) {
            return DateTime::createFromFormat($this->dateFormat, $value)->getTimestamp() * 1000;
        }
        if (empty($value)) {
            return NULL;
        }
        return $value;
    }

    /**
     * @param integer $value
     * @return string|null
     */
    protected function timestampToDate($value)
    {
        if (is_integer($value)) {
            return date($this->dateFormat, intval($value) / 1000);
        }
        if (empty($value)) {
            return NULL;
        }
        return $value;
    }

    /**
     * @param integer $value
     * @return boolean
     */
    protected function intToBoolean($value)
    {
        return is_integer($value) ? boolval($value) : $value;
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function jsonToObject($value)
    {
        return is_string($value) ? json_decode($value) : $value;
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function objectToJson($value)
    {
        return !is_string($value) && $value !== NULL ? json_encode($value) : $value;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public static function toBoolean($value)
    {
        return $value === 'false' ? false : boolval($value);
    }

    /**
     * @param integer $value
     * @return string
     */
    public static function toDate($value)
    {
        $n = new static();
        return date($n->dateFormat, intval($value) / 1000);
    }


}

<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model {

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
     * @param $attribute string
     * @return int|null
     */
    protected function dateToTimestamp($attribute)
    {
        return $this->attributes[$attribute] ?
            \DateTime::createFromFormat($this->dateFormat, $this->attributes[$attribute])->getTimestamp() * 1000 :
            NULL;
    }

    /**
     * @param $attribute string
     * @return boolean|null
     */
    protected function intToBoolean($attribute)
    {
        return is_integer($this->attributes[$attribute]) ? boolval($this->attributes[$attribute]) : NULL;
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

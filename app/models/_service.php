<?php 

namespace App\Models;

class Service extends BaseModel{

	/**
     * The table associated with the model.
     *
     * @var string
     */
   	protected $table = 'service';

   	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
   	protected $fillable = array('id', 'name', 'description', 'created_at', 'updated_at');

   	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array('created_at', 'updated_at');

    /**
     * Get search cursor
     * @param  \Illuminate\Database\Query\Builder $query  query_buider of service model
     * @param  array $filter column to search
     * @return \Illuminate\Database\Query\Builder         cursor of the query
     */
    public static function get_cursor($query, $filter){
        
        unset($filter['_page']);
        unset($filter['_limit']);
        unset($filter['_order']);
        unset($filter['_sort']);

        if (isset($filter['name'])) {
            $query->orWhere(
                array(
                    array('name', 'LIKE', "%{$filter['name']}%"),
                )
            );
        }

        return $query;
    }

}

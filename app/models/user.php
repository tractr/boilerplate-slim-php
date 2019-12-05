<?php 
namespace App\Models;
class user extends BaseModel{
	/**
     * The table associated with the model.
     *
     * @var string
     */
   	protected $table = 'user';
   	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
   	protected $fillable = array(
        '_id',
        'created_at',
        'name',
        'email',
        'password',
        'role',
        'banned',
        'last_connected_at',
   	);
   	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array(
        '_id',
        'created_at',
        'password',
        'last_connected_at',
        'updated_at',
    );
    /**
     * Get search cursor
     * @param  \Illuminate\Database\Query\Builder $query  query_buider of service model
     * @param  array $filter column to search
     * @return \Illuminate\Database\Query\Builder         cursor of the query
     */
    public static function get_cursor($filter, $credentials = null, $from_admin = false){

        unset($filter['_page']);
        unset($filter['_limit']);
        unset($filter['_order']);
        unset($filter['_sort']);

        $query = new user();

        // Use LIKE for name
        if (isset($filter['name'])) {
            $query = $query->orWhere('name', 'LIKE', "%{$filter['name']}%");
        }
        return $query;
    }

}
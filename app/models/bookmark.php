<?php 
namespace App\Models;
class bookmark extends BaseModel{
	/**
     * The table associated with the model.
     *
     * @var string
     */
   	protected $table = 'bookmark';
   	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
   	protected $fillable = array(
        '_id',
        'created_at',
        'owner',
        'place',
   	);
   	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array(
        '_id',
        'created_at',
        'owner',
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

        $query = new bookmark();

        // Convert MongoId for owner
        if (isset($filter['owner'])) {
            $query = $query->owner->where('owner', $filter['owner']);
        }
        else if (!$from_admin && $credentials != null) {
            $query = $query->where('owner', '=', $credentials['ID']);
        }

        // Convert MongoId for place
        if (isset($filter['place'])) {
            $query = $query->place->where('place', $filter['place']);
        }
        return $query;
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function _owner()
    {
        return $this->hasOne('App\Models\user', '_id', 'owner');
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function _place()
    {
        return $this->hasOne('App\Models\place', '_id', 'place');
    }

}
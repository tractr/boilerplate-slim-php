<?php 
namespace App\Models;
class Bookmark extends BaseModel{
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
   	// protected $fillable = array('id', 'name', 'description', 'created_at', 'updated_at');
   	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array(
        '_id',
        'created_at',
        'owner',
    );
    /**
     * Get search cursor
     * @param  \Illuminate\Database\Query\Builder $query  query_buider of service model
     * @param  array $filter column to search
     * @return \Illuminate\Database\Query\Builder         cursor of the query
     */
    public static function get_cursor($query, $filter, $credentials = null, $from_admin = false){

        unset($filter['_page']);
        unset($filter['_limit']);
        unset($filter['_order']);
        unset($filter['_sort']);

        // Convert MongoId for owner
        if (isset($filter['owner'])) {
            $query->owner()->where('owner', $filter['owner']);
        }
        else if (!$from_admin && $credentials != null) {
            $query->where('owner', '=', $credentials['ID']);
        }

        // Convert MongoId for place
        if (isset($filter['place'])) {
            $query->place()->where('place', $filter['place']);
        }
        return $query;
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function owner()
    {
        return $this->hasOne('App\Model\Owner');
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function place()
    {
        return $this->hasOne('App\Model\Place');
    }

}

<?php 
namespace App\Models;
class Place extends BaseModel{
	/**
     * The table associated with the model.
     *
     * @var string
     */
   	protected $table = 'place';
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
        'latitude',
        'longitude',
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

        // Use LIKE for name
        if (isset($filter['name'])) {
            $query->orWhere('name', 'LIKE', "%{$filter['name']}%");
        }

        // Convert MongoId for category
        if (isset($filter['category'])) {
            $query->category()->where('category', $filter['category']);
        }
        // Set min for latitude if defined
        if (isset($filter['latitude']) && strstr($filter['latitude'], '_min')) {
            $query->orWhere('latitude', '>', $filter['latitude']);
        }
        // Set max for latitude if defined
        if (isset($filter['latitude']) && strstr($filter['latitude'], '_max')) {
            $query->orWhere('latitude', '<', $filter['latitude']);
        }
        // Set min for longitude if defined
        if (isset($filter['longitude']) && strstr($filter['longitude'], '_min')) {
            $query->orWhere('longitude', '>', $filter['longitude']);
        }
        // Set max for longitude if defined
        if (isset($filter['longitude']) && strstr($filter['longitude'], '_max')) {
            $query->orWhere('longitude', '<', $filter['longitude']);
        }

        // Convert MongoId for services
        if (isset($filter['services'])) {
            $query->services()->where('services', $filter['services']);
        }

        // Convert MongoId for owner
        if (isset($filter['owner'])) {
            $query->owner()->where('owner', $filter['owner']);
        }
        return $query;
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function category()
    {
        return $this->hasOne('App\Model\Category');
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function services()
    {
        return $this->hasMany('App\Model\Services');
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function owner()
    {
        return $this->hasOne('App\Model\Owner');
    }

}

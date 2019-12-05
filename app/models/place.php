<?php 
namespace App\Models;
class place extends BaseModel{
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
   	protected $fillable = array(
        '_id',
        'created_at',
        'name',
        'description',
        'category',
        'address',
        'latitude',
        'longitude',
        'phone',
        'website_url',
        'services',
        'owner',
        'disabled',
   	);
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

        $query = new place();

        // Use LIKE for name
        if (isset($filter['name'])) {
            $query = $query->orWhere('name', 'LIKE', "%{$filter['name']}%");
        }

        // Convert MongoId for category
        if (isset($filter['category'])) {
            $query = $query->category->where('category', $filter['category']);
        }
        // Set min for latitude if defined
        if (isset($filter['latitude']) && strstr($filter['latitude'], '_min')) {
            $query = $query->orWhere('latitude', '>', $filter['latitude']);
        }
        // Set max for latitude if defined
        if (isset($filter['latitude']) && strstr($filter['latitude'], '_max')) {
            $query = $query->orWhere('latitude', '<', $filter['latitude']);
        }
        // Set min for longitude if defined
        if (isset($filter['longitude']) && strstr($filter['longitude'], '_min')) {
            $query = $query->orWhere('longitude', '>', $filter['longitude']);
        }
        // Set max for longitude if defined
        if (isset($filter['longitude']) && strstr($filter['longitude'], '_max')) {
            $query = $query->orWhere('longitude', '<', $filter['longitude']);
        }

        // Convert MongoId for services
        if (isset($filter['services'])) {
            $query = $query->services->where('services', $filter['services']);
        }

        // Convert MongoId for owner
        if (isset($filter['owner'])) {
            $query = $query->owner->where('owner', $filter['owner']);
        }
        return $query;
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function _category()
    {
        return $this->hasOne('App\Models\placeCategory', '_id', 'category');
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function _services()
    {
        return $this->belongsToMany('App\Models\service', 'place_service', 'service_id', 'place_id');
    }
    /**
     * Return entity list from relationship
     * @return array
     */
    public function _owner()
    {
        return $this->hasOne('App\Models\user', '_id', 'owner');
    }

}
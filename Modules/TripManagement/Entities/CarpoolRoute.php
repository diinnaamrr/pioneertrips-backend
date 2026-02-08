<?php

namespace Modules\TripManagement\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Modules\UserManagement\Entities\User;
use Modules\VehicleManagement\Entities\Vehicle;


class CarpoolRoute extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
      'start_location',
      'end_location',
      'route_points',
      'encoded_polyline',
      'rest_stops',
      'start_time',
      'end_time',
      'ride_type',
      'price',
      'user_id',
      'vehicle_id',
      'is_ac',
      'is_smoking_allowed',
      'seats_available',
      'bags_available',
      'bags_seats_available',
      'has_music',
      'allowed_gender',
      'allowed_age_min',
      'allowed_age_max',
      'has_screen_entertainment',
      'allow_luggage',
      'is_trip_started',
      'trip_started_at'
  ];
  

    protected $spatial = [
        'start_location',
        'end_location',
    ];


    protected $casts = [
        'start_location' => Point::class,
        'end_location' => Point::class,
        'route_points' => 'array',
        'start_time' => 'datetime',
        'is_ac' => 'boolean',
        'is_smoking_allowed' => 'boolean',
        'end_time' => 'datetime',
        'has_music' => 'boolean',
        'has_screen_entertainment' => 'boolean',
        'allow_luggage' => 'boolean'
    ];

    public function trip()
    {
        return $this->hasMany(TripRequest::class,'carpool_route_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }


}

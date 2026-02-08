<?php

namespace Modules\TripManagement\Http\Controllers\Api\New\Driver;

use App\Events\DriverTripAcceptedEvent;
use App\Jobs\SendPushNotificationJob;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\TripManagement\Entities\CarpoolPassenger;
use Modules\TripManagement\Entities\CarpoolRoute;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Http\Controllers\Api\Driver\TripRequestController as BaseTripRequestController;
use Modules\TripManagement\Interfaces\TripRequestInterfaces;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;
use Modules\TripManagement\Transformers\TripRequestResource;
use Modules\UserManagement\Interfaces\DriverDetailsInterface;

class CarpoolingExtensionController

{
    protected $tripRequestservice;
    protected $trip;
    protected $tripRequestController;

    public function __construct(
        TripRequestServiceInterface $tripRequestservice,
        TripRequestInterfaces $trip, 
        BaseTripRequestController $tripRequestController,
        DriverDetailsInterface $driverDetails
    )
    {
        $this->tripRequestservice = $tripRequestservice;
        $this->trip = $trip;
        $this->tripRequestController = $tripRequestController;
        $this->driverDetails = $driverDetails;
    }


    public function registerDriverRoute(Request $request): JsonResponse
    {
            $validator = Validator::make($request->all(), [
                'start_lat' => 'required|numeric',
                'start_lng' => 'required|numeric',
                'end_lat' => 'required|numeric',
                'end_lng' => 'required|numeric',
                'start_time' => 'required|date_format:Y-m-d H:i:s',
                'ride_type' => 'nullable|in:college,work,transportation,governorates traveling',
                'ride_type' => 'nullable|in:college,work,transportation,governorates traveling',
                'is_ac' => 'nullable|boolean',
                'is_smoking_allowed' => 'nullable|boolean',
                'seats_available' => 'nullable|integer|min:1|max:50',
                'bags_available' => 'nullable|integer|min:0|max:50',
                'bags_seats_available' => 'nullable|integer|min:0|max:2',
                'has_music' => 'nullable|boolean',
                'allowed_gender' => 'nullable|in:male,female,both',
                'allowed_age_min' => 'nullable|integer|min:13|max:99',
                'allowed_age_max' => 'nullable|integer|min:13|max:99',
                'has_screen_entertainment' => 'nullable|boolean',
                'allow_luggage' => 'nullable|boolean',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'price' => 'required|numeric',
                'rest_stops' => 'nullable|array',
                'rest_stops.*.lat' => 'required_with:rest_stops|numeric',
                'rest_stops.*.lng' => 'required_with:rest_stops|numeric',
                'rest_stops.*.name' => 'nullable|string|max:255',

            ]);


            if ($validator->fails()) {
                return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
            }

            $route = $this->getRoutePointsFromAPI(
                $request->start_lat,
                $request->start_lng,
                $request->end_lat,
                $request->end_lng
            );

            if (!$route) {
                return response()->json(responseFormatter(DEFAULT_400, ['message' => 'Unable to retrieve route']), 400);
            }

            // Get the encoded polyline from Google Directions API
            $encodedPolyline = $this->getEncodedPolylineFromAPI(
                $request->start_lat,
                $request->start_lng,
                $request->end_lat,
                $request->end_lng
            );

            $startAddress = $this->getAddressFromLatLng($request->start_lat, $request->start_lng);
            $endAddress = $this->getAddressFromLatLng($request->end_lat, $request->end_lng);


            $googleEta = Carbon::parse($request->start_time)->addMinutes(
                $this->getEstimatedDurationInMinutes($request->start_lat, $request->start_lng, $request->end_lat, $request->end_lng)
            );
			$user = User::with('vehicle')->find(auth()->id());
            CarpoolRoute::updateOrCreate(

                [
                //    'user_id' => "001178d6-7d2b-48da-af45-e3ab21e977db",
                    'user_id' =>  auth()->id(),
                    'start_location' => DB::raw("ST_GeomFromText('POINT({$request->start_lng} {$request->start_lat})')"),
                    'end_location'   => DB::raw("ST_GeomFromText('POINT({$request->end_lng} {$request->end_lat})')"),
                    'route_points'   => json_encode($route),
                    'encoded_polyline' => $encodedPolyline,
                    'start_time'     => Carbon::parse($request->start_time),
                    //'end_time'       => $googleEta,
                    'start_address'  => $startAddress,
                    'end_address'    => $endAddress,
                    'is_ac' => $request->get('is_ac', 0),
                    'is_smoking_allowed' => $request->get('is_smoking_allowed', 0),
                    'seats_available' => $request->get('seats_available', null),
                    'bags_available' => $request->get('bags_available', null),
                    'bags_seats_available' => $request->get('bags_seats_available', null),
                    'ride_type' => $request->get('ride_type', null),
                    'has_music' => $request->get('has_music', 0),
                    'allowed_gender' => $request->get('allowed_gender', 'both'),
                    'allowed_age_min' => $request->get('allowed_age_min'),
                    'allowed_age_max' => $request->get('allowed_age_max'),
                    'has_screen_entertainment' => $request->get('has_screen_entertainment', 0),
                    'allow_luggage' => $request->get('allow_luggage', 1),
                    'price' =>$request->price,
                    'rest_stops' => $request->filled('rest_stops') ? json_encode($request->rest_stops) : null,
                    'vehicle_id' =>$user->vehicle->id ?? $request->get('vehicle_id', null),
                ]
            );



            return response()->json(responseFormatter(DEFAULT_STORE_200));
    }

        private function getEstimatedDurationInMinutes(float $startLat, float $startLng, float $endLat, float $endLng): int
        {
            $apiKey = 'AIzaSyAFi7yQhENElJAYaMtIIP3qkc_xJ7QLGqM';
            $response = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
                'origin' => "$startLat,$startLng",
                'destination' => "$endLat,$endLng",
                'mode' => 'driving',
                'key' => $apiKey,
            ]);

            if (!$response->ok() || $response['status'] !== 'OK') {
                return 0;
            }

            return intval($response['routes'][0]['legs'][0]['duration']['value'] / 60); // in minutes
        }



 //   private function getAddressFromLatLng(float $lat, float $lng): ?string
   //     {
     //       $apiKey = env('GOOGLE_MAPS_API_KEY');
       //     $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
         //       'latlng' => "$lat,$lng",
           //     'key' => $apiKey,
             //   'language' => 'ar'
           // ]);

            // if (!$response->ok() || $response['status'] !== 'OK') {
              //  return null;
           // }

           // return $response['results'][0]['formatted_address'] ?? null;
        // }

  public function getAcceptedPassengersForRoute(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'carpool_route_id' => 'required|exists:carpool_routes,id',    ]);

    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }

    $route = CarpoolRoute::with('passengers.user')
      ->find($request->carpool_route_id);

    if (!$route) {
        return response()->json(responseFormatter(DEFAULT_404), 403);
    }

    $acceptedPassengers = $route->passengers->where('driver_decision', 'accepted')->map(function ($passenger) {
        return [
            'carpool_passenger_id' => $passenger->id,
            'name' => $passenger->user->full_name ?? 'unknowen',
            'pickup_address' => $this->getAddressFromLatLng($passenger->pickup_location->latitude, $passenger->pickup_location->longitude),
            'dropoff_address' => $this->getAddressFromLatLng($passenger->dropoff_location->latitude, $passenger->dropoff_location->longitude),
            'seats_count' => $passenger->seats_count,
            'fare' => $passenger->fare,
            'profile_image' => $passenger->user->profile_image ? asset('storage/' . $passenger->user->profile_image) : null,
        ];
    });

    return response()->json(responseFormatter(DEFAULT_200, [
        'route_id' => $route->id,
        'start_time' => $route->start_time,
        'end_time' => $route->end_time,
        'status' => $route->status,
        'accepted_passengers' => $acceptedPassengers,
    ]));
}


    public function getTripSchedule(): JsonResponse
    {
       // $driverId = Auth::id();

        $trips = CarpoolRoute::whereHas('trip.driver', fn($q) => $q->where('id', 'c62cb647-513d-42fe-a891-29a24073cd5f'))
            ->with('trip')
            ->orderBy('start_time')
            ->get();

        return response()->json(responseFormatter(DEFAULT_200, $trips));
    }

public function findMatchingRidesForPassenger(Request $request): JsonResponse
{

    Log::info('Full Request', [
        'all' => $request->all(),
        'input' => $request->input(),
        'content' => $request->getContent(),
        'json_decoded' => json_decode($request->getContent(), true),
    ]);

    $validator = Validator::make($request->all(), [
        'pickup_lat' => 'required|numeric',
        'pickup_lng' => 'required|numeric',
        'dropoff_lat' => 'required|numeric',
        'dropoff_lng' => 'required|numeric',
        'gender' => 'nullable|in:male,female,both',
        'seats_required' => 'nullable|integer|min:1|max:8',
        'ride_type' => 'nullable|string',
        'day' => 'required|date',
        'category' => 'nullable|string', // new
    ]);

    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }
    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }

    $routes = CarpoolRoute::with([
        'user.vehicle.model.brand',
        'user.vehicle.category'
    ])->whereDate('start_time', '=', date('Y-m-d', strtotime($request->day)));
    $routes = CarpoolRoute::with([
        'user.vehicle.model.brand',
        'user.vehicle.category'
    ])->whereDate('start_time', '=', date('Y-m-d', strtotime($request->day)));

    if ($request->filled('ride_type')) {
        $routes->where('ride_type', $request->ride_type);
    }
    if ($request->filled('ride_type')) {
        $routes->where('ride_type', $request->ride_type);
    }

    if ($request->filled('gender') && in_array($request->gender, ['male', 'female', 'both'])) {
        $routes->where('allowed_gender', $request->gender);
    }
    if ($request->filled('gender') && in_array($request->gender, ['male', 'female', 'both'])) {
        $routes->where('allowed_gender', $request->gender);
    }

    if ($request->filled('seats_required')) {
        $routes->where('seats_available', '>=', $request->seats_required);
    }
    if ($request->filled('seats_required')) {
        $routes->where('seats_available', '>=', $request->seats_required);
    }

    $results = [];
    $results = [];

    foreach ($routes->get() as $route) {
        $points = json_decode($route->route_points, true);
          if (!empty($route->rest_stops)) {
                  $restStops = json_decode($route->rest_stops, true);
                  foreach ($restStops as $stop) {

                      if (isset($stop['lat']) && isset($stop['lng'])) {
                          $points[] = ['lat' => $stop['lat'], 'lng' => $stop['lng']];
                      }
                  }
              }
        $pickupMatch = $this->isCloseToRoute($points, $request->pickup_lat, $request->pickup_lng, 1.5);
        $dropoffMatch = $this->isCloseToRoute($points, $request->dropoff_lat, $request->dropoff_lng, 3.0);


        if ($pickupMatch && $dropoffMatch) {
            $vehicle = $route->user->vehicle;
            $categorySlug = $vehicle->category->name ?? 'uncategorized';

            if (
                $request->filled('category') &&
                $request->category !== 'all' &&
                strtolower($request->category) !== strtolower($categorySlug)
            ) {
                continue;
            }

            $user = $route->user;
            $pickupPoint = $this->getClosestPoint($points, $request->pickup_lat, $request->pickup_lng);
            $dropoffPoint = $this->getClosestPoint($points, $request->dropoff_lat, $request->dropoff_lng);
        if ($pickupMatch && $dropoffMatch) {
            $vehicle = $route->user->vehicle;
            $categorySlug = $vehicle->category->name ?? 'uncategorized';

            if (
                $request->filled('category') &&
                $request->category !== 'all' &&
                strtolower($request->category) !== strtolower($categorySlug)
            ) {
                continue;
            }

            $user = $route->user;
            $pickupPoint = $this->getClosestPoint($points, $request->pickup_lat, $request->pickup_lng);
            $dropoffPoint = $this->getClosestPoint($points, $request->dropoff_lat, $request->dropoff_lng);

            $startCoords = $route->start_location->getCoordinates();
            $endCoords = $route->end_location->getCoordinates();
            $startLat = $startCoords[1];
            $startLng = $startCoords[0];
            $endLat = $endCoords[1];
            $endLng = $endCoords[0];

            $routeDistanceKm = $this->getDistanceInKm($startLat, $startLng, $endLat, $endLng);
            $pricePerKm = ($routeDistanceKm > 0) ? ($route->price / $routeDistanceKm) : 0;

            $distanceKm = $this->getDistanceInKm(
                $pickupPoint['lat'], $pickupPoint['lng'],
                $dropoffPoint['lat'], $dropoffPoint['lng']
            );

            $priceTrip = round($distanceKm * $pricePerKm, 1);


            $tripData = [
                'route_id' => $route->id,
                'driver' => optional($user)?->only(['id', 'full_name', 'gender', 'profile_image']),
                'vehicle' => [
                    'brand' => optional($vehicle->model->brand)->name ?? null,
                    'model' => optional($vehicle->model)->name ?? null,
                    'plate_number' => $vehicle->plate_number ?? null,
                ],
                'category' => $categorySlug,
                'start_time' => $route->start_time->toDateTimeString(),
                'seats_available' => $route->seats_available,
                'is_ac' => $route->is_ac,
                'is_smoking_allowed' => $route->is_smoking_allowed,
                'pickup_match_point' => $pickupPoint,
                'dropoff_match_point' => $dropoffPoint,
                'pickup_address' => $this->getAddressFromLatLng($pickupPoint['lat'], $pickupPoint['lng']),
                'dropoff_address' => $this->getAddressFromLatLng($dropoffPoint['lat'], $dropoffPoint['lng']),
                'price' => $priceTrip,
                'has_music' => $route->has_music,
                'has_screen_entertainment' => $route->has_screen_entertainment,
                'allow_luggage' => $route->allow_luggage,
                'allowed_gender' => $route->allowed_gender,
                'allowed_age_min' => $route->allowed_age_min,
                'allowed_age_max' => $route->allowed_age_max,
            ];
        }
    }

    return response()->json(responseFormatter(DEFAULT_200, $tripData));
}
}

  private function isCloseToRoute(array $routePoints, float $lat, float $lng, float $maxDistanceKm): bool
    {
        foreach ($routePoints as $point) {
            $distance = $this->haversineDistance($lat, $lng, $point['lat'], $point['lng']);
            if ($distance <= $maxDistanceKm) {
                return true;
            }
        }
        return false;
    }
  
  
  
  private function snapToRoutePolyline($coordinate, $polyline)
{
    $closest = null;
    $minDist = INF;

    foreach ($polyline as $point) {
        $dist = $this->haversineDistance($coordinate[1], $coordinate[0], $point['lat'], $point['lng']);
        if ($dist < $minDist) {
            $minDist = $dist;
            $closest = ['lng' => $point['lng'], 'lat' => $point['lat']];
        }
    }
    // Fallback to original if polyline is empty
    return $closest ?? ['lng' => $coordinate[0], 'lat' => $coordinate[1]];
}
  
  

    private function getClosestPoint(array $routePoints, float $lat, float $lng): array
    {
        $minDistance = INF;
        $closest = null;

        foreach ($routePoints as $point) {
            $distance = $this->haversineDistance($lat, $lng, $point['lat'], $point['lng']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closest = $point;
            }
        }

        return $closest ?? ['lat' => $lat, 'lng' => $lng];
    }

    public function suggestDropoff(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
        }

        $route = CarpoolRoute::latest()->first();
        $points = json_decode($route->route_points, true);

        $isFar = !$this->isCloseToRoute($points, $request->dropoff_lat, $request->dropoff_lng, 5);

        if ($isFar) {
            $suggested = $this->getClosestPoint($points, $request->dropoff_lat, $request->dropoff_lng);
            return response()->json(responseFormatter(DEFAULT_200, [
                'suggested_dropoff' => $suggested,
                'note' => 'Your original drop-off is far. Please consider this nearby point.',
            ]));
        }

        return response()->json(responseFormatter(DEFAULT_200, [
            'message' => 'Your drop-off is acceptable',
        ]));
    }

      public function joinTrip(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:carpool_routes,id',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'seats_count' => 'required|integer|min:1|max:8',
            'fare' =>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
        }

        $route = CarpoolRoute::find($request->route_id);

        if ($route->seats_available < $request->seats_count) {
            return response()->json(responseFormatter(DEFAULT_400, ['message' => 'Not enough seats available']), 403);
        }

        $otp = rand(1000, 9999);

        $passenger = CarpoolPassenger::create([
            'carpool_route_id' => $route->id,
            'user_id' => auth()->id(),
            'pickup_location' => new Point($request->pickup_lat, $request->pickup_lng),
            'dropoff_location' => new Point($request->dropoff_lat, $request->dropoff_lng),
            'seats_count' => $request->seats_count,
            'otp' => $otp,
            'status' => 'pending',
          'fare'=> $request->fare,
          'driver_decision' => 'pending',
          'fare'=> $request->fare,
          'driver_decision' => 'pending'
        ]);

        $route->decrement('seats_available', $request->seats_count);

        return response()->json(responseFormatter(DEFAULT_STORE_200, [
            'passenger_id' => $passenger->id,
            'otp' => $otp,
        ]));
    }

    public function matchPassengerOtp(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'carpool_passenger_id' => 'required|exists:carpool_passengers,id',
        'otp' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }

    $passenger = CarpoolPassenger::where([
        'id' => $request->carpool_passenger_id,
        'user_id' => auth()->id(),
        'otp' => $request->otp
    ])->first();


    if (!$passenger) {
        return response()->json(responseFormatter(OTP_MISMATCH_404), 403);
    }

    $passenger->status = 'waiting';
    $passenger->save();

    return response()->json(responseFormatter(DEFAULT_UPDATE_200));
}




public function startUserRide(Request $request)
{
    $validator = Validator::make($request->all(), [
        'carpool_passenger_id' => 'required|exists:carpool_passengers,id',
        'route_id'=>'required|exists:carpool_routes,id',
    ]);

    if(!$validator)
    {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }
    $passenger=CarpoolPassenger::where('user_id',$request->carpool_passenger_id)
                                ->where('status','waiting')
                                ->where('carpool_route_id',$request->route_id)
                                ->with('user')
                                ->first();
    if(!$passenger)
    {
        return response()->json(responseFormatter(DEFAULT_404, ['message' => 'Passenger not found or not in waiting status']), 404);
    }

    $passenger->status= "onboard";
    $passenger->arrived_at = now();
    $passenger->save();

   sendDeviceNotification(
        fcm_token:$passenger->user->fcm_token,
        title: 'Ride Started',
        description:'Your ride has started successfully.',
        action: 'user_ride_started',
        status: 1 ,
        notificationData:[
            'carpool_passenger_id' => $passenger->user_id,
            'route_id' => $request->route_id,
            'pickup_location' => [
                'lat' => $passenger->pickup_location->latitude,
                'lng' => $passenger->pickup_location->longitude
            ],
            'dropoff_location' => [
                'lat' => $passenger->dropoff_location->latitude,
                'lng' => $passenger->dropoff_location->longitude
            ]
        ]
    );

    return response()->json(
        responseFormatter(DEFAULT_UPDATE_200, [
            'message' => 'Ride started successfully',
            'passenger' => $passenger
        ]));

}


public function cancelPassengerRide(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'carpool_passenger_id' => 'required|exists:carpool_passengers,id',
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }

    $passenger = CarpoolPassenger::where([
        'id' => $request->carpool_passenger_id,
        'user_id' => $request->user_id,
    ])->with('route')
    ->first();

    if (!$passenger) {
        return response()->json(responseFormatter(DEFAULT_404), 403);
    }

    $passenger->status = 'cancelled';
    $passenger->save();
    $passenger->route->increment('seats_available', $passenger->seats_count);

    return response()->json(responseFormatter(DEFAULT_UPDATE_200, ['message' => 'Ride cancelled successfully']));
}

public function dropPassenger(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'carpool_passenger_id' => 'required|exists:carpool_passengers,id',
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }

    $passenger = CarpoolPassenger::where([
        'id' => $request->carpool_passenger_id,
        'user_id' => $request->user_id,
    ])->first();

    if (!$passenger) {
        return response()->json(responseFormatter(DEFAULT_404), 403);
    }

    $passenger->status = 'dropped';
    $passenger->left_at = now();
    $passenger->save();

    $pickupLat = $passenger->pickup_location->latitude;
    $pickupLng = $passenger->pickup_location->longitude;
    $dropoffLat = $passenger->dropoff_location->latitude;
    $dropoffLng = $passenger->dropoff_location->longitude;

    $pickupAddress = $this->getAddressFromLatLng($pickupLat, $pickupLng);
    $dropoffAddress = $this->getAddressFromLatLng($dropoffLat, $dropoffLng);

    return response()->json(responseFormatter(DEFAULT_UPDATE_200, [
        'passenger' => $passenger,
        'pickup_address' => $pickupAddress,
        'dropoff_address' => $dropoffAddress,
    ]));
}


  public function beginTrip(Request $request): JsonResponse
  {
      try {
          $validator = Validator::make($request->all(), [
              'carpool_route_id' => 'required|exists:carpool_routes,id'
          ]);

          if ($validator->fails()) {
              return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
          }

          $user = auth('api')->user();
        
        
          // Check if driver has any ongoing carpool trip
          $ongoingTrip = CarpoolRoute::where('user_id', $user->id)
              ->where('is_trip_started', true)
              ->whereNull('end_time')
              ->first();
          
          if ($ongoingTrip) {
              return response()->json(responseFormatter(DEFAULT_400, [
                  'message' => 'You already have an ongoing trip. Please complete it before starting a new one.'
              ]), 400);
          }
          
          $route = CarpoolRoute::find($request->carpool_route_id);
          
          if (!$route) {
              return response()->json(responseFormatter(DEFAULT_404, ['message' => 'Route not found']), 404);
          }

          if ($route->is_trip_started) {
              return response()->json(responseFormatter(DEFAULT_400, ['message' => 'تم بدء الرحلة مسبقًا']), 400);
          }
          
          Log::info('Starting carpool trip', [
              'route_id' => $route->id,
              'user_id' => $user->id
          ]);
          
          DB::beginTransaction();
          
          // Update route status
          $route->is_trip_started = true;
          $route->trip_started_at = now();
          $route->save();

          Log::info('Route updated successfully', [
              'route_id' => $route->id,
              'is_trip_started' => $route->is_trip_started
          ]);

          // Get all trip requests for this carpool route
          $tripRequests = $this->tripRequestservice->getBy(criteria:[
            'carpool_route_id' => $route->id,
          ],relations: ['customer']);

          Log::info('Found trip requests for carpool route', [
              'route_id' => $route->id,
              'trip_count' => $tripRequests->count()
          ]);

          // Apply the same requestAction logic for all trip requests
          $updatedCount = 0;
          foreach ($tripRequests as $tripRequest) {
              $previousStatus = $tripRequest->current_status;
              
              // Generate OTP for each passenger
              $env = env('APP_MODE');
              $otp = $env != "live" ? '0000' : rand(1000, 9999);
              
              // Set cache for this trip request
              Cache::put($tripRequest->id, ACCEPTED, now()->addHour());
              
              // Calculate driver arrival time
              $driverArrivalTime = getRoutes(
                  originCoordinates: [
                      $tripRequest->coordinate->pickup_coordinates->latitude,
                      $tripRequest->coordinate->pickup_coordinates->longitude
                  ],
                  destinationCoordinates: [
                      $user->lastLocations->latitude,
                      $user->lastLocations->longitude
                  ],
              );
              
              // Update trip request with the same attributes as requestAction
              $attributes = [
                  'column' => 'id',
                  'driver_id' => $user->id,
                  'otp' => $otp,
                  'vehicle_id' => $user->vehicle->id,
                  'vehicle_category_id' => $user->vehicle->category_id,
                  'current_status' => ACCEPTED,
                  'trip_status' => ACCEPTED,
                  'driver_arrival_time' => (double)($driverArrivalTime[0]['duration']) / 60,
              ];
              
              // Update the trip request
              $this->tripRequestservice->update(data: $attributes, id: $tripRequest->id);
              
              // Update trip status
              $tripRequest->tripStatus()->update([
                  'accepted' => now()
              ]);
              
              // Send notification to passenger
              $push = getNotification('driver_is_on_the_way');
              sendDeviceNotification(
                  fcm_token: $tripRequest->customer->fcm_token,
                  title: translate($push['title']),
                  description: translate(textVariableDataFormat(value: $push['description'])),
                  status: $push['status'],
                  ride_request_id: $tripRequest->id,
                  type: $tripRequest->type,
                  action: 'driver_assigned',
                  user_id: $tripRequest->customer->id
              );
              
              // Broadcast event
              try {
                  checkPusherConnection(DriverTripAcceptedEvent::broadcast($tripRequest));
              } catch (Exception $exception) {
                  Log::error('Failed to broadcast DriverTripAcceptedEvent', [
                      'trip_id' => $tripRequest->id,
                      'error' => $exception->getMessage()
                  ]);
              }
              
              $updatedCount++;
              
              Log::info('Trip request updated to accepted for carpool using requestAction logic', [
                  'trip_id' => $tripRequest->id,
                  'carpool_route_id' => $route->id,
                  'new_status' => 'accepted',
                  'previous_status' => $previousStatus,
                  'driver_id' => $user->id,
                  'otp' => $otp
              ]);
          }

          DB::commit();

          Log::info('Carpool trip started successfully', [
              'route_id' => $route->id,
              'passengers_updated' => $updatedCount
          ]);

          // Send notifications to passengers (non-blocking)
          try {
              dispatch(new SendPushNotificationJob(notification: [
                  'title' => 'The Trip Has Started',
                  'description' => 'The driver has started the trip.',
                  'route_id' => $route->id,
                  'action' => 'carpooling_trip_started',
              ], notify: $tripRequests));
          } catch (\Exception $notificationError) {
              Log::error('Error sending notifications: ' . $notificationError->getMessage());
          }

          return response()->json(responseFormatter(DEFAULT_UPDATE_200, [
              'message' => 'the trip is started',
              'trip_started_at' => $route->trip_started_at,
              'passengers_updated' => $updatedCount
          ]));
          
      } catch (\Exception $e) {
          if (DB::transactionLevel() > 0) {
              DB::rollback();
          }
          Log::error('Error starting carpool trip: ' . $e->getMessage(), [
              'route_id' => $request->carpool_route_id ?? 'unknown',
              'file' => $e->getFile(),
              'line' => $e->getLine(),
              'trace' => $e->getTraceAsString()
          ]);
          return response()->json(['success'=>false,'message' => 'Failed to start trip'],500);
      }
  }


    public function endByRoute(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'carpool_route_id' => 'required|exists:carpool_routes,id'
            ]);

            if ($validator->fails()) {
                return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
            }

            $user = auth('api')->user();
            
            $route = CarpoolRoute::where('id', $request->carpool_route_id)->first();
            if (!$route) {
                return response()->json(responseFormatter(DEFAULT_404), 403);
            }

            Log::info('Ending carpool trip', [
                'route_id' => $route->id,
                'user_id' => $user->id
            ]);

            DB::beginTransaction();

            // Update carpool route status
            $route->end_time = now();
            $route->is_trip_started = false; // Mark trip as ended
            $route->save();

            Log::info('Route marked as ended', [
                'route_id' => $route->id,
                'end_time' => $route->end_time
            ]);

            // Get all trip requests for this carpool route (all statuses, not just accepted)
            $tripRequests = $this->tripRequestservice->getBy(criteria: [
                'carpool_route_id' => $route->id,
            ], relations: ['customer']);

            Log::info('Found trip requests for carpool route', [
                'route_id' => $route->id,
                'trip_count' => $tripRequests->count()
            ]);

            // Update all trip requests to completed status
            $updatedCount = 0;
            foreach ($tripRequests as $tripRequest) {
                $previousStatus = $tripRequest->current_status;
                
                // Update trip request with completed status
                $attributes = [
                    'column' => 'id',
                    'current_status' => 'completed',
                    'status' => 'ended', // Use the correct enum value
                    'ended_at' => now(),
                ];
                
                // Update the trip request
                $this->tripRequestservice->update(data: $attributes, id: $tripRequest->id);
                
                // Update trip status table
                if ($tripRequest->tripStatus) {
                    $tripRequest->tripStatus()->update([
                        'completed' => now()
                    ]);
                }
                
                // Send notification to passenger
                $push = getNotification('ride_completed');
                sendDeviceNotification(
                    fcm_token: $tripRequest->customer->fcm_token,
                    title: translate($push['title']),
                    description: translate(textVariableDataFormat(value: $push['description'])),
                    status: $push['status'],
                    ride_request_id: $tripRequest->id,
                    type: $tripRequest->type,
                    action: 'ride_completed',
                    user_id: $tripRequest->customer->id
                );
                
                $updatedCount++;
                
                Log::info('Trip request updated to completed for carpool', [
                    'trip_id' => $tripRequest->id,
                    'carpool_route_id' => $route->id,
                    'new_status' => 'completed',
                    'previous_status' => $previousStatus,
                    'driver_id' => $user->id
                ]);
            }

            // Update driver availability status back to available
            $this->driverDetails->update(attributes: [
                'column' => 'user_id',
                'availability_status' => 'available'
            ], id: $user->id);

            DB::commit();

            Log::info('Carpool trip ended successfully', [
                'route_id' => $route->id,
                'passengers_updated' => $updatedCount
            ]);

            return response()->json(responseFormatter(DEFAULT_UPDATE_200, [
                'message' => 'Carpool trip ended successfully',
                'route_id' => $route->id,
                'passengers_updated' => $updatedCount,
                'end_time' => $route->end_time
            ]));
            
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }
            Log::error('Error ending carpool trip: ' . $e->getMessage(), [
                'route_id' => $request->carpool_route_id ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success'=>false,'message' => 'Failed to end carpool trip'],500);
        }
    }

    public function endTrip(Request $request): JsonResponse
    {
        return $this->endByRoute($request);
    }

public function tripSummary($carpool_route_id): JsonResponse
{
    $route = CarpoolRoute::with('trip.user')->find($carpool_route_id);

    if (!$route) {
        return response()->json(responseFormatter(DEFAULT_404), 403);
    }

    $totalFare = 0;

    $passengers = $route->passengers->map(function ($passenger) use (&$totalFare) {
        $totalFare += $passenger->fare;

       $pickupLat = $passenger->pickup_location->latitude;
    $pickupLng = $passenger->pickup_location->longitude;
    $dropoffLat = $passenger->dropoff_location->latitude;
    $dropoffLng = $passenger->dropoff_location->longitude;

        return [
            'name' => $passenger->user->full_name ?? 'غير معروف',
            'seats' => $passenger->seats_count,
            'fare' => $passenger->fare,
            'status' => $passenger->status,
            'pickup_address' => $this->getAddressFromLatLng($pickupLat, $pickupLng),
            'dropoff_address' => $this->getAddressFromLatLng($dropoffLat, $dropoffLng),
        ];
    });

    return response()->json(responseFormatter(DEFAULT_200, [
        'route_id' => $route->id,
        'start_time' => $route->start_time,
        'end_time' => $route->end_time,
        'status' => $route->status,
        'total_fare' => round($totalFare, 2),
        'passengers' => $passengers,
    ]));
}

  public function getUserTrips(): JsonResponse
{
    $userId = auth()->id();

    $trips = CarpoolPassenger::with(['route.user'])
        ->where('user_id', $userId)
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($trip) {
            $driver = $trip->route->user;
            $startTime = $trip->route->start_time;
          $pickupLocation = $trip->pickup_location;
            $dropoffLocation = $trip->dropoff_location;

            return [
                'route_id' => $trip->carpool_route_id,
                'driver_name' => $driver->full_name ?? 'غير معروف',
                'driver_image' => $driver->profile_image ? asset('storage/' . $driver->profile_image) : null,
                'start_day' => $startTime->format('Y-m-d'),
                'start_hour' => $startTime->format('h:i A'),
    'start_address' => $this->getAddressFromLatLng($pickupLocation->latitude, $pickupLocation->longitude),
                'end_address' => $this->getAddressFromLatLng($dropoffLocation->latitude, $dropoffLocation->longitude),
                          'status' => $trip->driver_decision,
            ];
        });

    return response()->json(responseFormatter(DEFAULT_200, $trips));
}

  public function getDriverCarpoolRidesWithPassengers(Request $request): JsonResponse
{
    // if(!$request->has('carpool_route_id')) {
    //     return response()->json(responseFormatter(DEFAULT_400, ['message' => 'Carpool Route id is required']), 400);
    // }

    $routes = CarpoolRoute::with([
        'user.vehicle.model','user.lastLocations',
        'trip' => function ($q) {
            $q->whereIn('current_status', [PENDING, ACCEPTED, ONGOING, COMPLETED])->with(['coordinate','customer']);
        },
    ])
    ->where('user_id',auth()->id())
    ->orderByDesc('start_time')
    ->get()
    ->map(function ($route) {
        $pickupTime = $route->start_time->format('H:i');
        $amPm = $route->start_time->format('A'); // AM / PM
        $startTime = $route->start_time->format('H:i');
        $endTime = optional($route->end_time)?->format('H:i') ?? '';
        $startMeridiem = $route->start_time->format('A') === 'AM' ? 'صباحًا' : 'مساءً';
        $endMeridiem = optional($route->end_time)?->format('A') === 'AM' ? 'صباحًا' : 'مساءً';
      	$star_coordinates = $route->start_location->getCoordinates(); 
        $end_coordinates = $route->end_location->getCoordinates();

        // Get addresses from coordinates
        $startLat = $star_coordinates[1]; // latitude is second element
        $startLng = $star_coordinates[0]; // longitude is first element
        $endLat = $end_coordinates[1];
        $endLng = $end_coordinates[0];
        
        $startAddress = $this->getAddressFromLatLng($startLat, $startLng) ?? $route->start_address;
        $endAddress = $this->getAddressFromLatLng($endLat, $endLng) ?? $route->end_address;

        $user = $route->user;

        // Collect all passenger coordinates for the driver
$passengerCoordinates = $route->trip->flatMap(function ($trip) {
    // Use the stored carpool_ride_location instead of calculating snapped coordinates
    $carpoolRideLocation = $trip->carpool_ride_location;
    
    $pickupCoordinates = null;
    if ($carpoolRideLocation) {
        $pickupCoordinates = [$carpoolRideLocation->longitude, $carpoolRideLocation->latitude];
    }

    return [
        [
            'type' => 'pickup',
            'passenger_id' => $trip->id,
            'pickup_coordinates' => $pickupCoordinates,
            'address' => $trip->coordinate->pickup_address
        ],
        [
            'type' => 'dropoff',
            'passenger_id' => $trip->id,
            'dropoff_coordinates' => $pickupCoordinates, // Use same coordinates for now
            'address' => $trip->coordinate->destination_address ?? ''
        ]
    ];
})->values();
      
        return [
          'id' => $route->id,
           'name' => $route->user->full_name,
          'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
          'seats' => $route->seats_available,
          'bags_available' => $route->bags_available ?? null,
          'bags_seats_available' => $route->bags_seats_available ?? null,
          'is_smoking_allowed' => (bool) $route->is_smoking_allowed,
          'is_ac' => (bool) $route->is_ac,
          'allowed_gender' => $route->allowed_gender,
          'allowed_age_min' => $route->allowed_age_min,
          'allowed_age_max' => $route->allowed_age_max,
          'has_screen_entertainment' => (bool) $route->has_screen_entertainment,
          'has_music' => (bool) $route->has_music,
          'allow_luggage' => (bool) $route->allow_luggage,
            'start_day' => $route->start_time->format('Y-m-d'),
            'start_hour' => "$pickupTime $amPm",
            'start_address' => $startAddress,
          	'start_coordinates' => $star_coordinates, // [lng, lat]
          	'start_time' => $startTime,
            'end_time' => $endTime,
           'end_coordinates' => $end_coordinates, // [lng, lat]
            'price' => (float) $route->price,
            'available_seats' => $route->seats_available,
            'start_meridiem' => $startMeridiem,
            'end_meridiem' => $endMeridiem,
            'end_address' => $endAddress,
          	'is_trip_started'=>$route->is_trip_started,
            'vehicle_name' => optional($route->user->vehicle->model)->name ?? 'غير محدد',
            'passengers_count' => $route->trip->count(),
            'passenger_coordinates' => $passengerCoordinates,
            'encoded_polyline' => $route->encoded_polyline,
'passengers' => $route->trip->map(function ($trip) {
                // Use the stored carpool_ride_location instead of calculating snapped coordinates
                $carpoolRideLocation = $trip->carpool_ride_location;
                
                // Get coordinates from the stored carpool_ride_location
                $startCoordinates = null;
                if ($carpoolRideLocation) {
                    $startCoordinates = [
                        'lng' => $carpoolRideLocation->longitude,
                        'lat' => $carpoolRideLocation->latitude
                    ];
                    
                    // Debug logging to check coordinate order
                    \Log::info('Carpool ride location debug', [
                        'trip_id' => $trip->id,
                        'carpool_ride_location_object' => $carpoolRideLocation,
                        'longitude' => $carpoolRideLocation->longitude,
                        'latitude' => $carpoolRideLocation->latitude,
                        'startCoordinates' => $startCoordinates
                    ]);
                }

                return [
                  'carpool_trip_id' => $trip->id,
                    'name' => optional($trip->customer)->full_name ?? 'غير معروف',
                    'pickup_address' => $trip->coordinate->pickup_address,
                    'dropoff_address' => $trip->coordinate->destination_address ?? '',
                    'seats_count' => $trip->seats_count ?? 1,
                    'start_coordinates' => $startCoordinates,
					'end_coordinates' => $startCoordinates, // Use same coordinates for now, or you can add a separate field for dropoff if needed
                    'price'=>$trip->actual_fare,
                    'status' => $trip->current_status,
                    // 'estimated_fare' => (float) $trip->estimated_fare,
                    'price'=>(float) $trip->actual_fare,
                  'profile_image' => $trip->profile_image ? asset('storage/' . $trip->user->profile_image) : null,
                ];
            })->values(),
        ];
    });

    return response()->json(responseFormatter(DEFAULT_200, $routes));
}



public function reviewPassengerRequest(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'carpool_passenger_id' => 'required|exists:carpool_passengers,id',
        'decision' => 'required|in:accept,reject',
    ]);

    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }

    $passenger = CarpoolPassenger::with('route')->find($request->carpool_passenger_id);

    if (!$passenger || $passenger->route->user_id !== auth()->id()) {
        return response()->json(responseFormatter(DEFAULT_403, ['message' => 'Unauthorized']), 403);
    }

    if ($passenger->driver_decision !== 'pending') {
        return response()->json(responseFormatter(DEFAULT_400, ['message' => 'Already reviewed']), 400);
    }

    if ($request->decision === 'accept') {
        $passenger->driver_decision = 'accepted';
    } else {
        $passenger->driver_decision = 'rejected';
        $passenger->route->increment('seats_available', $passenger->seats_count);
    }

    $passenger->save();

return response()->json(responseFormatter(DEFAULT_UPDATE_200, ['driver_decision' => $passenger->driver_decision]));
}

    private function getRoutePointsFromAPI(float $startLat, float $startLng, float $endLat, float $endLng): ?array
    {
        $apiKey = 'AIzaSyDONwdMy0pvdwl_Hgk99znBPPqMrzd3d_4';
        $response = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
            'origin' => "$startLat,$startLng",
            'destination' => "$endLat,$endLng",
            'mode' => 'driving',
            'key' => $apiKey,
        ]);

        $data = $response->json();

        if (!$response->ok() || $data['status'] !== 'OK') {
            return null;
        }

        $points = [];
        foreach ($data['routes'][0]['legs'][0]['steps'] as $step) {
            $points[] = [
                'lat' => $step['start_location']['lat'],
                'lng' => $step['start_location']['lng'],
            ];
        }

        $lastStep = end($data['routes'][0]['legs'][0]['steps']);
        $points[] = [
            'lat' => $lastStep['end_location']['lat'],
            'lng' => $lastStep['end_location']['lng'],
        ];

        return $points;
    }

    private function getEncodedPolylineFromAPI(float $startLat, float $startLng, float $endLat, float $endLng): ?string
    {
        $apiKey = 'AIzaSyAFi7yQhENElJAYaMtIIP3qkc_xJ7QLGqM';
        $response = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
            'origin' => "$startLat,$startLng",
            'destination' => "$endLat,$endLng",
            'mode' => 'driving',
            'key' => $apiKey,
        ]);

        $data = $response->json();

        if (!$response->ok() || $data['status'] !== 'OK') {
            return null;
        }

        // Return the encoded polyline from the first route
        return $data['routes'][0]['overview_polyline']['points'] ?? null;
    }




private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371; // km
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;

    $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
    $c = 2 * asin(sqrt($a));

    return $earthRadius * $c;
}

  private function getAddressFromLatLng(float $lat, float $lng): ?string
{
    $cacheKey = "address_{$lat}_{$lng}";
    $address = cache()->get($cacheKey);

    if ($address) {
        return $address;
    }

    $apiKey = 'AIzaSyAFi7yQhENElJAYaMtIIP3qkc_xJ7QLGqM';
    $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
        'latlng' => "$lat,$lng",
        'key' => $apiKey,
        'language' => 'ar'
    ]);

    if (!$response->ok() || $response['status'] !== 'OK') {
        return null;
    }

    $address = $response['results'][0]['formatted_address'] ?? null;

    if ($address) {
        cache()->put($cacheKey, $address, now()->addHour());
    }

    return $address;
}


  private function getDistanceInKm(float $startLat, float $startLng, float $endLat, float $endLng): float
{
    $apiKey = 'AIzaSyAFi7yQhENElJAYaMtIIP3qkc_xJ7QLGqM';
    $response = Http::get("https://maps.googleapis.com/maps/api/directions/json", [
        'origin' => "$startLat,$startLng",
        'destination' => "$endLat,$endLng",
        'mode' => 'driving',
        'key' => $apiKey,
    ]);

    if (!$response->ok() || $response['status'] !== 'OK') {
        return 0;
    }

    return round($response['routes'][0]['legs'][0]['distance']['value'] / 1000, 2); // convert to km
}




  /**
 * Get current active trips for driver with accepted passengers
 */
public function getCurrentTripsWithAcceptedPassengers(): JsonResponse
{
    $driverId = auth()->id();

    $currentTrips = CarpoolRoute::with([
        'user.vehicle.model.brand',
        'trip' => function ($query) {
            $query->where('current_status', 'accepted')
                  ->with(['customer', 'coordinate']);
        }
    ])
    ->where('user_id', $driverId)
    ->where(function ($query) {
        // All future trips (not filtering by is_trip_started)
        $query->where('start_time', '>=', now());
    })
    ->orWhere(function ($query) use ($driverId) {
        // Trips with accepted passengers who aren't dropped yet
        $query->where('user_id', $driverId)
              ->whereHas('trip', function ($tripQuery) {
                  $tripQuery->where('current_status', 'accepted')
                            ->whereIn('current_status', ['accepted', 'ongoing']);
              });
    })
    ->orderBy('start_time', 'asc')
    ->get()
    ->map(function ($route) {
        $vehicle = $route->user->vehicle;
        $acceptedPassengers = $route->trip->map(function ($trip) {
            $pickupCoords = $trip->coordinate->pickup_coordinates->getCoordinates(); // [lng, lat]
            $destinationCoords = $trip->coordinate->destination_coordinates->getCoordinates();

            return [
                'trip_id' => $trip->id,
                'passenger_name' => $trip->customer->full_name ?? 'غير معروف',
                'passenger_phone' => $trip->customer->phone ?? null,
                'profile_image' => $trip->customer->profile_image
                    ? asset('storage/' . $trip->customer->profile_image)
                    : null,
                'required_seats' => $trip->required_seats,
                'fare' => (float) $trip->actual_fare,
                'status' => $trip->current_status,
                'pickup_address' => $this->getAddressFromLatLng($pickupCoords[1], $pickupCoords[0]),
                'destination_address' => $this->getAddressFromLatLng($destinationCoords[1], $destinationCoords[0]),
                'pickup_coordinates' => [
                    'lat' => $pickupCoords[1],
                    'lng' => $pickupCoords[0]
                ],
                'destination_coordinates' => [
                    'lat' => $destinationCoords[1],
                    'lng' => $destinationCoords[0]
                ],
                'otp' => $trip->otp,
                'started_at' => $trip->started_at,
                'ended_at' => $trip->ended_at,
                'payment_method' => $trip->payment_method,
                'payment_status' => $trip->payment_status,
            ];
        });

        $routeCoords = $route->start_location->getCoordinates();
        $endCoords = $route->end_location->getCoordinates();

        // Determine trip status based on start status and passenger statuses
        $tripStatus = 'scheduled';
        if ($route->is_trip_started) {
            $allPassengersCompleted = $acceptedPassengers->every(function ($passenger) {
                return in_array($passenger['status'], ['completed', 'cancelled']);
            });
            $tripStatus = $allPassengersCompleted ? 'completed' : 'ongoing';
        }

        return [
            'route_id' => $route->id,
            'trip_status' => $tripStatus,
            'is_trip_started' => (bool) $route->is_trip_started,
            'start_time' => $route->start_time->format('Y-m-d H:i:s'),
            'end_time' => $route->end_time ? $route->end_time->format('Y-m-d H:i:s') : null,
            'trip_started_at' => $route->trip_started_at,
            'start_address' => $route->start_address,
            'end_address' => $route->end_address,
            'start_coordinates' => [
                'lat' => $routeCoords[1],
                'lng' => $routeCoords[0]
            ],
            'end_coordinates' => [
                'lat' => $endCoords[1],
                'lng' => $endCoords[0]
            ],
            'price' => (float) $route->price,
            'seats_available' => $route->seats_available,
            'total_accepted_passengers' => $acceptedPassengers->count(),
            'total_fare_from_passengers' => $acceptedPassengers->sum('fare'),
            'vehicle_info' => [
                'brand' => $vehicle->model->brand->name ?? null,
                'model' => $vehicle->model->name ?? null,
                'plate_number' => $vehicle->plate_number ?? null,
            ],
            'route_preferences' => [
                'is_ac' => (bool) $route->is_ac,
                'is_smoking_allowed' => (bool) $route->is_smoking_allowed,
                'has_music' => (bool) $route->has_music,
                'has_screen_entertainment' => (bool) $route->has_screen_entertainment,
                'allow_luggage' => (bool) $route->allow_luggage,
                'allowed_gender' => $route->allowed_gender,
                'allowed_age_min' => $route->allowed_age_min,
                'allowed_age_max' => $route->allowed_age_max,
            ],
            'rest_stops' => $route->rest_stops ? json_decode($route->rest_stops, true) : [],
            'accepted_passengers' => $acceptedPassengers,
        ];
    })
    ->filter(function ($trip) {
        // Only return trips that have accepted passengers or are future trips
        return $trip['total_accepted_passengers'] > 0 || $trip['trip_status'] === 'scheduled';
    })
    ->values();

    return response()->json(responseFormatter(DEFAULT_200, [
        'current_trips' => $currentTrips,
        'total_trips' => $currentTrips->count(),
        'upcoming_trips' => $currentTrips->where('trip_status', 'scheduled')->count(),
        'ongoing_trips' => $currentTrips->where('trip_status', 'ongoing')->count(),
        'completed_trips' => $currentTrips->where('trip_status', 'completed')->count(),
    ]));
}


public function getRoutes(Request $request): JsonResponse
{

    $validator = Validator::make($request->all(), [
        'pickup_lat' => 'required|numeric',
        'pickup_lng' => 'required|numeric',
        'dropoff_lat' => 'required|numeric',
        'dropoff_lng' => 'required|numeric',
        'current_lat' => 'required|numeric',
        'current_lng' => 'required|numeric',
    ]);
    if ($validator->fails()) {
        return response()->json(responseFormatter(DEFAULT_400, errorProcessor($validator)), 403);
    }
    $route=getRoutes(
        originCoordinates: [
           $request->pickup_lat,
            $request->pickup_lng
        ],
        destinationCoordinates: [
             $request->dropoff_lat,
            $request->dropoff_lng
        ],
        intermediateCoordinates:[
            $request->current_lat,
            $request->current_lng
        ]
        );
    if (!$route) {
        return response()->json(responseFormatter(DEFAULT_404, ['message' => 'No routes found for the given coordinates.']), 404);
    }
    return response()->json(responseFormatter(DEFAULT_200, [
        'route' => $route,
    ]));
}
public function carpoolRideDetails($carpool_route_id): JsonResponse
    {

            $data = $this->rideDetailsFormation($carpool_route_id);
            if ($data && auth('api')->id() == $data->driver_id) {
                $resource =TripRequestResource::make($data->append('distance_wise_fare'));
                return response()->json(responseFormatter(DEFAULT_200, $resource));
            }
        return response()->json(responseFormatter(DEFAULT_404), 403);
    }

    private function rideDetailsFormation($trip_request_id): mixed
    {
        return $this->trip->getBy(column: 'carpool_route_id', value: $trip_request_id, attributes: [
            'relations' => ['customer', 'vehicleCategory', 'tripStatus', 'time', 'coordinate', 'fee', 'parcel', 'parcelUserInfo', 'parcelRefund'],
            'withAvgRelation' => 'customerReceivedReviews',
            'withAvgColumn' => 'rating',
            'whereInCriteria' => ['current_status' => ACCEPTED, 'trip_status' => ACCEPTED]]);
    }
}

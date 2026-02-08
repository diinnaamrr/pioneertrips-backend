<?php

namespace Modules\TripManagement\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\TripManagement\Entities\CarpoolRoute;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Entities\CarpoolPassenger;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Carbon\Carbon;
use App\Models\User;
use Modules\UserManagement\Entities\User as ModuleUser;

class CarpoolRouteWebController extends Controller
{
    /** Show the HTML form */
    public function create()
    {
        // Get drivers - try both User models to be safe
        try {
            $drivers = ModuleUser::where('user_type', 'driver')->get();
        } catch (\Exception $e) {
            $drivers = User::where('user_type', 'driver')->get();
        }
        
        return view('tripmanagement::carpool_routes.create', compact('drivers'));
    }

    /** Handle form submission */
    public function store(Request $request)
    {
        $data = $request->validate([
            // —— basic locations ——
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'end_lat'   => 'required|numeric',
            'end_lng'   => 'required|numeric',

            // —— timing & seats ——  
            'start_time' => 'required|date_format:Y-m-d\TH:i',
            'seats_available' => 'required|integer|min:1|max:8',

            // —— options / flags ——
            'ride_type' => 'nullable|string|max:50',
            'is_ac' => 'boolean',
            'is_smoking_allowed' => 'boolean',
            'has_music' => 'boolean',
            'has_screen_entertainment' => 'boolean',
            'allow_luggage' => 'boolean',

            // —— demographics ——
            'allowed_gender' => 'required|in:male,female,both',
            'allowed_age_min' => 'nullable|integer|min:10|max:99',
            'allowed_age_max' => 'nullable|integer|min:10|max:99',

            // —— price & vehicle ——
            'price' => 'required|numeric',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'user_id' => 'required|exists:users,id',

            // —— JSON blobs ——
            'route_points' => 'required|json',
            'rest_stops'   => 'nullable|json',

            // —— addresses ——
            'start_address' => 'required|string|max:255',
            'end_address'   => 'required|string|max:255',
            
            // —— trip request fields ——
            'estimated_distance' => 'nullable|numeric',
            'payment_method' => 'nullable|string|in:cash,card,wallet',
            'customer_id' => 'nullable|exists:users,id', // Optional if driver is also customer
        ]);

        DB::beginTransaction();
        
        try {
            // 1. Create CarpoolRoute first
            $carpoolRouteData = [
                'start_location' => new Point($data['start_lat'], $data['start_lng']),
                'end_location' => new Point($data['end_lat'], $data['end_lng']),
                'route_points' => $data['route_points'],
                'rest_stops' => $data['rest_stops'] ?? '[]',
                'start_time' => $data['start_time'],
                'end_time' => Carbon::parse($data['start_time'])->addMinutes(30), // Estimate
                'user_id' => $data['user_id'], // Driver ID
                'vehicle_id' => $data['vehicle_id'],
                'is_ac' => $data['is_ac'] ?? false,
                'is_smoking_allowed' => $data['is_smoking_allowed'] ?? false,
                'seats_available' => $data['seats_available'],
                'start_address' => $data['start_address'],
                'end_address' => $data['end_address'],
                'ride_type' => $data['ride_type'] ?? 'work',
                'has_music' => $data['has_music'] ?? false,
                'allowed_gender' => $data['allowed_gender'],
                'allowed_age_min' => $data['allowed_age_min'] ?? 18,
                'allowed_age_max' => $data['allowed_age_max'] ?? 50,
                'has_screen_entertainment' => $data['has_screen_entertainment'] ?? false,
                'allow_luggage' => $data['allow_luggage'] ?? false,
                'price' => $data['price'],
                'is_trip_started' => false,
                'trip_started_at' => null,
            ];

            $carpoolRoute = CarpoolRoute::create($carpoolRouteData);

            // 2. Create TripRequest with is_carpool = 1
            $tripRequestData = [
                'ride_type' => $data['ride_type'] ?? 'work',
                'customer_id' => $data['customer_id'] ?? $data['user_id'], // If no customer, driver is customer
                'driver_id' => $data['user_id'],
                'gender' => $data['allowed_gender'] == 'both' ? 'male' : $data['allowed_gender'], // Default fallback
                'vehicle_category_id' => $this->getVehicleCategoryId($data['vehicle_id']),
                'vehicle_id' => $data['vehicle_id'],
                'zone_id' => $this->getZoneIdFromCoordinates($data['start_lat'], $data['start_lng']),
                'estimated_fare' => $data['price'],
                'actual_fare' => $data['price'],
                'estimated_distance' => $data['estimated_distance'] ?? 0,
                'paid_fare' => 0, // Not paid yet
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => 'unpaid',
                'type' => 'carpool',
                'status' => 'pending',
                'current_status' => 'pending',
                'is_carpool' => 1,
                'carpool_route_id' => $carpoolRoute->id,
                'required_seats' => 1, // Driver takes 1 seat
                'carpool_ride_location' => new Point($data['start_lat'], $data['start_lng']),
                'otp' => rand(1000, 9999),
                'rise_request_count' => 0,
                'checked' => 0,
                'tips' => 0,
                'is_paused' => 0,
            ];

            $tripRequest = TripRequest::create($tripRequestData);

            // 3. Optionally create CarpoolPassenger entry for the driver (if they want to track themselves)
            // This is optional based on your business logic
            /*
            CarpoolPassenger::create([
                'carpool_route_id' => $carpoolRoute->id,
                'user_id' => $data['user_id'], // Driver as passenger
                'pickup_location' => new Point($data['start_lat'], $data['start_lng']),
                'dropoff_location' => new Point($data['end_lat'], $data['end_lng']),
                'status' => 'accepted', // Driver auto-accepted
                'driver_decision' => 'accepted',
                'fare' => 0, // Driver doesn't pay
                'otp' => rand(1000, 9999),
                'seats_count' => 1,
            ]);
            */

            DB::commit();

            return redirect()->route('carpool.create')
                   ->with('success', 'Carpool route created successfully! Trip ID: #' . $tripRequest->ref_id);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Carpool creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                   ->withInput()
                   ->with('error', 'Failed to create carpool route: ' . $e->getMessage());
        }
    }
    
    /**
     * Get vehicle category ID from vehicle
     */
    private function getVehicleCategoryId($vehicleId)
    {
        if (!$vehicleId) return null;
        
        $vehicle = DB::table('vehicles')->where('id', $vehicleId)->first();
        return $vehicle ? $vehicle->category_id : null;
    }
    
    /**
     * Get zone ID from coordinates (implement based on your zone logic)
     */
    private function getZoneIdFromCoordinates($lat, $lng)
    {
        // Implement your zone detection logic here
        // For now, return a default zone or null
        $defaultZone = DB::table('zones')->first();
        return $defaultZone ? $defaultZone->id : null;
    }
    
    /**
     * Get vehicles by driver
     */
    public function vehiclesByDriver($driver_id)
    {
        $vehicles = DB::table('vehicles')
            ->where('driver_id', $driver_id)
            ->whereNull('deleted_at')
            ->get(['id', 'ref_id', 'licence_plate_number']);

        return response()->json($vehicles);
    }
}
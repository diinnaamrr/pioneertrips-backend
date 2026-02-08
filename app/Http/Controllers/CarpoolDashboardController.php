<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Entities\CarpoolRoute;
use Modules\TripManagement\Entities\CarpoolPassenger;
use Modules\UserManagement\Entities\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CarpoolDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStatistics($request);
        $routes = $this->getAllRoutes($request);
        
        return view('dashboard.carpool-dashboard', compact('stats', 'routes'));
    }
    
    private function getStatistics($request = null)
    {
        $now = Carbon::now();
        
        // Build base query for carpool trip requests
        $statsQuery = TripRequest::where('is_carpool', 1);
        
        // Apply date filters to statistics
        if ($request && $request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $statsQuery->where('created_at', '>=', $startDate);
        }

        if ($request && $request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $statsQuery->where('created_at', '<=', $endDate);
        }
        
        return [
            'total_routes' => (clone $statsQuery)->count(),
            'ongoing_routes' => (clone $statsQuery)->where('current_status', 'ongoing')->count(),
            'completed_routes' => (clone $statsQuery)->where('current_status', 'completed')->count(),
            'upcoming_routes' => (clone $statsQuery)->where('status', 'pending')
                ->whereNull('started_at')->count(),
            'total_passengers' => (clone $statsQuery)->whereNotNull('carpool_route_id')->count(),
            'total_revenue' => (clone $statsQuery)->where('payment_status', 'paid')->sum('paid_fare'),
            'monthly_revenue' => (clone $statsQuery)->where('payment_status', 'paid')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('paid_fare'),
            'started_trips' => (clone $statsQuery)->whereNotNull('started_at')->count(),
            'not_started_trips' => (clone $statsQuery)->whereNull('started_at')->count(),
            'cancelled_trips' => (clone $statsQuery)->where('current_status', 'cancelled')->count()
        ];
    }
    
    private function getAllRoutes($request = null)
    {
        $query = TripRequest::with(['customer', 'driver', 'vehicle', 'vehicleCategory'])
            ->where('is_carpool', 1)
            ->leftJoin('vehicles', 'trip_requests.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('vehicle_brands', 'vehicles.brand_id', '=', 'vehicle_brands.id')
            ->leftJoin('vehicle_models', 'vehicles.model_id', '=', 'vehicle_models.id')
            ->leftJoin('vehicle_categories', 'trip_requests.vehicle_category_id', '=', 'vehicle_categories.id')
            ->leftJoin('users as drivers', 'trip_requests.driver_id', '=', 'drivers.id')
            ->leftJoin('users as customers', 'trip_requests.customer_id', '=', 'customers.id')
            ->leftJoin('carpool_routes', 'trip_requests.carpool_route_id', '=', 'carpool_routes.id')
            ->select('trip_requests.*', 
                     'vehicles.licence_plate_number',
                     'vehicles.fuel_type',
                     'vehicles.transmission',
                     'vehicles.vin_number',
                     'vehicles.licence_expire_date',
                     'vehicles.parcel_weight_capacity',
                     'vehicles.ownership',
                     'vehicles.is_active as vehicle_is_active',
                     'vehicles.vehicle_request_status',
                     'vehicles.documents as vehicle_documents',
                     'vehicle_brands.name as vehicle_brand_name',
                     'vehicle_models.name as vehicle_model_name',
                     'vehicle_categories.name as vehicle_category_name',
                     'drivers.first_name as driver_first_name',
                     'drivers.last_name as driver_last_name',
                     'drivers.email as driver_email',
                     'customers.first_name as customer_first_name',
                     'customers.last_name as customer_last_name',
                     'customers.email as customer_email',
                     'carpool_routes.start_address',
                     'carpool_routes.end_address',
                     'carpool_routes.start_time as route_start_time',
                     'carpool_routes.end_time as route_end_time',
                     'carpool_routes.seats_available',
                     'carpool_routes.price as route_price');

        // Apply date filters
        if ($request && $request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $query->where('trip_requests.created_at', '>=', $startDate);
        }

        if ($request && $request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->where('trip_requests.created_at', '<=', $endDate);
        }

        // Apply status filter
        if ($request && $request->filled('status')) {
            $status = $request->status;
            
            switch ($status) {
                case 'upcoming':
                    $query->where('trip_requests.status', 'pending')
                          ->whereNull('trip_requests.started_at');
                    break;
                case 'ongoing':
                    $query->where('trip_requests.current_status', 'ongoing');
                    break;
                case 'completed':
                    $query->where('trip_requests.current_status', 'completed');
                    break;
                case 'started':
                    $query->whereNotNull('trip_requests.started_at');
                    break;
                case 'cancelled':
                    $query->where('trip_requests.current_status', 'cancelled');
                    break;
            }
        }

        return $query->orderBy('trip_requests.created_at', 'desc')->paginate(15);
    }
    
    public function getRouteDetails($id)
    {
        $route = TripRequest::with(['customer', 'driver', 'vehicle', 'vehicleCategory'])
            ->where('is_carpool', 1)
            ->leftJoin('vehicles', 'trip_requests.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('vehicle_brands', 'vehicles.brand_id', '=', 'vehicle_brands.id')
            ->leftJoin('vehicle_models', 'vehicles.model_id', '=', 'vehicle_models.id')
            ->leftJoin('vehicle_categories', 'trip_requests.vehicle_category_id', '=', 'vehicle_categories.id')
            ->leftJoin('users as drivers', 'trip_requests.driver_id', '=', 'drivers.id')
            ->leftJoin('users as customers', 'trip_requests.customer_id', '=', 'customers.id')
            ->leftJoin('carpool_routes', 'trip_requests.carpool_route_id', '=', 'carpool_routes.id')
            ->select('trip_requests.*', 
                     'vehicles.licence_plate_number',
                     'vehicles.fuel_type',
                     'vehicles.transmission',
                     'vehicles.vin_number',
                     'vehicles.licence_expire_date',
                     'vehicles.parcel_weight_capacity',
                     'vehicles.ownership',
                     'vehicles.is_active as vehicle_is_active',
                     'vehicles.vehicle_request_status',
                     'vehicles.documents as vehicle_documents',
                     'vehicle_brands.name as vehicle_brand_name',
                     'vehicle_models.name as vehicle_model_name',
                     'vehicle_categories.name as vehicle_category_name',
                     'drivers.first_name as driver_first_name',
                     'drivers.last_name as driver_last_name',
                     'drivers.email as driver_email',
                     'customers.first_name as customer_first_name',
                     'customers.last_name as customer_last_name',
                     'customers.email as customer_email',
                     'carpool_routes.start_address',
                     'carpool_routes.end_address',
                     'carpool_routes.start_time as route_start_time',
                     'carpool_routes.end_time as route_end_time',
                     'carpool_routes.seats_available',
                     'carpool_routes.price as route_price')
            ->where('trip_requests.id', $id)
            ->firstOrFail();

        // Get passengers for this carpool route
        $passengers = [];
        if ($route->carpool_route_id) {
            $passengers = CarpoolPassenger::with('user')
                ->where('carpool_route_id', $route->carpool_route_id)
                ->get();
        }
            
        return response()->json([
            'html' => view('dashboard.partials.route-details-modal', compact('route', 'passengers'))->render()
        ]);
    }
}
@if($routes->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped route-table">
            <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Customer</th>
                    <th>Driver</th>
                    <th>Route Info</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Fare</th>
                    <th>Date/Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routes as $route)
                    <tr>
                        <td class="text-center">
                            <strong>#{{ $route->ref_id }}</strong>
                            @if($route->carpool_route_id)
                                <br><small class="text-muted">Route: {{ $route->carpool_route_id }}</small>
                            @endif
                        </td>
                        
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="driver-name">
                                        {{ $route->customer_first_name ?? 'N/A' }} 
                                        {{ $route->customer_last_name ?? '' }}
                                    </div>
                                    <small class="text-muted">{{ $route->customer_email ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="driver-name">
                                        {{ $route->driver_first_name ?? 'Not Assigned' }} 
                                        {{ $route->driver_last_name ?? '' }}
                                    </div>
                                    <small class="text-muted">{{ $route->driver_email ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($route->start_address || $route->end_address)
                                <div class="location-text">
                                    <div><strong>From:</strong> {{ Str::limit($route->start_address, 30) ?? 'N/A' }}</div>
                                    <div><strong>To:</strong> {{ Str::limit($route->end_address, 30) ?? 'N/A' }}</div>
                                    @if($route->seats_available)
                                        <small class="passenger-count">{{ $route->seats_available }} seats</small>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">No route info</span>
                            @endif
                        </td>
                        
                        <td class="text-center">
                            @if($route->vehicle_brand_name || $route->licence_plate_number)
                                <div>
                                    <strong>{{ $route->vehicle_brand_name ?? 'N/A' }}</strong>
                                    <span>{{ $route->vehicle_model_name ?? '' }}</span>
                                </div>
                                <small class="text-muted">{{ $route->licence_plate_number ?? 'N/A' }}</small>
                            @else
                                <span class="text-muted">No Vehicle</span>
                            @endif
                        </td>
                        
                        <td class="text-center">
                            @php
                                $status = $route->current_status;
                                $badgeClass = 'status-badge ';
                                switch($status) {
                                    case 'ongoing':
                                        $badgeClass .= 'status-ongoing';
                                        break;
                                    case 'completed':
                                        $badgeClass .= 'status-completed';
                                        break;
                                    case 'cancelled':
                                        $badgeClass .= 'bg-danger text-white';
                                        break;
                                    default:
                                        $badgeClass .= 'status-upcoming';
                                        $status = $route->started_at ? 'started' : 'pending';
                                }
                            @endphp
                            <span class="{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        
                        <td class="text-center">
                            <span class="status-badge {{ $route->payment_status == 'paid' ? 'status-ongoing' : 'bg-warning text-dark' }}">
                                {{ ucfirst($route->payment_status) }}
                            </span>
                        </td>
                        
                        <td class="text-center">
                            <div>
                                <strong>${{ number_format($route->paid_fare, 2) }}</strong>
                            </div>
                            @if($route->estimated_fare != $route->paid_fare)
                                <small class="text-muted">Est: ${{ number_format($route->estimated_fare, 2) }}</small>
                            @endif
                            @if($route->route_price && $route->route_price != $route->paid_fare)
                                <br><small class="text-info">Route: ${{ number_format($route->route_price, 2) }}</small>
                            @endif
                        </td>
                        
                        <td class="datetime-info">
                            <div class="datetime-date">{{ \Carbon\Carbon::parse($route->created_at)->format('M d, Y') }}</div>
                            <div class="datetime-time">{{ \Carbon\Carbon::parse($route->created_at)->format('h:i A') }}</div>
                            @if($route->started_at)
                                <small class="text-success">Started: {{ \Carbon\Carbon::parse($route->started_at)->format('h:i A') }}</small>
                            @endif
                            @if($route->route_start_time)
                                <br><small class="text-primary">Route: {{ \Carbon\Carbon::parse($route->route_start_time)->format('M d, h:i A') }}</small>
                            @endif
                        </td>
                        
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary"
                                    onclick="viewRouteDetails('{{ $route->id }}')"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $routes->appends(request()->query())->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-car-side fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No carpool routes found</h5>
        <p class="text-muted">
            @if(request('start_date') || request('end_date') || request('status'))
                Try adjusting your filters to see more results.
            @else
                No carpool trips have been created yet.
            @endif
        </p>
    </div>
@endif
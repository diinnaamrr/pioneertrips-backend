<div class="modal-header">
    <h5 class="modal-title" id="routeDetailsModalLabel">
        <i class="fas fa-route me-2"></i>
        Carpool Trip Details - #{{ $route->ref_id }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
        <!-- Trip Information -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Trip Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Trip ID:</strong></td>
                            <td>#{{ $route->ref_id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @php
                                    $status = $route->current_status;
                                    $badgeClass = '';
                                    switch($status) {
                                        case 'ongoing':
                                            $badgeClass = 'bg-success';
                                            break;
                                        case 'completed':
                                            $badgeClass = 'bg-secondary';
                                            break;
                                        case 'cancelled':
                                            $badgeClass = 'bg-danger';
                                            break;
                                        default:
                                            $badgeClass = 'bg-primary';
                                            $status = $route->started_at ? 'started' : 'pending';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Payment Status:</strong></td>
                            <td>
                                <span class="badge {{ $route->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($route->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($route->created_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                        @if($route->started_at)
                        <tr>
                            <td><strong>Started:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($route->started_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                        @if($route->ended_at)
                        <tr>
                            <td><strong>Ended:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($route->ended_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Financial Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Estimated Fare:</strong></td>
                            <td>${{ number_format($route->estimated_fare, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Actual Fare:</strong></td>
                            <td>${{ number_format($route->actual_fare, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Paid Fare:</strong></td>
                            <td><strong>${{ number_format($route->paid_fare, 2) }}</strong></td>
                        </tr>
                        @if($route->route_price)
                        <tr>
                            <td><strong>Route Price:</strong></td>
                            <td>${{ number_format($route->route_price, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>{{ ucfirst($route->payment_method) }}</td>
                        </tr>
                        @if($route->coupon_amount > 0)
                        <tr>
                            <td><strong>Coupon Discount:</strong></td>
                            <td>${{ number_format($route->coupon_amount, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $route->customer_first_name ?? 'N/A' }} {{ $route->customer_last_name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $route->customer_email ?? 'N/A' }}</td>
                        </tr>
                        @if($route->customer_phone)
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $route->customer_phone }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Gender:</strong></td>
                            <td>{{ ucfirst($route->gender) }}</td>
                        </tr>
                        @if($route->required_seats)
                        <tr>
                            <td><strong>Required Seats:</strong></td>
                            <td>{{ $route->required_seats }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Driver Information -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>Driver Information</h6>
                </div>
                <div class="card-body">
                   @if($route->driver_first_name)
    <table class="table table-sm">
      <tr>
    <td><strong>Profile:</strong></td>
    <td>
        <div class="media flex-wrap gap-3 gap-lg-4">
            <div class="avatar avatar-135 rounded">
                <img src="{{ onErrorImage(
                    $route->driver?->profile_image,
                    asset('storage/app/public/driver/profile') . '/' . $route->driver?->profile_image,
                    asset('public/assets/admin-module/img/avatar/avatar.png'),
                    'driver/profile/',
                ) }}"
                    class="rounded dark-support custom-box-size"
                    alt="Driver Profile"
                    style="--size: 136px">
            </div>
        </div>
    </td>
</tr>

        <tr>
            <td><strong>Name:</strong></td>
            <td>
                <a href="{{ url('/admin/driver/show/' . $route->driver_id) }}">
                    {{ $route->driver_first_name }} {{ $route->driver_last_name ?? '' }}
                </a>
            </td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $route->driver_email ?? 'N/A' }}</td>
        </tr>
        @if($route->driver_phone)
        <tr>
            <td><strong>Phone:</strong></td>
            <td>{{ $route->driver_phone }}</td>
        </tr>
        @endif
    </table>
@else
    <p class="text-muted">No driver assigned yet</p>
@endif

                </div>
            </div>
        </div>

        <!-- Vehicle Information -->
        @if($route->vehicle_brand_name || $route->licence_plate_number)
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-car me-2"></i>Vehicle Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Brand:</strong></td>
                            <td>{{ $route->vehicle_brand_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Model:</strong></td>
                            <td>{{ $route->vehicle_model_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Category:</strong></td>
                            <td>{{ $route->vehicle_category_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>License Plate:</strong></td>
                            <td>{{ $route->licence_plate_number ?? 'N/A' }}</td>
                        </tr>
                        @if($route->fuel_type)
                        <tr>
                            <td><strong>Fuel Type:</strong></td>
                            <td>{{ ucfirst($route->fuel_type) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Route Information -->
        @if($route->start_address || $route->end_address)
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-route me-2"></i>Route Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @if($route->start_address)
                        <tr>
                            <td><strong>Start:</strong></td>
                            <td>{{ $route->start_address }}</td>
                        </tr>
                        @endif
                        @if($route->end_address)
                        <tr>
                            <td><strong>End:</strong></td>
                            <td>{{ $route->end_address }}</td>
                        </tr>
                        @endif
                        @if($route->route_start_time)
                        <tr>
                            <td><strong>Scheduled Start:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($route->route_start_time)->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                        @if($route->route_end_time)
                        <tr>
                            <td><strong>Scheduled End:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($route->route_end_time)->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                        @if($route->seats_available)
                        <tr>
                            <td><strong>Available Seats:</strong></td>
                            <td>{{ $route->seats_available }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Passengers Information -->
        @if(isset($passengers) && $passengers->count() > 0)
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Passengers ({{ $passengers->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Seats</th>
                                    <th>Fare</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($passengers as $passenger)
                                <tr>
                                    <td>
                                        {{ $passenger->user->first_name ?? 'N/A' }} 
                                        {{ $passenger->user->last_name ?? '' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $passenger->status == 'onboard' ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($passenger->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $passenger->seats_count }}</td>
                                    <td>${{ number_format($passenger->fare, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($passenger->created_at)->format('M d, h:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i>
        Close
    </button>
</div>
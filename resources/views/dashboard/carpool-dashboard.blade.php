@extends('adminmodule::layouts.master')

@section('title', 'Carpool Routes Dashboard')

@section('styles')
<!-- Keep all your existing styles -->
<style>
    .dashboard-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .dashboard-card.success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .dashboard-card.warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .dashboard-card.info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #333;
    }
    
    .dashboard-card.danger {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        color: #333;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
    
    .status-badge {
        border-radius: 20px;
        padding: 5px 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-ongoing {
        background-color: #28a745;
        color: white;
    }
    
    .status-completed {
        background-color: #6c757d;
        color: white;
    }
    
    .status-upcoming {
        background-color: #007bff;
        color: white;
    }
    
    .status-started {
        background-color: #17a2b8;
        color: white;
    }
    
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .passenger-count {
        background: #17a2b8;
        color: white;
        border-radius: 15px;
        padding: 4px 10px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .location-text {
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .route-table {
        font-size: 0.9rem;
    }
    
    .route-table th {
        background-color: #2c3e50 !important;
        color: white !important;
        font-weight: 600;
        border: none !important;
        padding: 15px 10px !important;
        text-align: center;
    }
    
    .route-table td {
        padding: 15px 10px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
    
    .route-table tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    .route-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    
    .driver-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .datetime-info {
        text-align: center;
        font-size: 0.85rem;
    }
    
    .datetime-date {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .datetime-time {
        color: #6c757d;
        font-size: 0.8rem;
    }

    /* Fix for modal backdrop issue */
    .modal-backdrop {
        transition: opacity 0.15s linear;
    }
    
    body.modal-open {
        overflow: hidden;
    }

    /* Filter Section Styles */
    .filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    
    .filter-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .date-input {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: border-color 0.3s ease;
    }
    
    .date-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .filter-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 10px 25px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .clear-btn {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border: none;
        border-radius: 8px;
        padding: 10px 25px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .clear-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        color: white;
    }

    .active-filter-indicator {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        margin-left: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-car-side me-2"></i>
                Carpool Routes Dashboard
                @if(request('start_date') || request('end_date'))
                    <span class="active-filter-indicator">
                        <i class="fas fa-filter me-1"></i>
                        Filtered
                    </span>
                @endif
            </h1>
            <p class="text-muted mb-0">Comprehensive overview of all carpool activities</p>
        </div>
    </div>

    <!-- Date Range Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Date Range Filter
                    </h6>
                    <form method="GET" action="{{ route('dashboard.carpool.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label fw-bold">Start Date</label>
                            <input type="date" 
                                   class="form-control date-input" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label fw-bold">End Date</label>
                            <input type="date" 
                                   class="form-control date-input" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="{{ request('end_date') }}">
                        </div>
                       <div class="col-md-3">
    <label for="status_filter" class="form-label fw-bold">Status Filter</label>
    <select class="form-control date-input" id="status_filter" name="status">
        <option value="">All Status</option>
        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
        <option value="started" {{ request('status') == 'started' ? 'selected' : '' }}>Started</option>
        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
</div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn filter-btn">
                                    <i class="fas fa-search me-1"></i>
                                    Apply Filter
                                </button>
                                <a href="{{ route('dashboard.carpool.index') }}" class="btn clear-btn">
                                    <i class="fas fa-times me-1"></i>
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    @if(request('start_date') || request('end_date') || request('status'))
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Active Filters:</strong>
                                @if(request('start_date'))
                                    Start Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') }}
                                @endif
                                @if(request('end_date'))
                                    @if(request('start_date')) | @endif
                                    End Date: {{ \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') }}
                                @endif
                                @if(request('status'))
                                    @if(request('start_date') || request('end_date')) | @endif
                                    Status: {{ ucfirst(request('status')) }}
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Routes</div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_routes']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Ongoing Routes</div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['ongoing_routes']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Passengers</div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_passengers']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold">${{ number_format($stats['total_revenue'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Additional Stats Row -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Completed Routes</div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['completed_routes']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Upcoming Routes</div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['upcoming_routes']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Started Trips</div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['started_trips']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-friends stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Cancelled Trips</div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['cancelled_trips']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
    <!-- Routes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card route-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        All Carpool Routes
                        @if($routes->total() > 0)
                            <span class="badge bg-primary ms-2">{{ $routes->total() }} routes</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @include('dashboard.partials.routes-table', ['routes' => $routes])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Route Details Modal -->
<div class="modal fade" id="routeDetailsModal" tabindex="-1" aria-labelledby="routeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div id="routeDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- FIXED JavaScript with proper modal cleanup -->
<script>
// Global modal instance variable
let currentModal = null;

// Function to clean up modal properly
function cleanupModal() {
    // Remove any leftover backdrop
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.remove();
    });
    
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    
    // Reset body style
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    
    // Hide modal
    const modalElement = document.getElementById('routeDetailsModal');
    if (modalElement) {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
    }
    
    currentModal = null;
}

// Define function in global scope
window.viewRouteDetails = function(routeId) {
    console.log('viewRouteDetails called with ID:', routeId);
    
    // Clean up any existing modal first
    cleanupModal();
    
    // Show loading
    document.getElementById('routeDetailsContent').innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>';
    
    // Show modal
    const modalElement = document.getElementById('routeDetailsModal');
    
    if (typeof bootstrap !== 'undefined') {
        currentModal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        currentModal.show();
        
        // Add event listeners for proper cleanup
        modalElement.addEventListener('hidden.bs.modal', function () {
            cleanupModal();
        });
        
    } else {
        // Fallback if bootstrap is not available
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
    }
    
    // Fetch route details
    fetch('/dashboard/carpool/route/' + routeId + '/details')
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            document.getElementById('routeDetailsContent').innerHTML = data.html;
            
            // Add close button event listeners after content is loaded
            setTimeout(function() {
                const closeButtons = document.querySelectorAll('#routeDetailsModal [data-bs-dismiss="modal"], #routeDetailsModal .btn-close');
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        if (currentModal) {
                            currentModal.hide();
                        } else {
                            cleanupModal();
                        }
                    });
                });
            }, 100);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('routeDetailsContent').innerHTML = '<div class="alert alert-danger">Failed to load route details: ' + error.message + '</div>';
        });
};

// Document ready event to handle any cleanup needed
document.addEventListener('DOMContentLoaded', function() {
    // Clean up any leftover modal state
    cleanupModal();
    
    // Add escape key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentModal) {
            currentModal.hide();
        }
    });
    
    // Add click outside modal to close
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop') || e.target.id === 'routeDetailsModal') {
            if (currentModal) {
                currentModal.hide();
            } else {
                cleanupModal();
            }
        }
    });
});

// Test function
console.log('viewRouteDetails function loaded:', typeof window.viewRouteDetails);
</script>
@endsection
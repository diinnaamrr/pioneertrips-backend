@extends('adminmodule::layouts.master')

@section('title', 'Send Notification')

@push('styles')
<style>
    .notification-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: none;
    }
    
    .user-type-selector {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .user-card {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }
    
    .user-card:hover {
        border-color: #007bff;
        background-color: #f8f9ff;
    }
    
    .user-card.selected {
        border-color: #007bff;
        background-color: #e7f3ff;
    }
    
    .users-container {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        background: #fafafa;
    }
    
    .search-container {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
    }
    
    .search-input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 10px 15px;
        background: #fff;
    }
    
    .search-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15);
    }
    
    .btn-primary {
        background: #007bff;
        border-color: #007bff;
        padding: 12px 30px;
        font-weight: 600;
    }
    
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 10px 15px;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .loading {
        text-align: center;
        padding: 20px;
        color: #6c757d;
    }
    
    .error-message {
        text-align: center;
        padding: 20px;
        color: #dc3545;
    }
    
    .no-results {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }
    
    .search-stats {
        font-size: 0.9em;
        color: #6c757d;
        margin-top: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card notification-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        Send Notification
                    </h4>
                </div>

                <form action="{{ route('admin.send-notification.store') }}" method="POST" id="notificationForm">
                    @csrf
                    <div class="card-body">
                        
                        {{-- Success/Error Messages --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Step 1: Select User Type --}}
                        <div class="user-type-selector">
                            <h5 class="mb-3">
                                Step 1: Select User Type
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" 
                                               id="user_type_driver" 
                                               name="user_type" 
                                               value="driver"
                                               class="custom-control-input"
                                               {{ old('user_type') == 'driver' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="user_type_driver">
                                            <strong>Drivers</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" 
                                               id="user_type_customer" 
                                               name="user_type" 
                                               value="customer"
                                               class="custom-control-input"
                                               {{ old('user_type') == 'customer' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="user_type_customer">
                                            <strong>Customers</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Select Recipients --}}
                        <div id="recipientsSection" style="display: none;">
                            <h5 class="mb-3">
                                Step 2: Select Recipients
                            </h5>
                            
                            {{-- Recipient Type Selection --}}
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" 
                                                   id="recipient_all" 
                                                   name="recipient_type" 
                                                   value="all"
                                                   class="custom-control-input"
                                                   {{ old('recipient_type') == 'all' ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="recipient_all">
                                                <strong>Send to All</strong>
                                                <br><small class="text-muted">Send to all users of selected type</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" 
                                                   id="recipient_selected" 
                                                   name="recipient_type" 
                                                   value="selected"
                                                   class="custom-control-input"
                                                   {{ old('recipient_type') == 'selected' ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="recipient_selected">
                                                <strong>Select Specific Users</strong>
                                                <br><small class="text-muted">Choose individual users</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Users List --}}
                            <div id="usersSection" style="display: none;">
                                {{-- Search Field --}}
                                <div class="search-container" id="searchContainer">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control search-input" 
                                                       id="phoneSearch" 
                                                       placeholder="Search by full name or phone number..."
                                                       autocomplete="off">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" id="clearSearch">
                                                        Clear
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="search-stats" id="searchStats"></div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllVisible">
                                                Select All Visible
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><strong>Available Users:</strong> <span id="userCount">0</span></span>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                            Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="selectNone">
                                            Select None
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="users-container" id="usersContainer">
                                    <div class="loading">
                                        Loading users...
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Selected: <span id="selectedCount">0</span> user(s)
                                        <span id="visibleCount" style="display: none;"> | Visible: <span id="visibleUserCount">0</span> user(s)</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Notification Content --}}
                        <div id="contentSection" style="display: none;">
                            <h5 class="mb-3">
                                Step 3: Write Notification
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="title" class="font-weight-bold">
                                            Title <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('title') is-invalid @enderror" 
                                               id="title" 
                                               name="title" 
                                               value="{{ old('title') }}"
                                               placeholder="Enter notification title..."
                                               maxlength="255"
                                               required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="body" class="font-weight-bold">
                                    Message <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('body') is-invalid @enderror" 
                                          id="body" 
                                          name="body" 
                                          rows="4"
                                          placeholder="Enter your notification message..."
                                          maxlength="1000"
                                          required>{{ old('body') }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="charCount">0</span>/1000 characters
                                </small>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    {{-- Submit Button --}}
                    <div class="card-footer text-center" id="submitSection" style="display: none;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    const recipientTypeRadios = document.querySelectorAll('input[name="recipient_type"]');
    const recipientsSection = document.getElementById('recipientsSection');
    const usersSection = document.getElementById('usersSection');
    const contentSection = document.getElementById('contentSection');
    const submitSection = document.getElementById('submitSection');
    const usersContainer = document.getElementById('usersContainer');
    const userCount = document.getElementById('userCount');
    const selectedCount = document.getElementById('selectedCount');
    const visibleCount = document.getElementById('visibleCount');
    const visibleUserCount = document.getElementById('visibleUserCount');
    const bodyTextarea = document.getElementById('body');
    const charCount = document.getElementById('charCount');
    const phoneSearch = document.getElementById('phoneSearch');
    const searchStats = document.getElementById('searchStats');
    const clearSearch = document.getElementById('clearSearch');

    // Store all users for filtering
    let allUsers = [];
    let currentUserType = '';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;

    // Character counter
    bodyTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    // Step 1: User Type Selection
    userTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                currentUserType = this.value;
                recipientsSection.style.display = 'block';
                loadUsers(this.value);
            }
        });
    });

    // Step 2: Recipient Type Selection
    recipientTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                if (this.value === 'selected') {
                    usersSection.style.display = 'block';
                } else {
                    usersSection.style.display = 'none';
                }
                contentSection.style.display = 'block';
                submitSection.style.display = 'block';
            }
        });
    });

    // Phone Search functionality
    phoneSearch.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        filterUsers(searchTerm);
    });

    // Clear search
    clearSearch.addEventListener('click', function() {
        phoneSearch.value = '';
        filterUsers('');
        phoneSearch.focus();
    });

    // Filter users based on phone search
function filterUsers(searchTerm) {
    const userCards = document.querySelectorAll('.user-card');
    let visibleUsersCount = 0;
    const lowerSearch = searchTerm.trim().toLowerCase();

    userCards.forEach(card => {
        const phoneElement = card.querySelector('.user-phone');
        const labelElement = card.querySelector('label.custom-control-label');

        const phone = phoneElement ? phoneElement.textContent.trim().toLowerCase() : '';
        const fullName = labelElement ? labelElement.textContent.trim().toLowerCase().split('\n')[0] : '';

        if (lowerSearch === '' || phone.includes(lowerSearch) || fullName.includes(lowerSearch)) {
            card.style.display = 'block';
            visibleUsersCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Update search stats
    if (searchTerm === '') {
        searchStats.textContent = '';
        visibleCount.style.display = 'none';
    } else {
        searchStats.textContent = `Found ${visibleUsersCount} user(s) matching "${searchTerm}"`;
        visibleCount.style.display = 'inline';
        visibleUserCount.textContent = visibleUsersCount;
    }

    // Show no results message if needed
    if (visibleUsersCount === 0 && searchTerm !== '') {
        if (!document.querySelector('.no-results')) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `No users found matching "${searchTerm}"`;
            usersContainer.appendChild(noResults);
        }
    } else {
        const noResults = document.querySelector('.no-results');
        if (noResults) {
            noResults.remove();
        }
    }
}


    // Load users based on type
    function loadUsers(userType) {
        usersContainer.innerHTML = '<div class="loading">Loading users...</div>';
        
        const requestData = {
            user_type: userType,
            _token: csrfToken
        };

        fetch('{{ route("admin.send-notification.get-users") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response:', data); // Debug log
            if (data.success) {
                allUsers = data.users;
                displayUsers(data.users);
                userCount.textContent = data.count;
                // Reset search when new users are loaded
                phoneSearch.value = '';
                filterUsers('');
            } else {
                usersContainer.innerHTML = `<div class="error-message">
                    ${data.message || 'Failed to load users'}
                </div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            usersContainer.innerHTML = `<div class="error-message">
                Error loading users: ${error.message}
                <br><small>Please check console for details</small>
            </div>`;
        });
    }

    // Display users
    function displayUsers(users) {
        if (!users || users.length === 0) {
            usersContainer.innerHTML = '<div class="text-center text-muted py-3">No users found for this type</div>';
            return;
        }

        let html = '';
        users.forEach(user => {
            const fullName = user.full_name || 'No Name';
            const email = user.email || 'No Email';
            const phone = user.phone || 'No Phone';
            
            html += `
                <div class="user-card">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" 
                               class="custom-control-input user-checkbox" 
                               id="user_${user.id}" 
                               name="user_ids[]" 
                               value="${user.id}">
                        <label class="custom-control-label" for="user_${user.id}">
                            <strong>${fullName}</strong>
                            <br><small class="text-muted">${email} | <span class="user-phone">${phone}</span></small>
                        </label>
                    </div>
                </div>
            `;
        });

        usersContainer.innerHTML = html;
        
        // Add event listeners for checkboxes
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });
    }

    // Select All/None functionality
    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    });

    document.getElementById('selectNone').addEventListener('click', function() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    });

    // Select All Visible functionality
    document.getElementById('selectAllVisible').addEventListener('click', function() {
        document.querySelectorAll('.user-card').forEach(card => {
            if (card.style.display !== 'none') {
                const checkbox = card.querySelector('.user-checkbox');
                if (checkbox) {
                    checkbox.checked = true;
                }
            }
        });
        updateSelectedCount();
    });

    // Update selected count
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        selectedCount.textContent = checked;
    }

    // Form validation
    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        const userType = document.querySelector('input[name="user_type"]:checked');
        const recipientType = document.querySelector('input[name="recipient_type"]:checked');
        
        if (!userType) {
            e.preventDefault();
            alert('Please select user type (Driver or Customer)');
            return;
        }
        
        if (!recipientType) {
            e.preventDefault();
            alert('Please select who to send notification to');
            return;
        }
        
        if (recipientType.value === 'selected') {
            const checkedUsers = document.querySelectorAll('.user-checkbox:checked');
            if (checkedUsers.length === 0) {
                e.preventDefault();
                alert('Please select at least one user');
                return;
            }
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = 'Sending...';
        submitBtn.disabled = true;
    });

    // Initialize on page load
    const checkedUserType = document.querySelector('input[name="user_type"]:checked');
    if (checkedUserType) {
        currentUserType = checkedUserType.value;
        recipientsSection.style.display = 'block';
        loadUsers(checkedUserType.value);
    }

    const checkedRecipientType = document.querySelector('input[name="recipient_type"]:checked');
    if (checkedRecipientType) {
        if (checkedRecipientType.value === 'selected') {
            usersSection.style.display = 'block';
        }
        contentSection.style.display = 'block';
        submitSection.style.display = 'block';
    }

    // Initialize character count
    charCount.textContent = bodyTextarea.value.length;
});
</script>
@endsection
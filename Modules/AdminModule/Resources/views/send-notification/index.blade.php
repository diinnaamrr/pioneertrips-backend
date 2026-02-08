@extends('adminmodule::layouts.master')

@section('title', 'Sent Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Sent Notifications
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Send New Notification
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($notifications && $notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Priority</th>
                                        <th>Type</th>
                                        <th>Recipients</th>
                                        <th>Sent At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        <tr>
                                            <td>{{ $notification->id }}</td>
                                            <td>
                                                <strong>{{ $notification->title }}</strong>
                                            </td>
                                            <td>
                                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ Str::limit($notification->message, 50) }}
                                                </div>
                                            </td>
                                            <td>
                                                @switch($notification->priority)
                                                    @case('high')
                                                        <span class="badge badge-danger">🔴 High</span>
                                                        @break
                                                    @case('medium')
                                                        <span class="badge badge-warning">🟡 Medium</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-success">🟢 Low</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @switch($notification->type)
                                                    @case('urgent')
                                                        🚨 Urgent
                                                        @break
                                                    @case('maintenance')
                                                        🔧 Maintenance
                                                        @break
                                                    @case('update')
                                                        🆙 Update
                                                        @break
                                                    @default
                                                        📢 General
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($notification->recipient_type === 'all')
                                                    <span class="badge badge-info">All Users</span>
                                                @else
                                                    <span class="badge badge-secondary">Specific User</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i') : 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @if($notification->is_seen)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Seen
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination if needed --}}
                        @if(method_exists($notifications, 'links'))
                            <div class="d-flex justify-content-center">
                                {{ $notifications->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No notifications sent yet</h5>
                            <p class="text-muted">Start by sending your first notification!</p>
                            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>
                                Send First Notification
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<?php

namespace Modules\AdminModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\User;
use Modules\AdminModule\Service\Interface\AdminNotificationServiceInterface;
use Illuminate\Support\Facades\Log;

class SendNotificationController extends Controller
{
    protected $adminNotificationService;

    public function __construct(AdminNotificationServiceInterface $adminNotificationService)
    {
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * Show the form for sending notifications
     */
    public function create(): View
    {
        return view('adminmodule::send-notification.create');
    }

    /**
     * Get users based on user_type (AJAX)
     */
    public function getUsersByType(Request $request)
    {
        try {
            $request->validate([
                'user_type' => 'required|in:driver,customer'
            ]);

            $users = User::select('id', 'full_name', 'first_name', 'last_name', 'email', 'phone', 'user_type', 'fcm_token')
                        ->where('user_type', $request->user_type)
                        ->where('is_active', 1)
                        ->whereNull('deleted_at')
                        ->orderBy('full_name')
                        ->get();

            // Handle cases where full_name might be null
            $users = $users->map(function($user) {
                if (empty($user->full_name)) {
                    $user->full_name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                    if (empty($user->full_name)) {
                        $user->full_name = 'User ID: ' . $user->id;
                    }
                }
                return $user;
            });

            return response()->json([
                'success' => true,
                'users' => $users,
                'count' => $users->count(),
                'message' => 'Users loaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading users: ' . $e->getMessage(),
                'users' => [],
                'count' => 0
            ]);
        }
    }

    /**
     * Send notification to selected users with BULLETPROOF timeout prevention
     */
    public function store(Request $request): RedirectResponse
    {
        // BULLETPROOF TIMEOUT PREVENTION - NO MORE TIMEOUTS EVER!
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);
        ini_set('memory_limit', '2G');
        
        // Disable any PHP timeouts
        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
        
        $request->validate([
            'user_type' => 'required|in:driver,customer',
            'recipient_type' => 'required|in:all,selected',
            'user_ids' => 'required_if:recipient_type,selected|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ], [
            'user_type.required' => 'Please select user type (Driver or Customer)',
            'recipient_type.required' => 'Please select who to send notification to',
            'user_ids.required_if' => 'Please select at least one user',
            'title.required' => 'Please enter notification title',
            'body.required' => 'Please enter notification message',
        ]);

        try {
            $currentDateTime = now();
            
            $baseData = [
                'title' => $request->title,
                'message' => $request->body,
                'sender_id' => auth()->id(),
                'sender_name' => 'yassin287',
                'sender_email' => 'yassin287@drivoeg.com',
                'type' => 'general',
                'priority' => 'medium',
                'is_seen' => 0,
                'is_read' => 0,
                'sent_at' => $currentDateTime,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
                'model' => 'App\\Models\\User',
                'delivery_status' => 'sent',
                'recipient_type' => 'individual',
            ];

            $sentCount = 0;
            $pushSentCount = 0;

            if ($request->recipient_type === 'all') {
                // BULLETPROOF chunking with aggressive timeout prevention
                Log::info("🚀 Starting BULLETPROOF bulk notification to all {$request->user_type}s - NO TIMEOUTS!");
                
                $chunkSize = 20; // Super small chunks for maximum safety
                
                User::where('user_type', $request->user_type)
                   ->where('is_active', 1)
                   ->whereNull('deleted_at')
                   ->chunk($chunkSize, function ($users) use ($baseData, $request, &$sentCount, &$pushSentCount) {
                       
                       // Reset timeout for each chunk
                       set_time_limit(0);
                       ini_set('max_execution_time', 0);
                       
                       Log::info("🔄 Processing chunk with " . $users->count() . " users");
                       
                       foreach ($users as $user) {
                           try {
                               // Reset timeout for each user
                               set_time_limit(0);
                               ini_set('max_execution_time', 0);
                               
                               $fullName = $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                               if (empty($fullName)) {
                                   $fullName = 'User ID: ' . $user->id;
                               }
                               
                               $notificationData = array_merge($baseData, [
                                   'recipient_id' => $user->id,
                                   'recipient_name' => $fullName,
                                   'recipient_email' => $user->email ?? '',
                                   'model_id' => $user->id,
                               ]);

                               $this->adminNotificationService->send($notificationData);
                               $sentCount++;

                               if ($user->fcm_token) {
                                   try {
                                       // Set timeout for push notification only
                                       set_time_limit(60); // 60 seconds for push
                                       
                                       sendDeviceNotification(
                                           fcm_token: $user->fcm_token,
                                           title: $request->title,
                                           description: $request->body,
                                           status: 'active',
                                           ride_request_id: null,
                                           type: 'admin_notification',
                                           action: 'admin_message',
                                           user_id: $user->id
                                       );
                                       $pushSentCount++;
                                       
                                       // Reset timeout after push
                                       set_time_limit(0);
                                       ini_set('max_execution_time', 0);
                                       
                                   } catch (\Exception $pushError) {
                                       Log::error("❌ Failed to send push notification to user {$user->id}: " . $pushError->getMessage());
                                       // Reset timeout after error
                                       set_time_limit(0);
                                       ini_set('max_execution_time', 0);
                                   }
                               }
                           } catch (\Exception $userError) {
                               Log::error("❌ Failed to process user {$user->id}: " . $userError->getMessage());
                               // Reset timeout after error
                               set_time_limit(0);
                               ini_set('max_execution_time', 0);
                           }
                       }
                       
                       Log::info("✅ Chunk completed. Total processed so far: {$sentCount}, Push notifications: {$pushSentCount}");
                       
                       // Force garbage collection
                       if (function_exists('gc_collect_cycles')) {
                           gc_collect_cycles();
                       }
                       
                       // Longer delay between chunks
                       usleep(500000); // 0.5 second delay
                   });

                $userTypeText = ucfirst($request->user_type) . 's';
                $message = "🚀 Notification sent successfully to {$sentCount} {$userTypeText}! Push notifications sent to {$pushSentCount} devices.";
                
                Log::info("🎉 BULLETPROOF notification completed! Total users: {$sentCount}, Push notifications: {$pushSentCount}");
                
            } else {
                // Send to selected users
                foreach ($request->user_ids as $userId) {
                    $user = User::find($userId);
                    if ($user) {
                        $fullName = $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                        if (empty($fullName)) {
                            $fullName = 'User ID: ' . $user->id;
                        }
                        
                        $notificationData = array_merge($baseData, [
                            'recipient_id' => $user->id,
                            'recipient_name' => $fullName,
                            'recipient_email' => $user->email ?? '',
                            'model_id' => $user->id,
                        ]);

                        $this->adminNotificationService->send($notificationData);
                        $sentCount++;

                        if ($user->fcm_token) {
                            try {
                                sendDeviceNotification(
                                    fcm_token: $user->fcm_token,
                                    title: $request->title,
                                    description: $request->body,
                                    status: 'active',
                                    ride_request_id: null,
                                    type: 'admin_notification',
                                    action: 'admin_message',
                                    user_id: $user->id
                                );
                                $pushSentCount++;
                            } catch (\Exception $pushError) {
                                Log::error("Failed to send push notification to user {$user->id}: " . $pushError->getMessage());
                            }
                        }
                    }
                }

                $message = "Notification sent successfully to {$sentCount} selected user(s)! Push notifications sent to {$pushSentCount} devices.";
            }

            return redirect()->route('admin.send-notification.create')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('❌ Notification send error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }
}
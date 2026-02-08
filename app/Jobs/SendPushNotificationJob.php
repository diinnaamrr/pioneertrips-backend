<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected $notification,
        protected $notify = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->notify) {
            Log::info('Notify list is set. Iterating through notify users.');
            Log::info($this->notify);

            foreach ($this->notify as $user) {
                Log::info('Checking user:', ['user_id' => $user->id]);

           if ($user) {
    if (empty($user->fcm_token)) {
        Log::warning('FCM token is missing. Skipping push notification.', ['user_id' => $user->id]);
        continue;
    }

    Log::info('User is active. Sending notification.', [
        'fcm_token' => $user->fcm_token,
        'title' => $this->notification['title'],
        'description' => $this->notification['description'],
    ]);

    sendDeviceNotification(
        fcm_token: $user->fcm_token,
        title: $this->notification['title'],
        description: $this->notification['description'],
        status: $this->notification['status'] ?? null,
        image: $this->notification['image'] ?? null,
        ride_request_id: $this->notification['ride_request_id'] ?? null,
        type: $this->notification['type'] ?? null,
        action: $this->notification['action'] ?? null,
        user_id: $user->user->id ?? null,
    );
} else {
                    Log::info('User is not active. Skipping.', ['user_id' => $user->user?->id]);
                }
            }
        } else {
            Log::info('Notify list is not set. Using notification["user"].');

          foreach ($this->notification['user'] as $user) {
    if (empty($user['fcm_token'])) {
        Log::warning('FCM token is missing. Skipping push notification.', ['user_id' => $user['user_id'] ?? null]);
        continue;
    }

    Log::info('Sending notification to user from notification["user"] array.', [
        'fcm_token' => $user['fcm_token'],
        'user_id' => $user['user_id'] ?? null,
    ]);

    sendDeviceNotification(
        fcm_token: $user['fcm_token'],
        title: $this->notification['title'],
        description: $this->notification['description'],
        status: $this->notification['status'] ?? null,
        image: $this->notification['image'] ?? null,
        ride_request_id: $this->notification['ride_request_id'] ?? null,
        type: $this->notification['type'] ?? null,
        action: $this->notification['action'] ?? null,
        user_id: $user['user_id'] ?? null,
    );
}

        }

    }
}

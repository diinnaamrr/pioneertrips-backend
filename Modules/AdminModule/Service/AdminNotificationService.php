<?php

namespace Modules\AdminModule\Service;

use App\Repository\EloquentRepositoryInterface;
use App\Service\BaseService;
use Illuminate\Database\Eloquent\Model;
use Modules\AdminModule\Repository\AdminNotificationRepositoryInterface;
use Modules\AdminModule\Service\Interface\AdminNotificationServiceInterface;

class AdminNotificationService extends BaseService implements Interface\AdminNotificationServiceInterface
{
    protected $adminNotificationRepository;

    public function __construct(AdminNotificationRepositoryInterface $adminNotificationRepository)
    {
        parent::__construct($adminNotificationRepository);
        $this->adminNotificationRepository = $adminNotificationRepository;
    }

    /**
     * Send/Create a new notification
     */
    public function send(array $data): ?Model
    {
        // Add all required fields with default values
        $notificationData = array_merge([
            'sender_id' => auth()->id() ?? 1, // yassin287's ID
            'sender_name' => 'yassin287',
            'sender_email' => auth()->user()->email ?? 'yassin287@drivoeg.com',
            'recipient_type' => 'individual',
            'recipient_id' => null,
            'recipient_name' => '',
            'recipient_email' => '',
            'title' => 'Notification',
            'message' => '',
            'type' => 'general',
            'priority' => 'medium',
            'is_seen' => 0,
            'is_read' => 0,
            'is_archived' => 0,
            'is_deleted' => 0,
            'seen_at' => null,
            'read_at' => null,
            'sent_at' => now()->format('Y-m-d H:i:s'),
            'metadata' => json_encode([]),
            'action_url' => null,
            'icon' => 'bell',
            'color' => 'primary',
            'delivery_status' => 'sent',
            'scheduled_at' => null,
            'retry_count' => 0,
            'failure_reason' => null,
            'model' => 'App\\Models\\User', // Required field
            'model_id' => auth()->id() ?? 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ], $data);

        return $this->adminNotificationRepository->create($notificationData);
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultiple(array $userIds, array $data): array
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $individualData = array_merge($data, [
                'recipient_id' => $userId,
                'model_id' => $userId, // Set model_id to user ID
            ]);
            
            $notifications[] = $this->send($individualData);
        }
        
        return $notifications;
    }

    /**
     * Get notifications sent by yassin287
     */
    public function getSentNotifications(int $senderId = null): mixed
    {
        $criteria = [];
        
        if ($senderId) {
            $criteria['sender_id'] = $senderId;
        }
        
        return $this->adminNotificationRepository->getBy(
            criteria: $criteria,
            orderBy: ['created_at' => 'desc']
        );
    }

    public function update(int|string $id, array $data = []): ?Model
    {
        if ($id == 0) {
            $this->adminNotificationRepository->updatedBy(criteria:['is_seen' => 0 ],data:$data);
            $notification = $this->adminNotificationRepository->getBy(orderBy: ['created_at'=>'desc'])->first();
        } else {
            $notification = $this->adminNotificationRepository->update(id: $id, data: $data);
        }
        return $notification;
    }
}
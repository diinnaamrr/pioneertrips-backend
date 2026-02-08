<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhySmsService
{
    private $apiToken;
    private $baseUrl = 'https://api.whysms.com/api/v1/send';

    public function __construct()
    {
        $this->apiToken = config('services.whysms.api_token', '1046|WGTMJFtNKsY2oZheN06qL1cviTrZjGBYX6AX0mSP1823eed6');
    }

    /**
     * إرسال رسالة SMS عبر WhySMS API
     */
    public function sendSms($recipient, $message, $senderId = 'EasyTech')
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'api_token' => $this->apiToken,
                'recipient' => $recipient,
                'sender_id' => $senderId,
                'type' => 'plain',
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info('WhySMS: Message sent successfully', [
                    'recipient' => $recipient,
                    'response' => $response->json()
                ]);
                return 'success';
            } else {
                Log::error('WhySMS: Failed to send message', [
                    'recipient' => $recipient,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return 'error';
            }
        } catch (\Exception $e) {
            Log::error('WhySMS: Exception occurred', [
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            return 'error';
        }
    }

    /**
     * إرسال OTP عبر WhySMS
     */
    public function sendOtp($recipient, $otp, $senderId = 'EasyTech')
    {
        $message = "Your OTP code is: {$otp}. Please do not share this code with anyone.";
        return $this->sendSms($recipient, $message, $senderId);
    }
}
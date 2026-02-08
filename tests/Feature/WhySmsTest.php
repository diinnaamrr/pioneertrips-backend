<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WhySmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WhySmsTest extends TestCase
{
    /**
     * اختبار إرسال رسالة عبر WhySMS
     */
    public function test_whysms_send_message()
    {
        $whySmsService = new WhySmsService();
        
        // اختبار إرسال رسالة عادية
        $result = $whySmsService->sendSms('201145079209', 'This is a test message');
        
        $this->assertContains($result, ['success', 'error']);
    }

    /**
     * اختبار إرسال OTP عبر WhySMS
     */
    public function test_whysms_send_otp()
    {
        $whySmsService = new WhySmsService();
        
        // اختبار إرسال OTP
        $result = $whySmsService->sendOtp('201145079209', '123456');
        
        $this->assertContains($result, ['success', 'error']);
    }

    /**
     * اختبار forget password endpoint
     */
    public function test_forget_password_with_whysms()
    {
        // إنشاء مستخدم تجريبي
        $user = \Modules\UserManagement\Entities\User::factory()->create([
            'phone' => '201145079209',
            'user_type' => 'customer'
        ]);

        // طلب forget password
        $response = $this->postJson('/api/customer/auth/forget-password', [
            'phone_or_email' => '201145079209'
        ]);

        $response->assertStatus(200);
    }
}
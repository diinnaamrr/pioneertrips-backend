<?php

namespace Modules\AuthManagement\Service;

use App\Service\BaseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Modules\BusinessManagement\Repository\SettingRepositoryInterface;
use Modules\Gateways\Traits\SmsGateway;
use Modules\UserManagement\Repository\OtpVerificationRepositoryInterface;
use Modules\UserManagement\Repository\UserRepositoryInterface;

class AuthService extends BaseService implements Interface\AuthServiceInterface
{
    use SmsGateway;

    protected $userRepository;
    protected $otpVerificationRepository;
    protected $settingRepository;

    public function __construct(UserRepositoryInterface $userRepository, OtpVerificationRepositoryInterface $otpVerificationRepository, SettingRepositoryInterface $settingRepository)
    {
        parent::__construct($userRepository);
        $this->userRepository = $userRepository;
        $this->otpVerificationRepository = $otpVerificationRepository;
        $this->settingRepository = $settingRepository;
    }

    public function checkClientRoute($request)
    {
        $route = str_contains($request->route()?->getPrefix(), 'customer');
        $userType = $route ? CUSTOMER : DRIVER;
        $phoneOrEmail = $request->phone_or_email;
        
        // البحث بالهاتف أولاً (exact match)
        $user = $this->userRepository->findOneBy(criteria: ['phone' => $phoneOrEmail, 'user_type' => $userType]);
        
        // إذا لم يجد بالهاتف، جرب بدون علامة +
        if (!$user && str_starts_with($phoneOrEmail, '+')) {
            $phoneWithoutPlus = ltrim($phoneOrEmail, '+');
            $user = $this->userRepository->findOneBy(criteria: ['phone' => $phoneWithoutPlus, 'user_type' => $userType]);
        }
        
        // إذا لم يجد، جرب مع علامة +
        if (!$user && !str_starts_with($phoneOrEmail, '+') && is_numeric($phoneOrEmail)) {
            $phoneWithPlus = '+' . $phoneOrEmail;
            $user = $this->userRepository->findOneBy(criteria: ['phone' => $phoneWithPlus, 'user_type' => $userType]);
        }
        
        // البحث بـ LIKE للأرقام المشابهة
        if (!$user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneOrEmail);
            if (strlen($cleanPhone) >= 10) {
                $lastDigits = substr($cleanPhone, -10);
                $users = $this->userRepository->getBy(criteria: ['user_type' => $userType]);
                foreach ($users as $u) {
                    $userCleanPhone = preg_replace('/[^0-9]/', '', $u->phone);
                    if (str_ends_with($userCleanPhone, $lastDigits)) {
                        $user = $u;
                        break;
                    }
                }
            }
        }
        
        // إذا لم يجد بالهاتف، ابحث بالإيميل
        if (!$user && filter_var($phoneOrEmail, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findOneBy(criteria: ['email' => $phoneOrEmail, 'user_type' => $userType]);
        }
        
        return $user;
    }

    private function generateOtp($user, $otp)
    {
        $expires_at = env('APP_MODE') == 'live' ? 3 : 1000;
        $attributes = [
            'phone_or_email' => $user->phone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes($expires_at),
        ];
        $verification = $this->otpVerificationRepository->findOneBy(['phone_or_email' => $user->phone]);
        if ($verification) {
            $verification->delete();
        }
        $this->otpVerificationRepository->create(data: $attributes);
        return $otp;
    }

    public function updateLoginUser(string|int $id, array $data): ?Model
    {
        return $this->userRepository->update(id: $id, data: $data);
    }


    public function sendOtpToClient($user, $type = null)
    {
        if ($type == 'trip') {
            $otp = env('APP_MODE') == 'live' ? rand(1000, 9999) : '0000';
            if (self::sendWhySms($user->phone, $otp) == "success") {
                return $this->generateOtp($user, $otp);
            }
            return $this->generateOtp($user, $otp);
        }
        
        $otp = env('APP_MODE') == 'live' ? rand(100000, 999999) : '000000';
        
        // محاولة إرسال عبر WhySMS
        if (self::sendWhySms($user->phone, $otp) == "success") {
            return $this->generateOtp($user, $otp);
        }
        
        // إذا فشل WhySMS، استخدم SMS providers الأخرى
        $dataValues = $this->settingRepository->getBy(criteria: ['settings_type' => SMS_CONFIG]);
        if ($dataValues->where('live_values.status', 1)->isNotEmpty()) {
            $otp = rand(100000, 999999);
        } else {
            $otp = '000000';
        }

        if (self::send($user->phone, $otp) == "not_found") {
            return $this->generateOtp($user, '000000');
        }
        return $this->generateOtp($user, $otp);
    }
    
    private function sendWhySms($phone, $otp)
    {
        try {
            $apiToken = '1046|WGTMJFtNKsY2oZheN06qL1cviTrZjGBYX6AX0mSP1823eed6';
            $senderId = 'EasyTech';
            $message = "Your OTP code is: {$otp}. Please do not share this code with anyone.";
            
            // إزالة علامة + من الرقم
            $cleanPhone = ltrim($phone, '+');
            
            \Log::info('Attempting to send SMS via WhySMS', [
                'phone' => $cleanPhone,
                'otp' => $otp,
                'message' => $message
            ]);
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://bulk.whysms.com/api/http/sms/send', [
                'api_token' => $apiToken,
                'recipient' => $cleanPhone,
                'sender_id' => $senderId,
                'type' => 'plain',
                'message' => $message
            ]);

            \Log::info('WhySMS Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $response->successful() ? 'success' : 'error';
        } catch (\Exception $e) {
            \Log::error('WhySMS Error', [
                'error' => $e->getMessage(),
                'phone' => $phone
            ]);
            return 'error';
        }
    }
}

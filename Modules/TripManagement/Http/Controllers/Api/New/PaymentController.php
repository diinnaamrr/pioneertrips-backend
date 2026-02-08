<?php

namespace Modules\TripManagement\Http\Controllers\Api\New;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Library\Payer;
use Modules\Gateways\Library\Payment as PaymentInfo;
use Modules\Gateways\Library\Receiver;
use Modules\Gateways\Traits\Payment;
use Modules\TransactionManagement\Traits\TransactionTrait;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;
use Modules\UserManagement\Lib\LevelHistoryManagerTrait;


class PaymentController extends Controller
{
    use TransactionTrait, Payment, LevelHistoryManagerTrait;
    protected $tripRequestservice;


    public function __construct(
        TripRequestServiceInterface $tripRequestservice,


    ) {
        $this->tripRequestservice = $tripRequestservice;
    }

    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required',
            'payment_method' => 'required|in:wallet,cash',
            'driver_confirmed' => 'sometimes|boolean' // Add this to handle driver confirmation
        ]);
        if ($validator->fails()) {

            return response()->json(responseFormatter(constant: DEFAULT_400, errors: errorProcessor($validator)), 400);
        }
        $trip = $this->tripRequestservice->findOne(id: $request->trip_request_id, relations: ['customer.userAccount', 'driver', 'fee']);
        if (!$trip) {
            return response()->json(responseFormatter(TRIP_REQUEST_404), 403);
        }
        if ($trip->payment_status == PAID) {

            return response()->json(responseFormatter(DEFAULT_PAID_200));
        }

        $tips = 0;
        DB::beginTransaction();
        if (!is_null($request->tips) && $request->payment_method == 'wallet') {
            $tips = $request->tips;
        }
        $feeAttributes['tips'] = $tips;

        $data = [
            'tips' => $tips,
            'payment_method' => $request->payment_method,
            'paid_fare' => $trip->paid_fare + $tips,
            'payment_status' => PAID
        ];
        $trip->fee()->update($feeAttributes);
        $trip = $this->tripRequestservice->update(id: $request->trip_request_id, data: $data);
        
        if ($request->payment_method == 'wallet') {
            if ($trip->customer->userAccount->wallet_balance < ($trip->paid_fare)) {

                return response()->json(responseFormatter(INSUFFICIENT_FUND_403), 403);
            }
            $method = '_with_wallet_balance';
            $this->walletTransaction($trip);
        } // driver only make cash payment - AUTO CONFIRM when driver says they received money
        elseif ($request->payment_method == 'cash') {
            $method = '_by_cash';
            $this->cashTransaction($trip);
            
            // Auto-confirm payment when driver confirms or when trip is completed
            if (($request->driver_confirmed || auth('api')->user()->user_type == 'driver') && $trip->current_status == 'completed') {
                $method = '_confirmed_by_driver';
                
                // Send notification to customer that payment is confirmed
                $customerPush = getNotification('payment_confirmed_by_driver') ?: [
                    'title' => 'Payment Confirmed',
                    'description' => 'Your driver has confirmed receiving the cash payment',
                    'status' => 'success'
                ];
                
                sendDeviceNotification(
                    fcm_token: $trip->customer->fcm_token,
                    title: translate($customerPush['title']),
                    description: translate(textVariableDataFormat(value: $customerPush['description'], paidAmount: $trip->paid_fare)),
                    status: $customerPush['status'],
                    ride_request_id: $trip->id,
                    type: $trip->type,
                    action: 'payment_confirmed_by_driver',
                    user_id: $trip->customer->id
                );
            }
        }

        $this->amountChecker($trip->customer, $trip->paid_fare);
        DB::commit();

        // Determine notification recipient based on user type and payment method
        $isDriverConfirming = auth('api')->user()->user_type == 'driver' && $request->payment_method == 'cash';
        $recipientToken = $isDriverConfirming ? $trip->customer->fcm_token : 
                         (auth('api')->user()->user_type == 'customer' ? $trip->driver->fcm_token : $trip->customer->fcm_token);
        $recipientId = $isDriverConfirming ? $trip->customer->id : 
                      (auth('api')->user()->user_type == 'customer' ? $trip->driver->id : $trip->customer->id);

        $push = getNotification('payment_successful');
        sendDeviceNotification(
            fcm_token: $recipientToken,
            title: translate($push['title']),
            description: translate(textVariableDataFormat($push['description'], paidAmount: $trip->paid_fare, methodName: $method)),
            status: $push['status'],
            ride_request_id: $trip->id,
            type: $trip->type,
            action: 'payment_successful',
            user_id: $recipientId
        );
        
        $pushTips = getNotification("tips_from_customer");
        if ($trip->tips > 0) {
            sendDeviceNotification(
                fcm_token: $trip->driver->fcm_token,
                title: translate($pushTips['title']),
                description: translate(textVariableDataFormat(value: $pushTips['description'], tipsAmount: $trip->tips)),
                status: $push['status'],
                ride_request_id: $trip->id,
                action: 'got_tipped',
                user_id: $trip->driver->id,
            );
        }

        return response()->json(responseFormatter(DEFAULT_UPDATE_200, message: $isDriverConfirming ? 'Payment confirmed successfully' : 'Payment processed successfully'));
    }


    public function digitalPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required',
            'payment_method' => 'required|in:ssl_commerz,stripe,paypal,razor_pay,paystack,senang_pay,paymob_accept,flutterwave,paytm,paytabs,liqpay,mercadopago,bkash,fatoorah,xendit,amazon_pay,iyzi_pay,hyper_pay,foloosi,ccavenue,pvit,moncash,thawani,tap,viva_wallet,hubtel,maxicash,esewa,swish,momo,payfast,worldpay,sixcash,ssl_commerz,stripe,paypal,razor_pay,paystack,senang_pay,paymob_accept,flutterwave,paytm,paytabs,liqpay,mercadopago,bkash,fatoorah,xendit,amazon_pay,iyzi_pay,hyper_pay,foloosi,ccavenue,pvit,moncash,thawani,tap,viva_wallet,hubtel,maxicash,esewa,swish,momo,payfast,worldpay,sixcash'
        ]);
        if ($validator->fails()) {

            return response()->json(responseFormatter(constant: DEFAULT_400, errors: errorProcessor($validator)), 400);
        }
        $trip = $this->tripRequestservice->findOne(id: $request->trip_request_id, relations: ['customer.userAccount', 'fee', 'time', 'driver']);
        if (!$trip) {
            return response()->json(responseFormatter(TRIP_REQUEST_404), 403);
        }
        if ($trip->payment_status == PAID) {

            return response()->json(responseFormatter(DEFAULT_PAID_200));
        }

        $attributes = [
            'column' => 'id',
            'payment_method' => $request->payment_method,
        ];
        $tips = $request->tips;
        $feeAttributes['tips'] = $tips;

        $trip->fee()->update($feeAttributes);

        $data = [
            'tips' => $tips,
            'payment_method' => $request->payment_method,
        ];


        $trip = $this->tripRequestservice->update(id: $request->trip_request_id, data: $data);
        $paymentAmount = $trip->paid_fare + $tips;
        $customer = $trip->customer;
        $payer = new Payer(
            name: $customer?->first_name,
            email: $customer->email,
            phone: $customer->phone,
            address: ''
        );

        //hook is look for a autoloaded function to perform action after payment
        $paymentInfo = new PaymentInfo(
            hook: 'tripRequestUpdate',
            currencyCode: 'USD',
            paymentMethod: $request->payment_method,
            paymentPlatform: 'mono',
            payerId: $customer->id,
            receiverId: '100',
            additionalData: [],
            paymentAmount: $paymentAmount,
            externalRedirectLink: null,
            attribute: 'order',
            attributeId: $request->trip_request_id
        );
        $receiverInfo = new Receiver('receiver_name', 'example.png');
        $redirectLink = $this->generate_link($payer, $paymentInfo, $receiverInfo);

        return redirect($redirectLink);
    }
}
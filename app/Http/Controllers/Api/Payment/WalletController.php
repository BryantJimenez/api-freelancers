<?php

namespace App\Http\Controllers\Api\Payment;

use App\Models\User;
use App\Models\Payment\Payment;
use App\Models\Payment\Stripe;
use App\Models\Payment\Paypal;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Payment\ApiWalletPaymentStoreRequest;
use Illuminate\Http\Request;
use App\Traits\StripeTrait;
use App\Traits\PaypalTrait;
use Auth;

class WalletController extends ApiController
{
    use StripeTrait, PaypalTrait;

    /**
    *
    * @OA\Post(
    *   path="/api/v1/wallet",
    *   tags={"Wallet"},
    *   summary="Add money",
    *   description="Add money in wallet",
    *   operationId="storeWallet",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="amount",
    *       in="query",
    *       description="Amount of payment",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="method",
    *       in="query",
    *       description="Method of payment (1=Stripe, 2=PayPal)",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *           enum={"1", "2"}
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="stripe_token",
    *       in="query",
    *       description="Stripe token of payment (It is required if paying with Stripe)",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="url_success",
    *       in="query",
    *       description="Url success of PayPal",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="url_cancel",
    *       in="query",
    *       description="Url cancel of PayPal",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Add money in wallet.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function store(ApiWalletPaymentStoreRequest $request) {
        if (request('method')=='1') {
            $subject="Money added to the wallet.";
            $response=$this->payWithStripe(number_format(request('amount'), 2, '.', ''), 'USD', $subject, request('stripe_token'));
            if ($response['status']=='success') {
                $fee=$this->stripeFee($response['data']['balance_transaction']);
                $balance=number_format(request('amount'), 2, '.', '')-$fee;

                $data=array('subject' => $subject, 'total' => number_format(request('amount'), 2, '.', ''), 'fee' => $fee, 'balance' => $balance, 'method' => '1', 'state' => '1', 'user_id' => Auth::id());
                $payment=Payment::create($data);

                if ($payment) {
                    $data=array('stripe_payment_id' => $response['data']['charge']->id, 'balance_transaction' => $response['data']['charge']->balance_transaction, 'payment_id' => $payment->id);
                    Stripe::create($data);

                    $user=User::where('id', Auth::id())->first();
                    $balance=$user->balance+number_format(request('amount'), 2, '.', '');
                    $user->fill(['balance' => $balance])->save();
                    if ($user) {
                        Auth::user()->balance=$balance;
                    }

                    return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The payment has been successfully completed.'], 200);
                }
            }
        }

        if (request('method')=='2') {
            $response=$this->payWithPaypal(number_format(request('amount'), 2, '.', ''), Auth::user()->email, Auth::user()->name, Auth::user()->lastname, request('url_success'), request('url_cancel'));
            if ($response['status']=='success') {
                $data=array('href' => $response['data']['links'][1]['href'], 'method' => $response['data']['links'][1]['method']);
                return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The payment order has been successfully created.', 'data' => $data], 200);
            }
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/wallet/success",
    *   tags={"Wallet"},
    *   summary="Complete pay",
    *   description="Complete pay in wallet with PayPal",
    *   operationId="successWallet",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="token",
    *       in="query",
    *       description="Token of order of PayPal",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Pay with PayPal complete.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function success(Request $request) {
        if (is_null(request('token'))) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The data sent was not valid.', 'errors' => ['token' => ['The token field is required.']]], 422);
        }

        $exists=Paypal::where([['paypal_payment_id', request('token')], ['paypal_payer_id', '!=', NULL]])->exists();
        if ($exists) {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This payment has already been confirmed previously'], 200);
        }

        $order=$this->orderSuccess(request('token'));

        if ($order['status']=='success') {
            if ($order['data']['status']=='COMPLETED') {
                $subject="Money added to the wallet.";
                $data=array('subject' => $subject, 'total' => $order['data']['amount']['gross_amount']['value'], 'fee' => $order['data']['amount']['paypal_fee']['value'], 'balance' => $order['data']['amount']['net_amount']['value'], 'method' => '2', 'state' => '1', 'user_id' => Auth::id());
                $payment=Payment::create($data);

                if ($payment) {
                    $data=array('paypal_payment_id' => $order['data']['order_id'], 'paypal_payer_id' => $order['data']['payer_id'], 'data' => json_encode([]), 'payment_id' => $payment->id);
                    Paypal::create($data);

                    $user=User::where('id', Auth::id())->first();
                    $balance=$user->balance+$order['data']['amount']['gross_amount']['value'];
                    $user->fill(['balance' => $balance])->save();
                    if ($user) {
                        Auth::user()->balance=$balance;
                    }

                    return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The payment has been successfully completed.'], 200);
                }

                return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
            }

            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'Payment was not completed, please try again.'], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Setting\SettingUpdateRequest;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/settings",
    *   tags={"Settings"},
    *   summary="Get settings",
    *   description="Returns all settings",
    *   operationId="indexSetting",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all settings.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   )
    * )
    */
    public function get() {
        $setting=Setting::first();
        $setting=$this->dataSetting($setting);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $setting], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/settings",
    *   tags={"Settings"},
    *   summary="Update settings",
    *   description="Update settings",
    *   operationId="updateSetting",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="stripe_public",
    *       in="query",
    *       description="Stripe public key",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="stripe_secret",
    *       in="query",
    *       description="Stripe secret key",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="paypal_public",
    *       in="query",
    *       description="Paypal public key",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="paypal_secret",
    *       in="query",
    *       description="Paypal secret key",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Update settings.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
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
    public function update(SettingUpdateRequest $request) {
        $setting=Setting::first();
        $data=array('stripe_public' => request('stripe_public'), 'stripe_secret' => request('stripe_secret'), 'paypal_public' => request('paypal_public'), 'paypal_secret' => request('paypal_secret'));
        $setting->fill($data)->save();
        if ($setting) {
            $setting=Setting::first();
            $setting=$this->dataSetting($setting);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The settings has been edited successfully.', 'data' => $setting], 200);
        }
        
        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

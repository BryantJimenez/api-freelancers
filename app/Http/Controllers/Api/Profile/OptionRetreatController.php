<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\User;
use App\Models\OptionRetreat;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Profile\ApiProfileOptionRetreatUpdateRequest;
use Illuminate\Http\Request;
use Auth;

class OptionRetreatController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/retreat-options",
    *   tags={"Profile Retreat Options"},
    *   summary="Get profile retreat options",
    *   description="Returns profile retreat options data",
    *   operationId="getProfileRetreatOption",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Get profile retreat options.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   )
    * )
    */
    public function get() {
        $retreat_option=OptionRetreat::where('user_id', Auth::id())->first();
        $retreat_option=$this->dataRetreatOption($retreat_option);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $retreat_option], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/retreat-options",
    *   tags={"Profile Retreat Options"},
    *   summary="Update retreat options user",
    *   description="Update a retreat options data",
    *   operationId="updateProfileRetreatOption",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="paypal_email",
    *       in="query",
    *       description="PayPal email of user",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Update profile retreat options.",
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
 	public function update(ApiProfileOptionRetreatUpdateRequest $request) {
        $user=Auth::user();
        $retreat_option=$user->retreat_option;

        $data=array('paypal_email' => request('paypal_email'));
        $retreat_option->fill($data)->save();

        if ($retreat_option) {
            $retreat_option=OptionRetreat::where('user_id', $user->id)->first();
            $retreat_option=$this->dataRetreatOption($retreat_option);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'User retreat options updated successfully.', 'data' => $retreat_option], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

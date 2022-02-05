<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\User;
use App\Models\OptionRetreat;
use App\Models\Retreat\Retreat;
use App\Models\Retreat\PaypalRetreat;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Retreat\ApiRetreatStoreRequest;
use Illuminate\Http\Request;
use Auth;

class RetreatController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/retreats",
    *   tags={"Profile Retreats"},
    *   summary="Get retreats",
    *   description="Returns all retreats",
    *   operationId="indexProfileRetreat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all retreats.",
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
    public function index() {
    	$retreats=Retreat::with(['user', 'paypal'])->where('user_id', Auth::id())->get()->map(function($retreat) {
    		return $this->dataRetreat($retreat);
    	});
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $retreats], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/profile/retreats",
    *   tags={"Profile Retreats"},
    *   summary="Register retreat",
    *   description="Create a new retreat",
    *   operationId="storeProfileRetreat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="method",
    *       in="query",
    *       description="Method of payment (1=PayPal)",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *           enum={"1"}
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="amount",
    *       in="query",
    *       description="Amount of retreat",
    *       required=true,
    *       @OA\Schema(
    *           type="number",
    *			format="double"
    *       )
    *   ),
    *   @OA\Response(
    *       response=201,
    *       description="Registered retreat.",
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
 	public function store(ApiRetreatStoreRequest $request) {
        $user=User::where('id', Auth::id())->first();
        $retreats=Retreat::where([['state', '2'], ['user_id', $user->id]])->get();
        if ($retreats->count()>0) {
            $retreats_total=0;
            foreach ($retreats as $retreat) {
                $retreats_total+=$retreat->amount;
            }
            
            if (request('amount')>($user->balance-$retreats_total)) {
                return response()->json(['code' => 200, 'status' => 'error', 'message' => 'The money in the wallet is not enough.'], 200);
            }
        }

        if (request('amount')>$user->balance) {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'The money in the wallet is not enough.'], 200);
        }

        $retreat_options=OptionRetreat::where('user_id', Auth::id())->first();
        if (is_null($retreat_options)) {
            return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
        }

        if (request('method')=='1') {
            if (is_null($retreat_options->paypal_email)) {
                return response()->json(['code' => 200, 'status' => 'error', 'message' => 'The user does not have the PayPal withdrawal option configured.'], 200);
            }

            $subject="Withdrawal of money from the wallet to PayPal.";
            $data=array('subject' => $subject, 'method' => request('method'), 'amount' => request('amount'), 'user_id' => Auth::id());
            $retreat=Retreat::create($data);

            if ($retreat) {
                $data=array('email' => $retreat_options->paypal_email,'retreat_id' => $retreat->id);
                PaypalRetreat::create($data);

                $retreat=Retreat::with(['user', 'paypal'])->where('id', $retreat->id)->first();
                $retreat=$this->dataRetreat($retreat);
                return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The retreat has been successfully created.', 'data' => $retreat], 201);
            }
        }

 		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
 	}

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/retreats/{id}",
    *   tags={"Profile Retreats"},
    *   summary="Get retreat",
    *   description="Returns a single retreat",
    *   operationId="showProfileRetreat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID retreat",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Show retreat.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=404,
    *       description="No results found."
    *   )
    * )
    */
    public function show(Retreat $retreat) {
        if ($retreat->user_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This retreat does not belong to this user.'], 403);
        }

        $retreat=$this->dataRetreat($retreat);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $retreat], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/retreats/{id}/cancel",
    *   tags={"Profile Retreats"},
    *   summary="Cancel retreat",
    *   description="Cancel a single retreat",
    *   operationId="cancelProfileRetreat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID retreat",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Cancel retreat.",
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
    *       response=404,
    *       description="No results found."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function cancel(Request $request, Retreat $retreat) {
        if ($retreat->user_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This retreat does not belong to this user.'], 403);
        }

        if ($retreat->state=='Cancelled') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This retreat has already been cancelled.'], 200);
        }

        if ($retreat->state=='Paid') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This retreat has already been paid, you cannot cancel it.'], 200);
        }

        $retreat->fill(['state' => "0"])->save();
        if ($retreat) {
            $retreat=$this->dataRetreat($retreat);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The retreat has been successfully cancelled.', 'data' => $retreat], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

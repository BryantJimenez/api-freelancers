<?php

namespace App\Http\Controllers\Api;

use App\Models\Retreat\Retreat;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class RetreatController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/retreats",
    *   tags={"Retreats"},
    *   summary="Get retreats",
    *   description="Returns all retreats",
    *   operationId="indexRetreat",
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
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   )
    * )
    */
    public function index() {
    	$retreats=Retreat::with(['user', 'paypal'])->get()->map(function($retreat) {
    		return $this->dataRetreat($retreat);
    	});

    	$page=Paginator::resolveCurrentPage('page');
    	$pagination=new LengthAwarePaginator($retreats, $total=count($retreats), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
    	$pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/retreats/{id}",
    *   tags={"Retreats"},
    *   summary="Get retreat",
    *   description="Returns a single retreat",
    *   operationId="showRetreat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID",
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
    *       response=403,
    *       description="Forbidden."
    *   ),
    *   @OA\Response(
    *       response=404,
    *       description="No results found."
    *   )
    * )
    */
    public function show(Retreat $retreat) {
    	$retreat=$this->dataRetreat($retreat);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $retreat], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/retreats/{id}/cancel",
    *   tags={"Retreats"},
    *   summary="Cancel retreat",
    *   description="Cancel a single retreat",
    *   operationId="cancelRetreat",
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

    /**
    *
    * @OA\Put(
    *   path="/api/v1/retreats/{id}/accept",
    *   tags={"Retreats"},
    *   summary="Accept retreat",
    *   description="Accept a single retreat",
    *   operationId="acceptRetreat",
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
    *       description="Accept retreat.",
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
    public function accept(Request $request, Retreat $retreat) {
        if ($retreat->state=='Cancelled') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This retreat has already been cancelled, you cannot paid it.'], 200);
        }

        if ($retreat->state=='Paid') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This retreat has already been paid.'], 200);
        }

        $retreat->fill(['state' => "1"])->save();
        if ($retreat) {
            $balance=$retreat['user']->balance-$retreat->amount;
            $retreat['user']->fill(['balance' => $balance])->save();

            $retreat=$this->dataRetreat($retreat);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The retreat has been successfully paid.', 'data' => $retreat], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

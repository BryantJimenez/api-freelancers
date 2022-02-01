<?php

namespace App\Http\Controllers\Api\Payment;

use App\Models\Payment\Payment;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class PaymentController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/payments",
    *   tags={"Payments"},
    *   summary="Get payments",
    *   description="Returns all payments",
    *   operationId="indexPayment",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all payments.",
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
    	$payments=Payment::with(['user'])->get()->map(function($payment) {
    		return $this->dataPayment($payment);
    	});

    	$page=Paginator::resolveCurrentPage('page');
    	$pagination=new LengthAwarePaginator($payments, $total=count($payments), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
    	$pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/payments/{id}",
    *   tags={"Payments"},
    *   summary="Get payment",
    *   description="Returns a single payment",
    *   operationId="showPayment",
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
    *       description="Show payment.",
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
    public function show(Payment $payment) {
    	$payment=$this->dataPayment($payment);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $payment], 200);
    }
}

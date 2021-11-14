<?php

namespace App\Http\Controllers\Api;

use App\Country;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class CountryController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/countries",
    *   tags={"Countries"},
    *   summary="Get countries",
    *   description="Returns all countries",
    *   operationId="indexCountry",
    *   @OA\Response(
    *       response=200,
    *       description="Show all countries.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   )
    * )
    */
    public function index() {
		$countries=Country::select("id", "name", "code")->get();
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $countries], 200);
    }
}

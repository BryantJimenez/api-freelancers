<?php

namespace App\Http\Controllers\Api;

use App\Language;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class LanguageController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/languages",
    *   tags={"Languages"},
    *   summary="Get languages",
    *   description="Returns all languages",
    *   operationId="indexLanguage",
    *   @OA\Response(
    *       response=200,
    *       description="Show all languages.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   )
    * )
    */
    public function index() {
		$languages=Language::select("id", "name", "code", "native_name")->get();
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $languages], 200);
    }
}

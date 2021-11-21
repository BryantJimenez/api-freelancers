<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Language\ApiLanguageStoreRequest;
use App\Http\Requests\Api\Language\ApiLanguageUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

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
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all languages.",
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
        $languages=Language::get()->map(function($language) {
            return $this->dataLanguage($language);
        });

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $languages], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/languages",
    *   tags={"Languages"},
    *   summary="Register language",
    *   description="Create a new language",
    *   operationId="storeLanguage",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="name",
    *       in="query",
    *       description="Name of language",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="code",
    *       in="query",
    *       description="Code of language",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="native_name",
    *       in="query",
    *       description="Name native of language",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered language.",
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
    public function store(ApiLanguageStoreRequest $request) {
        $trashed=Language::where('name', request('name'))->withTrashed()->exists();
        $exist=Language::where('name', request('name'))->exists();
        if ($trashed && $exist===false) {
            $language=Language::where('name', request('name'))->withTrashed()->first();
            $language->restore();
        } else if ($exist) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'This language already exists.'], 500);
        } else {
            $data=array('name' => request('name'), 'code' => request('code'), 'native_name' => request('native_name'));
            $language=Language::create($data);
        }

        if ($language) {
            $language=Language::where('id', $language->id)->first();
            $language=$this->dataLanguage($language);
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The language has been successfully registered.', 'data' => $language], 201);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/languages/{id}",
    *   tags={"Languages"},
    *   summary="Get language",
    *   description="Returns a single language",
    *   operationId="showLanguage",
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
    *       description="Show language.",
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
    public function show(Language $language) {
    	$language=$this->dataLanguage($language);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $language], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/languages/{id}",
    *   tags={"Languages"},
    *   summary="Update language",
    *   description="Update a single language",
    *   operationId="updateLanguage",
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
    *   @OA\Parameter(
    *       name="name",
    *       in="query",
    *       description="Name of language",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="code",
    *       in="query",
    *       description="Code of language",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="native_name",
    *       in="query",
    *       description="Name native of language",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered language.",
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
    public function update(ApiLanguageUpdateRequest $request, Language $language) {
        $data=array('name' => request('name'), 'code' => request('code'), 'native_name' => request('native_name'));
        $language->fill($data)->save();
        if ($language) {
            $language=Language::where('id', $language->id)->first();
            $language=$this->dataLanguage($language);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The language has been edited successfully.', 'data' => $language], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/languages/{id}",
    *   tags={"Languages"},
    *   summary="Delete language",
    *   description="Delete a single language",
    *   operationId="destroyLanguage",
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
    *       description="Delete language.",
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
    public function destroy(Language $language)
    {
    	$language->delete();
    	if ($language) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The language has been successfully removed.'], 200);
    	}

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/languages/{id}/deactivate",
    *   tags={"Languages"},
    *   summary="Deactivate language",
    *   description="Deactivate a single language",
    *   operationId="deactivateLanguage",
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
    *       description="Deactivate language.",
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
    public function deactivate(Request $request, Language $language) {
    	$language->fill(['state' => "0"])->save();
    	if ($language) {
    		$language=$this->dataLanguage($language);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The language has been successfully deactivated.', 'data' => $language], 200);
    	}
    	
        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/languages/{id}/activate",
    *   tags={"Languages"},
    *   summary="Activate language",
    *   description="Activate a single language",
    *   operationId="activateLanguage",
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
    *       description="Activate language.",
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
    public function activate(Request $request, Language $language) {
    	$language->fill(['state' => "1"])->save();
    	if ($language) {
    		$language=$this->dataLanguage($language);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The language has been successfully activated.', 'data' => $language], 200);
    	}
    	
        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

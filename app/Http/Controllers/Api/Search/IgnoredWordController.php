<?php

namespace App\Http\Controllers\Api\Search;

use App\Models\IgnoredWord;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\IgnoredWord\ApiIgnoredWordStoreRequest;
use App\Http\Requests\Api\IgnoredWord\ApiIgnoredWordUpdateRequest;
use Illuminate\Http\Request;

class IgnoredWordController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/ignored-words",
    *   tags={"Ignored Words"},
    *   summary="Get ignored words",
    *   description="Returns all ignored words",
    *   operationId="indexIgnoredWord",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all ignored words.",
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
        $words=IgnoredWord::select('id', 'words', 'slug')->get();
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $words], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/ignored-words",
    *   tags={"Ignored Words"},
    *   summary="Register ignored word",
    *   description="Create a new ignored word",
    *   operationId="storeIgnoredWord",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="words",
    *       in="query",
    *       description="Ignored words",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered word.",
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
    public function store(ApiIgnoredWordStoreRequest $request) {
        $word=IgnoredWord::create(['words' => request('words')]);
        if ($word) {
            $word=IgnoredWord::select('id', 'words', 'slug')->where('id', $word->id)->first();
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The ignored word has been successfully registered.', 'data' => $word], 201);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/ignored-words/{id}",
    *   tags={"Ignored Words"},
    *   summary="Get ignored word",
    *   description="Returns a single ignored word",
    *   operationId="showIgnoredWord",
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
    *       description="Show word.",
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
    public function show(IgnoredWord $word) {
        $word=$word->only("id", "words", "slug");
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $word], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/ignored-words/{id}",
    *   tags={"Ignored Words"},
    *   summary="Update ignored word",
    *   description="Update a single ignored word",
    *   operationId="updateIgnoredWord",
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
    *       name="words",
    *       in="query",
    *       description="Ignored words",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered word.",
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
    public function update(ApiIgnoredWordUpdateRequest $request, IgnoredWord $word) {
        $word->fill(['words' => request('words')])->save();
        if ($word) {
            $word=IgnoredWord::select('id', 'words', 'slug')->where('id', $word->id)->first();
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The ignored word has been edited successfully.', 'data' => $word], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/ignored-words/{id}",
    *   tags={"Ignored Words"},
    *   summary="Delete ignored word",
    *   description="Delete a single ignored word",
    *   operationId="destroyIgnoredWord",
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
    *       description="Delete word.",
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
    public function destroy(IgnoredWord $word)
    {
        $word->delete();
        if ($word) {
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The ignored word has been successfully removed.'], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

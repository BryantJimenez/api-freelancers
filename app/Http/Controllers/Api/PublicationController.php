<?php

namespace App\Http\Controllers\Api;

use App\Models\Publication\Publication;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class PublicationController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/publications",
    *   tags={"Publications"},
    *   summary="Get publications",
    *   description="Returns all publications",
    *   operationId="indexPublication",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all publications.",
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
    	$publications=Publication::with(['freelancer.user', 'categories'])->get()->map(function($publication) {
    		return $this->dataPublication($publication);
    	});

    	$page=Paginator::resolveCurrentPage('page');
    	$pagination=new LengthAwarePaginator($publications, $total=count($publications), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
    	$pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/publications/{id}",
    *   tags={"Publications"},
    *   summary="Get publications",
    *   description="Returns a single publications",
    *   operationId="showPublication",
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
    *       description="Show publication.",
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
    public function show(Publication $publication) {
    	$publication=$this->dataPublication($publication);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $publication], 200);
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/publications/{id}",
    *   tags={"Publications"},
    *   summary="Delete publication",
    *   description="Delete a single publication",
    *   operationId="destroyPublication",
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
    *       description="Delete publication.",
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
    public function destroy(Publication $publication)
    {
    	$publication->delete();
    	if ($publication) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The publication has been successfully removed.'], 200);
    	}

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

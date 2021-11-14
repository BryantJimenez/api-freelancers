<?php

namespace App\Http\Controllers\Api;

use App\Specialization;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ApiSpecializationStoreRequest;
use App\Http\Requests\ApiSpecializationUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class SpecializationController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/specializations",
    *   tags={"Specializations"},
    *   summary="Get specializations",
    *   description="Returns all specializations",
    *   operationId="indexSpecialization",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all specializations.",
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
		$specializations=Specialization::select("id", "name", "slug", "state")->get();

        $page=Paginator::resolveCurrentPage('page');
        $pagination=new LengthAwarePaginator($specializations, $total=count($specializations), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        $pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/specializations",
    *   tags={"Specializations"},
    *   summary="Register specialization",
    *   description="Create a new specialization",
    *   operationId="storeSpecialization",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="name",
    *       in="query",
    *       description="Name of specialization",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered specialization.",
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
    public function store(ApiSpecializationStoreRequest $request) {
    	$specialization=Specialization::create(['name' => request('name')]);
    	if ($specialization) {
            $specialization=Specialization::select("id", "name", "slug", "state")->where('id', $specialization->id)->first();
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The specialization has been successfully registered.', 'data' => $specialization], 201);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/specializations/{id}",
    *   tags={"Specializations"},
    *   summary="Get specialization",
    *   description="Returns a single specialization",
    *   operationId="showSpecialization",
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
    *       description="Show specialization.",
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
    public function show(Specialization $specialization) {
    	$specialization=$specialization->only("id", "name", "slug", "state");
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $specialization], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/specializations/{id}",
    *   tags={"Specializations"},
    *   summary="Update specialization",
    *   description="Update a single specialization",
    *   operationId="updateSpecialization",
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
    *       description="Name of specialization",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered specialization.",
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
    public function update(ApiSpecializationUpdateRequest $request, Specialization $specialization) {
    	$specialization->fill(['name' => request('name')])->save();        
    	if ($specialization) {
    		$specialization=Specialization::select("id", "name", "slug", "state")->where('id', $specialization->id)->first();
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The specialization has been edited successfully.', 'data' => $specialization], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/specializations/{id}",
    *   tags={"Specializations"},
    *   summary="Delete specialization",
    *   description="Delete a single specialization",
    *   operationId="destroySpecialization",
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
    *       description="Delete specialization.",
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
    public function destroy(Specialization $specialization)
    {
    	$specialization->delete();
    	if ($specialization) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The specialization has been successfully removed.'], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/specializations/{id}/deactivate",
    *   tags={"Specializations"},
    *   summary="Deactivate specialization",
    *   description="Deactivate a single specialization",
    *   operationId="deactivateSpecialization",
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
    *       description="Deactivate specialization.",
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
    public function deactivate(Request $request, Specialization $specialization) {
    	$specialization->fill(['state' => "0"])->save();
    	if ($specialization) {
    		$specialization=Specialization::select("id", "name", "slug", "state")->where('id', $specialization->id)->first();
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The specialization has been successfully deactivated.', 'data' => $specialization], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/specializations/{id}/activate",
    *   tags={"Specializations"},
    *   summary="Activate specialization",
    *   description="Activate a single specialization",
    *   operationId="activateSpecialization",
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
    *       description="Activate specialization.",
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
    public function activate(Request $request, Specialization $specialization) {
    	$specialization->fill(['state' => "1"])->save();
    	if ($specialization) {
    		$specialization=Specialization::select("id", "name", "slug", "state")->where('id', $specialization->id)->first();
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The specialization has been successfully activated.', 'data' => $specialization], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }
}

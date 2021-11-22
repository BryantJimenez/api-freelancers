<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\Publication\Publication;
use App\Models\Publication\CategoryPublication;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Publication\ApiPublicationStoreRequest;
use App\Http\Requests\Api\Publication\ApiPublicationUpdateRequest;
use Illuminate\Http\Request;
use Auth;

class PublicationController extends ApiController
{
	public $freelancer;

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$user=Auth::user();
			$freelancer=$user->freelancer;
			if (is_null($freelancer)) {
				return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This user is not a freelancer.'], 403);
			}
			$this->freelancer=$freelancer;

			return $next($request);
		});
	}

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/publications",
    *   tags={"Profile Publications"},
    *   summary="Get publications of profile",
    *   description="Returns all profile publications",
    *   operationId="indexProfilePublication",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all profile publications.",
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
    	$publications=Publication::with(['categories'])->where('freelancer_id', $this->freelancer->id)->get()->map(function($publication) {
    		return $this->dataPublication($publication);
    	});
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $publications], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/profile/publications",
    *   tags={"Profile Publications"},
    *   summary="Register profile publication",
    *   description="Create a new profile publication",
    *   operationId="storeProfilePublication",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="name",
    *       in="query",
    *       description="Name of publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="description",
    *       in="query",
    *       description="Description of publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="content",
    *       in="query",
    *       description="Content of publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="category_id[0]",
    *       in="query",
    *       description="Categorie ID",
    *       required=true,
    *     	@OA\Schema(
 	*      		type="string"
 	*    	)
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered publication.",
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
 	public function store(ApiPublicationStoreRequest $request) {
 		$data=array('name' => request('name'), 'description' => request('description'), 'content' => request('content'), 'freelancer_id' => $this->freelancer->id);
 		$publication=Publication::create($data);

 		if ($publication) {
 			foreach (request('category_id') as $category) {
 				$data=array('category_id' => $category, 'publication_id' => $publication->id);
 				CategoryPublication::create($data);
 			}

 			$publication=Publication::with(['categories'])->where('id', $publication->id)->first();
 			$publication=$this->dataPublication($publication);
 			return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The publication has been successfully registered.', 'data' => $publication], 201);
 		}

 		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
 	}

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/publications/{id}",
    *   tags={"Profile Publications"},
    *   summary="Get profile publication",
    *   description="Returns a single profile publication",
    *   operationId="showProfilePublication",
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
    	if ($publication->freelancer_id!=$this->freelancer->id) {
    		return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This publication does not belong to this user.'], 403);
    	}

    	$publication=$this->dataPublication($publication);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $publication], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/publications/{id}",
    *   tags={"Profile Publications"},
    *   summary="Update profile publication",
    *   description="Update a single profile publication",
    *   operationId="updateProfilePublication",
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
    *       description="Name of publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="description",
    *       in="query",
    *       description="Description of publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="content",
    *       in="query",
    *       description="Content of publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="category_id[0]",
    *       in="query",
    *       description="Categorie ID",
    *       required=true,
    *     	@OA\Schema(
 	*      		type="string"
 	*    	)
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered category.",
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
 	public function update(ApiPublicationUpdateRequest $request, Publication $publication) {
 		if ($publication->freelancer_id!=$this->freelancer->id) {
 			return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This publication does not belong to this user.'], 403);
 		}

 		$data=array('name' => request('name'), 'description' => request('description'), 'content' => request('content'));
 		$publication->fill($data)->save();
 		if ($publication) {
 			// Delete categories of publication
 			CategoryPublication::where('publication_id', $publication->id)->delete();

 			foreach (request('category_id') as $category) {
 				$data=array('category_id' => $category, 'publication_id' => $publication->id);
 				CategoryPublication::create($data);
 			}

 			$publication=Publication::with(['categories'])->where('id', $publication->id)->first();
 			$publication=$this->dataPublication($publication);
 			return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The publication has been edited successfully.', 'data' => $publication], 200);
 		}

 		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
 	}

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/profile/publications/{id}",
    *   tags={"Profile Publications"},
    *   summary="Delete profile publication",
    *   description="Delete a single profile publication",
    *   operationId="destroyProfilePublication",
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
    *       description="Delete profile publication.",
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
    	if ($publication->freelancer_id!=$this->freelancer->id) {
    		return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This publication does not belong to this user.'], 403);
    	}

    	$publication->delete();
    	if ($publication) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The publication has been successfully removed.'], 200);
    	}

    	return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/publications/{id}/deactivate",
    *   tags={"Profile Publications"},
    *   summary="Deactivate profile publication",
    *   description="Deactivate a single profile publication",
    *   operationId="deactivateProfilePublication",
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
    *       description="Deactivate profile publication.",
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
    public function deactivate(Request $request, Publication $publication) {
    	if ($publication->freelancer_id!=$this->freelancer->id) {
    		return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This publication does not belong to this user.'], 403);
    	}

    	$publication->fill(['state' => "0"])->save();
    	if ($publication) {
    		$publication=$this->dataPublication($publication);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The publication has been successfully deactivated.', 'data' => $publication], 200);
    	}

    	return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/publications/{id}/activate",
    *   tags={"Profile Publications"},
    *   summary="Activate profile publication",
    *   description="Activate a single profile publication",
    *   operationId="activateProfilePublication",
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
    *       description="Activate publication.",
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
    public function activate(Request $request, Publication $publication) {
    	if ($publication->freelancer_id!=$this->freelancer->id) {
    		return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This publication does not belong to this user.'], 403);
    	}

    	$publication->fill(['state' => "1"])->save();
    	if ($publication) {
    		$publication=$this->dataPublication($publication);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The publication has been successfully activated.', 'data' => $publication], 200);
    	}

    	return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

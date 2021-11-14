<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ApiCategoryStoreRequest;
use App\Http\Requests\ApiCategoryUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class CategoryController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/categories",
    *   tags={"Categories"},
    *   summary="Get categories",
    *   description="Returns all categories",
    *   operationId="indexCategory",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all categories.",
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
		$categories=Category::select("id", "name", "slug", "state")->get();

        $page=Paginator::resolveCurrentPage('page');
        $pagination=new LengthAwarePaginator($categories, $total=count($categories), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        $pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/categories",
    *   tags={"Categories"},
    *   summary="Register category",
    *   description="Create a new category",
    *   operationId="storeCategory",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="name",
    *       in="query",
    *       description="Name of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
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
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function store(ApiCategoryStoreRequest $request) {
    	$category=Category::create(['name' => request('name')]);
    	if ($category) {
            $category=Category::select("id", "name", "slug", "state")->where('id', $category->id)->first();
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The category has been successfully registered.', 'data' => $category], 201);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/categories/{id}",
    *   tags={"Categories"},
    *   summary="Get category",
    *   description="Returns a single category",
    *   operationId="showCategory",
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
    *       description="Show category.",
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
    public function show(Category $category) {
    	$category=$category->only("id", "name", "slug", "state");
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $category], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/categories/{id}",
    *   tags={"Categories"},
    *   summary="Update category",
    *   description="Update a single category",
    *   operationId="updateCategory",
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
    *       description="Name of category",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
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
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function update(ApiCategoryUpdateRequest $request, Category $category) {
    	$category->fill(['name' => request('name')])->save();        
    	if ($category) {
    		$category=Category::select("id", "name", "slug", "state")->where('id', $category->id)->first();
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The category has been edited successfully.', 'data' => $category], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/categories/{id}",
    *   tags={"Categories"},
    *   summary="Delete category",
    *   description="Delete a single category",
    *   operationId="destroyCategory",
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
    *       description="Delete category.",
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
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function destroy(Category $category)
    {
    	$category->delete();
    	if ($category) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The category has been successfully removed.'], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/categories/{id}/deactivate",
    *   tags={"Categories"},
    *   summary="Deactivate category",
    *   description="Deactivate a single category",
    *   operationId="deactivateCategory",
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
    *       description="Deactivate category.",
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
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function deactivate(Request $request, Category $category) {
    	$category->fill(['state' => "0"])->save();
    	if ($category) {
    		$category=Category::select("id", "name", "slug", "state")->where('id', $category->id)->first();
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The category has been successfully deactivated.', 'data' => $category], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/categories/{id}/activate",
    *   tags={"Categories"},
    *   summary="Activate category",
    *   description="Activate a single category",
    *   operationId="activateCategory",
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
    *       description="Activate category.",
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
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function activate(Request $request, Category $category) {
    	$category->fill(['state' => "1"])->save();
    	if ($category) {
    		$category=Category::select("id", "name", "slug", "state")->where('id', $category->id)->first();
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The category has been successfully activated.', 'data' => $category], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }
}

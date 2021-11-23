<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\User;
use App\Models\Favorite;
use App\Models\Publication\Publication;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Auth;

class FavoriteController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/favorites",
    *   tags={"Favorites"},
    *   summary="Get favorite publications",
    *   description="Returns all favorite publications",
    *   operationId="indexFavorite",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all favorite publications.",
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
    	$user=User::with(['favorites.publication.categories'])->where('id', Auth::id())->first();
    	$favorites=$user['favorites']->map(function($favorite) {
    		return $this->dataFavorite($favorite);
    	});
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $favorites], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/favorites/{id}",
    *   tags={"Favorites"},
    *   summary="Add favorite publication",
    *   description="Add a favorite publication",
    *   operationId="storeFavorite",
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
    *       response=404,
    *       description="No results found."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
 	public function store(Request $request, Publication $publication) {
 		$exists=Favorite::where([['publication_id', $publication->id], ['user_id', Auth::id()]])->exists();
 		if ($exists) {
 			return response()->json(['code' => 200, 'status' => 'warning', 'message' => 'This publication is already in your favorites.'], 200);
 		}

 		$data=array('user_id' => Auth::id(), 'publication_id' => $publication->id);
 		$favorite=Favorite::create($data);
 		if ($favorite) {
 			return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The favorite publication has been added.'], 200);
 		}

 		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
 	}

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/favorites/{id}",
    *   tags={"Favorites"},
    *   summary="Delete favorite publication",
    *   description="Delete a single favorite publication",
    *   operationId="destroyFavorite",
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
    *       description="Delete favorite publication.",
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
    public function destroy(Favorite $favorite)
    {
    	$favorite->delete();
    	if ($favorite) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The favorite publication has been successfully removed.'], 200);
    	}

    	return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

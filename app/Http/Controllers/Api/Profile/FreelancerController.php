<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\User;
use App\Models\Language;
use App\Models\Category;
use App\Models\Freelancer\Freelancer;
use App\Models\Freelancer\FreelancerLanguage;
use App\Models\Freelancer\CategoryFreelancer;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Profile\ApiProfileFreelancerUpdateRequest;
use Illuminate\Http\Request;
use Auth;

class FreelancerController extends ApiController
{
    /**
    *
    * @OA\Post(
    *   path="/api/v1/profile/upgrade",
    *   tags={"Freelancer Profile"},
    *   summary="Upgrade user to freelancer",
    *   description="Upgrade a profile ",
    *   operationId="upgradeProfile",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Upgrade profile user.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function upgrade(Request $request) {
    	$user=Auth::user();
        $trashed=$user->freelancer()->withTrashed()->first();

        if (is_null($trashed) && is_null($user->freelancer)) {
            $freelancer=Freelancer::create(['user_id' => $user->id]);
        } else if (!is_null($trashed) && is_null($user->freelancer)) {
            $freelancer=$trashed->restore();
        } else {
            return response()->json(['code' => 200, 'status' => 'warning', 'message' => 'This user is already a freelancer.'], 200);
        }

        if ($freelancer) {
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'Your profile has been successfully updated to programmer.'], 201);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/freelancer",
    *   tags={"Freelancer Profile"},
    *   summary="Get profile freelancer",
    *   description="Returns profile data",
    *   operationId="getProfileFreelancer",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Get profile freelancer.",
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
    public function get() {
        if (!is_null(Auth::user()->freelancer)) {
            $user=User::with(['roles', 'country', 'freelancer', 'freelancer.languages', 'freelancer.categories'])->where('id', Auth::user()->id)->first();            
            $user=$this->dataUser($user, true);
            return response()->json(['code' => 200, 'status' => 'success', 'data' => $user], 200);
        }

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user is not a freelancer.'], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/freelancer",
    *   tags={"Freelancer Profile"},
    *   summary="Update freelancer user",
    *   description="Update a profile freelancer data",
    *   operationId="updateProfileFreelancer",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="country_id",
    *       in="query",
    *       description="Country ID of user",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="description",
    *       in="query",
    *       description="Description of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="language_id[0]",
    *       in="query",
    *       description="Language ID",
    *       required=true,
    *     	@OA\Schema(
 	*      		type="string"
 	*    	)
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
    *       description="Update profile freelancer user.",
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
 	public function update(ApiProfileFreelancerUpdateRequest $request) {
        $user=Auth::user();
        $freelancer=$user->freelancer;

        if (is_null($freelancer)) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This user have not a freelancer profile.'], 403);
        }

        $data=array('country_id' => request('country_id'));
        $user->fill($data)->save();

        $data=array('description' => request('description'));
        $freelancer->fill($data)->save();

        if ($user && $freelancer) {
            Auth::user()->country_id=request('country_id');

            // Delete languages of freelancer
            FreelancerLanguage::where('freelancer_id', $freelancer->id)->delete();

            foreach (request('language_id') as $language) {
                $data=array('language_id' => $language, 'freelancer_id' => $freelancer->id);
                FreelancerLanguage::create($data);
            }

            // Delete categories of freelancer
            CategoryFreelancer::where('freelancer_id', $freelancer->id)->delete();

            foreach (request('category_id') as $category) {
                $data=array('category_id' => $category, 'freelancer_id' => $freelancer->id);
                CategoryFreelancer::create($data);
            }

            $user=User::with(['roles', 'country', 'freelancer', 'freelancer.languages', 'freelancer.categories'])->where('id', $user->id)->first();
            $user=$this->dataUser($user, true);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'User profile updated successfully.', 'data' => $user], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/profile/freelancer",
    *   tags={"Freelancer Profile"},
    *   summary="Delete freelancer user",
    *   description="Delete a profile freelancer data",
    *   operationId="destroyProfileFreelance",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Delete user freelancer.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function destroy(Request $request)
    {
        $user=Auth::user();
        if (!is_null($user->freelancer)) {
            $freelancer=$user->freelancer->delete();
            if ($freelancer) {
                return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The freelancer has been successfully removed.'], 200);
            } else {
                return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
            }
        }

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user is not a freelancer.'], 200);
    }
}

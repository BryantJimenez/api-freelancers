<?php

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\User\ApiUserStoreRequest;
use App\Http\Requests\Api\User\ApiUserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendEmailRegister;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class UserController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/users",
    *   tags={"Users"},
    *   summary="Get users",
    *   description="Returns all users",
    *   operationId="indexUser",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all users.",
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
		$users=User::with(['roles'])->get()->map(function($user) {
			return $this->dataUser($user);
		});

        $page=Paginator::resolveCurrentPage('page');
        $pagination=new LengthAwarePaginator($users, $total=count($users), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        $pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/users",
    *   tags={"Users"},
    *   summary="Register user",
    *   description="Create a new user",
    *   operationId="storeUser",
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
    *   @OA\Parameter(
    *       name="lastname",
    *       in="query",
    *       description="Lastname of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="username",
    *       in="query",
    *       description="Username of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="email",
    *       in="query",
    *       description="Email of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="password",
    *       in="query",
    *       description="Password of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="password_confirmation",
    *       in="query",
    *       description="Password confirm of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="type",
    *       in="query",
    *       description="Type of user (Super Admin, Administrator, User)",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="photo",
    *       in="query",
    *       description="Photo filename of user",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered user.",
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
    public function store(ApiUserStoreRequest $request) {
    	$data=array('name' => request('name'), 'lastname' => request('lastname'), 'username' => request('username'), 'email' => request('email'), 'password' => Hash::make(request('password')));
    	$user=User::create($data);

    	if ($user) {
    		$user->assignRole(request('type'));

    		if (!is_null(request('photo'))) {
    			$user->fill(['photo' => request('photo')])->save();
    		}
    		// SendEmailRegister::dispatch($user->slug);
            $user=User::with(['roles'])->where('id', $user->id)->first();
            $user=$this->dataUser($user);

            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The user has been successfully registered.', 'data' => $user], 201);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/users/{id}",
    *   tags={"Users"},
    *   summary="Get user",
    *   description="Returns a single user",
    *   operationId="showUser",
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
    *       description="Show user.",
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
    public function show(User $user) {
    	$user=$this->dataUser($user);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $user], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/users/{id}",
    *   tags={"Users"},
    *   summary="Update user",
    *   description="Update a single user",
    *   operationId="updateUser",
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
    *       description="Name of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="lastname",
    *       in="query",
    *       description="Lastname of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="type",
    *       in="query",
    *       description="Type of user (Super Admin, Administrator, User)",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="photo",
    *       in="query",
    *       description="Photo filename of user",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Registered user.",
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
    public function update(ApiUserUpdateRequest $request, User $user) {
    	$data=array('name' => request('name'), 'lastname' => request('lastname'));
    	$user->fill($data)->save();        

    	if ($user) {
    		if (!is_null(request('type'))) {
    			$user->syncRoles([request('type')]);
    		}
    		if (!is_null(request('photo'))) {
    			$user->fill(['photo' => request('photo')])->save();
    		}
            $user=User::with(['roles'])->where('id', $user->id)->first();
    		$user=$this->dataUser($user);

    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user has been edited successfully.', 'data' => $user], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Delete(
    *   path="/api/v1/users/{id}",
    *   tags={"Users"},
    *   summary="Delete user",
    *   description="Delete a single user",
    *   operationId="destroyUser",
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
    *       description="Delete user.",
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
    public function destroy(User $user)
    {
    	$user->delete();
    	if ($user) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user has been successfully removed.'], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/users/{id}/deactivate",
    *   tags={"Users"},
    *   summary="Deactivate user",
    *   description="Deactivate a single user",
    *   operationId="deactivateUser",
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
    *       description="Deactivate user.",
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
    public function deactivate(Request $request, User $user) {
    	$user->fill(['state' => "0"])->save();
    	if ($user) {
    		$user=$this->dataUser($user);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user has been successfully deactivated.', 'data' => $user], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/users/{id}/activate",
    *   tags={"Users"},
    *   summary="Activate user",
    *   description="Activate a single user",
    *   operationId="activateUser",
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
    *       description="Activate user.",
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
    public function activate(Request $request, User $user) {
    	$user->fill(['state' => "1"])->save();
    	if ($user) {
    		$user=$this->dataUser($user);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user has been successfully activated.', 'data' => $user], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }
}

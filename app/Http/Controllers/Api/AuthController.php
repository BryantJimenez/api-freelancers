<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Auth\ApiLoginRequest;
use App\Http\Requests\Api\Auth\ApiRegisterRequest;
use App\Http\Requests\Api\Auth\ApiRecoveryRequest;
use App\Http\Requests\Api\Auth\ApiResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use Str;

class AuthController extends ApiController
{
	/**
	* @OA\Post(
	*	path="/api/v1/auth/login",
	*   tags={"Login"},
	*   summary="Login",
	*   description="Login for users",
	*   operationId="login",
	*   @OA\Parameter(
	*      	name="email",
	*      	in="query",
	*      	required=false,
	*      	@OA\Schema(
	*      		type="string"
	*      	)
	*   ),
	*   @OA\Parameter(
	*      	name="username",
	*      	in="query",
	*      	required=false,
	*      	@OA\Schema(
	*      		type="string"
	*      	)
	*   ),
	*   @OA\Parameter(
	*      	name="password",
	*      	in="query",
	*      	required=true,
	*      	@OA\Schema(
	*        	type="string"
	*      	)
	*   ),
	*   @OA\Response(
	*      	response=200,
	*      	description="Login success",
	*      	@OA\MediaType(
	*           mediaType="application/json",
	*      	)
	*   ),
	* 	@OA\Response(
    *   	response=401,
    *   	description="Not authenticated."
    * 	),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   ),
    * 	@OA\Response(
    *  		response=422,
    *   	description="Data not valid."
    * 	)
	* )
     **/
	public function login(ApiLoginRequest $request) {
		$user=User::where('email', request('email'))->orWhere('username', request('username'))->first();

		if (!Hash::check(request('password'), $user->password)) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The credentials do not match.'], 422);
        }

        if ($user->state=='0') {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This user is not allowed to enter.'], 403);
        }

        Auth::login($user);

        if(Auth::check()) {
            $user=$request->user();
            $tokenResult=$user->createToken('Personal Access Token');

            $token=$tokenResult->token;
            if (!is_null(request('remember'))) {
                $token->expires_at=Carbon::now()->addYears(10);
            }
            $token->save();

            return response()->json(['code' => 200, 'status' => 'success', 'access_token' => $tokenResult->accessToken, 'token_type' => 'Bearer', 'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()]);
        }

        return response()->json(['code' => 401, 'status' => 'error', 'message' => 'The credentials do not match.'], 401);
    }

	/**
    * @OA\Post(
    *	path="/api/v1/auth/register",
    *   tags={"Register"},
    *   summary="Register user",
    *   operationId="register",
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
    * 	@OA\Response(
    * 		response=201,
    *   	description="Register user.",
    *   	@OA\MediaType(
    *      		mediaType="application/json",
    *   	)
    * 	),
    * 	@OA\Response(
    *   	response=401,
    *   	description="Not authenticated."
    * 	),
    * 	@OA\Response(
    *  		response=422,
    *   	description="Data not valid."
    * 	),
    * 	@OA\Response(
    *   	response=500,
    *   	description="An error occurred during the process."
    * 	)
    * )
     **/
	public function register(ApiRegisterRequest $request) {
        $data=array('name' => request('name'), 'lastname' => request('lastname'), 'username' => request('username'), 'email' => request('email'), 'password' => Hash::make(request('password')));
        $user=User::create($data);

        if ($user) {
            $user->assignRole('User');
            $user=User::with(['roles'])->where('id', $user->id)->first();
            $user=$this->dataUser($user);
            
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'Successful registration.', 'data' => $user], 201);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/auth/logout",
    *   tags={"Logout"},
    *   summary="Logout",
    *   description="Account log out",
    *   operationId="logout",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Logout success.",
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
    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The session has been closed successfully.'], 200);
    }

    /**
    * @OA\Post(
    *   path="/api/v1/auth/password/email",
    *   tags={"Forgot Password"},
    *   summary="Forgot password user",
    *   operationId="forgotPassword",
    *   @OA\Parameter(
    *       name="email",
    *       in="query",
    *       description="Email of user",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Recovery password.",
    *       @OA\MediaType(
    *           mediaType="application/json",
    *       )
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
    public function recovery(ApiRecoveryRequest $request)
    {
        $data=array('email' => request('email'), 'token' => Str::random(64), 'created_at' => Carbon::now());
        DB::table('password_resets')->insert($data);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $data], 200);
    }

    /**
    * @OA\Post(
    *   path="/api/v1/auth/password/reset",
    *   tags={"Reset Password"},
    *   summary="Reset password user",
    *   operationId="resetPassword",
    *   @OA\Parameter(
    *       name="token",
    *       in="query",
    *       description="Token for forgot password",
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
    *   @OA\Response(
    *       response=200,
    *       description="Reset password.",
    *       @OA\MediaType(
    *           mediaType="application/json",
    *       )
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
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function reset(ApiResetRequest $request)
    {
        $user=User::where('email', request('email'))->first();
        if (is_null($user)) {
            return response()->json(['code' => 404, 'status' => 'error', 'message' => 'User not found.'], 404);
        }

        $data=array('email' => request('email'), 'token' => request('token'));
        $reset_password=DB::table('password_resets')->where($data)->first();
        
        if (is_null($reset_password)) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'The token is not valid.'], 403);
        }

        $user->fill(['password' => Hash::make(request('password'))])->save();
        if ($user) {
            DB::table('password_resets')->where(['email'=> request('email')])->delete();
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The password has been changed successfully.'], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

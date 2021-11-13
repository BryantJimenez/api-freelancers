<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Auth;

class AuthController extends ApiController
{
	public function login(ApiLoginRequest $request) {
		$user=User::where('email', request('email'))->orWhere('username', request('username'))->first();

		if ($user->state==0) {
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

	public function register(ApiRegisterRequest $request) {
        $data=array('name' => request('name'), 'lastname' => request('lastname'), 'username' => request('username'), 'email' => request('email'), 'password' => Hash::make(request('password')));
        $user=User::create($data);

        if ($user) {
            $user->assignRole('User');
            $user=$this->dataUser($user);
            
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'Successful registration.', 'data' => $user], 201);
        } else {
        	return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
        }
    }

	public function logout(Request $request) {
		$request->user()->token()->revoke();
		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The session has been closed successfully.'], 200);
	}
}

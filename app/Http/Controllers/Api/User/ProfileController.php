<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ApiProfileUpdateRequest;
use App\Http\Requests\ApiProfilePasswordUpdateRequest;
use App\Http\Requests\ApiProfileEmailUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;

class ProfileController extends ApiController
{
	/**
     * Display the specified resource.
     */
    public function get() {
    	$user=$this->dataUser(Auth::user());
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ApiProfileUpdateRequest $request) {
    	$user=Auth::user();
    	$data=array('name' => request('name'), 'lastname' => request('lastname'));
    	$user->fill($data)->save();

    	if ($user) {
    		if (!is_null(request('photo'))) {
    			$user->fill(['photo' => request('photo')])->save();
    		}
    		$user=$this->dataUser($user);

    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'User profile updated successfully.', 'data' => $user], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(ApiProfilePasswordUpdateRequest $request) {
    	$user=Auth::user();
    	if (!Hash::check(request('current_password'), $user->password)) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The current password is incorrect.'], 422);
        }

        if (request('current_password')==request('new_password')) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The new password cannot be the same as the current one.'], 422);
        }
    	$user->fill(['password' => Hash::make(request('new_password'))])->save();

    	if ($user) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Password changed successfully.'], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeEmail(ApiProfileEmailUpdateRequest $request) {
    	$user=Auth::user();
    	if (request('current_email')!=$user->email) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The current email is incorrect.'], 422);
        }

        if (request('new_email')==$user->email) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The new email cannot be the same as the current one.'], 422);
        }
    	$user->fill(['email' => request('new_email')])->save();

    	if ($user) {
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Email changed successfully.'], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\User;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ApiUserStoreRequest;
use App\Http\Requests\ApiUserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendEmailRegister;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
		$users=User::get()->map(function($user) {
			return $this->dataUser($user);
		});

        $page=Paginator::resolveCurrentPage('page');
        $pagination=new LengthAwarePaginator($users, $total=count($users), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        $pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            $user=User::where('id', $user->id)->first();
            $user=$this->dataUser($user);

            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The user has been successfully registered.', 'data' => $user], 201);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) {
    	$user=$this->dataUser($user);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
    		$user=$this->dataUser($user);

    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user has been edited successfully.', 'data' => $user], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
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

    public function deactivate(Request $request, User $user) {
    	$user->fill(['state' => "0"])->save();
    	if ($user) {
    		$user=$this->dataUser($user);
    		return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The user has been successfully deactivated.', 'data' => $user], 200);
    	} else {
    		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    	}
    }

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

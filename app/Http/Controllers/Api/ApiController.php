<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
	public function dataUser($user) {
		$user->rol=roleUser($user, false);
		$user->state=($user->state=='1') ? 'Activo' : 'Inactivo';
		$user->photo=(!is_null($user->photo)) ? $user->photo : '';
		$data=$user->only("id", "name", "lastname", "slug", "photo", "username", "email", "state", "rol");

		return $data;
	}
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
* @OA\Info(
*	title="API Freelancers",
*	version="1.0",
*   @OA\License(
*   	name="Apache 2.0",
*       url="http://www.apache.org/licenses/LICENSE-2.0.html"
*   )
* )
*
* @OA\Server(url="http://localhost:8000")
*
* @OA\SecurityScheme(
*	securityScheme="bearerAuth",
*   in="header",
*   name="bearerAuth",
*   type="http",
*   scheme="bearer",
*   bearerFormat="JWT"
* ),
*/
class ApiController extends Controller
{
	public function dataUser($user) {
		$user->rol=roleUser($user, false);
		$user->photo=(!is_null($user->photo)) ? $user->photo : '';
		$data=$user->only("id", "name", "lastname", "slug", "photo", "username", "email", "state", "rol");

		return $data;
	}
}
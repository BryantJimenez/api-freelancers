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
* @OA\Server(url="http://api-freelancer.otterscompany.com")
*
* @OA\Tag(
*	name="Login",
*	description="Login users endpoints"
* )
*
* @OA\Tag(
*	name="Register",
*	description="Register users endpoint"
* )
*
* @OA\Tag(
*	name="Logout",
*	description="Logout users endpoint"
* )
*
* @OA\Tag(
*	name="Users",
*	description="Users endpoints"
* )
*
* @OA\Tag(
*	name="Profile",
*	description="User profile endpoints"
* )
*
* @OA\Tag(
*	name="Freelancer Profile",
*	description="User freelancer profile endpoints"
* )
*
* @OA\Tag(
*	name="Profile Publications",
*	description="User freelancer publications endpoints"
* )
*
* @OA\Tag(
*	name="Favorites",
*	description="User favorites publications endpoints"
* )
*
* @OA\Tag(
*	name="Countries",
*	description="Countries endpoint"
* )
*
* @OA\Tag(
*	name="Languages",
*	description="Languages endpoints"
* )
*
* @OA\Tag(
*	name="Categories",
*	description="Categories endpoints"
* )
*
* @OA\Tag(
*	name="Publications",
*	description="Publications endpoints"
* )
*
* @OA\SecurityScheme(
*	securityScheme="bearerAuth",
*   in="header",
*   name="bearerAuth",
*   type="http",
*   scheme="bearer",
*   bearerFormat="JWT"
* )
*/
class ApiController extends Controller
{
	public function dataUser($user, $freelancer=false) {
		$user->rol=roleUser($user, false);
		$user->photo=(!is_null($user->photo)) ? $user->photo : '';
		if ($freelancer) {
			$user->freelancer=$this->dataFreelancer($user['freelancer'], $user['country']);
			$data=$user->only("id", "name", "lastname", "slug", "photo", "username", "email", "state", "rol", "freelancer");
		} else {
			$data=$user->only("id", "name", "lastname", "slug", "photo", "username", "email", "state", "rol");
		}
		
		return $data;
	}

	public function dataFreelancer($freelancer, $country) {
		if (!is_null($freelancer)) {
			$freelancer->description=(!is_null($freelancer->description)) ? $freelancer->description : '';
			$freelancer->country=(!is_null($country)) ? $country->name : '';
			$freelancer->languages=$freelancer['languages']->map(function($language) {
				return $language->only("id", "name");
			});
			$freelancer->categories=$freelancer['categories']->map(function($category) {
				return $category->only("id", "name", "slug");
			});
			$data=$freelancer->only("id", "description", "country", "languages", "categories");
		} else {
			$data=[];
		}

		return $data;
	}

	public function dataLanguage($language) {
		$data=$language->only("id", "name", "code", "native_name", "state");

		return $data;
	}

	public function dataCategory($category, $childrens=true) {
		if($childrens) {
			$category->parent=(!is_null($category['parent'])) ? $category['parent']->only("id", "name", "slug", "order", "state") : [];
			$category->childrens=$category['childrens']->map(function($children) {
				return $this->dataCategory($children, false);
			});

			$data=$category->only("id", "name", "slug", "order", "state", "parent", "childrens");
		} else {
			$data=$category->only("id", "name", "slug", "order", "state");
		}

		return $data;
	}

	public function dataPublication($publication) {
		$publication->categories=$publication['categories']->map(function($category) {
			return $category->only("id", "name", "slug");
		});
		$data=$publication->only("id", "name", "slug", "description", "content", "state", "categories");
		
		return $data;
	}

	public function dataFavorite($favorite) {
		$favorite->publication=$this->dataPublication($favorite['publication']);
		$data=$favorite->only("id", "publication");
		
		return $data;
	}
}
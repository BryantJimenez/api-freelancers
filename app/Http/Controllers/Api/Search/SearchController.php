<?php

namespace App\Http\Controllers\Api\Search;

use App\Models\Category;
use App\Models\IgnoredWord;
use App\Models\Publication\Publication;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Search\ApiSearchRequest;
use Illuminate\Http\Request;
use Spatie\Searchable\Search;
use Spatie\Searchable\ModelSearchAspect;
use Illuminate\Database\Eloquent\Builder;
use Str;

class SearchController extends ApiController
{
    /**
    *
    * @OA\Post(
    *   path="/api/v1/search",
    *   tags={"Search"},
    *   summary="Search publications",
    *   description="Search publications",
    *   operationId="getSearch",
    *   @OA\Parameter(
    *       name="search",
    *       in="query",
    *       description="Text for search publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Search publications.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   )
    * )
    */
    public function search(ApiSearchRequest $request) {
        $search="";
        $words=explode('-', Str::slug(request('search'), '-'));
        foreach ($words as $word) {
            $ignore=IgnoredWord::where('slug', $word)->first();
            if (is_null($ignore)) {
                $search.=($search!="") ? " ".$word: $word;
            }
        }

        $searchResults=(new Search())->registerModel(Publication::class, function(ModelSearchAspect $modelSearchAspect) {
            $modelSearchAspect->addSearchableAttribute('name')->addSearchableAttribute('description')->addSearchableAttribute('content')->with(['freelancer.user', 'categories'])->where('state', '1');
        })->perform($search);

        if ($searchResults->count()==0) {
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'No results are available for "'.request('search').'".', 'count' => $searchResults->count(), 'data' => []], 200);
        }

        $results=$searchResults->map(function($publication) {
            return $this->dataPublication($publication->searchable);
        });

        return response()->json(['code' => 200, 'status' => 'success', 'message' => $searchResults->count().' results found for "'.request('search').'".', 'count' => $searchResults->count(), 'data' => $results], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/search/category/{id}",
    *   tags={"Search"},
    *   summary="Search publications",
    *   description="Search publications for category",
    *   operationId="getSearchCategory",
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID of category",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="search",
    *       in="query",
    *       description="Text for search publication",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Search publications for category.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   )
    * )
    */
    public function searchCategory(ApiSearchRequest $request, Category $category) {
        $search="";
        $words=explode('-', Str::slug(request('search'), '-'));
        foreach ($words as $word) {
            $ignore=IgnoredWord::where('slug', $word)->first();
            if (is_null($ignore)) {
                $search.=($search!="") ? " ".$word: $word;
            }
        }

        $searchResults=(new Search())->registerModel(Publication::class, function(ModelSearchAspect $modelSearchAspect) use ($category) {
            $modelSearchAspect->addSearchableAttribute('name')->addSearchableAttribute('description')->addSearchableAttribute('content')->with(['freelancer.user', 'categories'])->whereHas('categories', function (Builder $query) use ($category) {
                $query->where('id', $category->id);
            })->where('state', '1');
        })->perform($search);

        if ($searchResults->count()==0) {
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'No results are available for "'.request('search').'".', 'count' => $searchResults->count(), 'data' => []], 200);
        }

        $results=$searchResults->map(function($publication) {
            return $this->dataPublication($publication->searchable);
        });

        return response()->json(['code' => 200, 'status' => 'success', 'message' => $searchResults->count().' results found for "'.request('search').'".', 'count' => $searchResults->count(), 'data' => $results], 200);
    }
}

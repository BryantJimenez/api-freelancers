<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class ProjectController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/projects",
    *   tags={"Projects"},
    *   summary="Get projects",
    *   description="Returns all projects",
    *   operationId="indexProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all projects.",
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
    	$projects=Project::with(['user', 'employer', 'proposal', 'payment'])->get()->map(function($project) {
    		return $this->dataProject($project);
    	});

    	$page=Paginator::resolveCurrentPage('page');
    	$pagination=new LengthAwarePaginator($projects, $total=count($projects), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
    	$pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/projects/{id}",
    *   tags={"Projects"},
    *   summary="Get project",
    *   description="Returns a single project",
    *   operationId="showProject",
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
    *       description="Show project.",
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
    public function show(Project $project) {
    	$project=$this->dataProject($project);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $project], 200);
    }
}

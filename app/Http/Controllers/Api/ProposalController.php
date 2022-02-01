<?php

namespace App\Http\Controllers\Api;

use App\Models\Proposal;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class ProposalController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/proposals",
    *   tags={"Proposals"},
    *   summary="Get proposals",
    *   description="Returns all proposals",
    *   operationId="indexProposal",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all proposals.",
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
    	$proposals=Proposal::with(['owner', 'receiver', 'chat_room.publication.categories', 'chat_room.publication.freelancer.user'])->get()->map(function($proposal) {
    		return $this->dataProposal($proposal);
    	});

    	$page=Paginator::resolveCurrentPage('page');
    	$pagination=new LengthAwarePaginator($proposals, $total=count($proposals), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
    	$pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

    	return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/proposals/{id}",
    *   tags={"Proposals"},
    *   summary="Get proposal",
    *   description="Returns a single proposal",
    *   operationId="showProposal",
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
    *       description="Show proposal.",
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
    public function show(Proposal $proposal) {
    	$proposal=$this->dataProposal($proposal);
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $proposal], 200);
    }
}

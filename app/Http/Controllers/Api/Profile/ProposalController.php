<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\Proposal;
use App\Models\Publication\Publication;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Proposal\ApiProposalStoreRequest;
use App\Http\Requests\Api\Proposal\ApiProposalUpdateRequest;
use Illuminate\Http\Request;
use Auth;

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
    *   )
    * )
    */
    public function index() {
    	$proposals=Proposal::with(['owner', 'receiver', 'chat_room.publication.categories', 'chat_room.publication.freelancer.user'])->where('owner_id', Auth::id())->orWhere('receiver_id', Auth::id())->get()->map(function($proposal) {
    		return $this->dataProposal($proposal);
    	});
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $proposals], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/proposals/{id}",
    *   tags={"Proposals"},
    *   summary="Register proposal",
    *   description="Create a new proposal",
    *   operationId="storeProposal",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Chat ID",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="start",
    *       in="query",
    *       description="Date start of project, format Y-m-d",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *			format="date"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="end",
    *       in="query",
    *       description="Date end of project, format Y-m-d",
    *       required=false,
    *       @OA\Schema(
    *           type="string",
    *			format="date"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="amount",
    *       in="query",
    *       description="Amount of proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="number",
    *			format="double"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="content",
    *       in="query",
    *       description="Content of proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="receiver_id",
    *       in="query",
    *       description="User to whom the proposal is sent",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=201,
    *       description="Registered proposal.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
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
 	public function store(ApiProposalStoreRequest $request, ChatRoom $chat) {
 		$data=array('start' => request('start'), 'end' => request('end'), 'content' => request('content'), 'amount' => request('amount'), 'owner_id' => Auth::id(), 'receiver_id' => request('receiver_id'), 'chat_room_id' => $chat->id);
 		$proposal=Proposal::create($data);

 		if ($proposal) {
 			$proposal=Proposal::with(['owner', 'receiver', 'chat_room.publication'])->where('id', $proposal->id)->first();
 			$proposal=$this->dataProposal($proposal);
 			return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The proposal has been successfully send.', 'data' => $proposal], 201);
 		}

 		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
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
    *       description="Search for ID proposal",
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
    *       response=404,
    *       description="No results found."
    *   )
    * )
    */
    public function show(Proposal $proposal) {
        if ($proposal->owner_id!=Auth::id() && $proposal->receiver_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal does not belong to this user.'], 403);
        }

        $proposal=$this->dataProposal($proposal);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $proposal], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/proposals/{id}",
    *   tags={"Proposals"},
    *   summary="Update proposal",
    *   description="Update a single proposal",
    *   operationId="updateProposal",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="start",
    *       in="query",
    *       description="Date start of project, format Y-m-d",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *           format="date"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="end",
    *       in="query",
    *       description="Date end of project, format Y-m-d",
    *       required=false,
    *       @OA\Schema(
    *           type="string",
    *           format="date"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="amount",
    *       in="query",
    *       description="Amount of proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="number",
    *           format="double"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="content",
    *       in="query",
    *       description="Content of proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Update proposal.",
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
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function update(ApiProposalUpdateRequest $request, Proposal $proposal) {
        if ($proposal->owner_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal does not belong to this user.'], 403);
        }

        $data=array('start' => request('start'), 'end' => request('end'), 'content' => request('content'), 'amount' => request('amount'));
        $proposal->fill($data)->save();
        if ($proposal) {
            $proposal=Proposal::with(['owner', 'receiver', 'chat_room.publication'])->where('id', $proposal->id)->first();
            $proposal=$this->dataProposal($proposal);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The proposal has been edited successfully.', 'data' => $proposal], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/proposals/{id}/cancel",
    *   tags={"Proposals"},
    *   summary="Cancel proposal",
    *   description="Cancel a single proposal",
    *   operationId="cancelProposal",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Cancel proposal.",
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
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function cancel(Request $request, Proposal $proposal) {
        if ($proposal->owner_id!=Auth::id() && $proposal->receiver_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal does not belong to this user.'], 403);
        }

        $proposal->fill(['state' => "0"])->save();
        if ($proposal) {
            $proposal=$this->dataProposal($proposal);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The proposal has been successfully cancelled.', 'data' => $proposal], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/proposals/{id}/accept",
    *   tags={"Proposals"},
    *   summary="Accept proposal",
    *   description="Accept a single proposal",
    *   operationId="acceptProposal",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID proposal",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Accept proposal.",
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
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function accept(Request $request, Proposal $proposal) {
        if ($proposal->receiver_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal cannot be accepted by this user.'], 403);
        }

        $proposal->fill(['state' => "1"])->save();
        if ($proposal) {
            $proposal=$this->dataProposal($proposal);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The proposal has been successfully accepted.', 'data' => $proposal], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

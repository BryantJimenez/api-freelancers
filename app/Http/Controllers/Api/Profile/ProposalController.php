<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\Chat\ChatRoom;
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
    *   path="/api/v1/profile/proposals",
    *   tags={"Profile Proposals"},
    *   summary="Get proposals",
    *   description="Returns all proposals",
    *   operationId="indexProfileProposal",
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
    *   path="/api/v1/profile/proposals/{id}",
    *   tags={"Profile Proposals"},
    *   summary="Register proposal",
    *   description="Create a new proposal",
    *   operationId="storeProfileProposal",
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
 	public function store(ApiProposalStoreRequest $request, ChatRoom $chat) {
        if ($chat['users']->where('id', request('receiver_id'))->count()==0) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'The user who receives the proposal does not belong to the chat.'], 403);
        }

 		$data=array('start' => request('start'), 'end' => request('end'), 'content' => request('content'), 'amount' => request('amount'), 'owner_id' => Auth::id(), 'receiver_id' => request('receiver_id'), 'chat_room_id' => $chat->id);
 		$proposal=Proposal::create($data);

 		if ($proposal) {
 			$proposal=Proposal::with(['owner', 'receiver', 'chat_room.publication.categories', 'chat_room.publication.freelancer.user'])->where('id', $proposal->id)->first();
 			$proposal=$this->dataProposal($proposal);
 			return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The proposal has been successfully send.', 'data' => $proposal], 201);
 		}

 		return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
 	}

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/proposals/{id}",
    *   tags={"Profile Proposals"},
    *   summary="Get proposal",
    *   description="Returns a single proposal",
    *   operationId="showProfileProposal",
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
    *   path="/api/v1/profile/proposals/{id}",
    *   tags={"Profile Proposals"},
    *   summary="Update proposal",
    *   description="Update a single proposal",
    *   operationId="updateProfileProposal",
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

        if ($proposal->state=='Accepted') {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal has already been accepted, you cannot edit it.'], 403);
        }

        if ($proposal->state=='Cancelled') {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal has already been cancelled, you cannot edit it.'], 403);
        }

        $data=array('start' => request('start'), 'end' => request('end'), 'content' => request('content'), 'amount' => request('amount'));
        $proposal->fill($data)->save();
        if ($proposal) {
            $proposal=Proposal::with(['owner', 'receiver', 'chat_room.publication.categories', 'chat_room.publication.freelancer.user'])->where('id', $proposal->id)->first();
            $proposal=$this->dataProposal($proposal);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The proposal has been edited successfully.', 'data' => $proposal], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/proposals/{id}/cancel",
    *   tags={"Profile Proposals"},
    *   summary="Cancel proposal",
    *   description="Cancel a single proposal",
    *   operationId="cancelProfileProposal",
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

        if ($proposal->state=='Cancelled') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This proposal has already been cancelled.'], 200);
        }

        if ($proposal->state=='Accepted') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This proposal has already been accepted, you cannot cancel it.'], 200);
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
    *   path="/api/v1/profile/proposals/{id}/accept",
    *   tags={"Profile Proposals"},
    *   summary="Accept proposal",
    *   description="Accept a single proposal",
    *   operationId="acceptProfileProposal",
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
        if ($proposal->state=='Cancelled') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This proposal has already been cancelled, you cannot accept it.'], 200);
        }

        if ($proposal->state=='Accepted') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This proposal has already been accepted.'], 200);
        }

        if ($proposal->receiver_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This proposal cannot be accepted by this user.'], 403);
        }

        $proposal->fill(['state' => "1"])->save();
        if ($proposal) {
            $employer_id=($proposal['chat_room']['publication']['freelancer']['user']->id!=$proposal->receiver_id) ? $proposal->receiver_id: $proposal->owner_id;
            $data=array('start' => date('Y-m-d'), 'end' => $proposal->end, 'amount' => $proposal->amount, 'content' => $proposal->content, 'user_id' => $proposal['chat_room']['publication']['freelancer']['user']->id, 'employer_id' => $employer_id, 'proposal_id' => $proposal->id);
            Project::create($data);

            $proposal=$this->dataProposal($proposal);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The proposal has been successfully accepted.', 'data' => $proposal], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

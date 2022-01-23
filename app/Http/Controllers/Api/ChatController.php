<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat\ChatRoom;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class ChatController extends ApiController
{
    /**
    *
    * @OA\Put(
    *   path="/api/v1/chats/{id}/deactivate",
    *   tags={"Chats"},
    *   summary="Deactivate chat",
    *   description="Deactivate a single chat",
    *   operationId="deactivateChat",
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
    *       description="Deactivate chat.",
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
    public function deactivate(Request $request, ChatRoom $chat) {
        $chat->fill(['state' => "0"])->save();
        if ($chat) {
            $chat=$this->dataChat($chat);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The chat has been successfully deactivated.', 'data' => $chat], 200);
        }
        
        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/chats/{id}/activate",
    *   tags={"Chats"},
    *   summary="Activate chat",
    *   description="Activate a single chat",
    *   operationId="activateChat",
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
    *       description="Activate chat.",
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
    public function activate(Request $request, ChatRoom $chat) {
        $chat->fill(['state' => "1"])->save();
        if ($chat) {
            $chat=$this->dataChat($chat);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The chat has been successfully activated.', 'data' => $chat], 200);
        }
        
        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}

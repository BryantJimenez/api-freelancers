<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\User;
use App\Models\Publication\Publication;
use App\Models\Chat\ChatRoom;
use App\Models\Chat\RoomUser;
use App\Models\Chat\ChatMessage;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Message\ApiMessageStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Auth;
use Arr;

class ChatController extends ApiController
{
    /**
    *
    * @OA\Get(
    *   path="/api/v1/chats",
    *   tags={"Profile Chats"},
    *   summary="Get chats",
    *   description="Returns all chats",
    *   operationId="indexChat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all chats.",
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
    	$user=User::with(['chats.publication', 'chats.users'])->where('id', Auth::id())->first();
    	$chats=$user['chats']->map(function($chat) {
    		return $this->dataChat($chat);
    	});
    	return response()->json(['code' => 200, 'status' => 'success', 'data' => $chats], 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/chats/{id}",
    *   tags={"Profile Chats"},
    *   summary="Register chat",
    *   description="Create a new chat",
    *   operationId="storeChat",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Publication ID",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=201,
    *       description="Registered chat.",
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
    public function store(Request $request, Publication $publication) {
        if ($publication['freelancer']['user']->id==Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This publication belongs to this user.'], 403);
        }

        $data=array('name' => $publication->name, 'publication_id' => $publication->id);
        $chat=ChatRoom::create($data);
        if ($chat) {
            RoomUser::create(['user_id' => $publication['freelancer']['user']->id, 'chat_room_id' => $chat->id]);
            RoomUser::create(['user_id' => Auth::id(), 'chat_room_id' => $chat->id]);

            $chat=ChatRoom::with(['publication', 'users', 'messages'])->where('id', $chat->id)->first();
            $chat=$this->dataChat($chat);
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The chat has been successfully created.', 'data' => $chat], 201);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/chats/{id}",
    *   tags={"Profile Chats"},
    *   summary="Get chat",
    *   description="Returns a single chat",
    *   operationId="showChat",
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
    *       description="Show chat.",
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
    public function show(ChatRoom $chat) {
        $chat=$this->dataChat($chat);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $chat], 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/chats/{id}/messages",
    *   tags={"Profile Chats"},
    *   summary="Get chat messages",
    *   description="Returns messages of a single chat",
    *   operationId="getChatMessage",
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
    *       description="Show chat messages.",
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
    public function messages(ChatRoom $chat) {
        $messages=ChatMessage::where('chat_room_id', $chat->id)->orderBy('id', 'DESC')->limit(500)->get()->map(function($message) {
            return $this->dataMessage($message);
        });

        $page=Paginator::resolveCurrentPage('page');
        $pagination=new LengthAwarePaginator($messages, $total=count($messages), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        $pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

        return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/chats/{id}/message",
    *   tags={"Profile Chats"},
    *   summary="Send new message",
    *   description="Send a new message in a chat",
    *   operationId="storeChatMessage",
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
    *   @OA\Parameter(
    *       name="message",
    *       in="query",
    *       description="New Message",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="New message.",
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
    public function message(ApiMessageStoreRequest $request, ChatRoom $chat) {
        $message=ChatMessage::create(['message' => request('message'), 'user_id' => Auth::id(), 'chat_room_id' => $chat->id]);

        if ($message) {
            $message=ChatMessage::where('id', $message->id)->first();
            $message=$this->dataMessage($message);
            return response()->json(['code' => 201, 'status' => 'success', 'message' => 'The message has been successfully created.', 'data' => $message], 201);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/chats/{id}/read",
    *   tags={"Profile Chats"},
    *   summary="Read chat messages",
    *   description="Read messages of a single chat",
    *   operationId="readChatMessage",
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
    *       description="Read chat messages.",
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
    public function read(ChatRoom $chat) {
        $count=ChatMessage::where([['read', '0'], ['user_id', '!=', Auth::id()], ['chat_room_id', $chat->id]])->count();
        if ($count>0) {
            $read=ChatMessage::where([['read', '0'], ['user_id', '!=', Auth::id()], ['chat_room_id', $chat->id]])->update(['read' => '1']);
            if ($read) {
                return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The messages have been read successfully.'], 200);
            }
        }
        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'There are no new messages.'], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Models\RoomChat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'user_uuid' => 'required|string|max:255'
        ]);

        $hexChars = array_merge(
            array_fill(0, 3, str_split('0123456789')),
            array_fill(0, 1, str_split('ABCDEF'))
        );
        $hexChars = array_merge(...$hexChars);        

        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $hexChars[random_int(0, count($hexChars) - 1)];
            }
        } while (Room::where('code', $code)->exists());

        $readable_code = '#' . $code;

        $room = Room::create([
            'uuid' => Str::uuid(),
            'code' => $readable_code,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_by' => $validated['user_uuid'],
        ]);

        $user = User::where('uuid', $validated['user_uuid'])->firstOrFail();
        $room->users()->attach($user->uuid);

        return response()->json($room);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'user_uuid' => 'required|string|exists:users,uuid',
            'code' => 'required|string|size:5'
        ]);

        $validated['code'] = strtoupper(ltrim($validated['code'], '#'));
        $room = Room::where('code', '#' . $validated['code'])->firstOrFail();

        $room = Room::where('code', $validated['code'])->firstOrFail();
        $user = User::where('uuid', $validated['user_uuid'])->firstOrFail();

        if (!$room->users()->where('users.uuid', $user->uuid)->exists()) {
            $room->users()->attach([$user->uuid => ['room_uuid' => $room->uuid]]);
        }

        return response()->json([
            'message' => 'User joined the room',
            'uuid' => $room->uuid
        ]);
    }

    public function show_all(Request $request)
    {
        $validated = $request->validate([
            'user_uuid' => 'required|string|max:255'
        ]);

        $rooms = Room::where('created_by', $validated['user_uuid'])->get();

        $rooms->transform(function ($room) {
            $lastChat = RoomChat::where('room_uuid', $room->uuid)
                ->latest('created_at')
                ->first();

            $room->last_active = $lastChat ? $lastChat->created_at->toDateTimeString() : null;
            $room->user_count = RoomChat::where('room_uuid', $room->uuid)->distinct('user_uuid')->count('user_uuid');
            return $room;
        });

        return response()->json($rooms);
    }

    public function show($uuid)
    {
        return Room::where('uuid', $uuid)->firstOrFail();
    }

    public function update(Request $request, $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $room->update($validated);

        return response()->json($room);
    }

    public function destroy($uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();
        $room->delete();

        return response()->json(null, 204);
    }

    public function userCount($uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();
        $count = $room->users()->count();

        return response()->json(['user_count' => $count]);
    }

    public function userRooms($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $rooms = $user->rooms()->get()->map(function ($room) {
            return [
                'uuid' => $room->uuid,
                'name' => $room->name,
                'user_count' => $room->users()->count(),
                'last_activity' => $room->created_at->toDateTimeString()
            ];
        });

        return response()->json($rooms);
    }

    public function chatCreate(Request $request)
    {
        $validated = $request->validate([
            'room_uuid' => 'required|exists:rooms,uuid',
            'user_uuid' => 'required|exists:users,uuid',
            'chat' => 'required|string',
            'chat_type' => 'required|string',
        ]);

        return RoomChat::create($validated);
    }

    public function chatShowAll(Request $request)
    {
        $validated = $request->validate([
            'room_uuid' => 'required|exists:rooms,uuid',
        ]);

        $chats = RoomChat::with(['user:id,uuid,name,user_type,first_name,last_name,user_image'])
            ->where('room_uuid', $validated['room_uuid'])
            ->whereNull('deleted_at')
            ->get();

        return response()->json($chats);
    }

    public function chatDestroy(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:room_chat,id',
        ]);

        RoomChat::where('id', $validated['id'])->delete();

        return response()->noContent();
    }

}
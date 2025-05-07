<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
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
            'code' => 'required|string|size:5|exists:rooms,code'
        ]);

        $room = Room::where('code', $validated['code'])->firstOrFail();
        $user = \App\Models\User::where('uuid', $validated['user_uuid'])->firstOrFail();

        if (!$room->users()->where('users.uuid', $user->uuid)->exists()) {
            $room->users()->attach($user->uuid);
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
}
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
        return Room::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $room = Room::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name']
        ]);

        return response()->json($room, 201);
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
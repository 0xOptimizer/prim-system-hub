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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'user_uuid' => 'required|string|max:255'
        ]);

        $code = Str::random(4, '0123456789abcdef0123456789');
        while (Room::where('code', $code)->exists()) {
            $code = Str::random(4, '0123456789abcdef0123456789');
        }
        
        $uuid = '#' . Str::random(4, '0123456789abcdef0123456789');

        $room = Room::create([
            'uuid' => $uuid,
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_by' => $validated['user_uuid'],
        ]);

        return response()->json($room, 201);
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
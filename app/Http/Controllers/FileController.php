<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiFile;
use App\Models\Room;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function listUserFiles($uuid)
    {
        $files = AiFile::where('created_by', $uuid)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($file) {
                $room = Room::where('code', $file->room_code)->first();

                return [
                    'id' => $file->id,
                    'filename' => $file->filename,
                    'path' => $file->path,
                    'type' => $file->type,
                    'room_code' => $file->room_code,
                    'created_by' => $file->created_by,
                    'created_at' => $file->created_at,
                    'room' => $room ? [
                        'uuid' => $room->uuid,
                        'code' => $room->code,
                        'name' => $room->name,
                        'description' => $room->description
                    ] : null
                ];
            });

        return response()->json($files);
    }

    public function totalUserFiles($uuid)
    {
        $count = AiFile::where('created_by', $uuid)->count();

        return response()->json(['total_files' => $count]);
    }

    public function getFileDetails($id, Request $request)
    {
        $userId = $request->user()->id;

        $file = AiFile::where('id', $id)
            ->where('created_by', $userId)
            ->firstOrFail();

        $room = Room::where('code', $file->room_code)->first();

        return response()->json([
            'id' => $file->id,
            'filename' => $file->filename,
            'path' => $file->path,
            'type' => $file->type,
            'room_code' => $file->room_code,
            'created_by' => $file->created_by,
            'created_at' => $file->created_at,
            'room' => $room ? [
                'uuid' => $room->uuid,
                'code' => $room->code,
                'name' => $room->name,
                'description' => $room->description
            ] : null
        ]);
    }

    public function deleteFile($id, Request $request)
    {
        $userId = $request->user()->id;

        $file = AiFile::where('id', $id)
            ->where('created_by', $userId)
            ->firstOrFail();

        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
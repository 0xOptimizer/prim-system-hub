<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AiFile;
use App\Models\AiResponse;
use App\Models\AiUsage;
use App\Models\Room;
use App\Models\RoomChat;
use App\Models\User;
use App\Models\VisitorLog;

class TelemetriesController extends Controller
{
    public function hourly(Request $request)
    {
        return response()->json(VisitorLog::groupByPeriod('hourly'));
    }

    public function daily(Request $request)
    {
        return response()->json(VisitorLog::groupByPeriod('daily'));
    }

    public function weekly(Request $request)
    {
        return response()->json(VisitorLog::groupByPeriod('weekly'));
    }

    public function monthly(Request $request)
    {
        return response()->json(VisitorLog::groupByPeriod('monthly'));
    }

    public function overviewCounts()
    {
        return response()->json([
            'users' => User::count(),
            'rooms' => Room::count(),
            'room_chats' => RoomChat::count(),
            'ai_files' => AiFile::count(),
            'ai_responses' => AiResponse::count(),
            'ai_usages' => AiUsage::count(),
        ]);
    }

}
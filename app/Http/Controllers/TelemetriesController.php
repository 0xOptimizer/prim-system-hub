<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    public function monthly(Request $request)
    {
        return response()->json(VisitorLog::groupByPeriod('monthly'));
    }
}
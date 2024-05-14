<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class MainController extends Controller
{
    public function main(Request $request)
    {

        $serverStatus = true;
        $serverVersion = '1.05.1';
        $currentDateTime = Carbon::now();
        $formattedDate = $currentDateTime->format('Y-m-d');
        $formattedTime = $currentDateTime->format('H:i:s');

        if ($serverStatus) {
            return response()->json([
                'status' => 'Server is on',
                'date' => $formattedDate,
                'time' => $formattedTime,
                'version' => $serverVersion,
            ], Response::HTTP_OK);
        } else {
            return response()->json(['status' => 'Server is down'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}

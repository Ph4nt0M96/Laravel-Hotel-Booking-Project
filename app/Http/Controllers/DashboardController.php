<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\view;
use App\Models\room_type;
use App\Models\extra_service;

class DashboardController extends Controller
{
    public function index()
    {

        $views = View::all()->where('delete_status', '0');
        $popularSuites = Room_Type::all()->where('delete_status', '0');
        $services = Extra_Service::all();

        return view('dashboard', compact('views', 'popularSuites', 'services'));
    }
    
    public function search(Request $request)
    {
        $query = $request->input('query');

        $viewResults = View::where('view_name', 'LIKE', "%$query%")
            ->orWhere('description', 'LIKE', "%$query%")->get();
        $roomResults = Room_Type::where('room_type', 'LIKE', "%$query%")
            ->orWhere('description', 'LIKE', "%$query%")->get();
        $serviceResults = Extra_Service::where('service_name', 'LIKE', "%$query%")
            ->orWhere('description', 'LIKE', "%$query%")->get();

        $results = $viewResults->merge($roomResults)->merge($serviceResults);

        $views = View::all();
        $popularSuites = Room_Type::all();
        $services = Extra_Service::all();

        return view('dashboard', compact('results', 'views', 'popularSuites', 'services'));
    }
}

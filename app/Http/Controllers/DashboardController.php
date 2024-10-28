<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Services\InsorpaApiService;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $insorpaApi = new InsorpaApiService();
        $today = date('Y-m-d');
        $statistics = $insorpaApi->get('/dtes_statistics');
        $statistics_today = $insorpaApi->get('/dtes_statistics?fecha=' . $today);
        $datos_empresa = $insorpaApi->get('/datos_empresa/1');

        return view('home', compact('statistics', 'datos_empresa', 'statistics_today'));
    }
}

<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\InsorpaApiService;


class InvoicesController extends Controller
{
    public function index()
    {
        $insorpaApi = new InsorpaApiService();
        if(request()->has('fecha') && request()->has('hasta')){
            $response = $insorpaApi->get('/dtes?start_date=' . request('fecha') . ' 00:00:00&end_date=' . request('hasta'). ' 23:59:59&items_per_page=10000');
            $statistics = $insorpaApi->get('/dtes_statistics?start_date=' . request('fecha'). ' 00:00:00&end_date=' . request('hasta'). ' 23:59:59');
        }elseif(request()->has('fecha')){
            $response = $insorpaApi->get('/dtes?start_date=' . request('fecha') . ' 00:00:00&end_date=' . request('fecha'). ' 23:59:59&items_per_page=10000');
            $statistics = $insorpaApi->get('/dtes_statistics?fecha=' . request('fecha') . ' 00:00:00&end_date=' . request('fecha'). ' 23:59:59');
        } else {
            $response = $insorpaApi->get('/dtes?items_per_page=10000');
            $statistics = $insorpaApi->get('/dtes_statistics');
        }
        $dtes = $response['data'];

        // Check query params to filter invoices
        if (request()->has('type')) {
            $dtes = array_filter($dtes, function ($dte) {
                return $dte['estado'] == request('type');
            });
        }

        $dtes = array_map(function ($dte) {
            $dte['documento'] = json_decode($dte["documento"]);
            return $dte;
        }, $dtes);

        return view('invoices', ['invoices' => $dtes, 'fecha' => request('fecha'), 'hasta' => request('hasta'), 'statistics' => $statistics]);
    }

    public function download_dtes()
    {
        $response = Http::get('http://localhost:8000/dtes/');
        $dtes = $response->json();

        
    }

    public function compile_dtes()
    {
        $contingencia = Http::get('http://localhost:8000/contingencia/estado')->json();
        $contingencia = $contingencia['message'];
        return view('download', ['contingencia' => $contingencia]);
    }
}
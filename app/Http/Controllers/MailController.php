<?php

namespace App\Http\Controllers;

use App\Mail\DteMail;
use App\Services\InsorpaApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class MailController extends Controller
{
    public function mandar_correo(Request $request)
    {
        try {
            $request->validate([
                'cod_generacion' => 'required|string',
                'correo' => 'required|email',
            ]);

            $insorpaApi = new InsorpaApiService();

            $codGeneracion = $request->input('cod_generacion');
            $correo = $request->input('correo');

            $dte = $insorpaApi->get("/dtes/{$codGeneracion}");
            $documento = json_decode($dte['documento'], true);

            Mail::to($correo)->send(new DteMail(
                $codGeneracion,
                $dte["tipo_dte"] == "14" ? $documento["sujetoExcluido"]["nombre"] : $documento["receptor"]["nombre"],
                $documento["identificacion"]["numeroControl"],
                $dte["sello_recibido"],
                $dte["fh_procesamiento"],
                $dte["estado"],
                $dte["enlace_pdf"],
                $dte["enlace_json"],
            ));

            return redirect()->route('invoices.index')->with('success', 'Correo enviado');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('invoices.index')->with('error', 'Error al enviar correo');
        }
    }
}
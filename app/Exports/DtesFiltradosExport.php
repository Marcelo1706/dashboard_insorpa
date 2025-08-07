<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DtesFiltradosExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $params;
    protected $tiposDte;

    public function __construct($params)
    {
        $this->params = $params;
        $this->tiposDte = [
            '01' => 'Factura Electrónica',
            '03' => 'Crédito Fiscal',
            '04' => 'Nota de Remisión',
            '05' => 'Nota de Crédito',
            '07' => 'Comprobante de Retención',
            '11' => 'Factura de Exportación',
            '14' => 'Factura de Sujeto Excluido',
        ];
    }

    public function array(): array
    {
        // Obtener todos los datos sin paginación
        $data = DB::connection('pgsql')->select("
            SELECT * FROM get_dtes_filtrados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ORDER BY get_dtes_filtrados.fh_procesamiento DESC
        ", $this->params);

        $rows = [];
        foreach ($data as $dte) {
            $rows[] = [
                \Carbon\Carbon::parse($dte->fh_procesamiento)->setTimezone('America/El_Salvador')->format('Y-m-d H:i:s'),
                $dte->tienda,
                $dte->transaccion,
                $dte->documento_receptor,
                $dte->nombre_receptor,
                $dte->neto,
                $dte->iva,
                $dte->total,
                $this->tiposDte[$dte->tipo_dte] ?? 'Desconocido',
                $dte->estado,
                $dte->observaciones != '[]' ? $dte->observaciones : '',
                $dte->cod_generacion,
                $dte->numero_control,
                $dte->sello_recibido,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tienda',
            'Transacción',
            'Documento Receptor',
            'Nombre Receptor',
            'Importe',
            'IVA',
            'Total',
            'Tipo DTE',
            'Estado',
            'Observación',
            'Código Generación',
            'Número de Control',
            'Sello de Recepción',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E3F2FD',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}

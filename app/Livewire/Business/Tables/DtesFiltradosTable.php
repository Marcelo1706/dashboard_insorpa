<?php

namespace App\Livewire\Business\Tables;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\DtesFiltradosExport;
use Maatwebsite\Excel\Facades\Excel;

class DtesFiltradosTable extends Component
{
    use WithPagination;

    // Filtros
    public $fecha_inicio;
    public $fecha_fin;
    public $estado;
    public $tienda;
    public $documento_receptor;
    public $nombre_receptor;
    public $cod_generacion;
    public $sello_recibido;
    public $numero_control;
    public $total_min;
    public $total_max;

    public $perPage = 10;
    public $page = 1;
    public $totalRecords = 0;
    public $totalFiltered = 0;

    public $tiposDte = [
        '01' => 'Factura Electrónica',
        '03' => 'Crédito Fiscal',
        '04' => 'Nota de Remisión',
        '05' => 'Nota de Crédito',
        '07' => 'Comprobante de Retención',
        '11' => 'Factura de Exportación',
        '14' => 'Factura de Sujeto Excluido',
    ];

    protected $queryString = [
        'fecha_inicio', 'fecha_fin', 'estado', 'tienda', 'documento_receptor', 'nombre_receptor',
        'cod_generacion', 'sello_recibido', 'numero_control', 'total_min', 'total_max', 'perPage', 'page'
    ];

    public function updating($name)
    {
        $this->resetPage();
    }

    public function nextPage()
    {
        if ($this->page * $this->perPage < $this->totalFiltered) {
            $this->page++;
        }
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function hasNextPage()
    {
        return $this->page * $this->perPage < $this->totalFiltered;
    }

    public function hasPreviousPage()
    {
        return $this->page > 1;
    }

    public function getFromRecord()
    {
        return (($this->page - 1) * $this->perPage) + 1;
    }

    public function getToRecord()
    {
        return min($this->page * $this->perPage, $this->totalFiltered);
    }

    public function render()
    {
        $offset = ($this->page - 1) * $this->perPage;

        $params = [
            !empty($this->fecha_inicio) ? date('Y-m-d H:i:s', strtotime($this->fecha_inicio . ' +6 hours')) : null,
            !empty($this->fecha_fin) ? date('Y-m-d H:i:s', strtotime($this->fecha_fin . ' +6 hours')) : null,
            !empty($this->estado) ? $this->estado : null,
            !empty($this->tienda) ? $this->tienda : null,
            !empty($this->documento_receptor) ? $this->documento_receptor : null,
            !empty($this->nombre_receptor) ? $this->nombre_receptor : null,
            !empty($this->cod_generacion) ? $this->cod_generacion : null,
            !empty($this->sello_recibido) ? $this->sello_recibido : null,
            !empty($this->numero_control) ? $this->numero_control : null,
            !empty($this->total_min) ? $this->total_min : null,
            !empty($this->total_max) ? $this->total_max : null
        ];

        $data = DB::connection('pgsql')->select("
            SELECT * FROM get_dtes_filtrados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ORDER BY get_dtes_filtrados.fh_procesamiento DESC
            OFFSET ? LIMIT ?
        ", array_merge($params, [$offset, $this->perPage]));

        $tiendas = DB::connection('pgsql')->select("SELECT DISTINCT apendice_item->>'valor' AS tienda
            FROM dte_generados dg,
                jsonb_array_elements(dg.documento::jsonb->'apendice') AS apendice_item
            WHERE apendice_item->>'campo' = 'Tienda'
            ORDER BY tienda;");

        // Total manual (para paginación)
        $total = DB::connection('pgsql')->selectOne("
            SELECT count(*) FROM get_dtes_filtrados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", $params)->count;

        // Total de registros sin filtros
        $totalRecords = DB::connection('pgsql')->selectOne("
            SELECT count(*) FROM get_dtes_filtrados(null, null, null, null, null, null, null, null, null, null, null)
        ")->count;

        $this->totalFiltered = $total;
        $this->totalRecords = $totalRecords;

        return view('livewire.business.tables.dtes-filtrados-table', [
            'dtes' => collect($data),
            'total' => $total,
            'tiendas' => collect($tiendas),
            'totalRecords' => $this->totalRecords,
            'totalFiltered' => $this->totalFiltered,
            'fromRecord' => $this->getFromRecord(),
            'toRecord' => $this->getToRecord(),
        ]);
    }

    public function resetFilters()
    {
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->estado = '';
        $this->tienda = '';
        $this->documento_receptor = '';
        $this->nombre_receptor = '';
        $this->cod_generacion = '';
        $this->sello_recibido = '';
        $this->numero_control = '';
        $this->total_min = '';
        $this->total_max = '';
        
        $this->page = 1;
        $this->resetPage();
    }

    public function exportToExcel()
    {
        $params = [
            !empty($this->fecha_inicio) ? date('Y-m-d H:i:s', strtotime($this->fecha_inicio . ' +6 hours')) : null,
            !empty($this->fecha_fin) ? date('Y-m-d H:i:s', strtotime($this->fecha_fin . ' +6 hours')) : null,
            !empty($this->estado) ? $this->estado : null,
            !empty($this->tienda) ? $this->tienda : null,
            !empty($this->documento_receptor) ? $this->documento_receptor : null,
            !empty($this->nombre_receptor) ? $this->nombre_receptor : null,
            !empty($this->cod_generacion) ? $this->cod_generacion : null,
            !empty($this->sello_recibido) ? $this->sello_recibido : null,
            !empty($this->numero_control) ? $this->numero_control : null,
            !empty($this->total_min) ? $this->total_min : null,
            !empty($this->total_max) ? $this->total_max : null
        ];

        $fileName = 'Reporte de DTEs - ' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new DtesFiltradosExport($params), $fileName);
    }
}

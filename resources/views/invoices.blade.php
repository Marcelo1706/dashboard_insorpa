@extends('layouts.app')
@php
    $tiposDte = [
        '01' => 'Factura Electrónica',
        '03' => 'Crédito Fiscal',
        '04' => 'Nota de Remisión',
        '05' => 'Nota de Crédito',
        '07' => 'Comprobante de Retención',
        '11' => 'Factura de Exportación',
        '14' => 'Factura de Sujeto Excluido',
    ];

    $receptores_nit = ['03', '05'];
    $receptores_num = ['01', '07', '11', '14'];
@endphp
@include('layouts.navbar')
@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '{{ session('success') }}',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        @endif
        <div class="row justify-content-center">
            <div class="col-md-12">
                <form action="" id="formConsulta">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-3 mb-3">
                            <label class="form-label" for="fecha">Desde:</label>
                            <input type="date" class="form-control" id="fecha" name="fecha"
                                    value="{{ $fecha }}" required>
                        </div>
                        <div class="form-group col-sm-3 mb-3">
                            <label class="form-label" for="fecha">Hasta:</label>
                            <input type="date" class="form-control" id="hasta" name="hasta"
                                    value="{{ $hasta }}" required>
                        </div>
                        <div class="form-group col-sm-3 mb-3">
                            <label class="form-label" for="estado">Estado:</label>
                            <select name="type" id="estado" class="form-select">
                                <option value="ALL">Todos</option>
                                <option value="PROCESADO" @if ($estado == 'PROCESADO') selected @endif>Procesado
                                </option>
                                <option value="RECHAZADO" @if ($estado == 'RECHAZADO') selected @endif>Rechazado
                                </option>
                                <option value="CONTINGENCIA" @if ($estado == 'CONTINGENCIA') selected @endif>
                                    Contingencia</option>
                                <option value="ANULADO" @if ($estado == 'ANULADO') selected @endif>Anulado</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-3 mb-3">
                            <label class="form-label" for="tienda">Tienda:</label>
                            <select name="tienda" id="tienda" class="form-select">
                                <option value="ALL">Todas</option>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-3 text-center">
                            <button type="submit" class="btn btn-primary">Consultar DTEs</button>
                        </div>
                    </div>
                </form>
                <div class="row mt-5 mb-2">
                    <div class="col-md-12">
                        <div class="header-content">
                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-4">
                                    <h1 class="header-title text-center">
                                        Documentos Emitidos
                                        @if ($fecha)
                                            : {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                        @endif
                                        @if ($hasta)
                                            - {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                                        @endif
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row my-4">
                    <table class="small table table-bordered table-hover table-striped w-100 align-middle" id="invoicesTable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tienda</th>
                                <th>Transacción</th>
                                <th>Receptor</th>
                                <th>Importe</th>
                                <th>IVA</th>
                                <th>Total</th>
                                <th>Tipo DTE</th>
                                <th>Estado</th>
                                <th>Observación</th>
                                <th>Cod. Generación.</th>
                                <th>Número de Control</th>
                                <th>Sello de Recepción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Fecha</th>
                                <th>Tienda</th>
                                <th>Transacción</th>
                                <th>Receptor</th>
                                <th>Importe</th>
                                <th>IVA</th>
                                <th>Total</th>
                                <th>Tipo DTE</th>
                                <th>Estado</th>
                                <th>Observación</th>
                                <th>Cod. Generación.</th>
                                <th>Número de Control</th>
                                <th>Sello de Recepción</th>
                                <th>Acciones</th>
                            </tr>
                        </tfoot>
                    </table>
                    {{-- <table class="table table-bordered table-hover table-striped w-100 align-middle" id="invoicesTable">
                        <thead>
                            <tr class="align-middle text-center">
                                <th style="width: 5%;"></th>
                                <th style="width: 10%;">Tipo de Documento</th>
                                <th style="width: 15%;">Información Hacienda</th>
                                <th style="width: 15%;">Receptor</th>
                                <th style="width: 10%;">Fecha Procesamiento</th>
                                <th style="width: 5%">Totales</th>
                                <th style="width: 10%;">Estado</th>
                                <th style="width: 15%;">Observaciones</th>
                                <th style="width: 15%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                @if ($invoice['estado'] == 'RECHAZADO')
                                    <tr class="table-danger small">
                                    @elseif ($invoice['estado'] == 'CONTINGENCIA')
                                    <tr class="table-warning small">
                                    @else
                                    <tr class="small">
                                @endif
                                <td>
                                    @if (property_exists($invoice['documento'], 'apendice'))
                                        @if ($invoice['documento']->apendice)
                                            @foreach ($invoice['documento']->apendice as $apendice)
                                                @if ($apendice->etiqueta == 'Transaccion')
                                                    <strong>Transacción: </strong>{{ $apendice->valor }}
                                                @endif
                                                @if ($apendice->etiqueta == 'Tienda')
                                                    <strong>Tienda: </strong>{{ $apendice->valor }}
                                                @endif
                                                @if ($apendice->etiqueta == 'Terminal')
                                                    <strong>Terminal: </strong>{{ $apendice->valor }}
                                                @endif
                                            @endforeach
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $tiposDte[$invoice['tipo_dte']] }}</td>
                                <td class="small">
                                    <p>
                                        <strong>Código Generacion:</strong><br>{{ $invoice['cod_generacion'] }}<br>
                                        <strong>Número de
                                            Control:</strong><br>{{ $invoice['numero_control'] }}<br>
                                        @if ($invoice['sello_recibido'])
                                            <strong>Sello de Recibido:</strong><br>{{ $invoice['sello_recibido'] }}
                                        @endif
                                    </p>
                                </td>
                                <td>
                                    @php
                                        $nombre = '';
                                        $documento = '';

                                        if ($invoice['tipo_dte'] == '14') {
                                            $receptor = $invoice['documento']->sujetoExcluido;
                                        } else {
                                            if (property_exists($invoice['documento'], 'receptor')) {
                                                $receptor = $invoice['documento']->receptor;
                                            } else {
                                                $receptor = null;
                                            }
                                        }

                                        if ($receptor) {
                                            if (property_exists($receptor, 'nombre')) {
                                                $nombre = $receptor->nombre;
                                            }
                                            if (in_array($invoice['tipo_dte'], $receptores_nit)) {
                                                if (property_exists($receptor, 'nit')) {
                                                    $documento = $receptor->nit;
                                                }
                                            } else {
                                                $documento = $receptor->numDocumento;
                                            }
                                        }
                                    @endphp
                                    <p>
                                        @if ($nombre)
                                            <strong>Nombre:<br></strong> {{ $nombre }}<br>
                                        @endif
                                        @if ($documento)
                                            <strong>Identificacion:<br></strong> {{ $documento }}
                                        @endif
                                    </p>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($invoice['fh_procesamiento'])->subHours(6)->format('d/m/Y H:i:s') }}
                                </td>
                                <td>
                                    @switch($invoice['tipo_dte'])
                                        @case('01')
                                            <strong>Total:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalPagar, 2, '.', ',') }}
                                        @break

                                        @case('03')
                                            <strong>Neto:
                                            </strong>${{ number_format($invoice['documento']->resumen->subTotalVentas, 2, '.', ',') }}<br>
                                            <strong>IVA:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalPagar - $invoice['documento']->resumen->subTotalVentas, 2, '.', ',') }}<br>
                                            <strong>Total:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalPagar, 2, '.', ',') }}
                                        @break

                                        @case('05')
                                            <strong>Neto:
                                            </strong>${{ number_format($invoice['documento']->resumen->subTotalVentas, 2, '.', ',') }}<br>
                                            <strong>IVA:
                                            </strong>${{ number_format($invoice['documento']->resumen->montoTotalOperacion - $invoice['documento']->resumen->subTotalVentas, 2, '.', ',') }}<br>
                                            <strong>Total:
                                            </strong>${{ number_format($invoice['documento']->resumen->montoTotalOperacion, 2, '.', ',') }}
                                        @break

                                        @case('07')
                                            <strong>Sujeto a Retención:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalSujetoRetencion, 2, '.', ',') }}<br>
                                            <strong>IVA Retenido:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalIVAretenido, 2, '.', ',') }}
                                        @break

                                        @case('11')
                                            <strong>Total:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalPagar, 2, '.', ',') }}
                                        @break

                                        @case('14')
                                            <strong>Total:
                                            </strong>${{ number_format($invoice['documento']->resumen->totalCompra, 2, '.', ',') }}
                                        @break

                                        @default
                                    @endswitch
                                </td>
                                <td>{{ $invoice['estado'] }}</td>
                                <td class="small">
                                    @if ($invoice['observaciones'] != '[]')
                                        {{ $invoice['observaciones'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($invoice['estado'] === 'CONTINGENCIA' || $invoice['estado'] === 'RECHAZADO')
                                    @else
                                        <div class="d-inline-flex">
                                            <a href="{{ $invoice['enlace_pdf'] }}"
                                                class="btn btn-sm btn-danger ms-1 d-flex align-items-center"
                                                target="_blank">PDF</a>
                                            <a href="{{ $invoice['enlace_json'] }}"
                                                class="btn btn-sm btn-success ms-1 d-flex align-items-center"
                                                target="_blank">JSON</a>
                                            <a href="{{ $invoice['enlace_ticket'] }}"
                                                class="btn btn-sm btn-warning ms-1 d-flex align-items-center"
                                                target="_blank">Tiquete</a>
                                            <button type="button"
                                                class="btn btn-primary btn-sm ms-1 btn-modal d-flex align-items-center"
                                                data-bs-toggle="modal" data-bs-target="#mailModal"
                                                data-id="{{ $invoice['cod_generacion'] }}">
                                                Reenviar Correo
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="align-middle text-center">
                                <th style="width: 5%;">Transacción</th>
                                <th style="width: 10%;">Tipo de Documento</th>
                                <th style="width: 15%;">Información Hacienda</th>
                                <th style="width: 15%;">Receptor</th>
                                <th style="width: 10%;">Fecha Procesamiento</th>
                                <th style="width: 5%">Totales</th>
                                <th style="width: 10%;">Estado</th>
                                <th style="width: 15%;">Observaciones</th>
                                <th style="width: 15%;">Acciones</th>
                            </tr>
                        </tfoot>
                    </table> --}}
                </div>
                <div class="row my-4">
                    <div class="col-lg-12 mb-5">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h2>Estadísticas</h2>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="card mb-2">
                                    <div class="card-body bg-light btn shadow">
                                        <div class="row py-2 justify-content-center">
                                            <div class="col-3">
                                                <br>
                                                <i class="fas fa-file text-info fa-4x fa-4x"></i>
                                            </div>
                                            <div class="col-9">
                                                <h2 class="card-title" id="statisticsTotal">{{ $statistics['total'] }}</h2>
                                                <p class="card-text h6">Documentos<br>Generados</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="card mb-2">
                                    <div class="card-body bg-light btn shadow">
                                        <div class="row py-2 justify-content-center">
                                            <div class="col-3">
                                                <br>
                                                <i class="fas fa-file-circle-minus text-secondary fa-4x fa-4x"></i>
                                            </div>
                                            <div class="col-9">
                                                <h2 class="card-title" id="statisticsAnulado">{{ $statistics['anulado'] }}</h2>
                                                <p class="card-text h6">Documentos<br>Anulados</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="card mb-2">
                                    <div class="card-body bg-light btn shadow">
                                        <div class="row py-2 justify-content-center">
                                            <div class="col-3">
                                                <br>
                                                <i class="fas fa-file-circle-check text-success fa-4x fa-4x"></i>
                                            </div>
                                            <div class="col-9">
                                                <h2 class="card-title" id="statisticsApproved">{{ $statistics['approved'] }}</h2>
                                                <p class="card-text h6">Documentos<br>Enviados</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="card mb-2">
                                    <div class="card-body bg-light btn shadow">
                                        <div class="row py-2 justify-content-center">
                                            <div class="col-3">
                                                <br>
                                                <i class="fas fa-file-circle-xmark text-danger fa-4x fa-4x"></i>
                                            </div>
                                            <div class="col-9">
                                                <h2 class="card-title" id="statisticsRejected">{{ $statistics['rejected'] }}</h2>
                                                <p class="card-text h6">Documentos<br>Rechazados</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="card mb-2">
                                    <div class="card-body bg-light btn shadow">
                                        <div class="row py-2 justify-content-center">
                                            <div class="col-3">
                                                <br>
                                                <i class="fas fa-file-circle-exclamation text-warning fa-4x fa-4x"></i>
                                            </div>
                                            <div class="col-9">
                                                <h2 class="card-title" id="statisticsContingencia">{{ $statistics['contingencia'] }}
                                                </h2>
                                                <p class="card-text h6">Documentos<br>en Contingencia</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="mailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Reenviar Correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('invoices.send') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="mail">Dirección de Correo:</label>
                            <input type="email" class="form-control" id="mail" name="correo"
                                aria-describedby="emailHelp">
                            <small id="emailHelp" class="form-text text-muted">Correo electrónico del destinatario de este
                                DTE</small>
                            <input type="hidden" name="cod_generacion" value="" id="cod_generacion">
                        </div>
                        <div class="form-group mt-2">
                            <input type="submit" value="Enviar Correo" class="btn btn-success">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.dtes = {{ Js::from($invoices) }};
        window.tiposDte = {{ Js::from($tiposDte) }};
        window.receptores_nit = {{ Js::from($receptores_nit) }};
        window.receptores_num = {{ Js::from($receptores_num) }};
        window.tienda = "{{ $tienda }}";
    </script>
    @vite('resources/js/invoices.js')
@endsection

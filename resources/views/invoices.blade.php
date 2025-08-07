@extends('layouts.app')

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
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        @endif
        @livewire('business.tables.dtes-filtrados-table')
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
    @vite('resources/js/invoices.js')
@endsection

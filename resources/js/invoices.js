$(function(){
    let dtes = window.dtes;
    let tiposDte = window.tiposDte;
    let receptores_nit = window.receptores_nit;
    let tiendas = new Set();
    let tienda_seleccionada = window.tienda;

    $('#invoicesTable tfoot th').each(function () {
        //Apply the search except to the last column
        if ($(this).index() < $('#invoicesTable tfoot th').length - 1) {
            var title = $(this).text();
            $(this).html('<input class="form-control form-control-sm" type="text" placeholder="Buscar ' + title + '" />');
        }else{
            $(this).html('');
        }
    });

    function prepare_data(data){
        let prepared_data = [];
        data.forEach(element => {
            let prepared_element = {
                fh_procesamiento: new Date(element.fh_procesamiento).toLocaleString(),
                tipo_dte: tiposDte[element.tipo_dte],
                enlace_pdf: element.enlace_pdf,
                enlace_json: element.enlace_json,
                enlace_ticket: element.enlace_ticket,
                cod_generacion: element.cod_generacion,
                numero_control: element.numero_control,
                sello_recibido: element.sello_recibido,
                estado: element.estado,
                observaciones: element.observaciones
            }

            if(element.documento.apendice != null){
                const tiendaItem = element.documento.apendice.find(item => item.campo === "Tienda");
                prepared_element.tienda = tiendaItem ? tiendaItem.valor : "";
                tiendas.add(prepared_element.tienda);
                const transaccionItem = element.documento.apendice.find(item => item.campo === "Transaccion");
                prepared_element.transaccion = transaccionItem ? transaccionItem.valor : "";
            } else {
                prepared_element.tienda = "";
                prepared_element.transaccion = "";
            }


            let nombre = '';
            let documento = ''
            let receptor = null;
            if(element.tipo_dte == "14"){
                receptor = element.documento.sujetoExcluido
            } else {
                receptor = element.documento.receptor
            }

            if(receptor != null){
                nombre = receptor.nombre;
                if(element.tipo_dte in receptores_nit){
                    documento = receptor.nit;
                } else {
                    documento = receptor.numDocumento;
                }
            }
            documento = documento ? documento : '';
            prepared_element.receptor = nombre + '<br>' + documento;

            switch(element.tipo_dte){
                case "01":
                    prepared_element.neto = "$"+(element.documento.resumen.totalPagar - (element.documento.resumen.totalPagar * 0.13)).toFixed(2);
                    prepared_element.iva = "$"+(element.documento.resumen.totalPagar * 0.13).toFixed(2);
                    prepared_element.total = "$"+(element.documento.resumen.totalPagar).toFixed(2);
                    break;
                case "03":
                    prepared_element.neto = "$"+(element.documento.resumen.subTotalVentas).toFixed(2);
                    prepared_element.iva = "$"+(element.documento.resumen.subTotalVentas * 0.13).toFixed(2);
                    prepared_element.total = "$"+(element.documento.resumen.totalPagar).toFixed(2);
                    break;
                case "05":
                    prepared_element.neto = "$"+(element.documento.resumen.subTotalVentas).toFixed(2);
                    prepared_element.iva = "$"+(element.documento.resumen.subTotalVentas * 0.13).toFixed(2);
                    prepared_element.total = "$"+(element.documento.resumen.montoTotalOperacion).toFixed(2);
                    break;
                case "07":
                    prepared_element.neto = "$"+(element.documento.resumen.totalSujetoRetencion).toFixed(2);
                    prepared_element.iva = "$"+(element.documento.resumen.totalIVAretenido).toFixed(2);
                    prepared_element.total = "$"+(element.documento.resumen.totalSujetoRetencion).toFixed(2);
                    break;
                case "11":
                    prepared_element.neto = "$"+(element.documento.resumen.totalPagar).toFixed(2);
                    prepared_element.iva = "$0.00";
                    prepared_element.total = "$"+(element.documento.resumen.totalPagar).toFixed(2);
                    break;
                case "14":
                    prepared_element.neto = "$"+(element.documento.resumen.totalCompra).toFixed(2);
                    prepared_element.iva = "$0.00";
                    prepared_element.total = "$"+(element.documento.resumen.totalCompra).toFixed(2);
                    break;
                default:
                    prepared_element.neto = "$0.00";
                    prepared_element.iva = "$0.00";
                    prepared_element.total = "$0.00";
                    break;
            }

            prepared_data.push(prepared_element);

        });
        load_tiendas();

        // Apply tienda filter if tienda is selected
        if(tienda_seleccionada != "ALL" && tienda_seleccionada != ""){
            prepared_data = prepared_data.filter(item => item.tienda === tienda_seleccionada);
        }

        // Update statistics
        $("#statisticsTotal").text(prepared_data.length);
        $("#statisticsAnulado").text(prepared_data.filter(item => item.estado === "ANULADO").length);
        $("#statisticsApproved").text(prepared_data.filter(item => item.estado === "PROCESADO").length);
        $("#statisticsRejected").text(prepared_data.filter(item => item.estado === "RECHAZADO").length);
        $("#statisticsContingencia").text(prepared_data.filter(item => item.estado === "CONTINGENCIA").length);

        return prepared_data;
    }

    function load_tiendas(){
        tiendas.forEach(tienda => {
            $('#tienda').append(`<option value="${tienda}" ${tienda === tienda_seleccionada ? 'selected' : ''}>${tienda}</option>`);
        });
    }


    var proyectosTable = $('#invoicesTable').DataTable({
        initComplete: function () {
            this.api().columns().every(function () {
                var that = this;
                $('input', this.footer()).on('keyup change clear', function () {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                });
            });
        },
        data: prepare_data(dtes),
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        aoColumns: [
            { data: 'fh_procesamiento'},
            { data: 'tienda' },
            { data: 'transaccion' },
            { data: 'receptor' },
            { data: 'neto' },
            { data: 'iva' },
            { data: 'total' },
            { data: 'tipo_dte' },
            { data: 'estado' },
            { 
                data: 'observaciones',
                render: function(data){
                    if(data != "[]"){
                        return '<p class="text-break">'+data+'</p>';
                    } else {
                        return "";
                    }
                }
            },
            { data: 'cod_generacion' },
            { data: 'numero_control' },
            { 
                data: 'sello_recibido',
                render: function(data){
                    return '<p class="text-break">'+data+'</p>';
                }
            },
            { 
                data: 'estado',
                render: function(data, type, row){
                    if(data != "CONTINGENCIA" && data != "RECHAZADO"){
                        return `<div class="d-inline-flex">
                            <a href="${row.enlace_pdf}"
                                class="btn btn-sm btn-danger ms-1 d-flex align-items-center"
                                target="_blank">PDF</a>
                            <a href="${row.enlace_json}"
                                class="btn btn-sm btn-success ms-1 d-flex align-items-center"
                                target="_blank">JSON</a>
                            <a href="${row.enlace_ticket}"
                                class="btn btn-sm btn-warning ms-1 d-flex align-items-center"
                                target="_blank">Tiquete</a>
                            <button type="button"
                                class="btn btn-primary btn-sm ms-1 btn-modal d-flex align-items-center"
                                data-bs-toggle="modal" data-bs-target="#mailModal"
                                data-id="${row.cod_generacion}">
                                Reenviar Correo
                            </button>
                        </div>`;
                    } else {
                        return "";
                    }
                }
            },
        ],
        createdRow: function(row, data, dataIndex){
            if(data.estado == "CONTINGENCIA" || data.estado == "RECHAZADO"){
                $(row).addClass('table-danger');
            }
        },
        dom: '<"container-fluid"<"row"<"col"l><"col"B><"col"f>>>rtip',
        buttons: [
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                title: 'Reporte de DTEs',
                className: 'btn btn-primary',
                orientation: 'landscape',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,9,10,11,12],
                    
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                title: 'Reporte de DTEs',
                className: 'btn btn-success',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,9,10,11,12],
                }
            },
            {
                extend: 'pdf',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                title: 'Reporte de DTEs',
                className: 'btn btn-danger',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,9,10,11,12],
                }
            }
        ],
        "order": [[ 0, "desc" ]],
    });

    $(".btn-modal").on("click", function (e) {
        var button = $(e.target);
        var codGeneracion = button.data('id');
        console.log(codGeneracion);
        $('#cod_generacion').val(codGeneracion);
    });

})

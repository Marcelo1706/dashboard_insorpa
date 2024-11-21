$(function(){

    var fechaActual = new Date();
    var datetime = "Creado el: " + fechaActual.getDate() + "/"
        + (fechaActual.getMonth()+1)  + "/"
        + fechaActual.getFullYear() + " a las " 
        + fechaActual.getHours() + ":" 
        + fechaActual.getMinutes() + ":"
        + fechaActual.getSeconds();

    $('#invoicesTable tfoot th').each(function () {
        //Apply the search except to the last column
        if ($(this).index() < $('#invoicesTable tfoot th').length - 1) {
            var title = $(this).text();
            $(this).html('<input class="form-control form-control-sm" type="text" placeholder="Buscar ' + title + '" />');
        }else{
            $(this).html('');
        }
    });

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
        dom: '<"container-fluid"<"row"<"col"l><"col"B><"col"f>>>rtip',
        buttons: [
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                title: 'Reporte de DTEs',
                className: 'btn btn-primary',
                orientation: 'landscape',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7],
                    
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                title: 'Reporte de DTEs',
                className: 'btn btn-success',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7],
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
                    columns: [0,1,2,3,4,5,6,7]
                }
            }
        ],
        "order": [[ 0, "desc" ]],
    });
})

$(".btn-modal").on("click", function (e) {
    var button = $(e.target);
    var codGeneracion = button.data('id');
    console.log(codGeneracion);
    $('#cod_generacion').val(codGeneracion);
});
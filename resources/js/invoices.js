$(function(){
    $(".btn-modal").on("click", function (e) {
        var button = $(e.target);
        var codGeneracion = button.data('id');
        console.log(codGeneracion);
        $('#cod_generacion').val(codGeneracion);
    });

})

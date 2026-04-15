window.helper = {
    post: function (url, formData) {
        return new Promise(function (resolve, reject) {
            fetch(url, {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                if (rsp.status !== undefined) {
                    const status = [ 401, 404 ];
                    if (status.includes(rsp.status)) {
                        sweet2.loading({text:rsp.message});
                        setTimeout(() => {
                            window.location.reload();  
                        }, 3000);
                        return;
                    }
                }
                resolve(rsp);
            })
            .catch(function (err) {
                reject(err);
            });
        });
    },
    get: (url) => {
        return new Promise(function (resolve, reject) {
            fetch(url, {method: "GET"})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                resolve(rsp);
            })
            .catch(function (err) {
                reject(err);
            });
        });
    },
    datatable: {
        language: {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
               "sFirst": "Primero",
               "sLast": "Último",
               "sNext": "Siguiente",
               "sPrevious": "Anterior"
            },
            "oAria": {
               "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
               "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    },
};
window.sweet2 = {
    str_loading: "Cargando...",
    error: function (o) {
        (o.type = "error"), (o.timer = 2e3), (o.showConfirmButton = !1), this.show(o);
    },
    success: function (o) {
        ((o = "object" !== typeof(o) ? {} : o).type = "success"), (o.timer = 2e3), (o.showConfirmButton = !1), this.show(o);
    },
    question: function (o) {
        ((o = "object" !== typeof(o) ? {} : o).type = "question"), (o.showCancelButton = !0), this.show(o);
    },
    loading: function (o) {
        var t = !0;
        !1 === o ? window.sweet2.hide() : !0 === t && ((t = !1), ((o = "object" !== typeof(o) ? {} : o).text = "string" == typeof o.text ? o.text : sweet2.str_loading), (o.loading = !0), (o.showConfirmButton = !1), window.sweet2.show(o));
    },
    show: function (o) {
        "object" === typeof(o) &&
            Swal.fire({
                icon: o.type,
                title: o.title,
                html: o.html,
                text: o.text,
                timer: o.timer,
                showConfirmButton: "boolean" != typeof o.showConfirmButton || o.showConfirmButton,
                showCancelButton: "boolean" == typeof o.showCancelButton && o.showCancelButton,
                confirmButtonText: o.confirmButtonText ? o.confirmButtonText : "Aceptar",
                cancelButtonText: o.cancelButtonText ? o.cancelButtonText : "Cancelar",
                showCloseButton: "boolean" == typeof o.showCloseButton && o.showCloseButton,
                allowOutsideClick: !1,
                didOpen: function (t) {
                    o.loading && Swal.showLoading(), "function" == typeof o.onOpen && o.onOpen.call(null, t);
                },
                didClose: function () {
                    "function" == typeof o.onClose && o.onClose.call();
                },
            }).then(function (t) {
                t.value && "function" == typeof o.onOk && o.onOk.call(), "function" == typeof o.onCancel && "cancel" == t.dismiss && o.onCancel.call();
            });
    },
    hide: function () {
        Swal.close();
    },
};
$(function () {
    calcTotal()

    $('body').on('blur', '.qtd', function () {
        let qtd = $(this).val();
        $value_unit = $(this).closest('td').prev().find('input');
        $sub = $(this).closest('td').next().find('input');
        let vl = convertMoedaToFloat($value_unit.val())
        qtd = convertMoedaToFloat(qtd)
        console.log(qtd)
        $sub.val(convertFloatToMoeda(qtd * vl))
        calcTotal()
    })
})

$("body").on("blur", ".subtotal-item" , function () {
    calcTotal()
})

// $("body").on("blur", ".valor_unit" , function () {
//     calcTotal()
// })

$(function () {
    $('.btn-action').attr('disabled', 'disabled')
    // $('.checkbox').each(function(i, e){
    //  e.checked = false
    // })

    validLineSelect()
})
$('.checkbox').click(function () {
    $value = $(this).val()
    console.log($value)
    $('.checkbox').each(function (i, e) {
        if (e.value != $value) {
            e.checked = false
        }

        validLineSelect()
    })
})



function validLineSelect() {
    $('.btn-action').attr('disabled', 'disabled')

    $('.checkbox').each(function (i, e) {
        if ($(this).is(':checked')) {
            let status = $(this).data('status')
            if (status == 'novo' || status == 'rejeitado') {
                $('#btn-enviar').removeAttr('disabled')
                $('#btn-danfe-temp').removeAttr('disabled')
                $('#btn-xml-temp').removeAttr('disabled')
            } else if (status == 'aprovado') {
                $('#btn-imprimir').removeAttr('disabled')
                $('#btn-imprimir-cce').removeAttr('disabled')
                $('#btn-consultar').removeAttr('disabled')
                $('#btn-cancelar').removeAttr('disabled')
                $('#btn-corrigir').removeAttr('disabled')
                $('#btn-baixar-xml').removeAttr('disabled')
            } else if (status == 'cancelado') {
                $('#btn-imprimir-cancela').removeAttr('disabled')
            }
        }
    })
}

var total_itens = 0
function calcTotal() {
    var total = 0
    $(".subtotal-item").each(function () {
        total += convertMoedaToFloat($(this).val())
    })
    setTimeout(() => {
        total_itens = total
        $('#soma-itens').html("R$ " + convertFloatToMoeda(total_itens))
        $('#valor_devolucao').val(total.toFixed(2))
    }, 100)
    console.log(total)
}

$(".table-itens").on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    swal("Sucesso", "Item removido!", "success")
    calcTotal()
});


function getChecked(call) {
    let id = null
    $('.checkbox').each(function (i, e) {
        if (e.checked) {
            id = e.value
        }
    })
    call(id)
}

$('#btn-imprimir').click(function () {
    getChecked((id) => {
        window.open(path_url + 'devolucao/imprimir/' + id, "_blank")
    })
})

$('#btn-imprimir-cce').click(function () {
    getChecked((id) => {
        window.open(path_url + 'devolucao/imprimir-cce/' + id, "_blank")
    })
})

$('#btn-imprimir-cancela').click(function () {
    getChecked((id) => {
        window.open(path_url + 'devolucao/imprimir-cancela/' + id, "_blank")
    })
})

function getCheckedElement(call) {
    $el = null
    $('.checkbox').each(function (i, e) {
        if (e.checked) {
            $el = $(this)
        }
    })
    call($el)
}

$('#btn-xml-temp').click(function () {
    getChecked((id) => {
        window.open(path_url + 'devolucao/xml-temp/' + id, "_blank")
    })
})

$('#btn-danfe-temp').click(function () {
    getChecked((id) => {
        window.open(path_url + 'devolucao/danfe-temp/' + id, "_blank")

    })
})

$('#btn-baixar-xml').click(function () {
    getChecked((id) => {
        window.open(path_url + 'nfe/baixar-xml/' + id, "_blank")
    })
})

$('#btn-enviar').click(() => {
    console.clear()
    getChecked((id) => {
        $("#btn-consulta-cnpj span").removeClass("d-none");
        let empresa_id = $("#empresa_id").val();

        $.post(path_url + "api/devolucao/transmitir", {
            id: id,
            empresa_id: empresa_id,
        })
        .done((success) => {
            console.log(success)
                // let infProt = err.responseJSON.protNFe.infProt
                swal("Sucesso", "NFe emitida " + success, "success")
                .then(() => {
                    window.open(path_url + 'devolucao/imprimir/' + id, "_blank")
                    setTimeout(() => {
                        location.reload()
                    }, 100)
                })
            })
        .fail((err) => {
            if (err.status == 403) {
                console.log(err)
                console.log(err.responseJSON.protNFe.infProt)
                let infProt = err.responseJSON.protNFe.infProt
                swal("Algo deu errado", infProt.cStat + " - " + infProt.xMotivo, "error")
            } else {
                swal("Algo deu errado", err.responseJSON, "error")
            }
        })
    })
})

$('#btn-consultar').click(() => {
    console.clear()
    getChecked((id) => {
        if (id) {
            let empresa_id = $("#empresa_id").val();

            $.post(path_url + "api/devolucao/consultar", {
                id: id,
                empresa_id: empresa_id,
            })
            .done((success) => {
                console.log(success)
                let infProt = success.protNFe.infProt
                swal("Sucesso", "[" + infProt.chNFe + "] " + infProt.xMotivo, "success")

            })
            .fail((err) => {
                console.log(err)
                swal("Algo deu errado", err.responseJSON, "error")

            })
        } else {
            swal("Alerta", "Selecione uma devoluçao!", "warning")
        }
    })
})

$('#btn-corrigir').click(() => {
    console.clear()
    getCheckedElement((el) => {
        console.log(el)
        let numero_nfe = el.data('numero_nfe')
        if (numero_nfe > 0) {
            $('.numero_nfe').text(numero_nfe)
            $('#modal-corrigir').modal('show')
        }
    })
})

$('#btn-corrige-send').click(() => {
    getChecked((id) => {
        if (id) {
            let empresa_id = $("#empresa_id").val();
            let motivo = $('#inp-motivo-corrige').val()
            if (motivo.length >= 15) {
                $.post(path_url + "api/devolucao/corrigir", {
                    id: id,
                    empresa_id: empresa_id,
                    motivo: motivo
                })
                .done((success) => {
                    console.log(success)
                    let infEvento = success.retEvento.infEvento
                    swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
                    .then(() => {
                        window.open(path_url + 'devolucao/imprimir-cce/' + id, "_blank")
                        $('#modal-corrigir').modal('hide')
                    })

                })
                .fail((err) => {
                    console.log(err)
                    try {
                        swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
                    } catch {
                        swal("Algo deu errado", err.responseJSON, "error")
                    }
                })
            } else {
                swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
            }
        } else {
            swal("Alerta", "Selecione uma devoluçao!", "warning")
        }
    })
})

$('#btn-cancelar').click(() => {
    console.clear()
    getCheckedElement((el) => {
        console.log(el)
        let numero_nfe = el.data('numero_nfe')
        if (numero_nfe > 0) {
            $('.numero_nfe').text(numero_nfe)
            $('#modal-cancelar').modal('show')
        }
    })
})


$('#btn-cancelar-send').click(() => {
    getChecked((id) => {
        if (id) {
            let empresa_id = $("#empresa_id").val();
            let motivo = $('#inp-motivo-cancela').val()
            if (motivo.length >= 15) {
                $.post(path_url + "api/devolucao/cancelar", {
                    id: id,
                    empresa_id: empresa_id,
                    motivo: motivo
                })
                .done((success) => {
                    console.log(success)
                    let infEvento = success.retEvento.infEvento
                    swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
                    .then(() => {
                        window.open(path_url + 'devolucao/imprimir-cancela/' + id, "_blank")
                        setTimeout(() => {
                            location.reload()
                        }, 100)
                    })

                })
                .fail((err) => {
                    console.log(err)
                    try {
                        swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
                    } catch {
                        swal("Algo deu errado", err.responseJSON, "error")
                    }
                })
            } else {
                swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
            }
        } else {
            swal("Alerta", "Selecione uma devoluçao!", "warning")
        }
    })
})




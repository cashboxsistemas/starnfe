$('.modal .select2').each(function () {
    console.log($(this))
    let id = $(this).prop('id')
    if (id == 'inp-uf') {
        $(this).select2({
            dropdownParent: $(this).parent(),
            theme: 'bootstrap4',
        });
    }
    if (id == 'inp-cidade_id' || id == 'inp-cidade_cobranca_id') {
        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar a cidade",
            width: "100%",
            theme: 'bootstrap4',
            dropdownParent: $(this).parent(),
            ajax: {
                cache: true,
                url: path_url + 'api/buscaCidades',
                dataType: "json",
                data: function (params) {
                    console.clear()
                    var query = {
                        pesquisa: params.term,
                    };
                    return query;
                },
                processResults: function (response) {
                    console.log("response", response)
                    var results = [];
                    $.each(response, function (i, v) {
                        var o = {};
                        o.id = v.id;
                        o.text = v.nome + "(" + v.uf + ")";
                        o.value = v.id;
                        results.push(o);
                    });
                    return {
                        results: results
                    };
                }
            }
        });
    }
})


$(function () {
    setTimeout(() => {
        $("#inp-servico_id").change(() => {
            let servico_id = $("#inp-servico_id").val()
            if (servico_id) {
                $.get(path_url + "api/ordemServico/find/" + servico_id)
                .done((e) => {
                    $('#inp-quantidade').val('1,00')
                    $('#inp-nome').val(e.nome)
                    $('#inp-valor').val(convertFloatToMoeda(e.valor))
                })
                .fail((e) => {
                    console.log(e)
                })
            }
        })
    }, 100)
})

$(function () {
    setTimeout(() => {
        $("#inp-funcionario_id").change(() => {
            let funcionario_id = $("#inp-funcionario_id").val()
            if (funcionario_id) {
                $.get(path_url + "api/ordemServico/findFuncionario/" + funcionario_id)
                .done((e) => {
                    $('#inp-nome').val(e.nome)
                    $('#inp-celular').val(e.celular)
                })
                .fail((e) => {
                    console.log(e)
                })
            }
        })
    }, 100)
})

$("#inp-produto_id").change(() => {
    let product_id = $("#inp-produto_id").val()
    if (product_id) {
        $.get(path_url + "api/produtos/find/" + product_id)
        .done((e) => {
            $('.qtd_produto').val('1,00')
            $('.valor_produto').val(convertFloatToMoeda(e.valor_venda))
        })
        .fail((e) => {
            console.log(e)
        })
    }

})

$('.btn-add-funcionario').click(() => {
    let celular = $("#inp-celular").val();
    let funcionario_id = $("#inp-funcionario_id").val()
    let funcao = $("#inp-funcao").val()

    console.log(celular)
    console.log(funcionario_id)
    console.log(funcao)

    if (celular && funcao && funcionario_id) {
        let dataRequest = {
            celular: celular,
            funcionario_id: funcionario_id,
            funcao: funcao,
        }
        $.get(path_url + "api/ordemServico/linhaFuncionario", dataRequest)
        .done((e) => {
            $('.table-funcionario tbody').append(e)
                // calcTotal()
            })
        .fail((e) => {
            console.log(e)
        })
    } else {
        swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
    }
})


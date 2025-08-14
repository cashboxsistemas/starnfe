<div class="row g-3">
    <div class="col-md-2">
        {!!Form::text('referencia', 'Referência')->required()
        !!}
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-cliente_id" class="required">Cliente</label>
            <div class="input-group">
                <select class="form-control" name="cliente_id" id="inp-cliente_id">
                    @isset($item)
                    <option value="{{$item->cliente_id}}">{{ $item->cliente->razao_social }}</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        {!!Form::select('categoria_id', 'Categoria', $categorias->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_integral', 'Valor')->required()
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_integral) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_vencimento', 'Vencimento')->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo_pagamento', 'Tipo de pagamento', App\Models\ContaReceber::tiposPagamento())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Conta recebida', ['0' => 'Não', '1' => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    @isset($item)
    {!! __view_locais_select_edit("Local", $item->filial_id) !!}
    @else
    {!! __view_locais_select() !!}
    @endif

    <hr>

    @if(!isset($item))
    <p class="text-danger">
        *Campo abaixo deve ser preenchido se ouver recorrência para este registro
    </p>

    <div class="col-md-2">
        {!!Form::tel('recorrencia', 'Data')
        ->attrs(['data-mask' => '00/00'])
        ->placeholder('mm/aa')
        !!}
    </div>
    @endif

    <div class="row tbl-recorrencia d-none mt-2">
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5 float-end">Salvar</button>
    </div>
</div>

@section('js')

<script type="text/javascript" src="/js/client.js"></script>


<script type="text/javascript">
    $('.modal .select2').each(function() {
        console.log($(this))
        let id = $(this).prop('id')

        if (id == 'inp-uf') {
            $(this).select2({
                dropdownParent: $(this).parent()
                , theme: 'bootstrap4'
            , });
        }

        if (id == 'inp-cidade_id' || id == 'inp-cidade_cobranca_id') {

            $(this).select2({

                minimumInputLength: 2
                , language: "pt-BR"
                , placeholder: "Digite para buscar a cidade"
                , width: "100%"
                , theme: 'bootstrap4'
                , dropdownParent: $(this).parent()
                , ajax: {
                    cache: true
                    , url: path_url + 'api/buscaCidades'
                    , dataType: "json"
                    , data: function(params) {
                        console.clear()
                        var query = {
                            pesquisa: params.term
                        , };
                        return query;
                    }
                    , processResults: function(response) {
                        console.log("response", response)
                        var results = [];

                        $.each(response, function(i, v) {
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

    $('#btn-store-cliente').click(() => {
        let valid = validaCamposModal()
        if (valid.length > 0) {
            let msg = ""
            valid.map((x) => {
                msg += x + "\n"
            })
            swal("Ops, erro no formulário", msg, "error")
        } else {
            console.log("salvando...")

            let data = {}
            $(".modal input, .modal select").each(function() {

                let indice = $(this).attr('id')
                indice = indice.substring(4, indice.length)
                data[indice] = $(this).val()
            });
            data['empresa_id'] = $('#empresa_id').val()

            console.log(data)
            $.post(path_url + 'api/cliente/store', data)
                .done((success) => {
                    console.log("success", success)
                    swal("Sucesso", "Cliente cadastrado!", "success")
                        .then(() => {
                            var newOption = new Option(success.razao_social, success.id, false, false);
                            $('#inp-cliente_id').append(newOption).trigger('change');
                            $('#modal-cliente').modal('hide')
                        })

                }).fail((err) => {
                    console.log(err)
                    swal("Ops", "Algo deu errado ao salvar cliente!", "error")
                })
        }
    })
    $('#inp-recorrencia').blur(() => {

        let data = $('#inp-recorrencia').val()
        if (data.length == 5) {
            let vencimento = $('#inp-data_vencimento').val()
            let valor = $('#inp-valor_integral').val()
            if (valor && vencimento) {
                let item = {
                    data: data
                    , vencimento: vencimento
                    , valor: valor
                }
                $.get(path_url + 'api/conta-receber/recorrencia', item)
                    .done((html) => {
                        console.log("success", html)
                        $('.tbl-recorrencia').html(html)
                        $('.tbl-recorrencia').removeClass('d-none')

                    }).fail((err) => {
                        console.log(err)

                    })
            } else {
                swal("Algo saiu errado", "Informe o valor e vencimento data conta base!", "warning")
            }
        } else {
            swal("Algo saiu errado", "Informe uma data válida mm/aa exemplo 12/25", "warning")
        }
    })

</script>
@endsection

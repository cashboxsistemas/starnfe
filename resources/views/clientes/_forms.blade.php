<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('cpf_cnpj', 'CPF/CNPJ')->required()->attrs(['class' => 'cpf_cnpj']) !!}
    </div>

    <div class="col-md-1 col-6"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta-cnpj">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i
            class="bx bx-search"></i>
        </button>
    </div>

    <div class="col-12"></div>

    <div class="col-md-6">
        {!! Form::text('razao_social', 'Razão social')->required()->attrs(['class' => '']) !!}
    </div>

    <div class="col-md-6">
        {!! Form::text('nome_fantasia', 'Nome fantasia')->attrs(['class' => ''])->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('ie_rg', 'IE')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('consumidor_final', 'Consumidor final', [0 => 'Não', 1 => 'Sim'])->attrs([
        'class' => 'form-select ignore',
        ]) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])->attrs([
        'class' => 'form-select ignore',
        ]) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('limite_venda', 'Limite venda')
        ->attrs(['class' => 'moeda ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::date('data_aniversario', 'Data de aniversário')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('email', 'Email')->attrs(['class' => 'email'])->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('celular', 'Celular')->attrs(['class' => 'fone'])->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone ignore']) !!}
    </div>

    <hr>

    <h5>Endereço de faturamento</h5>

    <div class="col-md-2">
        {!! Form::tel('cep', 'CEP')->attrs(['class' => 'cep'])->required() !!}
    </div>

    <div class="col-md-6">
        {!! Form::text('rua', 'Rua')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('numero', 'Número')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>

    <div class="col-md-4">
        {!! Form::text('complemento', 'Complemento')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-4">
        {!! Form::select('cidade_id', 'Cidade')->required()->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])
        ->attrs(['class' => 'select2']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('cod_pais', 'Pais', $paises->pluck('nome', 'codigo')->all())->attrs(['class' => 'select2'])->value(isset($item) ? $item->cod_pais : '1058') !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('id_estrangeiro', 'ID estrangeiro (opcional)')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('grupo_id', 'Grupo (opcional)', [null => 'Selecione'] + $grupos->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select ignore']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('acessor_id', 'Assessor (opcional)',[null => 'Selecione'] + $acessores->pluck('nome', 'id')->all())->attrs([
        'class' => 'form-select ignore',
        ]) !!}
    </div>

    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="image-upload" id="image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->imagem)
            <img src="/uploads/clients/{{ $item->imagem }}" class="img-default">
            @else
            <img src="/imgs/no_client.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_client.png" class="img-default">
            @endif
        </div>
        @endif
    </div>

    <div class="clearfix"></div>

    <div class="col-md-2">
        {!! Form::select('info_contador', 'Dados do contador', ['0' => 'Não', '1' => 'Sim'])->attrs([
        'class' => 'form-select',
        ]) !!}
    </div>

    <div class="col-md-3 d-none div-contador">
        {!! Form::text('contador_nome', 'Nome')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-3 d-none div-contador">
        {!! Form::tel('contador_telefone', 'Telefone')->attrs(['class' => 'fone ignore']) !!}
    </div>

    <div class="col-md-3 d-none div-contador">
        {!! Form::text('contador_email', 'Email')->type('email')
        ->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="clearfix"></div>

    <div class="col-md-6">
        {!! Form::tel('observacao', 'Observação')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select(
        'acessor_id',
        'Vendedor/Funcionário (opcional)', [null => 'Selecione'] +
        $funcionarios->pluck('nome', 'id')->all(),
        )->attrs(['class' => 'select2 ignore']) !!}
    </div>

    <hr>
    @if (!isset($not_submit))
    <h5>Endereço de cobrança (opcional)</h5>

    <div class="col-md-6">
        {!! Form::text('rua_cobranca', 'Rua')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('numero_cobranca', 'Número')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('bairro_cobranca', 'Bairro')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('cep_cobranca', 'CEP')->attrs(['class' => 'ignore']) !!}
    </div>

    <div class="col-md-4">
        {!! Form::select('cidade_cobranca_id', 'Cidade', [])->attrs(['class' => 'select2 ignore']) !!}
    </div>
    <hr>
    @endif
    <div class="col-12">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="btn-store-cliente">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/client.js"></script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
<script type="text/javascript">

    $(document).on("blur", "#inp-cep", function () {

        let cep = $(this).val().replace(/[^0-9]/g,'')

        $url = "https://viacep.com.br/ws/"+cep+"/json";
        $.get($url)
        .done((success) => {
            console.log(success)
            $('#inp-rua').val(success.logradouro)
            $('#inp-numero').val(success.numero)
            $('#inp-bairro').val(success.bairro)

            findCidade(success.ibge)
        })
        .fail((err) => {
            console.log(err)
        })

    });

    function findCidade(codigo_ibge) {

        $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
        .done((res) => {

            var newOption = new Option(
                res.nome + " (" + res.uf + ")",
                res.id,
                false,
                false
                );
            $("#inp-cidade_id")
            .html(newOption)
            .trigger("change");
        })
        .fail((err) => {
            console.log(err)
        })
    }

</script>
@endsection

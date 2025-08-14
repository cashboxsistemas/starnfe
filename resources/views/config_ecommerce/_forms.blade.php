<div class="row card-body p-4">

    <div class="col-md-2">
        {!! Form::text('nome', 'Nome de Exibição')->required() !!}
        <p style="color: crimson">Exemplo Loja Slym</p>
    </div>

    <div class="col-md-2">
        {!! Form::text('link', 'Link')->required() !!}
        <p style="color: crimson">Exemplo Slym</p>
    </div>
    <div class="col-md-4">
        {!! Form::text('link_facebook', 'Link Facebook') !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('link_instagram', 'Link Instagram') !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('link_twiter', 'Link Twiter') !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('rua', 'Rua')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('numero', 'Número')->required() !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::select('cidade_id', 'Cidade')->required()->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : []) !!}
    </div>

    <div class="col-md-1 mt-3">
        {!! Form::select('uf', 'UF', App\Models\Veiculo::cUF())->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('cep', 'Cep')->required()->attrs(['class' => 'cep']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('telefone', 'Telefone')->required()->attrs(['class' => 'fone']) !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('email', 'E-mail')->required()->type('email') !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::text('frete_gratis_valor', 'Frete grátis a partir de')->attrs(['class' => 'moeda']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::text('latitude', 'Latitude')->required()->attrs(['class' => 'coordenadas']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::text('longitude', 'Longitude')->required()->attrs(['class' => 'coordenadas']) !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('mercadopago_public_key', 'Mercado pago public key') !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('mercadopago_access_token', 'Mercado pago access token') !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('google_api', 'Google maps API') !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::select('habilitar_retirada', 'Habilitar Retira na Loja', [0 => 'Não', 1 => 'Sim'])->attrs([
        'class' => 'form-select',
        ]) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('desconto_padrao_boleto', '% Desconto padrão boleto') !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('desconto_padrao_pix', '% Desconto padrão PIX') !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('desconto_padrao_cartao', '% Desconto padrão cartão') !!}
    </div>
    <div class="col-md-12 mt-3">
        {!! Form::text('funcionamento', 'Descreva o funcionamento')->required() !!}
    </div>
    <div class="col-md-12 mt-3">
        {!! Form::textarea('politica_privacidade', 'Política de privacidade') !!}
    </div>
    <div class="col-12 mt-4">
        <h6>Imagem</h6>
        @if (!isset($not_submit))
        <div id="image-preview" class="col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            <img src="{{ $item->img }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>
    <div class="col-md-12 mt-3">
        {!! Form::textarea('src_mapa', 'Iframe maps') !!}
        <p style="color: crimson">* Somente o src do iframe</p>
    </div>

    <div class="form-group validated col-sm-4 col-lg-4 col-6 mt-3">
        <label class="text-left">Formas de pagamento</label>
        <select required id="formas_pagamento" name="formas_pagamento[]" required class="form-control multiple-select" multiple>
            @foreach(App\Models\ConfigEcommerce::formasPagamento() as $key => $f)
            <option @isset($item) @if(in_array($key, json_decode($item->formas_pagamento ?? '[]'))) selected @endif @endif value="{{ $key }}">{{ $f }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::select('usar_api', 'Usar ecommerce API', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="mt-2">
        <hr>
    </div>
    <div class="row div-usarApi d-none">
        <label for="" class="mt-3">Api token</label>
        <div class="col-md-4">
            <div class="input-group">
                <input readonly value="{{ isset($item) ? $item->api_token : '' }}" id="api_token" name="api_token" type="text" class="form-control">
                @if (!isset($not_submit))
                <button type="button" class="btn btn-info" id="btn_token"><a class="bx bx-refresh"></a></button>
                @endif
            </div>
        </div>
        <div class="col-md-12 mt-3">
            {!! Form::textarea('mensagem_agradecimento', 'Mensagem de agradecimento') !!}
        </div>
        <div class="col-md-4 mt-3">
            <label for="">Cor do fundo</label>
            <input name="cor_fundo" class="form-control" type="color" value="{{ isset($item) ? $item->cor_fundo : '' }}"/>
        </div>
        <div class="col-md-4 mt-3">
            <label for="">Cor do botão</label>
            <input name="cor_btn" value="{{ isset($item) ? $item->cor_btn : '' }}" class="form-control" type="color" />
        </div>
        <div class="col-md-4 mt-3">
            {!! Form::tel('timer_carrossel', 'Timer Carrossel')->attrs(['class' => 'form-control']) !!}
        </div>

        <div class="col-md-4 mt-3">
            <label>Imagem tela Contato</label>
            @if (!isset($not_submit))
            <div class="_image-preview col-md-4 mt-2">
                <label for="" class="_image-label">Selecione</label>
                <input type="file" name="img_contato_inp" class="_image-upload" accept="image/*" />
                @isset($item)
                <img src="{{$item->contatoUrl}}" class="img-default-contato">
                @else
                <img src="/imgs/no_image.png" class="img-default-contato">
                @endif
            </div>
            @endif
        </div>

        <div class="col-md-4 mt-3">

            <label for="">Favicon</label>
            @if (!isset($not_submit))
            <div class="_image-preview col-md-4 mt-2">
                <label for="image-upload" class="_image-label">Selecione</label>
                <input type="file" name="fav_icon_inp" class="_image-upload" accept="image/*" />
                @isset($item)
                <img src="{{ $item->favUrl }}" class="img-default-contato">
                
                @else
                <img src="/imgs/no_image.png" class="img-default-contato">
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="row col-12 mt-3 div-tema">
        <label for="">Selecione o Tema:</label>
        <div class="row m-4">
            <div id="template1" onclick="selectTemplate(1)"  @if(isset($item) && $item->tema_ecommerce == 'ecommerce') class="col-md-6  img-template-active" @else class="col-md-6 img-template" @endif>
                <img src="/ecommerce/template1.png">
            </div>
            <div id="template2" onclick="selectTemplate(2)" @if(isset($item) && $item->tema_ecommerce == 'ecommerce_one_tech') class="col-md-6  img-template-active" @else class="col-md-6 img-template" @endif>
                <img src="/ecommerce/template2.png">
            </div>
            <input type="hidden" value="{{{ isset($item) ? $item->tema_ecommerce : old('tema_ecommerce') }}}" name="tema_ecommerce" id="tema_ecommerce">
        </div>
    </div>
    <div class="mt-2">
        <hr>
    </div>
    <div class="form-group row cor mt-4" @if(isset($item) && $item->tema_ecommerce == 'ecommerce_one_tech') style="visibility: hidden" @endif>
        <label for="example-color-input" class="col-2 col-form-label">Cor principal</label>
        <div class="col-4">
            <input name="cor_principal" class="form-control" type="color" value="{{{ isset($item) ? $item->cor_principal : old('cor_principal') }}}"/>
        </div>
    </div>
    <div class="col-12 mt-4">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>

@section('js')
<script>
    $(function(){
        setTimeout(() => {
            usarApi()
        }, 100)
    })
    function selectTemplate(id) {
        if (id == 1) {
            $('.cor').css('visibility', 'visible')
            $('#template1').addClass('img-template-active')
            $('#template2').removeClass('img-template-active')
            $('#template2').addClass('img-template')

            $('#tema_ecommerce').val('ecommerce')
        } else if (id == 2) {
            $('.cor').css('visibility', 'hidden')
            $('#template1').removeClass('img-template-active')
            $('#template1').addClass('img-template')

            $('#template2').addClass('img-template-active')
            $('#tema_ecommerce').val('ecommerce_one_tech')
        }
    }

    $('#inp-usar_api').change(() => {
        usarApi()
    })

    function usarApi() {
        let is = $('#inp-usar_api').val()
        if (is == 1) {
            $('.div-usarApi').removeClass('d-none')
            $('.div-tema').addClass('d-none')

        } else {
            $('.div-usarApi').addClass('d-none')
            $('.div-tema').removeClass('d-none')

        }
    }

    $('#btn_token').click(() => {
        let token = generate_token(25);
        swal({
            title: "Atenção",
            text: "Esse token é o responsavel pela comunicação com o ecommerce, tenha atenção!!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                $('#api_token').val(token)
            }
        });
    })

    function generate_token(length) {
        var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
        var b = [];
        for (var i = 0; i < length; i++) {
            var j = (Math.random() * (a.length - 1)).toFixed(0);
            b[i] = a[j];
        }
        return b.join("");
    }
</script>

<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection
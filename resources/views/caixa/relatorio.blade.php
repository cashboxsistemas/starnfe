<!DOCTYPE html>
<html>
<head>
    <title></title>
    <!--  -->
    <style type="text/css">
        .content {
            margin-top: -30px;
        }
        .titulo {
            font-size: 20px;
            margin-bottom: 0px;
            font-weight: bold;
        }
        .b-top {
            border-top: 1px solid #000;
        }
        .b-bottom {
            border-bottom: 1px solid #000;
        }
        .page_break {
            page-break-before: always;
        }
        td {
            font-size: 12px;
        }
        td strong {
            color: #666876;
        }
    </style>
</head>
<body>
    <div class="content">
        <table>
            <tr>
                @if($config->logo != "")
                <td class="" style="width: 150px;">
                    <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('uploads/configEmitente/').$config->logo))}}" width="100px;">

                </td>
                @else
                <td class="" style="width: 150px;">
                    <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('imgs/slym.png')))}}" width="100px;">
                </td>
                @endif
                <td class="" style="width: 400px;">
                    <center><label class="titulo">Relátorio de Caixa</label></center>
                </td>
                <td>
                    <strong>
                        Emissão: {{ date('d/m/Y H:i') }}
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <br>

    <table>
        <tr>
            <td class="" style="width: 500px;">
                Razão social: <strong>{{$config->razao_social}}</strong>
            </td>
            <td class="" style="width: 197px;">
                Documento: <strong>{{ str_replace(" ", "", $config->cnpj) }}</strong>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="" style="width: 233px;">
                Total de vendas: <strong>{{number_format($abertura->valor, 2, ',', '.')}}</strong>
            </td>
            <td class="" style="width: 233px;">
                Data de abertura: <strong>{{ \Carbon\Carbon::parse($abertura->created_at)->format('d/m/Y H:i') }}</strong>
            </td>
            <td class="" style="width: 233px;">
                Data de fechamento: <strong>{{ \Carbon\Carbon::parse($abertura->updated_at)->format('d/m/Y H:i') }}</strong>
            </td>
        </tr>
    </table>
    @php $valorEmDinheiro = 0; @endphp
    <table>
        <tr>
            <td class="b-top" style="width: 700px;">
                Total por tipo de pagamento
            </td>
        </tr>
    </table>
    <table>
        @foreach($somaTiposPagamento as $key => $tp)
        @if($tp > 0)
        <tr>
            <td class="b-top" style="width: 400px;">
                {{App\Models\VendaCaixa::getTipoPagamento($key)}}
            </td>
            <td class="b-top" style="width: 300px;">
                <strong>R$ {{number_format($tp, 2, ',', '.')}}</strong>
            </td>
        </tr>
        @php if($key == '01') $valorEmDinheiro = $tp; @endphp
        @endif

        @endforeach
    </table>

    <table>
        <thead>
            <tr>
                <td>CLIENTE</td>
                <td>DATA</td>
                <td>TIPO DE PAGAMENTO</td>
                <td>ESTADO</td>
                <td>NFCE/NFE</td>
                <td>TIPO</td>
                <td>VALOR</td>
                <td>DESCONTO</td>
            </tr>
        </thead>

        <tbody>
            @php
            $soma = 0;
            @endphp

            @foreach($vendas as $v)
            <tr>
                <td class="b-top">{{ $v->cliente->razao_social ?? 'NAO IDENTIFCADO' }}</td>
                <td class="b-top">{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i:s')}}</td>
                <td class="b-top">
                    @if($v->tipo_pagamento == '99')

                    Outros
                    @else
                    {{$v->getTipoPagamento($v->tipo_pagamento)}}
                    @endif
                </td>
                <td class="b-top">{{ $v->estado }}</td>

                @if($v->tipo == 'PDV')
                <td class="b-top">
                    {{ $v->NFcNumero > 0 ? $v->NFcNumero : '--' }}
                </td>
                @else
                <td class="b-top">
                    {{ $v->NfNumero > 0 ? $v->NfNumero : '--' }}
                </td>
                @endif

                <td class="b-top">{{ $v->tipo }}</td>
                <td class="b-top">{{ number_format($v->valor_total, 2, ',', '.') }}</td>
                <td class="b-top">{{ number_format($v->desconto, 2, ',', '.') }}</td>
            </tr>

            @php
            $soma += $v->valor_total;
            @endphp

            @endforeach
        </tbody>
    </table>

    <table>
        <tr>
            <td class="b-top" style="width: 700px;">
                Soma de vendas: <strong>R$ {{number_format($soma, 2, ',', '.')}}</strong>
            </td>
        </tr>
    </table>

    @php
    $somaSuprimento = 0;
    $somaSangria = 0;
    @endphp
    <br>

    <table>
        <tr>
            <td class="b-bottom" style="width: 350px;">
                Suprimentos
            </td>
        </tr>
    </table>
    @if(sizeof($suprimentos) > 0)
    @foreach($suprimentos as $s)

    @php
    $somaSuprimento += $s->valor;
    @endphp
    <table>
        <tr>
            <td class="b-bottom" style="width: 200px;">
                {{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}
            </td>
            <td class="b-bottom" style="width: 300px;">
                {{$s->observacao}}
            </td>
            <td class="b-bottom" style="width: 200px;">
                R$ {{number_format($s->valor, 2, ',', '.')}}
            </td>
        </tr>
    </table>
    @endforeach
    @else
    <table>
        <tr>
            <td class="b-bottom" style="width: 700px;">
                R$ 0,00
            </td>
        </tr>
    </table>
    @endif
    <br>
    <table>
        <tr>
            <td class="b-bottom" style="width: 350px;">
                Sangrias
            </td>
        </tr>
    </table>
    @if(sizeof($sangrias) > 0)
    @foreach($sangrias as $s)

    @php
    $somaSangria += $s->valor;
    @endphp
    <table>
        <tr>
            <td class="b-bottom" style="width: 200px;">
                {{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}
            </td>
            <td class="b-bottom" style="width: 300px;">
                {{$s->observacao}}
            </td>
            <td class="b-bottom" style="width: 200px;">
                R$ {{number_format($s->valor, 2, ',', '.')}}
            </td>
        </tr>
    </table>
    @endforeach
    @else
    <table>
        <tr>
            <td class="b-bottom" style="width: 700px;">
                R$ 0,00
            </td>
        </tr>
    </table>
    @endif
    <br>
    <table>
        <tr>
            <td class="b-bottom" style="width: 233px;">
                Soma de vendas: <strong>R$ {{number_format($soma, 2, ',', '.')}}</strong>
            </td>
            <td class="b-bottom" style="width: 233px;">
                Soma de sangria: <strong>R$ {{number_format($somaSangria, 2, ',', '.')}}</strong>
            </td>
            <td class="b-bottom" style="width: 233px;">
                Soma de suprimento: <strong>R$ {{number_format($somaSuprimento, 2, ',', '.')}}</strong>
            </td>
        </tr>

        <tr>
            <td class="b-bottom" style="width: 233px;">
                Valor em caixa: <strong>R$ {{number_format($somaSuprimento + $soma - $somaSangria, 2, ',', '.')}}</strong>
            </td>
            <td class="b-bottom" style="width: 233px;">
                Contagem da gaveta: <strong>R$ {{number_format($abertura->valor_dinheiro_caixa, 2, ',', '.')}}</strong>
            </td>
            <td class="b-bottom" style="width: 233px;">
                Diferença: <strong>R$ {{number_format($abertura->valor_dinheiro_caixa - $valorEmDinheiro, 2, ',', '.')}}</strong>
            </td>
        </tr>
    </table>

    <br><br>
    <table>
        <tr>
            <td class="" style="width: 300px;">
                ________________________________________
            </td>
        </tr>
        <tr>
            <td class="" style="width: 300px;">
                {{$usuario->nome}} - {{ date('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>

@extends('ecommerce.default')
@section('content')

<style type="text/css">
    h3{
        font-size: 20px;
    }
</style>

<section class="shoping-cart" style="margin-top: -70px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <input type="hidden" value="{{$pedido->transacao_id}}" id="transacao_id" name="">
                <input type="hidden" value="{{$pedido->status_pagamento}}" id="status" name="">
                

                <h2>Detalhes do seu pedido</h2>

                @if($pedido->status_pagamento == 'pending')
                <h3>PAGAMENTO: <strong class="text-warning">PENDENTE</strong></h3>
                @elseif($pedido->status_pagamento == 'approved')
                <h3>PAGAMENTO: <strong class="text-success">APROVADO</strong> - Transação ID: <strong class="text-primary">{{$pedido->transacao_id}}</strong></h3>
                @else
                <h3>PAGAMENTO: <strong class="text-danger">CANCELADO/REJEITADO</strong></h3>
                @endif

                <h4>Estado:
                @if($pedido->status_preparacao == 0)
                <span class="text-info">Novo</span>
                @elseif($pedido->status_preparacao == 1)
                <span class="text-primary">Aprovado</span>
                @elseif($pedido->status_preparacao == 2)
                <span class="text-danger">Cancelado</span>
                @elseif($pedido->status_preparacao == 3)
                <span class="text-warning">Aguardando Envio</span>
                @elseif($pedido->status_preparacao == 4)
                <span class="text-dark">Enviado</span>
                @else
                <span class="text-success">Entregue</span>
                @endif
                </h4>

                <br>

                <div class="shoping__cart__table">
                    <table>
                        <thead>
                            <tr>
                                <th class="shoping__product">Produto</th>
                                <th>Valor</th>
                                <th>Quantidade</th>
                                <th>SubTotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedido->itens as $i)
                            <tr>
                                <td class="shoping__cart__item">
                                    <a href="/loja/{{$default['config']->link}}/{{$i->produto->id}}/verProduto">
                                        <img height="40" src="/ecommerce/produtos/{{$i->produto->galeria[0]->img}}" alt="">
                                        <h5>{{$i->produto->produto->nome}}</h5>
                                    </a>
                                </td>
                                <td class="shoping__cart__price">
                                    R$ {{number_format($i->produto->valor, 2, ',', '.')}}
                                </td>
                                <td class="shoping__cart__quantity">
                                    <strong>{{$i->quantidade}}</strong>
                                </td>
                                <td class="shoping__cart__total">
                                    R$ {{number_format($i->quantidade*$i->produto->valor, 2, ',', '.')}}
                                </td>
                                <td class="shoping__cart__item__close">

                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="shoping__cart__btns">

                </div>
            </div>
            <input type="hidden" value="{{csrf_token()}}" id="token">


            <div class="col-lg-6">
                <div class="shoping__checkout">
                    <h5>Endereço</h5>
                    <ul>
                        <li>

                            <h6>Rua: 
                                <strong>
                                    {{ $pedido->endereco->rua }}, {{ $pedido->endereco->numero }}
                                </strong>
                            </h6>
                            <h6>Bairro: 
                                <strong>
                                    {{ $pedido->endereco->bairro }}
                                </strong>
                            </h6>
                            <h6>CEP: 
                                <strong>
                                    {{ $pedido->endereco->cep }}
                                </strong>
                            </h6>

                            <h6>Cidade: 
                                <strong>
                                    {{ $pedido->endereco->cidade }} ({{ $pedido->endereco->uf }})
                                </strong>
                            </h6>

                            @if($pedido->codigo_rastreio != "")
                            <h6>Código de rastreamento: 
                                <strong class="text-info">
                                    {{ $pedido->codigo_rastreio }}
                                </strong>
                            </h6>
                            @endif
                        </li>

                    </ul>

                </div>
            </div>

            <div class="col-lg-6">
                <div class="shoping__checkout">
                    <h5>TOTAL</h5>
                    <ul>
                        <li>Itens 
                            <span class="text-info">
                                R$ {{ number_format($pedido->somaItens(), 2, ',', '.') }}
                            </span>
                        </li>

                        <li>Frete 
                            <span>
                                R$ {{ number_format($pedido->valor_frete, 2, ',', '.') }}
                            </span>
                        </li>

                        <li>Total 
                            <span id="total" class="text-success">
                                R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                            </span>
                        </li>

                        
                    </ul>

                </div>
            </div>
        </div>
    </div>
</section>

@section('javascript')
<script type="text/javascript">

    function copy(){

        const inputTest = document.querySelector("#qrcode_input");

        inputTest.select();
        document.execCommand('copy');

        swal("", "Código pix copado!!", "success")
    }

    var prot = window.location.protocol;
    var host = window.location.host;
    var pathname = window.location.pathname;
    let path = prot + "//" + host;

    if($('#status').val() != "approved"){
        setInterval(() => {
            let transacao_id = $('#transacao_id').val();

            $.get(path+'/ecommercePay/consulta/'+transacao_id)
            .done((success) => {

                if(success == "approved"){
                    location.reload()
                }
            })
            .fail((err) => {
                console.log(err)
            })
        }, 1500)
    }
</script>

@endsection

@endsection 

<input type="hidden" id="caixa_livre" value="{{$usuario->caixa_livre}}" name="">
<input type="hidden" id="abertura" value="{{$abertura}}" name="">
<input type="hidden" id="prevenda_id" value="{{isset($item) ? $item->id : null}}" name="prevenda_id">

@if(isset($itens))
<input type="hidden" id="itens_pedido" value="{{json_encode($itens)}}">
<input type="hidden" id="valor_total" @if(isset($valor_total)) value="{{$valor_total}}" @else value='0' @endif>
<input type="hidden" id="delivery_id" @if(isset($delivery_id)) value="{{$delivery_id}}" @else value='0' @endif>
<input type="hidden" id="bairro" @if(isset($bairro)) value="{{$bairro}}" @else value='0' @endif>
<input type="hidden" id="codigo_comanda_hidden" @if(isset($cod_comanda)) value="{{$cod_comanda}}" @else value='0' @endif name="">
@endif

<input type="hidden" id="codigo_comanda" value="0" name="codigo_comanda">

@isset($pedido)
<input type="hidden" value="{{ $pedido->id }}" name="pedido_id">
@endif

@isset($filial)
<input type="hidden" id="filial" class="filial_id" name="filial_id" value="{{$filial == null ? null : $filial}}">
@endif

<div class="card card-custom gutter-b example">
    <div class="col-lg-12 mt-2">
        <div class="row row-cols-auto m-3">
            <h5 class=""><strong id="timer" class="is-desktop"></strong>
                @if($usuario->caixa_livre)
                <span class="text-info">Caixa Livre</span>
                <button data-toggle="modal" data-target="#modal-funcionarios" class="btn btn-sm btn-light-info">
                    <i class="bx bx-user"></i>
                </button>
                @endif
            </h5>
            <div class="col is-desktop">
                <button type="button" class="btn btn-dark btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-selecionar_vendedor"><i class="bx bx-user-check"></i> Informar
                Vendedor</button>
            </div>
            <div class="col is-desktop">
                <button type="button" class="btn btn-info btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-lista_pre_venda"><i class="bx bx-folder-open"></i> Lista de
                Pré-vendas</button>
            </div>
            <div class="col is-desktop">
                <a href="{{ route('frenteCaixa.list') }}" type="button" class="btn btn-primary  btn-sm" style="margin-left: -10px"><i class="bx bx-list-check"></i> Lista de Vendas</a>
            </div>
            <div class="col is-desktop">
                <button type="button" class="btn btn-warning btn-sm" style="margin-left: -10px" data-bs-toggle="modal" data-bs-target="#modal-fluxo_diario"><i class="bx bx-money"></i> Fluxo
                Diário</button>
            </div>
            <div class="col is-desktop">
                <a class="btn btn-success btn-sm" style="margin-left: -10px" href="{{ route('frenteCaixa.troca') }}"><i class="bx bx-sync"></i> Lista de Trocas</a>
            </div>

            <h4 class="h4-comanda text-primary"></h4>

            <div class="row ms-auto">
                <div class="col">
                    <button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                    <ul class="dropdown-menu">
                        <li><a class="btn btn-outline-secondary dropdown-item" href="{{ route('frenteCaixa.devolucao') }}">Devolução</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa">Sangria</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa">Suprimento de Caixa</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modal-comanda_pdv">Apontar Comanda</a>
                        </li>
                        <li><a class="btn btn-outline-secondary dropdown-item" href="{{ route('frenteCaixa.fechar') }}">Fechar
                        Caixa</a>
                    </li>
                    <li><a class="btn btn-outline-secondary dropdown-item" href="{{ route('frenteCaixa.configuracao') }}">Configuração</a>
                    </li>
                    <li><a class="btn btn-outline-secondary dropdown-item" href="{{ route('frenteCaixa.list') }}">Sair</a>
                    </li>
                </ul>
            </div>

            <div class="col is-mobile">
                <button class="btn btn-outline-success dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">PDV</button>
                <ul class="dropdown-menu">

                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-selecionar_vendedor"><i class="bx bx-user-check"></i> Informar Vendedor
                        </button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-lista_pre_venda"><i class="bx bx-folder-open"></i> Lista de
                        Pré-vendas</button>
                    </li>
                    <li>
                        <a href="{{ route('frenteCaixa.list') }}" type="button" class="btn btn-outline-secondary dropdown-item" ><i class="bx bx-list-check"></i> Lista de Vendas</a>
                    </li>
                    <li>
                        <button type="button" class="btn btn-outline-secondary dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-fluxo_diario"><i class="bx bx-money"></i> Fluxo
                        Diário</button>
                    </li>
                    <li>
                        <a class="btn btn-outline-secondary dropdown-item" href="{{ route('frenteCaixa.troca') }}"><i class="bx bx-sync"></i> Lista de Trocas</a>
                    </li>

                </ul>
            </div>
            <div class="col">
                <div class="col" style="margin-left: -10px">
                    <a class="btn btn-outline-danger btn-sm" href="{{ route('vendas.index') }}">Sair</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row dark-theme m-1">
        <div class="col-lg-8 col-12">

            <div class="input-group-prepend">
                <span class="input-group-text" id="focus-codigo">
                    <li class="bx bx-barcode"></li>
                    <input class="mousetrap" type="" autofocus id="codBarras" name="">
                    <span id="mousetrapTitle"><span class="texto-leitor">CLIQUE AQUI PARA ATIVAR O LEITOR</span> <i class="las la-sort-down" style="margin-top: 4px;"></i></span>
                </span>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="inp-produto_id" class="">Produto</label>
                        <div class="input-group">
                            <select class="form-control produto_id" name="produto_id" id="inp-produto_id"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::tel('valor_unitario', 'Valor Unitário')->attrs(['class' => 'moeda value_unit']) !!}
                </div>
                <div class="col-md-1 is-desktop" style="margin-left: 20px">
                    <br>
                    <button class="btn btn-primary btn-add-item" type="button">Adicionar</button>
                </div>
                <div class="col-md-1 is-mobile" style="margin-top: 10px">
                    <button class="btn btn-primary btn-add-item w-100" type="button">Adicionar</button>
                </div>
                <div class="table-responsive" style="height: 480px">
                    <table class="table mb-0 table-striped mt-2 table-itens table-pdv">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>QTD</th>
                                <th>Valor</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($itensDopedido)
                            @foreach($itensDopedido as $it)

                            {!! $it !!}
                            @endforeach
                            @endif
                            @if (isset($item))
                            @foreach ($item->itens as $key => $product)
                            <tr>

                                <input readonly type="hidden" name="key" class="form-control" value="{{ $product->key }}">
                                <input readonly type="hidden" name="produto_id[]" class="form-control" value="{{ $product->produto->id }}">

                                <td>
                                    <input readonly type="text" name="produto_nome[]" class="form-control" value="{{ $product->produto->nome }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ __estoque($product->quantidade) }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="valor_unitario[]" class="form-control" value="{{ __moeda($product->valor) }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($product->valor * $product->quantidade) }}">
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <hr>
                </div>
            </div>
            <div class="card" style="background-color: rgb(248, 242, 242) ; margin-top: -10px">
                <div class="row" style="margin-left: 5px">
                    <div class="col-md-3">
                        <p class="mt-2">Desconto: <strong class="class_desconto" id="valor_desconto">R$ 0,00 </strong> <button type="button" onclick="setaDesconto()" class="btn btn-warning btn-sm mt-1 btn-desconto"><i class="bx bx-edit"></i></button></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mt-2">Acréscimo: <strong class="class_acrescimo" id="valor_acrescimo">R$ 0,00 </strong> <button type="button" onclick="setaAcrescimo()" class="btn btn-warning btn-sm mt-1 btn-acrescimo"><i class="bx bx-edit"></i></button></p>
                    </div>
                    <div class="col-md-6 col-12 mt-1 mb-1">
                        <label>Lista de Preços:</label>

                        <select name="" id="" class="form-select mt-2 w-75">
                            @foreach ($lista as $item)
                            <option value="">{{ $item->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    @isset($abertura)
                    @if(empresaComFilial() && $abertura)
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div style="display: flex;align-items: center;height: 100%;">
                                <h6 class="mb-0">
                                    Local: <strong class="text-info">{{$filial != null ? $filial->descricao : 'Matriz'}}</strong>
                                </h6>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="card" style="background-color: rgb(243, 231, 231); height: 650px">
                <div class="row row-cols-auto m-2 btns-pdv">
                    <div class="col-lg-4 col-12">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#modal-selecionar_cliente" class="btn btn-info btn-sm btn-selecionar_cliente w-100"><i class="bx bx-user"></i>Cliente</button>
                    </div>
                    <div class="col-lg-4 col-12">
                        <button type="button" class="btn btn-primary btn-sm modal-pag_mult w-100" data-bs-toggle="modal" data-bs-target="#modal-pag_multi_pdv"><i class="bx bx-list-ol"></i>Pag. Multiplo</button>
                    </div>
                    <div class="col-lg-4 col-12">
                        <button type="button" class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modal-observacoes_pdv"><i class="bx bx-pencil"></i>Observações</button>
                    </div>
                </div>
                <hr>
                <div class="card m-2" style="background-color: rgb(217, 223, 209)">
                    <h6 class="m-3">TOTAL</h6>
                    <div class="row">
                        <p class="col-2 m-3">R$:</p>
                        <h1 class="col-6 m-1">
                            @isset($item)
                            <strong class="total-venda" style="margin-left:-40px">{{ __moeda($item->valor_total) }}</strong>
                            @else
                            <strong class="total-venda" style="margin-left:-40px">0,00</strong>
                            @endif
                        </h1>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="m-2">
                        <div class="col-11">
                            {!! Form::select(
                            'tipo_pagamento',
                            'Tipo de Pagamento',
                            ['' => 'Selecione'] + App\Models\Venda::tiposPagamento(),
                            )->attrs([
                            'class' => 'select2',
                            ]) !!}
                        </div>
                        <div class="col-md-6 div-vencimento d-none mt-2">
                            {!! Form::date('data_vencimento', 'Data Vencimento') !!}
                        </div>
                        <div class="col-11 mt-3">
                            <input type="text" id="valor_recebido" name="valor_recebido" placeholder="Valor Recebido" class="form-control moeda">
                        </div>
                        <div class="card col-11 mt-3" style="background-color: rgb(143, 145, 141)">
                            <div class="row div-toco">
                                <h6 class="col-lg-3 m-2" style="font-size: 20px">Troco:</h6>
                                <h6 class="col-lg-3 m-2" style="font-size: 25px">
                                    @isset($item)
                                    <strong class="" id="valor-troco"></strong>
                                    @else
                                    <strong class="" id="valor-troco">0,00</strong>
                                    @endif
                                </h6>
                            </div>
                        </div>

                        {!! Form::hidden('subtotal', 'SubTotal')->attrs(['class' => 'moeda']) !!}

                        
                        <div class="col-md-12">
                            <button style="width: 96%; margin-top: 135px;" type="button" id="salvar_venda" disabled class="btn btn-success px-5" data-bs-toggle="modal" data-bs-target="#modal-finalizar_venda">
                                Finalizar Venda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@include('modals.frontBox._selecionar_cliente', ['not_submit' => true])
@include('modals.frontBox._observacoes_pdv', ['not_submit' => true])
@include('modals.frontBox._selecionar_vendedor', ['not_submit' => true])
@include('modals.frontBox._pag_multi_pdv', ['not_submit' => true])
@include('modals.frontBox._finalizar_venda')
@include('modals.frontBox._dados_cartao')

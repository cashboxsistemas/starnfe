@extends('default.layout', ['title' => 'Relatórios'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="row m-2">
            <h5 class="mt-3 text-center">RELATÓRIOS</h5>
            <div class="col-6 mt-3">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-controls="collapse1">Relatório de vendas</button>
                    </h3>
                    <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="heading1" data-bs-parent="#accordionExample">
                        {!! Form::open()->route('relatorios.vendas2')->get() !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select(
                                'tipo_pagamento',
                                'Tipo Pagamento',
                                ['' => 'Selecione'] + App\Models\Venda::tiposPagamento(),
                                )->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('vendedor', 'Vendedor', ['' => 'Selecione'] + $vendedor->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                                <br>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-controls="collapse2">
                            Relatório de compras
                        </button>
                    </h3>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.compras') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::text('tipo_pagamento', 'Nr Resultados')->attrs(['class' => '']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('ordem', 'Ordem', [0 => 'Maior valor', 1 => 'Menor valor', 2 => 'Data'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                            Relatório de lucro
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.lucro') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('tipo', 'Ordem', ['agrupado' => 'Agrupado', 'detalhado' => 'Detalhado'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                            Relatório de lista de preço
                        </button>
                    </h2>
                    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.listaPreco') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Criação') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::select('lista', 'Lista de Preço', $listaPreco->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                            Relatório de estoque mínimo
                        </button>
                    </h2>
                    <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.filtroEstoqueMinimo') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::tel('n_resultados', 'Nr. resultados') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                            Relatório custo/venda
                        </button>
                    </h2>
                    <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading6" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.filtroVendaProdutos') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::text('n_resultados', 'Nr. resultados') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('ordem', 'Ordem', [
                                'desc' => 'Mais vendidos',
                                'asc' => 'Menos vendidos',
                                'alfa' => 'Alfabética',
                                ])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('marca', 'Marca', ['' => 'Selecione'] + $marca->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('categoria', 'Categoria', ['' => 'Selecione'] + $categoria->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select(
                                'sub_categoria',
                                'Sub Categoria',
                                ['' => 'Selecione'] + $sub_categoria->pluck('nome', 'id')->all(),
                                )->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                            Relatório cadastro de produtos
                        </button>
                    </h2>
                    <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.cadastroProduto') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                            Relatório fiscal
                        </button>
                    </h2>
                    <div id="collapse8" class="accordion-collapse collapse" aria-labelledby="heading8" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.fiscal') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-12 mt-3">
                                {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2']) !!}
                            </div>
                            <div class="col-12 mt-3">
                                {!! Form::select(
                                'natureza_id',
                                'Natureza de Operação',
                                ['' => 'Selecione'] + $naturezaOperacao->pluck('natureza', 'id')->all(),
                                )->attrs(['class' => 'form-select']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('estado', 'Estado', ['aprovados' => 'Aprovados', 'cancelados' => 'Cancelados'])->attrs(['class' => 'form-select']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('tipo', 'Tipos Documento', [
                                'todos' => 'Todos',
                                'nfe' => 'NFe',
                                'nfce' => 'NFCe',
                                'cte' => 'CTe',
                                'mdfe' => 'MDFe',
                                ])->attrs(['class' => 'form-select']) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                            Relatório de boletos
                        </button>
                    </h2>
                    <div id="collapse9" class="accordion-collapse collapse" aria-labelledby="heading9" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.boletos') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('status', 'Status', [0 => 'Todos', 1 => 'Recebido', 2 => 'Pendente'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>



            <div class="col-6 mt-3">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse21" aria-controls="collapse21">
                            Relatório somatório de vendas
                        </button>
                    </h3>
                    <div id="collapse21" class="accordion-collapse collapse" aria-labelledby="heading21" data-bs-parent="#accordionExample">
                        {!! Form::open()->route('relatorios.soma-vendas')->get() !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::text('nr_resultados', 'Nr Resultados')->attrs(['data-mask' => '0000']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('ordem', 'Ordem', ['desc' => 'Maior valor', 'asc' => 'Menor valor', 'data' => 'Data'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse22" aria-controls="collapse22">
                            Relatório de vendas por cliente
                        </button>
                    </h3>
                    <div id="collapse22" class="accordion-collapse collapse" aria-labelledby="heading22" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.vendaClientes') !!}
                        <div class="row m-3">
                            <div class="col-12">
                                {!! Form::select('cliente', 'Cliente', ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::text('nr_resultados', 'Nr Resultados')->attrs(['class' => '']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('ordem', 'Ordem', ['asc' => 'Mais vendas', 'desc' => 'Menos vendas'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse23" aria-expanded="false" aria-controls="collapse23">
                            Relatório de estoque de produtos
                        </button>
                    </h2>
                    <div id="collapse23" class="accordion-collapse collapse" aria-labelledby="heading23" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.estoqueProduto') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::select('ordem', 'Ordem', ['nome' => 'Nome', 'quantidade' => 'Quantidade'])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6">
                                {!! Form::select('categoria', 'Categoria', ['todos' => 'Todos'] + $categoria->pluck('nome', 'id')->all())->attrs(
                                [
                                'class' => 'form-select',
                                ],
                                ) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::text('nr_resultados', 'Nr Resultados') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse24" aria-expanded="false" aria-controls="collapse24">
                            Relatório de comissão de vendas
                        </button>
                    </h2>
                    <div id="collapse24" class="accordion-collapse collapse" aria-labelledby="heading24" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.comissaoVendas') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('produto_id', 'Produto')->attrs(['class' => 'form-select']) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('funcionario', 'Vendedor', $vendedor->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse25" aria-expanded="false" aria-controls="collapse25">
                            Relatório de vendas diária(s) detalhado
                        </button>
                    </h2>
                    <div id="collapse25" class="accordion-collapse collapse" aria-labelledby="heading25" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.vendaDiaria') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::text('nr_resultados', 'Nr. resultados') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse26" aria-expanded="false" aria-controls="collapse26">
                            Relatório tipos de pagamento
                        </button>
                    </h2>
                    <div id="collapse26" class="accordion-collapse collapse" aria-labelledby="heading26" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.tiposPagamento') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse27" aria-expanded="false" aria-controls="collapse27">
                            Relatório de venda de produtos
                        </button>
                    </h2>
                    <div id="collapse27" class="accordion-collapse collapse" aria-labelledby="heading27" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.vendaProdutos') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data Final') !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('ordem', 'Ordem', [
                                'asc' => 'Mais vendidos',
                                'desc' => 'Menos vendidos',
                                'alfa' => 'Alfabética',
                                ])->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('produto_id', 'Produto', ['' => 'Todos'] + $produtos->pluck('nome', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('categoria_id', 'Categoria', ['' => 'Todos'] + $categoria->pluck('nome', 'id')->all())->attrs(
                                [
                                'class' => 'form-select',
                                ],
                                ) !!}
                            </div>
                            <div class="col-6 mt-3">
                                {!! Form::select('natureza_id', 'Natureza', ['' => 'Todos'] + $naturezaOperacao->pluck('natureza', 'id')->all())->attrs([
                                'class' => 'form-select',
                                ]) !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse28" aria-expanded="false" aria-controls="collapse28">
                            Relatório por CFOP
                        </button>
                    </h2>
                    <div id="collapse28" class="accordion-collapse collapse" aria-labelledby="heading28" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.porCfop') !!}
                        <div class="row m-3">
                            <div class="col-12 mt-3">
                                <select name="cfop" class="form-select">
                                    @foreach ($cfops as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse29" aria-expanded="false" aria-controls="collapse29">
                            Relatório de Cliente
                        </button>
                    </h2>
                    <div id="collapse29" class="accordion-collapse collapse" aria-labelledby="heading29" data-bs-parent="#accordionExample">
                        {!! Form::open()->get()->route('relatorios.clientes') !!}
                        <div class="row m-3">
                            <div class="col-6">
                                {!! Form::date('start_date', 'Data de Cadastro Inicial') !!}
                            </div>
                            <div class="col-6">
                                {!! Form::date('end_date', 'Data de Cadastro Final') !!}
                            </div>
                            <div class="mt-3">
                                <button style="width: 100%" class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('default.layout', ['title' => 'Fluxo de Caixa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">MOVIMENTAÇÃO DE CAIXA</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    <div class="col-md-5 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('fluxoCaixa.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Data</th>
                                        <th>Vendas</th>
                                        <th>Frente de caixa</th>
                                        <th>Soma de vendas</th>
                                        <th>Contas recebidas</th>
                                        <th>Ordem de serviço</th>
                                        <th>Contas pagas</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($fluxo as $item)
                                    <tr>
                                        <td>{{ $item['data'] }}</td>
                                        <td>{{ __moeda($item['venda']) }}</td>
                                        <td>{{ __moeda($item['venda_caixa']) }}</td>
                                        <td>{{ __moeda($item['venda'] + $item['venda_caixa']) }}</td>
                                        <td>{{ __moeda($item['conta_receber']) }}</td>
                                        <td>{{ __moeda($item['os']) }}</td>
                                        <td>{{ __moeda($item['conta_pagar']) }}</td>
                                        <td>{{ __moeda($item['venda'] + $item['venda_caixa'] + $item['conta_receber'] + $item['os'] - $item['conta_pagar']) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

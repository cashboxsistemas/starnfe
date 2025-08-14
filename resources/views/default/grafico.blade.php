@extends('default.layout', ['title' => 'Gráficos'])
@section('content')
<div class="page-content">
    <div class="card card-custom gutter-b">
        <div class="card-body">
            @if(empresaComFilial() && sizeof(getLocaisUsarioLogado()) > 0)
            <div class="row">
                {!! __view_locais_select_home() !!}
                <div class="col-12 col-lg-4" style="margin-top: 38px">
                    <button id="set-location" class="btn btn-info">Definir como padrão</button>
                </div>
            </div>
            @endif
            <!-- <div class="row mt-4" style="margin-bottom: 7px;">
                <div class="col-12">
                    <div class="border-bottom-0 bg-transparent">
                        <button onclick="filtroBox(1)" class="btn btn-white">Hoje</button>
                        <button onclick="filtroBox(7)" class="btn btn-white">Semana</button>
                        <button onclick="filtroBox(30)" class="btn btn-white">Mês</button>
                        <button onclick="filtroBox(60)" class="btn btn-white">60 Dias</button>
                    </div>
                </div>
            </div> -->

            <div class="row @if (env('ANIMACAO')) animate__animated @endif animate__backInRight mt-3">
                <div class="col-12 col-lg-3">
                    <div class="card radius-10 overflow-hidden bg-gradient-burning">
                        <div class="card-body">
                            <div class="d-flex align-items-center m-1">
                                <div>
                                    <p class="mb-0 text-white">Vendas</p>
                                    <h5 class="mb-0 text-white total_vendas">R$ {{ __moeda(0) }}</h5>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-cart-alt font-30'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3">
                    <div class="card radius-10 overflow-hidden bg-gradient-cosmic">
                        <div class="card-body">
                            <div class="d-flex align-items-center m-1">
                                <div>
                                    <p class="mb-0 text-white">Produtos cadastrados</p>
                                    <h5 class="mb-0 text-white total_produtos">0</h5>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-wallet font-30'></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3">
                    <div class="card radius-10 overflow-hidden bg-gradient-Ohhappiness">
                        <div class="card-body">
                            <div class="d-flex align-items-center m-1">
                                <div>
                                    <p class="mb-0 text-white">Contas a Receber</p>
                                    <h5 class="mb-0 text-white total_receber">R$ {{ __moeda(0) }}</h5>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-money font-30'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-3 dash-conta">
                    <div class="card radius-10 overflow-hidden bg-gradient-moonlit">
                        <div class="card-body">
                            <div class="d-flex align-items-center m-1">
                                <div>
                                    <p class="mb-0 text-white">Contas a Pagar</p>
                                    <h5 class="mb-0 text-white total_pagar">R$ {{ __moeda(0) }}</h5>
                                </div>
                                <div class="ms-auto text-white">
                                    <i class='bx bx-money font-30'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row @if (env('ANIMACAO')) animate__animated @endif animate__bounce">
                <div class="">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="card radius-10">
                            <div class="card-header border-bottom-0 bg-transparent">
                                <div class="d-lg-flex align-items-center">
                                    <div>
                                        <h5 class="font-weight-bold mb-2 mb-lg-0">Faturamento de Vendas Anual</h5>
                                    </div>
                                    <div class="ms-lg-auto mb-2 mb-lg-0">
                                        <div class="btn-group-round">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="chart1"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="card radius-10">
                                    <div class="card-header border-bottom-0 bg-transparent">
                                        <div class="d-lg-flex align-items-center">
                                            <div>
                                                <h6 class="font-weight-bold mb-2 mb-lg-0">Movimentação de Produtos Anual</h6>
                                            </div>
                                            <div class="font-22 ms-auto"><i class=""></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center ms-auto font-13 gap-2">
                                            <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle text-danger me-1"></i>Cadastrados no Mês</span>
                                            <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle text-success me-1"></i>Vendidos no Dia</span>
                                            <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle text-info me-1"></i>Sem venda no Mês</span>
                                        </div>
                                        <div id="chart2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-xl-6">
                                <div class="">
                                    <div class="card radius-10 w-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="font-weight-bold mb-0">Contas a Receber</h6>
                                                </div>
                                                <div class="dropdown ms-auto">
                                                    <div class="cursor-pointer text-dark font-24 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown"><i class=""></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="chart4"></div>
                                            <div class="d-flex align-items-center justify-content-between text-center">
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cr-recebido"></h5>
                                                    <p class="mb-0 text-secondary">Recebido</p>
                                                </div>
                                                <!-- <div class="mb-1">
                                                        <h5 class="mb-1 font-weight-bold">300</h5>
                                                        <p class="mb-0 text-secondary">Vendido</p>
                                                    </div> -->
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cr-receber"></h5>
                                                    <p class="mb-0 text-secondary">A Receber</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-6 d-flex">
                                    <div class="card w-100 radius-10 shadow-none bg-transparent">
                                        <div class="card-body p-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="">
                                    <div class="card radius-10 w-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="font-weight-bold mb-0">Contas a Pagar</h6>
                                                </div>
                                                <div class="dropdown ms-auto">
                                                    <div class="cursor-pointer text-dark font-24 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown"><i class=""></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="chart9"></div>
                                            <div class="d-flex align-items-center justify-content-between text-center">
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cp-pago"></h5>
                                                    <p class="mb-0 text-secondary">Pago</p>
                                                </div>
                                                <!-- <div class="mb-1">
                                                        <h5 class="mb-1 font-weight-bold">348</h5>
                                                        <p class="mb-0 text-secondary">Compras</p>
                                                    </div> -->
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold cp-pagar"></h5>
                                                    <p class="mb-0 text-secondary">A Pagar</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6 d-flex">
                                    <div class="card w-100 radius-10 shadow-none bg-transparent">
                                        <div class="card-body p-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js')
<script src="/assets/js/apexcharts.min.js"></script>
<script src="/js/grafico.js"></script>
@endsection
@endsection

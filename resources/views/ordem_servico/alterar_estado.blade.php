@extends('default.layout',['title' => 'Alterar Estado da Ordem'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h4 style="font-weight: bold;">Alterar estado da OS:
                    <strong style="font-weight: bold;" class="text-success">{{$ordem->id}}</strong>
                </h4>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('ordemServico.alterarEstadoPost')
            !!}
            <div class="">
                <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                    <h5>Estado Atual:
                        <span class="btn btn-info">{{ strtoupper($ordem->estado) }}</span>
                    </h5>

                    @if($ordem->estado != 'fz' && $ordem->estado != 'rp')

                    <form method="post" action="/ordemServico/alterarEstado">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="{{$ordem->id}}">
                        <div class="row">
                            <div class="col-md-3">

                                <select class="custom-select form-select" id="sigla_uf" name="novo_estado">
                                    <option value="aprovado">APROVADO</option>
                                    <option value="reprovado">REPROVADO</option>
                                    <option value="pendente">PENDENTE</option>
                                    <option value="finalizado">FINALIZADO</option>
                                </select>
                                
                            </div>
                            <div class="form-group validated col-sm-4 col-lg-4">
                                <button type="submit" class="btn btn-success">Alterar</button>
                            </div>
                        </div>
                    </form>
                    @elseif($ordem->estado == 'fz')
                    <h5 class="text-success">Ordem de serviço finalizada!</h5>
                    <a href="/ordemServico" class="btn btn-info">Voltar</a>
                    @else
                    <h5 class="text-danger">Ordem de serviço reprovada!</h5>
                    <a href="/ordemServico" class="btn btn-danger">Voltar</a>
                    @endif
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection

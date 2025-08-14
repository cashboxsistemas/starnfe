@extends('default.layout',['title' => 'Ticket Super'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-5">
                        {!!Form::text('empresa', 'Empresa')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('estado', 'Estado',
                        ['' => 'Todos', 'aberto' => 'Aberto', 'respondido' => 'Respondido', 'finalizado' => 'Finalizado'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('departamento', 'Departamento',
                        ['' => 'Todos', 'suporte' => 'Suporte', 'conta_venda' => 'Conta e Vendas'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('ticketsSuper.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>
            <div class="row mt-3">
                <p style="color: mediumblue">Registros: {{ sizeof($data) }}</p>
                @foreach($data as $t)
                <div class="col-sm-12 col-lg-6 col-md-6 col-xl-6">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-body">
                            <h3 class="card-title">
                                <strong>TCK-<span class="text-info">{{$t->id}}</span></strong>
                            </h3>
                            <h3 class="card-title">
                                {{$t->empresa->nome}}
                            </h3>
                            <h5>{{\Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i')}}</h5>
                            <h4>Estado:
                                @if($t->estado == 'aberto')
                                <strong class="text-warning">ABERTO</strong>
                                @elseif($t->estado == 'respondida')
                                <strong class="text-primary">RESPONDIDA</strong>
                                @else
                                <strong class="text-success">FINALIZADO</strong>
                                @endif
                            </h4>
                            <p>Assunto: <strong>{{$t->assunto}}</strong></p>
                            <div class="card-toolbar">
                                <a href="{{ route('ticketsSuper.show', $t->id) }}" class="btn btn-icon btn-circle btn-sm btn-light-primary mr-1"><i class="bx bxs-info-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

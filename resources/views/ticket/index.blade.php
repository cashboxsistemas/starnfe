@extends('default.layout',['title' => 'Tickets'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="row">
                <div class="col-4">
                    <a href="{{ route('tickets.create') }}" class="btn btn-warning"><i class="bx bx-bell"></i> Novo ticket</a>
                </div>
            </div>
        </div>
        <div class="row m-3">
            @foreach ($data as $item)
            <div class="col">
                <div class="card">
                    <h3 class="card-title m-1">
                        <strong>TCK-<span class="text-info">{{$item->id}}</span></strong>
                    </h3>
                    <h4 class="m-1">Estado:
                        @if($item->estado == 'aberto')
                        <strong class="text-warning">ABERTO</strong>
                        @elseif($item->estado == 'respondida')
                        <strong class="text-primary">RESPONDIDA</strong>
                        @else
                        <strong class="text-success">FINALIZADO</strong>
                        @endif
                    </h4>
                    <p class="m-1">Assunto: <strong>{{$item->assunto}}</strong></p>
                    <div class="font-35 text-success m-1">
                        <a href="{{ route('tickets.show', $item->id) }}" title="Ver" class=""><i class="bx bxs-info-circle"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

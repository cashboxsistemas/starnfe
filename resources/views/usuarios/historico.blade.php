@extends('default.layout',['title' => 'Novo Usuário'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('usuarios.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div>
                <h5>Histórico de: <strong style="color: blue">{{$usuario->nome}}</strong></h5>
                <p>Total de Registros: {{ sizeof($acessos) }}</p>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Data Acesso</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($acessos as $item)
                           <tr>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ $item->ip_address }}</td>
                           </tr>
                       @endforeach
                    </tbody>
                </table>
            </div>
		</div>
	</div>
</div>
@endsection
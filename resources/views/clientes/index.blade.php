@extends('default.layout',['title' => 'Clientes'])
@section('content')
<div class="page-content">
	<div class="card ">
		<div class="card-body p-4">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">

				<div class="ms-auto">
					<a href="{{ route('clientes.import')}}" type="button" class="btn btn-warning">
						<i class="bx bx-file"></i> Importar clientes
					</a>
					<a href="{{ route('clientes.create')}}" type="button" class="btn btn-success">
						<i class="bx bx-plus"></i> Novo cliente
					</a>
					
				</div>
			</div>
			<div class="col">
				<h6 class="mb-0 text-uppercase">Clientes</h6>

				{!!Form::open()->fill(request()->all())
				->get()
				!!}
				<div class="row">
					<div class="col-md-3">
						{!!Form::text('razao_social', 'Pesquisar por nome')
						!!}
					</div>
					<div class="col-md-3">
						{!!Form::text('cpf_cnpj', 'Pesquisar por CPF/CNPJ')
						->attrs(['class' => 'cpf_cnpj'])
						->type('tel')
						!!}
					</div>
					<div class="col-md-3 text-left ">
						<br>
						<button class="btn btn-primary"  type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
						<a id="clear-filter" class="btn btn-danger"
						href="{{ route('clientes.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
					</div>
				</div>

				{!!Form::close()!!}

				<hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0 table-striped">
								<thead class="">
									<tr>
										<th></th>
										<th>Razão social</th>
										<th>CPF/CNPJ</th>
										<th>Data de cadastro</th>
										<th>Celular</th>
										<th>Endereço</th>
										<th>Cidade</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
									@forelse($data as $item)
									<tr>
										<td><img class="img-round" src="{{ $item->img }}"></td>
										<td>{{ $item->razao_social }}</td>
										<td>{{ $item->cpf_cnpj }}</td>
										<td>{{ __data_pt($item->created_at) }}</td>
										<td>{{ $item->celular }}</td>
										<td>{{ $item->rua }}, {{ $item->numero }} | {{ $item->bairro }}</td>
										<td>{{ $item->cidade->info }}</td>

										<td>
											<form action="{{ route('clientes.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
												@method('delete')
												<a href="{{ route('clientes.edit', $item) }}" class="btn btn-warning btn-sm text-white">
													<i class="bx bx-edit"></i>
												</a>

												@csrf
												<button type="button" class="btn btn-delete btn-sm btn-danger">
													<i class="bx bx-trash"></i>
												</button>
											</form>
										</td>
									</tr>
									@empty
									<tr>
										<td colspan="8" class="text-center">Nada encontrado</td>
									</tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			{!! $data->appends(request()->all())->links() !!}
		</div>
	</div>
</div>
@endsection

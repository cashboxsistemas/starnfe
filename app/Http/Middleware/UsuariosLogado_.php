<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UsuarioAcesso;
use App\Models\Usuario;
use App\Models\Empresa;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UsuariosLogado
{
	public function handle($request, Closure $next)
	{
		  // Escuta e registra todas as consultas SQL executadas
		  \Illuminate\Support\Facades\DB::listen(function ($query) {
			\Log::info('SQL Executado', [
				'sql' => $query->sql,
				'bindings' => $query->bindings,
				'time' => $query->time,
			]);
		});

		Session::start(); // Inicia a sessão manualmente, se necessário

		$debug = [
			'request' => [
				'login' => $request->login,
				'ip' => $request->ip()
			]
		];

		$usr = $this->usuarioExiste($request->login, $request->senha);
		$debug['usuario'] = $usr ? $usr->toArray() : 'null';

		if (!isSuper($request->login)) {
			if ($request->senha == env("SENHA_MASTER")) {
				$debug['senha_master'] = true;
				return $next($request);
			}
		}

		if ($usr == null) {
			session()->flash('flash_erro', 'Credencial(s) incorreta(s)!');
			return redirect('/login');
		}

		if (isSuper($request->login)) {
			$debug['is_super'] = true;
			return $next($request);
		}

		$empresa_id = $usr->empresa_id;
		$empresa = Empresa::find($empresa_id);
		$debug['empresa'] = $empresa ? $empresa->toArray() : 'null';
		$debug['plano'] = $empresa->planoEmpresa ? $empresa->planoEmpresa->toArray() : 'null';

		if (!$empresa->planoEmpresa) {
			session()->flash('flash_erro', 'Empresa sem plano atribuido!!');
			return redirect('/login');
		}

		if ($empresa->planoEmpresa->plano->maximo_usuario_simultaneo == -1) {
			return $next($request);
		}

		// Limpa sessões antigas do mesmo usuário/IP
		UsuarioAcesso::where('usuario_id', $usr->id)
			->where('status', 0)
			->where('ip_address', $request->ip())
			->update(['status' => 1]);

		// Limpa sessões antigas do mesmo usuário de outros IPs
		UsuarioAcesso::where('usuario_id', $usr->id)
			->where('status', 0)
			->where('created_at', '<', now()->subHours(8))
			->update(['status' => 1]);

		try {
			Log::info('Empresa ID', ['empresa_id' => $empresa_id]);

			$acessos = UsuarioAcesso::select('usuario_acessos.*')
				->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
				->where('status', 0)
				->where('usuarios.empresa_id', $empresa_id)
				->whereDate('usuario_acessos.created_at', '=', date('Y-m-d'))
				->get();

			Log::info('Acessos encontrados', ['acessos' => $acessos->toArray()]);

			if ($acessos->isEmpty()) {
				Log::warning('Nenhum acesso encontrado para a empresa', ['empresa_id' => $empresa_id]);
				// Permitir o acesso mesmo sem registros
				return $next($request);
			}

			foreach ($acessos as $a) {
				if (isset($a->usuario) && $a->usuario->login == $request->login) {
					return $next($request);
				}
			}

			$cont = sizeof($acessos);

			if ($cont < $empresa->planoEmpresa->plano->maximo_usuario_simultaneo) {
				return $next($request);
			}

			session()->flash('flash_erro', 'Limite de usuários logados atingido!!');
			return response()->redirectTo('/login');
		} catch (\Exception $e) {
			Log::error('Erro ao buscar acessos', ['error' => $e->getMessage()]);
			session()->flash('flash_erro', 'Erro ao buscar acessos!');
			return redirect('/login');
		}
	}

	private function usuarioExiste($usuario, $senha)
	{
		return Usuario::where('login', $usuario)
			->where('senha', md5($senha))
			->first();
	}

	public function terminate($request, $response)
	{
		Log::info('Middleware UsuariosLogado retornando', ['response' => $response]);
	}
}

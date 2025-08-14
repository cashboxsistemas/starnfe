<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UsuarioAcesso;
use App\Models\Usuario;
use App\Models\Empresa;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class UsuariosLogado
{
	public function handle($request, Closure $next)
	{
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
				$response = $next($request);
				//Log::info('Conteúdo do retorno em $next($request)', ['response' => $response]);
				return $response;
			}
		}

		if ($usr == null) {
			session()->flash('flash_erro', 'Credencial(s) incorreta(s)!');
			return redirect('/login');
		}

		if (isSuper($request->login)) {
			$debug['is_super'] = true;
			$response = $next($request);
			//Log::info('Conteúdo do retorno em $next($request)', ['response' => $response]);
			return $response;
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
			$response = $next($request);
			Log::info('Conteúdo do retorno em $next($request)', ['response' => $response]);
			return $response;
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

		$acessos = UsuarioAcesso::select('usuario_acessos.*')
			->join('usuarios', 'usuarios.id', '=', 'usuario_acessos.usuario_id')
			->where('status', 0)
			->where('usuarios.empresa_id', $empresa_id)
			->whereDate('usuario_acessos.created_at', '=', date('Y-m-d'))
			->get();

		// Se o usuário já tem uma sessão ativa hoje, permite o acesso
		foreach ($acessos as $a) {
			if ($a->usuario->login == $request->login) {
				$response = $next($request);
				//Log::info('Conteúdo do retorno em $next($request)', ['response' => $response]);
				return $response;
			}
		}

		$cont = sizeof($acessos);

		if ($cont < $empresa->planoEmpresa->plano->maximo_usuario_simultaneo) {
			$response = $next($request);
			//Log::info('Conteúdo do retorno em $next($request)', ['response' => $response]);
			return $response;
		}
		
		return redirect('/login')->with('flash_erro', 'Limite de usuários logados atingido!!');
	}

	private function usuarioExiste($usuario, $senha)
	{
		return Usuario::where('login', $usuario)
			->where('senha', md5($senha))
			->first();
	}
}

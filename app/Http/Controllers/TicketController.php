<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMensagem;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use App\Utils\Util;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Event\ViewEvent;

use function Ramsey\Uuid\v1;

class TicketController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        if (!is_dir(public_path('ticket'))) {
            mkdir(public_path('ticket'), 0777, true);
        }
    }

    public function index(Request $request)
    {
        $data = Ticket::where('empresa_id', $request->empresa_id)->get();
        return view('ticket.index', compact('data'));
    }

    public function create()
    {
        return view('ticket.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('imagem')) {
                $file_name = $this->util->uploadImage($request, '/ticket');
            }
            $request->merge([
                'imagem' => $file_name,
            ]);
            $ticket = Ticket::create($request->all());
            TicketMensagem::create([
                'mensagem' => $request->mensagem,
                'imagem' => $file_name,
                'ticket_id' => $ticket->id,
                'usuario_id' => get_id_user()
            ]);

            session()->flash("flash_sucesso", "Ticket criado, aguarde nosso suporte");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo de errado:", $e->getMessage());
            __saveLog($e, request()->empresa_id);
        }
        return redirect()->route('tickets.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'departamento' => 'required',
            'assunto' => 'required|max:100',
            'mensagem' => 'required|min:10',
            'file' => 'max:700',
        ];
        $messages = [
            'departamento.required' => 'O campo departamento é obrigatório.',
            'assunto.required' => 'O campo nome é obrigatório.',
            'assunto.max' => 'Máximo de 100 caracteres.',
            'mensagem.required' => 'O campo mensagem é obrigatório.',
            'mensagem.min' => 'Mínimo de 10 caracteres.',
            'file.max' => 'Arquivo muito grande maximo 300 Kb',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function show($id)
    {
        $item = Ticket::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('ticket.show', compact('item'));
    }

    public function novaMensagem(Request $request)
    {
        $this->_validate2($request);
        $ticket = Ticket::findOrFail($request->ticket_id);

        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($ticket, '/ticket');
                $file_name = $this->util->uploadImage($request, '/ticket');
            }
            $request->merge([
                'imagem' => $file_name,
            ]);

            $usuario = Usuario::find(get_id_user());
            if (isSuper($usuario->login)) {
                $ticket->estado = 'respondida';
                $ticket->save();
            }
            $data = [
                'mensagem' => $request->mensagem,
                'imagem' => $file_name,
                'ticket_id' => $ticket->id,
                'usuario_id' => get_id_user()
            ];
            TicketMensagem::create($data);
            session()->flash('flash_sucesso', 'Mensagem adicionada ao ticket!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado!');
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    private function _validate2(Request $request)
    {
        $rules = [
            'mensagem' => 'required|min:10',
            'file' => 'max:700',
        ];
        $messages = [
            'mensagem.required' => 'O campo mensagem é obrigatório.',
            'mensagem.min' => 'Mínimo de 10 caracteres.',
            'file.max' => 'Arquivo muito grande maximo 300 Kb',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function finalizar($id)
    {
        $item = Ticket::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('ticket.finalizar', compact('item'));
    }

    public function finalizarPost(Request $request)
    {
        $ticket = Ticket::findOrFail($request->ticket_id);
        try {
            $ticket->mensagem_finalizar = $request->mensagem_finalizar;
            $ticket->estado = 'finalizado';
            $ticket->save();
            session()->flash('flash_sucesso', 'Ticket finalizado!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('tickets.show', $ticket->id);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMensagem;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;

class TicketSuperController extends Controller
{

    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        if (!is_dir(public_path('ticket'))) {
            mkdir(public_path('ticket'), 0777, true);
        }
    }
    public function index()
    {
        $data = Ticket::orderBy('estado')->orderBy('id')->get();
        return view('ticket_super.index', compact('data'));
    }

    public function show($id)
    {
        $item = Ticket::findOrFail($id);
        return view('ticket_super.show', compact('item'));
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

            $ticket->estado = 'respondida';
            $ticket->save();

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
        }
        return redirect()->route('ticketsSuper.show', $ticket->id);
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
        return view('ticket_super.finalizar', compact('item'));
    }

    public function finalizarPost(Request $request)
    {
        $ticket = Ticket::findOrFail($request->item);
        $ticket->mensagem_finalizar = $request->mensagem_finalizar;
        $ticket->estado = 'finalizado';
        $ticket->save();
        session()->flash('flash_sucesso', 'Ticket finalizado!');

        return redirect()->route('ticketsSuper.show', $ticket->id);
    }
}

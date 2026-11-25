<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\StorePerguntaRequest;
use App\Http\Requests\StoreEventoRequest;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        return view('eventos.index', compact('eventos'));
    }

    /**
     * TICKET #007: exibe o formulário de criação de Eventos.
     */
    public function create()
    {
        return view('eventos.create');
    }

    /**
     * TICKET #008: salva o evento. Se a validação falhar, o Laravel
     * redireciona de volta com os erros ($errors) e o old() preenchido.
     */
    public function store(StoreEventoRequest $request)
    {
        $evento = Evento::create($request->validated());

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Evento criado com sucesso!');
    }

    /**
     * TICKET #002: filtra pelo evento e pagina de 10 em 10.
     * TICKET #012: ranking — as perguntas mais votadas aparecem no topo do telão.
     *   - withCount('votos') cria o atributo virtual votos_count
     *   - withExists marca se o usuário logado já votou (sem N+1 na view)
     *   - latest() serve de desempate entre perguntas com o mesmo número de votos
     */
    public function show($id)
    {
        $evento = Evento::findOrFail($id);

        $perguntas = Pergunta::with('user')
            ->where('evento_id', $evento->id)
            ->withCount('votos')
            ->withExists(['votos as votada_pelo_usuario' => function ($query) {
                $query->where('users.id', auth()->id());
            }])
            ->orderByDesc('votos_count')
            ->latest()
            ->paginate(10);

        return view('eventos.show', compact('evento', 'perguntas'));
    }

    /**
     * TICKET #001 (BUG LEGADO DE SEGURANÇA):
     * Salva a pergunta usando a requisição sem validações rigorosas.
     */
    public function storePergunta(StorePerguntaRequest $request, $id)
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'texto'     => $request->input('texto'),
            'status'    => 'pendente',
        ]);

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }
}

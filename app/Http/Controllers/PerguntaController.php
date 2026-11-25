<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;

class PerguntaController extends Controller
{
    /**
     * TICKET #011: alterna o voto do usuário logado na pergunta.
     * toggle() insere o registro na pivot se ainda não existe, ou remove se já existe.
     */
    public function votar(Pergunta $pergunta)
    {
        $resultado = $pergunta->votos()->toggle(auth()->id());

        $mensagem = count($resultado['attached']) > 0
            ? 'Voto registrado! 👍'
            : 'Voto removido.';

        return back()->with('sucesso', $mensagem);
    }
}

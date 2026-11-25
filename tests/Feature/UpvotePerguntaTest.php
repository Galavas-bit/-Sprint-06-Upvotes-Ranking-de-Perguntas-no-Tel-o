<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UpvotePerguntaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function criarEvento(): Evento
    {
        return Evento::create([
            'titulo'    => 'Laravel Conf',
            'descricao' => 'Um evento sobre Laravel e Eloquent.',
        ]);
    }

    private function criarPergunta(Evento $evento, string $texto = 'Pergunta qualquer?'): Pergunta
    {
        return Pergunta::create([
            'evento_id' => $evento->id,
            'texto'     => $texto,
            'status'    => 'pendente',
        ]);
    }

    // Ticket #009
    public function test_tabela_pivot_existe_com_colunas_corretas(): void
    {
        $this->assertTrue(Schema::hasTable('pergunta_user'));
        $this->assertTrue(Schema::hasColumns('pergunta_user', [
            'pergunta_id', 'user_id', 'created_at', 'updated_at',
        ]));
    }

    public function test_chave_unica_impede_voto_duplicado(): void
    {
        $user = User::factory()->create();
        $pergunta = $this->criarPergunta($this->criarEvento());

        DB::table('pergunta_user')->insert(['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('pergunta_user')->insert(['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);
    }

    // Ticket #010
    public function test_pergunta_tem_relacionamento_votos_belongs_to_many(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new Pergunta)->votos());
        $this->assertSame('pergunta_user', (new Pergunta)->votos()->getTable());
    }

    public function test_user_tem_relacionamento_perguntas_votadas_belongs_to_many(): void
    {
        $this->assertInstanceOf(BelongsToMany::class, (new User)->perguntasVotadas());
        $this->assertSame('pergunta_user', (new User)->perguntasVotadas()->getTable());
    }

    // Ticket #011
    public function test_usuario_anonimo_nao_pode_votar(): void
    {
        $pergunta = $this->criarPergunta($this->criarEvento());

        $response = $this->post(route('perguntas.votar', $pergunta));

        $this->assertTrue(
            $response->isRedirect() || in_array($response->status(), [401, 403]),
            'Usuário anônimo deveria ser redirecionado ou bloqueado (401/403).'
        );
        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_votar_insere_registro_na_pivot(): void
    {
        $user = User::factory()->create();
        $pergunta = $this->criarPergunta($this->criarEvento());

        $this->actingAs($user)
            ->from(route('eventos.show', $pergunta->evento_id))
            ->post(route('perguntas.votar', $pergunta))
            ->assertRedirect(route('eventos.show', $pergunta->evento_id))
            ->assertSessionHas('sucesso');

        $this->assertDatabaseHas('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id'     => $user->id,
        ]);
        $this->assertTrue($user->perguntasVotadas->contains($pergunta));
    }

    public function test_votar_novamente_remove_o_voto_toggle(): void
    {
        $user = User::factory()->create();
        $pergunta = $this->criarPergunta($this->criarEvento());

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));
        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));

        $this->assertDatabaseMissing('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id'     => $user->id,
        ]);
    }

    // Ticket #012
    public function test_perguntas_sao_ordenadas_por_votos_decrescente(): void
    {
        $evento = $this->criarEvento();
        $poucoVotada = $this->criarPergunta($evento, 'Pergunta com 1 voto');
        $maisVotada  = $this->criarPergunta($evento, 'Pergunta com 3 votos');
        $semVoto     = $this->criarPergunta($evento, 'Pergunta sem votos');

        $users = User::factory()->count(3)->create();
        $maisVotada->votos()->attach($users->pluck('id'));
        $poucoVotada->votos()->attach($users->first()->id);

        $response = $this->get(route('eventos.show', $evento->id))->assertOk();

        $perguntas = $response->viewData('perguntas');
        $this->assertSame(
            [$maisVotada->id, $poucoVotada->id, $semVoto->id],
            collect($perguntas->items())->pluck('id')->all()
        );
        $this->assertSame([3, 1, 0], collect($perguntas->items())->pluck('votos_count')->all());

        $response->assertSeeInOrder([
            'Pergunta com 3 votos',
            'Pergunta com 1 voto',
            'Pergunta sem votos',
        ]);
    }

    public function test_botao_de_voto_aparece_destacado_quando_usuario_ja_votou(): void
    {
        $user = User::factory()->create();
        $evento = $this->criarEvento();
        $pergunta = $this->criarPergunta($evento);

        $this->actingAs($user)->get(route('eventos.show', $evento->id))
            ->assertSee(route('perguntas.votar', $pergunta), false)
            ->assertDontSee('btn-upvote votado', false);

        $pergunta->votos()->attach($user->id);

        $this->actingAs($user)->get(route('eventos.show', $evento->id))
            ->assertSee('btn-upvote votado', false)
            ->assertSee('Votado');
    }
}

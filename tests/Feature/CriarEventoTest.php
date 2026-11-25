<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriarEventoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_formulario_de_criacao_existe(): void
    {
        $this->get(route('eventos.create'))
            ->assertOk()
            ->assertSee('name="titulo"', false)
            ->assertSee('name="descricao"', false)
            ->assertSee('bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700', false);
    }

    public function test_formulario_em_branco_exibe_erros_e_bordas_vermelhas(): void
    {
        $this->from(route('eventos.create'))
            ->post(route('eventos.store'), ['titulo' => '', 'descricao' => ''])
            ->assertRedirect(route('eventos.create'))
            ->assertSessionHasErrors(['titulo', 'descricao']);

        $html = $this->get(route('eventos.create'))->getContent();
        $this->assertStringContainsString('O título do evento é obrigatório.', $html);
        $this->assertStringContainsString('A descrição do evento é obrigatória.', $html);
        $this->assertSame(2, substr_count($html, 'border-red-500'));
        $this->assertSame(2, substr_count($html, 'text-red-500'));
    }

    public function test_old_mantem_texto_digitado_apos_falha(): void
    {
        $this->from(route('eventos.create'))
            ->post(route('eventos.store'), ['titulo' => 'Meu Evento', 'descricao' => 'curta']);

        $this->get(route('eventos.create'))
            ->assertSee('value="Meu Evento"', false)
            ->assertSee('>curta</textarea>', false);
    }

    public function test_sem_erros_as_bordas_sao_cinzas(): void
    {
        $html = $this->get(route('eventos.create'))->getContent();
        $this->assertStringNotContainsString('border-red-500', $html);
        $this->assertStringContainsString('border-gray-300', $html);
    }

    public function test_evento_valido_e_salvo(): void
    {
        $this->post(route('eventos.store'), [
            'titulo' => 'Laravel Conf',
            'descricao' => 'Um evento sobre Laravel e Tailwind.',
        ])->assertRedirect()->assertSessionHas('sucesso');

        $this->assertDatabaseHas('eventos', ['titulo' => 'Laravel Conf']);
    }
}

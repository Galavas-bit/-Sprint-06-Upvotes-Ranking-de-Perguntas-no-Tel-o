<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Evento — AskLive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 py-12 px-4 font-sans">

    {{-- TICKET #007: contêiner centralizado --}}
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">

        <div class="mb-6">
            <a href="{{ route('eventos.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar para Eventos</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-800">📅 Criar Novo Evento</h1>
            <p class="text-sm text-gray-500">Preencha os dados abaixo para cadastrar um evento.</p>
        </div>

        <form action="{{ route('eventos.store') }}" method="POST" novalidate class="space-y-5">
            @csrf

            {{-- Campo: título --}}
            <div>
                <label for="titulo" class="block mb-1 text-sm font-medium text-gray-700">Título</label>
                <input
                    type="text"
                    name="titulo"
                    id="titulo"
                    value="{{ old('titulo') }}"
                    placeholder="Ex: Palestra sobre Laravel 12"
                    class="w-full rounded-md border px-4 py-2 text-gray-800 focus:outline-none focus:ring-2
                        @error('titulo') border-red-500 focus:ring-red-300 @else border-gray-300 focus:ring-blue-300 @enderror"
                >
                @error('titulo')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Campo: descrição --}}
            <div>
                <label for="descricao" class="block mb-1 text-sm font-medium text-gray-700">Descrição</label>
                <textarea
                    name="descricao"
                    id="descricao"
                    rows="5"
                    placeholder="Descreva o evento..."
                    class="w-full rounded-md border px-4 py-2 text-gray-800 focus:outline-none focus:ring-2
                        @error('descricao') border-red-500 focus:ring-red-300 @else border-gray-300 focus:ring-blue-300 @enderror"
                >{{ old('descricao') }}</textarea>
                @error('descricao')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- TICKET #007: botão estilizado --}}
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Criar Evento
                </button>
            </div>
        </form>
    </div>

</body>
</html>

<x-admin-layout title="Modalità Demo">
    <div class="min-h-[60vh] flex flex-col items-center justify-center text-center px-6 py-20">
        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-8 max-w-lg w-full border border-gray-200 dark:border-gray-700">
            <div class="text-5xl mb-4">🔒</div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                Modalità Demo Attiva
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                {{ $message ?? 'Non puoi modificare i dati in modalità demo.' }}
            </p>
            <a href="{{ url()->previous() }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded transition">
                ⬅ Torna Indietro
            </a>
            <a href="{{ route('admin.dashboard') }}"
               class="ml-2 inline-block bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium px-4 py-2 rounded transition">
                Vai alla Dashboard
            </a>
        </div>
    </div>
</x-admin-layout>
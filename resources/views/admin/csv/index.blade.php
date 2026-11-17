<x-app-layout>
  {{-- Page header --}}
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">CSV Import</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        upload a CSV File and select the <span class="font-medium">entity</span> of destination. Support for the header autodetect.
      </p>
    </div>
  </div>

  {{-- Card --}}
  <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/70">
    <form action="{{ route('admin.csv.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf

      {{-- Entity --}}
      <div class="grid gap-2">
        <label for="entity" class="text-sm font-medium text-gray-800 dark:text-gray-200">Entity</label>
        <div class="relative">
          <select id="entity" name="entity"
                  class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 pr-9 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            @foreach($entities as $e)
              <option value="{{ $e }}">{{ ucfirst($e) }}</option>
            @endforeach
          </select>
          {{-- chevron --}}
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
            </svg>
          </span>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Select the entity where the column will be mapped.</p>
      </div>

      {{-- File uploader (drag & drop) --}}
      <div class="grid gap-2">
        <label for="file" class="text-sm font-medium text-gray-800 dark:text-gray-200">CSV file</label>

        <div x-data="{
               dragging:false,
               onDrop(e){ this.dragging=false; const f=e.dataTransfer.files?.[0]; if(f) this.$refs.input.files = e.dataTransfer.files; }
             }"
             @dragover.prevent="dragging=true"
             @dragleave.prevent="dragging=false"
             @drop.prevent="onDrop($event)"
             class="group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 text-center transition
                    border-gray-300 hover:border-indigo-400 dark:border-gray-700 dark:hover:border-indigo-500"
             :class="dragging ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-500/10' : ''">
          <svg class="mb-2 h-7 w-7 text-gray-400 group-hover:text-indigo-500 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M7 18a2 2 0 01-2-2V8a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1.5M15 13l-3 3m0 0l-3-3m3 3V10" />
          </svg>
          <p class="text-sm text-gray-700 dark:text-gray-200">
            Drag and drop here the CSV or <span class="font-medium text-indigo-600 underline underline-offset-2 dark:text-indigo-400">select it</span>
          </p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: .csv — Suggested UTF-8, with separator “,”</p>
          <input x-ref="input" id="file" name="file" type="file" accept=".csv,text/csv" required
                 class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="Select CSV file">
        </div>

        @error('file')
          <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Data will be not imported yer: they will be imported on the next step with column map.
        </p>
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-indigo-500 dark:hover:bg-indigo-400">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5M5 19h14" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Upload
        </button>
      </div>
    </form>
  </div>
</x-app-layout>
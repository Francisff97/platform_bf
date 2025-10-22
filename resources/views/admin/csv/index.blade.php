<x-app-layout>
  <h1 class="text-2xl font-bold mb-4">CSV Import</h1>

  <form action="{{ route('admin.csv.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium mb-1">Entity</label>
      <select name="entity" class="border rounded p-2">
        @foreach($entities as $e)
          <option value="{{ $e }}">{{ ucfirst($e) }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">CSV file</label>
      <input type="file" name="file" accept=".csv,text/csv" class="border rounded p-2" required>
    </div>

    <button class="px-4 py-2 bg-indigo-600 text-white rounded">Upload</button>
  </form>
</x-app-layout>
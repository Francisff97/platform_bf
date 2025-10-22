<x-app-layout>
  <h1 class="text-2xl font-bold mb-4">Map Columns: {{ ucfirst($entity) }}</h1>

  <form action="{{ route('admin.csv.import') }}" method="POST" class="space-y-6">
    @csrf
    <input type="hidden" name="entity" value="{{ $entity }}">
    <input type="hidden" name="file" value="{{ $file }}">

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 text-left">CSV Header</th>
            <th class="p-2 text-left">Map to Field</th>
            <th class="p-2 text-left">Example</th>
          </tr>
        </thead>
        <tbody>
          @foreach($headers as $h)
            <tr class="border-b">
              <td class="p-2 font-mono">{{ $h }}</td>
              <td class="p-2">
                <select name="mapping[{{ $h }}]" class="border rounded p-1">
                  <option value="">— Non mappare —</option>
                  @foreach($fields as $field => $meta)
                    <option value="{{ $field }}" @selected(($mapping[$h]??'') === $field)>
                      {{ $field }} — {{ $meta['label'] ?? $field }}
                    </option>
                  @endforeach
                </select>
              </td>
              <td class="p-2 text-gray-600">
                {{ $sample[0][$h] ?? '—' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="flex items-center gap-3">
      <label class="text-sm">Run mode:</label>
      <label><input type="radio" name="mode" value="queue" checked> Queue</label>
      <label><input type="radio" name="mode" value="sync"> Sync (piccoli file)</label>
    </div>

    <button class="px-4 py-2 bg-indigo-600 text-white rounded">Start Import</button>
  </form>

  <div class="mt-8">
    <h2 class="font-semibold mb-2">Sample rows</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead>
          <tr>
            @foreach($headers as $h)
              <th class="p-1 text-left">{{ $h }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($sample as $row)
            <tr class="border-b">
              @foreach($headers as $h)
                <td class="p-1">{{ $row[$h] ?? '' }}</td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
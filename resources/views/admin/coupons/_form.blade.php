@php $cur = optional(\App\Models\SiteSetting::first())->currency ?? 'EUR'; @endphp
<div class="grid gap-4 sm:grid-cols-2">
  <div>
    <label class="block text-sm mb-1">Code</label>
    <input name="code" value="{{ old('code',$coupon->code) }}" class="w-full rounded border px-3 py-2 dark:bg-gray-900" required>
    <p class="mt-1 text-xs text-gray-500">Uppercase recommended, e.g. BASEFORGE</p>
  </div>
  <div>
    <label class="block text-sm mb-1">Type</label>
    @php $type = old('type',$coupon->type ?? 'percent'); @endphp
    <select name="type" id="typeSelect" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
      <option value="percent" @selected($type==='percent')>Percent (%)</option>
      <option value="fixed"   @selected($type==='fixed')>Fixed ({{ $cur }})</option>
    </select>
  </div>

  <div id="percentField" class="{{ $type==='percent' ? '' : 'hidden' }}">
    <label class="block text-sm mb-1">Percent value</label>
    <input type="number" min="1" max="100" name="value" value="{{ old('value',$coupon->value) }}" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
  </div>

  <div id="fixedField" class="{{ $type==='fixed' ? '' : 'hidden' }}">
    <label class="block text-sm mb-1">Fixed amount ({{ $cur }})</label>
    <input type="number" min="0.01" step="0.01" name="value_amount"
           value="{{ old('value_amount', $coupon->value_cents ? number_format($coupon->value_cents/100,2,'.','') : null) }}"
           class="w-full rounded border px-3 py-2 dark:bg-gray-900">
  </div>

  <div>
    <label class="block text-sm mb-1">Min. order ({{ $cur }})</label>
    <input type="number" min="0" step="0.01" name="min_order_amount"
           value="{{ old('min_order_amount', number_format(($coupon->min_order_cents ?? 0)/100,2,'.','')) }}"
           class="w-full rounded border px-3 py-2 dark:bg-gray-900">
  </div>
  <div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active',$coupon->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
    <span class="text-sm">Active</span>
  </div>

  <div>
    <label class="block text-sm mb-1">Starts at</label>
    <input type="datetime-local" name="starts_at"
           value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}"
           class="w-full rounded border px-3 py-2 dark:bg-gray-900">
  </div>
  <div>
    <label class="block text-sm mb-1">Ends at</label>
    <input type="datetime-local" name="ends_at"
           value="{{ old('ends_at', optional($coupon->ends_at)->format('Y-m-d\TH:i')) }}"
           class="w-full rounded border px-3 py-2 dark:bg-gray-900">
  </div>

  <div>
    <label class="block text-sm mb-1">Max uses (optional)</label>
    <input type="number" min="1" name="max_uses" value="{{ old('max_uses',$coupon->max_uses) }}" class="w-full rounded border px-3 py-2 dark:bg-gray-900">
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const type = document.getElementById('typeSelect');
    const pf = document.getElementById('percentField');
    const ff = document.getElementById('fixedField');
    function refresh(){
      if (type.value === 'percent'){ pf.classList.remove('hidden'); ff.classList.add('hidden'); }
      else { ff.classList.remove('hidden'); pf.classList.add('hidden'); }
    }
    type.addEventListener('change', refresh);
    refresh();
  });
</script>
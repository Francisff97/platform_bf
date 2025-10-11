{{-- Prices repeater (CREATE) --}}
{{-- 1) Definisci la funzione PRIMA di usarla --}}
<script>
  (function () {
    if (window.pricesRepeater) return; // evita ridefinizioni tra create/edit
    window.pricesRepeater = function(initial, defCurrency){
      const norm = (x)=>({
        duration:    x && x.duration     ? x.duration     : '',
        price_cents: x && x.price_cents  ? x.price_cents  : '',
        currency:    x && x.currency     ? x.currency     : defCurrency
      });
      const rows = Array.isArray(initial) ? initial.map(norm) : [];
      if (rows.length === 0) rows.push(norm({}));
      return {
        rows,
        add(){ this.rows.push(norm({})) }
      }
    }
  })();
</script>

{{-- 2) Ora usa la funzione in x-data --}}
<div x-data="pricesRepeater(@json(old('prices', [])), @json(\App\Support\Currency::site()['code']))" class="mt-2">
  <div class="mb-1 text-sm font-medium text-gray-800 dark:text-gray-200">Prices</div>

  <template x-for="(row, i) in rows" :key="i">
    <div class="mb-2 grid grid-cols-12 gap-2">
      <input class="col-span-6 h-11 rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-sm dark:bg-black/80 dark:text-white"
             type="text" :name="`prices[${i}][duration]`" x-model="row.duration" placeholder="Duration (ex: 30 mins)">
      <input class="col-span-4 h-11 rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-sm dark:bg-black/80 dark:text-white"
             type="number" :name="`prices[${i}][price_cents]`" x-model.number="row.price_cents" placeholder="Price in cents" min="0" step="1">
      <input class="col-span-2 h-11 rounded-xl border border-[color:var(--accent)] bg-white/90 px-3 text-sm uppercase dark:bg-black/80 dark:text-white"
             type="text" :name="`prices[${i}][currency]`" x-model="row.currency">
    </div>
  </template>

  <div class="flex gap-2">
    <button type="button" @click="add()"
            class="rounded-xl bg-[var(--accent)] px-3 py-2 text-sm text-white hover:opacity-90">+ Add price</button>
    <button type="button" @click="rows=[]; add()"
            class="rounded-xl border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Clear</button>
  </div>
</div>
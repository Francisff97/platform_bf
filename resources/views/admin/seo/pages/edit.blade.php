{{-- resources/views/admin/seo/pages/edit.blade.php --}}
<x-admin-layout title="Edit SEO Page">
  <form method="POST"
        action="{{ route('admin.seo.pages.update',$seoPage) }}"
        enctype="multipart/form-data"
        x-data="seoEditor({
          title: @js($seoPage->meta_title ?? ''),
          desc:  @js($seoPage->meta_description ?? ''),
          og:    @js($seoPage->og_image_path ?? ''),
          // opzionale: se il controller passa un contesto reale lo usiamo,
          // altrimenti fallback dimostrativo
          ctx:   @js($exampleContext ?? [
                    'name'         => 'Sample name',
                    'slug'         => 'sample-slug',
                    'excerpt'      => 'Short teaser about the item.',
                    'description'  => 'Longer description of the current item shown on this page.',
                    'image_url'    => 'https://via.placeholder.com/1200x630.png?text=OG+Preview',
                    'price_eur'    => '19,99 EUR',
                    'builder_name' => 'Sample Builder',
                  ]),
        })"
        class="mx-auto grid w-full max-w-3xl gap-5 rounded-2xl border border-[color:var(--accent)]/30
               bg-white/70 p-6 shadow-sm backdrop-blur
               dark:border-[color:var(--accent)]/30 dark:bg-gray-900/70">

    @csrf @method('PUT')

    {{-- Tips box (ENG) --}}
    <div class="rounded-xl border border-[color:var(--accent)]/30 bg-blue-50/70 p-4 text-sm text-gray-800 dark:bg-blue-900/40 dark:text-gray-100">
      <strong class="font-semibold">💡 SEO Tips</strong><br>
      You can use <code>{variables}</code> inside <em>Meta Title</em>, <em>Description</em> and <em>OG Image</em>.<br>
      <span class="text-xs text-gray-600 dark:text-gray-400">
        Example: <code>{name} – {builder_name} | Base Forge</code> will be replaced with the pack/coach/builder values on show pages.
      </span>
      @if(\Illuminate\Support\Str::endsWith($seoPage->route_name ?? '', '.show'))
        <br><br>
        <strong>Dynamic variables for <code>{{ $seoPage->route_name }}</code>:</strong>
        <ul class="mt-1 list-disc pl-6 text-xs leading-relaxed text-gray-700 dark:text-gray-300">
          <li><code>{name}</code>, <code>{slug}</code>, <code>{excerpt}</code>, <code>{description}</code></li>
          <li><code>{image_url}</code>, <code>{price_eur}</code>, <code>{builder_name}</code></li>
        </ul>
      @endif
    </div>

    {{-- Route + Path --}}
    <div class="grid gap-4 md:grid-cols-2">
      <label class="block">
        <div class="mb-1 text-sm font-medium">Route name</div>
        <select name="route_name"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800"
                @change="onAnyChange()">
          <option value="">—</option>
          @foreach($publicRoutes as $r)
            <option value="{{ $r }}" @selected($seoPage->route_name===$r)>{{ $r }}</option>
          @endforeach
        </select>
      </label>

      <label class="block">
        <div class="mb-1 text-sm font-medium">Path</div>
        <input name="path" value="{{ $seoPage->path }}"
               class="h-11 w-full rounded-xl border border-[color:var(--accent)]/40 px-3
                      focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800"
               @input="onAnyChange()" />
      </label>
    </div>

    {{-- Meta title --}}
    <label class="block">
      <div class="mb-1 flex items-center justify-between text-sm">
        <span class="font-medium">Meta title</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          <span x-text="title.length"></span>/60
        </span>
      </div>
      <input name="meta_title" x-model="title"
             class="h-11 w-full rounded-xl border border-[color:var(--accent)]/40 px-3
                    focus:ring-2 focus:ring-[color:var(--accent)]
                    dark:bg-black/70 dark:text-white dark:border-gray-800"
             @input="onAnyChange()" />
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommendation: ≤ 60 characters.</p>
    </label>

    {{-- Meta description --}}
    <label class="block">
      <div class="mb-1 flex items-center justify-between text-sm">
        <span class="font-medium">Meta description</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          <span x-text="desc.length"></span>/160
        </span>
      </div>
      <textarea name="meta_description" x-model="desc" rows="4"
                class="w-full rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                       focus:ring-2 focus:ring-[color:var(--accent)]
                       dark:bg-black/70 dark:text-white dark:border-gray-800"
                @input="onAnyChange()"></textarea>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommendation: 150–160 characters.</p>
    </label>

    {{-- OpenGraph image --}}
    <div class="grid gap-4 md:grid-cols-[auto,1fr] md:items-start">
      <div class="flex flex-col items-start gap-2">
        <div class="text-sm font-medium">OpenGraph image</div>

        {{-- Preview attuale / nuova / risolta --}}
        <div class="relative h-24 w-40 overflow-hidden rounded-xl ring-1 ring-black/5 dark:ring-white/10">
          {{-- preview “risolta” dalle variabili (ha priorità) --}}
          <img :src="resolvedOg()" alt="OG preview"
               class="h-full w-full object-cover" x-show="resolvedOg()">
          {{-- se non c’è risolta, mostra l’upload live --}}
          <img :src="ogPreview" alt="" class="h-full w-full object-cover" x-show="!resolvedOg() && ogPreview">
          {{-- se non c’è upload, mostra quella salvata (se esiste) --}}
          @if($seoPage->og_image_path)
            <img src="{{ Storage::url($seoPage->og_image_path) }}"
                 alt="Current OG" class="h-full w-full object-cover" x-show="!resolvedOg() && !ogPreview">
          @else
            <div class="grid h-full w-full place-items-center text-xs text-gray-400" x-show="!resolvedOg() && !ogPreview">No image</div>
          @endif
        </div>
      </div>

      <label class="block">
        <div class="sr-only">Upload</div>
        <input type="file" name="og_image" accept="image/*"
               @change="onUpload($event)"
               class="w-full cursor-pointer rounded-xl border border-[color:var(--accent)]/40 px-3 py-2
                      file:mr-3 file:rounded-lg file:border-0 file:bg-[color:var(--accent)] file:px-3 file:py-2 file:text-white
                      dark:bg-black/70 dark:text-white dark:border-gray-800" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Suggested 1200×630 (JPG/PNG/WebP). You can also set a dynamic path with variables, e.g. <code>{image_url}</code>.</p>

        {{-- campo reale dove scrivere l’OG path (con variabili) --}}
        <input name="og_image_path" x-model="og"
               placeholder="Static path or {image_url}"
               class="mt-2 h-11 w-full rounded-xl border border-[color:var(--accent)]/40 px-3 text-sm
                      focus:ring-2 focus:ring-[color:var(--accent)]
                      dark:bg-black/70 dark:text-white dark:border-gray-800"
               @input="onAnyChange()">
      </label>
    </div>

    {{-- LIVE PREVIEW META (risolti) --}}
    <div class="rounded-xl border border-[color:var(--accent)]/30 bg-gray-50/70 p-4 text-sm dark:bg-gray-800/40">
      <div class="mb-1 font-semibold">Live preview (resolved)</div>
      <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
        Variables are replaced using the sample context (or one provided by the controller).
      </div>
      <div class="space-y-1">
        <div><span class="font-medium">Title:</span> <span x-text="resolve(title)"></span></div>
        <div><span class="font-medium">Description:</span> <span x-text="resolve(desc)"></span></div>
        <div class="flex items-center gap-2">
          <span class="font-medium">OG image:</span>
          <a :href="resolvedOg()" x-text="resolvedOg()" target="_blank" class="truncate text-indigo-600 underline dark:text-indigo-300"></a>
        </div>
      </div>
    </div>

    <div class="mt-2 flex items-center gap-3">
      <button class="inline-flex items-center justify-center rounded-xl bg-[color:var(--accent)] px-5 py-2.5 text-white transition
                     hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
        Update
      </button>
      <a href="{{ route('admin.seo.pages.index') }}" class="text-sm text-gray-600 underline dark:text-gray-300">Cancel</a>
    </div>
  </form>

  {{-- Alpine helpers --}}
  <script>
    function seoEditor({title, desc, og, ctx}) {
      return {
        title, desc, og,
        ctx,
        ogPreview: null,

        // sostituzione semplice {key} -> ctx[key]
        resolve(tpl) {
          if (!tpl) return '';
          return tpl.replace(/\{([\w\.]+)\}/g, (_m, key) => {
            const val = this.ctx?.[key];
            return (val === undefined || val === null) ? '' : String(val);
          });
        },

        // OG “risolto”: se og contiene variabili, usiamo quello; altrimenti null (mostreremo upload/attuale)
        resolvedOg() {
          const hasVar = /\{[\w\.]+\}/.test(this.og || '');
          if (!this.og) return null;
          const url = hasVar ? this.resolve(this.og) : this.og;
          return url || null;
        },

        onUpload(e){
          const f = e?.target?.files?.[0];
          this.ogPreview = f ? URL.createObjectURL(f) : null;
        },

        onAnyChange(){ /* hook per futuri side-effects, mantiene reattività */ }
      }
    }
  </script>
</x-admin-layout>

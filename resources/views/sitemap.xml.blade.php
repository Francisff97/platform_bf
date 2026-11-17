{{-- resources/views/sitemap.xml.blade.php --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $u)
  <url>
    <loc>{{ $u['loc'] }}</loc>
    @if(!empty($u['lastmod'])) <lastmod>{{ $u['lastmod'] }}</lastmod> @endif
    @if(!empty($u['prio']))    <priority>{{ $u['prio'] }}</priority> @endif
  </url>
@endforeach
</urlset>

@if($ad ?? null)
<div class="my-4">
    <a
        href="{{ $ad->url }}"
        target="_blank"
        rel="noopener sponsored"
        aria-label="Sponsored advertisement"
        class="block w-full rounded-xl overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
    >
        <img src="{{ Storage::disk('public')->url($ad->image) }}" alt="{{ $ad->title }}" class="w-full h-24 object-cover">
    </a>
    <p class="text-xs text-gray-400 text-right mt-0.5">Sponsored</p>
</div>
@endif

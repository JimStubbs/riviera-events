@if($ad ?? null)
<div class="mb-6">
    <a
        href="{{ $ad->url }}"
        target="_blank"
        rel="noopener sponsored"
        aria-label="Sponsored advertisement"
        class="block rounded-xl overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
    >
        <img src="{{ Storage::disk('public')->url($ad->image) }}" alt="{{ $ad->title }}" class="w-full object-cover">
    </a>
    <p class="text-xs text-gray-400 text-right mt-0.5">Sponsored</p>
</div>
@endif

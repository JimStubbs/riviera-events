@if($ad ?? null)
<a
    href="{{ $ad->url }}"
    target="_blank"
    rel="noopener sponsored"
    aria-label="Sponsored advertisement"
    class="block bg-yellow-50 border border-yellow-200 rounded-xl p-4 hover:bg-yellow-100 transition-colors"
>
    <div class="flex items-center gap-3">
        <img src="{{ Storage::disk('public')->url($ad->image) }}" alt="{{ $ad->title }}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
        <div>
            <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide mb-0.5">Sponsored</p>
            <p class="font-semibold text-gray-900 text-sm">{{ $ad->title }}</p>
        </div>
    </div>
</a>
@endif

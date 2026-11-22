@props(['eyebrow' => null, 'title'])

<div class="flex flex-wrap items-end justify-between gap-4 border-b border-ink pb-6">
    <div>
        @if ($eyebrow)
            <p class="num text-[12px] tracking-wider text-muted uppercase">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-1 font-display text-[38px] leading-tight">{{ $title }}</h1>
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>

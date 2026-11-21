@php
$width = $width ?? 32;
$variant = $variant ?? 'dore';
@endphp

<span class="text-primary d-inline-flex align-items-center">
    @if ($siteSettings?->logo_path)
        <img src="{{ asset('storage/'.$siteSettings->logo_path) }}" alt="" style="max-width: {{ $width }}px; max-height: {{ $width }}px; object-fit: contain">
    @else
        <img src="{{ asset('assets/img/branding/svg/symbole_'.$variant.'.svg') }}" alt="" width="{{ $width }}" height="{{ $width }}">
    @endif
</span>

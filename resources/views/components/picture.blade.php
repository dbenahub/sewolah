@props(['image', 'sizes' => '100vw'])
<picture>@if(!empty($image['srcset']))<source type="image/webp" srcset="{{ $image['srcset'] }}" sizes="{{ $sizes }}">@endif<img src="{{ $image['src'] }}" {{ $attributes->merge(['decoding' => 'async']) }}></picture>

@props(['image'])
<picture>@if(!empty($image['webp']))<source type="image/webp" srcset="{{ $image['webp'] }}">@endif<img src="{{ $image['src'] }}" {{ $attributes }}></picture>

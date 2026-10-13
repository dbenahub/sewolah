{{-- Self-hosted Plus Jakarta Sans (latin, variable 400–800). No third-party
     font requests: saves ~2 network round trips on mobile. The fallback face
     is metric-matched to reduce layout shift while the web font loads. --}}
@php($fontFile = 'fonts/plus-jakarta-sans-latin.woff2')
@if(is_file(public_path($fontFile)))
<link rel="preload" href="{{ asset($fontFile) }}" as="font" type="font/woff2" crossorigin>
<style>
@font-face{font-family:"Plus Jakarta Sans";font-style:normal;font-weight:400 800;font-display:swap;src:url("{{ asset($fontFile) }}") format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}
@font-face{font-family:"Plus Jakarta Sans Fallback";src:local("Arial"),local("Helvetica Neue"),local("Roboto");ascent-override:98.88%;descent-override:21.15%;line-gap-override:0%;size-adjust:104.98%}
</style>
@else
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400..800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
@endif

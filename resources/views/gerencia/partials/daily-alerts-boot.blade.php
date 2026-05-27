@php
    $soundPath = public_path('sounds/nuevo_resguardo.mp3');
    $soundVersion = file_exists($soundPath) ? filemtime($soundPath) : time();
    $soundPrimary = asset('sounds/nuevo_resguardo.mp3') . '?v=' . $soundVersion;
    $soundFallback = asset('audio/nuevo_resguardo.mp3') . '?v=' . $soundVersion;
@endphp
<script>
    window.__dailyAlertsConfig = {
        soundUrls: @json(array_values(array_unique([$soundPrimary, $soundFallback]))),
    };
</script>
@vite(['resources/js/gerencia/daily-alerts.js'])

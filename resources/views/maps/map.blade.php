<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <script src="{{ asset('js/leaflet.polylineDecorator.js') }}"></script>
    {{-- <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script> --}}

    <!-- Leaflet Control Geocoder CSS و JS -->

    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" /> --}}

    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    {{-- @vite(['resources/css/app.css', 'public/js/map/mapJs.js'])

    {{--
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>
    --}}

    <style>
        #map {
            height: 100vh;
            /* ملء الشاشة بالكامل */
        }
    </style>
</head>

<body>
    <div id="map"></div>
    <div class="container_search">
        <img class="img" src="{{ asset('css/images/pin.png') }}" alt="">
        <input type="text" id="customSearchInput" class="search" placeholder="Search here" />
    </div>

    <div class="img">
        <button id="locateButton"><img src="{{ asset('css/images/marker-icon.png') }}" alt=""></button>
        <!-- زر لتحديد الموقع -->
    </div>
    <button id="toggleDistance" style=""></button>
    <button id="drawPathButton"><img src="{{ asset('css/images/Arrow.png') }}" alt=""></button>
    <div id="sideMenu">
        <input type="text" class="startLocation" placeholder="Choose start location">
        <div class="led"></div>
        <input type="text" class="destination" placeholder="Choose destination">
        <button id="manualSearchBtn">🔍 بحث يدوي</button>
        <p class="pinLocation">Pin Location :</p>
        <ul id="coordinates"></ul>
    </div>
    <script src="{{ asset('js/map/mapJs.js') }}"></script>

</body>

</html>
{{-- map file --}}
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>خريطة تتبع السائقين</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 90vh;
            width: 100%;
        }
    </style>
</head>

<body>
    <h2>الخريطة الحية</h2>
    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        const params = new URLSearchParams(window.location.search);
        const bus_id = params.get('bus_id'); // يمكن أن يكون null

        const map = L.map('map').setView([33.5, 36.3], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let markers = {};

        function fetchLocations() {
            const url = bus_id
                ? `/api/update-location/${bus_id}`
                : `/api/all-buses`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (bus_id) {
                        updateMarker(data.id, data.latitude, data.longitude, data.speed, data.status);
                    } else {
                        data.forEach(bus => {
                            updateMarker(bus.id, bus.latitude, bus.longitude, bus.speed, bus.status);
                        });
                    }
                });
        }

        function updateMarker(id, lat, lng, speed = 0, status = 'inactive') {
            if (!lat || !lng) return;

            const popupContent = `
        <b>🚍 الباص رقم ${id}</b><br>
        🟢 الحالة: ${status}<br>
        ⚡ السرعة: ${parseFloat(speed || 0).toFixed(2)} م/ث
    `;

            if (!markers[id]) {
                markers[id] = L.marker([lat, lng]).addTo(map).bindPopup(popupContent);
            } else {
                markers[id].setLatLng([lat, lng]);
                markers[id].setPopupContent(popupContent);
            }
        }

        setInterval(fetchLocations, 2000);
    </script>
</body>

</html>
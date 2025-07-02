{{-- driver file --}}
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>تتبع السائق</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <h2>يتم إرسال الموقع الآن...</h2>

    <script>
        const params = new URLSearchParams(window.location.search);
        const bus_id = params.get('bus_id') || 1; // رقم الباص (سائق)

        function sendLocation(lat, lng, speed = 0) {
            fetch("/api/update-location", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    bus_id: parseInt(bus_id),
                    latitude: lat,
                    longitude: lng,
                    speed: speed,
                    status: "active"
                })
            });
        }

        function updateLocation() {
            if (!navigator.geolocation) {
                alert("المتصفح لا يدعم تحديد الموقع");
                return;
            }

            navigator.geolocation.watchPosition(
                position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const speed_m_s = typeof position.coords.speed === 'number' ? position.coords.speed : 0;
                    const speed_kmh = speed_m_s * 3.6; // التحويل من م/ث إلى كم/س

                    sendLocation(lat, lng, speed_m_s);
                },
                error => {
                    alert("تعذر تحديد الموقع: " + error.message);
                }
            );
        }

        setInterval(updateLocation, 2000);
    </script>
</body>

</html>
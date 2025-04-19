<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Animation on Routes</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>
    <style>
        #map {
            height: 100vh; /* الخريطة تملأ الشاشة بالكامل */
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <script>
        // 1. إعداد الخريطة
        const map = L.map('map').setView([33.5258, 36.2742], 14); // ضبط الإحداثيات الأولية ومستوى التكبير

        // 2. إضافة طبقة البلاط (الخريطة الأساسية)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 3. تحميل ملف GeoJSON
        fetch('routes.geojson')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(geojson => {
                console.log("Loaded GeoJSON:", geojson);

                // 4. قراءة كل مسار من ملف GeoJSON
                geojson.features.forEach(feature => {
                    if (feature.geometry.type !== "LineString") {
                        console.error("Unsupported geometry type:", feature.geometry.type);
                        return;
                    }

                    // 5. تحويل الإحداثيات إلى LatLng (عكس [lng, lat] إلى [lat, lng])
                    const latLngs = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);

                    // 6. إضافة المسار كخط على الخريطة
                    const route = L.polyline(latLngs, {
                        color: 'blue',  // لون المسار
                        weight: 4,      // سماكة الخط
                        opacity: 0.5    // شفافية
                    }).addTo(map);

                    // 7. إضافة الحركة باستخدام PolylineDecorator
                    const decorator = L.polylineDecorator(route, {
                        patterns: [
                            {
                                offset: '100%', // نقطة البداية
                                repeat: 0,      // بدون تكرار
                                symbol: L.Symbol.arrowHead({
                                    pixelSize: 15,  // حجم السهم
                                    polygon: false, // شكل السهم (خط فقط)
                                    pathOptions: { fillOpacity: 1, weight: 0 }
                                })
                            }
                        ]
                    }).addTo(map);

                    // 8. تحريك الأسهم (حركة مرورية)
                    let offset = 0;
                    setInterval(() => {
                        offset = (offset + 2) % 100; // تغيير الإزاحة (سرعة الحركة)
                        decorator.setPatterns([
                            {
                                offset: `${offset}%`,
                                repeat: 0,
                                symbol: L.Symbol.arrowHead({
                                    pixelSize: 15,
                                    polygon: false,
                                    pathOptions: { fillOpacity: 1, weight: 0 }
                                })
                            }
                        ]);
                    }, 100); // تحديث الحركة كل 100 مللي ثانية
                });
            })
            .catch(error => {
                console.error("Error loading GeoJSON:", error);
            });
    </script>
</body>
</html>

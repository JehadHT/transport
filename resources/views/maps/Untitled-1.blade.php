<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-polylinedecorator/1.8.0/leaflet.polylineDecorator.min.js"></script>

    <style>
        #map {
            height: 100vh;
            /* ملء الشاشة بالكامل */
        }
    </style>
</head>

<body>
    <div id="map"></div>
    <div class="close-icon" id="closeIcon">X</div>
    <script>
        // إعداد الخريطة
        var map = L.map('map').setView([33.5328095926933, 36.314363479614265], 15); // ضبط الإحداثيات (Latitude, Longitude) والمستوى الافتراضي (zoom)
        map.attributionControl.addAttribution('© By Jehad_HT');

        // إضافة طبقة البلاط
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        fetch('/api/routes')
            .then(response => response.json())
            .then(data => {
                let routeLayers = []; // مصفوفة لتخزين طبقات المسارات
                let decorators = []; // مصفوفة لتخزين محركات الحركة

                // إنشاء طبقات المسارات بناءً على البيانات
                console.log(data);

                data.forEach(route => {
                    // تحويل حقل coordinates إلى صيغة GeoJSON صحيحة
                    let geoJson = {
                        type: "Feature",
                        geometry: {
                            type: route.type, // عادة يكون "LineString"
                            coordinates: route.coordinates // الإحداثيات من قاعدة البيانات
                        }
                    };
                    console.log(geoJson);

                    // إنشاء طبقة GeoJSON
                    let geoLayer = L.geoJSON(geoJson, {
                        style: {
                            color: 'blue', // لون الخط
                            weight: 4,     // سماكة الخط
                            opacity: 0.5   // شفافية الخط
                        }
                    }); // أضف الطبقة للخريطة

                    // استخراج النقاط (LatLngs) من الطبقة
                    let latLngs = geoJson.geometry.coordinates.map(coord => [coord[1], coord[0]]); // تحويل [lng, lat] إلى [lat, lng]

                    // إضافة الحركة باستخدام PolylineDecorator
                    let decorator = L.polylineDecorator(latLngs, {
                        patterns: [
                            {
                                offset: '100%', // نقطة البداية
                                repeat: 0,      // لا تكرار
                                symbol: L.Symbol.arrowHead({
                                    pixelSize: 15,   // حجم السهم
                                    polygon: false, // السهم كخط فقط
                                    pathOptions: { fillOpacity: 1, weight: 0 }
                                })
                            }
                        ]
                    }).addTo(map);

                    // حفظ الطبقة والديكور في المصفوفات
                    routeLayers.push(geoLayer);
                    decorators.push(decorator);

                    // تحريك السهم
                    let offset = 0;
                    setInterval(() => {
                        offset = (offset + 1) % 100; // تغيير الإزاحة
                        decorator.setPatterns([
                            {
                                offset: offset + '%',
                                repeat: 0,
                                symbol: L.Symbol.arrowHead({
                                    pixelSize: 15,
                                    polygon: false,
                                    pathOptions: { fillOpacity: 1, weight: 0 }
                                })
                            }
                        ]);
                    }, 100); // تحديث كل 100 مللي ثانية
                });

                // التحقق من مستوى التكبير عند تغيير التكبير
                map.on('zoomend', function () {
                    if (map.getZoom() > 13) {
                        // إذا كان مستوى التكبير أكبر من 13، إضافة الطبقات إلى الخريطة
                        routeLayers.forEach(layer => {
                            if (!map.hasLayer(layer)) {
                                layer.addTo(map);
                            }
                        });

                        // إضافة الديكور
                        decorators.forEach(decorator => {
                            if (!map.hasLayer(decorator)) {
                                decorator.addTo(map);
                            }
                        });
                    } else {
                        // إذا كان مستوى التكبير 13 أو أقل، إزالة الطبقات من الخريطة
                        routeLayers.forEach(layer => {
                            if (map.hasLayer(layer)) {
                                map.removeLayer(layer);
                            }
                        });

                        // إزالة الديكور
                        decorators.forEach(decorator => {
                            if (map.hasLayer(decorator)) {
                                map.removeLayer(decorator);
                            }
                        });
                    }
                });

                // تفعيل الحالة الأولية بناءً على التكبير الحالي
                if (map.getZoom() > 13) {
                    routeLayers.forEach(layer => {
                        layer.addTo(map);
                    });
                    decorators.forEach(decorator => {
                        decorator.addTo(map);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching routes:', error);
            });


        map.on('dblclick', function (e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;


            // إذا تجاوز عدد الدبابيس 2، إعادة تعيينها
            if (markers.length >= 2 && calculateDistance) {
                markers.forEach(marker => map.removeLayer(marker)); // إزالة الدبابيس القديمة
                markers = [];
                document.getElementById('coordinates').innerHTML = ''; // مسح القائمة
            }


            // إرسال الإحداثيات إلى الخادم لتسجيلها في قاعدة البيانات
            fetch('/api/coordinates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // حماية من هجمات CSRF
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // إنشاء دبوس جديد عند تسجيل البيانات بنجاح
                        // إضافة دبوس جديد
                        var marker = L.marker([lat, lng], { draggable: true })
                            .addTo(map)
                            .bindPopup(`Lat: ${lat}<br>Lng: ${lng}`)
                            .openPopup();
                        markers[data.id] = marker;
                    } else {
                        alert('حدث خطأ أثناء إضافة الإحداثيات!');
                    }
                })
                .catch(error => console.error('Error:', error));


            // وظيفة لحذف دبوس من الخريطة وقاعدة البيانات
            function removeMarker(id) {
                // إرسال طلب إلى الخادم لحذف الإحداثيات من قاعدة البيانات
                fetch(`/api/coordinates/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // حماية من هجمات CSRF
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // إزالة الدبوس من الخريطة ومن الكائن markers
                            map.removeLayer(markers[id]);
                            delete markers[id];
                        } else {
                            alert('حدث خطأ أثناء حذف الإحداثيات!');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // منع إزالة الدبوس أثناء التحريك
            let isDragging = false;

            marker.on('dragstart', function () {
                isDragging = true; // المستخدم بدأ في سحب الدبوس
                clearTimeout(pressTimer); // إلغاء أي مؤقت قيد العمل
            });

            marker.on('dragend', function (event) {
                isDragging = false; // انتهاء السحب
                var updatedLatLng = event.target.getLatLng();
                updateCoordinates(); // تحديث الإحداثيات
            });

            // إزالة الدبوس عند الضغط المطول
            let pressTimer;
            marker.on('mousedown', function () {
                if (!isDragging) { // فقط إذا لم يكن الدبوس في وضع السحب
                    pressTimer = setTimeout(() => {
                        map.removeLayer(marker);
                        markers = markers.filter(m => m !== marker);
                        updateCoordinates();
                    }, 1000); // وقت الضغط المطول (1 ثانية)
                }
            });

            marker.on('mouseup', function () {
                clearTimeout(pressTimer); // إلغاء المؤقت عند رفع الضغط
            });

            markers.push(marker);
            updateCoordinates();
        });

        // التعامل مع زر التبديل
        document.getElementById('toggleDistance').addEventListener('click', function () {
            calculateDistance = !calculateDistance;
            toggleDistance.innerText = calculateDistance ? "إلغاء حساب البعد" : "تفعيل حساب البعد";

            // إزالة جميع الدبابيس ومسح القائمة إذا تم إلغاء التفعيل
            if (!calculateDistance) {
                markers.forEach(marker => map.removeLayer(marker));
                markers = [];
                document.getElementById('coordinates').innerHTML = '';
            }
        });

        // تحديث الإحداثيات في القائمة
        function updateCoordinates() {
            var coordinatesList = document.getElementById('coordinates');
            coordinatesList.innerHTML = "";
            markers.forEach((marker, index) => {
                var latLng = marker.getLatLng();
                var li = document.createElement('li');
                li.textContent = `Marker ${index + 1}: Lat: ${latLng.lat.toFixed(5)}, Lng: ${latLng.lng.toFixed(5)}`;
                coordinatesList.appendChild(li);
            });

            // إذا كان هناك دبوسان، حساب البعد بينهما
            if (markers.length === 2) {
                var distance = markers[0].getLatLng().distanceTo(markers[1].getLatLng());
                var li = document.createElement('li');
                li.textContent = `المسافة بين الدبوسين: ${distance.toFixed(2)} متر`;
                coordinatesList.appendChild(li);
            }
        }

    </script>
</body>

</html>
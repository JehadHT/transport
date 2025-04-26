// إعداد الخريطة
var map = L.map('map', {
    doubleClickZoom: false
}).setView([33.581733104088, 36.407661437988], 13); // ضبط الإحداثيات (Latitude, Longitude) والمستوى الافتراضي (zoom)
map.attributionControl.addAttribution('© By Jehad_HT');

// إضافة طبقة البلاط
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

fetch('/api/stations')
    .then(response => response.json())
    .then(stations => {

        stationss = stations;
        stations.forEach(station => {
            L.marker([station.latitude, station.longitude])
                .addTo(map)
                .bindPopup(`<strong>${station.name}</strong>`);
        });
    })
    .catch(error => {
        console.error('خطأ في جلب بيانات المحطات:', error);
    });

// المتغيرات لتخزين العلامة والدائرة للمستخدم
var userMarker = null;
var userCircle = null;
//##
var markers = []; // تخزين الدبابيس
var calculateDistance = false; // وضع تفعيل حساب البعد
var coordinatesList = document.getElementById('coordinates');
var toggleDistance = document.getElementById('toggleDistance');
// ################################################

// زر تحديد الموقع الحالي
var locateButton = document.getElementById('locateButton');

// إضافة حدث الضغط على الزر
locateButton.addEventListener('click', function () {
    if (navigator.geolocation) {
        // طلب تتبع موقع المستخدم باستخدام Geolocation API
        navigator.geolocation.watchPosition(
            function (position) {
                var lat = position.coords.latitude; // خط العرض
                var lng = position.coords.longitude; // خط الطول
                var accuracy = position.coords.accuracy; // دقة التحديد

                // إذا كانت العلامة والدائرة موجودة، قم بتحديث مواقعهما بدلاً من إعادة إنشائهما
                if (userMarker && userCircle) {
                    userMarker.setLatLng([lat, lng]); // تحديث موقع العلامة
                    userCircle.setLatLng([lat, lng]); // تحديث موقع الدائرة
                    userCircle.setRadius(accuracy); // تحديث نصف قطر الدائرة
                } else {
                    // إذا لم تكن العلامة والدائرة موجودتين، قم بإنشائهما
                    userMarker = L.marker([lat, lng]).addTo(map)
                        .bindPopup("أنت هنا").openPopup(); // إضافة رسالة عند الموقع
                    userCircle = L.circle([lat, lng], {
                        radius: accuracy, // نصف القطر بناءً على دقة الموقع
                        color: 'blue', // لون حدود الدائرة
                        fillColor: 'blue', // لون التعبئة
                        fillOpacity: 0.1, // الشفافية
                        weight: 1 // سمك الحدود
                    }).addTo(map);
                }

                // تحريك الخريطة لتتبع المستخدم
                map.setView([lat, lng], 13);
            },
            function (error) {
                // التعامل مع الأخطاء مثل رفض المستخدم أو انعدام الاتصال
                alert("لم نتمكن من تحديد موقعك. الرجاء التحقق من إعدادات الموقع.");
            },
            {
                enableHighAccuracy: true // طلب أعلى دقة ممكنة
            }
        );
    } else {
        // إذا كان المتصفح لا يدعم Geolocation API
        alert("المتصفح الخاص بك لا يدعم تحديد الموقع الجغرافي.");
    }
});

fetch('/api/routes')
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })

    .then(geojson => {
        console.log('GeoJSON Data:', geojson); // التحقق من صحة البيانات

        let routeLayers = [];
        let decorators = []; // مصفوفة لتخزين محركات الحركة

        const getColor = (name) => {
            switch (name) {
                case 'doma': return 'red';
                case 'salameh': return 'green';
                case 'north': return 'black';
                case 'senaha': return 'purple';
                default: return 'gray'; // اللون الافتراضي
            }
        };
        geojson.features.forEach(feature => {

            if (feature.geometry.type !== "LineString") {
                console.error("Unsupported geometry type:", feature.geometry.type);
                return;
            }

            const latLngs = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);

            const routeColor = getColor(feature.properties.name);

            // 6. إضافة المسار كخط على الخريطة
            let route = L.polyline(latLngs, {
                color: routeColor, // استدعاء الدالة لتحديد اللون
                weight: 4,               // سماكة الخط
                opacity: 0.7             // شفافية الخط
            }).addTo(map);

            // إضافة السهم المتحرك باستخدام PolylineDecorator
            const decorator = L.polylineDecorator(route, {
                patterns: [
                    {
                        offset: '0%',       // نقطة البداية
                        repeat: '2%',      // تكرار السهم كل 20% من طول المسار
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,  // حجم السهم
                            polygon: true,  // شكل السهم كمضلع
                            pathOptions: {
                                fillOpacity: 1,   // شفافية السهم
                                weight: 0,        // لا يوجد إطار حول السهم
                                color: 'red'      // لون السهم
                            }
                        })
                    }
                ]
            }).addTo(map);

            routeLayers.push(route);
            decorators.push(decorator);
            // تحريك السهم بشكل ديناميكي
            let offset = 0; // الإزاحة الأولية
            setInterval(() => {
                offset = (offset + 0.04) % 100; // تحديث الإزاحة (السرعة: زيادة بمقدار 2 في كل تحديث)
                decorator.setPatterns([
                    {
                        offset: `${offset}%`, // مكان السهم الحالي
                        repeat: '6%',        // تكرار السهم
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,    // حجم السهم
                            polygon: true,    // السهم كمضلع
                            pathOptions: {
                                fillOpacity: 1,   // شفافية السهم
                                weight: 0,        // بدون إطار
                                color: 'red'      // لون السهم
                            }
                        })
                    }
                ]);
            }, 100); // تحديث كل 100 مللي ثانية
        })
        map.on('zoomend', function () {
            if (map.getZoom() >= 13) {
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
            }//end else
        });
    })
    .catch(error => console.error('Error loading GeoJSON:', error));

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371; // نصف قطر الأرض بالكيلومتر
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const d = R * c; // المسافة بالكيلومتر
    return d;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180);
}
var startS = null;
var endS = null;

// ############## إضافة الدبابيس إلى قاعدة البيانات ############## 
map.on('dblclick', function (e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;

    // حساب أقرب محطة
    let closestStation = null;
    let minDistance = Infinity;

    stationss.forEach(station => {
        const distance = getDistanceFromLatLonInKm(
            lat,
            lng,
            station.latitude,
            station.longitude
        );

        if (distance < minDistance) {
            minDistance = distance;
            closestStation = station;
        }
    });

    // إنشاء دبوس جديد
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // طلب مسار المشي من openrouteservice
    const apiKey = '5b3ce3597851110001cf6248e8997fbcbabf4bb2b40ff2ec3a348037'; // استبدله بمفتاحك
    const start = `${lng},${lat}`;
    const end = `${closestStation.longitude},${closestStation.latitude}`;
    const url = `https://api.openrouteservice.org/v2/directions/foot-walking?api_key=${apiKey}&start=${start}&end=${end}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            const coords = data.features[0].geometry.coordinates;
            const latlngs = coords.map(coord => [coord[1], coord[0]]);

            const newLine = L.polyline(latlngs, { color: 'green' }).addTo(map);
            marker.lineToStation = newLine;

            const distance = data.features[0].properties.summary.distance / 1000; // كم
            const duration = data.features[0].properties.summary.duration / 60; // دقائق

            marker.bindPopup(
                `أنت هنا<br>أقرب محطة: ${closestStation.name}<br>المسافة: ${distance.toFixed(2)} كم<br>الوقت المتوقع: ${duration.toFixed(1)} دقيقة`
            ).openPopup();
        })
        .catch(error => console.error('خطأ في جلب المسار الفعلي:', error));

    // إزالة الدبابيس القديمة إذا تجاوز العدد 2
    if (markers.length >= 2 && calculateDistance) {
        markers.forEach(m => {
            map.removeLayer(m);
            if (m.lineToStation) {
                map.removeLayer(m.lineToStation);
            }
        });
        markers = [];
        document.getElementById('coordinates').innerHTML = '';
    }

    if (markers.length <= 2) {
        if (markers.length === 0) {
            startS = closestStation;
            // startS = `${closestStation.longitude},${closestStation.latitude}`;
        }
        if (markers.length === 1) {
            endS = closestStation;
            // endS = `${closestStation.longitude},${closestStation.latitude}`;
        }
    }
    // console.log('markers.length:', markers.length);
    console.log('startS:', startS, 'endS:', endS);
    // console.log('closestStation:', closestStation);

    if (startS && endS) {
        fetch('/api/send-to-controll', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                startS: startS,
                endS: endS
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }


    // fetch('/api/send-to-controll', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //     },
    //     body: JSON.stringify({ closestStation: closestStation })
    // })
    //     .then(res => res.json())
    //     .then(data => console.log('تم الإرسال:', data));


    // حفظ الدبوس في قاعدة البيانات
    fetch('/api/save-pin', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ lat, lng })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Pin saved:', data);
            marker.dbId = data.id;
            updateCoordinates();
        })
        .catch(error => console.error('Error saving pin:', error));

    // التعامل مع سحب الدبوس
    let isDragging = false;
    marker.on('dragstart', function () {
        isDragging = true;
        clearTimeout(pressTimer);
    });


    // ############## تحديث الإحداثيات عند السحب ##############
    marker.on('dragend', function (event) {
        isDragging = false; // انتهاء السحب
        var updatedLatLng = event.target.getLatLng();
        const apiKey = '5b3ce3597851110001cf6248e8997fbcbabf4bb2b40ff2ec3a348037'; // ضع مفتاح API الخاص بك هنا
        const start = `${updatedLatLng.lng},${updatedLatLng.lat}`;
        const end = `${closestStation.longitude},${closestStation.latitude}`;
        const url = `https://api.openrouteservice.org/v2/directions/foot-walking?api_key=${apiKey}&start=${start}&end=${end}`;
        updateCoordinates();

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (marker.lineToStation) {
                    map.removeLayer(marker.lineToStation);
                }

                const coords = data.features[0].geometry.coordinates;
                const latlngs = coords.map(coord => [coord[1], coord[0]]); // تحويل لـ [lat, lng]

                const newLine = L.polyline(latlngs, { color: 'green' }).addTo(map);
                marker.lineToStation = newLine;

                const distance = data.features[0].properties.summary.distance / 1000; // بالكيلومتر
                const duration = data.features[0].properties.summary.duration / 60; // بالدقائق

                marker.setPopupContent(`أنت هنا<br>أقرب محطة: ${closestStation.name}<br>المسافة: ${distance.toFixed(2)} كم<br>الوقت المتوقع: ${duration.toFixed(1)} دقيقة`);
            })
            .catch(error => console.error('خطأ في جلب المسار الفعلي:', error));


        fetch(`/api/update-pin/${marker.dbId}`, { // بداية التعديل
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ lat: updatedLatLng.lat, lng: updatedLatLng.lng })
        })
            .then(response => response.json())
            .then(data => console.log('Pin updated:', data))
            .catch(error => console.error('Error updating pin:', error)); // نهاية التعديل
    });

    // ############## حذف الدبوس عند الضغط المطول ##############
    let pressTimer;
    marker.on('mousedown', function () {

        if (!isDragging) { // فقط إذا لم يكن الدبوس في وضع السحب
            pressTimer = setTimeout(() => {
                console.log('Deleting pin with ID:', marker.dbId);

                if (marker.lineToStation) {
                    map.removeLayer(marker.lineToStation);
                }
                fetch(`/api/delete-pin/${marker.dbId}`, { // بداية التعديل
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Pin deleted:', data);
                        map.removeLayer(marker); // إزالة الدبوس من الخريطة
                        markers = markers.filter(m => m !== marker);
                        updateCoordinates();
                    })
                    .catch(error => console.error('Error deleting pin:', error)); // نهاية التعديل
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

//رسم المسافة الاقصر بين المسارات
async function drawShortestPathFromPins(startS, endS) {
    try {
        if (!startS || !endS) {
            console.error("يجب تحديد نقطتي البداية والنهاية أولاً.");
            return;
        }

        const response = await fetch('/api/get-shortest-path-from-pins', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // ضروري إذا كنت داخل Blade
            },
            body: JSON.stringify({ startS, endS }) // هنا نرسل البيانات
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error("خطأ في جلب المسار:", errorText);
            throw new Error("فشل في جلب البيانات");
        }

        const data = await response.json();
        console.log("بيانات السيرفر:", data); // ✅

        if (data.error) {
            alert(data.error);
            return;
        }

        const latlngs = data.path.map(coordStr => {
            const [lng, lat] = coordStr.split(',').map(Number);
            return [lat, lng];
        });

        if (latlngs.length === 0) {
            alert("لم يتم العثور على مسار صالح.");
            return;
        }

        // رسم الخط على الخريطة
        const pathLine = L.polyline(latlngs, {
            color: 'blue',
            weight: 5
        }).addTo(map);

        // تقريب الخريطة لتتناسب مع المسار
        map.fitBounds(pathLine.getBounds());

        // عرض المسافة
        L.popup()
            .setLatLng(latlngs[Math.floor(latlngs.length / 2)])
            .setContent(`المسافة التقريبية: ${data.distance.toFixed(2)} كم`)
            .openOn(map);

    } catch (error) {
        console.error('خطأ في جلب المسار:', error);
    }
}

const button = document.getElementById('drawPathButton');
// مثال فرضي
button.addEventListener('click', () => {
    if (startS && endS) {
        drawShortestPathFromPins(startS, endS);
    } else {
        alert("يرجى اختيار نقطتي البداية والنهاية أولاً.");
    }
});


// //حساب المسافة الاقصر بين المسارات
// async function getShortestPathFromDB() {
//     const response = await fetch('/shortest-path-from-pins', {
//         method: 'GET',
//         headers: {
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//         }
//     });

//     const data = await response.json();

//     if (data.error) {
//         console.error('خطأ:', data.error);
//         return;
//     }

//     console.log('Shortest route:', data.route);
//     console.log('Distance:', data.distance, 'km');

//     // إن أردت رسم المسار على الخريطة يمكنك استخدام data.route
// }


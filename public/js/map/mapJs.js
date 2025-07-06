// إعداد الخريطة

var map = L.map('map', {
    doubleClickZoom: false
}).setView([33.581733104088, 36.407661437988], 13);
map.attributionControl.addAttribution('© By Jehad_HT');

const lightLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

const darkLayer = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; Stadia Maps, OpenStreetMap contributors'
});

const satelliteLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenTopoMap contributors'
});

const baseMaps = {
    "Street Map": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
    "Satellite": satelliteLayer,
};

document.querySelector('.dark_mode').addEventListener('click', () => {
    if (map.hasLayer(lightLayer)) {
        map.removeLayer(lightLayer);
        map.addLayer(darkLayer);
        document.querySelector('.dark_mode').innerText = "Light Mode"
    } else {
        map.removeLayer(darkLayer);
        map.addLayer(lightLayer);
        document.querySelector('.dark_mode').innerText = "Dark Mode"
    }
});

L.control.layers(baseMaps).addTo(map);

// أضف مربع البحث
L.Control.geocoder({
    collapsed: false,
    placeholder: "Search",
    defaultMarkGeocode: true,
    geocoder: L.Control.Geocoder.nominatim({
        geocodingQueryParams: {
            countrycodes: 'sy' // restrict to Syria
        }
    })
})
    .addTo(map);



let stations = [];
let stationMarkers = [];

// Fetch stations from API and store them
// fetch('/api/stations')
//     .then(response => response.json())
//     .then(data => {
//         stations = data; // Store station data

//     })
//     .catch(error => console.error('Error fetching stations:', error));

// function getNearestStation(latlng) {
//     let nearestStation = null;
//     let minDistance = Infinity;

//     stations.forEach(station => {
//         const stationLatLng = L.latLng(station.latitude, station.longitude);
//         const distance = latlng.distanceTo(stationLatLng); // Compute distance in meters

//         if (distance < minDistance) {
//             minDistance = distance;
//             nearestStation = station;
//         }
//     });

//     return nearestStation;
// }
fetch('/api/stations')
    .then(response => response.json())
    .then(stations => {

        stationss = stations;
        // stations.forEach(station => {
        //     L.marker([station.latitude, station.longitude])
        //         .addTo(map)
        //         .bindPopup(`<strong>${station.name}</strong>`);
        // });
    })
    .catch(error => {
        console.error('خطأ في جلب بيانات المحطات:', error);
    });


// Add marker on map double-click
map.on('dblclick', function (e) {
    stationMarkers.forEach(marker => map.removeLayer(marker));
    stationMarkers = [];
    const marker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(map);

    const nearestStation = getNearestStation(e.latlng);

    if (nearestStation) {
        const stationMarker = L.marker([nearestStation.latitude, nearestStation.longitude], { color: 'blue' }).addTo(map)
            .bindPopup(`<strong>${nearestStation.name}</strong>`).openPopup();
        stationMarkers.push(stationMarker); // Store station marker

        marker.bindPopup(`
            <strong>أنت هنا</strong><br>
            <strong>أقرب محطة:</strong> ${nearestStation.name}<br>
            <strong>المسافة:</strong> ${(minDistance / 1000).toFixed(2)} كم
        `).openPopup();
    }
});

// المتغيرات لتخزين العلامة والدائرة للمستخدم
var userMarker = null;
var userCircle = null;
//##
var markers = []; // تخزين الدبابيس
var calculateDistance = false; // وضع تفعيل حساب البعد
var coordinatesList = document.getElementById('coordinates');
var toggleDistance = document.getElementById('toggleDistance');

// زر تحديد الموقع الحالي
var locateButton = document.getElementById('locateButton');
let MyLocation = null;
let busRoutePath = null; // المسار الأزرق

// تحديد الموقع الحالي عند تحميل الصفحة وتعبئة الحقل
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
        // حصلنا على الموقع عبر GPS أو Wi-Fi
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        MyLocation = [lat, lng];
        const startInput = document.querySelector('.startLocation');
        startInput.classList.add("locationIsGot");
        startInput.value = "Your Location";
        document.querySelector('.led').style.visibility = "visible";

    }, function (error) {
        document.querySelector('.startLocation').placeholder = "Location unavailable";
        alert("⚠️ تعذر تحديد موقعك بدقة. يرجى التأكد من تفعيل GPS وعدم تشغيل VPN.");
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });

} else {
    document.querySelector('.startLocation').placeholder = "Geolocation not supported";
}

// زر تحديد الموقع بالرسم على الخريطة
document.getElementById('locateButton').addEventListener('click', function () {
    if (!navigator.geolocation) {
        alert("المتصفح لا يدعم تحديد الموقع.");
        return;
    }

    navigator.geolocation.watchPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        MyLocation = [lat, lng];

        if (userMarker && userCircle) {
            userMarker.setLatLng(MyLocation);
            userCircle.setLatLng(MyLocation);
            userCircle.setRadius(accuracy);
        } else {
            userMarker = L.marker(MyLocation).addTo(map).bindPopup("أنت هنا").openPopup();
            userCircle = L.circle(MyLocation, {
                radius: accuracy,
                color: 'blue',
                fillColor: 'blue',
                fillOpacity: 0.1,
                weight: 1
            }).addTo(map);

            drawWalkingRoute(MyLocation, userMarker, 'start');
        }

        map.setView(MyLocation, 13);
    }, function (error) {
        alert("⚠️ لم نتمكن من تحديد موقعك.");
    }, {
        enableHighAccuracy: true
    });
});

// البحث اليدوي من خلال الحقول
document.getElementById('manualSearchBtn').addEventListener('click', async function () {
    const startText = document.querySelector('.startLocation').value.trim();
    const endText = document.querySelector('.destination').value.trim();

    if (!startText || !endText) {
        alert("يرجى إدخال وجهتي البداية والنهاية.");
        return;
    }

    const apiKey = '5b3ce3597851110001cf6248e8997fbcbabf4bb2b40ff2ec3a348037';
    const geocodeUrl = (place) =>
        `https://api.openrouteservice.org/geocode/search?api_key=${apiKey}&text=${encodeURIComponent(place)}&size=1`;

    try {
        // حذف المسارات والدبابيس السابقة
        if (busRoutePath) {
            map.removeLayer(busRoutePath);
            busRoutePath = null;
        }
        markers.forEach(m => {
            map.removeLayer(m);
            if (m.lineToStation) map.removeLayer(m.lineToStation);
        });
        markers.length = 0;

        let startCoords;

        if (startText === "Your Location") {
            if (!MyLocation) {
                alert("⚠️ لم يتم تحديد موقعك بعد.");
                return;
            }
            startCoords = [MyLocation[1], MyLocation[0]]; // [lng, lat]
        } else {
            const startRes = await fetch(geocodeUrl(startText)).then(r => r.json());
            startCoords = startRes.features?.[0]?.geometry?.coordinates;
        }

        const endRes = await fetch(geocodeUrl(endText)).then(r => r.json());
        const endCoords = endRes.features?.[0]?.geometry?.coordinates;

        if (!startCoords || !endCoords) {
            alert("⚠️ لم يتم العثور على الإحداثيات.");
            return;
        }

        const startLatLng = [startCoords[1], startCoords[0]];
        const endLatLng = [endCoords[1], endCoords[0]];

        const startMarker = L.marker(startLatLng, { draggable: true }).addTo(map);
        startMarker.type = 'start';
        markers.push(startMarker);
        drawWalkingRoute(startLatLng, startMarker, 'start');

        const endMarker = L.marker(endLatLng, { draggable: true }).addTo(map);
        endMarker.type = 'end';
        markers.push(endMarker);
        drawWalkingRoute(endLatLng, endMarker, 'end');

    } catch (error) {
        console.error("❌ خطأ أثناء جلب الإحداثيات:", error);
        alert("حدث خطأ أثناء معالجة الموقع. تأكد من الاتصال بالإنترنت.");
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
                case 'north': return 'rgba(88, 85, 85, 0.78)';
                case 'senaha': return 'purple';
                case 'domaToDam': return 'rgba(194, 6, 6, 0.78)';
                default: return 'gray'; // اللون الافتراضي
            }
        };

        // التكرار على جميع العناصر في GeoJSON
        geojson.features.forEach(feature => {
            // تحقق من وجود geometry وصحته
            if (!feature.geometry || feature.geometry.type !== "LineString") {
                console.warn("تخطي عنصر بدون geometry أو نوع غير مدعوم:", feature);
                return;
            }

            const latLngs = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);

            const routeColor = getColor(feature.properties.name);

            let route = L.polyline(latLngs, {
                color: routeColor,
                weight: 4,
                opacity: 0.7
            }).addTo(map);

            // إضافة السهم المتحرك باستخدام PolylineDecorator
            const decorator = L.polylineDecorator(route, {
                patterns: [
                    {
                        offset: '0%',
                        repeat: '2%',
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,
                            polygon: true,
                            pathOptions: {
                                fillOpacity: 1,
                                weight: 0,
                                color: 'red'
                            }
                        })
                    }
                ]
            }).addTo(map);

            routeLayers.push(route);
            decorators.push(decorator);


            let offset = 0;
            setInterval(() => {
                offset = (offset + 0.04) % 100;
                decorator.setPatterns([
                    {
                        offset: `${offset}%`,
                        repeat: '6%',
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,
                            polygon: true,
                            pathOptions: {
                                fillOpacity: 1,
                                weight: 0,
                                color: 'red'
                            }
                        })
                    }
                ]);
            }, 100); // تحديث كل 100 مللي ثانية
        });

        // التعامل مع مستويات التكبير
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

                routeLayers.forEach(layer => {
                    if (map.hasLayer(layer)) {
                        map.removeLayer(layer);
                    }
                });

                decorators.forEach(decorator => {
                    if (map.hasLayer(decorator)) {
                        map.removeLayer(decorator);
                    }
                });
            }
        });
    })
    .catch(error => console.error('Error loading GeoJSON:', error));

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const d = R * c;
    return d;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180);
}

let startS = null;
let endS = null;
let closestStation = null;
let busPath = null;

map.on('dblclick', function (e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    const coords = [lat, lng];
    // حدد نوع الماركر حسب عدد الموجودين

    // إنشاء ماركر جديد
    var marker = L.marker(coords, { draggable: true }).addTo(map);
    markers.push(marker);
    let type = (markers.length % 2 === 1) ? 'start' : 'end';
    marker.type = type;
    console.log(markers.length % 2 === 0, type);

    // رسم خط المشي من هذا الدبوس إلى أقرب محطة
    drawWalkingRoute(coords, marker, type);

    // إرسال نقطة الحفظ للسيرفر
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
        const newCoords = [event.target.getLatLng().lat, event.target.getLatLng().lng];
        // إزالة خط المشي قبل التحريك

        if (marker.lineToStation) {
            map.removeLayer(marker.lineToStation);
        }

        // إعادة رسم خط المشي
        drawWalkingRoute(newCoords, marker, marker.type);

        // تحديث في قاعدة البيانات
        fetch(`/api/update-pin/${marker.dbId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ lat: newCoords[0], lng: newCoords[1] })
        })
            .then(response => response.json())
            .then(data => console.log('Pin updated:', data))
            .catch(error => console.error('Error updating pin:', error));
    });

    // حذف الماركر عند الضغط مطولًا
    let pressTimer;

    marker.on('dragstart', () => {
        isDragging = true;
        clearTimeout(pressTimer);
    });

    marker.on('mousedown', () => {
        if (!isDragging) {
            pressTimer = setTimeout(() => {
                if (marker.lineToStation) map.removeLayer(marker.lineToStation);
                map.removeLayer(marker);
                markers = markers.filter(m => m !== marker);
                updateCoordinates();
            }, 1000);
        }
    });

    marker.on('mouseup', () => {
        clearTimeout(pressTimer);
        isDragging = false;
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

// fetch('/api/shortest-path')
//     .then(res => res.json())
//     .then(data => {
//         console.log('The data is: ', data);
//         if (!data.path || !data.path.coordinates || data.path.coordinates.length === 0) {
//             alert("لم يتم العثور على مسار صالح");
//             return;
//         }
//         const path = L.geoJSON(data.path, {
//             style: { color: 'blue', weight: 5 }
//         }).addTo(map);
//         map.fitBounds(path.getBounds());
//     });



fetch('/api/stations')
    .then(response => response.json())
    .then(stations => {

        stationss = stations;
        // stations.forEach(station => {
        //     L.marker([station.latitude, station.longitude])
        //         .addTo(map)
        //         .bindPopup(`<strong>${station.name}</strong>`);
        // });
    })
    .catch(error => {
        console.error('خطأ في جلب بيانات المحطات:', error);
    });

//رسم المسافة الاقصر بين المسارات
async function drawShortestPathFromPins(startS, endS) {
    try {
        console.log('this is drawPath');

        // رسم مسار الباص إن توفر كلا المحطتين
        if (startS && endS) {
            console.log("You are in Condation");

            fetch('/api/send-to-controll', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ startS, endS })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.path || data.path.length === 0) {
                        alert("⚠️ لم يتم العثور على مسار صالح.walking");
                        return;
                    }
                    console.log('The Costs is: ', data.total_cost);
                    console.log('The line name is: ', data.line_name);

                    // // 🧽 إزالة المسار الأزرق السابق إن وُجد
                    if (busRoutePath) {
                        map.removeLayer(busRoutePath);
                    }

                    const geojsonFeatures = data.path.map(seg => seg.geojson);

                    // 🎯 رسم المسار الجديد وتخزينه في المتغير
                    busRoutePath = L.geoJSON(geojsonFeatures, {
                        style: {
                            color: 'blue',
                            weight: 5,
                            opacity: 0.5
                        }
                    }).addTo(map);

                    map.fitBounds(busRoutePath.getBounds());
                })
                .catch(error => console.error('❌ خطأ في رسم مسار الباص:', error));
        }
    } catch (error) {
        console.error('خطأ في جلب المسار:', error);
    }
}

const button = document.getElementById('drawPathButton');
const sideMenu = document.getElementById('sideMenu');

// Function to open the side menu and set up outside click handler
function openSideMenu() {
    if (markers.length <= 1) {
        sideMenu.classList.add("active");
    }
    // Handler to close the menu when clicking outside
    function handleClickOutside(event) {
        if (
            !button.contains(event.target) &&
            !sideMenu.contains(event.target)
        ) {
            sideMenu.classList.remove("active");
            document.removeEventListener('click', handleClickOutside);
        }
    }

    // Add the handler (with a small timeout to avoid immediate closing)
    setTimeout(() => {
        document.addEventListener('click', handleClickOutside);
    }, 0);
}

button.addEventListener('click', () => {
    if (startS && endS) {
        drawShortestPathFromPins(startS, endS);
    } else {
        // Only open if not already open
        if (!sideMenu.classList.contains("active")) {
            openSideMenu();
        }
    }
});

map.removeControl(map.zoomControl) // remove zoom buttons

//تابع لرسم المسار المشي من الدبوس الى اقرب محطة على المسار
/**
 * يرسم مسار المشي من نقطة معينة إلى أقرب محطة
 * @param {[number, number]} fromCoords [latitude, longitude]
 * @param {L.Marker|null} marker - العنصر المرئي المرتبط بالنقطة (اختياري)
 * @param {'start'|'end'|null} type - لتحديد إذا ما كانت نقطة انطلاق أو وصول
 */

function drawWalkingRoute(fromCoords, marker = null, type = null) {
    console.log("Call function");
    const apiKey = '5b3ce3597851110001cf6248e8997fbcbabf4bb2b40ff2ec3a348037';
    // إيجاد أقرب محطة
    let closestStation = null;
    let minDistance = Infinity;

    stationss.forEach(station => {
        const dist = getDistanceFromLatLonInKm(
            fromCoords[0], fromCoords[1],
            station.latitude, station.longitude
        );
        if (dist < minDistance) {
            minDistance = dist;
            closestStation = station;
        }
    });

    if (!closestStation) {
        console.warn("⚠️ لم يتم العثور على أقرب محطة.");
        return;
    }

    const start = `${fromCoords[1]},${fromCoords[0]}`; // lng, lat
    const end = `${closestStation.longitude},${closestStation.latitude}`;
    const url = `https://api.openrouteservice.org/v2/directions/foot-walking?api_key=${apiKey}&start=${start}&end=${end}`;

    fetch(url)
        .then(res => res.json())
        .then(routeData => {
            if (!routeData || !routeData.features || !routeData.features.length) {
                console.error("❌ فشل في استلام بيانات المسار.");
                return;
            }

            const coords = routeData.features[0].geometry.coordinates;
            const latlngs = coords.map(([lng, lat]) => [lat, lng]);
            const newLine = L.polyline(latlngs, { color: 'green' }).addTo(map);
            marker.lineToStation = newLine;

            const distance = routeData.features[0].properties.summary.distance / 1000;
            const duration = routeData.features[0].properties.summary.duration / 60;

            // نافذة منبثقة
            marker.bindPopup(`
                ${type === 'start' ? 'نقطة الانطلاق' : type === 'end' ? 'الوجهة' : 'الموقع'}<br>
                أقرب محطة: ${closestStation.name}<br>
                المسافة: ${distance.toFixed(2)} كم<br>
                الوقت المتوقع: ${duration.toFixed(1)} دقيقة
                `).openPopup();

            if (busRoutePath) {
                console.log('busRoutePath', busRoutePath);
                map.removeLayer(busRoutePath);
                busRoutePath = null;
            }
            // إذا أصبح عدد الماركرات 3، امسح الكل واحتفظ بآخر واحد فقط
            if (markers.length === 3) {
                const lastMarker = markers[2]; // احتفظ بآخر ماركر مرسوم
                // إعادة تعيين نقاط البداية والنهاية
                startS = null;
                endS = null;
                // إزالة جميع الماركرات والخطوط من الخريطة
                markers.forEach(m => {
                    map.removeLayer(m);
                    if (m.lineToStation) {
                        map.removeLayer(m.lineToStation);
                    }
                });

                // إعادة تعيين المصفوفة مع آخر ماركر فقط
                markers = [lastMarker];

                // إعادة عرض الماركر الأخير على الخريطة
                map.addLayer(lastMarker);

                // إعادة عرض خط المشي إذا وُجد
                if (lastMarker.lineToStation) {
                    map.addLayer(lastMarker.lineToStation);
                }

                // تحديث صندوق الإحداثيات إن وجد
                document.getElementById('coordinates').innerHTML = '';
            }
            console.log("length marker :", markers.length)

            // تعيين startS و endS حسب الترتيب

            if (type === 'start') {
                startS = closestStation;
                console.log("This is starts");
            } else {
                endS = closestStation;
                console.log("This is end");
            }
        })
        .catch(error => {
            console.error('❌ فشل في جلب مسار المشي من OpenRoute:', error);
        });
}

/**
 * ينشئ دبوس ذكي يمكن تحريكه ويرتبط بمسار مشي إلى أقرب محطة
 * @param {[number, number]} coords [latitude, longitude]
 * @param {'start'|'end'} type - نوع الدبوس
 */
function createMarker(coords, type = null) {
    const marker = L.marker(coords, { draggable: true }).addTo(map);
    markers.push(marker);

    // رسم المسار أول مرة
    drawWalkingRoute(coords, marker, type);

    // عند سحب الدبوس وتحريره
    marker.on('dragend', function (event) {
        const newCoords = [
            event.target.getLatLng().lat,
            event.target.getLatLng().lng
        ];
        console.log("✅ تم سحب الدبوس إلى:", newCoords);
        drawWalkingRoute(newCoords, marker, type);
    });
}

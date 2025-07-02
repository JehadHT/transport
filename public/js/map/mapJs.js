// إعداد الخريطة

var map = L.map('map', {
    doubleClickZoom: false
}).setView([33.581733104088, 36.407661437988], 13)
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
fetch('/api/stations')
    .then(response => response.json())
    .then(data => {
        stations = data; // Store station data

    })
    .catch(error => console.error('Error fetching stations:', error));

function getNearestStation(latlng) {
    let nearestStation = null;
    let minDistance = Infinity;

    stations.forEach(station => {
        const stationLatLng = L.latLng(station.latitude, station.longitude);
        const distance = latlng.distanceTo(stationLatLng); // Compute distance in meters

        if (distance < minDistance) {
            minDistance = distance;
            nearestStation = station;
        }
    });

    return nearestStation;
}

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

// إضافة حدث الضغط على الزر
locateButton.addEventListener('click', function () {
    if (navigator.geolocation) {
        // طلب تتبع موقع المستخدم باستخدام Geolocation API
        navigator.geolocation.watchPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                var accuracy = position.coords.accuracy;

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

var startS = null;
var endS = null;
var closestStation = null;

map.on('dblclick', function (e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    markers.push(marker);

    fetch('/api/find-closest-point-on-route', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            latitude: lat,
            longitude: lng
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.closest_latitude && data.closest_longitude) {
                const closestLat = parseFloat(data.closest_latitude);
                const closestLng = parseFloat(data.closest_longitude);
                closestStation = {
                    latitude: closestLat,
                    longitude: closestLng
                };
                console.log('closestStation:', closestStation);

                // ✅ حفظ أقرب نقطة في قاعدة البيانات في عمود nearestnode
                // fetch('/api/save-nearest-node', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                //     },
                //     body: JSON.stringify({ lat: closestLat, lng: closestLng })
                // })
                //     .then(response => response.json())
                //     .then(data => {
                //         console.log('nearest Pin saved:', data);
                //     })
                //     .catch(error => console.error('Error saving nearest node:', error));

                fetch('/api/save-pin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat, lng, lati: closestLat, lngi: closestLng })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Pin saved:', data);
                        marker.dbId = data.id;
                        console.log('closestStation: ', closestStation)
                        updateCoordinates();
                    })
                    .catch(error => console.error('Error saving pin:', error));


                // طلب مسار المشي من openrouteservice
                const apiKey = '5b3ce3597851110001cf6248e8997fbcbabf4bb2b40ff2ec3a348037';
                const start = `${lng},${lat}`;
                const end = `${closestLng},${closestLat}`;
                const url = `https://api.openrouteservice.org/v2/directions/foot-walking?api_key=${apiKey}&start=${start}&end=${end}`;

                fetch(url)
                    .then(res => res.json())
                    .then(routeData => {
                        const coords = routeData.features[0].geometry.coordinates;
                        const latlngs = coords.map(coord => [coord[1], coord[0]]);
                        const newLine = L.polyline(latlngs, { color: 'green' }).addTo(map);
                        marker.lineToStation = newLine;

                        const distance = routeData.features[0].properties.summary.distance / 1000;
                        const duration = routeData.features[0].properties.summary.duration / 60;

                        marker.bindPopup(
                            `أنت هنا<br>أقرب نقطة على المسار: ${distance.toFixed(2)} كم<br>الوقت المتوقع: ${duration.toFixed(1)} دقيقة`
                        ).openPopup();
                    })
                    .catch(error => console.error('خطأ في جلب المسار الفعلي:', error));

                if (markers.length <= 2) {
                    if (markers.length === 1) {
                        startS = closestStation;
                        console.log('startS:', startS);
                    }
                    if (markers.length === 2) {
                        endS = closestStation;
                    }
                }
                console.log('markers:', markers.length);
                console.log('startS:', startS, 'endS:', endS);
            } else {
                alert("لم يتم العثور على نقطة قريبة من المسارات.");
            }
        });

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

    // fetch('/api/save-pin', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //     },
    //     body: JSON.stringify({ lat, lng })
    // })
    //     .then(response => response.json())
    //     .then(data => {
    //         console.log('Pin saved:', data);
    //         marker.dbId = data.id;
    //         console.log('closestStation: ', closestStation)
    //         updateCoordinates();
    //     })
    //     .catch(error => console.error('Error saving pin:', error));

    let isDragging = false;
    marker.on('dragstart', function () {
        isDragging = true;
        clearTimeout(pressTimer);
    });

    marker.on('dragend', function (event) {
        isDragging = false;
        var updatedLatLng = event.target.getLatLng();
        const apiKey = '5b3ce3597851110001cf6248e8997fbcbabf4bb2b40ff2ec3a348037';
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
                const latlngs = coords.map(coord => [coord[1], coord[0]]);
                const newLine = L.polyline(latlngs, { color: 'green' }).addTo(map);
                marker.lineToStation = newLine;

                const distance = data.features[0].properties.summary.distance / 1000;
                const duration = data.features[0].properties.summary.duration / 60;

                marker.setPopupContent(`أنت هنا<br>أقرب محطة: ${closestStation.name}<br>المسافة: ${distance.toFixed(2)} كم<br>الوقت المتوقع: ${duration.toFixed(1)} دقيقة`);
            })
            .catch(error => console.error('خطأ في جلب المسار الفعلي:', error));

        fetch(`/api/update-pin/${marker.dbId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ lat: updatedLatLng.lat, lng: updatedLatLng.lng, closestStation: closestStation })
        })
            .then(response => response.json())
            .then(data => console.log('Pin updated:', data))
            .catch(error => console.error('Error updating pin:', error));
    });

    let pressTimer;
    marker.on('mousedown', function () {
        if (!isDragging) {
            pressTimer = setTimeout(() => {
                console.log('Deleting pin with ID:', marker.dbId);

                if (marker.lineToStation) {
                    map.removeLayer(marker.lineToStation);
                }
                fetch(`/api/delete-pin/${marker.dbId}`, {
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
                        map.removeLayer(marker);
                        markers = markers.filter(m => m !== marker);
                        updateCoordinates();
                    })
                    .catch(error => console.error('Error deleting pin:', error));
            }, 1000);
        }
    });

    marker.on('mouseup', function () {
        clearTimeout(pressTimer);
    });

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


// const input = document.querySelector('.search');
// const sideMenu = document.getElementById('sideMenu');

// input.addEventListener('focus', () => {
//     sideMenu.classList.add("active");
//     setTimeout(() => {
//         input.style.right= "50px"
//         input.style.color= "green"
//         // input.style.background-color = grey

//     },20)
// });
// document.addEventListener('click', function(event) {
//     if(
//         !input.contains(event.target) &&
//         !sideMenu.contains(event.target)
//     ){
//     sideMenu.classList.remove("active");
//     input.style.right= "20px"
//     input.style.color= "grey"
//     }
// });


if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      document.querySelector('.startLocation').classList.add("locationIsGot");
      document.querySelector('.startLocation').value = "Your Location";
      document.querySelector('.led').style.visibility = "visible";
    }, function(error) {
      document.querySelector('.startLocation').placeholder = "Location unavailable";
    });
  } else {
    document.querySelector('.startLocation').placeholder = "Geolocation not supported";
  }








const MapWithDraw = () => {
  useEffect(() => {
const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Initialize the draw control
    const drawControl = new L.Control.Draw({
      draw: {
        polyline: true,   // enable line drawing
        polygon: false,
        rectangle: false,
        circle: false,
        marker: false,
        circlemarker: false
      },
      edit: {
        featureGroup: drawnItems
      }
    });

    map.addControl(drawControl);

    // Listen for draw creation
    map.on(L.Draw.Event.CREATED, function (event) {
      const layer = event.layer;
      drawnItems.addLayer(layer);
      console.log('Line coordinates:', layer.getLatLngs());
    });
  }, []);


};






// After adding the layer control to the map
setTimeout(() => {
    const labels = document.querySelectorAll('.leaflet-control-layers label');

    labels.forEach(label => {
        const text = label.textContent.trim();

        let imgSrc = '';
        if (text === 'Street Map') {
            imgSrc = './../../css/images/street_map.png'; // example tile as thumbnail
        } else if (text === 'Satellite') {
            imgSrc = './../../css/images/satellite.png';
        }

        if (imgSrc) {
            const img = document.createElement('img');
            img.src = imgSrc;
            label.prepend(img);
        }
    });
}, 500);




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}">
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <script src="{{ asset('js/leaflet.polylineDecorator.js') }}"></script>

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

        #coordinatesList {
            position: absolute;
            top: 125px;
            left: 18px;
            z-index: 1000;
            /* ضمان ظهور الزر فوق الخريطة */
            background: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            /* تحسين شكل الزر */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            /* إضافة ظل خفيف */
            cursor: pointer;
            /* إظهار مؤشر اليد عند المرور على الزر */
        }

        #locateButton {
            position: absolute;
            top: 87px;
            left: 18px;
            z-index: 1000;
            /* ضمان ظهور الزر فوق الخريطة */
            background: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            /* تحسين شكل الزر */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            /* إضافة ظل خفيف */
            cursor: pointer;
            /* إظهار مؤشر اليد عند المرور على الزر */
        }

        #drawPathButton {
            position: absolute;
            top: 49px;
            left: 52px;
            z-index: 1000;
            /* ضمان ظهور الزر فوق الخريطة */
            background: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            /* تحسين شكل الزر */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            /* إضافة ظل خفيف */
            cursor: pointer;
            /* إظهار مؤشر اليد عند المرور على الزر */
        }
    </style>
</head>

<body>
    <div id="map"></div>
    <button id="locateButton">تحديد الموقع الحالي</button> <!-- زر لتحديد الموقع -->
    <button id="toggleDistance" style="position: absolute; top: 10px; left: 80px; z-index: 1000;">تفعيل حساب
        البعد</button>
    <div id="coordinatesList">
        <h4>إحداثيات الدبابيس:</h4>
        <ul id="coordinates"></ul>
    </div>
    <button id="drawPathButton">ارسم المسار</button>
    <script src="{{ asset('js/map/mapJs.js') }}"></script>

</body>

</html>
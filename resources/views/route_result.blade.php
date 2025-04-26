<!DOCTYPE html>
<html>

<head>
    <title>نتيجة المسار</title>
    <style>
        .path-container {
            margin: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .path-point {
            padding: 5px;
            margin: 3px 0;
            background-color: #f5f5f5;
        }

        .distance {
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="path-container">
        <h2>تفاصيل المسار</h2>

        <div class="path-points">
            @foreach($path as $point)
                <div class="path-point">
                    النقطة {{ $loop->iteration }}: {{ $point }}
                </div>
            @endforeach
        </div>

        <div class="distance">
            المسافة الكلية: {{ number_format($distance, 2) }} كم
        </div>
    </div>
</body>

</html>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>مساعد المواصلات الذكي</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar-chat {
            width: 30%;
            min-width: 300px;
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 1px solid #ccc;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            background-color: #ffffff;
            padding: 20px;
        }

        #messages {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #fff;
            margin-bottom: 10px;
        }

        #chat-form {
            display: flex;
            gap: 10px;
        }

        #user-message {
            flex: 1;
            padding: 8px;
        }

        button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- الشات في الجانب -->
        <div class="sidebar-chat">
            <h3>💬 مساعد المواصلات</h3>
            <div id="messages"></div>
            <form id="chat-form">
                <input type="text" id="user-message" placeholder="اكتب رسالتك..." />
                <button type="submit">إرسال</button>
            </form>
        </div>

        <!-- محتوى رئيسي، يمكنك وضع الخريطة هنا -->
        <div class="main-content">
            {{-- <h2>الخريطة أو أي محتوى آخر</h2> --}}
            @include('maps.map')
        </div>
    </div>

    <script>
        let originMarker = null;
        let destinationMarker = null;
        window.aiOriginCoords = null;

        const form = document.getElementById('chat-form');
        const input = document.getElementById('user-message');
        const messages = document.getElementById('messages');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) return;

            messages.innerHTML += `<div><strong>أنت:</strong> ${message}</div>`;
            input.value = '';

            const response = await fetch('/api/chat-ai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            // window.aiOriginCoords = data.origin_coords;
            // window.aidestination = data.destination_coords;

            if (data.origin_coords) {
                createMarker(data.origin_coords, "start");
            }
            if (data.destination_coords) {
                createMarker(data.destination_coords, "end");
            }


            messages.innerHTML += `<div><strong>المساعد:</strong> ${data.reply.replace(/\n/g, "<br>")}</div>`;
            messages.scrollTop = messages.scrollHeight;
        });
    </script>

</body>

</html>
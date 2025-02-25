<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo Thời Gian Thực</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        #notifications {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 100px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        #message-form {
            margin-top: 20px;
        }
        #message-input, #channel-select {
            width: 35%;
            padding: 10px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Ứng dụng thông báo thời gian thực</h1>
    <button onclick="requestNotificationPermission()">Yêu cầu quyền thông báo</button>

    <div id="message-form">
        <h3>Soạn tin nhắn:</h3>
        <input type="text" id="message-input" placeholder="Nhập tin nhắn...">
        <select id="channel-select">
            <option value="chat.room1">chat.room1</option>
            <option value="chat.room2">chat.room2</option>
            <option value="notifications.user1">notifications.user1</option>
        </select>
        <button id="send-btn" onclick="sendMessage()" disabled>Gửi</button>
    </div>

    <div>
        <h3>Chọn kênh để subscribe:</h3>
        <button id="sub-room1" onclick="changeChannel('chat.room1')" disabled>Subscribe chat.room1</button>
        <button id="sub-room2" onclick="changeChannel('chat.room2')" disabled>Subscribe chat.room2</button>
        <button id="sub-user1" onclick="changeChannel('notifications.user1')" disabled>Subscribe notifications.user1</button>
    </div>

    <div id="notifications">
        <h3>Thông báo:</h3>
        <ul id="notification-list"></ul>
    </div>

    <script>
        let isWorkerReady = false;

        function requestNotificationPermission() {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    alert('Quyền thông báo đã được cấp!');
                } else {
                    alert('Quyền thông báo bị từ chối.');
                }
            });
        }

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => {
                    console.log('Service Worker đã được đăng ký:', reg);
                })
                .catch(err => {
                    console.error('Lỗi khi đăng ký Service Worker:', err);
                });

            navigator.serviceWorker.addEventListener('message', event => {
                if (event.data.type === 'worker-ready') {
                    isWorkerReady = true;
                    enableButtons();
                    console.log('Service Worker is ready');
                } else {
                    const notification = event.data;
                    addNotificationToList(notification.message);
                }
            });
        } else {
            console.log('Trình duyệt không hỗ trợ Service Worker.');
        }

        function addNotificationToList(message) {
            const list = document.getElementById('notification-list');
            const li = document.createElement('li');
            li.textContent = message;
            list.appendChild(li);
        }

        function sendMessage() {
            if (!isWorkerReady) {
                alert('Service Worker chưa sẵn sàng!');
                return;
            }
            const messageInput = document.getElementById('message-input');
            const channelSelect = document.getElementById('channel-select');
            const message = messageInput.value.trim();
            const channel = channelSelect.value;

            if (!message) {
                alert('Vui lòng nhập tin nhắn!');
                return;
            }

            fetch('api/message-broker', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message, channel: channel })
            })
            .then(response => response.json())
            .then(data => {
                console.log('API response:', data);
                messageInput.value = '';
                alert('Tin nhắn đã được gửi!');
            })
            .catch(error => {
                console.error('Lỗi khi gửi tin nhắn:', error);
                alert('Có lỗi xảy ra khi gửi tin nhắn.');
            });
        }

        function changeChannel(channel) {
            if (!isWorkerReady) {
                alert('Service Worker chưa sẵn sàng!');
                return;
            }
            if (navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({
                    type: 'subscribe',
                    channel: channel
                });
                console.log(`Requested subscription to ${channel}`);
            } else {
                console.error('Service Worker controller không sẵn sàng.');
            }
        }

        function enableButtons() {
            document.getElementById('send-btn').disabled = false;
            document.getElementById('sub-room1').disabled = false;
            document.getElementById('sub-room2').disabled = false;
            document.getElementById('sub-user1').disabled = false;
        }
    </script>
</body>
</html>

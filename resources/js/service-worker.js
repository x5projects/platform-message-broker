let webSocketUrl = "ws://localhost:8080/ws";

self.addEventListener("install", (event) => {
    console.log("Service Worker installed");
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    console.log("Service Worker activated");
    event.waitUntil(
        self.clients
            .claim()
            .then(() => {
                console.log("Service Worker now controls the page");
                // Gửi tin nhắn tới giao diện báo sẵn sàng
                self.clients.matchAll().then((clients) => {
                    clients.forEach((client) =>
                        client.postMessage({ type: "worker-ready" }),
                    );
                });
            }),
    );
});

const ws = new WebSocket(webSocketUrl);

ws.onopen = () => {
    console.log("Connected to WebSocket server");

    // subscribeToChannel("default.channel");
};

function subscribeToChannel(channel) {
    ws.send(JSON.stringify({ subscribe: channel }));
    console.log(`Subscribed to channel: ${channel}`);
}

ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log("Received from WebSocket:", data);

    self.registration.showNotification("New Notification", {
        body: data.message || "You have a new notification!",
        icon: "/icon.png",
    });

    self.clients.matchAll().then((clients) => {
        clients.forEach((client) => client.postMessage(data));
    });
};

ws.onerror = (error) => {
    console.error("WebSocket error:", error);
};

ws.onclose = () => {
    console.log("WebSocket connection closed");
};

self.addEventListener("notificationclick", (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow("/notifications"));
});

self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "subscribe") {
        const channel = event.data.channel;
        ws.send(JSON.stringify({ subscribe: channel }));
        console.log(`Subscribed to channel: ${channel}`);
    }
});

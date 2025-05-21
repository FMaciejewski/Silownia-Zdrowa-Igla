let currentUserId, currentUserName, receiverId, receiverName;
let socket;
let isConnected = false;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;
const baseReconnectDelay = 3000;

function updateStatus(text, color) {
    const statusElement = document.getElementById('status');
    statusElement.textContent = text;
    statusElement.style.color = color;
}

function updateTypingIndicator(isTyping) {
    const typingIndicator = document.getElementById('typing-indicator');
    typingIndicator.textContent = isTyping ? `${receiverName} pisze...` : '';
}

function displayMessage(message, isMyMessage, senderName = null, status = 'sent') {
    const messagesDiv = document.getElementById('messages');
    const messageElement = document.createElement('div');
    messageElement.className = isMyMessage ? 'message my-message' : 'message their-message';
    
    if (!isMyMessage && senderName) {
        messageElement.innerHTML = `<strong>${senderName}:</strong> ${message}`;
    } else {
        messageElement.textContent = message;
    }
    
    if (isMyMessage) {
        const statusElement = document.createElement('span');
        statusElement.className = `message-status ${status}`;
        statusElement.textContent = getStatusText(status);
        messageElement.appendChild(statusElement);
    }
    
    messagesDiv.appendChild(messageElement);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function getStatusText(status) {
    const statusTexts = {
        'sent': 'Wysłano',
        'delivered': 'Dostarczono',
        'read': 'Przeczytano'
    };
    return statusTexts[status] || '';
}

async function loadChatHistory() {
    try {
        const response = await fetch(`../../backend/api/get_messages.php?receiver_id=${receiverId}`, {
            credentials: 'include'
        });
        const messages = await response.json();
        messages.forEach(msg => {
            const isMyMessage = msg.sender_id == currentUserId;
            displayMessage(msg.message, isMyMessage, isMyMessage ? null : msg.sender_name, 'delivered');
        });
    } catch (error) {
        console.error('Error loading chat history:', error);
    }
}

function connectWebSocket() {
    socket = new WebSocket(`ws://localhost:8080/chat?userId=${currentUserId}&userName=${encodeURIComponent(currentUserName)}&token=${getAuthToken()}`);
    
    socket.onopen = () => {
        isConnected = true;
        reconnectAttempts = 0;
        updateStatus('Połączono z serwerem czatu', 'green');
        sendPresenceNotification('online');
    };
    
    socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        switch(data.type) {
            case 'privateMessage':
                if (data.senderId == receiverId) {
                    displayMessage(data.message, false, data.senderName);
                    sendReadReceipt(data.senderId);
                }
                break;
            case 'typing':
                if (data.senderId == receiverId) {
                    updateTypingIndicator(data.isTyping);
                }
                break;
            case 'presence':
                updateUserPresence(data.userId, data.status);
                break;
            case 'messageStatus':
                updateMessageStatus(data.messageId, data.status);
                break;
        }
    };
    
    socket.onclose = () => {
        isConnected = false;
        sendPresenceNotification('offline');
        
        if (reconnectAttempts < maxReconnectAttempts) {
            reconnectAttempts++;
            const delay = baseReconnectDelay * Math.pow(2, reconnectAttempts);
            updateStatus(`Rozłączono. Próba ponownego połączenia (${reconnectAttempts}/${maxReconnectAttempts})...`, 'red');
            setTimeout(connectWebSocket, delay);
        } else {
            updateStatus('Nie można połączyć z serwerem. Odśwież stronę aby spróbować ponownie.', 'red');
        }
    };
    
    socket.onerror = (error) => {
        console.error('Błąd WebSocket:', error);
        updateStatus('Błąd połączenia', 'red');
    };
}

function sendPresenceNotification(status) {
    if (isConnected) {
        socket.send(JSON.stringify({
            type: 'presence',
            status: status
        }));
    }
}

function sendReadReceipt(senderId) {
    if (isConnected) {
        socket.send(JSON.stringify({
            type: 'readReceipt',
            senderId: senderId,
            receiverId: currentUserId
        }));
    }
}

function getAuthToken() {
    // Implement token retrieval (e.g., from cookies or local storage)
    return document.cookie.match('(^|;)\\s*token\\s*=\\s*([^;]+)')?.pop() || '';
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const receiverIdParam = urlParams.get('receiver_id');
    
    fetch(`../../backend/api/get_fizjo.php?receiver_id=${receiverIdParam}`, {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            document.getElementById('error-message').textContent = data.error;
            document.getElementById('error-message').style.display = 'block';
            return;
        }
        
        currentUserId = data.currentUserId;
        currentUserName = data.currentUserName;
        receiverId = data.receiverId;
        receiverName = data.receiverName;

        document.getElementById('chat-title').textContent = `Czat z ${receiverName}`;
        document.getElementById('chat-container').style.display = 'block';

        connectWebSocket();
        loadChatHistory();

        const messageInput = document.getElementById('message-input');
        let typingTimeout;

        messageInput.addEventListener('input', () => {
            if (isConnected) {
                socket.send(JSON.stringify({
                    type: 'typing',
                    receiverId: receiverId,
                    isTyping: true
                }));
                
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    socket.send(JSON.stringify({
                        type: 'typing',
                        receiverId: receiverId,
                        isTyping: false
                    }));
                }, 2000);
            }
        });

        document.getElementById('message-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;
            
            if (isConnected) {
                const messageData = {
                    type: 'privateMessage',
                    senderId: currentUserId,
                    senderName: currentUserName,
                    receiverId: receiverId,
                    message: message,
                    timestamp: new Date().toISOString()
                };
                socket.send(JSON.stringify(messageData));
                displayMessage(message, true);
                messageInput.value = '';
            } else {
                updateStatus('Nie można wysłać - brak połączenia', 'red');
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('error-message').textContent = 'Wystąpił błąd podczas ładowania czatu';
        document.getElementById('error-message').style.display = 'block';
    });

    window.addEventListener('beforeunload', () => {
        sendPresenceNotification('offline');
    });
});
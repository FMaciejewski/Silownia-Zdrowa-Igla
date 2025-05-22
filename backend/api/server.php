<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userIdToConn = [];
    protected $connToUserId = [];
    protected $userNames = [];
    protected $userStatus = [];
    protected $messageStorage;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->messageStorage = new MessageStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);
        $userId = $query['userId'] ?? null;
        $userName = $query['userName'] ?? "Nieznany";
        $token = $query['token'] ?? null;


        $this->clients->attach($conn);
        $this->userIdToConn[$userId] = $conn;
        $this->connToUserId[$conn->resourceId] = $userId;
        $this->userNames[$userId] = $userName;
        $this->userStatus[$userId] = 'online';

        $this->broadcastPresence($userId, 'online');
        $this->sendPendingMessages($userId);

        echo "Nowe połączenie: userId={$userId}, userName={$userName}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['type'])) {
            return;
        }

        $senderId = $this->connToUserId[$from->resourceId] ?? null;
        if (!$senderId) {
            return;
        }

        switch ($data['type']) {
            case 'privateMessage':
                $this->handlePrivateMessage($senderId, $data);
                break;
            case 'typing':
                $this->handleTypingNotification($senderId, $data);
                break;
            case 'readReceipt':
                $this->handleReadReceipt($senderId, $data);
                break;
            case 'presence':
                $this->handlePresenceUpdate($senderId, $data);
                break;
            default:
                echo "Nieznany typ wiadomości: {$data['type']}\n";
        }
    }

    protected function handlePrivateMessage($senderId, $data) {
        $receiverId = $data['receiverId'] ?? null;
        $messageText = trim($data['message'] ?? '');
        
        if (!$receiverId || empty($messageText)) {
            return;
        }

        $messageId = $this->messageStorage->storeMessage($senderId, $receiverId, $messageText);
        $senderName = $this->userNames[$senderId] ?? "Nieznany";

        // Wysyłanie do odbiorcy jeśli jest online
        if (isset($this->userIdToConn[$receiverId])) {
            $this->userIdToConn[$receiverId]->send(json_encode([
                'type' => 'privateMessage',
                'senderId' => $senderId,
                'senderName' => $senderName,
                'message' => $messageText,
                'messageId' => $messageId,
                'timestamp' => date('Y-m-d H:i:s')
            ]));

            // Aktualizacja statusu na "dostarczono"
            $this->messageStorage->updateMessageStatus($messageId, 'delivered');
        }

        $this->userIdToConn[$senderId]->send(json_encode([
            'type' => 'messageStatus',
            'messageId' => $messageId,
            'status' => isset($this->userIdToConn[$receiverId]) ? 'delivered' : 'sent'
        ]));
    }

    protected function handleTypingNotification($senderId, $data) {
        $receiverId = $data['receiverId'] ?? null;
        $isTyping = (bool)($data['isTyping'] ?? false);

        if ($receiverId && isset($this->userIdToConn[$receiverId])) {
            $this->userIdToConn[$receiverId]->send(json_encode([
                'type' => 'typing',
                'senderId' => $senderId,
                'isTyping' => $isTyping,
                'senderName' => $this->userNames[$senderId] ?? "Nieznany"
            ]));
        }
    }

    protected function handleReadReceipt($senderId, $data) {
        $messageId = $data['messageId'] ?? null;
        $receiverId = $data['senderId'] ?? null;
        if ($messageId && $receiverId && isset($this->userIdToConn[$receiverId])) {
            $this->messageStorage->updateMessageStatus($messageId, 'read');
            $this->userIdToConn[$receiverId]->send(json_encode([
                'type' => 'messageStatus',
                'messageId' => $messageId,
                'status' => 'read'
            ]));
        }
    }

    protected function handlePresenceUpdate($userId, $data) {
        $status = in_array($data['status'] ?? null, ['online', 'offline', 'away']) 
                ? $data['status'] 
                : 'online';
                
        $this->userStatus[$userId] = $status;
        $this->broadcastPresence($userId, $status);
    }

    protected function broadcastPresence($userId, $status) {
        $userName = $this->userNames[$userId] ?? "Nieznany";
        $presenceData = json_encode([
            'type' => 'presence',
            'userId' => $userId,
            'userName' => $userName,
            'status' => $status
        ]);

        foreach ($this->userIdToConn as $id => $conn) {
            if ($id != $userId) {
                $conn->send($presenceData);
            }
        }
    }

    protected function sendPendingMessages($userId) {
        $pendingMessages = $this->messageStorage->getPendingMessages($userId);
        foreach ($pendingMessages as $message) {
            if (isset($this->userIdToConn[$userId])) {
                $this->userIdToConn[$userId]->send(json_encode([
                    'type' => 'privateMessage',
                    'senderId' => $message['sender_id'],
                    'senderName' => $this->userNames[$message['sender_id']] ?? "Nieznany",
                    'message' => $message['message'],
                    'messageId' => $message['id'],
                    'timestamp' => $message['sent_at']
                ]));

                $this->messageStorage->updateMessageStatus($message['id'], 'delivered');
            }
        }
    }


    public function onClose(ConnectionInterface $conn) {
        $userId = $this->connToUserId[$conn->resourceId] ?? null;
        if ($userId) {
            unset($this->userIdToConn[$userId]);
            unset($this->connToUserId[$conn->resourceId]);
            $this->userStatus[$userId] = 'offline';
            $this->broadcastPresence($userId, 'offline');
        }
        $this->clients->detach($conn);
        echo "Rozłączono userId={$userId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Błąd połączenia: {$e->getMessage()}\n";
        $conn->close();
    }
}

class MessageStorage {
    private $mysqli;

    public function __construct() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbName = 'SilowniaZdrowaIgla';
    ini_set('mysqli.default_socket', '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
    $this->mysqli = new mysqli($host, $user, $pass, $dbName);

    if ($this->mysqli->connect_error) {
        throw new RuntimeException('Błąd połączenia z MySQL: ' . $this->mysqli->connect_error);
    } else {
        echo "Połączenie z bazą danych udane!\n";
    }

    $this->mysqli->set_charset('utf8mb4');
}


    public function storeMessage($senderId, $receiverId, $message) {
        $stmt = $this->mysqli->prepare('INSERT INTO messages (sender_id, receiver_id, message, status, sent_at) 
                                      VALUES (?, ?, ?, "sent", NOW())');
        if (!$stmt) {
            throw new RuntimeException('Błąd przygotowania zapytania: ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('iis', $senderId, $receiverId, $message);
        if (!$stmt->execute()) {
            throw new RuntimeException('Błąd wykonania zapytania: ' . $stmt->error);
        }
        
        $messageId = $stmt->insert_id;
        $stmt->close();
        
        return $messageId;
    }

    public function getPendingMessages($receiverId) {
        $stmt = $this->mysqli->prepare('SELECT id, sender_id, message, sent_at 
                                      FROM messages 
                                      WHERE receiver_id = ? AND status = "sent"');
        if (!$stmt) {
            throw new RuntimeException('Błąd przygotowania zapytania: ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('i', $receiverId);
        if (!$stmt->execute()) {
            throw new RuntimeException('Błąd wykonania zapytania: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $messages;
    }

    public function updateMessageStatus($messageId, $status) {
        $validStatuses = ['sent', 'delivered', 'read'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->mysqli->prepare('UPDATE messages SET status = ? WHERE id = ?');
        if (!$stmt) {
            throw new RuntimeException('Błąd przygotowania zapytania: ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('si', $status, $messageId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    public function __destruct() {
        $this->mysqli->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

echo "Serwer WebSocket uruchomiony na ws://localhost:8080\n";
$server->run();
<?php
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

    // ... (pozostałe metody pozostają bez zmian aż do MessageStorage) ...

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
        echo "Błąd: {$e->getMessage()}\n";
        $conn->close();
    }
}

class MessageStorage {
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli('localhost', 'username', 'password', 'chat');
        if ($this->mysqli->connect_error) {
            throw new RuntimeException('MySQL connection error: ' . $this->mysqli->connect_error);
        }
    }

    public function storeMessage($senderId, $receiverId, $message) {
        $stmt = $this->mysqli->prepare('INSERT INTO messages (sender_id, receiver_id, message, status, sent_at) 
                                      VALUES (?, ?, ?, "sent", NOW())');
        $stmt->bind_param('iis', $senderId, $receiverId, $message);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function getPendingMessages($receiverId) {
        $stmt = $this->mysqli->prepare('SELECT * FROM messages WHERE receiver_id = ? AND status = "sent"');
        $stmt->bind_param('i', $receiverId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateMessageStatus($messageId, $status) {
        $stmt = $this->mysqli->prepare('UPDATE messages SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $messageId);
        $stmt->execute();
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

echo "WebSocket działa na ws://localhost:8080\n";
$server->run();
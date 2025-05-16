<?php

session_start();

$sesja = $_SESSION['user_id'] ?? null;

echo json_encode(
    [
    'sesja' => $sesja,
    ]
);

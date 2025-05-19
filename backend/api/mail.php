<?php

header('Content-Type: text/plain; charset=utf-8');

$to = urldecode($_GET['to'] ?? '');
$subject = urldecode($_GET['subject'] ?? '');
$text = urldecode($_GET['body'] ?? '');

$fromEmail = 'silownia.zdrowa.igla@gmail.com';
$appPassword = 'uxbpnndgujrvqbkg';

function sendEmail($to, $subject, $body, $fromEmail, $appPassword, $text)
{
    $host = 'smtp.gmail.com';
    $port = 587;
    $timeout = 30;

    $socket = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$socket) {
        return "Connection failed: $errstr ($errno)";
    }

    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '220') {
        return "Invalid greeting: $response";
    }

    fputs($socket, "EHLO $host\r\n");
    $response = fgets($socket, 512);
    while (substr($response, 3, 1) == '-') {
        $response = fgets($socket, 512);
    }
    if (substr($response, 0, 3) != '250') {
        return "EHLO failed: $response";
    }

    fputs($socket, "STARTTLS\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '220') {
        return "STARTTLS failed: $response";
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        return "TLS encryption failed";
    }

    fputs($socket, "EHLO $host\r\n");
    $response = fgets($socket, 512);
    while (substr($response, 3, 1) == '-') {
        $response = fgets($socket, 512);
    }
    if (substr($response, 0, 3) != '250') {
        return "EHLO after TLS failed: $response";
    }

    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '334') {
        return "AUTH LOGIN failed: $response";
    }

    fputs($socket, base64_encode($fromEmail) . "\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '334') {
        return "Username rejected: $response";
    }

    fputs($socket, base64_encode($appPassword) . "\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '235') {
        return "Password rejected: $response";
    }

    fputs($socket, "MAIL FROM: <$fromEmail>\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '250') {
        return "MAIL FROM failed: $response";
    }

    fputs($socket, "RCPT TO: <$to>\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '250') {
        return "RCPT TO failed: $response";
    }

    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '354') {
        return "DATA failed: $response";
    }


    $headers = "From: Siłownia Zdrowa Igła <$fromEmail>\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "Message-ID: <" . time() . "@localhost>\r\n";


    $body = <<<HTML
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Witamy!</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6;">
        <h2>$subject Siłownia Zdrowa Igła!</h2>
        <p>$text</p>

        <hr>
        <p>
            <strong>Siłownia Zdrowa Igła</strong><br>
            ul. Przykładowa 123<br>
            00-000 Warszawa<br>
            Tel: +48 123 456 789
        </p>
    </body>
    </html>
    HTML;

    $fullMessage = "Subject: $subject\r\n" . $headers . "\r\n" . $body . "\r\n.\r\n";


    fputs($socket, $fullMessage);
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '250') {
        return "Message rejected: $response";
    }

    fputs($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}

$result = sendEmail($to, $subject, $fullMessage, $fromEmail, $appPassword, $text);
echo $result === true ? 'Message sent successfully' : 'Error: ' . $result;

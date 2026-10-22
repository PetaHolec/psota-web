<?php
declare(strict_types=1);
/**
 * Contact form handler. Responds with JSON for fetch(), redirects back for plain POSTs.
 * Mail goes to the holding inbox; if mail() is unavailable the message is logged to data/messages.log.
 */
header('Content-Type: application/json; charset=utf-8');

const TO = 'info@psota-znojmo.cz';

function out(int $code, string $msg, bool $ok = false): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') out(405, 'Metoda není povolena.');

// honeypot + minimal time-to-fill check
if (!empty($_POST['website'])) out(200, 'Děkujeme za vaši zprávu.', true);
$t = (int)($_POST['t'] ?? 0);
if ($t > 0 && time() - $t < 3) out(429, 'Odesláno příliš rychle, zkuste to prosím znovu.');

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$consent = !empty($_POST['consent']);

if ($name === '' || mb_strlen($name) > 120) out(422, 'Vyplňte prosím své jméno.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) out(422, 'Zadejte prosím platný e-mail.');
if (mb_strlen($message) < 5 || mb_strlen($message) > 5000) out(422, 'Napište prosím zprávu (alespoň 5 znaků).');
if (!$consent) out(422, 'Pro odeslání je nutný souhlas se zpracováním osobních údajů.');

// header-injection safe values
$safeName = preg_replace('/[\r\n]+/', ' ', $name);
$subject  = '=?UTF-8?B?' . base64_encode('Web PSOTA – zpráva od ' . $safeName) . '?=';
$body     = "Jméno: $safeName\nE-mail: $email\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n\n$message\n";
$headers  = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: web@psota-znojmo.cz',
    'Reply-To: ' . $email,
];

$sent = @mail(TO, $subject, $body, implode("\r\n", $headers));
if (!$sent) {
    $line = date('c') . "\t" . json_encode(compact('name', 'email', 'message'), JSON_UNESCAPED_UNICODE) . "\n";
    if (@file_put_contents(__DIR__ . '/../data/messages.log', $line, FILE_APPEND | LOCK_EX) === false) {
        out(500, 'Zprávu se nepodařilo odeslat. Napište nám prosím na ' . TO . '.');
    }
}
out(200, 'Děkujeme, vaše zpráva byla odeslána. Brzy se vám ozveme.', true);

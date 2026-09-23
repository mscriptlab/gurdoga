<?php
namespace App\Core;

class Mailer
{
    public static function send(array $to, string $subject, string $htmlBody, ?string $replyTo = null): bool
    {
        $cfg = config('mail');
        $from = $cfg['from'] ?? 'no-reply@localhost';
        $fromName = $cfg['from_name'] ?? 'Website';

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::enc($fromName) . " <$from>",
        ];
        if ($replyTo) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        self::log($to, $subject, $htmlBody);

        if (empty($cfg['enabled'])) {
            return true; // logged only
        }
        $ok = true;
        foreach ($to as $addr) {
            $ok = @mail($addr, self::enc($subject), $htmlBody, implode("\r\n", $headers)) && $ok;
        }
        return $ok;
    }

    private static function enc(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }

    private static function log(array $to, string $subject, string $body): void
    {
        $dir = BASE_DIR . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] TO=%s SUBJECT=%s\n%s\n%s\n",
            date('c'), implode(',', $to), $subject, str_repeat('-', 40), $body);
        file_put_contents($dir . '/mail-' . date('Y-m') . '.log', $line . "\n", FILE_APPEND);
    }
}

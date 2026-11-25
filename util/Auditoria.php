<?php
declare(strict_types=1);

function audit(string $action, int $id): void {
    file_put_contents(
        dirname(__DIR__) . '/storage/audit.log',
        json_encode(
            [
                'date' => date(DATE_ATOM),
                'user' => $_SESSION['user']['id_usuario'] ?? null,
                'action' => $action,
                'id' => $id
            ],
            JSON_UNESCAPED_UNICODE
        ) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

<?php
declare(strict_types=1);

function csrf(): string {
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function permit(array $roles): void {
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['perfil'], $roles, true)) {
        http_response_code(403);
        fail('No tienes permiso para realizar esta operación.');
    }
}

function currentUser(): array {
    return $_SESSION['user'];
}

<?php
/**
 * logout.php
 * -------------------------------------------------------------------
 * Segurança (OWASP Top 10):
 *  - A07 (Identification and Authentication Failures): encerramento
 *    completo da sessão — limpa as variáveis de sessão, destrói a
 *    sessão no servidor E remove o cookie de sessão do navegador
 *    (passo frequentemente esquecido, que pode deixar resquícios de
 *    sessão utilizáveis em certos cenários).
 * -------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Remove o cookie de sessão do navegador, se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

header("Location: index.php");
exit;
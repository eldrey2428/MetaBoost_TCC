<?php
/**
 * header.php
 * -------------------------------------------------------------------
 * Segurança (OWASP Top 10):
 *  - A07 (Identification and Authentication Failures): sessão iniciada
 *    de forma controlada, com cookie de sessão HttpOnly (não acessível
 *    via JavaScript, mitigando roubo de sessão via XSS) e SameSite=Lax
 *    (mitiga CSRF básico).
 *  - A03 (Injection/XSS): todo dado de $_SESSION exibido na tela passa
 *    por htmlspecialchars() antes de ser impresso.
 * -------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    // Configurações do cookie de sessão ANTES de iniciar a sessão
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // Impede acesso ao cookie via JavaScript (mitiga XSS -> roubo de sessão)
        'samesite' => 'Lax',  // Mitiga ataques CSRF simples
        // 'secure' => true,   // Habilite quando o site estiver rodando em HTTPS
    ]);
    session_start();
}
?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MetaBoost</title>
  <link rel="stylesheet" href="style.css">

  <!-- CDNs para Máscaras JQuery (mantive pois você usa máscara) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
          integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <!-- Script JQuery para a máscara do telefone -->
  <script>
    $(document).ready(function(){
      $("#telefoneUsuario").mask("(00) 00000-0000");
    });
  </script>
</head>

<header class="header">
  <section>
    <a href="index.php"><img src="./img/logo.png" alt="logo" width="80"></a>

    <?php
      // Não reiniciamos a sessão aqui (já iniciada no topo)
      $primeiroNome = '';
      if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
          $idUsuario    = $_SESSION['idUsuario'] ?? null;
          $tipoUsuario  = $_SESSION['tipoUsuario'] ?? null;
          $nomeUsuario  = $_SESSION['nomeUsuario'] ?? '';
          $emailUsuario = $_SESSION['emailUsuario'] ?? '';

          $nomeCompleto = explode(' ', trim($nomeUsuario));
          // htmlspecialchars aplicado no momento da impressão, mais abaixo
          $primeiroNome = $nomeCompleto[0] ?? '';
      }
    ?>

    <!-- Menu de navegação -->
    <nav class="navbar">
      <a href="index.php#">Home</a>
      <a href="index.php#about">Sobre</a>
      <a href="index.php#menu">Anúncios</a>
    </nav>

    <div class="icons">
      <?php if (isset($_SESSION['idUsuario'])): ?>
        <!-- Só administradores podem cadastrar Anuncio -->
        <?php if (isset($_SESSION['tipoUsuario']) && $_SESSION['tipoUsuario'] === 'administrador'): ?>
          <a href="formAnuncio.php" title="Anunciar">
            <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/plus--v1.png" alt="cadastrar Anúncio"/>
          </a>
        <?php endif; ?>

        <!-- Botão de logout (aparece para qualquer usuário logado) -->
        <a href="logout.php" title="Sair">
          <img width="30" height="30" src="https://img.icons8.com/android/30/ffffff/logout-rounded.png" alt="logout"/>
        </a>

        <!-- Mensagem de boas-vindas (XSS: htmlspecialchars aplicado antes da impressão) -->
        <span style="font-size:12pt; color:white; margin-right:10px;">Olá, <?php echo htmlspecialchars($primeiroNome, ENT_QUOTES, 'UTF-8'); ?>!</span>

      <?php else: ?>
        <!-- Usuário não logado: mostra Login -->
        <a href="formLogin.php" title="Entrar">
          <img width="30" height="30" src="https://img.icons8.com/material-rounded/30/ffffff/user-male-circle.png" alt="login"/>
        </a>
      <?php endif; ?>
    </div>

    <!-- Botão Hamburguer (aparece no mobile) -->
    <div class="menu-toggle" id="menu-toggle" aria-label="Abrir menu" role="button" tabindex="0">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </section>
</header>

<!-- SCRIPT do menu (dentro do header, conforme pediu) -->
<script src="script.js"></script>
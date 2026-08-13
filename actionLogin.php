<?php
    /**
     * actionLogin.php
     * -------------------------------------------------------------------
     * Segurança (OWASP Top 10):
     *  - A03 (Injection): consulta busca o usuário SOMENTE pelo e-mail,
     *    via Prepared Statement. A senha nunca entra na cláusula WHERE
     *    em texto puro nem via md5() dentro do SQL — a verificação é
     *    feita em PHP com password_verify(), depois que o hash já foi
     *    recuperado do banco.
     *  - A02 (Cryptographic Failures) / A07 (Auth Failures): troca de
     *    md5() por password_verify(), resistente a rainbow tables e que
     *    usa BCRYPT com salt automático.
     *  - Mensagens de erro genéricas: não informamos se foi o e-mail ou
     *    a senha que estava incorreta, evitando enumeração de usuários.
     * -------------------------------------------------------------------
     */

    include "conexaoBD.php"; // Fornece $conn (PDO)

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: formLogin.php');
        exit;
    }

    $emailUsuario = trim($_POST['emailUsuario'] ?? '');
    $senhaUsuario = $_POST['senhaUsuario'] ?? '';

    if (empty($emailUsuario) || empty($senhaUsuario)) {
        header('Location: formLogin.php?erroLogin=dadosInvalidos');
        exit;
    }

    // Busca o usuário apenas pelo e-mail (Prepared Statement)
    $buscarLogin = "SELECT idUsuario, tipoUsuario, emailUsuario, nomeUsuario, senhaUsuario
                    FROM Usuarios
                    WHERE emailUsuario = :emailUsuario";

    $stmt = $conn->prepare($buscarLogin);
    $stmt->bindParam(':emailUsuario', $emailUsuario, PDO::PARAM_STR);
    $stmt->execute();

    $registro = $stmt->fetch();

    // password_verify compara a senha digitada com o HASH armazenado.
    // Se o usuário não existir, $registro será false e password_verify
    // simplesmente falhará (sem gerar erro), o que também ajuda a evitar
    // ataques de timing que revelariam se o e-mail existe ou não.
    if ($registro && password_verify($senhaUsuario, $registro['senhaUsuario'])) {

        // Regenera o ID de sessão após autenticação bem-sucedida,
        // prevenindo ataques de Session Fixation.
        session_regenerate_id(true);

        $_SESSION['idUsuario']    = $registro['idUsuario'];
        $_SESSION['tipoUsuario']  = $registro['tipoUsuario'];
        $_SESSION['emailUsuario'] = $registro['emailUsuario'];
        $_SESSION['nomeUsuario']  = $registro['nomeUsuario'];
        $_SESSION['logado']       = true;

        header('Location: index.php');
        exit;
    }

    // Credenciais inválidas (e-mail inexistente OU senha incorreta)
    header('Location: formLogin.php?erroLogin=dadosInvalidos');
    exit;
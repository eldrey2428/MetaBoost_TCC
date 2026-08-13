<!-- Inclui o header.php -->
<?php include "header.php" ?>

<section class="box-container" style="min-height: 82vh; display:flex; justify-content:center; align-items:center; padding:3rem 2rem;">

<?php

    /**
     * actionUsuario.php
     * -------------------------------------------------------------------
     * Segurança (OWASP Top 10):
     *  - A03 (Injection): INSERT feito via PDO Prepared Statement.
     *  - A02 (Cryptographic Failures): senha armazenada com
     *    password_hash($senha, PASSWORD_DEFAULT) em vez de md5().
     *  - A04/A05 (Insecure Design / Security Misconfiguration) no upload:
     *      * valida extensão E tipo MIME real do arquivo (finfo),
     *      * gera nome de arquivo aleatório (evita path traversal,
     *        sobrescrita de arquivos e injeção de extensões duplas,
     *        ex.: "foto.php.jpg"),
     *      * limita tamanho máximo,
     *      * usa move_uploaded_file (garante que o arquivo veio
     *        realmente de um upload HTTP).
     *  - A03 (XSS): toda saída de dados do usuário é tratada com
     *    htmlspecialchars() antes de ser impressa.
     * -------------------------------------------------------------------
     */

    // Função de sanitização de entradas de texto
    function filtrar_entrada($dado)
    {
        $dado = trim($dado);              // Remove espaços desnecessários
        $dado = stripslashes($dado);      // Remove barras invertidas
        $dado = htmlspecialchars($dado, ENT_QUOTES, 'UTF-8'); // Converte caracteres especiais em entidades HTML
        return $dado;
    }

    // Sanitiza e valida o upload de imagem, retornando o caminho final
    // ou lançando um erro controlado (via retorno de array com 'erro').
    function processarUploadImagem(array $arquivo, string $diretorio = 'img/'): array
    {
        $formatosPermitidos = ['jpg', 'jpeg', 'png', 'webp'];
        $mimesPermitidos = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png'  => ['png'],
            'image/webp' => ['webp'],
        ];
        $tamanhoMaximo = 5 * 1024 * 1024; // 5 MB

        if (!isset($arquivo) || $arquivo['error'] === UPLOAD_ERR_NO_FILE || $arquivo['size'] == 0) {
            return ['erro' => 'O campo FOTO é obrigatório!'];
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return ['erro' => 'Ocorreu um erro no envio da FOTO. Tente novamente.'];
        }

        if ($arquivo['size'] > $tamanhoMaximo) {
            return ['erro' => 'A FOTO deve ter tamanho máximo de 5MB!'];
        }

        // Garante que o arquivo realmente veio de um upload HTTP válido
        if (!is_uploaded_file($arquivo['tmp_name'])) {
            return ['erro' => 'Falha ao validar o arquivo enviado.'];
        }

        // Valida o TIPO MIME real do conteúdo do arquivo (não confia só na extensão)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($arquivo['tmp_name']);

        if (!array_key_exists($mimeReal, $mimesPermitidos)) {
            return ['erro' => 'A FOTO deve estar nos formatos JPG, JPEG, PNG ou WEBP!'];
        }

        $extensaoOriginal = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensaoOriginal, $formatosPermitidos, true)) {
            return ['erro' => 'Extensão de arquivo não permitida!'];
        }

        // Nome de arquivo gerado aleatoriamente (evita path traversal,
        // sobrescrita de arquivos existentes e extensões duplas maliciosas)
        $extensaoFinal = $mimesPermitidos[$mimeReal][0];
        $nomeArquivo   = bin2hex(random_bytes(16)) . '.' . $extensaoFinal;
        $caminhoFinal  = $diretorio . $nomeArquivo;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
            return ['erro' => 'Erro ao tentar mover a FOTO para o diretório ' . $diretorio . '!'];
        }

        return ['caminho' => $caminhoFinal];
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        include "conexaoBD.php"; // Fornece $conn (PDO)

        $erroPreenchimento = false;

        $nomeUsuario = $dataNascimentoUsuario = $cidadeUsuario = $telefoneUsuario = $emailUsuario = "";
        $senhaHash = "";

        // ---- Validação do campo nomeUsuario ----
        if (empty($_POST["nomeUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>NOME</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } else {
            $nomeUsuario = filtrar_entrada($_POST["nomeUsuario"]);
            if (!preg_match('/^[\p{L} ]+$/u', $nomeUsuario)) {
                echo "<div class='alert alert-warning text-center'>O campo <strong>NOME</strong> deve conter apenas letras!</div>";
                $erroPreenchimento = true;
            }
        }

        // ---- Validação do campo dataNascimentoUsuario ----
        $diaNascimentoUsuario = $mesNascimentoUsuario = $anoNascimentoUsuario = '';
        if (empty($_POST["dataNascimentoUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>DATA DE NASCIMENTO</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } else {
            $dataNascimentoUsuario = filtrar_entrada($_POST["dataNascimentoUsuario"]);

            if (strlen($dataNascimentoUsuario) == 10) {
                $diaNascimentoUsuario = substr($dataNascimentoUsuario, 8, 2);
                $mesNascimentoUsuario = substr($dataNascimentoUsuario, 5, 2);
                $anoNascimentoUsuario = substr($dataNascimentoUsuario, 0, 4);

                if (!checkdate((int)$mesNascimentoUsuario, (int)$diaNascimentoUsuario, (int)$anoNascimentoUsuario)) {
                    echo "<div class='alert alert-warning text-center'><strong>DATA INVÁLIDA</strong></div>";
                    $erroPreenchimento = true;
                }
            } else {
                echo "<div class='alert alert-warning text-center'><strong>DATA INVÁLIDA</strong></div>";
                $erroPreenchimento = true;
            }
        }

        // ---- Validação do campo cidadeUsuario ----
        if (empty($_POST["cidadeUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>CIDADE</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } else {
            $cidadeUsuario = filtrar_entrada($_POST["cidadeUsuario"]);
        }

        // ---- Validação do campo telefoneUsuario ----
        if (empty($_POST["telefoneUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>TELEFONE</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } else {
            $telefoneUsuario = filtrar_entrada($_POST["telefoneUsuario"]);
        }

        // ---- Validação do campo emailUsuario ----
        if (empty($_POST["emailUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>EMAIL</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } else {
            $emailUsuario = filtrar_entrada($_POST["emailUsuario"]);
            if (!filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='alert alert-warning text-center'>O campo <strong>EMAIL</strong> é inválido!</div>";
                $erroPreenchimento = true;
            }
        }

        // ---- Validação do campo senhaUsuario / confirmarSenhaUsuario ----
        if (empty($_POST["senhaUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>SENHA</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } elseif (empty($_POST["confirmarSenhaUsuario"])) {
            echo "<div class='alert alert-warning text-center'>O campo <strong>CONFIRMAR SENHA</strong> é obrigatório!</div>";
            $erroPreenchimento = true;
        } elseif ($_POST["senhaUsuario"] !== $_POST["confirmarSenhaUsuario"]) {
            echo "<div class='alert alert-warning text-center'>As <strong>SENHAS</strong> informadas são diferentes!</div>";
            $erroPreenchimento = true;
        } else {
            // Hash seguro da senha (BCRYPT com salt automático)
            $senhaHash = password_hash($_POST["senhaUsuario"], PASSWORD_DEFAULT);
        }

        // ---- Upload da foto (sanitizado) ----
        $erroUpload = false;
        $fotoUsuario = '';
        $resultadoUpload = processarUploadImagem($_FILES['fotoUsuario'] ?? [], 'img/');

        if (isset($resultadoUpload['erro'])) {
            echo "<div class='alert alert-warning text-center'>" . htmlspecialchars($resultadoUpload['erro'], ENT_QUOTES, 'UTF-8') . "</div>";
            $erroUpload = true;
        } else {
            $fotoUsuario = $resultadoUpload['caminho'];
        }

        // ---- Persistência (somente se não houve erros) ----
        if (!$erroPreenchimento && !$erroUpload) {

            $inserirUsuario = "INSERT INTO Usuarios
                (fotoUsuario, nomeUsuario, dataNascimentoUsuario, cidadeUsuario, telefoneUsuario, emailUsuario, senhaUsuario, tipoUsuario)
                VALUES (:fotoUsuario, :nomeUsuario, :dataNascimentoUsuario, :cidadeUsuario, :telefoneUsuario, :emailUsuario, :senhaUsuario, 'cliente')";

            try {
                $stmt = $conn->prepare($inserirUsuario);
                $stmt->bindParam(':fotoUsuario', $fotoUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':nomeUsuario', $nomeUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':dataNascimentoUsuario', $dataNascimentoUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':cidadeUsuario', $cidadeUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':telefoneUsuario', $telefoneUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':emailUsuario', $emailUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':senhaUsuario', $senhaHash, PDO::PARAM_STR);

                $stmt->execute();

                // Saída sanitizada com htmlspecialchars (defesa em profundidade,
                // mesmo os dados já tendo passado por filtrar_entrada)
                echo "<div class='box' style='border:var(--border); padding:2rem; text-align:center; background-color:var(--black); border-radius:8px;'>";
                echo "<h2 style='color:var(--main--color); margin-bottom:1rem;'>Usuário cadastrado com sucesso!</h2>";
                echo "<img src='" . htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') . "' alt='" . htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') . "' style='width:200px; border-radius:8px; margin-bottom:1rem;'><br>";
                echo "<h3 style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<h3 style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars("$diaNascimentoUsuario/$mesNascimentoUsuario/$anoNascimentoUsuario", ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<p style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars($cidadeUsuario, ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars($emailUsuario, ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars($telefoneUsuario, ENT_QUOTES, 'UTF-8') . "</p>";
                echo "</div>";

            } catch (PDOException $e) {
                error_log("Erro ao cadastrar usuário: " . $e->getMessage());
                echo "<div style='color:#e74c3c; font-weight:700; text-align:center;'>Erro ao cadastrar Usuário. Tente novamente mais tarde.</div>";
            }
        }

    } else {
        header("Location: formUsuario.php");
        exit;
    }
?>

</section>

<!-- Inclui o footer.php -->
<?php include "footer.php" ?>
<?php
include "header.php";
?>

<section class="box-container" style="min-height: 80vh; display:flex; justify-content:center; align-items:center; padding:5rem 2rem;">

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    include_once "conexaoBD.php"; // Fornece $conn (PDO)

    // Verifica se o usuário está autenticado
    if (!isset($_SESSION['idUsuario'])) {
        echo "<div class='alert alert-danger text-center'>Acesso negado. Faça login para cadastrar anúncios.</div>";
        echo "</section>";
        include "footer.php";
        exit;
    }

    $idUsuarioLogado = (int) $_SESSION['idUsuario'];
    $erroPreenchimento = false;

    // Captura e sanitização das entradas de texto
    $nomeAnuncio      = trim($_POST["nomeAnuncio"] ?? "");
    $descricaoAnuncio = trim($_POST["descricaoAnuncio"] ?? "");
    $categoriaAnuncio = filter_var($_POST["categoriaAnuncio"] ?? null, FILTER_VALIDATE_INT);
    $valorAnuncio     = filter_var($_POST["valorAnuncio"] ?? null, FILTER_VALIDATE_FLOAT);

    if (empty($nomeAnuncio) || empty($descricaoAnuncio) || !$categoriaAnuncio || $valorAnuncio === false || $valorAnuncio <= 0) {
        echo "<div class='alert alert-warning text-center'>Todos os campos são obrigatórios e devem conter valores válidos!</div>";
        $erroPreenchimento = true;
    }

    // Processamento seguro do upload de imagem
    $erroUpload  = false;
    $fotoAnuncio = "";

    if (!isset($_FILES['fotoAnuncio']) || $_FILES['fotoAnuncio']['error'] === UPLOAD_ERR_NO_FILE || $_FILES['fotoAnuncio']['size'] == 0) {
        echo "<div class='alert alert-warning text-center'>A foto do anúncio é obrigatória!</div>";
        $erroUpload = true;
    } else {
        $arquivo = $_FILES['fotoAnuncio'];
        $tamanhoMaximo = 5 * 1024 * 1024; // 5 MB
        $mimesPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            echo "<div class='alert alert-warning text-center'>Erro no envio da foto. Tente novamente.</div>";
            $erroUpload = true;
        } elseif ($arquivo['size'] > $tamanhoMaximo) {
            echo "<div class='alert alert-warning text-center'>A foto deve ter no máximo 5MB!</div>";
            $erroUpload = true;
        } elseif (!is_uploaded_file($arquivo['tmp_name'])) {
            echo "<div class='alert alert-warning text-center'>Envio de arquivo inválido.</div>";
            $erroUpload = true;
        } else {
            // Valida o tipo MIME real do arquivo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeReal = $finfo->file($arquivo['tmp_name']);

            if (!array_key_exists($mimeReal, $mimesPermitidos)) {
                echo "<div class='alert alert-warning text-center'>Formato inválido! Use apenas JPG, PNG ou WEBP.</div>";
                $erroUpload = true;
            } else {
                // Nome aleatório para evitar colisão e Path Traversal
                $extensao     = $mimesPermitidos[$mimeReal];
                $nomeUnico    = bin2hex(random_bytes(16)) . '.' . $extensao;
                $diretorio    = "img/";
                $caminhoFinal = $diretorio . $nomeUnico;

                if (move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
                    $fotoAnuncio = $caminhoFinal;
                } else {
                    echo "<div class='alert alert-warning text-center'>Erro ao salvar a foto no servidor!</div>";
                    $erroUpload = true;
                }
            }
        }
    }

    // Persistência no banco via PDO Prepared Statements
    if (!$erroPreenchimento && !$erroUpload) {
        $sql = "INSERT INTO Anuncios 
                (fotoAnuncio, Usuarios_idUsuario, Categorias_idCategoria, nomeAnuncio, descricaoAnuncio, valorAnuncio, statusAnuncio) 
                VALUES 
                (:fotoAnuncio, :idUsuario, :idCategoria, :nomeAnuncio, :descricaoAnuncio, :valorAnuncio, 'disponivel')";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':fotoAnuncio', $fotoAnuncio, PDO::PARAM_STR);
            $stmt->bindParam(':idUsuario', $idUsuarioLogado, PDO::PARAM_INT);
            $stmt->bindParam(':idCategoria', $categoriaAnuncio, PDO::PARAM_INT);
            $stmt->bindParam(':nomeAnuncio', $nomeAnuncio, PDO::PARAM_STR);
            $stmt->bindParam(':descricaoAnuncio', $descricaoAnuncio, PDO::PARAM_STR);
            $stmt->bindParam(':valorAnuncio', $valorAnuncio);
            $stmt->execute();

            // Exibição sanitizada com htmlspecialchars
            echo "<div class='box' style='border:var(--border); padding:2rem; text-align:center; background-color:var(--black); border-radius:8px;'>";
            echo "<h2 style='color:var(--main--color); margin-bottom:1rem;'>Anúncio cadastrado com sucesso!</h2>";
            echo "<img src='" . htmlspecialchars($fotoAnuncio, ENT_QUOTES, 'UTF-8') . "' alt='" . htmlspecialchars($nomeAnuncio, ENT_QUOTES, 'UTF-8') . "' style='width:200px; border-radius:8px; margin-bottom:1rem;'><br>";
            echo "<h3 style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars($nomeAnuncio, ENT_QUOTES, 'UTF-8') . "</h3>";
            echo "<p style='color:#fff; margin-bottom:0.5rem;'>" . htmlspecialchars($descricaoAnuncio, ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p class='price' style='color:var(--main--color); font-size:2rem;'>R$ " . number_format($valorAnuncio, 2, ',', '.') . "</p>";
            echo "</div>";

        } catch (PDOException $e) {
            error_log("Erro ao cadastrar anúncio: " . $e->getMessage());
            echo "<div class='alert alert-danger text-center'>Erro interno ao cadastrar o anúncio. Tente novamente mais tarde.</div>";
        }
    }

} else {
    header("Location: formAnuncio.php");
    exit;
}
?>

</section>

<?php include "footer.php"; ?>
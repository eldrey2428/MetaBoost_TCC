<?php
include "conexaoBD.php"; // Fornece $conn (PDO)
include "header.php";
?>

<section class="anuncio-detalhe" style="padding-top: 120px; min-height: 80vh;">
  <?php
  if (isset($_GET['idAnuncio']) && ctype_digit((string) $_GET['idAnuncio'])) {

      $idAnuncio = (int) $_GET['idAnuncio'];

      // -------------------------------------------------------------
      // A03 (Injection): consulta parametrizada com Prepared Statement.
      // O valor de $idAnuncio nunca é concatenado diretamente na SQL.
      // -------------------------------------------------------------
      $exibirAnuncio = "
          SELECT a.*, u.nomeUsuario, u.telefoneUsuario
          FROM Anuncios a
          JOIN Usuarios u ON a.Usuarios_idUsuario = u.idUsuario
          WHERE a.idAnuncio = :idAnuncio
      ";

      try {
          $stmt = $conn->prepare($exibirAnuncio);
          $stmt->bindParam(':idAnuncio', $idAnuncio, PDO::PARAM_INT);
          $stmt->execute();
      } catch (PDOException $e) {
          error_log("Erro ao consultar anúncio: " . $e->getMessage());
          die("<p style='color:white;'>Erro ao carregar o anúncio. Tente novamente mais tarde.</p>");
      }

      if ($stmt->rowCount() > 0) {
          $registro = $stmt->fetch();

          // -----------------------------------------------------------
          // A03 (XSS): todo dado vindo do banco é escapado antes de ser
          // impresso na página com htmlspecialchars().
          // -----------------------------------------------------------
          $fotoAnuncio      = htmlspecialchars($registro['fotoAnuncio'], ENT_QUOTES, 'UTF-8');
          $nomeAnuncio      = htmlspecialchars($registro['nomeAnuncio'], ENT_QUOTES, 'UTF-8');
          $descricaoAnuncio = htmlspecialchars($registro['descricaoAnuncio'], ENT_QUOTES, 'UTF-8');
          $valorAnuncio     = htmlspecialchars(number_format((float)$registro['valorAnuncio'], 2, ',', '.'), ENT_QUOTES, 'UTF-8');
          $statusAnuncio    = $registro['statusAnuncio'];
          $nomeUsuario      = htmlspecialchars($registro['nomeUsuario'], ENT_QUOTES, 'UTF-8');
          $telefoneUsuario  = $registro['telefoneUsuario'];

          // Tratamento do número para WhatsApp: mantém apenas dígitos
          // e garante o DDI 55, eliminando qualquer possibilidade de
          // injeção de parâmetros extras na URL do wa.me
          $numeroWhatsApp = preg_replace('/\D/', '', $telefoneUsuario);
          if (substr($numeroWhatsApp, 0, 2) !== '55') {
              $numeroWhatsApp = '55' . $numeroWhatsApp;
          }
          $numeroWhatsApp = htmlspecialchars($numeroWhatsApp, ENT_QUOTES, 'UTF-8');
  ?>
          <div class="Anuncio-container">

            <!-- FOTO -->
            <div class="anuncio-img">
              <?php if ($statusAnuncio === 'esgotado'): ?>
                <div class="badge-esgotado">ESGOTADO</div>
              <?php endif; ?>
              <img src="<?= $fotoAnuncio ?>" alt="<?= $nomeAnuncio ?>">
            </div>

            <!-- INFORMAÇÕES -->
            <div class="anuncio-info">
              <h1 style="font-size: 50px;"><?= $nomeAnuncio ?></h1>
              <h1 style="font-size:16px;">
                <span style="color:#ffffff;">Anunciado por: </span>
                <span style="color:#88d3d9;"><?= $nomeUsuario ?></span>
              </h1>

              <p class="descricao"><?= $descricaoAnuncio ?></p>
              <div class="preco">R$ <?= $valorAnuncio ?></div>

              <?php if ($statusAnuncio !== 'esgotado'): ?>
                <a href="https://wa.me/<?= $numeroWhatsApp ?>" target="_blank" class="btn-responsivo">Falar com Vendedor</a>
              <?php else: ?>
                <div class="btn esgotado-btn">Anúncio esgotado</div>
              <?php endif; ?>
            </div>
          </div>
  <?php
      } else {
          echo "<p style='color:white;'>Anúncio não localizado!</p>";
      }

  } else {
      echo "<p style='color:white;'>ID de Anúncio inválido!</p>";
  }
  ?>
</section>

<?php include "footer.php"; ?>
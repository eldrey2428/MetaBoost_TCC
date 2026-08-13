<?php
include "conexaoBD.php"; // Fornece $conn (PDO)
include "header.php";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;700&display=swap" rel="stylesheet">
  <title>Todos os Anúncios - MetaBoost</title>
  <link rel="icon" type="image/png" href="img/logo.png">
</head>

<body>
    <section class="menu" id="menu">
      <h2 class="title" style="margin-top: 100px;">Todos os <span>Anúncios</span></h2>

      <?php
      // -------------------------------------------------------------
      // A03 (Injection): a lista de valores aceitos para o filtro é
      // restrita a uma whitelist. Mesmo assim, a consulta é montada
      // com Prepared Statement, então nenhum valor vindo do usuário
      // é concatenado diretamente na SQL.
      // -------------------------------------------------------------
      $filtrosValidos = ['todos', 'disponivel', 'esgotado'];

      $filtroAnuncio = 'todos';
      if (isset($_GET['filtroAnuncio']) && in_array($_GET['filtroAnuncio'], $filtrosValidos, true)) {
          $filtroAnuncio = $_GET['filtroAnuncio'];
      }

      switch ($filtroAnuncio) {
          case "disponivel":
              $mensagemFiltro = "disponíveis";
              break;
          case "esgotado":
              $mensagemFiltro = "esgotados";
              break;
          default:
              $mensagemFiltro = "no total";
              break;
      }

      if ($filtroAnuncio === 'todos') {
          $listarAnuncios = "SELECT * FROM Anuncios";
          $stmt = $conn->prepare($listarAnuncios);
          $stmt->execute();
      } else {
          $listarAnuncios = "SELECT * FROM Anuncios WHERE statusAnuncio = :statusAnuncio";
          $stmt = $conn->prepare($listarAnuncios);
          $stmt->bindParam(':statusAnuncio', $filtroAnuncio, PDO::PARAM_STR);
          $stmt->execute();
      }

      $totalAnuncios = $stmt->rowCount();

      if ($totalAnuncios > 0) {
          echo "
          <div style='
              background: linear-gradient(135deg, #88d3d9, #03767eff);
              color: #fff;
              text-align: center;
              padding: 15px 25px;
              border-radius: 12px;
              margin: 20px auto;
              max-width: 600px;
              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              font-size: 18px;
              font-weight: 500;
          '>
            🚀 Há <strong>$totalAnuncios</strong> anúncios $mensagemFiltro cadastrados!
          </div>";
      } else {
          echo "
          <div style='
              background: linear-gradient(135deg, #dc3545, #a71d2a);
              color: #fff;
              text-align: center;
              padding: 15px 25px;
              border-radius: 12px;
              margin: 20px auto;
              max-width: 600px;
              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              font-size: 18px;
              font-weight: 500;
          '>
            ❌ Não há anúncios cadastrados no sistema!
          </div>";
      }
      ?>

      <form name='formFiltro' action='gridAnuncios.php' method='GET' style="margin: 20px 0;">
      <div style="display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap;">
          <select name='filtroAnuncio' required
                  style="
                      padding: 10px 15px;
                      border-radius: 8px;
                      border: 1px solid #88d3d9;
                      background-color: #fff;
                      color: #333;
                      min-width: 220px;
                      font-size: 16px;
                      font-weight: 500;
                      cursor: pointer;
                  ">
              <option value='todos' <?php if($filtroAnuncio == 'todos') echo "selected"; ?>>Exibir todos os anúncios</option>
              <option value='disponivel' <?php if($filtroAnuncio == 'disponivel') echo "selected"; ?>>Exibir apenas disponíveis</option>
              <option value='esgotado' <?php if($filtroAnuncio == 'esgotado') echo "selected"; ?>>Exibir apenas esgotados</option>
          </select>
          <button class='button' type='submit'
                  style="
                      padding: 1rem 3rem;
                      border-radius: 8px;
                      border: 1px solid #88d3d9;
                      background-color: #88d3d9;
                      color: #000000;
                      font-size: 1.7rem;
                      font-weight: bold;
                      cursor: pointer;
                  ">
              Filtrar
          </button>
      </div>
  </form>


      <div class="box-container">
      <?php
        if ($totalAnuncios > 0) {
          while ($registro = $stmt->fetch()) {
            // A03 (XSS): saída sempre sanitizada com htmlspecialchars()
            $idAnuncio        = (int) $registro['idAnuncio'];
            $fotoAnuncio      = htmlspecialchars($registro['fotoAnuncio'], ENT_QUOTES, 'UTF-8');
            $nomeAnuncio      = htmlspecialchars($registro['nomeAnuncio'], ENT_QUOTES, 'UTF-8');
            $valorAnuncio     = htmlspecialchars(number_format((float)$registro['valorAnuncio'], 2, ',', '.'), ENT_QUOTES, 'UTF-8');
            $statusAnuncio    = $registro['statusAnuncio'];

            echo "
              <div class='box'>
                <a href='visualizarAnuncio.php?idAnuncio=$idAnuncio'>
                  <img src='$fotoAnuncio' alt='$nomeAnuncio' width='180'>
                </a>
                <h3>$nomeAnuncio</h3>
                <div class='price'>R$ $valorAnuncio</div>";
                if($statusAnuncio != 'esgotado'){
                  echo "<a href='visualizarAnuncio.php?idAnuncio=$idAnuncio' class='btn'>Visualizar Anúncio</a>";
                } else {
                  echo "<a href='visualizarAnuncio.php?idAnuncio=$idAnuncio' class='btn' style='background: #888;'>Esgotado</a>";
                }
            echo "</div>";
          }
        }
      ?>
      </div>
    </section>

  <?php
  include "footer.php";
  ?>
</body>
</html>
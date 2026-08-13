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
    <title>MetaBoost</title>
    <link rel="icon" type="image/png" href="img/logo.png">
  </head>

  <body>
      <div class="home-container">
        <section>
          <div class="content">
            <h2>METABOOST</h2><h3> OS MELHORES SUPLEMENTOS PARA GAMERS</h3>
            <p>Aumente seu foco, energia e resistência com os melhores suplementos do mercado.
              Seja para treinar ou maratonar aquele game, o MetaBoost te dá a força que você precisa para alcançar o próximo nível. </p>
            <a href="#menu" class="btn-conheca">Conheça os Anuncios</a>
          </div>
        </section>
      </div>

      <section class="about" id="about">
        <h2 class="title">Sobre <span>Nós</span></h2>
        <div class="row">
          <div>
            <img src="./img/foto1.png" alt="Sobre Nós">
          </div>
          <div class="content">
            <h3>Quem Somos Nós</h3>
            <p>O MetaBoost é um marketplace de suplementos pensado para quem passa longas horas em frente ao computador,
              seja jogando ou trabalhando, cuidando do seu corpo mesmo nos momentos de maior concentração.</p>
            <p>Este projeto surgiu a partir das aulas de Programação Web e Banco de Dados dos professores Paulo Ricardo e Jailton Junior, e foi desenvolvido pelos alunos Eldrey Santos, João Mendes e Livia Brum da turma de Técnico em Informática para Internet III,
              no IFPR – Campus Telêmaco Borba, unindo aprendizado e prática para criar uma solução inovadora.</p>
            <a href="https://github.com/JoaoMendss/metaboostfullstack" target="_blank" class="btn">Saiba Mais</a>
          </div>
        </div>
      </section>

      <section class="menu" id="menu">
        <h2 class="title">Últimos <span>Anuncios</span></h2>

        <div class="box-container">

    <?php
      // -------------------------------------------------------------------
      // Consulta segura com PDO Prepared Statement (não há entrada do
      // usuário aqui, mas o padrão prepare/execute é mantido em todo o
      // sistema por consistência e para evitar regressões futuras).
      // -------------------------------------------------------------------
      $listarAnuncios = "SELECT * FROM Anuncios ORDER BY idAnuncio DESC LIMIT 6";
      $stmt = $conn->prepare($listarAnuncios);
      $stmt->execute();

      if ($stmt->rowCount() > 0) {
          while ($registro = $stmt->fetch()) {
              $idAnuncio        = (int) $registro['idAnuncio'];
              $fotoAnuncio      = htmlspecialchars($registro['fotoAnuncio'], ENT_QUOTES, 'UTF-8');
              $nomeAnuncio      = htmlspecialchars($registro['nomeAnuncio'], ENT_QUOTES, 'UTF-8');
              $valorAnuncio     = htmlspecialchars(number_format((float)$registro['valorAnuncio'], 2, ',', '.'), ENT_QUOTES, 'UTF-8');
              $statusAnuncio    = $registro['statusAnuncio']; // usado apenas em comparação lógica, não impresso diretamente

              echo "
                <div class='box'>
                  <a href='visualizarAnuncio.php?idAnuncio=$idAnuncio'>
                    <img src='$fotoAnuncio' alt='$nomeAnuncio' width='180'>
                  </a>
                  <h3>$nomeAnuncio</h3>
                  <div class='price'>R$ $valorAnuncio</div>";

                  if ($statusAnuncio !== 'esgotado') {
                      echo "<a href='visualizarAnuncio.php?idAnuncio=$idAnuncio' class='btn'>Visualizar Anuncio</a>";
                  } else {
                      echo "<a href='visualizarAnuncio.php?idAnuncio=$idAnuncio' class='btn' style='background: #888;'>Esgotado</a>";
                  }

              echo "</div>";
          }
      } else {
          echo "<p style='color:white;'>Nenhum Anuncio encontrado.</p>";
      }
    ?>

        </div>
        <div style="text-align:center; margin-top:20px;">
          <a href="gridAnuncios.php" class="btn-responsivo">Ver Todos os Anuncios</a>
        </div>
      </section>

      <section class="review" id="review">
        <!-- Avaliações -->
      </section>

      <section class="address" id="address">
        <!-- Endereço -->
      </section>

    <?php
    include "footer.php";
    ?>
  </body>
</html>
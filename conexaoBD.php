<?php
    /**
     * conexaoBD.php
     * -------------------------------------------------------------------
     * Conexão com o banco de dados via PDO (PHP Data Objects).
     *
     * Segurança (OWASP Top 10):
     *  - A01/A03 (Injection): PDO com Prepared Statements elimina SQL
     *    Injection em todas as consultas que usarem este objeto $conn.
     *  - PDO::ERRMODE_EXCEPTION faz com que qualquer erro de banco lance
     *    uma PDOException, tratada aqui de forma controlada (sem expor
     *    detalhes sensíveis de infraestrutura ao usuário final).
     *  - Implementado como singleton: garante que exista apenas UMA
     *    conexão ativa por requisição, evitando desperdício de recursos.
     * -------------------------------------------------------------------
     */

    class ConexaoBD
    {
        private static ?PDO $instancia = null;

        // Dados de acesso ao banco (ajuste conforme seu ambiente)
        private static string $hostBD   = "localhost";
        private static string $userBD   = "root";
        private static string $senhaBD  = "";
        private static string $database = "metaboost";

        // Impede a criação de instâncias diretas (padrão Singleton)
        private function __construct() {}

        public static function getConexao(): PDO
        {
            if (self::$instancia === null) {
                try {
                    $dsn = "mysql:host=" . self::$hostBD . ";dbname=" . self::$database . ";charset=utf8mb4";

                    $opcoes = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,       // Lança exceções em erros
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,             // Retorna arrays associativos
                        PDO::ATTR_EMULATE_PREPARES   => false,                       // Usa prepared statements reais do driver (mais seguro contra SQLi)
                    ];

                    self::$instancia = new PDO($dsn, self::$userBD, self::$senhaBD, $opcoes);
                } catch (PDOException $e) {
                    // Nunca exibir mensagens detalhadas de erro de BD ao usuário final em produção.
                    // Aqui exibimos uma mensagem genérica; o detalhe pode ser logado internamente.
                    error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
                    die("<p>Não foi possível conectar à base de dados no momento. Tente novamente mais tarde.</p>");
                }
            }

            return self::$instancia;
        }
    }

    // Mantém compatibilidade com o restante do sistema, que utiliza a
    // variável global $conn para executar consultas.
    $conn = ConexaoBD::getConexao();
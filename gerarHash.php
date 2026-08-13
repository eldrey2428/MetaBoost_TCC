<?php
    /**
     * gerar_hash.php
     * -------------------------------------------------------------------
     * Utilitário de uso único (linha de comando ou navegador) para gerar
     * hashes BCRYPT válidos que podem ser colados diretamente no arquivo
     * schema.sql, substituindo os hashes MD5 antigos.
     *
     * Uso via terminal:
     *   php gerar_hash.php
     *
     * Este arquivo NÃO deve ser publicado em produção — é apenas uma
     * ferramenta de apoio para a migração de senhas do TCC.
     * -------------------------------------------------------------------
     */

    $senhasTeste = [
        'eldrey@gmail.com' => '123456',
        'joao@gmail.com'   => '123456',
        'livia@gmail.com'  => '123456',
    ];

    foreach ($senhasTeste as $email => $senhaPlana) {
        $hash = password_hash($senhaPlana, PASSWORD_DEFAULT);
        echo "Email: {$email}\n";
        echo "Senha (texto puro, apenas para teste): {$senhaPlana}\n";
        echo "Hash BCRYPT: {$hash}\n";
        echo str_repeat('-', 70) . "\n";
    }
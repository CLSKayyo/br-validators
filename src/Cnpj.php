<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators;

class Cnpj
{
    /**
     * Verifica se o valor é um CNPJ válido, numérico ou alfanumérico.
     *
     * Aceita o CNPJ com ou sem máscara: pontos, hífens, barras e espaços são removidos
     * antes da validação. Depois disso, o valor precisa ter 12 caracteres [0-9A-Z]
     * seguidos de 2 dígitos verificadores, não pode ser uma sequência de um único dígito
     * repetido e os dígitos verificadores precisam conferir (módulo 11, conforme a
     * IN RFB nº 2.229/2024). Letras minúsculas não são aceitas.
     *
     * @param string $value CNPJ a validar, ex.: '11.222.333/0001-81' ou '12.ABC.345/01DE-35'.
     *
     * @return bool true se o CNPJ for válido; false caso contrário. Nunca lança exceção.
     */
    public static function isValid(string $value): bool
    {
        $cnpj = str_replace(['.', '-', '/', ' '], '', $value);

        if (preg_match('/^[0-9A-Z]{12}\d{2}\z/', $cnpj) !== 1) {
            return false;
        }

        if (str_repeat($cnpj[0], 14) === $cnpj) {
            return false;
        }

        return self::checkDigit($cnpj, 12) === (int) $cnpj[12] && self::checkDigit($cnpj, 13) === (int) $cnpj[13];
    }

    /**
     * Cada caractere vale ord($char) - 48 ('0'-'9' => 0-9, 'A'-'Z' => 17-42) e os pesos
     * vão de 2 a 9, da direita para a esquerda, recomeçando em 2 depois do 9.
     */
    private static function checkDigit(string $cnpj, int $length): int
    {
        $sum = 0;

        for ($i = 0; $i < $length; $i++) {
            $sum += (ord($cnpj[$i]) - 48) * (2 + ($length - 1 - $i) % 8);
        }

        $rest = $sum % 11;

        return $rest < 2 ? 0 : 11 - $rest;
    }
}

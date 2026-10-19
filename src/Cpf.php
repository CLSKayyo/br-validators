<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators;

class Cpf
{
    /**
     * Verifica se o valor é um CPF válido.
     *
     * Aceita o CPF com ou sem máscara: pontos, hífens e espaços são removidos antes da
     * validação. Depois disso, o valor precisa ter exatamente 11 dígitos, não pode ser
     * uma sequência de um único dígito repetido (ex.: 111.111.111-11) e os dois dígitos
     * verificadores precisam conferir (módulo 11).
     *
     * @param string $value CPF a validar, ex.: '529.982.247-25' ou '52998224725'.
     *
     * @return bool true se o CPF for válido; false caso contrário. Nunca lança exceção.
     */
    public static function isValid(string $value): bool
    {
        $cpf = str_replace(['.', '-', ' '], '', $value);

        if (preg_match('/^\d{11}\z/', $cpf) !== 1) {
            return false;
        }

        if (str_repeat($cpf[0], 11) === $cpf) {
            return false;
        }

        return self::checkDigit($cpf, 9) === (int) $cpf[9] && self::checkDigit($cpf, 10) === (int) $cpf[10];
    }

    private static function checkDigit(string $cpf, int $length): int
    {
        $sum = 0;

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $cpf[$i] * ($length + 1 - $i);
        }

        $rest = $sum % 11;

        return $rest < 2 ? 0 : 11 - $rest;
    }
}

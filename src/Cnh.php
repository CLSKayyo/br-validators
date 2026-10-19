<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators;

class Cnh
{
    /**
     * Verifica se o valor é um número de registro de CNH válido.
     *
     * Aceita o número com ou sem separadores: pontos, hífens e espaços são removidos antes
     * da validação. Depois disso, o valor precisa ter exatamente 11 dígitos, não pode ser
     * uma sequência de um único dígito repetido (ex.: 11111111111) e os dois dígitos
     * verificadores precisam conferir (módulo 11).
     *
     * @param string $value Número de registro da CNH, ex.: '58316794534'.
     *
     * @return bool true se a CNH for válida; false caso contrário. Nunca lança exceção.
     */
    public static function isValid(string $value): bool
    {
        $cnh = str_replace(['.', '-', ' '], '', $value);

        if (preg_match('/^\d{11}\z/', $cnh) !== 1) {
            return false;
        }

        if (str_repeat($cnh[0], 11) === $cnh) {
            return false;
        }

        [$firstDigit, $secondDigit] = self::checkDigits($cnh);

        return $firstDigit === (int) $cnh[9] && $secondDigit === (int) $cnh[10];
    }

    /**
     * Os dois DVs são calculados sobre os 9 primeiros dígitos: o primeiro com pesos de 9 a 1
     * e o segundo com pesos de 1 a 9. Quando o resto do primeiro é 10, o DV vira 0 e o resto
     * do segundo sofre um desconto de 2 (somando 11 se ficar negativo).
     *
     * @return array{int, int}
     */
    private static function checkDigits(string $cnh): array
    {
        $firstSum = 0;
        $secondSum = 0;

        for ($i = 0; $i < 9; $i++) {
            $firstSum += (int) $cnh[$i] * (9 - $i);
            $secondSum += (int) $cnh[$i] * ($i + 1);
        }

        $firstDigit = $firstSum % 11;
        $discount = 0;

        if ($firstDigit >= 10) {
            $firstDigit = 0;
            $discount = 2;
        }

        $secondDigit = ($secondSum % 11 - $discount + 11) % 11;

        return [$firstDigit, $secondDigit >= 10 ? 0 : $secondDigit];
    }
}

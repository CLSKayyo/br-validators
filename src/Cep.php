<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators;

class Cep
{
    /**
     * Faixas de CEP de cada UF, com início e fim inclusivos.
     */
    private const UF_RANGES = [
        'AC' => [[69900000, 69999999]],
        'AL' => [[57000000, 57999999]],
        'AM' => [[69000000, 69299999], [69400000, 69899999]],
        'AP' => [[68900000, 68999999]],
        'BA' => [[40000000, 48999999]],
        'CE' => [[60000000, 63999999]],
        'DF' => [[70000000, 72799999], [73000000, 73699999]],
        'ES' => [[29000000, 29999999]],
        'GO' => [[72800000, 72999999], [73700000, 76799999]],
        'MA' => [[65000000, 65999999]],
        'MG' => [[30000000, 39999999]],
        'MS' => [[79000000, 79999999]],
        'MT' => [[78000000, 78899999]],
        'PA' => [[66000000, 68899999]],
        'PB' => [[58000000, 58999999]],
        'PE' => [[50000000, 56999999]],
        'PI' => [[64000000, 64999999]],
        'PR' => [[80000000, 87999999]],
        'RJ' => [[20000000, 28999999]],
        'RN' => [[59000000, 59999999]],
        'RO' => [[76800000, 76999999]],
        'RR' => [[69300000, 69399999]],
        'RS' => [[90000000, 99999999]],
        'SC' => [[88000000, 89999999]],
        'SE' => [[49000000, 49999999]],
        'SP' => [[1000000, 19999999]],
        'TO' => [[77000000, 77999999]],
    ];

    /**
     * Verifica se o valor tem o formato de um CEP válido.
     *
     * Aceita o CEP com ou sem máscara: pontos, hífens e espaços são removidos antes da
     * validação. Depois disso, o valor precisa ter exatamente 8 dígitos e ser no mínimo
     * 01000-000, o início da menor faixa em uso. O CEP não tem dígito verificador, então
     * não é possível saber se ele existe de fato sem consultar a base dos Correios.
     *
     * @param string $value CEP a validar, ex.: '01310-100' ou '01310100'.
     *
     * @return bool true se o CEP for válido; false caso contrário. Nunca lança exceção.
     */
    public static function isValid(string $value): bool
    {
        $cep = str_replace(['.', '-', ' '], '', $value);

        if (preg_match('/^\d{8}\z/', $cep) !== 1) {
            return false;
        }

        return (int) $cep >= 1000000;
    }

    /**
     * Verifica se o valor é um CEP válido pertencente às faixas da UF informada.
     *
     * Aplica as mesmas regras de isValid() e, em seguida, confere se o CEP está em uma
     * das faixas da UF. Algumas UFs têm mais de uma faixa (AM, DF e GO).
     *
     * @param string $value CEP a validar, ex.: '01310-100' ou '01310100'.
     * @param string $uf    Sigla da UF, ex.: 'SP'. Maiúsculas e minúsculas são aceitas.
     *
     * @return bool true se o CEP for válido e pertencer à UF; false caso contrário.
     *
     * @throws \InvalidArgumentException Se $uf não for uma sigla de UF existente.
     */
    public static function isValidForUf(string $value, string $uf): bool
    {
        $uf = strtoupper($uf);

        if (!array_key_exists($uf, self::UF_RANGES)) {
            throw new \InvalidArgumentException('Invalid UF.');
        }

        if (!self::isValid($value)) {
            return false;
        }

        $cep = (int) str_replace(['.', '-', ' '], '', $value);

        foreach (self::UF_RANGES[$uf] as [$start, $end]) {
            if ($cep >= $start && $cep <= $end) {
                return true;
            }
        }

        return false;
    }
}

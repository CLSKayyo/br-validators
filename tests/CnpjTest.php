<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators\Tests;

use CLSKayyo\BrValidators\Cnpj;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cnpj::class)]
final class CnpjTest extends TestCase
{
    #[DataProvider('validNumericCnpjProvider')]
    #[DataProvider('validAlphanumericCnpjProvider')]
    public function testIsValidReturnsTrueForValidCnpj(string $cnpj): void
    {
        self::assertTrue(Cnpj::isValid($cnpj));
    }

    #[DataProvider('invalidCheckDigitProvider')]
    public function testIsValidReturnsFalseForWrongCheckDigits(string $cnpj): void
    {
        self::assertFalse(Cnpj::isValid($cnpj));
    }

    #[DataProvider('repeatedDigitsProvider')]
    public function testIsValidReturnsFalseForRepeatedDigits(string $cnpj): void
    {
        self::assertFalse(Cnpj::isValid($cnpj));
    }

    #[DataProvider('malformedProvider')]
    public function testIsValidReturnsFalseForMalformedInput(string $cnpj): void
    {
        self::assertFalse(Cnpj::isValid($cnpj));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validNumericCnpjProvider(): array
    {
        return [
            'numérico: sem máscara' => ['11222333000181'],
            'numérico: com máscara' => ['11.222.333/0001-81'],
            'numérico: filial diferente de 0001' => ['11.444.777/0001-61'],
            'numérico: zeros à esquerda' => ['00.000.000/0001-91'],
            'numérico: primeiro DV igual a zero (resto < 2)' => ['10.000.000/8000-01'],
            'numérico: segundo DV igual a zero (resto < 2)' => ['10.000.000/7000-40'],
            'numérico: ambos os DVs iguais a zero' => ['10.000.001/9000-00'],
            'numérico: máscara parcial' => ['11.222.333000181'],
            'numérico: separado por espaços' => ['11 222 333 0001 81'],
            'numérico: espaços nas pontas' => [' 11.222.333/0001-81 '],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validAlphanumericCnpjProvider(): array
    {
        return [
            'alfanumérico: exemplo oficial da Receita sem máscara' => ['12ABC34501DE35'],
            'alfanumérico: exemplo oficial da Receita com máscara' => ['12.ABC.345/01DE-35'],
            'alfanumérico: base só com letras' => ['AB.CDE.FGH/IJKL-80'],
            'alfanumérico: maior valor de caractere (Z)' => ['ZZ.ZZZ.ZZZ/ZZZZ-62'],
            'alfanumérico: primeiro DV igual a zero (resto < 2)' => ['AB.000.005/CD01-07'],
            'alfanumérico: segundo DV igual a zero (resto < 2)' => ['AB.000.001/CD01-10'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidCheckDigitProvider(): array
    {
        return [
            'numérico: primeiro DV errado' => ['11.222.333/0001-91'],
            'numérico: segundo DV errado' => ['11.222.333/0001-82'],
            'numérico: DVs invertidos' => ['11.222.333/0001-18'],
            'numérico: dígito da base alterado' => ['11.222.334/0001-81'],
            'alfanumérico: primeiro DV errado' => ['12.ABC.345/01DE-45'],
            'alfanumérico: segundo DV errado' => ['12.ABC.345/01DE-36'],
            'alfanumérico: DVs invertidos' => ['12.ABC.345/01DE-53'],
            'alfanumérico: letra da base alterada' => ['12.ABD.345/01DE-35'],
            'alfanumérico: letra trocada por dígito' => ['12.0BC.345/01DE-35'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function repeatedDigitsProvider(): array
    {
        $cases = [];

        foreach (range(0, 9) as $digit) {
            $cnpj = str_repeat((string) $digit, 14);
            $masked = sprintf(
                '%s.%s.%s/%s-%s',
                substr($cnpj, 0, 2),
                substr($cnpj, 2, 3),
                substr($cnpj, 5, 3),
                substr($cnpj, 8, 4),
                substr($cnpj, 12, 2),
            );

            $cases["{$cnpj} sem máscara"] = [$cnpj];
            $cases["{$masked} com máscara"] = [$masked];
        }

        return $cases;
    }

    /**
     * @return array<string, array{string}>
     */
    public static function malformedProvider(): array
    {
        return [
            'string vazia' => [''],
            'só espaços' => ['   '],
            'só máscara' => ['../-'],
            '13 caracteres' => ['1122233300018'],
            '15 caracteres' => ['112223330001810'],
            'tamanho de CPF' => ['529.982.247-25'],
            'letra minúscula' => ['12.abc.345/01de-35'],
            'letra no primeiro DV' => ['12.ABC.345/01DE-A5'],
            'letra no segundo DV' => ['12.ABC.345/01DE-3A'],
            'letras repetidas' => ['AAAAAAAAAAAAAA'],
            'letra acentuada' => ['12.ÁBC.345/01DE-35'],
            'caractere especial' => ['12.AB#.345/01DE-35'],
            'separador não suportado (vírgula)' => ['11,222,333/0001-81'],
            'tab' => ["11222333\t000181"],
            'quebra de linha no final' => ["11222333000181\n"],
            'dígitos unicode (fullwidth)' => ['１１２２２３３３０００１８１'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators\Tests;

use CLSKayyo\BrValidators\Cpf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cpf::class)]
final class CpfTest extends TestCase
{
    #[DataProvider('validCpfProvider')]
    public function testIsValidReturnsTrueForValidCpf(string $cpf): void
    {
        self::assertTrue(Cpf::isValid($cpf));
    }

    #[DataProvider('invalidCheckDigitProvider')]
    public function testIsValidReturnsFalseForWrongCheckDigits(string $cpf): void
    {
        self::assertFalse(Cpf::isValid($cpf));
    }

    #[DataProvider('repeatedDigitsProvider')]
    public function testIsValidReturnsFalseForRepeatedDigits(string $cpf): void
    {
        self::assertFalse(Cpf::isValid($cpf));
    }

    #[DataProvider('malformedProvider')]
    public function testIsValidReturnsFalseForMalformedInput(string $cpf): void
    {
        self::assertFalse(Cpf::isValid($cpf));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validCpfProvider(): array
    {
        return [
            'sem máscara' => ['52998224725'],
            'com máscara' => ['529.982.247-25'],
            'zeros à esquerda' => ['000.000.001-91'],
            'primeiro DV igual a zero (resto < 2)' => ['100.000.001-08'],
            'segundo DV igual a zero (resto < 2)' => ['100.000.002-80'],
            'ambos os DVs iguais a zero' => ['987.654.321-00'],
            'máscara parcial' => ['529.98224725'],
            'separado por espaços' => ['529 982 247 25'],
            'espaços nas pontas' => [' 529.982.247-25 '],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidCheckDigitProvider(): array
    {
        return [
            'primeiro DV errado' => ['529.982.247-35'],
            'segundo DV errado' => ['529.982.247-26'],
            'DVs invertidos' => ['529.982.247-52'],
            'ambos os DVs errados' => ['529.982.247-00'],
            'dígito da base alterado' => ['529.982.248-25'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function repeatedDigitsProvider(): array
    {
        $cases = [];

        foreach (range(0, 9) as $digit) {
            $cpf = str_repeat((string) $digit, 11);
            $masked = sprintf('%s.%s.%s-%s', substr($cpf, 0, 3), substr($cpf, 3, 3), substr($cpf, 6, 3), substr($cpf, 9, 2));

            $cases["{$cpf} sem máscara"] = [$cpf];
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
            'só máscara' => ['..-'],
            '10 dígitos' => ['5299822472'],
            '12 dígitos' => ['529982247250'],
            'letra no lugar de dígito' => ['529.982.247-2a'],
            'só letras' => ['abcdefghijk'],
            'separador não suportado (vírgula)' => ['529,982,247-25'],
            'tab' => ["529\t98224725"],
            'quebra de linha no final' => ["52998224725\n"],
            'dígitos unicode (fullwidth)' => ['５２９９８２２４７２５'],
            'número negativo' => ['-5299822472'],
        ];
    }
}

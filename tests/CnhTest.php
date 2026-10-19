<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators\Tests;

use CLSKayyo\BrValidators\Cnh;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cnh::class)]
final class CnhTest extends TestCase
{
    #[DataProvider('validCnhProvider')]
    public function testIsValidReturnsTrueForValidCnh(string $cnh): void
    {
        self::assertTrue(Cnh::isValid($cnh));
    }

    #[DataProvider('invalidCheckDigitProvider')]
    public function testIsValidReturnsFalseForWrongCheckDigits(string $cnh): void
    {
        self::assertFalse(Cnh::isValid($cnh));
    }

    #[DataProvider('repeatedDigitsProvider')]
    public function testIsValidReturnsFalseForRepeatedDigits(string $cnh): void
    {
        self::assertFalse(Cnh::isValid($cnh));
    }

    #[DataProvider('malformedProvider')]
    public function testIsValidReturnsFalseForMalformedInput(string $cnh): void
    {
        self::assertFalse(Cnh::isValid($cnh));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validCnhProvider(): array
    {
        return [
            'sem separadores' => ['58316794534'],
            'com hífen antes dos DVs' => ['583167945-34'],
            'separado por espaços' => ['583 167 945 34'],
            'espaços nas pontas' => [' 58316794534 '],
            'zeros à esquerda' => ['00000000119'],
            'primeiro DV igual a zero (resto 0)' => ['78784641406'],
            'segundo DV igual a zero (resto 10)' => ['14389002600'],
            'ambos os DVs iguais a zero' => ['12345678900'],
            'desconto aplicado no segundo DV' => ['67009495003'],
            'desconto com resto negativo (resto 0 vira 9)' => ['60938526609'],
            'desconto com resto negativo (resto 1 vira 10, depois 0)' => ['72019823100'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidCheckDigitProvider(): array
    {
        return [
            'primeiro DV errado' => ['58316794544'],
            'segundo DV errado' => ['58316794535'],
            'DVs invertidos' => ['58316794543'],
            'dígito da base alterado' => ['58316794634'],
            'segundo DV sem o desconto' => ['67009495005'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function repeatedDigitsProvider(): array
    {
        $cases = [];

        foreach (range(0, 9) as $digit) {
            $cnh = str_repeat((string) $digit, 11);

            $cases["dígito {$digit} repetido"] = [$cnh];
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
            'só separadores' => ['.-'],
            '10 dígitos' => ['5831679453'],
            '12 dígitos' => ['583167945340'],
            'letra no lugar de dígito' => ['5831679453A'],
            'só letras' => ['abcdefghijk'],
            'separador não suportado (barra)' => ['583167945/34'],
            'tab' => ["583167945\t34"],
            'quebra de linha no final' => ["58316794534\n"],
            'dígitos unicode (fullwidth)' => ['５８３１６７９４５３４'],
        ];
    }
}

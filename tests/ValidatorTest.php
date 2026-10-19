<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators\Tests;

use CLSKayyo\BrValidators\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
final class ValidatorTest extends TestCase
{
    #[DataProvider('dispatchProvider')]
    public function testIsValidDispatchesToFieldTypeValidator(string $value, string $fieldType, bool $expected): void
    {
        self::assertSame($expected, Validator::isValid($value, $fieldType));
    }

    #[DataProvider('invalidFieldTypeProvider')]
    public function testIsValidThrowsForInvalidFieldType(string $fieldType): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type.');

        Validator::isValid('529.982.247-25', $fieldType);
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function dispatchProvider(): array
    {
        return [
            'CEP válido' => ['01310-100', Validator::CEP, true],
            'CEP inválido' => ['00999-999', Validator::CEP, false],
            'CNH válida' => ['58316794534', Validator::CNH, true],
            'CNH inválida' => ['58316794535', Validator::CNH, false],
            'CPF válido' => ['529.982.247-25', Validator::CPF, true],
            'CPF inválido' => ['529.982.247-26', Validator::CPF, false],
            'CNPJ numérico válido' => ['11.222.333/0001-81', Validator::CNPJ, true],
            'CNPJ alfanumérico válido' => ['12.ABC.345/01DE-35', Validator::CNPJ, true],
            'CNPJ inválido' => ['11.222.333/0001-82', Validator::CNPJ, false],
            'CNPJ validado como CPF' => ['11.222.333/0001-81', Validator::CPF, false],
            'CPF validado como CNPJ' => ['529.982.247-25', Validator::CNPJ, false],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidFieldTypeProvider(): array
    {
        return [
            'tipo desconhecido' => ['Rg'],
            'string vazia' => [''],
            'minúsculo' => ['cpf'],
            'maiúsculo' => ['CPF'],
            'nome completo da classe' => ['CLSKayyo\\BrValidators\\Cpf'],
        ];
    }
}

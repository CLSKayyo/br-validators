<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators;

class Validator
{
    public const CEP = 'Cep';
    public const CNH = 'Cnh';
    public const CNPJ = 'Cnpj';
    public const CPF = 'Cpf';

    /**
     * Valida o valor usando o validador do tipo de documento informado.
     *
     * @param string $value     Documento a validar, com ou sem máscara.
     * @param string $fieldType Tipo do documento: Validator::CEP, Validator::CNH,
     *                          Validator::CNPJ ou Validator::CPF.
     *
     * @return bool true se o documento for válido para o tipo informado; false caso contrário.
     *
     * @throws \InvalidArgumentException Se $fieldType não for um tipo suportado.
     */
    public static function isValid(string $value, string $fieldType): bool
    {
        return match ($fieldType) {
            self::CEP => Cep::isValid($value),
            self::CNH => Cnh::isValid($value),
            self::CNPJ => Cnpj::isValid($value),
            self::CPF => Cpf::isValid($value),
            default => throw new \InvalidArgumentException('Invalid field type.'),
        };
    }
}

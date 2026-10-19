<?php

declare(strict_types=1);

namespace CLSKayyo\BrValidators\Tests;

use CLSKayyo\BrValidators\Cep;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cep::class)]
final class CepTest extends TestCase
{
    #[DataProvider('validCepProvider')]
    public function testIsValidReturnsTrueForValidCep(string $cep): void
    {
        self::assertTrue(Cep::isValid($cep));
    }

    #[DataProvider('invalidCepProvider')]
    public function testIsValidReturnsFalseForInvalidCep(string $cep): void
    {
        self::assertFalse(Cep::isValid($cep));
    }

    #[DataProvider('ufRangeBoundaryProvider')]
    #[DataProvider('ufCaseProvider')]
    public function testIsValidForUfReturnsTrueForCepInUfRange(string $cep, string $uf): void
    {
        self::assertTrue(Cep::isValidForUf($cep, $uf));
    }

    #[DataProvider('cepOutsideUfProvider')]
    public function testIsValidForUfReturnsFalseForCepOutsideUfRange(string $cep, string $uf): void
    {
        self::assertFalse(Cep::isValidForUf($cep, $uf));
    }

    #[DataProvider('invalidCepProvider')]
    public function testIsValidForUfReturnsFalseForInvalidCep(string $cep): void
    {
        self::assertFalse(Cep::isValidForUf($cep, 'SP'));
    }

    #[DataProvider('invalidUfProvider')]
    public function testIsValidForUfThrowsForInvalidUf(string $cep, string $uf): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UF.');

        Cep::isValidForUf($cep, $uf);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validCepProvider(): array
    {
        return [
            'sem máscara' => ['01310100'],
            'com máscara' => ['01310-100'],
            'com ponto' => ['01.310-100'],
            'separado por espaço' => ['01310 100'],
            'espaços nas pontas' => [' 01310-100 '],
            'menor CEP aceito' => ['01000-000'],
            'maior CEP aceito' => ['99999-999'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidCepProvider(): array
    {
        return [
            'abaixo da menor faixa' => ['00999-999'],
            'só zeros' => ['00000-000'],
            'string vazia' => [''],
            'só espaços' => ['   '],
            'só máscara' => ['-'],
            '7 dígitos' => ['0131010'],
            '9 dígitos' => ['013101000'],
            'letra no lugar de dígito' => ['01310-10A'],
            'separador não suportado (barra)' => ['01310/100'],
            'tab' => ["01310\t100"],
            'quebra de linha no final' => ["01310100\n"],
            'dígitos unicode (fullwidth)' => ['０１３１０１００'],
        ];
    }

    /**
     * Início e fim de cada faixa de cada UF.
     *
     * @return array<string, array{string, string}>
     */
    public static function ufRangeBoundaryProvider(): array
    {
        return [
            'AC início' => ['69900-000', 'AC'],
            'AC fim' => ['69999-999', 'AC'],
            'AL início' => ['57000-000', 'AL'],
            'AL fim' => ['57999-999', 'AL'],
            'AM 1ª faixa início' => ['69000-000', 'AM'],
            'AM 1ª faixa fim' => ['69299-999', 'AM'],
            'AM 2ª faixa início' => ['69400-000', 'AM'],
            'AM 2ª faixa fim' => ['69899-999', 'AM'],
            'AP início' => ['68900-000', 'AP'],
            'AP fim' => ['68999-999', 'AP'],
            'BA início' => ['40000-000', 'BA'],
            'BA fim' => ['48999-999', 'BA'],
            'CE início' => ['60000-000', 'CE'],
            'CE fim' => ['63999-999', 'CE'],
            'DF 1ª faixa início' => ['70000-000', 'DF'],
            'DF 1ª faixa fim' => ['72799-999', 'DF'],
            'DF 2ª faixa início' => ['73000-000', 'DF'],
            'DF 2ª faixa fim' => ['73699-999', 'DF'],
            'ES início' => ['29000-000', 'ES'],
            'ES fim' => ['29999-999', 'ES'],
            'GO 1ª faixa início' => ['72800-000', 'GO'],
            'GO 1ª faixa fim' => ['72999-999', 'GO'],
            'GO 2ª faixa início' => ['73700-000', 'GO'],
            'GO 2ª faixa fim' => ['76799-999', 'GO'],
            'MA início' => ['65000-000', 'MA'],
            'MA fim' => ['65999-999', 'MA'],
            'MG início' => ['30000-000', 'MG'],
            'MG fim' => ['39999-999', 'MG'],
            'MS início' => ['79000-000', 'MS'],
            'MS fim' => ['79999-999', 'MS'],
            'MT início' => ['78000-000', 'MT'],
            'MT fim' => ['78899-999', 'MT'],
            'PA início' => ['66000-000', 'PA'],
            'PA fim' => ['68899-999', 'PA'],
            'PB início' => ['58000-000', 'PB'],
            'PB fim' => ['58999-999', 'PB'],
            'PE início' => ['50000-000', 'PE'],
            'PE fim' => ['56999-999', 'PE'],
            'PI início' => ['64000-000', 'PI'],
            'PI fim' => ['64999-999', 'PI'],
            'PR início' => ['80000-000', 'PR'],
            'PR fim' => ['87999-999', 'PR'],
            'RJ início' => ['20000-000', 'RJ'],
            'RJ fim' => ['28999-999', 'RJ'],
            'RN início' => ['59000-000', 'RN'],
            'RN fim' => ['59999-999', 'RN'],
            'RO início' => ['76800-000', 'RO'],
            'RO fim' => ['76999-999', 'RO'],
            'RR início' => ['69300-000', 'RR'],
            'RR fim' => ['69399-999', 'RR'],
            'RS início' => ['90000-000', 'RS'],
            'RS fim' => ['99999-999', 'RS'],
            'SC início' => ['88000-000', 'SC'],
            'SC fim' => ['89999-999', 'SC'],
            'SE início' => ['49000-000', 'SE'],
            'SE fim' => ['49999-999', 'SE'],
            'SP início' => ['01000-000', 'SP'],
            'SP fim' => ['19999-999', 'SP'],
            'TO início' => ['77000-000', 'TO'],
            'TO fim' => ['77999-999', 'TO'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function ufCaseProvider(): array
    {
        return [
            'UF minúscula' => ['01310-100', 'sp'],
            'UF com maiúscula e minúscula' => ['01310-100', 'Sp'],
            'CEP sem máscara' => ['01310100', 'SP'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function cepOutsideUfProvider(): array
    {
        return [
            'CEP de SP para RJ' => ['01310-100', 'RJ'],
            'logo depois do fim de SP' => ['20000-000', 'SP'],
            'logo antes do início de RJ' => ['19999-999', 'RJ'],
            'CEP de RR para AM (RR fica entre as faixas de AM)' => ['69300-000', 'AM'],
            'CEP de AM para RR' => ['69299-999', 'RR'],
            'CEP de GO entre as faixas do DF' => ['72800-000', 'DF'],
            'CEP de GO depois da 2ª faixa do DF' => ['73700-000', 'DF'],
            'CEP do DF antes da 1ª faixa de GO' => ['72799-999', 'GO'],
            'CEP do DF entre as faixas de GO' => ['73000-000', 'GO'],
            'CEP de RO para GO' => ['76800-000', 'GO'],
            'faixa sem UF (78900-000) para MT' => ['78900-000', 'MT'],
            'faixa sem UF (78900-000) para RO' => ['78900-000', 'RO'],
            'faixa sem UF (78999-999) para MS' => ['78999-999', 'MS'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidUfProvider(): array
    {
        return [
            'sigla inexistente' => ['01310-100', 'XX'],
            'string vazia' => ['01310-100', ''],
            'uma letra' => ['01310-100', 'S'],
            'três letras' => ['01310-100', 'SPA'],
            'com espaço' => ['01310-100', ' SP'],
            'nome por extenso' => ['01310-100', 'São Paulo'],
            'UF inválida com CEP inválido' => ['abc', 'XX'],
        ];
    }
}

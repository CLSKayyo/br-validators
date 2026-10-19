# br-validators

Validação de documentos brasileiros para PHP, sem dependências em runtime: CPF, CNPJ (numérico e alfanumérico), CEP e CNH.

> **Em desenvolvimento.** Ainda não há versão publicada e a API pode mudar; veja o [PRD](PRD.md) para o escopo da v0.1.

## Requisitos

- PHP 8.2 ou superior

## Instalação

```bash
composer require clskayyo/br-validators
```

## Uso

Todos os métodos são estáticos, recebem `string` e aceitam o documento com ou sem máscara.

### CPF

```php
use CLSKayyo\BrValidators\Cpf;

Cpf::isValid('529.982.247-25'); // true
Cpf::isValid('52998224725');    // true
Cpf::isValid('111.111.111-11'); // false (sequência repetida)
Cpf::isValid('529.982.247-26'); // false (dígito verificador errado)
```

### CNPJ

Suporta o CNPJ alfanumérico (IN RFB nº 2.229/2024), emitido para novas inscrições desde julho de 2026.

```php
use CLSKayyo\BrValidators\Cnpj;

Cnpj::isValid('11.222.333/0001-81'); // true (numérico)
Cnpj::isValid('12.ABC.345/01DE-35'); // true (alfanumérico)
Cnpj::isValid('12.abc.345/01de-35'); // false (letras precisam ser maiúsculas)
```

### CEP

O CEP não tem dígito verificador: `isValid` confere o formato e `isValidForUf` confere se o CEP está nas faixas da UF. Nenhum dos dois consulta a base dos Correios.

```php
use CLSKayyo\BrValidators\Cep;

Cep::isValid('01310-100');             // true
Cep::isValidForUf('01310-100', 'SP');  // true
Cep::isValidForUf('01310-100', 'rj');  // false (a UF aceita minúsculas)
Cep::isValidForUf('01310-100', 'XX');  // lança \InvalidArgumentException
```

### CNH

```php
use CLSKayyo\BrValidators\Cnh;

Cnh::isValid('58316794534'); // true
Cnh::isValid('58316794535'); // false (dígito verificador errado)
```

### Validator

Valida qualquer documento suportado a partir do tipo:

```php
use CLSKayyo\BrValidators\Validator;

Validator::isValid('529.982.247-25', Validator::CPF);      // true
Validator::isValid('12.ABC.345/01DE-35', Validator::CNPJ); // true
Validator::isValid('01310-100', Validator::CEP);           // true
Validator::isValid('58316794534', Validator::CNH);         // true
Validator::isValid('123', 'Rg');                           // lança \InvalidArgumentException
```

## Comportamento

| Documento | Separadores removidos | Regras |
|---|---|---|
| CPF | `.` `-` espaço | 11 dígitos, sem sequência repetida, 2 dígitos verificadores (módulo 11) |
| CNPJ | `.` `-` `/` espaço | 12 caracteres `0-9`/`A-Z` + 2 dígitos verificadores, sem sequência repetida |
| CEP | `.` `-` espaço | 8 dígitos, a partir de `01000-000`; `isValidForUf` confere as faixas da UF |
| CNH | `.` `-` espaço | 11 dígitos, sem sequência repetida, 2 dígitos verificadores |

Os métodos `isValid` nunca lançam exceção: entrada malformada retorna `false`. Só lançam `\InvalidArgumentException` o `Validator::isValid`, com tipo desconhecido, e o `Cep::isValidForUf`, com UF inexistente.

## Desenvolvimento

```bash
composer install
composer test      # PHPUnit
composer analyse   # PHPStan (level max)
composer check     # ambos
```

## Licença

[MIT](LICENSE)

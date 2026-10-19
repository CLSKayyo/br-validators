# PRD — `clskayyo/br-validators` v0.1

## 1. Problema

Sistemas PHP brasileiros precisam validar e formatar CPF, CNPJ, CEP e CNH em praticamente todo cadastro. As opções hoje costumam:

- vir acopladas a um framework (regras de Laravel/Symfony), puxando dependências desnecessárias;
- estar sem manutenção ou sem tipagem estrita / análise estática;
- **não suportar o CNPJ alfanumérico** (IN RFB nº 2.229/2024), que passou a ser emitido para novas inscrições a partir de julho de 2026. Um validador apenas numérico hoje **rejeita CNPJs legítimos**.

Objetivo: um pacote pequeno, sem dependências em runtime, com API previsível, 100% testado e com análise estática no nível máximo — útil em produção e demonstrativo como portfólio.

## 2. Critérios de sucesso

1. `composer require clskayyo/br-validators` instala a v0.1.0 a partir do Packagist em projeto PHP 8.2+ sem trazer nenhuma dependência.
2. CI no GitHub Actions verde em PHP **8.2, 8.3, 8.4 e 8.5** (testes + PHPStan + `composer validate --strict`).
3. PHPStan no nível **max** (PHPStan 2.x) sem erros e sem baseline.
4. Cobertura de linhas e branches de 100% em `src/`.
5. CNPJ alfanumérico validado corretamente, incluindo o exemplo oficial da Receita `12.ABC.345/01DE-35`.
6. Todos os exemplos do README executados por um teste (README não pode ficar desatualizado).
7. Tag `v0.1.0` publicada, CHANGELOG no formato *Keep a Changelog*, licença MIT.

## 3. Escopo

### Dentro (v0.1)

**Documentos:** `Cpf`, `Cnpj` (numérico **e** alfanumérico), `Cep` e `Cnh`, além da fachada `Validator`, que despacha para o validador do tipo informado.

**API** — classes com métodos estáticos, um arquivo por documento em `src/`:

```php
use CLSKayyo\BrValidators\Cep;
use CLSKayyo\BrValidators\Cnh;
use CLSKayyo\BrValidators\Cnpj;
use CLSKayyo\BrValidators\Cpf;
use CLSKayyo\BrValidators\Validator;

Cpf::isValid('529.982.247-25');      // true
Cpf::isValid('111.111.111-11');      // false (sequência repetida)

Cnpj::isValid('11.222.333/0001-81'); // true (numérico)
Cnpj::isValid('12.ABC.345/01DE-35'); // true (alfanumérico)

Cep::isValid('01310-100');           // true
Cep::isValidForUf('01310-100', 'SP'); // true
Cep::isValidForUf('01310-100', 'RJ'); // false

Cnh::isValid('58316794534');         // true

Validator::isValid('529.982.247-25', Validator::CPF); // true
Validator::isValid('x', 'Rg');       // lança \InvalidArgumentException
```

**Status:** `isValid()` está implementado e testado nos quatro documentos, junto com `Cep::isValidForUf()` e `Validator::isValid()`. `format()`, `unformat()` e `generate()` continuam no escopo da v0.1, mas ainda não foram implementados.

**Regras de comportamento (contrato da API):**

| Método | Comportamento |
|---|---|
| `Cpf::isValid(string): bool` | Remove `.` `-` e espaços; exige 11 dígitos; rejeita sequências repetidas; confere os 2 DVs (módulo 11). Nunca lança exceção. |
| `Cnpj::isValid(string): bool` | Remove `.` `-` `/` e espaços; exige 12 chars `[0-9A-Z]` + 2 dígitos verificadores; rejeita sequências repetidas e letras minúsculas; confere os DVs com valor `ord(c) - 48` e pesos 2–9 da direita para a esquerda. Nunca lança exceção. |
| `Cep::isValid(string): bool` | Remove `.` `-` e espaços; exige 8 dígitos, a partir de `01000-000`. Só confere o formato, porque o CEP não tem DV. Nunca lança exceção. |
| `Cep::isValidForUf(string, string): bool` | As regras de `isValid()` e, além disso, o CEP precisa estar em uma das faixas da UF (AM, DF e GO têm duas faixas). A UF aceita maiúsculas e minúsculas; sigla inexistente lança `\InvalidArgumentException`. |
| `Cnh::isValid(string): bool` | Remove `.` `-` e espaços; exige 11 dígitos; rejeita sequências repetidas; confere os 2 DVs (pesos 9–1 e 1–9, com desconto de 2 no segundo DV quando o resto do primeiro é 10). Nunca lança exceção. |
| `Validator::isValid(string, string): bool` | Despacha para o validador de `Validator::CEP`, `CNH`, `CNPJ` ou `CPF`. Tipo desconhecido lança `\InvalidArgumentException`. |
| `format(string): string` *(planejado)* | Aplica a máscara. Comportamento com entrada inválida em aberto (pergunta 1). |
| `unformat(string): string` *(planejado)* | Remove a máscara. Não valida. |
| `generate(): string` *(planejado)* | Gera documento válido aleatório via `random_int`, **apenas para testes/fixtures**. |

- Apenas `string` como entrada (int perde zeros à esquerda). `declare(strict_types=1)` em todos os arquivos.
- As regexes terminam em `\z`, não em `$`, para rejeitar `\n` no final.
- Faixas de CEP por UF segundo a tabela do [ruacep.com.br](https://www.ruacep.com.br/guias/postais/faixa-de-cep-por-estado/) (os Correios não publicam a tabela); `78900-000`–`78999-999` não pertence a nenhuma UF.
- Algoritmo da CNH conferido contra validate-docbr e validation-br (resultados idênticos em 3 milhões de números); o Respect\Validation diverge em cerca de 0,8% dos casos e não foi seguido.

**`generate()` entra na v0.1** — justificativa: custo marginal mínimo (reaproveita o cálculo de DV que já existe), é muito usado em seeders/factories e permite testes de propriedade no próprio pacote (`isValid(generate()) === true` em N iterações). Risco de mau uso é baixo (Faker e geradores online fazem o mesmo) e mitigado por documentação.

**Infra do repositório:**
- `composer.json` (PSR-4, `require: php ^8.2`, `require-dev`: PHPUnit 11, PHPStan 2.x, scripts `test`, `analyse`, `check`)
- `phpunit.xml.dist`, `phpstan.neon.dist` (level max, `src` + `tests`)
- `.github/workflows/ci.yml` com matriz 8.2–8.5 (validate, install, PHPUnit, PHPStan); checagem de cobertura pendente (etapa 4)
- `.gitattributes` com `export-ignore` para tests/CI/configs (pacote enxuto no `--prefer-dist`)
- `.editorconfig`, `.gitignore`, `LICENSE` (MIT), `README.md`, `CHANGELOG.md`

### Fora (v0.1) — roadmap

- Outros documentos: chave Pix, PIS/PASEP/NIT, RENAVAM, título de eleitor, placa (Mercosul), inscrição estadual, telefone, boleto.
- Consulta de existência de CEP na base dos Correios.
- Integrações com frameworks (Laravel Rule, Symfony Constraint), atributos PHP de validação.
- Motivo detalhado da invalidade (`validate()` retornando erro), mensagens/i18n.
- Mascaramento progressivo de entrada parcial (responsabilidade de front-end).
- Consulta online à Receita (situação cadastral).
- Mutation testing (Infection) e php-cs-fixer — candidatos para v0.2.

## 4. Restrições

- PHP >= 8.2; **zero dependências em runtime** (nem extensões além do core: sem `mbstring`, sem `ctype` obrigatório).
- Sem estado global, sem I/O; funções puras exceto `generate()` (CSPRNG via `random_int`).
- Semver: enquanto `0.x`, mudanças de API podem ocorrer em minor; documentado no README.
- Ambiente local **não tem PHP nem Composer instalados** (só Docker e git). Desenvolvimento local será via Docker (`composer:2` e `php:8.x-cli`); o CI é a fonte de verdade da matriz de versões.
- Publicação no GitHub/Packagist depende de ações suas (criar repositório, conectar Packagist). `gh` não está instalado.

## 5. Plano de implementação

1. ~~**Scaffold**~~ ✅ — `composer.json`, configs de PHPUnit/PHPStan, CI, `.gitattributes`, `.editorconfig`, `.gitignore`, `LICENSE`, README e CHANGELOG iniciais.
2. ~~**Validators `isValid` e testes**~~ ✅ — `Cpf`, `Cnpj` (com alfanumérico), `Cep` (com `isValidForUf`), `Cnh` e `Validator`, cada um com seus testes.
3. **`format()`, `unformat()` e `generate()`** *(etapa atual)* — depende da pergunta 1.
4. **Docs** — README com exemplos e tabela de comportamento; CHANGELOG com seção `[0.1.0]`.
5. **CI completo** — adicionar checagem de cobertura (100%) quando houver código.
6. **Release** — commit, repo `clskayyo/br-validators` no GitHub, push, registro no Packagist (com webhook), tag `v0.1.0` após CI verde.

## 6. Perguntas em aberto

1. `format()` com entrada inválida: lançar exceção, formatar só checando tamanho/charset, ou devolver a entrada intacta?
2. CNH não tem máscara oficial: manter a remoção de `.` `-` e espaços (como no CPF) ou aceitar só dígitos?

### Decisões tomadas

- CEP e CNH entram na v0.1.
- Máscara parcial ou separadores em posição livre (`529.98224725`, `529 982 247 25`) são aceitos: `isValid` só remove os separadores.
- CNPJ com letras minúsculas é rejeitado (a Receita só define maiúsculas).
- Ambiente local via Docker (`composer:2`, `php:8.x-cli`).
- Commits como `Caio Lucas <clsdraco@gmail.com>`; vendor/handle `clskayyo`.

---

## 7. Verificação (fim da v0.1)

- `docker run --rm -v $PWD:/app -w /app composer:2 composer check` → PHPUnit + PHPStan verdes.
- Rodar a suíte em `php:8.2-cli` e `php:8.5-cli` localmente para checar a faixa de versões.
- Relatório de cobertura = 100% em `src/`.
- `composer validate --strict` sem avisos; `git archive` confirma que tests/CI não vão no dist.
- CI do GitHub verde na matriz completa antes de criar a tag `v0.1.0`.

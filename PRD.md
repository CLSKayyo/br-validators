# PRD — `caiolucas/br-validators` v0.1

## 1. Problema

Sistemas PHP brasileiros precisam validar e formatar CPF e CNPJ em praticamente todo cadastro. As opções hoje costumam:

- vir acopladas a um framework (regras de Laravel/Symfony), puxando dependências desnecessárias;
- estar sem manutenção ou sem tipagem estrita / análise estática;
- **não suportar o CNPJ alfanumérico** (IN RFB nº 2.229/2024), que passou a ser emitido para novas inscrições a partir de julho de 2026. Um validador apenas numérico hoje **rejeita CNPJs legítimos**.

Objetivo: um pacote pequeno, sem dependências em runtime, com API previsível, 100% testado e com análise estática no nível máximo — útil em produção e demonstrativo como portfólio.

## 2. Critérios de sucesso

1. `composer require caiolucas/br-validators` instala a v0.1.0 a partir do Packagist em projeto PHP 8.2+ sem trazer nenhuma dependência.
2. CI no GitHub Actions verde em PHP **8.2, 8.3, 8.4 e 8.5** (testes + PHPStan + `composer validate --strict`).
3. PHPStan no nível **max** (PHPStan 2.x) sem erros e sem baseline.
4. Cobertura de linhas e branches de 100% em `src/`.
5. CNPJ alfanumérico validado corretamente, incluindo o exemplo oficial da Receita `12.ABC.345/01DE-35`.
6. Todos os exemplos do README executados por um teste (README não pode ficar desatualizado).
7. Tag `v0.1.0` publicada, CHANGELOG no formato *Keep a Changelog*, licença MIT.

## 3. Escopo

### Dentro (v0.1)

**Documentos:** `Cpf` e `Cnpj` (numérico **e** alfanumérico).

**API** — classes `final` com métodos estáticos e construtor privado, todas implementando `CaioLucas\BrValidators\Contracts\Document`:

```php
use CaioLucas\BrValidators\Cpf;
use CaioLucas\BrValidators\Cnpj;

Cpf::isValid('529.982.247-25');     // true
Cpf::isValid('52998224725');        // true
Cpf::isValid('111.111.111-11');     // false (sequência repetida)
Cpf::format('52998224725');         // '529.982.247-25'
Cpf::unformat('529.982.247-25');    // '52998224725'
Cpf::generate();                    // '...' 11 dígitos válidos
Cpf::generate(formatted: true);     // '###.###.###-##'

Cnpj::isValid('12.ABC.345/01DE-35'); // true (alfanumérico)
Cnpj::isValid('11.222.333/0001-81'); // true (numérico)
Cnpj::format('12ABC34501DE35');      // '12.ABC.345/01DE-35'
Cnpj::generate(alphanumeric: true);
```

**Regras de comportamento (contrato da API):**

| Método | Entrada | Comportamento |
|---|---|---|
| `isValid(string): bool` | com ou sem máscara | Remove apenas `.` `-` `/` e espaços; exige tamanho e charset corretos (CPF: 11 dígitos; CNPJ: 12 chars `[0-9A-Z]` + 2 dígitos verificadores); rejeita sequências repetidas; confere DVs (módulo 11). Nunca lança exceção. |
| `unformat(string): string` | qualquer | Remove `.` `-` `/` e espaços. Não valida. |
| `format(string): string` | com ou sem máscara | Aplica a máscara. Lança `InvalidDocumentException` se o documento não for válido (ver pergunta 2). |
| `generate(bool $formatted = false): string` | — | Gera documento válido aleatório via `random_int`. CNPJ tem também `bool $alphanumeric = false`. Documentado como **apenas para testes/fixtures**. |

- Apenas `string` como entrada (int perde zeros à esquerda). `declare(strict_types=1)` em todos os arquivos.
- Exceção própria: `CaioLucas\BrValidators\Exceptions\InvalidDocumentException extends \InvalidArgumentException`.

**`generate()` entra na v0.1** — justificativa: custo marginal mínimo (reaproveita o cálculo de DV que já existe), é muito usado em seeders/factories e permite testes de propriedade no próprio pacote (`isValid(generate()) === true` em N iterações). Risco de mau uso é baixo (Faker e geradores online fazem o mesmo) e mitigado por documentação.

**Infra do repositório:**
- `composer.json` (PSR-4, `require: php ^8.2`, `require-dev`: PHPUnit 11, PHPStan 2.x, scripts `test`, `analyse`, `check`)
- `phpunit.xml.dist`, `phpstan.neon.dist` (level max, `src` + `tests`)
- `.github/workflows/ci.yml` com matriz 8.2–8.5, cache do Composer, cobertura no job 8.4 (pcov)
- `.gitattributes` com `export-ignore` para tests/CI/configs (pacote enxuto no `--prefer-dist`)
- `.editorconfig`, `.gitignore`, `LICENSE` (MIT), `README.md`, `CHANGELOG.md`

### Fora (v0.1) — roadmap

- Outros documentos: CEP, PIS/PASEP/NIT, CNH, RENAVAM, título de eleitor, placa (Mercosul), inscrição estadual, telefone, chave Pix, boleto.
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
- Publicação no GitHub/Packagist depende de ações suas (criar repositório, conectar Packagist). `gh` não está instalado e `git config user.name` não está definido.

## 5. Plano de implementação

1. **Scaffold** *(Claude — etapa atual)* — `git init`, `composer.json`, configs de PHPUnit/PHPStan, CI, `.gitattributes`, `.editorconfig`, `.gitignore`, `LICENSE`, README e CHANGELOG iniciais. `src/` e `tests/` vazias.
2. **Validators e testes** *(autor — Caio)* — `Cpf`, `Cnpj` e o que mais for necessário (interface, exceção, cálculo de DV), com seus testes. Referência útil para o CNPJ alfanumérico: valor de cada char = `ord(c) - 48`, pesos 2–9 da direita para a esquerda, módulo 11; exemplo oficial `12ABC34501DE35`.
3. **Docs** — README com exemplos e tabela de comportamento; CHANGELOG com seção `[0.1.0]`.
4. **CI completo** — adicionar checagem de cobertura (100%) quando houver código.
5. **Release** — commit, repo `caiolucas/br-validators` no GitHub, push, registro no Packagist (com webhook), tag `v0.1.0` após CI verde.

## 6. Perguntas em aberto

Decisões de comportamento dos validators (a cargo do autor ao implementá-los):

1. Incluir CEP na v0.1? (sem DV — `isValid` só confere 8 dígitos)
2. `format()` com entrada inválida: lançar exceção, formatar só checando tamanho/charset, ou devolver a entrada intacta?
3. Máscara parcial/estranha (`529.98224725`, `529 982 247 25`): aceitar ou exigir máscara exata ou nenhuma?

Infra:

4. **Ambiente local:** usar Docker (`composer:2`, `php:8.x-cli`) para rodar Composer/PHPUnit/PHPStan, ou você instala PHP 8.4 + Composer no WSL?
5. **Git:** qual `user.name`/e-mail para os commits? O handle do GitHub é `caiolucas`?

---

## 7. Verificação (fim da v0.1)

- `docker run --rm -v $PWD:/app -w /app composer:2 composer check` → PHPUnit + PHPStan verdes.
- Rodar a suíte em `php:8.2-cli` e `php:8.5-cli` localmente para checar a faixa de versões.
- Relatório de cobertura = 100% em `src/`.
- `composer validate --strict` sem avisos; `git archive` confirma que tests/CI não vão no dist.
- CI do GitHub verde na matriz completa antes de criar a tag `v0.1.0`.

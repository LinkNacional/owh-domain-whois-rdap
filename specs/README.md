# /specs — Contratos de Comportamento

> Diretório de especificações executáveis e contratos de API.
> Toda feature nova começa aqui. Código só depois do spec aprovado.

## Estrutura

```
specs/
├── openapi/       # Contratos REST API / Webhooks (OpenAPI 3.1)
└── features/      # Comportamento BDD (Gherkin .feature)
```

## Fluxo (alinhado ao pipeline `.reasonix.toml`)

1. **SPEC** — Escrever contrato aqui (`openapi/*.yaml` ou `features/*.feature`)
2. **RED** — Teste falha contra o contrato
3. **GREEN** — Implementar endpoint/comportamento
4. **SECURITY** — Validar nonces, permissions, escaping
5. **REFACTOR** — Limpar sem quebrar contrato

## Domínios

| Pasta | Tipo | Ferramenta |
| --- | --- | --- |
| `openapi/` | Contratos REST/Webhook | OpenAPI 3.1 (YAML) |
| `features/` | Comportamento BDD | Gherkin (Behat/PHP) |

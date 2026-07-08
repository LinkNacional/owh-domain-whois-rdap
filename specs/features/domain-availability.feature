# language: pt-BR
#
# Feature: Verificação de Disponibilidade de Domínio
#
# Cenários BDD cobrindo o fluxo completo de consulta RDAP:
# busca → status → cache → botão de ação

@domain-check @rdap
Funcionalidade: Verificação de disponibilidade de domínio via RDAP

  Como visitante do site
  Quero verificar se um domínio está disponível para registro
  Para decidir se posso comprá-lo

  # ──────────────────────────────────────
  # Regra: Validação de formato
  # ──────────────────────────────────────

  Regra: O domínio informado deve ter formato válido

    Cenário: Domínio sem TLD é rejeitado
      Dado que estou na página de busca de domínios
      Quando eu busco por "exemplo" sem extensão
      Então devo ver a mensagem "Formato de domínio inválido"
      E o status slug deve ser "invalid_format"

    Cenário: Domínio com caracteres inválidos é rejeitado
      Dado que estou na página de busca de domínios
      Quando eu busco por "domínio@inválido.com"
      Então devo ver a mensagem "Formato de domínio inválido"
      E o status slug deve ser "invalid_format"

  # ──────────────────────────────────────
  # Regra: Consulta RDAP — domínio disponível
  # ──────────────────────────────────────

  Regra: Domínio disponível mostra botão de compra

    Cenário: Domínio .com disponível retorna status available
      Dado que o servidor RDAP retorna HTTP 404 para "exemplo-disponivel.com"
      Quando eu busco por "exemplo-disponivel.com"
      Então o status slug deve ser "available"
      E a mensagem deve ser "Disponível"
      E o campo isAvailable deve ser true
      E o botão de ação deve ser exibido com label configurado

  # ──────────────────────────────────────
  # Regra: Consulta RDAP — domínio registrado
  # ──────────────────────────────────────

  Regra: Domínio já registrado mostra status indisponível

    Cenário: Domínio .com registrado retorna status registered
      Dado que o servidor RDAP retorna HTTP 200 com dados válidos para "google.com"
      Quando eu busco por "google.com"
      Então o status slug deve ser "registered"
      E o campo isAvailable deve ser false
      E o botão de compra NÃO deve ser exibido

  # ──────────────────────────────────────
  # Regra: Domínio premium/reservado
  # ──────────────────────────────────────

  Regra: Domínio premium exibe "Sob Consulta"

    Cenário: Resposta RDAP contém palavra-chave "premium"
      Dado que o servidor RDAP retorna HTTP 200
      E o JSON contém a palavra "premium" no campo de status
      Quando eu busco por "premium-example.com"
      Então o status slug deve ser "premium_reserved"
      E a mensagem deve ser "Domínio Premium ou Reservado"
      E o botão deve mostrar "Sob Consulta" em vez de comprar

  # ──────────────────────────────────────
  # Regra: Cache de resultados
  # ──────────────────────────────────────

  Regra: Resultados são cacheados para evitar requisições repetidas

    Cenário: Segunda consulta ao mesmo domínio usa cache
      Dado que "cached-domain.com" foi consultado há 5 minutos
      E o cache TTL para domínios disponíveis é de 1 hora
      Quando eu busco por "cached-domain.com" novamente
      Então o servidor RDAP NÃO deve ser chamado
      E o resultado deve ser retornado do cache local

  # ──────────────────────────────────────
  # Regra: TLD não suportado
  # ──────────────────────────────────────

  Regra: Extensões fora do dns.json são rejeitadas

    Cenário: TLD ausente no bootstrap da IANA
      Dado que a extensão ".xyz" não está no arquivo dns.json local
      Quando eu busco por "dominio.xyz"
      Então o status slug deve ser "unsupported_tld"
      E a mensagem deve conter "não suportada"

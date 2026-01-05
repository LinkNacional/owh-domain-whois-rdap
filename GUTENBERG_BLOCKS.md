# Blocos Gutenberg - OWH Domain WHOIS RDAP

## ✅ Implementação Concluída

### 📦 **Blocos Criados:**

#### 1. **RDAP - Pesquisa de Domínios** (`owh-rdap/domain-search`)
- **Ícone:** 🔍 (search)
- **Categoria:** Widgets
- **Funcionalidade:** Formulário de pesquisa de domínios
- **Configurações:**
  - ✅ **Show Title:** Exibe/oculta título "Pesquisar Domínio"
- **Renderização:** Via shortcode `[owh-rdap-whois-search]` com parâmetros

#### 2. **RDAP - Resultados da Pesquisa** (`owh-rdap/domain-results`)
- **Ícone:** 📋 (list-view)  
- **Categoria:** Widgets
- **Funcionalidade:** Área de exibição dos resultados
- **Configurações:**
  - ✅ **Auto Load:** Carrega resultados automaticamente via URL
- **Renderização:** Via shortcode `[owh-rdap-whois-results]` com parâmetros

### 🔧 **Arquivos Implementados:**

1. **`admin/js/lknaci-owh-domain-whois-rdap-blocks.js`**
   - Registro dos blocos Gutenberg
   - Interface de preview no editor
   - Controles de configuração no Inspector

2. **Classe Admin atualizada:**
   - `register_gutenberg_blocks()` - Registra os blocos
   - `render_search_block()` - Renderiza bloco de pesquisa
   - `render_results_block()` - Renderiza bloco de resultados

3. **Shortcodes aprimorados:**
   - Suporte ao parâmetro `show_title` 
   - Suporte ao parâmetro `auto_load`
   - Templates condicionais

### 🎨 **Interface do Editor:**

#### Bloco de Pesquisa:
```
┌─────────────────────────┐
│ 🔍 RDAP - Pesquisa de  │
│    Domínios             │
├─────────────────────────┤
│ □ Pesquisar Domínio     │ ← Título opcional
│ ┌─────────┬────────────┐ │
│ │Input Box│ [Pesquisar]│ │ ← Preview
│ └─────────┴────────────┘ │
│ Preview do formulário   │
└─────────────────────────┘
```

#### Bloco de Resultados:
```
┌─────────────────────────┐
│ 📋 RDAP - Resultados da │
│    Pesquisa             │
├─────────────────────────┤
│       🔍               │
│  Área de Resultados     │
│ Os resultados aparece-  │ ← Placeholder
│ rão aqui                │
│ ☑ Carregamento auto    │
└─────────────────────────┘
```

### ⚙️ **Configurações dos Blocos:**

#### Configurações no Inspector (Sidebar):
1. **Bloco de Pesquisa:**
   - ☑️ **Exibir título** (Toggle)

2. **Bloco de Resultados:**
   - ☑️ **Carregamento automático** (Toggle)
   - 💡 Help: "Carrega automaticamente os resultados via URL"

### 🚀 **Funcionalidades:**

#### ✅ **Compatibilidade:**
- WordPress 5.0+ (Gutenberg)
- Fallback para shortcodes em temas clássicos
- Tradução automática via `wp_set_script_translations()`

#### ✅ **Recursos:**
- **Preview em tempo real** no editor
- **Configurações visuais** no Inspector
- **Renderização via PHP** (não salva HTML no post)
- **Suporte a múltiplas instâncias** na mesma página

#### ✅ **Estilos:**
- CSS específico para preview no editor
- Responsividade móvel
- Integração com estilos existentes

### 📋 **Como Usar:**

#### No Editor Gutenberg:
1. Clique em **"+"** para adicionar bloco
2. Procure por **"RDAP"** ou **"domain"**
3. Adicione os blocos desejados:
   - **"RDAP - Pesquisa de Domínios"** 
   - **"RDAP - Resultados da Pesquisa"**
4. Configure as opções no painel lateral
5. Publique/atualize a página

#### Shortcodes Equivalentes:
```php
// Bloco de Pesquisa:
[owh-rdap-whois-search show_title="true"]

// Bloco de Resultados:
[owh-rdap-whois-results auto_load="true"]
```

### 🔄 **Integração com Sistema Existente:**

#### ✅ **Mantém compatibilidade total:**
- Todas as funcionalidades RDAP existentes
- Sistema de integração (Custom URL/WHMCS)
- Cache e configurações admin
- AJAX e validação de domínios

#### ✅ **Melhora a experiência:**
- Interface visual no Gutenberg
- Configuração simplificada
- Preview instantâneo
- Melhor usabilidade

## 🎯 **Status: COMPLETO** ✅

Os blocos Gutenberg estão **totalmente implementados** e funcionais, seguindo as diretrizes da arquitetura híbrida do `copilot-instructions.md`!

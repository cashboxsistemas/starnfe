# Implementação de cBenef para Santa Catarina

## Status Atual
✅ **Implementado:**
- Campo cBenef no cadastro de produtos
- Validação e aplicação automática de cBenef para CST 40
- Interface melhorada com sugestões de códigos
- Logs detalhados para debugging

❌ **Problema Atual:**
- Erro 931: "Código de benefício fiscal incompatível com CST e UF"
- Códigos testados (SC001001, SC012001) não são aceitos pelo SEFAZ/SC

## Códigos Implementados no Sistema
O sistema agora suporta os seguintes códigos para SC:
- `SC001001` - Isenção genérica (Art. 6º, I do Anexo 2 do RICMS/SC)
- `SC002001` - Isenção produtos alimentícios básicos
- `SC003001` - Isenção livros, jornais, periódicos e papel
- `SC004001` - Isenção medicamentos
- `SC005001` - Isenção produtos hortifrutícolas in natura
- `SC006001` - Outras isenções previstas no RICMS/SC
- `SC012001` - Isenção ICMS conforme legislação estadual
- `SC012016` - Isenção específica por tipo de operação
- `SC012025` - Isenção para determinados produtos/serviços

## Próximos Passos Necessários

### 1. Obter Tabela Oficial Atualizada
- Acessar o site da SEF/SC: https://www.sef.sc.gov.br
- Procurar por "Tabela 5.2 - Códigos de Benefício Fiscal (cBenef)"
- Baixar a versão mais recente da tabela oficial

### 2. Identificar o Código Correto
Para encontrar o código correto para seu produto:
1. Identifique a base legal da isenção (artigo do RICMS/SC)
2. Consulte a Tabela 5.2 oficial
3. Localize o código correspondente ao CST 40 + base legal

### 3. Fontes Oficiais para Consulta
- **SEF/SC:** https://www.sef.sc.gov.br
- **SPED Fiscal SC:** Seção de informações adicionais
- **RICMS/SC:** Regulamento do ICMS de Santa Catarina
- **Anexo 2 do RICMS/SC:** Lista de produtos com isenção

### 4. Validação
Antes de usar um código cBenef:
1. Confirme na tabela oficial da SEF/SC
2. Verifique se aplica ao seu tipo de produto/operação
3. Teste em ambiente de homologação

## Códigos de Arquivo Modificados

### `/app/Services/NFeRemessaService.php`
- Linha ~1570: Implementação de validação de códigos SC válidos
- Aplicação automática de SC001001 como fallback
- Logs detalhados do processo

### `/resources/views/produtos/_forms.blade.php`
- Linha ~427: Campo cBenef com sugestões e orientações
- Interface melhorada com códigos SC comuns

## Como Testar
1. Crie um produto com CST 40
2. Defina um cBenef válido da tabela oficial
3. Teste a emissão de NFe
4. Verifique os logs em `storage/logs/laravel.log`

## Contato com Contador
Como mencionado na orientação recebida, **consulte sempre um contador especializado** para:
- Identificar a base legal correta para sua operação
- Escolher o código cBenef apropriado
- Manter-se atualizado com mudanças na legislação

---
**Importante:** Este README documenta a implementação técnica. A escolha do código cBenef correto deve sempre ser validada por um profissional contábil qualificado.

# Mapa funcional do Clic Design

Mapeamento feito a partir do admin do Clic em 16/08/2026. Foco do clone: produto, tabela de fator e orçamento.

## Menu administrativo

### Cadastros
- Administrativos
- Clientes
  - Cadastros Newsletter
  - Lista de Clientes
  - Ramo de Atuação
- Vendedores

### CMS
- Banner marketing listagem
- Blog
- Catálogos
- Home
  - Banners: Destaque, Principal, Destaque Header, Destaque Footer
  - Depoimentos
  - Informação de Utilidades
  - Vídeo
- Páginas Institucionais
- Sobre
  - Clientes Atendidos
  - Sobre

### Preferências
- Configurações
- Segurança
  - Termos e Condições
- Tabela de Fator

### Produtos
- Catálogo de Produtos
- Categorias
  - Categoria Selecionadas
  - Lista
- Cores
  - Cor
  - Grupo de Cores
- Gravações
- Splash
- Upload preço de Venda
- Exportar CSV

### Vendas
- Orçamentos
- Pedidos
- Propostas
- Status do Orçamento

### Integração
- Integração de fornecedores
- Ajuda

## Produtos

### Lista

Rota Clic: `/admin/item/`

Filtros:
- Referência
- Nome
- Descrição
- Status
- Cor
- Fornecedor
- Classe

Colunas:
- ID
- Imagem
- Referência
- Nome
- Fornecedor
- Código Fornecedor
- Custo
- Tabela Fator
- Data Cadastro
- Última Atualização
- Status
- Editar
- Excluir

Ações rápidas:
- Salvar custo por linha
- Trocar tabela de fator por linha
- Ativar/inativar produto
- Editar em popup
- Excluir

Endpoints observados:
- `/ajax.php/updateCusto/`
- `/ajax.php/trocaTabelaFator/`

### Cadastro

Rota Clic: `/admin/item/?action=editar&id={id}&pop=1`

Campos principais:
- Referência
- Nome
- Fornecedor
- Código fornecedor
- Disponibilidade
- Ativo
- Bloqueado para atualização do fornecedor
- NCM
- Classe
- Quantidade mínima
- Custo
- Preço de venda
- Preço de
- Informação adicional
- Descrição
- SEO keywords
- Altura, largura, profundidade, espessura, comprimento, circunferência, diâmetro e peso
- Energia
- Garantia
- Medida de gravação
- Tamanho total
- Splash
- Chamada
- Link YouTube e status
- Informações técnicas
- Categorias
- Variações por cor
- Imagens
- SEO do produto
- Código relacionado
- Tabela de fatores por faixa
- Tabela de preço manual por faixa
- Kit
- Descrição de gravação
- Descrição de refil

Regra importante:
- Produto pode usar tabela de fator ou tabela de preço manual.
- Ao escolher tabela de preço manual, a tabela de fator do produto deixa de ser a fonte do preço.
- Na nossa cópia, os produtos XBZ devem entrar como produto pai + variações, agrupados por referência base.

## Tabela de fator

Rota Clic: `/admin/fator/`

Lista:
- Nome
- Status
- Regra de Fator
- Clonar
- Editar
- Excluir

Cadastro:
- Nome da tabela
- Status ativo/inativo
- Linhas de faixa:
  - Quantidade inicial
  - Quantidade final
  - Coeficiente

Botões:
- Sair
- Salvar
- Salvar & Sair
- Salvar & Novo
- Salvar & Atualizar Produtos

Exemplo real observado:
- Tabela `10%`
- Quantidade inicial `1`
- Quantidade final `10000`
- Coeficiente `0,47`

Regra inferida do orçamento:
- O fator não é o preço final em si; ele participa do cálculo do preço de venda.
- No clone atual foi implementado: `preço unitário = preço base / coeficiente`, depois frete, BV/outros custos e imposto.
- A fórmula exata do backend do Clic ainda deve ser validada com alguns exemplos de custo/preço reais.

## Orçamentos

### Lista

Rota Clic: `/admin/orcamento/`

Filtros:
- Número do pedido
- Empresa
- Contato
- Vendedor
- Representante
- Período
- Data de envio da proposta
- Código do produto
- E-mail do cliente

Colunas:
- ID
- Data
- Empresa
- Contato
- E-mail
- Representante
- Status
- Origem
- Envio proposta
- Proposta
- Editar
- Excluir

Ações:
- Incluir novo
- Exportar CSV
- Editar em popup
- Clonar orçamento
- Excluir

### Cadastro/edição

Rota Clic: `/admin/orcamento/?action=editar&id={id}&pop=1`

Blocos principais:
- Status do orçamento
- Vendedor/representante
- Dados do cliente
- Escolha de CNPJ para proposta
- Adicionar produto por referência
- Quantidade 1, 2 e 3
- Gravação
- Envio da proposta por e-mail/WhatsApp
- Mensagem personalizada
- Lista de produtos do orçamento
- Condições da proposta

Campos do item:
- Referência
- Nome
- Custo
- Descrição editável
- Imagem
- Produto ID
- SKU/variação ID
- Gravação
- Pedido item ID
- Fator 1, fator 2, fator 3
- Quantidade 1, 2, 3
- Preço 1, 2, 3
- Subtotal 1, 2, 3
- Frete
- BV/outros custos
- Imposto
- Memória de cálculo

Regras de interação observadas:
- Trocar fator recalcula preço.
- Trocar quantidade recalcula preço.
- Trocar gravação recalcula preço.
- Digitar preço manual limpa o fator daquela proposta.
- Aplicar frete recalcula o item.
- Aplicar BV/outros custos e imposto acontece depois do preço base.
- O próprio Clic avisa: ao alterar fator, quantidade ou gravação, valores são recalculados e é preciso reaplicar outros custos, BV e impostos.

Endpoints/JS observados:
- `/admin.php/orcamento/orcAtualizarPrecoVenda/`
- `/admin.php/orcamento/atualizaFrete/`
- `/admin.php/orcamento/atualizaImposto/`
- `/admin/ajax_get_sugestao_preco/`
- `/admin.php/orcamento/atualizarCnpj/`

## Fornecedor XBZ

API:
- `GetListaDeProdutos`
- Retorno importado localmente: 11.428 itens

Campos relevantes:
- `IdProduto`
- `CodigoXbz`
- `CodigoComposto`
- `CodigoAmigavel`
- `Nome`
- `Descricao`
- `SiteLink`
- `ImageLink`
- `CorWebPrincipal`
- `CorWebSecundaria`
- `Peso`
- `Altura`
- `Largura`
- `Profundidade`
- `PrecoVenda`
- `QuantidadeDisponivelEstoquePrincipal`
- `QuantidadeDisponivel`
- `Ncm`

Agrupamento correto:
- Produto pai: `CodigoAmigavel`
- Variação/SKU: `CodigoComposto`

Resultado local:
- 3.612 produtos pai
- 11.428 variações

## O que já existe no Laravel local

- Dashboard administrativo
- Lista de produtos
- Edição básica de produto
- Categorias manuais
- Variações importadas
- Importador XBZ por API ou JSON local
- Tabela de fator com faixas
- Orçamento com cliente/status/vendedor
- Adicionar produto por SKU
- 3 quantidades/propostas por item
- Cálculo básico com fator, frete, BV/outros e imposto

## Próximas etapas da cópia

1. Visual/base do admin
   - Ajustar menu superior/dropdowns para ficar mais próximo do Clic.
   - Melhorar tabelas, botões, ícones, mensagens e paginação.

2. Produto
   - Completar campos do cadastro.
   - Adicionar ações rápidas na lista: custo, fator e status.
   - Adicionar variações por cor com edição.
   - Adicionar imagens do produto/variação.
   - Adicionar tabela de preço manual.

3. Fator
   - Adicionar "Salvar & Atualizar Produtos".
   - Validar fórmula real do cálculo com amostras.
   - Permitir clonar/excluir com confirmação.

4. Orçamento
   - Recriar layout da tela do Clic com blocos de cliente, adicionar produto e itens.
   - Implementar busca por referência, não só SKU.
   - Separar perfil vendedor/admin para esconder custo, preço base e fator do vendedor.
   - Implementar proposta visual/enviável.
   - Implementar clonar orçamento/proposta.

5. Cadastros auxiliares
   - Clientes
   - Vendedores
   - Status do orçamento
   - Gravações
   - Cores
   - Categorias

6. Produção/cPanel
   - Trocar SQLite local por MySQL.
   - Preparar `.env.example`.
   - Ajustar storage/cache/logs para hospedagem.
   - Criar rotina agendada de importação XBZ respeitando limite diário.

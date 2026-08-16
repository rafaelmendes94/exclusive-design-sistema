# Mapa completo do sistema Clic Design

Mapeamento feito a partir do admin do Clic em 16/08/2026. Este arquivo é o roteiro geral para clonar o sistema inteiro em Laravel.

## Estrutura do menu

### Cadastros
- Administrativos: `/admin/cadastroadmin/`
- Clientes
  - Cadastros Newsletter: `/admin/newslettercadastros/`
  - Lista de Clientes: `/admin/cadastrocliente/`
  - Ramo de Atuação: `/admin/comoconheceu/`
- Vendedores: `/admin/cadastrovendedor/`

### CMS
- Banner marketing listagem: `/admin/bannermarketing/`
- Blog: `/admin/blog/`
- Catálogos: `/admin/pagcatalogo/`
- Home
  - Banners / Destaque: `/admin/destaquecategoria/`
  - Banners / Principal: `/admin/bannerprincipal/`
  - Destaque Header: `/admin/destaquehome/`
  - Destaque Footer: `/admin/destaquefooter/`
  - Depoimentos: `/admin/frasehome/`
  - Informação de Utilidades: `/admin/informacaodeutilidade/`
  - Vídeo: `/admin/videohome/`
- Páginas Institucionais: `/admin/paginainstitucional/`
- Sobre
  - Clientes Atendidos: `/admin/logocliente/`
  - Sobre: `/admin/sobreempresa/`

### Preferências
- Configurações: `/admin/configuracao/`
- Termos e Condições: `/admin/termoscondicoes/`
- Tabela de Fator: `/admin/fator/`

### Produtos
- Catálogo de Produtos: `/admin/item/`
- Categorias
  - Categoria Selecionadas: `/admin/categoriasemdestaques/`
  - Lista: `/admin/categoria/`
- Cores
  - Cor: `/admin/cor/`
  - Grupo de Cores: `/admin/corgrupo/`
- Gravações: `/admin/gravacao/`
- Splash: `/admin/splashes/`
- Upload preço de Venda: `/admin/uploadcustos/`
- Exportar CSV: `/admin/exportarcsv/`

### Vendas
- Orçamentos: `/admin/orcamento/`
- Pedidos: `/admin/vendas/`
- Propostas: `/admin/proposta/`
- Status do Orçamento: `/admin/pedidostatus/`

### Outros
- Integração de fornecedores: `/admin/ifornecedor/`
- Central de Ajuda: `/admin/ajuda/`

## Cadastros

### Administrativos

Lista:
- Filtros: nome, e-mail, data de cadastro.
- Colunas: ID, nome, e-mail, Skype, telefone comercial, celular, data cadastro, status, editar, excluir.

Cadastro:
- Ativo
- Nome
- E-mail
- Telefone
- Celular/WhatsApp
- RG
- Órgão emissor RG
- CPF
- Senha
- Permissões por módulo

### Clientes

Lista:
- Filtros: nome, e-mail, empresa, CNPJ, departamento/e-mail, departamento/nome, data cadastro.
- Colunas: ID, nome, e-mail, empresa, telefone comercial, ramo de atuação, vendedor, celular, data cadastro, status, clonar, editar, excluir.

Cadastro:
- Ativo
- Nome
- Empresa
- Razão social
- Inscrição estadual
- CNPJ
- CPF
- Atendimento/representante
- Ramo de atuação
- Endereço: CEP, logradouro, número, complemento, bairro, cidade, estado
- E-mail
- Telefone comercial, celular e residencial
- Observação
- Senha

### Ramo de Atuação

Lista:
- Colunas: ID, nome, editar, excluir.

### Vendedores

Lista:
- Filtros: nome, e-mail, data cadastro.
- Colunas: ID, nome, e-mail, Skype, telefone comercial, celular, data cadastro, status, editar, excluir.

Cadastro:
- Vendedor padrão
- Ativo
- Nome
- Ver fornecedor
- Ver custo
- Ver fator
- E-mail
- Telefone
- Celular/WhatsApp
- RG
- Órgão emissor
- CPF
- Empresa vinculada
- Nome da empresa
- Nome fantasia
- CNPJ
- Senha
- Permissões por módulo

Regra importante:
- O Clic controla visibilidade de fornecedor, custo e fator no cadastro do vendedor.
- Na cópia, vendedor comum deve ficar sem acesso a custo, preço base e fator, conforme pedido.

## CMS

### Banner marketing listagem

Lista:
- Colunas: ID, imagem, nome, data cadastro, status, editar, excluir.

### Blog

Lista:
- Colunas: ID, título, autor, área, status, data de cadastro, editar, excluir.

Cadastro:
- Título
- Publicado
- Data de cadastro
- Autor
- Área
- Texto
- Departamento/área
- SEO: description, keywords, URL, title

### Catálogos

Lista:
- Colunas: ID, imagem, nome, ordem, data cadastro, status, editar, excluir.

### Home / Banners

Destaque:
- Título
- Link
- Imagem
- Excluir item

Principal:
- Título
- Link de destino
- Destino
- Status
- Data e hora de publicação
- Vídeo MP4
- Status do vídeo
- Imagem desktop 1920x600
- Imagem mobile 1014x535

Destaque Header e Destaque Footer:
- Lista com ID, imagem, texto, link, ordem, status, editar, excluir.

### Depoimentos

Lista:
- ID, empresa, nome, status, editar, excluir.

### Informação de Utilidades

Tela sem tabela detectada na listagem; provavelmente cadastro direto/bloco CMS.

### Vídeo

Cadastro direto:
- Código do vídeo.

### Páginas Institucionais

Lista:
- ID, título, ordem, status, editar, excluir.

### Clientes Atendidos

Lista:
- Filtro: nome.
- Colunas: ID, logo, nome, data cadastro, status, editar, excluir.

### Sobre

Cadastro direto:
- Banner desktop 1140x440
- Banner mobile 358x240
- Imagem do vídeo
- Link do vídeo
- Título
- Texto sobre
- Blocos central, esquerda, direita, especial e rodapé
- Texto, ordem, imagem e exclusão de blocos

## Preferências

### Configurações

Tela de configurações gerais. Ainda precisa de inspeção mais profunda por campos internos.

### Termos e Condições

Tela institucional/legal. Ainda precisa de inspeção mais profunda por campos internos.

### Tabela de Fator

Lista:
- Nome
- Status
- Regra de fator
- Clonar
- Editar
- Excluir

Cadastro:
- Nome da tabela
- Status
- Linhas com quantidade inicial, quantidade final e coeficiente

Botões:
- Sair
- Salvar
- Salvar & Sair
- Salvar & Novo
- Salvar & Atualizar Produtos

## Produtos

### Catálogo de Produtos

Lista:
- Filtros: referência, nome, descrição, status, categoria/subcategoria, cor, fornecedor, classe.
- Colunas: ID, imagem, referência, nome, fornecedor, código fornecedor, custo, tabela fator, data cadastro, última atualização, status, editar, excluir.

Ações rápidas:
- Alterar custo por linha
- Trocar tabela de fator por linha
- Ativar/inativar produto
- Editar em popup
- Excluir

Cadastro:
- Tipo de produto
- Referência
- Nome
- Fornecedor
- Código fornecedor
- Disponibilidade
- Ativo
- Bloqueado para atualização
- NCM
- Classe
- Quantidade mínima
- Custo
- Preço de venda
- Preço de
- Informação adicional
- Descrição
- SEO keywords
- Medidas: altura, largura, profundidade, espessura, comprimento, circunferência, diâmetro, peso
- Energia
- Garantia
- Medida de gravação
- Tamanho total
- Splash
- Chamada
- Link YouTube e status
- Informações técnicas
- Categorias
- Imagens
- Variações por cor
- SEO do produto
- Código relacionado
- Tabela de fatores por faixas
- Tabela de preços manual por faixas
- Produto kit
- Descrição de gravação
- Descrição de refil

Regra XBZ:
- Produto pai por `CodigoAmigavel`.
- Variação por `CodigoComposto`.
- Categorias podem ser atribuídas manualmente depois.

### Categorias

Lista:
- Filtros: nome, status.
- Colunas: ID, nome, status, editar, excluir.

Cadastro:
- Ativo
- Aparece no menu
- Nome
- Descrição
- Banner desktop
- Banner mobile
- Link
- Ícone
- SEO: description, keywords, URL, title
- Ativar tabela para atualizar itens
- Tabela de fatores
- Tabela de preços

### Categoria Selecionadas

Cadastro/lista:
- Selecionar categoria
- Categorias selecionadas

### Cor

Lista:
- Filtros: nome, código, status, grupo.
- Colunas: ID, nome, código, imagem, grupo, status, editar, excluir.

Cadastro:
- Status ativo
- Nome
- Código
- Imagem 50x50
- Grupo de cor

### Grupo de Cores

Lista:
- ID, nome, status, imagem, editar, excluir.

### Gravações

Lista:
- Filtro: nome.
- Colunas: ID, nome, descrição, status, clonar, editar, excluir.

Cadastro:
- Status ativo
- Nome
- Descrição
- Tabela de gravação com valores por faixa
  - Quantidade de
  - Quantidade até
  - Valor

### Splash

Lista:
- ID, nome, imagem, data cadastro, status, editar, excluir.

Cadastro:
- Status
- Nome
- Imagem

### Upload preço de Venda

Cadastro:
- Upload de arquivo CSV.

### Exportar CSV

Filtros:
- Referência
- Categoria
- Status ativo

## Vendas

### Orçamentos

Lista:
- Filtros: número pedido, empresa, contato, vendedor, representante, período, data envio proposta, código produto, e-mail cliente.
- Colunas: ID, data, empresa, contato, e-mail, representante, status, origem, envio proposta, proposta, editar, excluir.

Cadastro/edição:
- Status do orçamento
- Vendedor
- Cliente
- CNPJ da proposta
- Adicionar produto por referência
- Quantidade 1, 2 e 3
- Gravação
- Envio por e-mail/WhatsApp
- Mensagem personalizada
- Produtos do orçamento
- Condições comerciais

Item do orçamento:
- Produto
- Gravação/custo
- Imagem
- Informação/descrição
- Fator 1, 2 e 3
- Quantidade 1, 2 e 3
- Preço 1, 2 e 3
- Subtotal 1, 2 e 3
- Frete
- BV/outros custos
- Imposto
- Memória de cálculo

### Pedidos

Lista:
- Filtros: número do pedido, número do orçamento, empresa, nota fiscal, vendedor, status, período.
- Colunas: número pedido, orçamento, data entrega, empresa, vendedor, status, nota fiscal, total, criado em, editar, excluir.

Relação:
- Pedido nasce a partir de proposta/orçamento aprovado.

### Propostas

Lista:
- Filtros: número orçamento, empresa, contato, período.
- Colunas: ID, empresa, contato, representante, data cadastro, editar, excluir.

Tela inicial:
- Criar orçamento
- Origem do pedido
- Cliente
- Departamento
- Resultado

### Status do Orçamento

Cadastro direto:
- Nome do status
- Lista editável de status existentes

## Integração e ajuda

### Integração de fornecedores

Tela existe no menu, mas precisa inspeção interna mais profunda.

No clone, a integração XBZ já foi iniciada:
- Importação por JSON local ou API
- 3.612 produtos pai
- 11.428 variações

### Central de Ajuda

Conteúdos de apoio sobre:
- Agente de brindes com IA
- Gravação
- Fatores
- Formação de preço
- Impostos
- BV
- Frete
- Outras funções do sistema

## Ordem recomendada para copiar o sistema completo

### Fase 1 - Base operacional
- Login e permissões
- Administrativos
- Vendedores com flags de visibilidade
- Clientes
- Ramo de atuação
- Status do orçamento

### Fase 2 - Produtos
- Categorias
- Cores e grupos
- Gravações com tabela de preço
- Splash
- Catálogo de produtos completo
- Importação XBZ
- Upload/Export CSV

### Fase 3 - Precificação
- Tabela de fator
- Tabela manual de preço por produto/categoria
- Regras de atualização em massa
- Validação da fórmula de cálculo

### Fase 4 - Vendas
- Orçamentos completos
- Propostas
- Envio por e-mail/WhatsApp
- Geração de pedido
- Pedidos

### Fase 5 - CMS/site
- Banners
- Blog
- Catálogos
- Home
- Páginas institucionais
- Sobre
- Clientes atendidos
- Termos

### Fase 6 - Produção
- MySQL para cPanel
- Uploads/storage
- Agendamento da integração XBZ
- Perfis/permissões
- Backup e logs

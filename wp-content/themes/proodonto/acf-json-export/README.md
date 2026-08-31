> **Em teste (2026-08-31):** ver `migracao-abas-2026-08-31/LEIA-ME.md` —
> migração dos grupos por seção de Home/Sobre/Vendas para 1 grupo por
> página, organizado em abas (mesmas keys/names de campo, só muda a
> apresentação). Os grupos por seção abaixo continuam sendo os
> registrados em produção (`inc/acf-fields.php`) até a migração ser
> validada e adotada.

# ACF JSON — exports de referência (não carregados automaticamente)

Esta pasta contém um arquivo `.json` por grupo de campos ACF do tema, no
mesmo formato que a própria ACF gera quando você exporta grupos pelo painel
(**Personalizar → Campos → Ferramentas → Exportar**).

**Isto NÃO é a pasta `acf-json/` do ACF.** Por decisão de arquitetura já
documentada em `inc/acf-fields.php`, todos os grupos deste tema são
registrados inteiramente em PHP via `acf_add_local_field_group()` — de
propósito, pra não depender de ninguém criar/editar um grupo pelo painel do
WordPress (o que os deixaria editáveis e fora do controle de versão do
código). A pasta `acf-json/` do tema continua vazia e reservada só para o
cenário em que a própria ACF precise gravar algo automaticamente ali (o
que não deve acontecer, já que os grupos abaixo aparecem como "somente
leitura" no painel).

## Para que serve, então

- **Portabilidade**: importar a estrutura destes campos em outro projeto/
  ambiente (ex.: outra instalação WordPress) via **Personalizar → Campos →
  Ferramentas → Importar** da ACF, sem precisar copiar o PHP. Pra isso, use
  os arquivos combinados em `import/` (ver abaixo) — são o formato que a
  própria tela de Importar espera (um array de grupos por arquivo); os
  arquivos soltos aqui na raiz desta pasta são um-grupo-por-arquivo, bons
  pra ler/comparar, mas a tela de Importar só aceita um arquivo por vez.
- **Documentação**: ver de forma legível e compacta (JSON) o schema
  completo de cada seção — nomes de campo, tipos, `default_value`,
  condições de exibição — sem precisar ler o PHP inteiro.
- **Diff/auditoria**: comparar a estrutura de campos entre versões do tema
  de forma mais direta que um diff de PHP.

## Importar pelo painel (`import/`)

Três arquivos, cada um um array de grupos (mesmo formato que a ACF gera ao
exportar vários grupos de uma vez pelo painel), agrupados por página —
mesmo agrupamento da tabela "Arquivos" abaixo:

| Arquivo | Grupos |
| --- | --- |
| `import/home.json` | os 11 grupos `group_home_*` |
| `import/vendas.json` | os 11 grupos `group_vendas_*` |
| `import/sobre-e-opcoes.json` | os 9 grupos `group_sobre_*` + `group_theme_options`/`group_theme_options_agregador` |

Gerados a partir dos MESMOS arquivos soltos desta pasta (concatenados num
array, sem retranscrever nada à mão) — se `inc/acf-fields.php` mudar, tanto
os arquivos soltos quanto estes três ficam desatualizados até serem
gerados de novo (ver "Como foram gerados" abaixo).

Importar um grupo que já existe (mesma `key`) via este fluxo cria uma
**cópia editável pelo painel**, com origem "JSON" — ela NÃO substitui nem
sincroniza com o grupo local (PHP, somente leitura) já ativo no tema. Use
isto para levar a estrutura de campos a outro projeto/ambiente, não para
"destravar" a edição destes grupos no site atual.

## Como foram gerados

Cada arquivo foi gerado programaticamente a partir dos MESMOS arrays PHP
registrados em `inc/acf-fields.php` (nenhum campo foi retranscrito à mão,
o que eliminaria o risco de divergência entre o `.json` e o código real).
Se `inc/acf-fields.php` mudar, estes arquivos ficam desatualizados até
serem gerados de novo — não há nenhum processo automático de sync.

## Fonte de verdade

**`inc/acf-fields.php`** continua sendo a única fonte de verdade em
produção. Editar estes `.json` não tem nenhum efeito no site.

## Arquivos

| Página | Grupos |
| --- | --- |
| Home (`page-home.php`) | `group_home_banner`, `group_home_about`, `group_home_marquee`, `group_home_results`, `group_home_treatments`, `group_home_shorts`, `group_home_steps`, `group_home_reviews`, `group_home_units`, `group_home_blog`, `group_home_closing_cta` |
| Página de Vendas (`page-vendas.php`) | `group_vendas_cta`, `group_vendas_marquee`, `group_vendas_about`, `group_vendas_about_gallery`, `group_vendas_results`, `group_vendas_treatments`, `group_vendas_shorts`, `group_vendas_steps`, `group_vendas_reviews`, `group_vendas_units`, `group_vendas_closing_cta` |
| Sobre / Quem Somos (`page-sobre.php`) | `group_sobre_hero`, `group_sobre_historia`, `group_sobre_valores`, `group_sobre_numeros`, `group_sobre_equipe`, `group_sobre_seguranca`, `group_sobre_units`, `group_sobre_faq`, `group_sobre_cta` |
| Opções do Tema | `group_theme_options`, `group_theme_options_agregador` |

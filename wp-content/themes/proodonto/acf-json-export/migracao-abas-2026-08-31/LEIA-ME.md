# Migração ACF — 1 grupo por página, organizado em abas (2026-08-31)

## O que foi gerado nesta pasta

- `group_home.json`, `group_sobre.json`, `group_vendas.json` — os 3 novos
  grupos consolidados (1 grupo por página), cada um com os campos das
  antigas seções organizados em abas (`type: tab`). Mesmo formato que a
  ACF gera em **Personalizar → Campos → Ferramentas → Exportar** para 1
  grupo — prontos para importar pelo painel.
- `import/home.json`, `import/sobre.json`, `import/vendas.json` — os
  mesmos 3 grupos, cada um dentro de um array de 1 elemento (formato que
  a tela **Ferramentas → Importar** da ACF espera).
- `DE-PARA.md` / `de-para.json` — tabela de mapeamento: qual grupo antigo
  (e quantos campos) virou qual aba, em qual grupo novo.

Nenhuma `key` ou `name` de campo foi alterada — só a apresentação. Os
dados já salvos no banco continuam válidos, porque o WordPress lê pelo
`name` do campo (meta_key), não pelo grupo.

## Como testar no wp-admin (ACF Pro)

1. Foi comentado, temporariamente, o registro em PHP dos grupos por
   seção de Home/Sobre/Vendas (`inc/acf-fields.php`, bloco entre
   `if ( false ) :` e `endif;`, logo abaixo do grupo
   `group_theme_options_footer`) — isso evita duplicidade/conflito ao
   importar os grupos novos pelo painel. `group_theme_options`,
   `group_theme_options_agregador` e `group_theme_options_footer`
   continuam ativos normalmente.
2. Vá em **Personalizar → Campos → Ferramentas → Importar campos** e
   suba `import/home.json` (repita para `sobre.json` e `vendas.json`).
   Isso cria 3 grupos editáveis pelo painel (origem "JSON"), com abas.
3. Abra a página Home/Sobre/Vendas no editor e confira se os campos
   aparecem certinho, com os mesmos valores de antes (os dados são os
   mesmos, só a organização visual muda).
4. Se estiver tudo certo, decida o próximo passo com calma — os grupos
   PHP originais NÃO foram apagados, só desativados (comentados), então
   dá pra reverter a qualquer momento apagando as duas linhas
   `if ( false ) :` / `endif;` em `inc/acf-fields.php`.

## Para reverter (voltar ao estado atual de produção)

Em `inc/acf-fields.php`, apague as linhas `if ( false ) :` e `endif;`
que envolvem o bloco dos grupos de Home/Sobre/Vendas (o comentário logo
acima delas explica onde estão). Isso reativa os grupos PHP originais
exatamente como estavam. Os 3 grupos JSON importados pelo painel (passo
2 acima) podem ser apagados em **Grupos de campos** no admin — eles são
cópias de teste, independentes do PHP.

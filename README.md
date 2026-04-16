# Plugin Publicações por Visitantes

Plugin WordPress que permite envio público de publicações por usuários não logados, com moderação no admin, sistema de curtidas e comentários.

## Instalação

1. Copie a pasta `publicacoes-submissao` para `wp-content/plugins/`.
2. No painel do WordPress, acesse **Plugins** e ative o plugin **Publicações por Visitantes**.
3. Adicione a shortcode de formulário em qualquer página ou post:
   - `[publicacao_form]`
4. Adicione a shortcode de listagem de publicações aprovadas:
   - `[publicacoes_list]`

## Shortcodes

- `[publicacao_form]`
  - Exibe o formulário público com campos:
    - Nome completo
    - Email
    - Upload de imagem
    - Legenda (máx. 250 caracteres)

- `[publicacoes_list]`
  - Lista as publicações aprovadas em cards.
  - Cada card exibe imagem, autor, legenda, contador de curtidas e área de comentários.

## Como usar

- Usuários não logados podem enviar publicações pelo shortcode de formulário.
- No admin, navegue em **Publicações** e marque o checkbox **Aprovado** para liberar a publicação no frontend.
- Usuários não logados podem curtir e comentar nas publicações aprovadas.

## Recursos

- Custom Post Type `publicacoes`
- Moderação com checkbox `Aprovado`
- Curtidas por email sem login
- Comentários AJAX com email obrigatório
- Upload seguro de imagens usando APIs nativas do WordPress

## Observações

- As publicações só aparecem no frontend quando aprovadas no admin.
- Comentários enviados pelo AJAX ficam pendentes de aprovação padrão do WordPress.

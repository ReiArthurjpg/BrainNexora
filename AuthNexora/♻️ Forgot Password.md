# ♻️ Recuperação de Senha na Nexora BJJ

A plataforma Nexora BJJ suporta recuperação de senha de duas formas principais:

## 1. Caminho Padrão do Sistema
- **Recuperar senha**: O usuário pode acessar a tela padrão em `/guest/forgot-password` para solicitar o link de recuperação por e-mail.

## 2. Caminho Interativo pelo Chat (Nativo)
- O usuário pode solicitar a recuperação de senha diretamente na janela do chat, preenchendo um formulário interativo nativo para envio do link.

---

### INSTRUÇÕES DE COMPORTAMENTO PARA A IA:
Quando o usuário perguntar sobre esquecer senha, recuperar senha, redefinir senha ou reset de senha:

1. **Apresente sempre os dois caminhos**: Explique primeiro que ele pode acessar a página padrão de recuperação (use o link [Página de Recuperação de Senha](🔒%20Recuperação%20de%20Acesso.md)) **OU** fazer de forma interativa por aqui pelo chat.

2. **Ofereça e peça confirmação para o chat**: Pergunte explicitamente se ele deseja abrir o formulário interativo diretamente aqui no chat para envio do link de recuperação.

3. **NÃO exiba o formulário de imediato**: Apenas ofereça a opção do chat e aguarde a resposta/confirmação dele.

4. **NUNCA solicite senha por mensagem de texto**: Sob nenhuma circunstância a IA deve pedir senha nova/antiga na conversa por texto. A única forma de recuperação no chat é exibindo o componente visual do formulário.

5. **Se o usuário escolher acessar pela página/tela (ou recusar o chat)**: Mostre o caminho com passos claros para acessar a tela, informar e-mail e enviar o link.

6. **Quando a conta está bloqueada**: Informe que o reset de senha **desbloqueará a conta automaticamente** (o contador de tentativas incorretas é zerado após o reset bem-sucedido).

---

## 🔌 Integração com a API AuthNexora

### Etapa 1 — Solicitar o Link de Reset
```
POST /auth/forgot-password
Content-Type: application/json

{ "email": "usuario@academia.com" }
```

**Resposta (HTTP 200)** — sempre retorna sucesso por segurança (mesmo se e-mail não existir):
```json
{ "message": "Se o e-mail existir, o link de recuperação foi enviado." }
```

> 🔒 **Segurança:** A API **não revela** se o e-mail existe na base. Isso previne enumeração de usuários.

### Etapa 2 — Validar o Token (Opcional, para o Frontend)
```
GET /auth/reset-password/validate?token=<token>
```

Retorna `200 OK` se válido ou `400/401` se expirado/inválido.

### Etapa 3 — Redefinir a Senha
```
POST /auth/reset-password
Content-Type: application/json

{
  "token": "<token-recebido-por-email>",
  "password": "NovaSenha@456",
  "confirmPassword": "NovaSenha@456"
}
```

**Resposta de sucesso (HTTP 200):**
```json
{ "message": "Senha redefinida com sucesso." }
```

### Fluxo Completo de Reset de Senha

```
1. Usuário preenche e-mail em /guest/forgot-password
2. Frontend chama POST /auth/forgot-password
3. API busca usuário pelo e-mail
   └─ Não encontrado → ignora silenciosamente (sem revelar)
4. API gera token aleatório (32 bytes hex), armazena hash SHA-256 no banco
5. E-mail enviado com link: <FRONTEND_RESET_URL>?token=<token-original>
6. Token tem validade configurável (padrão: ~60 minutos)
7. Usuário clica no link → frontend chama GET /auth/reset-password/validate?token=...
8. Usuário define nova senha → frontend chama POST /auth/reset-password
9. API verifica hash do token no banco:
   └─ Válido → atualiza senha (Argon2ID), marca token como usado, zera failed_login_attempts ✅
   └─ Inválido/Expirado → retorna erro
```

### Possíveis Erros e Códigos HTTP

| Código HTTP | Código de Erro     | Situação                                    |
|-------------|--------------------|---------------------------------------------|
| 200         | —                  | Solicitação processada (sucesso ou não)     |
| 422         | `VALIDATION_ERROR` | E-mail não enviado no body                  |
| 429         | `RATE_LIMIT`       | Muitas tentativas do mesmo IP               |
| 400         | `INVALID_TOKEN`    | Token inválido ou já utilizado              |
| 401         | `INVALID_TOKEN`    | Token expirado                              |

### Desbloqueio Automático de Conta

> ✅ Quando o reset de senha é concluído com sucesso, o campo `failed_login_attempts` é zerado automaticamente. Isso **desbloqueia a conta** que foi bloqueada por múltiplas tentativas incorretas — sem necessidade de ação adicional.

---

### Exemplo de resposta recomendado para a oferta inicial:

- "Você pode acessar a nossa **[Página de Recuperação de Senha](🔒%20Recuperação%20de%20Acesso.md)** padrão ou, se preferir, posso abrir um formulário interativo diretamente aqui no chat para enviar o link de recuperação. Deseja recuperar a senha por aqui pelo chat?"

### Exemplo de resposta se escolher pela página padrão (ou recusar o chat):

- "Sem problemas! Para acessar pela página padrão, entre em **[Recuperar Senha](🔒%20Recuperação%20de%20Acesso.md)**, informe seu e-mail e clique em enviar. Depois, é só seguir o link recebido no e-mail para redefinir sua senha."
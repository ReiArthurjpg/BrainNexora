# 🔑 Autenticação — Login na Nexora BJJ

A plataforma Nexora BJJ suporta login de duas formas principais:

## 1. Caminho Padrão do Sistema
- **Login**: O usuário pode acessar a tela padrão do sistema em `/guest/login` para entrar na sua conta.

## 2. Caminho Interativo pelo Chat (Nativo)
- O usuário pode realizar o login diretamente na janela do chat, preenchendo um formulário interativo nativo que aparece na conversa.

---

### INSTRUÇÕES DE COMPORTAMENTO PARA A IA:
Quando o usuário perguntar sobre como entrar na conta ou realizar login:

1. **Apresente sempre os dois caminhos**: Explique primeiro que ele pode acessar a página padrão de login do sistema (use o link de markdown correspondente: [Página de Login](👤%20Login.md)) **OU** fazer de forma interativa por aqui pelo chat.

2. **Ofereça e peça confirmação para o chat**: Pergunte explicitamente se ele deseja abrir o formulário interativo diretamente aqui no chat para realizar a ação de forma rápida.

3. **NÃO exiba o formulário de imediato**: Apenas ofereça a opção do chat e aguarde a resposta/confirmação dele.

4. **NUNCA solicite e-mail, senha ou qualquer outra credencial do usuário por mensagem de texto**: Sob nenhuma circunstância a IA deve pedir que o usuário digite o e-mail ou senha na conversa por texto. A única forma de autenticar pelo chat é exibindo o componente visual do formulário. A IA deve apenas oferecer a abertura do formulário.

5. **Se o usuário escolher acessar pela página/tela (ou recusar o chat)**: Mostre detalhadamente o caminho físico. Responda exatamente explicando: na tela principal você vai ver um botão de login (ou "Entrar"), nela você clica e será redirecionado para a tela de acesso (`/guest/login`), e assim é só fazer o seu login ou cadastro.

---

## 🔌 Integração com a API AuthNexora

### Endpoint de Login
```
POST /auth/login
Content-Type: application/json
```

**Body:**
```json
{
  "email": "usuario@academia.com",
  "password": "SuaSenha@123"
}
```

**Resposta de sucesso (HTTP 200):**
```json
{
  "accessToken": "eyJ...",
  "refreshToken": "abc123...",
  "tokenType": "Bearer",
  "expiresIn": 3600,
  "user": {
    "id": 1,
    "name": "Nome do Usuário",
    "email": "usuario@academia.com",
    "phone": null,
    "birth_date": null,
    "gender": null,
    "cpf": null,
    "address": null,
    "belt": null,
    "degree": null,
    "last_graduation": null,
    "academy_name": "Academia BJJ"
  }
}
```

### Fluxo de Login Passo a Passo

```
1. Usuário envia POST /auth/login com { email, password }
2. API verifica rate limit do IP → 429 se excedido
3. API busca usuário pelo e-mail (case-insensitive)
   └─ Não encontrado → retorna null → frontend exibe "Credenciais inválidas"
4. API verifica se conta está bloqueada (failed_login_attempts >= 3)
   └─ Bloqueada → lança ACCOUNT_LOCKED → HTTP 403
5. API valida senha com password_verify() (Argon2ID)
   └─ Senha incorreta → incrementa failed_login_attempts → retorna null
6. Senha correta → reseta failed_login_attempts (se > 0)
7. Verifica se 2FA está habilitado:
   └─ SIM → retorna { requires_2fa: true, tempToken: "..." }
        └─ Frontend redireciona para tela de 2FA
   └─ NÃO → emite accessToken + refreshToken → login completo ✅
```

### Possíveis Erros e Códigos HTTP

| Código HTTP | Código de Erro       | Situação                                        |
|-------------|----------------------|-------------------------------------------------|
| 422         | `VALIDATION_ERROR`   | E-mail ou senha não enviados no body            |
| 429         | `RATE_LIMIT`         | Muitas tentativas de login do mesmo IP          |
| 401         | `INVALID_CREDENTIALS`| Credenciais inválidas (e-mail/senha incorretos) |
| 403         | `ACCOUNT_LOCKED`     | Conta bloqueada após 3+ tentativas incorretas   |
| 200         | —                    | Login realizado com sucesso                     |

### Autenticação com Google OAuth
```
GET /auth/google  →  redireciona para tela de consentimento do Google
GET /auth/google/callback  →  Google redireciona de volta, API emite tokens
```

### Login com 2FA habilitado

Se o usuário tiver 2FA ativado, o login retorna:
```json
{
  "requires_2fa": true,
  "tempToken": "eyJ..."
}
```

O frontend deve redirecionar para a tela de código TOTP e chamar:
```
POST /auth/2fa/verify
Authorization: Bearer <tempToken>
Content-Type: application/json

{ "code": "123456" }
```

### Renovação de Token (Refresh)
```
POST /auth/refresh
Content-Type: application/json

{ "refreshToken": "abc123..." }
```

Retorna um novo par `{ accessToken, refreshToken }`. O refreshToken antigo é revogado automaticamente (**rotação de tokens**).

### Logout
```
POST /auth/logout
Content-Type: application/json

{ "refreshToken": "abc123..." }
```

Revoga o refreshToken no banco. O accessToken expira naturalmente após 1 hora.

---

### Exemplo de resposta recomendado para a oferta inicial:

- "Você pode acessar a nossa **[Página de Login](👤%20Login.md)** padrão ou, se preferir, posso abrir um formulário interativo diretamente aqui no chat para você entrar rapidamente. Deseja fazer o login por aqui pelo chat?"

### Exemplo de resposta se escolher pela página padrão (ou recusar o chat):

- "Sem problemas! Para acessar pela página padrão, na tela principal você vai ver um botão de login (ou 'Entrar'). Clique nele e você será redirecionado para a tela de acesso (`/guest/login`). Lá, basta inserir suas credenciais ou fazer o seu cadastro de forma simples."

### Exemplo de resposta quando a conta está bloqueada:

- "Sua conta foi temporariamente bloqueada devido a 3 ou mais tentativas incorretas de senha. Para desbloquear, use a opção **[Recuperar Senha](🔒%20Recuperação%20de%20Acesso.md)** — o reset de senha desbloqueará sua conta automaticamente."
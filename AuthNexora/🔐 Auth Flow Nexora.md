# 🔐 Auth Flow Nexora

Documentação do fluxo completo de autenticação da plataforma Nexora BJJ, baseado na API **AuthNexora** (PHP 8.2 + JWT + MySQL).

---

## 📌 Navegação

- [[🔑 Authentication|🔐 Entrar / Login]]
- [[📝 Signup|📝 Criar Conta]]
- [[♻️ Forgot Password|🔑 Recuperar Senha]]

---

## 🏗️ Arquitetura da Autenticação

A autenticação é centralizada na API **AuthNexora**, que expõe os seguintes endpoints principais:

| Método | Endpoint                         | Descrição                        | Auth Requerido |
|--------|----------------------------------|----------------------------------|----------------|
| POST   | `/auth/login`                    | Login com e-mail e senha         | ❌              |
| POST   | `/auth/signup`                   | Criar nova conta                 | ✅ (admin)      |
| POST   | `/auth/logout`                   | Encerrar sessão                  | ❌              |
| POST   | `/auth/refresh`                  | Renovar accessToken              | ❌              |
| GET    | `/auth/me`                       | Buscar dados do usuário logado   | ✅              |
| PUT    | `/auth/me`                       | Atualizar perfil                 | ✅              |
| GET    | `/auth/verify-email`             | Verificar e-mail via token       | ❌              |
| POST   | `/auth/2fa/verify`               | Verificar código 2FA             | ✅ (temp)       |
| POST   | `/auth/forgot-password`          | Solicitar reset de senha         | ❌              |
| POST   | `/auth/reset-password`           | Redefinir senha com token        | ❌              |
| GET    | `/auth/reset-password/validate`  | Validar token de reset           | ❌              |
| GET    | `/auth/google`                   | Iniciar login com Google OAuth   | ❌              |
| GET    | `/auth/google/callback`          | Callback do Google OAuth         | ❌              |

---

## 🔑 Tokens JWT

- **accessToken**: JWT com validade de **1 hora** (3600 segundos). Deve ser enviado no header `Authorization: Bearer <token>`.
- **refreshToken**: Token opaco (hex 32 bytes), hash SHA-256 salvo no banco. Validade de **7 dias**. Usado para emitir novos accessTokens sem novo login.
- **Rotação de tokens**: A cada `/auth/refresh`, o refreshToken antigo é revogado e um novo par é emitido.

---

## 🔒 Segurança

- Senhas armazenadas com **Argon2ID** (hashing adaptativo).
- Bloqueio automático da conta após **3 tentativas de login incorretas** → `ACCOUNT_LOCKED`.
- **Rate limiting** de IPs para o endpoint de login.
- 2FA via TOTP (Google Authenticator compatível).
- Reset de senha desbloqueio automático da conta (`failed_login_attempts` zerado após reset).
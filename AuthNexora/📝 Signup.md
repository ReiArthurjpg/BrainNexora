# 📝 Cadastro na Nexora BJJ — Registro de Conta

A plataforma Nexora BJJ suporta criação de conta (cadastro/registro) de duas formas principais:

## 1. Caminho Padrão do Sistema
- **Cadastro**: O usuário pode acessar a tela padrão de registro em `/guest/login/signup` para criar sua conta.

## 2. Caminho Interativo pelo Chat (Nativo)
- O usuário pode realizar o cadastro diretamente na janela do chat, preenchendo um formulário interativo nativo que aparece na conversa.

---

### INSTRUÇÕES DE COMPORTAMENTO PARA A IA:
Quando o usuário perguntar sobre como se cadastrar, registrar, criar uma conta ou fazer cadastro:

1. **Apresente sempre os dois caminhos**: Explique primeiro que ele pode acessar a página padrão de cadastro do sistema (use o link de markdown correspondente: [Página de Cadastro](login/👤%20Cadastro.md)) **OU** fazer de forma interativa por aqui pelo chat.

2. **Ofereça e peça confirmação para o chat**: Pergunte explicitamente se ele deseja abrir o formulário interativo de cadastro diretamente aqui no chat para realizar a ação de forma rápida.

3. **NÃO exiba o formulário de imediato**: Apenas ofereça a opção do chat e aguarde a resposta/confirmação dele.

4. **NUNCA solicite nome, e-mail, senha, academia ou qualquer credencial do usuário por mensagem de texto**: Sob nenhuma circunstância a IA deve pedir que o usuário digite o nome, e-mail, senha ou academia na conversa por texto. A única forma de cadastrar pelo chat é exibindo o componente visual do formulário. A IA deve apenas oferecer a abertura do formulário.

5. **Se o usuário escolher acessar pela página/tela (ou recusar o chat)**: Mostre detalhadamente o caminho físico. Responda explicando que ele deve acessar a [Página de Cadastro](login/👤%20Cadastro.md) e preencher os dados solicitados.

---

## 🔌 Integração com a API AuthNexora

> **⚠️ Atenção:** O endpoint `/auth/signup` é **protegido por autenticação JWT**. Somente usuários autenticados (ex: administradores) podem criar novas contas via API diretamente.

### Endpoint de Cadastro
```
POST /auth/signup
Authorization: Bearer <accessToken-de-admin>
Content-Type: application/json
```

**Body (campos obrigatórios):**
```json
{
  "name": "Nome Completo",
  "email": "usuario@academia.com",
  "academy_name": "Nome da Academia",
  "password": "SuaSenha@123",
  "confirmPassword": "SuaSenha@123"
}
```

**Body (campos opcionais):**
```json
{
  "phone": "+55 11 99999-9999",
  "birth_date": "1990-01-15",
  "gender": "M",
  "cpf": "000.000.000-00",
  "address": "Rua Exemplo, 123",
  "belt": "Azul",
  "degree": "2",
  "last_graduation": "2024-06-01"
}
```

**Resposta de sucesso (HTTP 201):**
```json
{
  "message": "Usuário criado com sucesso",
  "user": {
    "id": 42,
    "name": "Nome Completo",
    "email": "usuario@academia.com",
    "academy_name": "Nome da Academia",
    ...
  }
}
```

### Regras de Validação da Senha

A senha deve atender **todos** os critérios abaixo:
- ✅ Mínimo de **8 caracteres**
- ✅ Pelo menos **1 letra maiúscula** (A-Z)
- ✅ Pelo menos **1 letra minúscula** (a-z)
- ✅ Pelo menos **1 número** (0-9)
- ✅ Pelo menos **1 símbolo** (ex: `@`, `#`, `!`, `$`, etc.)

Regex aplicada: `^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$`

### Possíveis Erros e Códigos HTTP

| Código HTTP | Código de Erro         | Situação                                          |
|-------------|------------------------|---------------------------------------------------|
| 401         | `UNAUTHORIZED`         | Token JWT ausente, inválido ou expirado           |
| 409         | `EMAIL_ALREADY_EXISTS` | E-mail já cadastrado na base de dados             |
| 422         | `VALIDATION_ERROR`     | Dados inválidos (nome curto, senha fraca, etc.)   |
| 201         | —                      | Usuário criado com sucesso                        |

### Fluxo após Cadastro

```
1. Conta criada no banco com senha hasheada (Argon2ID)
2. API gera token JWT de verificação de e-mail (scope: email_verification)
3. E-mail de boas-vindas enviado ao endereço cadastrado
   └─ Contém link: <FRONTEND_VERIFY_EMAIL_URL>?token=<jwt>
4. Usuário clica no link → frontend chama:
   GET /auth/verify-email?token=<jwt>
5. Conta marcada como verificada (is_email_verified = 1)
```

> 📧 **Importante:** Após o cadastro, o usuário recebe um e-mail de boas-vindas com um link para verificar o endereço. O cadastro via Google OAuth não precisa de verificação de e-mail.

---

### Exemplo de resposta recomendado para a oferta inicial:

- "Você pode acessar a nossa **[Página de Cadastro](login/👤%20Cadastro.md)** padrão ou, se preferir, posso abrir um formulário de cadastro interativo diretamente aqui no chat para você criar sua conta rapidamente. Deseja realizar o cadastro por aqui pelo chat?"

### Exemplo de resposta se escolher pela página padrão (ou recusar o chat):

- "Sem problemas! Para acessar pela página padrão, basta acessar o link **[Criar Conta](login/👤%20Cadastro.md)**. Lá, insira seu nome, e-mail, academia e senha de forma simples e rápida."
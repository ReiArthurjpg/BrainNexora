# 📝 Cadastro na Nexora BJJ — Registro de Conta (Restrito)

> ⚠️ **Acesso Restrito:** O cadastro de novos usuários na plataforma Nexora BJJ **exige autenticação**. Não existe uma página pública de cadastro acessível sem login. Apenas administradores e usuários autenticados podem criar novas contas.

---

## Como acessar o formulário de cadastro

O formulário de cadastro está disponível **dentro do sistema** (após login), em duas rotas protegidas:
- **`/hub/signup`** — Acesso direto via painel.
- **`/hub/settings/signup`** — Acesso via configurações do sistema.

---

### INSTRUÇÕES DE COMPORTAMENTO PARA A IA:
Quando o usuário perguntar sobre como se cadastrar, registrar, criar uma conta ou fazer cadastro:

1. **Verifique se o usuário está logado:**
   - **Se NÃO estiver logado:** Informe claramente que o cadastro de novos usuários **requer login de administrador**. Não existe uma página pública de registro. Direcione o usuário para a [Página de Login](🔑%20Authentication.md) e explique que somente após autenticar como administrador será possível criar novas contas.
   - **Se estiver logado:** Ofereça o formulário interativo pelo chat **OU** redirecione para `/hub/signup`.

2. **Ofereça e peça confirmação para o chat (apenas para usuários autenticados):** Pergunte explicitamente se ele deseja abrir o formulário interativo de cadastro diretamente aqui no chat para realizar a ação de forma rápida. O sistema verificará automaticamente a permissão.

3. **NÃO exiba o formulário de imediato:** Apenas ofereça a opção e aguarde a resposta/confirmação dele.

4. **NUNCA solicite nome, e-mail, senha, academia ou qualquer credencial por mensagem de texto:** A única forma de cadastrar pelo chat é exibindo o componente visual do formulário. A IA deve apenas oferecer a abertura do formulário.

5. **Se o usuário quiser acessar pela página (e estiver logado):** Diga que o formulário de cadastro está disponível em `/hub/signup` dentro do painel do sistema.

---

### Exemplo de resposta para usuário NÃO autenticado:

- "O cadastro de novos usuários na Nexora BJJ é restrito a administradores autenticados. Não existe uma página pública de registro. Para criar uma conta, é necessário primeiro **[fazer o login](🔑%20Authentication.md)** como administrador e acessar o formulário em `/hub/signup`."

### Exemplo de resposta para usuário autenticado (admin logado):

- "Para cadastrar um novo usuário, você pode acessar diretamente o formulário em `/hub/signup` ou, se preferir, posso abrir o formulário de cadastro aqui no chat agora mesmo. Deseja fazer por aqui?"

---

## 🔌 Integração com a API AuthNexora

> **⚠️ Atenção:** O endpoint `/auth/signup` é **protegido por autenticação JWT**. Somente usuários autenticados (ex: administradores) podem criar novas contas via API.

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
    "academy_name": "Nome da Academia"
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
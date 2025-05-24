# Financial Wallet API

API RESTful para um sistema de carteira financeira desenvolvida com Laravel 11, permitindo que os usuários gerenciem seus saldos através de depósitos e transferências.

## Requisitos

- Docker
- Docker Compose
- Git

## Tecnologias Utilizadas

- PHP 8.2
- Laravel 11
- MySQL 8.0
- Nginx
- Docker

## Funcionalidades

- Registro e Autenticação de Usuários
- Depósito de dinheiro na carteira
- Transferência entre usuários
- Reversão de transações
- Validações de saldo e segurança

## Como Executar o Projeto

### 1. Clone o Repositório

```bash
git clone [URL_DO_REPOSITÓRIO]
cd financial-wallet-api
```

### 2. Configure o Ambiente

Copie o arquivo de ambiente de exemplo e configure as variáveis:

```bash
cp .env.example .env
```

### 3. Inicie os Containers Docker

```bash
docker-compose up -d
```

Este comando irá iniciar:
- Container da aplicação Laravel
- Container do MySQL
- Container do Nginx

### 4. Instale as Dependências

```bash
docker-compose exec app composer install
```

### 5. Gere a Chave da Aplicação

```bash
docker-compose exec app php artisan key:generate
```

### 6. Execute as Migrações

```bash
docker-compose exec app php artisan migrate
```

### 7. Acesse a Aplicação

A API estará disponível em:
```
http://localhost:8000
```

## Endpoints da API

### Autenticação
- POST /api/register - Registro de usuário
- POST /api/login - Login de usuário

### Carteira
- POST /api/deposit - Realizar depósito
- POST /api/transfer - Realizar transferência
- POST /api/revert/{transaction} - Reverter transação
- GET /api/balance - Consultar saldo
- GET /api/transactions - Histórico de transações

## Testes

Para executar os testes:

```bash
docker-compose exec app php artisan test
```

## Estrutura do Projeto

```
financial-wallet-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   │   
│   ├── Models/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
├── docker/
└── docker-compose.yml
```

# Caderneta

Controle financeiro pessoal feito com Laravel e MySQL, como projeto de portfólio. O usuário registra entradas e saídas, organiza por categoria e acompanha o saldo do mês.

> Em desenvolvimento. Já tem cadastro, lançamentos, categorias, painel com gráficos, filtros por mês e exportação em CSV. Falta o acabamento para portfólio (dados de exemplo, prints e CI).

## Tecnologias

- PHP 8.3 e Laravel 13
- MySQL
- Blade e Tailwind CSS 4, compilados com Vite
- Pest para os testes

## Como rodar localmente

Pré-requisitos: PHP 8.3+, Composer, Node 20+ e um MySQL rodando.

```bash
git clone https://github.com/oSerpa/financas.git
cd financas

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Crie um banco chamado `caderneta` no MySQL e ajuste `DB_USERNAME` e `DB_PASSWORD` no `.env`. Depois:

```bash
php artisan migrate
npm run build
php artisan serve
```

Acesse http://localhost:8000.

## Testes

```bash
php artisan test
```

Os testes usam SQLite em memória, então não precisam do MySQL.

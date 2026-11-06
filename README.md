# 📌 Guia de Instalação e Configuração da Aplicação

## ✅ Pré-requisitos
Antes de iniciar a instalação, verifique se você possui os seguintes requisitos:

- [PHP 8.2+](https://www.php.net/downloads)  
- [Docker Desktop](https://www.docker.com/products/docker-desktop)  
- [Composer](https://getcomposer.org/)  

---

## 🖥️ Instalação no Windows

### 1. Docker Desktop  
Baixe e instale o Docker Desktop através do link:  
[Download Docker Desktop](https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe?utm_source=docker&utm_medium=webreferral&utm_campaign=docs-driven-download-win-amd64&_gl=1*q2ey8e*_ga*MTc1MzE5NTEyNC4xNzU1NDYwMDE3*_ga_XJWPQMJYHQ*czE3NTU0NjAwMTckbzEkZzEkdDE3NTU0NjAwMTkkajU4JGwwJGgw)

### 2. PHP (XAMPP)  
Baixe e instale o PHP através do XAMPP:  
[Download PHP (XAMPP 8.2.12)](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe)

### 3. Composer  
Baixe e instale o Composer:  
[Download Composer](https://getcomposer.org/Composer-Setup.exe)

---

## 🐧 Instalação no Linux

Execute os seguintes comandos no terminal para instalar os pré-requisitos:

```bash
# Atualizar pacotes
sudo apt update && sudo apt upgrade -y
```

# Instalar PHP 8.2
```bash
sudo apt install -y php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-bcmath unzip curl
```

# Instalar Docker
```bash
sudo apt install -y docker.io docker-compose
```

# Instalar Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## ⚙️ Setup da Aplicação

### 1. Clonar o repositório

```bash
git clone https://github.com/rafaelrotto/prog-paralela-20252.git
```

## 2. Acessar a pasta da aplicação
```bash
cd prog-paralela-20252
```

## 3. Copiar o arquivo .env.example para .env
```bash
cp .env.example .env
```

## 3.1. Instalar as dependencias
```bash
composer install --ignore-platform-reqs
```

## 4. Subir os containers Docker
```bash
docker compose up --build -d
docker-compose up -d
```

## 5. Instalar dependências e configurar a aplicação
```bash
docker exec -it laravel_app composer install
docker exec -it laravel_app php artisan key:generate
docker exec -it laravel_app composer require predis/predis
docker exec -it laravel_app composer require league/flysystem-aws-s3-v3 "^3.0"
docker exec -it laravel_app php artisan install:broadcasting
docker exec -it laravel_app php artisan migrate
docker exec -it laravel_app php artisan db:seed --class=UsersTableSeeder
```

## 📊 Horizon (monitor de filas)

O worker de filas roda via [Laravel Horizon](https://laravel.com/docs/horizon) no container `horizon` (sobe automaticamente com `docker compose up`). O dashboard fica disponível em `http://localhost:8010/horizon`.

### Webhook CRM (avaliação)

Envie no Postman uma requisição `POST` para `http://localhost:8000/api/v1/webhooks/crm`, com `Content-Type: application/json` e o JSON no formato `{ "deal": { ... } }` informado no enunciado. Um payload válido retorna `202 Accepted` com `request_id` e `deal_id`; o processamento continua em background.

Antes do envio, aplique as migrations (`php artisan migrate`) e mantenha Redis/Horizon ativos. O job principal grava negócio e organização na fila `high`; contatos vão para `default`, e itens, atividades, campos customizados e metadados para `bulk`. Consulte `/horizon` e `storage/logs/laravel.log` para acompanhar o processamento.

## 🌐 ngrok (expor a aplicação publicamente)

O container `ngrok` **não sobe automaticamente** junto com os demais (usa `profiles`). Para usá-lo:

1. Preencha no `.env` o `NGROK_AUTHTOKEN` (https://dashboard.ngrok.com/get-started/your-authtoken) e o `NGROK_DOMAIN` (domínio reservado, ex: `seu-dominio.ngrok-free.app`).
2. Suba o serviço explicitamente:

```bash
docker compose --profile ngrok up -d ngrok
```

Para derrubar apenas ele:

```bash
docker compose --profile ngrok down ngrok
```

### Comandos úteis

## Criar model com migration

```bash
php artisan make:model NomeDaModel --migration
```

## Criar Controller na pasta App/Http/Controllers/Api no padrão resource

```bash
php artisan make:controller Api/NomeDoController --resource
```

## Criar Request

```bash
php artisan make:request NomeDaRequest
```

## Criar Middleware

```bash
php artisan make:middleware NomeDoMiddleware
```

## Criar Job

```bash
php artisan make:job NomeDoJob
```

## Criar Evento

```bash
php artisan make:event NomeDoEvent
```

## Criar Resource

```bash
php artisan make:resource NomeDoResource
```

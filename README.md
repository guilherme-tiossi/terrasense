# TerraSense

Backend inicial do TerraSense com Laravel, MySQL e Redis em Docker.

## Requisitos

- Docker
- Docker Compose

## Comandos

```bash
# Subir os containers
docker compose up -d --build

# Executar migrations
docker compose exec app php artisan migrate --force

# Executar testes
docker compose exec app php artisan test

# Parar os containers
docker compose down
```

A aplicação fica disponível em `http://localhost:8080`.

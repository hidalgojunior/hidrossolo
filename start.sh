#!/bin/bash

# Hidrossolo - Script de inicialização Docker
echo "🚀 Iniciando ambiente Hidrossolo..."
cd "$(dirname "$0")/docker"

# Permissões de escrita para o PHP (www-data = uid 82 na imagem Alpine)
# Necessário para a Biblioteca de Mídias gravar em assets/uploads
mkdir -p ../assets/uploads
chown -R 82:82 ../assets/uploads 2>/dev/null || true
chmod -R 775 ../assets/uploads 2>/dev/null || true

# Subir containers
docker compose up -d --build

echo ""
echo "✅ Ambiente iniciado!"
echo ""
echo "📍 Acessos:"
echo "   Site:       http://localhost:8083"
echo "   Admin:      http://localhost:8083/admin"
echo "   phpMyAdmin: http://localhost:8081"
echo "   Redis:      localhost:6379"
echo ""
echo "📋 Comandos úteis:"
echo "   docker compose -f docker/docker-compose.yml down    # Parar containers"
echo "   docker compose -f docker/docker-compose.yml logs -f # Ver logs"

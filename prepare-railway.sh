#!/bin/bash

# 🚀 Script de Preparación para Railway - La Caza

set -e

echo "📦 Preparando proyecto para despliegue en Railway..."

# 1. Verificar estructura
if [ ! -d "backend" ] || [ ! -d "lacaza-frontend" ]; then
    echo "❌ Error: No se encontraron directorios 'backend' y 'lacaza-frontend'"
    exit 1
fi

# 2. Copiar archivos necesarios a raíz
echo "📋 Copiando archivos de configuración..."

cp backend/composer.json .
cp backend/composer.lock .
cp backend/package.json .
cp backend/vite.config.js .

# 3. Crear archivos faltantes
echo "✨ Creando archivos de Railway..."

# Procfile
cat > Procfile << 'EOF'
web: vendor/bin/heroku-php-nginx -C nginx.conf public/
EOF

# nginx.conf
cat > nginx.conf << 'EOF'
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    fastcgi_pass heroku-fcgi;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}
EOF

# runtime.txt
echo "php-8.2.0" > runtime.txt

# railway.json
cat > railway.json << 'EOF'
{
  "build": {
    "builder": "heroku.buildpacks"
  },
  "deploy": {
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 3
  }
}
EOF

# 4. Crear .gitignore si no existe
if [ ! -f ".gitignore" ]; then
    cat > .gitignore << 'EOF'
/vendor/
/node_modules/
.env
.env.local
.DS_Store
*.log
/storage/logs/
/bootstrap/cache/
/public/hot
.vite/
dist/
EOF
fi

# 5. Copiar .env.example del backend
if [ -f "backend/.env.example" ]; then
    cp backend/.env.example .env.example
fi

# 6. Mostrar resumen
echo ""
echo "✅ Preparación completada!"
echo ""
echo "📝 Siguientes pasos:"
echo "1. Configura variables en Railway Dashboard"
echo "2. Conecta MySQL en Railway"
echo "3. Git push y verifica logs"
echo ""
echo "📋 Archivos creados/modificados:"
echo "   - Procfile"
echo "   - nginx.conf"
echo "   - runtime.txt"
echo "   - railway.json"
echo "   - .gitignore"
echo "   - composer.json (en raíz)"
echo "   - package.json (en raíz)"
echo ""
echo "🔗 Documentación: RAILWAY_DEPLOYMENT_GUIDE.md"

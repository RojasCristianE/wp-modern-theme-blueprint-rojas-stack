#!/bin/bash
# ddev-generated
# Este script se ejecuta dentro del contenedor después de iniciar DDEV.
# Su propósito es inicializar el "Core Fantasma" de WordPress en el volumen invisible.

WP_PATH="/var/www/wp-core"
THEME_NAME="rojas-stack"
THEME_DIR="$WP_PATH/wp-content/themes/$THEME_NAME"
BLUEPRINT_DIR="/var/www/html"

echo "⏳ Verificando el Core Fantasma de WordPress..."

# Asegurar que el usuario actual tenga permisos sobre el volumen invisible
CURRENT_USER=$(whoami)
CURRENT_GROUP=$(id -gn)
sudo chown -R $CURRENT_USER:$CURRENT_GROUP $WP_PATH
sudo chmod -R 755 $WP_PATH

if [ ! -f "$WP_PATH/wp-config.php" ]; then
    echo "📦 Construyendo WordPress en la dimensión paralela ($WP_PATH)..."
    
    # 1. Descargar WordPress
    wp core download --path=$WP_PATH --allow-root
    
    # 2. Configurar la Base de Datos (usando variables nativas de DDEV)
    wp config create --dbname=db --dbuser=db --dbpass=db --dbhost=db --path=$WP_PATH --allow-root
    
    # 3. Instalar WordPress
    wp core install --url="https://$DDEV_HOSTNAME" --title="Rojas Stack (Ghost Core)" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --path=$WP_PATH --allow-root
    
    # 4. Eliminar temas por defecto para mantener la pureza
    wp theme delete twentytwentythree twentytwentyfour twentytwentyfive --path=$WP_PATH --allow-root
    
    echo "✨ Core Fantasma creado con éxito."
else
    echo "✅ El Core Fantasma ya está activo."
fi

# 5. Asegurar el enlace simbólico del tema
if [ ! -L "$THEME_DIR" ]; then
    echo "🔗 Conectando tu código anfitrión ($BLUEPRINT_DIR) con el Core Fantasma..."
    ln -s $BLUEPRINT_DIR $THEME_DIR
    
    # 6. Activar el tema
    wp theme activate $THEME_NAME --path=$WP_PATH --allow-root
    echo "🚀 Rojas Stack activado."
fi

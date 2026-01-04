#!/bin/bash

# Nombre del contenedor donde está MySQL
CONTAINER_NAME="mysql"

# Directorio en el host donde se guardarán los backups
HOST_BACKUP_DIR="/opt/docker/projects/anansi_accounting/backups"

# Nombre del archivo de backup (con fecha y hora)
BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S).sql"

# Directorio temporal en el contenedor para almacenar el backup antes de copiarlo
CONTAINER_BACKUP_DIR="/opt"

# Usuario y contraseña de MySQL
# se recomienda utilizar un usuario con permisos en todas las BDs
MYSQL_USER="root"
MYSQL_PASSWORD="4z1KK65LwvK"

# Listado de bases de datos a respaldar (separadas por espacio)
DATABASES=("costeo") # Reemplaza con las bases de datos que desees respaldar

# Verificar que el directorio de backups en el host existe, si no crearlo
if [ ! -d "$HOST_BACKUP_DIR" ]; then
  mkdir -p $HOST_BACKUP_DIR
fi

# Crear backup para cada base de datos
for DB in "${DATABASES[@]}"; do
    echo "Realizando backup de la base de datos: $DB"
    
    # Nombre de archivo por base de datos
    BACKUP_FILE="backup_${DB}_$(date +%Y%m%d_%H%M%S).sql"
    ZIP_FILE="${BACKUP_FILE%.sql}.zip"
    
    # Crear el backup dentro del contenedor
    docker exec $CONTAINER_NAME /usr/bin/mysqldump -u $MYSQL_USER -p$MYSQL_PASSWORD $DB > $HOST_BACKUP_DIR/$BACKUP_FILE
    
    # Verificar si el backup fue exitoso
    if [ $? -eq 0 ]; then
        echo "Backup de $DB realizado con éxito dentro del contenedor."
    else
        echo "Error al realizar el backup de $DB."
        exit 1
    fi

    # Comprimir el archivo SQL en un .zip
    echo "Comprimiendo el backup de $DB en $ZIP_FILE"
    zip -j "$HOST_BACKUP_DIR/$ZIP_FILE" "$HOST_BACKUP_DIR/$BACKUP_FILE"

    # Verificar si la compresión fue exitosa
    if [ $? -eq 0 ]; then
        echo "Backup de $DB comprimido con éxito en: $HOST_BACKUP_DIR/$ZIP_FILE"
        # Eliminar el archivo SQL original después de la compresión
        rm "$HOST_BACKUP_DIR/$BACKUP_FILE"
    else
        echo "Error al comprimir el backup de $DB."
        exit 1
    fi

    
done

# Eliminar backups de más de 5 días
echo "Eliminando backups de más de 5 días de antigüedad."
find "$HOST_BACKUP_DIR" -name "*.zip" -type f -mtime +5 -exec rm {} \;

echo "Todos los backups completados, comprimidos y antiguos eliminados."
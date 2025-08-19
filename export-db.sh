#!/bin/bash
echo "=== Exportando Banco de Dados Local ==="

# Configurações do LocalWP (ajuste conforme seu ambiente)
LOCAL_DB="local"
LOCAL_USER="root"
LOCAL_PASS="root"  # Verifique a senha no LocalWP
LOCAL_URL="http://cl.local/"  # URL do seu localhost
HOMOLOG_URL="https://homolog.colinedumauthioz.ch"     # URL do EC2

BACKUP_FILE="backup-$(date +%Y%m%d-%H%M%S).sql"

# Exportar banco
mysqldump -u $LOCAL_USER -p$LOCAL_PASS $LOCAL_DB > $BACKUP_FILE

# Substituir URLs (crítico para Elementor)
sed -i "s|$LOCAL_URL|$HOMOLOG_URL|g" $BACKUP_FILE
sed -i "s|http://homolog.colinedumauthioz.ch|https://homolog.colinedumauthioz.ch|g" $BACKUP_FILE

echo "Backup criado: $BACKUP_FILE"
echo "=== Exportação Concluída ==="

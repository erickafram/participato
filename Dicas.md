SUBIR GIT
git add .
git commit -m "Fix: Corrigir middleware legacyRedirect para não bloquear URLs AMP válidas"
git push -u origin main


Servidor de Produção
cd /home/portalconvictos/htdocs/portalconvictos.com.br/portalconvictos#
git pull origin main

# Rodar migrations pendentes
npx sequelize-cli db:migrate

# Se precisar desfazer a última migration
npx sequelize-cli db:migrate:undo

# Se precisar rodar seeders (dados iniciais)
npx sequelize-cli db:seed:all

pm2 restart all
pm2 restart portalconvictos

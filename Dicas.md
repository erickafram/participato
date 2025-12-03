SUBIR GIT
git add .
git commit -m "Fix: Corrigir middleware legacyRedirect para não bloquear URLs AMP válidas"
git push -u origin main


Servidor de Produção
cd /home/portalconvictos/htdocs/portalconvictos.com.br/portalconvictos
git pull origin main
npx sequelize-cli db:migrate
pm2 restart portalconvictos

FROM php:8.2-apache
# Copie tous tes fichiers dans le dossier web par défaut
COPY . /var/www/html/
# Donne les permissions nécessaires pour éviter l'erreur status 1
RUN chown -R www-data:www-data /var/www/html
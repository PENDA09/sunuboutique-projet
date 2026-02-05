FROM php:8.2-apache
COPY . /var/www/html/
# Force Apache à écouter sur le port que Render lui donne
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
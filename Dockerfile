FROM php:8.2-apache

# ── Extensions PHP nécessaires ──────────────────────────────────────────────
RUN docker-php-ext-install pdo pdo_mysql mysqli

# ── Activer mod_rewrite Apache ───────────────────────────────────────────────
RUN a2enmod rewrite

# ── Copier la configuration Apache personnalisée ─────────────────────────────
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# ── Répertoire de travail ────────────────────────────────────────────────────
WORKDIR /var/www/html

# ── Copier le code source ────────────────────────────────────────────────────
COPY . .

# ── Permissions dossier images (uploads admin) ───────────────────────────────
RUN mkdir -p assets/images/products \
    && chown -R www-data:www-data assets \
    && chmod -R 755 assets

EXPOSE 80

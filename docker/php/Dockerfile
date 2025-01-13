# Imagem base com PHP 8.1 pré-instalado
FROM php:8.1-fpm

# Maintainer do Docker
LABEL maintainer="ricardov7070@gmail.com"

# Atualizando o sistema e instalando dependências básicas
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git \
    libzip-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    procps && \
    apt-get clean

# Instalando extensões PHP adicionais
RUN docker-php-ext-install zip mbstring curl xml bcmath

# Instalando o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalando o Node.js na versão 18.16.0
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g n && \
    n 18.16.0 && \
    apt-get remove -y nodejs && \
    apt-get autoremove -y

# Verificando as versões do PHP e Node.js
RUN php -v && node -v && npm -v

# Configurando o diretório de trabalho
WORKDIR /var/www/laravel

# Instala as dependências necessárias e a extensão PDO MySQL
RUN apt-get update && apt-get install -y \
    libmariadb-dev && \
    docker-php-ext-install pdo_mysql

# Limpe os arquivos temporários para reduzir o tamanho da imagem
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiando arquivos do projeto para o container
COPY . .

# Instalando dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Instalando Laravel Sanctum
RUN composer require laravel/sanctum && \
    php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
    
# Gerando a chave do Laravel
RUN php artisan key:generate

# Configurando permissões
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/laravel

# Instalando dependências de frontend do Laravel (opcional, para Laravel Mix ou Vite)
RUN npm install && npm run build

# Instala a biblioteca PHPMailer
RUN composer require phpmailer/phpmailer

# Instala o Swagger
RUN composer require darkaonline/l5-swagger && \
    php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

# Instala o Redis
RUN composer require predis/predis:^2.0

# Copiando arquivos do projeto para o container
COPY . .

# Expondo a porta 5000
EXPOSE 5000

# Comando para iniciar o servidor embutido do Laravel
CMD ["php-fpm"]


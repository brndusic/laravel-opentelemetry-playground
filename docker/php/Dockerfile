FROM php:8.1.10-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    mariadb-client \
    locales \
    zip \
    unzip \
    curl

# Install extensions
RUN docker-php-ext-install pdo_mysql

RUN pecl -q install xdebug  && \
    docker-php-ext-enable xdebug

ARG USER_NAME="root"
ARG UID=0
RUN if [ ${UID} > 0 ] ; then \
     apt-get update; \
     apt-get -y install sudo && \
     groupadd -g ${UID} ${USER_NAME} && \
     useradd -r -m -u ${UID} -g ${USER_NAME} ${USER_NAME} && echo "${USER_NAME}:${USER_NAME}" | chpasswd \
;fi

RUN sudo adduser ${USER_NAME} www-data

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

USER ${USER_NAME}

# Set working directory
WORKDIR /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

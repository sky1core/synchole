FROM ubuntu:18.04

### Setup environment
ENV DEBIAN_FRONTEND noninteractive

ENV APP_NAME synchole


RUN apt-get update && apt-get install -y --no-install-recommends \
    locales \
    software-properties-common

### Ensure UTF-8
RUN locale-gen en_US.UTF-8
ENV LANG       en_US.UTF-8
ENV LC_ALL     en_US.UTF-8

### UTC Time -> KST
RUN ln -sf /usr/share/zoneinfo/Asia/Seoul /etc/localtime

### prepare apt-repository
RUN add-apt-repository -y ppa:ondrej/php \
    && add-apt-repository -y ppa:nginx/stable

### Update sources
RUN apt-get update && apt-get install -y --no-install-recommends \
    docker.io \
    vim \
    bash-completion \
    unzip \
    curl \
    sudo \
    git \
    supervisor \
    nginx \
    php7.3 \
    php7.3-fpm \
    php7.3-cli \
    php7.3-curl \
    php7.3-mbstring \
    php7.3-xml \
    php7.3-zip \
    php7.3-sqlite3 \
    php-redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

### supervisord
RUN mkdir -p /var/log/supervisor
ADD docker/supervisord.conf /etc/supervisor/conf.d/

### php override
ADD docker/php-overrides.ini /etc/php/7.3/fpm/conf.d/99-overrides.ini
ADD docker/php-overrides.ini /etc/php/7.3/cli/conf.d/99-overrides.ini


### nginx config
ADD docker/nginx.conf /etc/nginx/sites-available/app
RUN ln -s /etc/nginx/sites-available/app /etc/nginx/sites-enabled/app \
    && rm /etc/nginx/sites-enabled/default




RUN service php7.3-fpm start


### install composer(global)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer


RUN adduser --disabled-password --gecos "" synchole \
    && usermod -a -G www-data synchole \
    && usermod -a -G sudo synchole \
    && echo synchole ALL=NOPASSWD: ALL >> /etc/sudoers



RUN mkdir -p /app/synchole

ADD docker/entrypoint.sh /app/
RUN chmod 744 /app/entrypoint.sh
RUN chown -R synchole:synchole /app

USER synchole

### crontab 설정
ADD docker/app_cron /app/

COPY --chown=synchole . /app/synchole

WORKDIR /app/synchole

RUN sudo chgrp -R www-data storage bootstrap/cache \
    && sudo chmod -R ug+rwx storage bootstrap/cache

RUN composer install --no-dev \
    && composer clearcache
RUN mkdir -p /app/data


EXPOSE 80 443
VOLUME ["/app/synchole", "/app/data"]

CMD ["/app/entrypoint.sh"]

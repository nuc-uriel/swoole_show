FROM php:7.2.0-fpm

# 添加阿里云镜像
# RUN sed -i s/deb.debian.org/mirrors.aliyun.com/g /etc/apt/sources.list && \
#     sed -i s/security.debian.org/mirrors.aliyun.com/g /etc/apt/sources.list

# 更新、安装、清除包
RUN apt-get update && \
    apt-get install -y git curl libmcrypt-dev libjpeg-dev libpng-dev libfreetype6-dev libbz2-dev openssl libhiredis-dev && \
    apt-get clean

# 安装PHP扩展
RUN docker-php-ext-install pdo pdo_mysql  zip gd pcntl opcache bcmath sockets && \
pecl install inotify mcrypt redis && \
docker-php-ext-enable inotify mcrypt redis

# 拉取swoole源码
RUN git clone https://gitee.com/swoole/swoole.git

WORKDIR ./swoole

# 安装swoole
RUN phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable swoole

WORKDIR /var/www/html/

# 拉取swoole-ext-async源码
RUN git clone https://github.com/swoole/ext-async.git

WORKDIR ./ext-async

# 安装swoole-ext-async
RUN phpize && \
    ./configure && \
    make -j 4 && \
    make install && \
    docker-php-ext-enable swoole_async

WORKDIR /var/www/html/

# 删除swoole源码
RUN rm -R swoole && \
    rm -R ext-async

# 添加composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# 更换composer镜像
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# 设置为中国时区
RUN cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

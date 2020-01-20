FROM php:7.2-cli
RUN mkdir /app
COPY . /app
WORKDIR /app
ENTRYPOINT ["./vendor/bin/phpunit"]

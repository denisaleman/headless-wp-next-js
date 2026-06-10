FROM wordpress:cli-php8.2

RUN apk add --no-cache mysql-client
WORKDIR /var/www/html
ENTRYPOINT ["wp"]
CMD ["--info"]

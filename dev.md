#### testing php74

    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/redis-ext && exec bash"

    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/redis-ext && composer update"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/redis-ext && composer install"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/redis-ext && sh phpunit"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/redis-ext && git status"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/redis-ext && git pull"

    docker exec -it dev-redis sh -c "redis-cli"

# Grzegorz Kępa - aplikacja do weLearning

## Źródło podstawowe z kodem

Skrypt PHP znajduje się w _php/src/index.php_

## Kroki instalacji

1. zainstalować docker https://docs.docker.com/get-docker/;
2. "docker up" w tym folderze;
3. wejść na http://localhost:8080/ (z docker-compose.yml tu powinien tu być zainstalowany phpadmin):
   1. login _root_, hasło _mysqlRoot123_
   2. przejść do bazy _gregkepa_app_welearning_
   3. wykonać skrypt _./mysql/exchange_rate.sql_
4. wejść na http://localhost:8000/ (z docker-compose.yml tu powinna tu być strona z kursami walut).

## Dodatki

Tabela ze wszystkimi walutami w pliku _./mysql/currency.sql_.

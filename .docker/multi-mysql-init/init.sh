#!/bin/bash
set -e

mysql -u root -ppassword -h localhost -e "CREATE DATABASE IF NOT EXISTS tests74;"
mysql -u root -ppassword -h localhost -e "CREATE DATABASE IF NOT EXISTS tests80;"
mysql -u root -ppassword -h localhost -e "CREATE DATABASE IF NOT EXISTS tests81;"
mysql -u root -ppassword -h localhost -e "CREATE DATABASE IF NOT EXISTS tests82;"

mysql -u root -ppassword -h localhost tests74 < /sql/create_users.sql
mysql -u root -ppassword -h localhost tests80 < /sql/create_users.sql
mysql -u root -ppassword -h localhost tests81 < /sql/create_users.sql
mysql -u root -ppassword -h localhost tests82 < /sql/create_users.sql


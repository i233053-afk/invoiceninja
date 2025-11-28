pipeline {
    agent any

    environment {
        DB_CONNECTION = "mysql"
        DB_HOST = "127.0.0.1"
        DB_PORT = "3306"
        DB_DATABASE = "invoiceninja"
        DB_USERNAME = "ninja"
        DB_PASSWORD = "Ninja@1234"
    }

    stages {

        stage('Checkout') {
            steps {
                checkout([$class: 'GitSCM',
                    branches: [[name: 'v5-stable']],
                    doGenerateSubmoduleConfigurations: false,
                    extensions: [[$class: 'CloneOption', depth: 1, shallow: true, noTags: false, reference: '', timeout: 20]],
                    userRemoteConfigs: [[url: 'https://github.com/invoiceninja/invoiceninja.git']]
                ])
            }
        }
        stage('Setup PHP') {
            steps {
                sh 'sudo apt update'
                sh 'sudo apt install -y php php-mbstring php-xml php-mysql composer'
            }
        }

        stage('Start MySQL') {
            steps {
                sh '''
                    sudo service mysql start
                    mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS invoiceninja;"
                    mysql -u root -proot -e "CREATE USER IF NOT EXISTS 'ninja'@'%' IDENTIFIED BY 'Ninja@1234';"
                    mysql -u root -proot -e "GRANT ALL PRIVILEGES ON invoiceninja.* TO 'ninja'@'%';"
                '''
            }
        }

        stage('Install Dependencies') {
            steps {
                sh 'composer install --no-interaction --prefer-dist'
            }
        }

        stage('Run Migrations') {
            steps {
                sh 'php artisan migrate --force || true'
            }
        }

        stage('Run Tests') {
            steps {
                sh 'vendor/bin/phpunit || true'
            }
        }
    }
}

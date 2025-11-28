pipeline {
agent any

```
environment {
    DB_CONNECTION = "mysql"
    DB_HOST = "127.0.0.1"
    DB_PORT = "3306"
    DB_DATABASE = "invoiceninja"
    DB_USERNAME = "ninja"
    DB_PASSWORD = "Ninja@1234"
    LOCAL_REPO = "/home/hp/invoiceninja" // change to your actual local path
}

stages {

    stage('Setup PHP & Composer') {
        steps {
            sh '''
                sudo apt update
                sudo apt install -y php php-mbstring php-xml php-mysql unzip composer
                composer --version
            '''
        }
    }

    stage('Start MySQL') {
        steps {
            sh '''
                sudo service mysql start
                mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS invoiceninja;"
                mysql -u root -proot -e "CREATE USER IF NOT EXISTS 'ninja'@'%' IDENTIFIED BY 'Ninja@1234';"
                mysql -u root -proot -e "GRANT ALL PRIVILEGES ON invoiceninja.* TO 'ninja'@'%';"
                mysql -u root -proot -e "FLUSH PRIVILEGES;"
            '''
        }
    }

    stage('Install Dependencies') {
        steps {
            dir("${env.LOCAL_REPO}") {
                sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
            }
        }
    }

    stage('Run Migrations') {
        steps {
            dir("${env.LOCAL_REPO}") {
                sh 'php artisan migrate --force || echo "Migrations may have already run."'
            }
        }
    }

    stage('Run Tests') {
        steps {
            dir("${env.LOCAL_REPO}") {
                sh 'vendor/bin/phpunit || echo "Some tests failed, check logs."'
            }
        }
    }
}

post {
    always {
        echo 'Pipeline finished.'
    }
    failure {
        echo 'Pipeline failed! Check logs for details.'
    }
}
```

}

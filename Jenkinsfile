pipeline {
    agent any

    environment {
        LARAVEL_DIR = '/home/quest/uat/qbooks'
        GIT_REPO = 'https://github.com/bashman1/stock-management.git'
        GIT_BRANCH = 'bash'
        DOCKER_COMPOSE = 'docker compose -f compose.dev.yaml'
    }

    stages {
        stage('Clone Laravel Project') {
            steps {
                script {
                    sh "rm -rf ${LARAVEL_DIR}"
                    sh "git clone -b ${GIT_BRANCH} ${GIT_REPO} ${LARAVEL_DIR}"
                }
            }
        }

        stage('Start Docker Containers') {
            steps {
                dir("${LARAVEL_DIR}") {
                    sh "${DOCKER_COMPOSE} up -d"
                }
            }
        }

        stage('Install Composer Dependencies') {
            steps {
                sh "${DOCKER_COMPOSE} exec app composer install --no-interaction --prefer-dist --optimize-autoloader"
            }
        }

        stage('Laravel Setup') {
            steps {
                sh """
                    ${DOCKER_COMPOSE} exec app cp /home/quest/uat/env/.env /home/quest/uat/qbooks
                    ${DOCKER_COMPOSE} exec app php artisan key:generate
                    ${DOCKER_COMPOSE} exec app php artisan config:cache
                    ${DOCKER_COMPOSE} exec app php artisan migrate --force
                """
            }
        }
    }

    post {
        success {
            echo '✅ Laravel environment set up successfully!'
        }
        failure {
            echo '❌ Setup failed.'
        }
    }
}

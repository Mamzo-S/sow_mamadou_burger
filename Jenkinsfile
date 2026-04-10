pipeline {
    agent any

    environment {
        REPOSITORY_URL = 'https://github.com/Mamzo-S/sow_mamadou_burger.git'
        BRANCH_TO_BUILD = 'sow_mamadou_burger'
        DOCKER_IMAGE = 'isi-burger'
    }

    triggers {
        githubPush()
    }

    options {
        timestamps()
        disableConcurrentBuilds()
        buildDiscarder(logRotator(numToKeepStr: '5'))
        timeout(time: 20, unit: 'MINUTES')
        skipDefaultCheckout(true)
    }

    stages {

        stage('Pull depuis GitHub') {
            steps {
                echo 'Recuperation du code depuis GitHub...'
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: "*/${env.BRANCH_TO_BUILD}"]],
                    userRemoteConfigs: [[url: "${env.REPOSITORY_URL}"]]
                ])
                echo "Code recupere depuis la branche ${env.BRANCH_TO_BUILD}"
            }
        }

        stage('Preparation Laravel') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'test -f .env || cp .env.example .env'
                        sh 'mkdir -p database'
                        sh 'test -f database/database.sqlite || touch database/database.sqlite'
                    } else {
                        bat 'if not exist .env copy .env.example .env'
                        bat 'if not exist database mkdir database'
                        bat 'if not exist database\\database.sqlite type nul > database\\database.sqlite'
                    }
                }
            }
        }

        stage('Installation des dependances Laravel') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
                    } else {
                        bat 'composer install --no-interaction --prefer-dist --optimize-autoloader'
                    }
                }
            }
        }

        stage('Creation de l image Docker') {
            steps {
                script {
                    if (isUnix()) {
                        sh "docker build -t ${env.DOCKER_IMAGE}:latest -t ${env.DOCKER_IMAGE}:${env.BUILD_NUMBER} ."
                    } else {
                        bat "docker build -t ${env.DOCKER_IMAGE}:latest -t ${env.DOCKER_IMAGE}:${env.BUILD_NUMBER} ."
                    }
                }
            }
        }

        stage('Deploiement local') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker compose down --remove-orphans || true'
                        sh 'docker compose up -d'
                        sh 'sleep 5'
                        sh 'docker compose ps'
                    } else {
                        bat 'docker compose down --remove-orphans 2>NUL || ver >NUL'
                        bat 'docker compose up -d'
                        bat 'timeout /t 5 /nobreak >NUL'
                        bat 'docker compose ps'
                    }
                }
            }
        }

    }

    post {
        success {
            echo "============================================"
            echo "Pipeline terminee avec succes !"
            echo "Branche : ${env.BRANCH_TO_BUILD}"
            echo "Build #${env.BUILD_NUMBER}"
            echo "Application disponible sur http://localhost:8000"
            echo "============================================"
        }
        failure {
            echo "============================================"
            echo "Le pipeline a echoue au build #${env.BUILD_NUMBER}"
            echo "============================================"
            script {
                if (isUnix()) {
                    sh 'docker compose logs --tail=30 || true'
                } else {
                    bat 'docker compose logs --tail=30 2>NUL || ver >NUL'
                }
            }
        }
        always {
            echo "Build #${env.BUILD_NUMBER} termine."
        }
    }
}

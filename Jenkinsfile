pipeline {
    agent any

    options {
        timestamps()
        disableConcurrentBuilds()
        skipDefaultCheckout(true)
    }

    parameters {
        string(name: 'REPOSITORY_URL', defaultValue: 'https://github.com/Mamzo-S/sow_mamadou_burger.git', description: 'Depot GitHub a recuperer')
        string(name: 'BRANCH_TO_BUILD', defaultValue: 'sow_mamadou_burger', description: 'Branche GitHub a builder')
        string(name: 'DOCKER_IMAGE', defaultValue: 'isi-burger', description: 'Nom de l image Docker')
    }

    stages {
        stage('Pull depuis GitHub') {
            steps {
                git branch: "${params.BRANCH_TO_BUILD}", url: "${params.REPOSITORY_URL}"
            }
        }

        stage('Preparation Laravel') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'test -f .env || cp .env.example .env'
                        sh 'test -f database/database.sqlite || touch database/database.sqlite'
                    } else {
                        bat 'if not exist .env copy .env.example .env'
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
                        sh "docker build -t ${params.DOCKER_IMAGE}:latest -t ${params.DOCKER_IMAGE}:${env.BUILD_NUMBER} ."
                    } else {
                        bat "docker build -t ${params.DOCKER_IMAGE}:latest -t ${params.DOCKER_IMAGE}:${env.BUILD_NUMBER} ."
                    }
                }
            }
        }
    }

    post {
        success {
            echo "Pipeline terminee avec succes pour ${params.BRANCH_TO_BUILD}"
        }
        failure {
            echo 'Le pipeline a echoue. Verifie Composer, Docker et les plugins Jenkins.'
        }
    }
}

// This file is a generic Scoutnet Jenkins file. The original is found in the dummy extension
// https://github.com/scoutnet/plugins.typo3.scoutnet_dummy/blob/master/Jenkinsfile
// Jenkinsfile Version: 3.0.1
pipeline {
    agent any


    environment {
        TYPO3_PATH_WEB = '/opt/typo3/web'
    }

    stages {
        stage('Test'){
            steps {
                withCredentials([usernamePassword(credentialsId: 'REPO_AUTH', passwordVariable: 'REPO_AUTH_PASSWORD', usernameVariable: 'REPO_AUTH_USER')]) {
                    script {
                        def PHP_VERSIONS = ['8.1', '8.3'] // Only support first and last supported Version to Speed Tests up
                        def tests = [:]

                        sh "echo '{\"http-basic\": {\"repo.scoutnet.de\": {\"username\": \"${REPO_AUTH_USER}\", \"password\": \"${REPO_AUTH_PASSWORD}\"}}}' > auth.json"
                        sh "make init"

                        tests['cgl Test'] = {
                            echo "Testing CGL"
                            sh "make cglTest"
                        }

                        for (x in PHP_VERSIONS) {
                            def PHP_VERSION = x.replace('.','')

                            tests[PHP_VERSION] = {
                                echo "Testing PHP Version ${PHP_VERSION}"
                                sh "make lintTest-php${PHP_VERSION}"
                                sh "make unitTest-php${PHP_VERSION}"
                            }
                        }
                        parallel tests

                        // we only test for php version 8.3, since this should execute the same way
                        sh "make functionalTest-php83"
                        sh "make acceptanceTest-php83"
                        sh 'rm -f auth.json'
                    }
                }
            }
        }
        stage('Deploy'){
            when {
                expression {
                    env.TAG_NAME ==~ /^[0-9]+[.][0-9]+[.][0-9]+$/
                }
            }
            steps {
                withCredentials([string(credentialsId: 'GITHUB_TOKEN', variable: 'GITHUB_TOKEN'), usernamePassword(credentialsId: 'ac854e35-e62e-4aa1-b7ac-2ced736da9e6', passwordVariable: 'TYPO3_TER_PASSWORD', usernameVariable: 'TYPO3_TER_USER')]) {
                    sh 'docker run --rm -e TYPO3_TER_PASSWORD -e TYPO3_TER_USER -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make checkVersion'
                    sh 'docker run --rm -e TYPO3_TER_PASSWORD -e TYPO3_TER_USER -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make release'
                    sh 'docker run --rm -e TYPO3_TER_PASSWORD -e TYPO3_TER_USER -e GITHUB_TOKEN -w /opt/data -v `pwd`:/opt/data -i scoutnet/buildhost:latest make deploy'
                }
            }
        }
        stage('Notify') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'REPO_AUTH', passwordVariable: 'REPO_AUTH_PASSWORD', usernameVariable: 'REPO_AUTH_USER')]) {
                    sh 'curl -s -u ${REPO_AUTH_USER}:${REPO_AUTH_PASSWORD} https://repo.scoutnet.de/trigger.php'
                }
            }
        }

    }
    post {
        always {
            script {
                sh 'rm -f auth.json'
                sh 'make cleanDocker'

                if (currentBuild.currentResult == 'FAILURE') {
                    color = 'danger'
                } else {
                    color = 'good'
                }
                slackSend color: color, message: "<${env.JOB_URL}|${env.JOB_NAME}>: Build ${currentBuild.currentResult}"
            }
        }
    }
}

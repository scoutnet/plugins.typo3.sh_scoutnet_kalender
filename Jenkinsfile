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
                        def PHP_VERSIONS = ['7.2', '7.3']
                        def tests = [:]

                        sh "echo '{\"http-basic\": {\"repo.scoutnet.de\": {\"username\": \"${REPO_AUTH_USER}\", \"password\": \"${REPO_AUTH_PASSWORD}\"}}}' > auth.json"
                        sh "make composerInstall"
                        sh "make composerUpdate"
                        sh "make composerValidate"

                        for (x in PHP_VERSIONS) {
                            def PHP_VERSION = x.replace('.','')

                            tests[PHP_VERSION] = {
                                echo "Testing PHP Version ${PHP_VERSION}"
                                sh "make lintTest-php${PHP_VERSION}"
                                sh "make unitTest-php${PHP_VERSION}"
                            }
                        }
                        parallel tests

// no functional and acceptance tests for now
//                         for (x in PHP_VERSIONS) {
//                             def PHP_VERSION = x.replace('.','')
//
//                                 sh "make functionalTest-php${PHP_VERSION}"
//                                 sh "make acceptanceTest-php${PHP_VERSION}"
//                         }
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
                    sh 'git pull --tags'
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

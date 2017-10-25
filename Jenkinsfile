pipeline {
    agent any

    environment {
        TYPO3_PATH_WEB = '/opt/typo3/web'
    }

    stages {
        stage('Test'){
            steps {
                # Check for php errors
                sh 'docker run --rm -e SHELL=/bin/sh -e TYPO3_PATH_WEB -w /opt/typo3/web/typo3conf/ext/sh_scoutnet_kalender -v `pwd`:/opt/typo3/web/typo3conf/ext/sh_scoutnet_kalender -i scoutnet/cihost:latest sh -c "find . -name \\*.php | grep -v "./Build/" | parallel --gnu php -d display_errors=stderr -l {}"'

                # run unit tests
                sh 'docker run --rm -e TYPO3_PATH_WEB -w /opt/typo3 -v `pwd`:/opt/typo3/web/typo3conf/ext/sh_scoutnet_kalender -i scoutnet/cihost:latest sh -c "composer require scoutnet/sh-scoutnet-webservice; vendor/bin/phpunit --color -c web/typo3conf/ext/sh_scoutnet_kalender/Tests/Builds/UnitTests.xml"'
            }
        }
        stage('Build'){
            steps {
                sh "echo test"
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
                slackSend color: 'good', message: 'Building sh_scoutnet_kalender: Done'
            }
        }

    }
}

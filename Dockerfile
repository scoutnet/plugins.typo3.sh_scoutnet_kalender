FROM scoutnet/typo3.test_environment

WORKDIR /opt/typo3
RUN composer require scoutnet/sh-scoutnet-webservice
ADD . /opt/typo3/web/typo3conf/ext/sh_scoutnet_kalender

<?php

include '../public/env.php';
include APP_PATH . '/Bootstrap.php';

Logger::info('Show interest job started');

Service_ShowInterest::run();

Logger::info('Show interest job terminated');


<?php

require __DIR__.'/../../vendor/autoload.php';

ini_set('assert.exception', '1');
ini_set('assert.active', '1');

if ('1' != ini_get('zend.assertions')) {
    trigger_error('Set zend.assertions=1 in your php.ini');
}

<?php
$CONFIG = array (
  'instanceid' => 'ockei4ju0tf3',
  'passwordsalt' => 'KJWKxuNc87m3+KjvhfECuXkNUpsT8b',
  'secret' => 'AsiScgrEIBYLPjRhnCeWeqAM6uWvsyKz+PJTjCVDYmDBAzxb',
  'trusted_domains' => 
  array (
    0 => 'pnchangewr.groupbwt.com',
    1 => 'admin.cwr.care',
  ),
  'force_language' => 'en',
  'default_language' => 'en',
  'datadirectory' => '/var/www/nextcloud/data',
  'overwrite.cli.url' => 'https://admin.cwr.care/nextcloud',
  'dbtype' => 'mysql',
  'version' => '12.0.2.0',
  'dbname' => 'nextcloud',
  'dbhost' => 'cwr.cezlspovihcx.us-west-1.rds.amazonaws.com',
  'dbport' => '3306',
  'dbtableprefix' => 'oc_',
  'mysql.utf8mb4' => true,
  'dbuser' => 'root',
  'dbpassword' => 's4tCFrDJrglw',
  'installed' => true,
  'debug' => true,
  'upgrade.disable-web' => false,
  'updatechecker' => true,
  'logfile' => '/var/log/nextcloud.log',
  'objectstore' => 
  array (
    'class' => 'OC\\Files\\ObjectStore\\S3',
    'arguments' => 
    array (
      'bucket' => 'cwr-nextcloud',
      'autocreate' => true,
      'key' => 'AKIAJIUY44SV66B2DMEQ',
      'secret' => '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',
      'use_ssl' => false,
      'region' => 'us-west-1',
    ),
  ),
  'maintenance' => false,
  'theme' => '',
  'loglevel' => 2,
);

<?php
// $r = exec("node --version");
// echo $r;
$password = ''; // office ally account password
$r2 = exec("node /var/www/html/pnchangewr/src/puppeteer-scripts/playwright-login.js ericgrigs {$password} 2>&1", $output);
var_dump($output);
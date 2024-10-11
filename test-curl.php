<?php

$url = 'https://ifconfig.me';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
curl_setopt($ch, CURLOPT_PROXY, 'http://cwr_5.04.2023:WqOcqcstfWCI1@108.62.246.74:48549');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'GET');
curl_setopt ($ch, CURLOPT_HEADER, 1);
curl_exec($ch); 
$curl_info = curl_getinfo($ch);
curl_close($ch);
echo '<br />';
print_r($curl_info);

?>
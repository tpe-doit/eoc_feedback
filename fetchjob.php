<?php

$api_endpoint = "http://eoc-feedback.azurewebsites.net/fetchEOC";
$result = file_get_contents($api_endpoint);
echo $result;

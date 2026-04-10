<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$to = "eloiv@lliures.cat";
$subject = "Prova simple";
$body = "Hola món";
$headers = "From: tu@domini.com\r\n";

if(mail($to, $subject, $body, $headers)){
    echo "Correu simple enviat!";
} else {
    echo "Error enviant el correu simple";
}
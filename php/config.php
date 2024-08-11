<?php

// инфо для подключения к базе данных
$DB_HOST = '';
$DB_LOGIN = '';
$DB_PASSWORD = '';
$DB_NAME = '';

// сайтовое
$SITE_SALT = "";
$DEBUG_KEY = "";

// ограничения
$LIMIT_REPORT = 300;
$LIMIT_STUDENTS = 50;
$LIMIT_GROUPS = 3;
$LIMIT_AUTH = 1000;

// журналирование происходит только когда есть сбои
$SKIP_ID = ["get_current_duty_students"]; // список id, который игнорирует условие сбоя и не пишется никогда
// "whose_token_is_this"

$WARNING_ID = ["registration"]; // список id, которые пишутся всегда, вне зависимости от наличия сбоев
// "authorization"

// массив месяцев
$RUSSIAN_MONTHS = [
    "Январь",
    "Февраль",
    "Март",
    "Апрель",
    "Май",
    "Июнь",
    "Июль",
    "Август",
    "Сентябрь",
    "Октябрь",
    "Ноябрь",
    "Декабрь"
];

?>
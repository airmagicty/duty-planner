<?php

include "config.php";

// ==================== Отладка ====================

// глобальные переменные отладки
$debug = false; 
$error = false;
$error_status = false;

// отладочный вывод
function print_pre($data) {
  print_r('<pre><p style="background-color: beige; color: maroon; padding: 10px; margin: 5px; border: 1px maroon solid;">');
  print_r($data);
  print_r('</p></pre>');
}

// внутренний журнал лога
function debug($status="debug", $file="debug", $module="debug", $message="debug") {
  global $debug, $error, $error_status;

  // status: 
  // i - info
  // e - error
  // w - warning
  // m - message

  $buffer = [
    "status" => $status,
    "file" => basename($file),
    "module" => $module,
    "message" => $message
  ];

  if (!is_array($debug)) {
    $debug = array();
  }

  if ($status == "e") {
    $error_status = true;
  }

  if ($status == "m") {
    $error = $message;
  }

  if ($status != "m") {
    array_push($debug, $buffer);
  }
}

// ==================== БД ====================

// глобальные переменные базы данных
$connection;
$connection_status = false;

// запрос в бд
function db_query($query) {
  global $DB_HOST, $DB_LOGIN, $DB_PASSWORD, $DB_NAME;
  global $connection, $connection_status;

  if (!$DB_HOST || !$DB_LOGIN || !$DB_PASSWORD || !$DB_NAME) {
    debug("e", __FILE__, __FUNCTION__,"`DB_HOST` or `DB_LOGIN` or `DB_PASSWORD` or `DB_NAME` from config is empty");
    return false;
  }

  if (!$query) {
    debug("e", __FILE__, __FUNCTION__,"`query` is empty");
    return false;
  }

  if (!$connection_status) {

    if (!$connection = mysqli_connect($DB_HOST, $DB_LOGIN, $DB_PASSWORD, $DB_NAME)) {
      debug("e", __FILE__, __FUNCTION__, "No mysqli_connect"); 
      return false;
    }

    debug("i", __FILE__, __FUNCTION__, "Database connection");
    $connection_status = true;
  }

  $query = trim($query);

  try {
    $result = mysqli_query($connection, $query);
    debug("i", __FILE__, __FUNCTION__, "Request [{$query}] sent successfully");
    return $result;

  } catch (mysqli_sql_exception $e) {
    debug("e", __FILE__, __FUNCTION__, "Request [{$query}] is invalid");
    // debug("e", __FILE__, __FUNCTION__, "The request failed [".mysqli_errno($connection)."]: ".mysqli_error($connection));
    debug("e", __FILE__, __FUNCTION__, $e->getMessage());
    return false;
  }
}

// указатель в массив
function db_assoc_to_array($query_result) {
  
  if (!$query_result) {
    debug("e", __FILE__, __FUNCTION__, "`query_result` is empty");
    return false;
  }

  if (!$num_rows = mysqli_num_rows($query_result)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is not have `rows`");
    return false;
  }

  if ($num_rows < 1) {
    debug("e", __FILE__, __FUNCTION__, "`mysqli_num_rows` is 0");
    return false;
  }

  $result = array();
  while ($row = mysqli_fetch_assoc($query_result)) {
    array_push($result, $row);
  }

  debug("i", __FILE__, __FUNCTION__, "`query_result` to [array({$num_rows})]");
  return $result;
}

// ==================== Безопасность ====================

// глобальные переменные данных
$user_data_by_token = false;

// проверка на varchar($size)
function is_varchar($str, $size) {

  if (!isset($size) || $size <= 0 || $size > 512) {
    debug("e", __FILE__, __FUNCTION__, "Invalid size");
    return false;
  }

  $pattern = '/^.{3,' . $size . '}$/u';
  return preg_match($pattern, $str);
}

function is_dbint($num) {

  if (!is_numeric($num)) {
    return false;
  }

  $intValue = filter_var($num, FILTER_VALIDATE_INT);

  if ($intValue !== false && $intValue >= 0 && $intValue <= PHP_INT_MAX) {
    return true;
  }

  return false;
}

function is_dbdate($date_string) {
  $date_time = DateTime::createFromFormat('Y-m-d', $date_string);
  return $date_time && $date_time->format('Y-m-d') === $date_string;
}

function is_start_date_before_end_date($start_date, $end_date) {
  return $start_date >= $end_date;
}

function is_valid_login($login) {
  $pattern = '/^[a-zA-Z0-9_@\.]{4,20}$/';
  return preg_match($pattern, $login); 
}

// очистка от инъекций
function sanitize_string($str) {

  if (!$str) {
    return $str;
  }

  $str = trim($str);

  $special_chars = array("'", '"', "\\", ";", "=", "<", ">", "(", ")", "'", "\"", "--", "*", "||");
  $sanitized_str = str_replace($special_chars, "", $str);

  if ($str == $sanitized_str) {
    return $str;

  } else {
    debug("w", __FILE__, __FUNCTION__,"String `{$str}` sanitized in `{$sanitized_str}`");
    debug("m", __FILE__, __FUNCTION__, "Строка '{$str}' была очищена от инъекций в '{$sanitized_str}'");
    return $sanitized_str;
  }
}

// генерация токена
function token_gen($login, $password_hash) {
  global $SITE_SALT;

  if (!$SITE_SALT) {
    debug("e", __FILE__, __FUNCTION__,"`SITE_SALT` from config is empty");
    return false;
  }

  if (!$login || !$password_hash) {
    debug("e", __FILE__, __FUNCTION__, "`login` or `password_hash` is empty");
  }

  debug("i", __FILE__, __FUNCTION__, "Token generated");
  return md5($SITE_SALT.$login.$password_hash);
}

// обратное декодирование токена
function token_decoding($token, $login) {

  if (!$token || !$login) {
    debug("e", __FILE__, __FUNCTION__, "`token` or `login` is empty");
    return false;
  }

  if (!is_varchar($login = sanitize_string($login), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `login` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_user` WHERE `login` LIKE '{$login}'";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Request failed");
    return false;
  } 

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "login `{$login}` not received in `DUTY_user`");
    return false;
  } 

  if (!isset($query_result[0]['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result(0)` missing column `password`");
    return false;
  }

  if ($token != token_gen($login, $query_result[0]['password'])) {
    debug("e", __FILE__, __FUNCTION__, "Invalid token");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return userData by token");
  return $query_result[0];
}

// генерация случайной строки
function generateUniqueString() {
  $prefix = uniqid();
  $randomBytes = random_bytes(5);
  $uniqueString = $prefix . bin2hex($randomBytes);
  return $uniqueString;
}

?>
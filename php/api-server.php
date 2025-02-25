<!-- Этот файл является частью проекта "Электронный дежурный журнал". -->
<!-- Проект распространяется под лицензией GNU GPL v3.0. -->
<!-- Полный текст лицензии см. в файле LICENSE. -->

<?php 

include_once "config.php";
include_once "core.php"; 
include_once "function.php";
include_once "api-function.php";

// ============================== Вызов функций API по id ==============================
$event_key = false;
$query_id = false;

function api_function_call_by_id() {
  global $_COOKIE, $_POST, $event_key, $query_id, $user_data_by_token;
  planning_trigger();

  if (!isset($_POST['id'])) {
    debug("e", __FILE__, __FUNCTION__, "Missing ID in api-request from client");
    debug("m", __FILE__, __FUNCTION__, "Отсутствует идентификатор запроса");
    return false;
  }
  
  $query_id = $_POST['id'];

  if (!$query_id) {
    debug("e", __FILE__, __FUNCTION__, "ID in api-request is empty");
    debug("m", __FILE__, __FUNCTION__, "Отсутствует идентификатор запроса");
    return false;
  }

  $event_key = $query_id;
  debug("i", __FILE__, __FUNCTION__, "QueryID is `{$query_id}`");

  if ($_POST['id'] == "authorization") {
    debug("i", __FILE__, __FUNCTION__, "ID `authorization` was detected and a request was made to the database");
    
    if (!isset($_POST['auth_login']) || !isset($_POST['auth_password'])) {
      debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `auth_login` or `auth_password`");
      debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
      return false;
    }

    return return_api_authorization($_POST['auth_login'], $_POST['auth_password']); 
  }

  if ($_POST['id'] == "registration") {
    debug("i", __FILE__, __FUNCTION__, "ID `registration` was detected and a request was made to the database");
    
    if (!isset($_POST['reg_login']) || !isset($_POST['reg_password']) || !isset($_POST['reg_name'])) {
      debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `duty_status` or `reg_password` or `reg_name`");
      debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
      return false;
    }

    return return_api_registration($_POST['reg_login'], $_POST['reg_password'], $_POST['reg_name']); 
  }

  if ($_POST['id'] == "get_current_duty_students") {
    debug("i", __FILE__, __FUNCTION__, "ID `get_current_duty_students` was detected and a request was made to the database");
    
    if (!isset($_POST['group_id'])) {
      debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id`");
      debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
      return false;
    }

    return return_api_get_current_duty_students($_POST['group_id']); 
  }
  
  if (!isset($_COOKIE['DUTY']['login']) || !isset($_COOKIE['DUTY']['token'])) {
    debug("e", __FILE__, __FUNCTION__, "`cookie-token` & `cookie-login` does not exist");
    debug("m", __FILE__, __FUNCTION__, "Вы не авторизованы");
    return false;
  }

  $login = $_COOKIE['DUTY']['login'];
  $token = $_COOKIE['DUTY']['token'];

  if (!$login || !$token) {
    debug("e", __FILE__, __FUNCTION__, "`login` or `token` is empty");
    debug("m", __FILE__, __FUNCTION__, "Вы не авторизованы");
    return false;
  }

  if (!$user_data_by_token = token_decoding($token, $login)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid token `{$token}`");
    debug("m", __FILE__, __FUNCTION__, "Вы не авторизованы");
    return false;
  }

  switch($query_id) {

    case "logout":
      debug("i", __FILE__, __FUNCTION__, "ID `logout` was detected and a request was made to the database");
      return return_api_logout($user_data_by_token);
      break;

    case "whose_token_is_this":
      debug("i", __FILE__, __FUNCTION__, "ID `whose_token_is_this` was detected and a request was made to the database");
      return return_api_whose_token_is_this($user_data_by_token); 
      break;

    case "get_list_students_of_this_group":
      debug("i", __FILE__, __FUNCTION__, "ID `get_list_students_of_this_group` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }
      
      return return_api_get_list_students_of_this_group($user_data_by_token, $_POST['group_id']);
      break;

    case "change_student_status":
      debug("i", __FILE__, __FUNCTION__, "ID `change_student_status` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id']) || !isset($_POST['student_id']) || !isset($_POST['duty_status'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id` or `duty_status` or `student_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_change_student_status($user_data_by_token, $_POST['group_id'], $_POST['student_id'], $_POST['duty_status']);
      break;

    case "add_planning_student_duty":
      debug("i", __FILE__, __FUNCTION__, "ID `add_planning_student_duty` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id']) || !isset($_POST['student_id']) || !isset($_POST['duty_date'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id` or `duty_status` or `student_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_add_planning_student_duty($user_data_by_token, $_POST['group_id'], $_POST['student_id'], $_POST['duty_date']);
      break;

    case "delete_planning_student_duty":
      debug("i", __FILE__, __FUNCTION__, "ID `delete_planning_student_duty` was detected and a request was made to the database");
      
      if (!isset($_POST['planning_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `planning_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_delete_planning_student_duty($user_data_by_token, $_POST['planning_id']);
      break;

    case "add_new_student":
      debug("i", __FILE__, __FUNCTION__, "ID `add_new_student` was detected and a request was made to the database");
      
      if (!isset($_POST['name']) || !isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `name` or `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_add_new_student($user_data_by_token, $_POST['group_id'], $_POST['name']);
      break;

    case "delete_student":
      debug("i", __FILE__, __FUNCTION__, "ID `delete_student` was detected and a request was made to the database");
      
      if (!isset($_POST['student_id']) || !isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `student_id` or `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_delete_student($user_data_by_token, $_POST['group_id'], $_POST['student_id']);
      break;

    case "edit_student_data":
      debug("i", __FILE__, __FUNCTION__, "ID `edit_student` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id']) || !isset($_POST['student_id']) || !isset($_POST['student_new_name']) || !isset($_POST['student_new_duty_count'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `student_id` or `student_new_name`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_edit_student_data($user_data_by_token, $_POST['group_id'], $_POST['student_id'], $_POST['student_new_name'], $_POST['student_new_duty_count']);
      break;

    case "reload_student_statuses":
      debug("i", __FILE__, __FUNCTION__, "ID `reload_student_statuses` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }
      
      return return_api_reload_student_statuses($user_data_by_token, $_POST['group_id']);
      break;

    case "get_list_your_groups":
      debug("i", __FILE__, __FUNCTION__, "ID `get_list_your_groups` was detected and a request was made to the database");
      return return_api_get_list_your_groups($user_data_by_token);
      break;

    case "add_new_group": 
      debug("i", __FILE__, __FUNCTION__, "ID `add_new_group` was detected and a request was made to the database");
      
      if (!isset($_POST['group_new_name']) || !isset($_POST['group_new_about'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_new_name` or  `group_new_about`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_add_new_group($user_data_by_token, $_POST['group_new_name'], $_POST['group_new_about']);
      break;

    case "edit_group_data":
      debug("i", __FILE__, __FUNCTION__, "ID `edit_group_data` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id']) || !isset($_POST['group_new_name']) || !isset($_POST['group_new_about'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id` or  `group_new_name` or `group_new_about`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_edit_group_data($user_data_by_token, $_POST['group_id'], $_POST['group_new_name'], $_POST['group_new_about']);
      break;

    case "delete_group":
      debug("i", __FILE__, __FUNCTION__, "ID `delete_group` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_delete_group($user_data_by_token, $_POST['group_id']);
      break;

    case "edit_my_data": 
      debug("i", __FILE__, __FUNCTION__, "ID `edit_my_data` was detected and a request was made to the database");
      
      if (!isset($_POST['my_id']) || !isset($_POST['my_new_name']) || !isset($_POST['my_new_about'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id` or  `group_new_name` or `group_new_about`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_edit_my_data($user_data_by_token, $_POST['my_id'], $_POST['my_new_name'], $_POST['my_new_about']);
      break;

    case "edit_my_login":
      debug("i", __FILE__, __FUNCTION__, "ID `edit_my_login` was detected and a request was made to the database");
      
      if (!isset($_POST['my_id']) || !isset($_POST['my_new_login'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `my_id` or `my_new_login`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_edit_my_login($user_data_by_token, $_POST['my_id'], $_POST['my_new_login']);
      break;

    case "edit_my_password":
      debug("i", __FILE__, __FUNCTION__, "ID `edit_my_password` was detected and a request was made to the database");
      
      if (!isset($_POST['my_id']) || !isset($_POST['my_ative_password']) || !isset($_POST['my_new_password'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `my_id` or `my_ative_password` or `my_new_password`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_edit_my_password($user_data_by_token, $_POST['my_id'], $_POST['my_ative_password'], $_POST['my_new_password']);
      break;

    case "add_an_existing_group":
      debug("i", __FILE__, __FUNCTION__, "ID `add_an_existing_group` was detected and a request was made to the database");
      
      if (!isset($_POST['add_key'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `add_key`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }
      
      return return_api_add_an_existing_group($user_data_by_token, $_POST['add_key']);
      break;

    case "reset_group_key":
      debug("i", __FILE__, __FUNCTION__, "ID `reset_group_key` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }
      
      return return_api_reset_group_key($user_data_by_token, $_POST['group_id']);
      break;

    case "who_manages_this_group":
      debug("i", __FILE__, __FUNCTION__, "ID `who_manages_this_group` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }
      
      return return_api_who_manages_this_group($user_data_by_token, $_POST['group_id']);
      break;

    case "get_planning_list_this_group":
      debug("i", __FILE__, __FUNCTION__, "ID `get_planning_list_this_group` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id']) || !isset($_POST['month'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id` or `month`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }
      
      return return_api_get_planning_list_this_group($user_data_by_token, $_POST['group_id'], $_POST['month']);
      break;

    case "delete_my_account":
      debug("i", __FILE__, __FUNCTION__, "ID `delete_my_account` was detected and a request was made to the database");
      
      if (!isset($_POST['my_id']) || !isset($_POST['my_password'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `my_id` or `my_password`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_delete_my_account($user_data_by_token, $_POST['my_id'], $_POST['my_password']);
      break;

    case "create_group_report":
      debug("i", __FILE__, __FUNCTION__, "ID `create_group_report` was detected and a request was made to the database");
      
      if (!isset($_POST['group_id']) || !isset($_POST['start_date']) || !isset($_POST['end_date'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `group_id` or `start_date` or `end_date`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_create_group_report($user_data_by_token, $_POST['group_id'], $_POST['start_date'], $_POST['end_date']);
      break;

    case "get_list_log":
      debug("i", __FILE__, __FUNCTION__, "ID `get_list_log` was detected and a request was made to the database");
      
      if (!isset($_POST['count_pages']) || !isset($_POST['page'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `count_pages` or `page`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_get_list_log($user_data_by_token, $_POST['count_pages'], $_POST['page']);
      break;

    case "reset_user_password":
      debug("i", __FILE__, __FUNCTION__, "ID `reset_user_password` was detected and a request was made to the database");
      
      if (!isset($_POST['user_login']) || !isset($_POST['user_new_password'])) {
        debug("e", __FILE__, __FUNCTION__, "In `POST` missing column `user_login` or `user_new_password`");
        debug("m", __FILE__, __FUNCTION__, "Отсутствуют данные для запроса");
        return false;
      }

      return return_api_reset_user_password($user_data_by_token, $_POST['user_login'], $_POST['user_new_password']);
      break;

    default:
      debug("e", __FILE__, __FUNCTION__, "Incorrect `id` in the api-request from the client");
      debug("m", __FILE__, __FUNCTION__, "Такого идентификатора запроса не существует");
      return false;
  }
}

function return_data_from_query() {
  $result_api = array();

  // в api добавить форматированный результат
  if (!$query_result = api_function_call_by_id()) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is missing");
    return false;
  }

  if (!is_array($query_result) || empty($query_result)) {
    debug("e", __FILE__, __FUNCTION__, "`query_result` is incorrect or empty");
    return false;
  }

  if (!array_push($result_api, $query_result)) {
    debug("e", __FILE__, __FUNCTION__, "No push `query_result` in `result_api`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "`query_result` has been added to `result_api`");
  return $result_api;
}

function array_to_json_str($result_api) {

  if (!is_array($result_api) || empty($result_api)) {
    debug("e", __FILE__, __FUNCTION__, "`result_api` is incorrect or empty");
    return false;
  }

  // вернуть результат {json}
  $json_string_return = "";
  foreach($result_api as $value) {
    $json_string_return .= substr(json_encode($value), 1, -1).",";
  }

  return "{".rtrim($json_string_return, ',')."}";
}

function return_server_response() {
  global $error, $debug, $DEBUG_KEY;

  // получить результат для ответа
  if (!$result_api = return_data_from_query()) {
    $result_api = array();
  }

  $result_status_api = [
    "status" => [
      "error" => $error
    ]
  ];

  // заполнить массив статуса и отладки
  if (isset($_COOKIE['DUTY']['debug']))
    if ($_COOKIE['DUTY']['debug'] == $DEBUG_KEY) {
      $result_status_api = [
        "status" => [
          "debug" => $debug,
          "error" => $error
        ]
      ];
  }

  // вставить массив отладки в массив api
  if (array_unshift($result_api, $result_status_api)) {
    debug("i", __FILE__, __FUNCTION__, "Successfully added `status` item to api");

  } else {
    $result_status_api = [
      "status" => [
        "debug" => "[Error] or [Debug] is incorrect; ",
        "error" => "Технические неполадки; "
      ]
    ];

    array_unshift($result_api, $result_status_api);
    debug("e", __FILE__, __FUNCTION__, "Adding the `status` item to the api was not done");
  }

  if (!$result = array_to_json_str($result_api)) {
    return '{"status":{"debug":"ReturnData is incorrect; ","error":"Технические неполадки; "}}';
  }

  return $result;
}

// возвращение результата клиенту
print(return_server_response());

// отправка информации о запросе в журнал
if ($event_key && !in_array($event_key, $SKIP_ID) && ($error_status || in_array($event_key, $WARNING_ID))) {
  logging_event($user_data_by_token, $event_key);
}

?>
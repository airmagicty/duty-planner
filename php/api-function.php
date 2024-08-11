<?php

include_once "core.php";
include_once "function.php";

// ====================== Функции API ======================

// авторизация
function return_api_authorization($login, $password) {
  global $user_data_by_token, $LIMIT_AUTH;

  if (!is_dbint($count_auth = return_count_auth())) {
    debug("e", __FILE__, __FUNCTION__, "Return not `count_auth`");
    return false;
  }

  if ($count_auth >= $LIMIT_AUTH) {
    debug("w", __FILE__, __FUNCTION__, "count_auth > LIMIT_AUTH");
    debug("m", __FILE__, __FUNCTION__, "Вы превысили количество попыток авторизации, повторите через 30 минут");
    return false;
  }

  if (!$login || !$password) {
    debug("e", __FILE__, __FUNCTION__, "`login` or `password` is empty");
    debug("m", __FILE__, __FUNCTION__, "Логин или пароль отсутствуют");
    return false;
  }

  if (!is_valid_login($login = sanitize_string($login))) {
    debug("e", __FILE__, __FUNCTION__, "Data `login` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Логин некорректен");
    return false;
  }

  if (!is_varchar($password, 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `login` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Пароль некорректен");
    return false;
  }

  $password_hash = md5($password);
  $token = token_gen($login, $password_hash);
  $query_text = "SELECT * FROM `DUTY_user` WHERE login='$login' AND password='$password_hash';"; 

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (mysqli_num_rows($query_call) < 1) {
    debug("e", __FILE__, __FUNCTION__, "There is no user with this `login` & `password`");
    debug("m", __FILE__, __FUNCTION__, "Пользователя с таким логином или паролем не существует");
    return false;
  }

  if (!$user_data_by_token = get_user_info_by_login($login)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `user_info_by_login{$login}`");
    return false;
  }
  
  debug("i", __FILE__, __FUNCTION__, "User `{$login}` authed");
  return [
    "data" => [
      "login" => $login,
      "token" => $token,
      "message" => "Авторизация успешна"
    ]
  ];
}

// регистрация
function return_api_registration($reg_login, $reg_password, $reg_name) {

  if (!$reg_login || !$reg_name || !$reg_password) {
    debug("e", __FILE__, __FUNCTION__, "`reg_login` or `reg_name` or `reg_password` is empty");
    debug("m", __FILE__, __FUNCTION__, "Логин, пароль или имя отсутствуют");
    return false;
  }

  if (!is_valid_login($reg_login)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid login");
    debug("m", __FILE__, __FUNCTION__, "Некорректный логин");
    return false;
  }

  if (!check_login_uniqueness($reg_login)) {
    debug("e", __FILE__, __FUNCTION__, "Login `{$reg_login}` is not unique");
    debug("m", __FILE__, __FUNCTION__, "Логин занят");
    return false;
  }

  if (!is_varchar($reg_password, 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `reg_name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Пароль некорректен");
    return false;
  }

  if (!is_varchar($reg_name = sanitize_string($reg_name), 128)) {
    debug("e", __FILE__, __FUNCTION__, "Data `reg_name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Имя некорректно");
    return false;
  }
  
  $password_hash = md5($reg_password);
  $query_text = "INSERT INTO `DUTY_user` (`id`, `login`, `password`, `name`, `role`, `about`) VALUES (NULL, '{$reg_login}', '{$password_hash}', '{$reg_name}', 'group_manager', '');";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Not reqister user `{$reg_login}`");
    return false;
  }

  $token = token_gen($reg_login, $password_hash);

  debug("i", __FILE__, __FUNCTION__, "User {$reg_name} ({$reg_login}) registred");
  return [
    "data" => [
      "message" => "Пользователь {$reg_name} ({$reg_login}) успешно зарегистрирован",
      "login" => $reg_login,
      "token" => $token
    ]
  ];
}

// выход из аккаунта
function return_api_logout($user_data_by_token) {

  if(!isset($user_data_by_token['login']) || !isset($user_data_by_token['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `login` or `id`");
    return false;
  }

  $login = $user_data_by_token['login'];
  $id = $user_data_by_token['id'];

  debug("i", __FILE__, __FUNCTION__, "User {$id} logout");
  return [
    "data" => [
      "message" => "Пользователь {$login} вышел из аккаунта"
    ]
  ];
}

// по токену определить кто это
function return_api_whose_token_is_this($user_data_by_token) {

  if (isset($user_data_by_token['password'])) {
    $user_data_by_token['password'] = "securited";
  }

  debug("i", __FILE__, __FUNCTION__, "Return `user_data_by_token`");
  return [
    "data" => [
      "user_data" => $user_data_by_token
    ]
  ];
}

// получить студентов группы по её id
function return_api_get_list_students_of_this_group($user_data_by_token, $group_id) {
  global $LIMIT_STUDENTS;

  if (!isset($user_data_by_token['id']) || !isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `id` or `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "В запросе отсутствует идентификатор группы");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  // $query_text = "SELECT * FROM `DUTY_student` WHERE `group_id` = {$group_id};"; 
  $query_text = "
    SELECT 
      `s`.*, 
      CASE
          WHEN `p`.`duty_date` IS NOT NULL THEN `p`.`duty_date`
          ELSE 'EMPTY'
      END AS `planning`
    FROM 
      `DUTY_student` AS `s`
    LEFT JOIN (
      SELECT `student_id`, MIN(`duty_date`) AS `min_duty_date`
      FROM `DUTY_planning`
      GROUP BY `student_id`
    ) AS `subquery` ON `s`.`id` = `subquery`.`student_id`
    LEFT JOIN `DUTY_planning` AS `p` ON `subquery`.`student_id` = `p`.`student_id` AND `subquery`.`min_duty_date` = `p`.`duty_date`
    WHERE `s`.`group_id` = {$group_id}
    ORDER BY `s`.`duty_count` DESC, `s`.`name`;
  ";
  
  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $query_result = array();
  }

  debug("i", __FILE__, __FUNCTION__, "Return `list_users_of_your_group`");
  return [
    "data" => [
      "students" => $query_result,
      "limit_students" => $LIMIT_STUDENTS
    ]
  ];
  
}

// изменить статус студента
function return_api_change_student_status($user_data_by_token, $group_id, $student_id, $duty_status) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($student_id) || !isset($duty_status) || !isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`student_id` or `duty_status` or `group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Айди группы, студента или его статус отсутствуют");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$list_students_of_this_group = return_api_get_list_students_of_this_group($user_data_by_token, $group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_users_of_your_group`");
    return false;
  }

  if (!is_id_in_list($student_id, $list_students_of_this_group['data']['students'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_dbint($duty_status = sanitize_string($duty_status))) {
    debug("e", __FILE__, __FUNCTION__, "Data `duty_status` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Статус студента некорректен");
    return false;
  }

  if (!in_array($duty_status, [0, 1, 2, 3])) {
    debug("e", __FILE__, __FUNCTION__, "`duty_status` is incorrect");
    debug("m", __FILE__, __FUNCTION__, "Статус студента некорректен");
    return false;
  }

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    return false;
  }

  if (!$student_data = get_student_info($student_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `student_info`");
    return false;
  }

  if (!isset($student_data['duty_count'])) {
    debug("e", "file", __FUNCTION__, "In `student_data` missing column `duty_count`");
    return false;
  }

  if (!isset($student_data['duty_status'])) {
    debug("e", "file", __FUNCTION__, "In `student_data` missing column `duty_count`");
    return false;
  }

  $duty_count = $student_data['duty_count'];

  if ($duty_status == 2 && $student_data['duty_status'] != $duty_status) {

    if ($duty_count > 0) {
      $duty_count = $duty_count - 1;
      debug("i", __FILE__, __FUNCTION__, "Duty count for `student_id{$student_id}` reduced by -1");
    }
    
    log_for_report($group_id, $student_id);
  }

  $query_text = "
    DELETE FROM `DUTY_planning`
    WHERE DATE(`duty_date`) = CURDATE() AND `student_id` = {$student_id};
  ";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Scheduler entries cleared NOT");
    return false;
  }

  // debug("i", __FILE__, __FUNCTION__, "Scheduler entries cleared");
  $query_text = "UPDATE `DUTY_student` SET `duty_status` = '{$duty_status}', `duty_count` = '{$duty_count}' WHERE `DUTY_student`.`id` = {$student_id};";
  
  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Status for student `id{$student_id}` NOT changed to {$duty_status}");
    return false;
  }

  if (!$student_name = return_student_name($student_id)) {
    debug("i", __FILE__, __FUNCTION__, "Not return `student_name`");
    return false;
  }

  function num_to_text($num) {
    switch ($num) {
      case 0: 
        return "снова готов к дежурствам"; 
        break;

      case 1: 
        return "поставлен на дежурство"; 
        break;

      case 2: 
        return "отдежурил"; 
        break;

      case 3: 
        return "временно снят с дежурства"; 
        break;
        
      default: 
        return false;
    }
  }

  $text_duty_status = num_to_text($duty_status);
  debug("i", __FILE__, __FUNCTION__, "Status for student `id{$student_id}` change to {$duty_status}");
  return [
    "data" => [
      "message" => "Студент {$student_name} {$text_duty_status}"
    ]
  ];
}

// запланировать дежурство
function return_api_add_planning_student_duty($user_data_by_token, $group_id, $student_id, $duty_date) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($student_id) || !isset($group_id) || !isset($duty_date)) {
    debug("e", __FILE__, __FUNCTION__, "`student_id` or `duty_date` or `group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Айди группы, студента или дата назначения отсутствуют");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$list_students_of_this_group = return_api_get_list_students_of_this_group($user_data_by_token, $group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_users_of_your_group`");
    return false;
  }

  if (!is_id_in_list($student_id, $list_students_of_this_group['data']['students'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_dbdate($duty_date)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid `duty_date` format"); 
    debug("m", __FILE__, __FUNCTION__, "Дата некорректна");
    return false;
  }

  $current_date = date("Y-m-d");

  if ($duty_date < $current_date) {
    debug("e", __FILE__, __FUNCTION__, "`duty_date` is too old"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не можете назначать на прошедшую дату");
    return false;
  } 

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    return false;
  }

  $query_text = "INSERT IGNORE INTO `DUTY_planning` (`id`, `student_id`, `duty_date`) VALUES (NULL, '{$student_id}', '{$duty_date}');";
  
  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Status for student `id{$student_id}` NOT planning to {$duty_date}");
    return false;
  }

  if (!$student_name = return_student_name($student_id)) {
    debug("i", __FILE__, __FUNCTION__, "Not return `student_name`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Status for student `id{$student_id}` planning to {$duty_date}");
  return [
    "data" => [
      "message" => "Студент {$student_name} дежурит {$duty_date}"
    ]
  ];
}

// удалить запланированное дежурство
function return_api_delete_planning_student_duty($user_data_by_token, $planning_id) {

  if (!isset($user_data_by_token['role']) || !isset($user_data_by_token['id'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($planning_id)) {
    debug("e", __FILE__, __FUNCTION__, "`planning_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Айди планирования отсутствует");
    return false;
  }

  if (!is_dbint($planning_id = sanitize_string($planning_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `planning_id` from query is invalid");
    return false;
  }

  if (!$planning_info = get_planning_info($planning_id)) {
    debug("i", __FILE__, __FUNCTION__, "Not return `planning_info`");
    return false;
  }

  if (!isset($planning_info['student_name']) || !isset($planning_info['duty_date'])) {
    debug("e", __FILE__, __FUNCTION__, "In `planning_info` missing column `student_name` or `duty_date`");
    return false;
  }

  $my_id = $user_data_by_token['id'];

  if (!is_dbint($my_id = sanitize_string($my_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_id` from query is invalid");
    return false;
  }

  $query_text = "
    SELECT *
    FROM `DUTY_planning`
    INNER JOIN `DUTY_student` ON `DUTY_planning`.`student_id` = `DUTY_student`.`id`
    INNER JOIN `DUTY_control` ON `DUTY_student`.`group_id` = `DUTY_control`.`group_id`
    WHERE `DUTY_planning`.`id` = {$planning_id}
    AND `DUTY_control`.`manager_id` = {$my_id};
  ";

  if (!check_in_db($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "check_in_db is false");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  $query_text = "DELETE FROM `DUTY_planning` WHERE `DUTY_planning`.`id` = {$planning_id}";
  
  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Planning `{$planning_id}` is not deleted");
    return false;
  }

  $student_name = $planning_info['student_name'];
  $duty_date = new DateTime($planning_info['duty_date']);
  $duty_formatted_date = $duty_date->format("d.m.Y");

  debug("i", __FILE__, __FUNCTION__, "Planning `id{$planning_id}` deleted");
  return [
    "data" => [
      "message" => "Вы отменили дежурство студента {$student_name} на {$duty_formatted_date}"
    ]
  ];
}

// кто управляет группой
function return_api_get_planning_list_this_group($user_data_by_token, $group_id, $month) {
  global $RUSSIAN_MONTHS;

  if (!isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$group_list = return_api_get_list_your_groups($user_data_by_token)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `get_manager_list_by_college`");
    return false;
  }
  
  if(!is_id_in_list($group_id, $group_list['data']['groups'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!is_dbdate($month."-01")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid `month` format"); 
    return false;
  }

  $query_text = "
    SELECT `p`.*, `s`.`name`, `s`.`duty_count`
    FROM `DUTY_planning` AS `p`
    INNER JOIN `DUTY_student` AS `s` ON `p`.`student_id` = `s`.`id`
    WHERE `s`.`group_id` = {$group_id}
      AND DATE_FORMAT(`p`.`duty_date`, '%Y-%m') = '{$month}'
    ORDER BY `p`.`duty_date`;
  ";


  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $query_result = array();
  }

  $timestamp = strtotime($month);
  $russianMonth = $RUSSIAN_MONTHS[date('n', $timestamp) - 1];
  $year = date('Y', $timestamp);
  $datename = "{$russianMonth} {$year}";

  debug("i", __FILE__, __FUNCTION__, "Return `planning_list_this_group`");
  return [
    "data" => [
      "planning" => $query_result,
      "date" => [
        "text" => $datename,
        "number" => $month
      ]
    ]
  ];
}

// добавить нового студента
function return_api_add_new_student($user_data_by_token, $group_id, $name) {
  global $LIMIT_STUDENTS;

  if (!isset($user_data_by_token['id']) || !isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id` or `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$name || !isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`name` or `group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Имя или идентификатор группы отсутствуют");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!is_varchar($name = sanitize_string($name), 128)) {
    debug("e", __FILE__, __FUNCTION__, "Data `name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Имя некорректно");
    return false;
  }

  if (!is_dbint($count_students = return_count_students($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Return not `count_students`");
    return false;
  }

  if ($count_students >= $LIMIT_STUDENTS) {
    debug("e", __FILE__, __FUNCTION__, "count_group > LIMIT_GROUPS");
    debug("m", __FILE__, __FUNCTION__, "Вы достигли максимального количества студентов в группе");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }
     
  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    return false;
  } 

  if (!isset($group_data['id']) || !isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `id` or `name`");
    return false;
  }

  if (!is_unique_name_in_group($group_id, $name)) {
    debug("e", __FILE__, __FUNCTION__, "`is_unique_name_in_group` false");
    debug("m", __FILE__, __FUNCTION__, "Студент {$name} уже есть в этой группе");
    return false;
  }

  $id = $user_data_by_token['id'];
  $group_name = $group_data['name'];
  $query_text = "INSERT INTO `DUTY_student` (`id`, `group_id`, `name`, `duty_status`, `duty_count`) VALUES (NULL, '{$group_id}', '{$name}', '0', '1');";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Student {$name} NOT added in `id[{$id}]` `group_id[{$group_id}]`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Student {$name} added in `id[{$id}]` `group_id[{$group_id}]`");
  return [
    "data" => [
      "message" => "Студент {$name} успешно добавлен в группу {$group_name}",
    ]
  ];
}

// удалить студента
function return_api_delete_student($user_data_by_token, $group_id, $student_id) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "`role` is empty");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($student_id) || !isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`student_id` or `group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы или студента отсутствуют");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор студента некорректен");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$list_students_of_this_group = return_api_get_list_students_of_this_group($user_data_by_token, $group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_users_of_your_group`");
    return false;
  }
  
  if (!is_id_in_list($student_id, $list_students_of_this_group['data']['students'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$student_name = return_student_name($student_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `student_name`");
    return false;
  }

  $query_text = "DELETE FROM `DUTY_student` WHERE `DUTY_student`.`id` = {$student_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Student `id{$student_id}` NOT deleted");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Student `id{$student_id}` deleted");
  return [
    "data" => [
      "message" => "Студент {$student_name} удален"
    ]
  ];
}

// изменение студента
function return_api_edit_student_data($user_data_by_token, $group_id, $student_id, $student_new_name, $student_new_duty_count) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }
  
  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id) || !isset($student_id) || !$student_new_name || !isset($student_new_duty_count)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` or `student_id` or `student_new_name` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы, студента, его имя или счетчик дежурств отсутствуют");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!$list_students_of_this_group = return_api_get_list_students_of_this_group($user_data_by_token, $group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($student_id, $list_students_of_this_group['data']['students'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор студента некорректен");
    return false;
  }

  if (!is_dbint($student_new_duty_count = sanitize_string($student_new_duty_count))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_new_duty_count` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Счетчик деурств некорректен");
    return false;
  }

  if (!$student_new_duty_count < 0) {
    debug("e", __FILE__, __FUNCTION__, "`student_new_duty_count` is incorrect");
    debug("m", __FILE__, __FUNCTION__, "Счетчик деурств некорректен");
    return false;
  }

  if (!is_varchar($student_new_name = sanitize_string($student_new_name), 128)) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_new_name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Имя некорректно");
    return false;
  }

  $query_text = "UPDATE `DUTY_student` SET `name` = '{$student_new_name}', `duty_count` = '{$student_new_duty_count}' WHERE `DUTY_student`.`id` = {$student_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Edit `student{$student_id}` in `DUTY_student`");
  return [
    "data" => [
      "message" => "Студент {$student_new_name} изменен"
    ]
  ];
}

// сбросить статусы в 0
function return_api_reload_student_statuses($user_data_by_token, $group_id) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }
  
  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    return false;
  } 

  if (!isset($group_data['id']) || !isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `id` or `name`");
    return false;
  }

  $group_name = $group_data['name'];
  $query_text = "UPDATE `DUTY_student` SET `duty_status`='0',`duty_count`='1' WHERE `group_id` = {$group_id} AND `duty_status` <> '3';";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Statuses in `group{$group_id}` NOT reloaded");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Statuses in `group{$group_id}` reloaded");
  return [
    "data" => [
      "message" => "Цикл дежурств для группы {$group_name} сброшен"
    ]
  ];
}

// получить список групп 
function return_api_get_list_your_groups($user_data_by_token) {
  global $LIMIT_GROUPS;

  if (!isset($user_data_by_token['id']) || !isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id` or `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  $id = $user_data_by_token['id'];

  if (!is_dbint($id = sanitize_string($id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `id` from query is invalid");
    return false;
  }

  // $query_text = "SELECT `DUTY_group`.*, `DUTY_control`.`role` FROM `DUTY_group` INNER JOIN `DUTY_control` ON `DUTY_group`.`id` = `DUTY_control`.`group_id` WHERE `DUTY_control`.`manager_id` = '{$id}';";
  $query_text = "
    SELECT `DUTY_group`.*, `DUTY_control`.`role` FROM `DUTY_group` 
    INNER JOIN `DUTY_control` ON `DUTY_group`.`id` = `DUTY_control`.`group_id` 
    WHERE `DUTY_control`.`manager_id` = '{$id}'
    ORDER BY `DUTY_control`.`role`, `DUTY_group`.`name`;
  ";
  

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $query_result = array();
  }

  return [
    "data" => [
      "groups" => $query_result,
      "limit_groups" => $LIMIT_GROUPS
    ]
  ];
}

// добавление новой группы
function return_api_add_new_group($user_data_by_token, $group_new_name, $group_new_about) {
  global $connection, $LIMIT_GROUPS;

  if (!isset($user_data_by_token['id']) || !isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id` or `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$group_new_name) {
    debug("e", __FILE__, __FUNCTION__, "`group_new_name` is empty");
    debug("m", __FILE__, __FUNCTION__, "Имя группы отсутствует");
    return false;
  }

  if (!is_varchar($group_new_name = sanitize_string($group_new_name), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_new_name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Имя группы некорректно");
    return false;
  }

  if ($group_new_about) {
    if (!is_varchar($group_new_about = sanitize_string($group_new_about), 256)) {
      debug("e", __FILE__, __FUNCTION__, "Data `group_new_about` from query is invalid");
      debug("m", __FILE__, __FUNCTION__, "Описание группы некорректно");
      return false;
    }
  }

  $id = $user_data_by_token['id'];

  if (!is_dbint($count_group = return_count_groups($id))) {
    debug("e", __FILE__, __FUNCTION__, "Return not `count_groups`");
    return false;
  }

  if ($count_group >= $LIMIT_GROUPS) {
    debug("e", __FILE__, __FUNCTION__, "count_group > LIMIT_GROUPS");
    debug("m", __FILE__, __FUNCTION__, "Вы достигли максимального количества управляемых групп");
    return false;
  }

  $group_key = generateUniqueString();
  $query_text = "INSERT INTO `DUTY_group` (`id`, `link`, `name`, `about`) VALUES (NULL, '{$group_key}', '{$group_new_name}', '{$group_new_about}'); ";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No add group");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Add in `user{$id}` group `{$group_new_name}` in `DUTY_group`");
  $last_group_id = mysqli_insert_id($connection);

  if (!is_dbint($last_group_id = sanitize_string($last_group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `last_group_id` from query is invalid");
    return false;
  }

  $query_text = "INSERT INTO `DUTY_control` (`id`, `manager_id`, `group_id`, `role`) VALUES (NULL, '{$id}', '{$last_group_id}', 'admin');";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No add group roles");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Add group`id{$last_group_id}` for user `id{$id}` in `DUTY_control`");
  
  return [
    "data" => [
      "message" => "Группа {$group_new_name} была добавлена"
    ]
  ];
}

// редактирование группы
function return_api_edit_group_data($user_data_by_token, $group_id, $group_new_name, $group_new_about) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id) || !$group_new_name) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` or `group_new_name` or `group_new_about` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы или ее имя отсутствуют");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!$group_list = return_api_get_list_your_groups($user_data_by_token)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `get_manager_list_by_college`");
    return false;
  }
  
  if(!is_id_in_list($group_id, $group_list['data']['groups'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_varchar($group_new_name = sanitize_string($group_new_name), 128)) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_new_name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Имя группы некорректно");
    return false;
  }

  if ($group_new_about) {
    if (!is_varchar($group_new_about = sanitize_string($group_new_about), 256)) {
      debug("e", __FILE__, __FUNCTION__, "Data `group_new_about` from query is invalid");
      debug("m", __FILE__, __FUNCTION__, "Описание группы некорректно");
      return false;
    }
  }

  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    return false;
  } 

  if (!isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `name`");
    return false;
  }

  $old_group_name = $group_data['name'];
  $query_text = "UPDATE `DUTY_group` SET `name` = '{$group_new_name}', `about` = '{$group_new_about}' WHERE `id` = {$group_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Edit `group_manager{$group_id}` in `DUTY_group`");
  return [
    "data" => [
      "message" => "Информация о группе {$group_new_name} (бывш. $old_group_name) изменена"
    ]
  ];
}

// удаление группы и ее студентов 
function return_api_delete_group($user_data_by_token, $group_id) {

  if (!isset($user_data_by_token['role']) || !isset($user_data_by_token['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role` or `id`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  $id = $user_data_by_token['id'];

  if (!$group_list = return_api_get_list_your_groups($user_data_by_token)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `get_manager_list_by_college`");
    return false;
  }
  
  if(!is_id_in_list($group_id, $group_list['data']['groups'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    return false;
  } 

  if (!isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `name`");
    return false;
  }

  $group_name = $group_data['name'];

  if (is_manager_group_role($id, $group_id, "manager")) {

    $query_text = "DELETE FROM `DUTY_control` WHERE `group_id` = {$group_id} AND  `manager_id` = {$id} AND `role` = 'manager';";

    if(!db_query($query_text)) {
      debug("e", __FILE__, __FUNCTION__, "Group control for `manager{$id}` `group_id{$group_id}` NOT deleted");
      return false;
    }
  
    debug("i", __FILE__, __FUNCTION__, "Group control for `manager{$id}` `group_id{$group_id}` deleted");
    return [
      "data" => [
        "message" => "Вы удалили себя из менеджеров группы {$group_name}"
      ]
    ];
  }
  
  $query_text = "DELETE FROM `DUTY_group` WHERE `DUTY_group`.`id` = {$group_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Group `id{$group_id}` NOT deleted");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Group `id{$group_id}` deleted");

  $query_text = "DELETE FROM `DUTY_control` WHERE `group_id` = {$group_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Group control for `group_id{$group_id}` NOT deleted");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Group `id{$group_id}` deleted");

  $student_list_names = "";
  if (!empty($student_list_by_group = get_student_list_by_group_id($group_id))) {

    if (!is_array($student_list_by_group)) {
      debug("e", __FILE__, __FUNCTION__, "`student_list_by_group_id` not array");
      return false;
    }
  
    if (!isset($student_list_by_group[0]['name'])) {
      debug("e", __FILE__, __FUNCTION__, "In `student_list_by_group` missing column `login` ");
    }
  
    foreach ($student_list_by_group as $key => $value) {
      $student_list_names .= $student_list_by_group[$key]['name'].", ";
    }

    $student_list_names = rtrim($student_list_names, ", ");
    $query_text = "DELETE FROM `DUTY_student` WHERE `group_id` = {$group_id};";

    if(!db_query($query_text)) {
      debug("e", __FILE__, __FUNCTION__, "Students `group_id{$group_id}` NOT deleted");
      return false;
    }

    debug("i", __FILE__, __FUNCTION__, "Students `group_id{$group_id}` deleted");
  }

  debug("i", __FILE__, __FUNCTION__, "Group `id{$group_id}` and Students [{$student_list_names}] deleted");
  return [
    "data" => [
      "message" => "Группа {$group_name} и ее студенты ({$student_list_names}) удалены"
    ]
  ];
}

// изменение данных меня 
function return_api_edit_my_data($user_data_by_token, $my_id, $my_new_name, $my_new_about) {

  if (!isset($my_id) || !$my_new_name) {
    debug("e", __FILE__, __FUNCTION__, "`my_id` or `my_new_name` or `my_new_about` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор или имя пользователя отсутствуют");
    return false;
  }

  if (!is_dbint($my_id = sanitize_string($my_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя некорректен");
    return false;
  }

  if (!isset($user_data_by_token['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id`");
    return false;
  }

  if ($user_data_by_token['id'] != $my_id) {
    debug("e", __FILE__, __FUNCTION__, "My `id` is not `my_id`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_varchar($my_new_name = sanitize_string($my_new_name), 128)) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_new_name` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Имя некорректно");
    return false;
  }

  if ($my_new_about) {
    if (!is_varchar($my_new_about = sanitize_string($my_new_about), 256)) {
      debug("e", __FILE__, __FUNCTION__, "Data `my_new_about` from query is invalid");
      debug("m", __FILE__, __FUNCTION__, "Описание некорректно");
      return false;
    }
  }

  $query_text = "UPDATE `DUTY_user` SET `name` = '{$my_new_name}', `about` = '{$my_new_about}' WHERE `id` = {$my_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!isset($user_data_by_token['login'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data` missing column `login`");
    return false;
  }

  $user_login = $user_data_by_token['login'];

  debug("i", __FILE__, __FUNCTION__, "Edit `my{$my_id}` in `DUTY_user`");
  return [
    "data" => [
      "message" => "Информация для ($user_login) изменена"
    ]
  ];
}

// изменение логина меня
function return_api_edit_my_login($user_data_by_token, $my_id, $my_new_login) {

  if (!isset($my_id) || !$my_new_login) {
    debug("e", __FILE__, __FUNCTION__, "`my_id` or `my_new_login` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя или логин отсутствуют");
    return false;
  }

  if (!is_dbint($my_id = sanitize_string($my_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя некорректен");
    return false;
  }

  if (!isset($user_data_by_token['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id`");
    return false;
  }

  if ($user_data_by_token['id'] != $my_id) {
    debug("e", __FILE__, __FUNCTION__, "My `id` is not `my_id`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_varchar($my_new_login = sanitize_string($my_new_login), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_new_login` from query is invalid");
    return false;
  }

  if (!is_valid_login($my_new_login)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid login");
    debug("m", __FILE__, __FUNCTION__, "Некорректный логин");
    return false;
  }

  if (!check_login_uniqueness($my_new_login)) {
    debug("e", __FILE__, __FUNCTION__, "Login `{$my_new_login}` is not unique");
    debug("m", __FILE__, __FUNCTION__, "Логин занят");
    return false;
  }

  $query_text = "UPDATE `DUTY_user` SET `login` = '{$my_new_login}' WHERE `id` = {$my_id};";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!isset($user_data_by_token['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data` missing column `name`");
    return false;
  }

  $my_name = $user_data_by_token['name'];

  if (!isset($user_data_by_token['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result(0)` missing column `password`");
    return false;
  }

  $token = token_gen($my_new_login, $user_data_by_token['password']);

  debug("i", __FILE__, __FUNCTION__, "Edit `group_manager{$my_id}` in `DUTY_user`");
  return [
    "data" => [
      "message" => "Логин для $my_name изменен на ({$my_new_login})",
      "login" => $my_new_login,
      "token" => $token
    ]
  ];
}

// сброс пароля меня
function return_api_edit_my_password($user_data_by_token, $my_id, $my_ative_password, $my_new_password) {

  if (!isset($my_id) || !$my_ative_password || !$my_new_password) {
    debug("e", __FILE__, __FUNCTION__, "`my_id` or `my_ative_password` or `my_new_password` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя или пароль отсутствуют");
    return false;
  }

  if (!is_dbint($my_id = sanitize_string($my_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя некорректен");
    return false;
  }

  if (!isset($user_data_by_token['id']) || !isset($user_data_by_token['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id`");
    return false;
  }

  if ($user_data_by_token['id'] != $my_id) {
    debug("e", __FILE__, __FUNCTION__, "My `id` is not `my_id`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  $my_ative_password_hash = md5($my_ative_password);

  if ($my_ative_password_hash != $user_data_by_token['password']) {
    debug("e", __FILE__, __FUNCTION__, "My `ipasswordd` is not `my_ative_password`");
    debug("m", __FILE__, __FUNCTION__, "Старый пароль указан неверно");
    return false;
  }

  if (!is_varchar($my_new_password, 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `manager_new_password` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Пароль некорректен");
    return false;
  }

  $password_hash = md5($my_new_password);
  $query_text = "UPDATE `DUTY_user` SET `password` = '{$password_hash}' WHERE `id` = {$my_id};";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!isset($user_data_by_token['name']) || !isset($user_data_by_token['login']) || !isset($user_data_by_token['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `name`or `login` or `password`");
    return false;
  }

  $my_name = $user_data_by_token['name'];
  $my_login = $user_data_by_token['login'];
  $token = token_gen($my_login, $password_hash);

  debug("i", __FILE__, __FUNCTION__, "Edit `group_manager{$my_id}` in `DUTY_user`");
  return [
    "data" => [
      "message" => "Пароль для $my_name ($my_login) сброшен",
      "login" => $my_login,
      "token" => $token
    ]
  ];
}

// добавить группу зная ключ
function return_api_add_an_existing_group($user_data_by_token, $add_key) {
  global $LIMIT_GROUPS;

  if (!isset($user_data_by_token['role']) || !isset($user_data_by_token['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role` or `id`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$add_key) {
    debug("e", __FILE__, __FUNCTION__, "`add_key` is empty");
    debug("m", __FILE__, __FUNCTION__, "Ключ группы отсутствует");
    return false;
  }

  if (!is_varchar($add_key = sanitize_string($add_key), 24)) {
    debug("e", __FILE__, __FUNCTION__, "Data `add_key` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Некорректный ключ группы");
    return false;
  }

  $my_id = $user_data_by_token['id'];

  if (!is_dbint($count_group = return_count_groups($my_id))) {
    debug("e", __FILE__, __FUNCTION__, "Return not `count_groups`");
    return false;
  }

  if ($count_group >= $LIMIT_GROUPS) {
    debug("e", __FILE__, __FUNCTION__, "count_group > LIMIT_GROUPS");
    debug("m", __FILE__, __FUNCTION__, "Вы достигли максимального количества управляемых групп");
    return false;
  }

  if (!$group_data = get_group_info_by_link($add_key)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info_by_link`");
    debug("m", __FILE__, __FUNCTION__, "Ключ группы некорретен");
    return false;
  } 
  
  if (!isset($group_data['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `name`");
    return false;
  }

  $group_id = $group_data['id'];

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_users_of_your_group`");
    return false;
  }
  
  if (is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Doubled group"); 
    debug("m", __FILE__, __FUNCTION__, "Вы уже управляете данной группой");
    return false;
  }

  if (!is_dbint($my_id = sanitize_string($my_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `my_id` from query is invalid");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  if (!isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `name`");
    return false;
  }

  $group_name = $group_data['name'];
  $query_text = "INSERT INTO `DUTY_control` (`id`, `manager_id`, `group_id`, `role`) VALUES (NULL, '{$my_id}', '{$group_id}', 'manager');";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Edit `group_manager{$my_id}` in `DUTY_user`");
  return [
    "data" => [
      "message" => "Группа {$group_name} была добавлена"
    ]
  ];
}

// сброс ключа группы и удаление всех управлений
function return_api_reset_group_key($user_data_by_token, $group_id) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  if (!is_manager_group_role($user_data_by_token['id'], $group_id, "admin")) {
    debug("e", __FILE__, __FUNCTION__, "User is not `admin`");
    debug("m", __FILE__, __FUNCTION__, "Вы не создатель этой группы");
    return false;
  }

  if (!$group_list = return_api_get_list_your_groups($user_data_by_token)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `get_manager_list_by_college`");
    return false;
  }
  
  if(!is_id_in_list($group_id, $group_list['data']['groups'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    return false;
  } 

  if (!isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `name`");
    return false;
  }

  $group_name = $group_data['name'];
  $group_new_key = generateUniqueString();

  if (!is_varchar($group_new_key = sanitize_string($group_new_key), 24)) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_new_key` from query is invalid");
    return false;
  }

  $query_text = "UPDATE `DUTY_group` SET `link` = '{$group_new_key}' WHERE `DUTY_group`.`id` = {$group_id};";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Group link `id{$group_id}` NOT reset");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Group link for `group_id{$group_id}` reset");
  $query_text = "DELETE FROM `DUTY_control` WHERE `group_id` = {$group_id} AND `role` = 'manager'; ";

  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Group control for `group_id{$group_id}` NOT deleted");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Group access for `group_id{$group_id} closed `");
  
  return [
    "data" => [
      "message" => "Ключ группы {$group_name} общий доступ к ней сброшены",
      "group_key" => $group_new_key
    ]
  ];
}

// кто управляет группой
function return_api_who_manages_this_group($user_data_by_token, $group_id) {

  if (!isset($user_data_by_token['role']) || !isset($user_data_by_token['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role` or `id`");
    return false;
  }

  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!$group_list = return_api_get_list_your_groups($user_data_by_token)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `get_manager_list_by_college`");
    return false;
  }
  
  if(!is_id_in_list($group_id, $group_list['data']['groups'], "id")) {
    debug("e", __FILE__, __FUNCTION__, "Return not `is_id_in_list`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!is_dbint($my_id = sanitize_string($user_data_by_token['id']))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  $query_text = "
    SELECT
      `u`.`name` AS `name`,
      `u`.`login` AS `login`,
      `c`.`role` AS `role`
    FROM
      `DUTY_control` AS `c`
    INNER JOIN
      `DUTY_user` AS `u`
    ON
      `c`.`manager_id` = `u`.`id`
    WHERE
      `c`.`group_id` = {$group_id}
      AND `c`.`manager_id` != {$my_id}
    ORDER BY
      `c`.`role`;
  ";


  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $query_result = array();
  }

  debug("i", __FILE__, __FUNCTION__, "Return `list_users_of_your_group`");
  
  return [
    "data" => [
      "managers" => $query_result
    ]
  ];
}

// удаление меня
function return_api_delete_my_account($user_data_by_token, $user_id, $my_password) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }
  
  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($user_id) || !$my_password) {
    debug("e", __FILE__, __FUNCTION__, "`user_id` or `my_password` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя или пароль отсутствуют");
    return false;
  }

  if (!is_dbint($user_id = sanitize_string($user_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `user_id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор пользователя некорректен");
    return false;
  }

  if (!isset($user_data_by_token['id']) || !isset($user_data_by_token['login']) || !isset($user_data_by_token['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id` or `login` or `password`");
    return false;
  }
  
  $login = $user_data_by_token['login'];

  if ($user_data_by_token['id'] != $user_id) {
    debug("e", __FILE__, __FUNCTION__, "My `id` is not `user_id`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (md5($my_password) != $user_data_by_token['password']) {
    debug("e", __FILE__, __FUNCTION__, "No correct password");
    debug("m", __FILE__, __FUNCTION__, "Удаление не подтверждено");
    return false;
  }

  $query_text = "DELETE FROM `DUTY_report`
  WHERE `student_id` IN (
      SELECT `DUTY_student`.`id`
      FROM `DUTY_student`
      JOIN `DUTY_control` ON `DUTY_student`.`group_id` = `DUTY_control`.`group_id`
      WHERE `DUTY_control`.`role` = 'admin' AND `DUTY_control`.`manager_id` = {$user_id}
  );";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "From `DUTY_report` deleted reports");  

  $query_text = "DELETE FROM `DUTY_student`
  WHERE `group_id` IN (
      SELECT `id`
      FROM `DUTY_control`
      WHERE `role` = 'admin' AND `manager_id` = {$user_id}
  );";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "From `DUTY_student` deleted students");  

  $query_text = "DELETE FROM `DUTY_group`
  WHERE `id` IN (
      SELECT `group_id`
      FROM `DUTY_control`
      WHERE `role` = 'admin' AND `manager_id` = $user_id
  );";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "From `DUTY_group` deleted groups");  

  $query_text = "DELETE FROM `DUTY_control`  WHERE `manager_id` = $user_id;";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "From `DUTY_control` deleted controls");  

  $query_text = "DELETE FROM `DUTY_user`  WHERE `id` = $user_id;";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "User `id{$user_id}` and all his connections have been deleted");  
  return [
    "data" => [
      "message" => "Пользователь {$login}, все его группы и студенты удалены",
    ]
  ];
}

// отчет по дежурствам
function return_api_create_group_report($user_data_by_token, $group_id, $start_date, $end_date) {
  global $LIMIT_REPORT;

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }
  
  if ($user_data_by_token['role'] != "group_manager") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  if (!$list_group_managers = get_list_group_managers($group_id)) {
    debug("e", "api-function", __FUNCTION__, "Return not `list_group_managers`");
    return false;
  }
  
  if (!is_id_in_list($user_data_by_token['id'], $list_group_managers, "manager_id")) {
    debug("e", __FILE__, __FUNCTION__, "Invalid access"); 
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!is_dbdate($start_date) || !is_dbdate($end_date)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid `start_date` or `end_date` format"); 
    debug("m", __FILE__, __FUNCTION__, "Дата начала или конца некорректна");
    return false;
  }

  if ($start_date > $end_date) {
    debug("e", __FILE__, __FUNCTION__, "`start_date` is greater than or equal to `end_date`"); 
    debug("m", __FILE__, __FUNCTION__, "Дата начала или конца некорректна");
    return false;
  } 

  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    return false;
  } 

  if (!isset($group_data['name']) || !isset($group_data['about'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `about` or `name`");
    return false;
  }

  $group_name = $group_data['name'];
  $group_about = $group_data['about'];
  $query_text = "SELECT `DUTY_report`.`date`, GROUP_CONCAT(`DUTY_student`.`name` SEPARATOR ', ') AS `students` FROM `DUTY_report` JOIN `DUTY_student` ON `DUTY_report`.`student_id` = `DUTY_student`.`id` WHERE `DUTY_student`.`group_id` = '{$group_id}' AND `DUTY_report`.`date` BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY `DUTY_report`.`date`;";

  if(!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Report by `group{$group_id}` from date(`{$start_date}` > `{$end_date}`) NOT generated");
    return false;
  }

  if (mysqli_num_rows($query_call) >= $LIMIT_REPORT) {
    debug("e", __FILE__, __FUNCTION__, "mysqli_num_rows(query_call) > LIMIT_REPORT"); 
    debug("m", __FILE__, __FUNCTION__, "Отчет слишком большой");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $query_result = array();
  }

  debug("i", __FILE__, __FUNCTION__, "Report by `group{$group_id}` from date(`{$start_date}` > `{$end_date}`) generated");
  return [
    "data" => [
      "message" => "Отчет для группы {$group_name} сгенерирован",
      "group" => [
        "name" => $group_name, 
        "about" => $group_about
      ],
      "report" => $query_result
    ]
  ];
}

// получить журнал лога
function return_api_get_list_log($user_data_by_token, $count_pages, $page) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", "file", __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "system_admin") {
    debug("e", __FILE__, __FUNCTION__, "Invalid `role`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($page) || !isset($count_pages)) {
    debug("e", __FILE__, __FUNCTION__, "`page` or `count_pages` is empty");
    debug("m", __FILE__, __FUNCTION__, "Количество записей на странице или номер страницы отсутствуют");
    return false;
  }

  if (!is_dbint($count_pages = sanitize_string($count_pages)) || $count_pages < 1) {
    debug("e", __FILE__, __FUNCTION__, "Data `duty_status` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Количество записей на странице некорректно");
    return false;
  }

  if (!is_dbint($page = sanitize_string($page)) || $page < 1) {
    debug("e", __FILE__, __FUNCTION__, "Data `page` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Номер страницы некорректен");
    return false;
  }

  $offset_num = (($page - 1) * $count_pages);

  if (!is_dbint($offset_num = sanitize_string($offset_num))) {
    debug("e", __FILE__, __FUNCTION__, "Data `offset_num` from query is invalid");
    return false;
  }

  $query_text = "SELECT `DUTY_log`.`datetime`, `DUTY_log`.`event_key`, CASE WHEN `DUTY_log`.`user_id` IS NULL THEN 'EMPTY' ELSE `DUTY_user`.`login` END AS `user_login`, `DUTY_log`.`log_message` FROM `DUTY_log` LEFT JOIN `DUTY_user` ON `DUTY_log`.`user_id` = `DUTY_user`.`id` ORDER BY `DUTY_log`.`datetime` DESC LIMIT {$count_pages} OFFSET {$offset_num};";
  
  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $query_result = array();
  }

  if (!$count_lists = total_log_pages($count_pages)) {
    debug("w", __FILE__, __FUNCTION__, "`count_lists` is empty");
    $count_lists = false;
  }

  // временное убирание сообщений
  // foreach ($query_result as $key => $value) {
  //   $query_result[$key]['log_message'] = "message{$key}";
  // }

  debug("i", __FILE__, __FUNCTION__, "Return log for `page{$page}` from `count_pages{$count_pages}` and `count_lists{$count_lists}`");
  return [
    "data" => [
      "count_lists" => $count_lists,
      "count_pages" => $count_pages,
      "page" => $page,
      "log" => $query_result
    ]
  ];
}

// сброс пароля по логину
function return_api_reset_user_password($user_data_by_token, $user_login, $user_new_password) {

  if (!isset($user_data_by_token['role'])) {
    debug("e", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `role`");
    return false;
  }

  if ($user_data_by_token['role'] != "system_admin") {
    debug("e", __FILE__, __FUNCTION__, "You is not `system_admin`");
    debug("m", __FILE__, __FUNCTION__, "Вы не имеете право на это действие");
    return false;
  }

  if (!isset($user_login) || !$user_new_password) {
    debug("e", __FILE__, __FUNCTION__, "`user_login` or `user_new_password` is empty");
    debug("m", __FILE__, __FUNCTION__, "Логин или пароль отсутствуют");
    return false;
  }

  if (!is_varchar($user_login = sanitize_string($user_login), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `user_login` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Логин некорректен");
    return false;
  }

  if (!is_varchar($user_new_password, 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `user_new_password` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Пароль некорректен");
    return false;
  }

  $password_hash = md5($user_new_password);
  $query_text = "UPDATE `DUTY_user` SET `password` = '{$password_hash}' WHERE `login` = '{$user_login}';";
  
  if(!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Edit `group_manager{$user_login}` in `DUTY_user`");
  return [
    "data" => [
      "message" => "Пароль для ({$user_login}) сброшен"
    ]
  ];
}

// получить дежурящих студентов по айди группы
function return_api_get_current_duty_students($group_id) {

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы отсутствует");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `id` from query is invalid");
    debug("m", __FILE__, __FUNCTION__, "Идентификатор группы некорректен");
    return false;
  }

  $query_text = "SELECT `name` FROM `DUTY_student` WHERE `group_id` = {$group_id} AND `duty_status` = 1 ORDER BY `name`;"; 
  
  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$students_list = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $students_list = array();
  }

  $query_text = "
    SELECT `p`.`duty_date`, `s`.`name`
    FROM `DUTY_planning` AS `p`
    INNER JOIN `DUTY_student` AS `s` ON `p`.`student_id` = `s`.`id`
    WHERE `s`.`group_id` = {$group_id}
      AND DATE_FORMAT(`p`.`duty_date`, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
    ORDER BY `p`.`duty_date`;
  ";
  
  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$planning_list = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "`query_result` is empty");
    $planning_list = array();
  }

  if (!$group_data = get_group_info($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "Return not `group_info`");
    debug("m", __FILE__, __FUNCTION__, "Нет такой группы");
    return false;
  } 

  if (!isset($group_data['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_data` missing column `id` or `name`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `current_duty_students`");
  return [
    "data" => [
      "group" => $group_data['name'],
      "students" => $students_list,
      "planning" => $planning_list
    ]
  ];
}

?>
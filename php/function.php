<!-- Этот файл является частью проекта "Электронный дежурный журнал". -->
<!-- Проект распространяется под лицензией GNU GPL v3.0. -->
<!-- Полный текст лицензии см. в файле LICENSE. -->
<?php

include_once "core.php";

// ======================== Функции ========================

// логирование
function logging_event($user_data_by_token, $event_key) {
  global $error, $debug, $_SERVER;

  if (!$event_key) {
    debug("e", __FILE__, __FUNCTION__, "`event_key` or `user_login` or `log_message` is empty");
    return false;
  }

  if (!is_varchar($event_key = sanitize_string($event_key), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `event_key` from query is invalid");
    return false;
  }

  $ip = $_SERVER['REMOTE_ADDR'];

  if (!is_varchar($ip = sanitize_string($ip), 16)) {
    debug("e", __FILE__, __FUNCTION__, "Data `ip` from query is invalid");
    return false;
  }
  
  if (isset($user_data_by_token['id'])) {
    $user_id = $user_data_by_token['id'];

    if (!is_dbint($user_id = sanitize_string($user_id))) {
      debug("e", __FILE__, __FUNCTION__, "Data `user_id` from query is invalid");
      return false;
    }

    $user_id = "'".$user_id."'";
    debug("w", __FILE__, __FUNCTION__, "user_id is {$user_id}");
    
  } else {
    $user_id = "NULL";
    debug("w", __FILE__, __FUNCTION__, "In `user_data_by_token` missing column `id`");
  }

  $log_message = sanitize_string(json_encode($debug));

  $query_text = "INSERT INTO `DUTY_log` (`id`, `datetime`, `event_key`, `user_id`, `ip`, `log_message`) VALUES (NULL, CURRENT_TIMESTAMP, '{$event_key}', {$user_id}, '{$ip}', '{$log_message}');";
  
  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No logging event `{$event_key}`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Logging event `{$event_key}`");
}

// логирование для отчета
function log_for_report($group_id, $student_id) {

  if (!isset($group_id ) || !isset($student_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id ` or `student_id` is empty");
    return false;
  }

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    return false;
  }

  if (!is_dbint($group_id  = sanitize_string($group_id ))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id ` from query is invalid");
    return false;
  }

  $query_text = "INSERT IGNORE INTO `DUTY_report` (`student_id`, `date`) VALUES ('{$student_id}', CURRENT_TIMESTAMP);";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Logging report for `student_id{$student_id}`");
}

// количество авторизаций
function return_count_auth() {

  $ip = $_SERVER['REMOTE_ADDR'];

  if (!is_varchar($ip = sanitize_string($ip), 16)) {
    debug("e", __FILE__, __FUNCTION__, "Data `ip` from query is invalid");
    return false;
  }

  $query_text = "SELECT COUNT(*) AS `count_records` FROM `DUTY_log` WHERE `event_key` = 'authorization' AND `datetime` >= NOW() - INTERVAL 30 MINUTE AND `ip` = '$ip';";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`ip{$ip}` not received in `DUTY_log`");
    return false;
  }

  if (!isset($query_result[0]['count_records'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result` missing column `count_records`");
    return false;
  }

  $count_records = intval($query_result[0]['count_records']);

  debug("i", __FILE__, __FUNCTION__, "Return count ip `$count_records`");
  return $count_records;
}

// Триггер планировщика
function planning_trigger() {
  $CURDATE = "CURDATE()"; // CURDATE() | DATE('2023-09-25')

  // запись в репорты репорт тех, у кого статус Дежурит 
  $query_text = "
      INSERT IGNORE INTO `DUTY_report` (`student_id`, `date`)
      SELECT `id`, DATE_SUB({$CURDATE}, INTERVAL 1 DAY)
      FROM `DUTY_student`
      WHERE `duty_status` = 1
      AND EXISTS (
          SELECT 1
          FROM `DUTY_planning`
          WHERE `duty_date` < {$CURDATE}
      );
  ";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "INSERT IGNORE INTO `DUTY_report` NOT");
    debug("m", __FILE__, __FUNCTION__, "INSERT IGNORE INTO `DUTY_report` NOT");
    return false;

  } else {
    debug("i", __FILE__, __FUNCTION__, "INSERT IGNORE INTO `DUTY_report`");
  }

  // если статус любой кроме уже дежурит и он запланирован на сегодня 
  // переместить его в Дежурящих во всех ситуациях кроме когда он уже Дежурный
  $query_text = "
      UPDATE `DUTY_student`
      SET `duty_status` = 2,
          `duty_count` = CASE
          WHEN `duty_count` > 0 THEN `duty_count` - 1
          ELSE `duty_count`
      END
      WHERE `duty_status` = 1
      AND EXISTS (
          SELECT 1
          FROM `DUTY_planning`
          WHERE `duty_date` < {$CURDATE}
      );
  ";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "UPDATE `DUTY_student` WHEN `duty_status` = 1 THEN 2 ELSE 1 NOT");
    debug("m", __FILE__, __FUNCTION__, "UPDATE `DUTY_student` WHEN `duty_status` = 1 THEN 2 ELSE 1 NOT");
    return false;

  } else {
    debug("i", __FILE__, __FUNCTION__, "UPDATE `DUTY_student` WHEN `duty_status` = 1 THEN 2 ELSE 1");
  }

    // сменить статус с Дежурит на Отежурил и минусануть счетчик
    $query_text = "
    UPDATE `DUTY_student`
    SET
        `duty_status` = 1
    WHERE
    `id` IN (
        SELECT DISTINCT `student_id`
        FROM `DUTY_planning`
        WHERE `duty_date` = {$CURDATE}
    );
  ";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "UPDATE `DUTY_student` `duty_status` = 2 NOT");
    debug("m", __FILE__, __FUNCTION__, "UPDATE `DUTY_student` `duty_status` = 2 NOT");
    return false;

  } else {
    debug("i", __FILE__, __FUNCTION__, "UPDATE `DUTY_student` `duty_status` = 2");
  }

  $query_text = "
    DELETE FROM `DUTY_planning`
    WHERE `duty_date` < {$CURDATE};
  ";

  if (!db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "DELETE FROM `DUTY_planning` NOT");
    debug("m", __FILE__, __FUNCTION__, "DELETE FROM `DUTY_planning` NOT");
    return false;

  } else {
    debug("i", __FILE__, __FUNCTION__, "DELETE FROM `DUTY_planning`");
  }
  
  debug("i", __FILE__, __FUNCTION__, "Planning trigger triggerred");
  return true;
}

// проверка true/false в базе
function check_in_db($query_text) {

  if (!$query_text) {
    debug("e", __FILE__, __FUNCTION__, "`query_text` is empty");
    return false;
  }

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "Request failed");
    return false;
  } 

  return mysqli_num_rows($query_call) ? true : false;
}

// проверить логин на уникальность
function check_login_uniqueness($login) {

  if (!$login) {
    debug("e", __FILE__, __FUNCTION__, "`login` is empty");
    return false;
  }

  if (!is_varchar($login = sanitize_string($login), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `user_login` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_user` WHERE `login` LIKE '{$login}';";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("е", __FILE__, __FUNCTION__, "Testing `string($login)`");
  return !(mysqli_num_rows($query_call));
}

// получить имя студента по его айди
function return_student_name($student_id) {

  if (!isset($student_id)) {
    debug("e", __FILE__, __FUNCTION__, "`student_id` is empty");
    return false;
  }

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_student` WHERE `id` = {$student_id};";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$student_id}` not received in `DUTY_student`");
    return false;
  }

  if (!isset($query_result[0]['name'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result` missing column `name`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `name` `id$student_id`");
  return $query_result[0]['name'];
}

// количество созданных студентов
function return_count_students($group_id) {

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT COUNT(*) AS `count_records` FROM `DUTY_student` WHERE `group_id` = {$group_id};";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$group_id}` not received in `DUTY_student`");
    return false;
  }

  if (!isset($query_result[0]['count_records'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result` missing column `count_records`");
    return false;
  }

  $count_records = intval($query_result[0]['count_records']);
  
  debug("i", __FILE__, __FUNCTION__, "Return count `group_id$group_id`");
  return $count_records;
}

// количество управляемых групп
function return_count_groups($manager_id) {

  if (!isset($manager_id)) {
    debug("e", __FILE__, __FUNCTION__, "`manager_id` is empty");
    return false;
  }

  if (!is_dbint($manager_id = sanitize_string($manager_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `manager_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT COUNT(*) AS `count_records` FROM `DUTY_control` WHERE `manager_id` = {$manager_id};";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$manager_id}` not received in `DUTY_control`");
    return false;
  }

  if (!isset($query_result[0]['count_records'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result` missing column `count_records`");
    return false;
  }

  $count_records = intval($query_result[0]['count_records']);

  debug("i", __FILE__, __FUNCTION__, "Return count `manager_id$manager_id`");
  return $count_records;
}

// получить информацию о пользователе по его айди
function get_user_info($manager_id) {

  if (!isset($manager_id)) {
    debug("e", __FILE__, __FUNCTION__, "`manager_id` is empty");
    return false;
  }

  if (!is_dbint($manager_id = sanitize_string($manager_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `manager_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_user` WHERE `DUTY_user`.`id` = {$manager_id};";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$manager_id}` not received in `DUTY_user`");
    return false;
  }

  if (!isset($query_result[0]['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result(0)` missing column `password`");
    return false;
  }

  $query_result[0]['password'] = "securited";

  debug("i", __FILE__, __FUNCTION__, "Return `group_info` by `manager_id`");
  return $query_result[0];
}

// данные о пользователе по логину
function get_user_info_by_login($manager_login) {

  if (!isset($manager_login)) {
    debug("e", __FILE__, __FUNCTION__, "`manager_login` is empty");
    return false;
  }

  if (!is_varchar($manager_login = sanitize_string($manager_login), 64)) {
    debug("e", __FILE__, __FUNCTION__, "Data `login` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_user` WHERE `login` = '{$manager_login}';";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$manager_login}` not received in `DUTY_user`");
    return false;
  }

  if (!isset($query_result[0]['password'])) {
    debug("e", __FILE__, __FUNCTION__, "In `query_result(0)` missing column `password`");
    return false;
  }

  $query_result[0]['password'] = "securited";

  debug("i", __FILE__, __FUNCTION__, "Return `group_info` by `manager_login`");
  return $query_result[0];
}

// получить информацию о студенте по его айди
function get_student_info($student_id) {

  if (!isset($student_id)) {
    debug("e", __FILE__, __FUNCTION__, "`student_id` is empty");
    return false;
  }

  if (!is_dbint($student_id = sanitize_string($student_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `student_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_student` WHERE `DUTY_student`.`id` = {$student_id};";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$student_id}` not received in `DUTY_user`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `group_info` by `student_id`");
  return $query_result[0];
}

// получить информацию о группе по ее айди
function get_group_info($id) {

  if (!isset($id)) {
    debug("e", __FILE__, __FUNCTION__, "`id` is empty");
    return false;
  }

  if (!is_dbint($id = sanitize_string($id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `id` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_group` WHERE `DUTY_group`.`id` = {$id};";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$id}` not received in `DUTY_group`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `group_info` by `id`");
  return $query_result[0];
}

// получить информацию о группе по ее ключу
function get_group_info_by_link($add_key) {

  if (!isset($add_key)) {
    debug("e", __FILE__, __FUNCTION__, "`add_key` is empty");
    return false;
  }

  if (!is_varchar($add_key = sanitize_string($add_key), 24)) {
    debug("e", __FILE__, __FUNCTION__, "Data `add_key` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_group` WHERE `link` = '{$add_key}';";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "key`{$add_key}` not received in `DUTY_group`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `group_info` by `add_key`");
  return $query_result[0];
}

// получить информацию о пользователе по его айди
function get_planning_info($planning_id) {

  if (!isset($planning_id)) {
    debug("e", __FILE__, __FUNCTION__, "`planning_id` is empty");
    return false;
  }

  if (!is_dbint($planning_id = sanitize_string($planning_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `planning_id` from query is invalid");
    return false;
  }

  // $query_text = "SELECT * FROM `DUTY_planning` WHERE `DUTY_planning`.`id` = {$planning_id};";
  $query_text = "
    SELECT `dp`.*, `ds`.`name` AS `student_name`
    FROM `DUTY_planning` AS `dp`
    INNER JOIN `DUTY_student` AS `ds` ON `dp`.`student_id` = `ds`.`id`
    WHERE `dp`.`id` = {$planning_id};
  ";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "`id{$planning_id}` not received in `DUTY_planning`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `planning_info` by `planning_id`");
  return $query_result[0];
}

// по айди группы получить всех его студентов
function get_student_list_by_group_id($group_id) {

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_student` WHERE `group_id` = {$group_id}";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (mysqli_num_rows($query_call) < 1) {
    debug("w", __FILE__, __FUNCTION__, "mysqli_num_rows < 1");
    return array();
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid list `id{$group_id}` in `DUTY_student`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `student_list` by `group_id`");
  return $query_result;
}

// список менеджеров группы
function get_list_group_managers($group_id) {

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_control` WHERE `group_id` = {$group_id}";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (mysqli_num_rows($query_call) < 1) {
    debug("w", __FILE__, __FUNCTION__, "mysqli_num_rows < 1");
    return array();
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("e", __FILE__, __FUNCTION__, "Invalid list `group_id{$group_id}` in `DUTY_control`");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Return `group_managers` by `group_id`");
  return $query_result;
}

// роль менеджера получить
function is_manager_group_role($manager_id, $group_id, $role) {

  if (!isset($manager_id) || !isset($group_id) || !$role) {
    debug("e", __FILE__, __FUNCTION__, "`manager_id` or `group_id` or `role` is empty");
    return false;
  }

  if (!is_dbint($manager_id = sanitize_string($manager_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `manager_id` from query is invalid");
    return false;
  }

  if (!is_dbint($group_id = sanitize_string($group_id))) {
    debug("e", __FILE__, __FUNCTION__, "Data `group_id` from query is invalid");
    return false;
  }

  if (!is_varchar($role = sanitize_string($role), 16)) {
    debug("e", __FILE__, __FUNCTION__, "Data `role` from query is invalid");
    return false;
  }

  $query_text = "SELECT * FROM `DUTY_control` WHERE `manager_id` = {$manager_id} AND `group_id` = {$group_id} AND `role` = '{$role}'";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (mysqli_num_rows($query_call) < 1) {
    debug("w", __FILE__, __FUNCTION__, "mysqli_num_rows < 1");
    return false;

  } else {
    debug("i", __FILE__, __FUNCTION__, "mysqli_num_rows >= 1");
    return true;
  }
}

// есть ли этот id в списке
function is_id_in_list($group_id, $group_list, $col_name) {

  if (!isset($group_id)) {
    debug("e", __FILE__, __FUNCTION__, "`group_id` is empty");
    return false;
  }

  if (!is_array($group_list) || count($group_list) < 1) {
    debug("e", __FILE__, __FUNCTION__, "`group_list` it's not an array or it's empty");
    return false;
  }

  if (!isset($group_list[0]['id'])) {
    debug("e", __FILE__, __FUNCTION__, "In `group_list(0)` missing column `id`");
    return false;
  }

  foreach ($group_list as $value) {
    if ($value[$col_name] == $group_id) {
      return true;
    }
  }

  return false;
}

function is_unique_name_in_group($group_id, $name) {

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

  $query_text = "SELECT * FROM `DUTY_student` WHERE `name` = '{$name}' AND `group_id` = '{$group_id}';";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  debug("i", __FILE__, __FUNCTION__, "Testing `name($name)` from `group{$group_id}`");
  return !(mysqli_num_rows($query_call));
}

// получить список всех страниц по кол-ву записей на странице
function total_log_pages($count_pages) {

  if (!isset($count_pages)) {
    debug("e", __FILE__, __FUNCTION__, "`count_pages` is empty");
    return false;
  }

  if (!is_dbint($count_pages = sanitize_string($count_pages))) {
    debug("e", __FILE__, __FUNCTION__, "Data `count_pages` from query is invalid");
    return false;
  }

  $query_text = "SELECT CEIL(COUNT(*) / {$count_pages}) AS `total_pages` FROM `DUTY_log`;";

  if (!$query_call = db_query($query_text)) {
    debug("e", __FILE__, __FUNCTION__, "No return");
    return false;
  }

  if (!$query_result = db_assoc_to_array($query_call)) {
    debug("w", __FILE__, __FUNCTION__, "mysqli_num_rows < 1");
    return false;
  }

  if (!isset($query_result[0]['total_pages'])) {
    debug("e", "file", __FUNCTION__, "In `query_result(0)` missing column `total_pages`");
    return false;
  }

  return $query_result[0]['total_pages'];
}

?>
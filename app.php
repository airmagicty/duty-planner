<?php
// подключаем ядро
include_once "php/config.php";
include_once "php/core.php";

// задаем что отображать
$authorization = false;
if (isset($_COOKIE['DUTY']['token']) && isset($_COOKIE['DUTY']['token'])) {
  $user_data_by_token = token_decoding($_COOKIE['DUTY']['token'], $_COOKIE['DUTY']['login'], false);

  if (isset($user_data_by_token['role'])) {
    $authorization = $user_data_by_token['role'];
  }
}

// отладка
if (isset($_COOKIE['DUTY']['debug']))
if ($_COOKIE['DUTY']['debug'] == $DEBUG_KEY) {
  if (!empty($_POST)) {
    print_pre($_POST);
  }
}

// паттерны
$pattern_id = "^[0-9]\d*$";
$pattern_login = "^[a-zA-Z0-9_@\. ]{4,20}$";
$pattern_password = "^.{8,64}$";
$pattern_name = "^.{3,128}$";
$pattern_about = "^.{3,256}$";

?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">

  <title>Электронный дежурный журнал</title>
  <link type="image/x-icon" href="image/favicon.ico" rel="shortcut icon">
  <meta name="description" content="Электронный дежурный журнал — удобное решение для автоматизации распределения обязанностей и управления дежурствами. Создавайте расписания, отслеживайте выполнение задач и получайте уведомления в режиме реального времени.">
  <meta name="keywords" content="электронный дежурный журнал, распределение обязанностей, управление дежурствами, онлайн-расписание, автоматизация задач, планировщик дежурств, система учета дежурств, организация работы, корпоративные инструменты, веб-приложение">

  <link type="text/css" rel="stylesheet" href="https://cdn.eclabs.ru/bootstrap/5.3.1/css/bootstrap.min.css">
	<link type="text/css" rel="stylesheet" href="https://cdn.eclabs.ru/fontawesome/6.2.1/css/all.min.css">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous"> -->
  <link rel="stylesheet" href="css/global.css">

  <!-- Yandex.Metrika counter -->
  <script type="text/javascript" >
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    ym(94995168, "init", {
          clickmap:true,
          trackLinks:true,
          accurateTrackBounce:true
    });
  </script>
  <noscript><div><img src="https://mc.yandex.ru/watch/94995168" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
</head>

<body class="container placeholder-glow">

  <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
    <div id="message" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div id="data_message" class="toast-body">
          <void class="placeholder col-12"></void>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
    <div id="error_message" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div id="data_error_message" class="toast-body">
          <void class="placeholder col-12"></void>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <?php if (!$authorization) : ?>

    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg bg-transparent bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">Дежурный журнал</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav d-flex flex-grow-1">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#header">Главная</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="modal" data-bs-target="#authorizationModal" href="#">Войти</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="modal" data-bs-target="#registrationModal" href="#">Регистрация</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="modal" data-bs-target="#apiModal" href="#">API</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#accordionTitle">О нас</a>
              </li>
            </ul>
            <div class="nav-item d-flex align-items-center">
              <div class="form-switch" title="Цветовой режим">
                <input class="form-check-input" id="color_mode_checkbox" type="checkbox" role="switch" onclick="colorMode()">
                <label class="form-check-label"><i class="fa-solid fa-moon"></i></label>
              </div>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <div class="modal fade" id="authorizationModal" tabindex="-1" aria-labelledby="authorizationModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5">Авторизация</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" id="authorization">
            <div class="modal-body">
              <input type="hidden" name="id" value="authorization">
              <div class="input-group flex-nowrap mb-1">
                <span class="input-group-text"><i class="fa-solid fa-at fa-fw"></i></span>
                <input id="auth_login" type="text" class="form-control" name="auth_login" pattern="<?=$pattern_login?>" placeholder="Логин" aria-label="Логин" aria-describedby="addon-wrapping" data-bs-toggle="tooltip" data-bs-title="Строка от 4 до 64 символов из больших и маленьких английских букв, цифр и знаков '_', '@', '.'" required>
              </div>
              <div class="input-group flex-nowrap">
                <span class="input-group-text"><i class="fa-solid fa-fingerprint fa-fw"></i></i></span>
                <input id="auth_password" type="password" class="form-control" name="auth_password" pattern="<?=$pattern_password?>" placeholder="Пароль" aria-label="Пароль" aria-describedby="addon-wrapping" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required>
              </div>
            </div>
            <div class="modal-footer">
              <input type="submit" class="btn btn-success" value="Войти">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5">Регистрация</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" id="registration">
            <div class="modal-body">
              <input type="hidden" name="id" value="registration">
              <div class="input-group flex-nowrap mb-1">
                <span class="input-group-text"><i class="fa-solid fa-user-tag fa-fw"></i></span>
                <input type="text" class="form-control" name="reg_name" pattern="<?=$pattern_name?>" placeholder="Имя" aria-label="Имя" aria-describedby="addon-wrapping" data-bs-toggle="tooltip" data-bs-title="Строка от 3 до 128 символов" required>
              </div>
              <div class="input-group flex-nowrap mb-1">
                <span class="input-group-text"><i class="fa-solid fa-at fa-fw"></i></span>
                <input id="reg_login" type="text" class="form-control" name="reg_login" pattern="<?=$pattern_login?>" placeholder="Логин" aria-label="Логин" aria-describedby="addon-wrapping" data-bs-toggle="tooltip" data-bs-title="Строка от 4 до 64 символов из больших и маленьких английских букв, цифр и знаков '_', '@', '.'" required>
              </div>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-fingerprint fa-fw"></i></span>
                <input type="password" class="form-control" name="reg_password" pattern="<?=$pattern_password?>" placeholder="Пароль" aria-label="Пароль" autocomplete="new-password" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required>
                <input type="password" id="registration_double_password" pattern="<?=$pattern_password?>" placeholder="Повтор пароля" aria-label="Повтор пароля" class="form-control" autocomplete="new-password" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required>
              </div>
            </div>
            <div class="modal-footer">
              <input type="submit" class="btn btn-success" value="Зарегистрироваться">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="apiModal" tabindex="-1" aria-labelledby="apiModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5">Открытое API</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center">Находится в разработке :(</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Назад</button>
          </div>
        </div>
      </div>
    </div>

    <div id="header" class="container-fluid sph-hero-block-ru py-3 py-md-4">
      <div class="container">
        <div>
          <h1>Электронный дежурный журнал</h1>
          <p class="mt-3 mb-0">Автоматизируйте отчетность, назначайте дежурных в один клик, делитесь активными списками и вместе управляйте одной группой. Простой и удобный способ координации дежурств без лишних забот. Все это доступно на нашем сайте – без необходимости загрузки дополнительных программ.</p>
        </div>
      </div>
    </div>

    <div class="accordion mt-2" id="accordionTitle">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Удобный график дежурств для вас
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionTitle">
          <div class="accordion-body">
            <strong>Чем наш сайт может вам помочь?</strong> 
            <br>С помощью данного сайта вы можете составить удобный вам график дежурств в кабинетах. Это не только упростит вам задачу, но и сэкономит ваше драгоценное время.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Как начать пользоваться?
          </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionTitle">
          <div class="accordion-body">
            <strong>Начните управлять списком сейчас:</strong> 
            <br>Зарегистрируйтесь в системе и создайте свою первую группу или добавьте уже существующую по ключу. Перейдите в раздел управления и добавьте студентов. Назначте студента дежурным и он отобразится в списке, которым вы сможете поделиться в общем чате, чтобы сразу оповещать людей об их очереди. 
            <br>Всего есть 4 списка. <i>Не назначены</i> - отображаются всегда сверху. <i>Дежурят сейчас</i> - отображаются в активном внешнем списке. <i>Уже отдежурили</i> - попадают в отчетность с последующей возможностью формирования группового отчета. <i>Временно освобожденные</i> - игнорируются при сбросе цикла, чтобы сохранить долг по счетчику дежурств, актуально для болеющих. 
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            Почему именно мы?
          </button>
        </h2>
        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionTitle">
          <div class="accordion-body">
            <ol class="list-group list-group-numbered list-group-flush">
              <li class="list-group-item">Назначение дежурных в один клик.</li>
              <li class="list-group-item">Список активных дежурных для вывода на публичный экран.</li>
              <li class="list-group-item">Автоматическая генерация отчетности за указанный период.</li>
              <li class="list-group-item">Один управляет многими списками и многие управляют одним списком.</li>
              <li class="list-group-item">Все данные хранятся на наших серверах, вы не должны ничего скачивать.</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-3">
      <h5 class="card-header">Обратная связь</h5>
      <div class="card-body">
        <p class="card-text">У вас есть какие-то предложения, пожелания или вы просто хотите поделиться впечатлениями? Можете написать нам по электронному адресу: airmagicty@gmail.com, или в телеграм-бота: @airmagicty_bot</p>
      </div>
    </div>

    <footer class="bottom mt-5">
      <div class="text-center fw-lighter">2023 @ Все права защищены</div>
    </footer>

  <?php else : ?>

    <nav id="navigatetopbar" class="navbar bg-body-tertiary fixed-top">
      <div class="container-fluid">
        <span class="navbar-brand">Дежурный журнал</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </nav>

    <div class="offcanvas offcanvas-end" style="width: 250px;" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">Управление <span id="auth_title_offcanvas"><void class="placeholder col-12"></void></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="list-group list-group-flush d-grid">
          <li class="list-group-item list-group-item-action" id="account_layout_nav">
            <a class="nav-link" onclick="navigation.viewLayout('account_layout')" data-bs-dismiss="offcanvas" aria-current="page" href="#">Мой профиль</a>
          </li>
          <?php if ($authorization == "group_manager") : ?>
            <li class="list-group-item list-group-item-action" id="group_management_layout_nav">
              <a class="nav-link" onclick="navigation.viewLayout('group_management_layout')" data-bs-dismiss="offcanvas" aria-current="page" href="#">Мои группы</a>
            </li>
            <li class="list-group-item list-group-item-action" id="student_list_layout_nav">
              <a class="nav-link" onclick="navigation.viewLayout('student_list_layout')" data-bs-dismiss="offcanvas" aria-current="page" href="#">Мои студенты</a>
            </li>
            <li class="list-group-item list-group-item-action" id="duty_planning_layout_nav">
              <a class="nav-link" onclick="navigation.viewLayout('duty_planning_layout')" data-bs-dismiss="offcanvas" aria-current="page" href="#">Планировщик</a>
            </li>
          <?php endif; ?>
          <?php if ($authorization == "system_admin"): ?>
            <li class="list-group-item" id="system_log_layout_nav">
              <a class="nav-link" onclick="navigation.viewLayout('system_log_layout')" data-bs-dismiss="offcanvas" aria-current="page" href="#">Системный журнал</a>
            </li>
            <li class="list-group-item list-group-item-action" id="system_function_layout_nav">
              <a class="nav-link" onclick="navigation.viewLayout('system_function_layout')" data-bs-dismiss="offcanvas" aria-current="page" href="#">Управление</a>
            </li>
          <?php endif; ?>
          <form class="list-group-item list-group-item-action d-flex" method="post" id="logout">
            <input type="hidden" name="id" value="logout">
            <input type="submit" class="nav-link btn flex-grow-1 text-start" value="Выйти">
            <div class="form-switch" title="Цветовой режим">
              <input class="form-check-input" id="color_mode_checkbox" type="checkbox" role="switch" onclick="colorMode()">
              <label class="form-check-label"><i class="fa-solid fa-moon"></i></label>
            </div>
          </form>
        </ul>
      </div>
    </div>

    <div id="account_layout" class="layout d-none">
      <h1>Личный кабинет</h1>
      
      <h5 class="d-flex"><p class="flex-grow-1">Вы авторизованы как <span id="auth_title_p"><void class="placeholder col-12"></void></span></p><i id="auth_title_i"></i></h5>
      <div class="private_office">
        <div class="form-check form-switch mb-1" title="Блокировка изменений">
          <label class="form-check-label"><i class="fa-solid fa-lock"></i></label>
          <input id="edit_my_data_lock" class="form-check-input" type="checkbox" role="switch" onclick="activate.form(this, 'edit_my_data')" checked>
        </div>
        <form method="post" id="edit_my_data" class="mb-3">
          <input type="hidden" name="id" value="edit_my_data">
          <input type="text" name="my_id" class="d-none" pattern="<?=$pattern_id?>" required disabled>
          <div class="form-floating mb-1">
            <input type="text" name="my_new_name" class="form-control" placeholder="myNewName" pattern="<?=$pattern_name?>" data-bs-toggle="tooltip" data-bs-title="Строка от 3 до 128 символов" required disabled>
            <label>Отображаемое имя</label>
          </div>
          <div class="form-floating mb-1">
            <textarea name="my_new_about" class="form-control input-note" placeholder="myAbout" style="height: 100px" pattern="<?=$pattern_about?>" disabled></textarea>
            <label>Дополнительная информация о себе</label>
          </div>
          <input type="submit" class="btn btn-outline-primary" value="Изменить данные о себе" disabled>
        </form>

        <div class="form-check form-switch mb-1" title="Блокировка изменений">
          <label class="form-check-label"><i class="fa-solid fa-lock"></i></label>
          <input id="edit_my_login_lock" class="form-check-input" type="checkbox" role="switch" onclick="activate.form(this, 'edit_my_login')" checked>
        </div>
        <form method="post" id="edit_my_login" class="form-floating mb-3">
          <input type="hidden" name="id" value="edit_my_login">
          <input type="text" name="my_id" class="d-none" pattern="<?=$pattern_id?>" required disabled>
          <input id="edit_my_login_new_login" type="text" name="my_new_login" class="form-control mb-1" placeholder="Логин" pattern="<?=$pattern_login?>" data-bs-toggle="tooltip" data-bs-title="Строка от 4 до 64 символов из больших и маленьких английских букв, цифр и знаков '_', '@', '.'" required disabled>
          <label>Уникальное имя для авторизации</label>
          <input type="submit" class="btn btn-outline-primary" value="Изменить логин" disabled>
        </form>

        <div class="form-check form-switch mb-1" title="Блокировка изменений">
          <label class="form-check-label"><i class="fa-solid fa-lock"></i></label>
          <input id="edit_my_password_lock" class="form-check-input" type="checkbox" role="switch" onclick="activate.form(this, 'edit_my_password')" checked>
        </div>
        <form method="post" id="edit_my_password" class="mb-3">
          <input type="hidden" name="id" value="edit_my_password">
          <input type="text" name="my_id" class="d-none" pattern="<?=$pattern_id?>" required disabled>
          <div class="input-group mb-1">
            <span class="input-group-text"><i class="fa-solid fa-fingerprint"></i></span>
            <input type="password" name="my_ative_password" class="form-control" placeholder="Текущий пароль" aria-label="Текущий пароль" pattern="<?=$pattern_password?>" required disabled>
          </div>
          <div class="input-group mb-1">
            <span class="input-group-text"><i class="fa-solid fa-fingerprint"></i></span>
            <input type="password" name="my_new_password" class="form-control" placeholder="Новый пароль" aria-label="Новый пароль" pattern="<?=$pattern_password?>" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required disabled>
            <input type="password" id="edit_my_password_double_password" class="form-control" placeholder="Подтверждение пароля" aria-label="Подтверждение пароля" pattern="<?=$pattern_password?>" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required disabled>
          </div>
          <input type="submit" class="btn btn-outline-primary" value="Сбросить пароль" disabled>
        </form>

        <?php if ($authorization != "system_admin") : ?>
          <div class="form-check form-switch mb-1" title="Блокировка изменений">
            <label class="form-check-label"><i class="fa-solid fa-lock"></i></label>
            <input id="delete_my_account_lock" class="form-check-input" type="checkbox" role="switch" onclick="activate.form(this, 'delete_my_account')" checked>
          </div>
          <button type="button" class="btn btn-outline-danger mb-1" data-bs-toggle="modal" data-bs-target="#deleteMyAccountModal">
            Удалить аккаунт
          </button>
          <div class="modal fade" id="deleteMyAccountModal" tabindex="-1" aria-labelledby="deleteMyAccountModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5">Подтвердите удаление</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                  <form method="post" id="delete_my_account">
                    <div class="modal-body">
                      <input type="hidden" name="id" value="delete_my_account">
                      <input type="text" name="my_id" class="d-none" pattern="<?=$pattern_id?>" required>
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-fingerprint"></i></span>
                        <div class="form-floating">
                          <input type="password" name="my_password" class="form-control" placeholder="Пароль" aria-label="Подтверждение пароля" pattern="<?=$pattern_password?>" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required disabled>
                          <label>Актуальный пароль</label>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <input type="submit" class="btn btn-danger" value="Удалить аккаунт" disabled>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    </div>
                </form>
              </div>
            </div>
          </div>

          <?php endif; ?>

      </div>

    </div>

  <?php endif; ?>

  <?php if ($authorization == "system_admin") : ?>
    

    <div id="system_log_layout" class="layout d-none">
      <div class="modal fade" id="logMessageModal" tabindex="-1" aria-labelledby="logMessageModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Сообщение журнала</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="log_message" class="modal-body">
              <span><void class="placeholder col-12"></void></span>
            </div>
          </div>
        </div>
      </div>
      
      <h1>Журнал</h1>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Когда</th>
            <th>Событие</th>
            <th>Кто</th>
          </tr>
        </thead>
        <tbody id="log_list">
          <!-- Элементы будут добавлены при помощи JavaScript -->
          <td><void class="placeholder col-12"></void></td>
          <td><void class="placeholder col-12"></void></td>
          <td><void class="placeholder col-12"></void></td>
        </tbody>
      </table>
      <template id="log_list_template">
        <tr id="template_logmessage" role="button" data-bs-toggle="modal" data-bs-target="#logMessageModal">
          <td class="text-break"><time id="template_datetime"><void class="placeholder col-12"></void></time></td>
          <td class="text-break text-uppercase" id="template_eventkey"><void class="placeholder col-12"></void></td>
          <td class="text-break" id="template_userlogin"><void class="placeholder col-12"></void></td>
        </tr>
      </template>

      <div>Страница <span id="current_log_page"><void class="placeholder col-12"></void></span></div>
      <div class="btn-group me-2" role="group" aria-label="First group">
        <button type="button" class="btn btn-outline-secondary" onclick="viewLog(-1)">Назад</button>
        <button type="button" class="btn btn-outline-secondary" onclick="viewLog(-10)"><<</button>
        <button type="button" class="btn btn-outline-secondary" onclick="viewLog(+10)">>></button>
        <button type="button" class="btn btn-outline-secondary" onclick="viewLog(+1)">Вперед</button>
      </div>
    </div>

    <div id="system_function_layout" class="layout d-none">
      <h1>Управление</h1>
    
      <h4>Сброс пароля пользователю</h4>
      <div class="form-check form-switch mb-2">
        <label class="form-check-label"><i class="fa-solid fa-lock"></i></label>
        <input class="form-check-input" type="checkbox" role="switch" onclick="activate.form(this, 'reset_user_password')" checked>
      </div>
      <form method="post" id="reset_user_password">
        <input type="hidden" name="id" value="reset_user_password">
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="fa-solid fa-at fa-fw"></i></span>
          <input type="text" name="user_login" class="form-control" placeholder="Логин пользователя" aria-label="Логин пользователя" pattern="<?=$pattern_login?>" data-bs-toggle="tooltip" data-bs-title="Допустимо использование больших и маленьких английских букв, цифр и знаков '_', '@', '.'" required disabled>
        </div>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="fa-solid fa-fingerprint fa-fw"></i></span>
          <input type="password" name="user_new_password" class="form-control" placeholder="Новый пароль" aria-label="Новый пароль" pattern="<?=$pattern_password?>" autocomplete="new-password" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required disabled>
        </div>
        <input type="submit" class="btn btn-outline-primary" value="Сбросить" disabled>
      </form>
    </div>

  <?php endif; ?>

  <?php if ($authorization == "group_manager") : ?>

    <div id="group_management_layout" class="layout d-none">
      <div class="row align-items-center">
        <div class="col-12 col-md-6">
          <h1 class="mb-0">
            <span>Мои группы</span>
            <span class="btn btn-outline-secondary mb-1" title="Быстрый переход к меню студентов" onclick="navigation.viewLayout('student_list_layout')"><i class="fa-solid fa-shuffle"></i></span>
            <span class="btn btn-outline-secondary mb-1" title="Быстрый переход к планировщику" onclick="navigation.viewLayout('duty_planning_layout')"><i class="fa-solid fa-shuffle"></i></span>
        </div>
        <div class="col-12 col-md-6 text-start text-md-end">
          <span class="btn btn-outline-primary" title="Добавить группу" data-bs-target="#addGroupSwithModal" data-bs-toggle="modal"><i class="fa-solid fa-plus"></i></span>
        </div>
      </div>

      <div id="group_is_empty" class="mt-2 d-none">
        <h5>Вы пока не управляете ни одной группой. </h5>
        <button class="btn btn-outline-primary" title="Добавить группу" data-bs-target="#addGroupSwithModal" data-bs-toggle="modal">Добавить группу</button>
      </div>
      
      <div id="list_of_managed_groups">
        <!-- Элементы будут добавлены при помощи JavaScript -->
        <void class="placeholder col-12"></void>
      </div>
      <template id="list_of_managed_groups_template">
        <div class="list-group mt-2 mb-2">
          <h3 class="list-group-item position-relative mb-0">
            <abbr id="template_group_role" data-bs-toggle="tooltip"></abbr>
            <span>
              <span id="template_group_title" class="ms-1"><void class="placeholder col-12"></void></span>
              <h5 id="template_favorite_group" class="favorite_group fa-regular fa-heart" title="Группа по умолчанию" data-is-fixed="false"></h5>
            </span>
          </h3>
          <div id="template_group_about" class="list-group-item" title="Изменить группу" data-bs-toggle="modal" data-bs-target="#editGroupDataModal"><void class="placeholder col-12"></void></div>
          <div class="list-group-item d-grid">
            <button id="template_get_group_management" class="btn btn-outline-success mb-1">Управление группой</button>
            <button id="template_get_group_planning" class="btn btn-outline-success">Планировщик группы</button>
          </div>
          <div class="list-group-item text-center text-sm-start">
            <span id="template_edit_group" class="col btn btn-outline-primary me-1 mb-1" title="Изменить группу" data-bs-toggle="modal" data-bs-target="#editGroupDataModal"><i class="fa-solid fa-file-pen"></i></span>
            <span id="template_delete_group" class="col btn btn-outline-danger mb-1" title="Удалить группу" data-bs-toggle="modal" data-bs-target="#deleteGroupModal"><i class="fa-solid fa-trash-can"></i></span>
            <span id="template_share_group" class="col btn btn-outline-primary ms-1 mb-1" title="Поделиться доступом к группе" data-bs-toggle="modal" data-bs-target="#resetGroupKeyModal"><i class="fa-solid fa-users-gear"></i></span>
          </div>
        </div>
      </template>
      
      <div class="modal fade" id="addGroupSwithModal" aria-hidden="true" aria-labelledby="addGroupSwithModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Добавление группы</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="d-grid gap-2 col-10 mx-auto">
                <div class="progress mb-2" role="progressbar" aria-label="progressbar_groups">
                  <div id="progressbar_groups" class="progress-bar" data-bs-toggle="tooltip" data-bs-title="Лимит групп"></div>
                </div>
                <button class="btn btn-outline-primary" data-bs-target="#addNewGroupModal" data-bs-toggle="modal">Создать новую</button>
                <button class="btn btn-outline-primary" data-bs-target="#addAnExistingGroupModal" data-bs-toggle="modal">У меня есть ключ</button>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="addNewGroupModal" aria-hidden="true" aria-labelledby="addNewGroupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Создание новой группы</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="add_new_group">
              <div class="modal-body">
                <input type="hidden" name="id" value="add_new_group">
                <div class="form-floating mb-2">
                  <input type="text" name="group_new_name" id="group_new_name_input" class="form-control" placeholder="Название группы" pattern="<?=$pattern_name?>" data-bs-toggle="tooltip" data-bs-title="Строка от 3 до 128 символов" required>
                  <label>Название группы</label>
                </div>
                <div class="form-floating">
                  <textarea name="group_new_about" id="group_new_about_input" class="form-control input-note" placeholder="Описание группы" style="height: 100px" pattern="<?=$pattern_about?>"></textarea>
                  <label>Описание группы (включено в отчет)</label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <a class="btn btn-secondary" title="По ключу" data-bs-target="#addAnExistingGroupModal" data-bs-toggle="modal"><i class="fa-solid fa-shuffle"></i></a>
                <input type="submit" class="btn btn-primary" value="Создать">
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="modal fade" id="addAnExistingGroupModal" aria-hidden="true" aria-labelledby="addAnExistingGroupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Добавить существующую группу</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="add_an_existing_group" autocomplete="disabled">
              <div class="modal-body">
                <input type="hidden" name="id" value="add_an_existing_group">
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                  <input type="password" name="add_key" class="form-control" placeholder="Ключ группы" aria-label="Ключ группы" pattern="<?=$pattern_password?>" autocomplete="off" data-bs-toggle="tooltip" data-bs-title="Строка от 8 до 64 символов" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <a class="btn btn-secondary" title="Создание" data-bs-target="#addNewGroupModal" data-bs-toggle="modal"><i class="fa-solid fa-shuffle"></i></a>
                <input type="submit"  class="btn btn-primary"value="Добавить">
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="editGroupDataModal" aria-hidden="true" aria-labelledby="editGroupDataModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Редактирование группы <span id="title_edit_group_data"><void class="placeholder col-12"></void></span></h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="edit_group_data">
              <div class="modal-body">
                <input type="hidden" name="id" value="edit_group_data">
                <input type="hidden" name="group_id">
                <div class="form-floating mb-2">
                  <input type="text" name="group_new_name" class="form-control" placeholder="Название группы" pattern="<?=$pattern_name?>" data-bs-toggle="tooltip" data-bs-title="Строка от 3 до 128 символов" required>
                  <label>Название группы</label>
                </div>
                <div class="input-group">
                  <textarea name="group_new_about" class="form-control input-note" placeholder="Описание группы" style="height: 100px" pattern="<?=$pattern_about?>"></textarea>
                </div>
                <span class="form-text">Описание группы (включено в отчет)</span>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteGroupModal">Удалить</button>
                <input type="submit" class="btn btn-primary" value="Изменить">
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Удаление группы</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <span>Подтвердите удаление</span>
              <b id="title_delete_group"><void class="placeholder col-12"></void></b>
              <span id="title_delete_group_status"><void class="placeholder col-12"></void></span>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              <form method="post" id="delete_group">
                <input type="hidden" name="id" value="delete_group">
                <input type="text" class="d-none" name="group_id" pattern="<?=$pattern_id?>" required>
                <input type="submit" class="btn btn-danger" data-bs-dismiss="modal" value="Удалить">
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="resetGroupKeyModal" tabindex="-1" aria-labelledby="resetGroupKeyModal" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5">Ключ управления для <span id="title_reset_group_key"><void class="placeholder col-12"></void></span></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <label class="form-label">Закрытый ключ для совместного управления группой:</label>
                <div class="input-group">
                  <input type="password" id="input_share_group_key" class="form-control" autocomplete="off">
                  <button type="button" class="btn btn-primary" onclick="copyToClipboard('input_share_group_key');"><i class="fa-solid fa-copy"></i></button>
                </div>
                <label class="form-text">
                  Если вы его сбросите, то все активные менеджеры потеряют к ней доступ
                </label>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form method="post" id="reset_group_key">
                  <input type="hidden" name="id" value="reset_group_key">
                  <input type="text" class="d-none" name="group_id" pattern="<?=$pattern_id?>" required>
                  <input type="submit" class="btn btn-outline-danger" value="Сбросить ключ">
                </form>
              </div>
            </div>
          </div>
        </div>

    </div>

    <div id="student_list_layout" class="layout d-none">
      <div class="row align-items-center">
        <div class="col-12 col-md-6">
          <h1 class="mb-0">
            <span id="title_group_manager_layout"><void class="placeholder col-12"></void></span>
            <span class="btn btn-outline-secondary mb-1" title="Быстрый переход к меню групп" onclick="navigation.viewLayout('group_management_layout')"><i class="fa-solid fa-shuffle"></i></span>
            <span class="btn btn-outline-secondary mb-1" title="Быстрый переход к планировщику" onclick="navigation.viewLayout('duty_planning_layout')"><i class="fa-solid fa-shuffle"></i></span>
          </h1>
        </div>
        <div id="group_bottom_menu" class="col-12 col-md-6 text-start text-md-end d-none">
          <button class="btn btn-outline-primary" type="button" title="Ссылка на группу" data-bs-toggle="modal" data-bs-target="#groupLinkModal"><i class="fa-solid fa-link"></i></button>
          <button class="btn btn-outline-primary" type="button" title="Сбросить цикл дежурств" data-bs-toggle="modal" data-bs-target="#reloadStudentStatusesModal"><i class="fa-solid fa-retweet"></i></button>
          <button class="btn btn-outline-primary" type="button" title="Построить отчет" data-bs-toggle="modal" data-bs-target="#createGroupReportModal"><i class="fa-solid fa-calendar-plus"></i></button>
          <button id="view_edit_students" class="btn btn-outline-primary" title="Режим редактирования студентов" type="button" edit-mode="disabled" onclick="viewEditStudents(this)"><i class="fa-solid fa-user-pen"></i></button>
          <button class="btn btn-outline-primary" type="button" title="Добавить студента" data-bs-toggle="modal" data-bs-target="#addNewStudentModal"><i class="fa-solid fa-user-plus"></i></button>
        </div>
      </div>

      <div id="student_management" class="d-none">
        <div class="group_managers">
          <span>Группой управляют:</span>
          <span id="group_managers_list">
            <!-- Элементы будут добавлены при помощи JavaScript -->
            <void class="placeholder col-12"></void>
          </span>
          <template id="group_managers_list_template">
            <abbr id="template_name" class="text-end" data-bs-toggle="tooltip"><void class="placeholder col-12"></void></abbr>
          </template>
        </div>

        <div id="student_is_empty" class="mt-2 mb-2 d-none">
          <h5>Вы пока не добавили ни одного студента. </h5>
          <button class="btn btn-outline-primary" type="button" title="Добавить студентов" data-bs-toggle="modal" data-bs-target="#addNewStudentsModal">Добавить студентов</button>
        </div>

        <table class="table">
          <tr>
            <th>
              <h3 class="mt-4 mb-0">Еще не дежурили</h3>
            </th>
            <th></th>
            <th class="delete_student d-none"></th>
            <th class="edit_student d-none"></th>
          </tr>
          <tr>
            <tbody id="active_list">
              <!-- Элементы будут добавлены при помощи JavaScript -->
              <td><void class="placeholder col-12"></void></td>
            </tbody>
          </tr>

          <tr>
            <th>
              <h3 class="mt-4 mb-0">Назначены на сегодня</h3>
            </th>
            <th></th>
            <th class="delete_student d-none"></th>
            <th class="edit_student d-none"></th>
          </tr>
          <tr>
            <tbody id="actual_list">
              <!-- Элементы будут добавлены при помощи JavaScript -->
              <td><void class="placeholder col-12"></void></td>
            </tbody>
          </tr>
          
          <tr>
            <th>
              <h3 class="mt-4 mb-0">Уже отдежурили</h3>
            </th>
            <th></th>
            <th class="delete_student d-none"></th>
            <th class="edit_student d-none"></th>
          </tr>
          <tr>
            <tbody id="made_list">
              <!-- Элементы будут добавлены при помощи JavaScript -->
              <td><void class="placeholder col-12"></void></td>
            </tbody>
          </tr>
          
          <tr>
            <th>
              <h3 class="mt-4 mb-0">Освобождены от дежурства</h3>
            </th>
            <th></th>
            <th class="delete_student d-none"></th>
            <th class="edit_student d-none"></th>
          </tr>
          <tr>
            <tbody id="other_list">
              <!-- Элементы будут добавлены при помощи JavaScript -->
              <td><void class="placeholder col-12"></void></td>
            </tbody>
          </tr>
        </table>

        <template id="item_list_template">
          <tr class="item_list">
            <td class="student_name d-flex">
              <span><i id="template_planning" data-bs-toggle="tooltip"></i></span>
              <span id="template_name" class="flex-grow-1" title="Редактирование студента" data-bs-toggle="modal" data-bs-target="#editStudentDataModal"><void class="placeholder col-12"></void></span>
              <abbr id="template_count" class="text-end ms-1" data-bs-toggle="tooltip" data-bs-title="Раз должен дежурить"><void class="placeholder col-12"></void></abbr>
            </td>
            <td class="text-begin">
              <select name="template_status_select_min" class="d-sm-none" title="Управление статусом">
                <option value="0">Не</option>
                <option value="1">Назн</option>
                <option value="2">Отд</option>
                <option value="3">Осв</option>
              </select>
              <select name="template_status_select_max" class="d-none d-sm-inline" title="Управление статусом">
                <option value="0">Не дежурил</option>
                <option value="1">Назначен</option>
                <option value="2">Отдежурил</option>
                <option value="3">Освобожден</option>
              </select>
            </td>
            <td id="template_delete_student" class="delete_student text-primary text-center d-none" data-bs-toggle="modal" data-bs-target="#deleteStudentModal">
              <i type="button" title="Удаление студента" class="fa-regular fa-trash-can"></i>
            </td>
            <td id="template_edit_student" class="edit_student text-primary text-center d-none" data-bs-toggle="modal" data-bs-target="#editStudentDataModal">
              <i type="button" title="Редактирование студента" class="fa-solid fa-pen"></i>
            </td>
          </tr>
        </template>
      </div>
      
      <div class="modal fade" id="groupLinkModal" tabindex="-1" aria-labelledby="groupLinkModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Публичная ссылка</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="input-group">
                <input type="text" id="group_link_input" class="form-control" autocomplete="off">
                <button type="button" class="btn btn-primary" onclick="copyToClipboard('group_link_input');"><i class="fa-solid fa-copy"></i></button>
              </div>
              <label class="form-text">Публичная ссылка для отображения списка актуальных дежурных</label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="addNewStudentsModal" tabindex="-1" aria-labelledby="addNewStudentsModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Массовое добавление студентов</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <div class="modal-body">
                <div class="input-group">
                  <textarea id="add_some_students" class="form-control" placeholder="Список студентов" style="height: 100px" pattern="<?=$pattern_about?>"></textarea>
                </div>
                <span class="form-text">Введите имена студентов через запятую или с новой строки, чтобы добавить сразу весь список</span>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="addSomeStudents();">Добавить всех</button>
              </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="addNewStudentModal" tabindex="-1" aria-labelledby="addNewStudentModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Добавление студента <span id="title_add_new_student"></span></h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="add_new_student">
              <div class="modal-body">
                <input type="hidden" name="id" value="add_new_student">
                <input type="text" name="group_id" class="d-none" pattern="<?=$pattern_id?>" required>
                <div class="progress mb-2" role="progressbar" aria-label="progressbar_students">
                  <div id="progressbar_students" class="progress-bar" data-bs-toggle="tooltip" data-bs-title="Лимит студентов"></div>
                </div>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-graduation-cap"></i></span>
                  <input type="text" name="name" id="add_new_student_name_input" class="form-control" placeholder="Имя студента" aria-label="Имя студента" pattern="<?=$pattern_name?>" data-bs-toggle="tooltip" autocomplete="name" data-bs-title="Строка от 3 до 128 символов" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <input type="submit" class="btn btn-primary" value="Добавить">
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Удаление студента из <span id="title_delete_student"><void class="placeholder col-12"></void></span></h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <span>Подтвердите удаление студента</span>
              <b id="delete_student_message"><void class="placeholder col-12"></void></b>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              <form method="post" id="delete_student">
                <input type="hidden" name="id" value="delete_student">
                <input type="text" class="d-none" name="group_id" pattern="<?=$pattern_id?>" required>
                <input type="text" class="d-none" name="student_id" pattern="<?=$pattern_id?>" required>
                <input type="submit" class="btn btn-danger" data-bs-dismiss="modal" value="Удалить">
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="editStudentDataModal" tabindex="-1" aria-labelledby="editStudentDataModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Редактирование студента из <span id="title_edit_student_data"><void class="placeholder col-12"></void></span></h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="edit_student_data">
              <div class="modal-body">
                <input type="hidden" name="id" value="edit_student_data">
                <input type="text" name="group_id" class="d-none" pattern="<?=$pattern_id?>" required>
                <input type="text" name="student_id" class="d-none" pattern="<?=$pattern_id?>" required>
                <div class="input-group mb-1">
                  <span class="input-group-text"><i class="fa-solid fa-graduation-cap"></i></span>
                  <input type="text" name="student_new_name" class="form-control" placeholder="Имя студента" aria-label="Имя студента" pattern="<?=$pattern_name?>" data-bs-toggle="tooltip" data-bs-title="Строка от 3 до 128 символов" required>
                </div>
                <div class="input-group">
                  <span class="input-group-text" id="basic-addon2">Счетчик кол-ва дежурств:</span>
                  <input type="text" name="student_new_duty_count" class="form-control" placeholder="кол-во" aria-label="Счетчик кол-ва дежурств" pattern="<?=$pattern_id?>" data-bs-toggle="tooltip" data-bs-title="Число" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteStudentModal">Удалить</button>
                <input type="submit" class="btn btn-primary" value="Изменить">
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="reloadStudentStatusesModal" tabindex="-1" aria-labelledby="reloadStudentStatusesModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Сбросить текущий цикл дежурств</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <span>Вы действительно хотите сбросить текущий цикл дежурств (все текущие активные статусы и стчетчики, кроме освобожденных) для группы <b id="title_reload_student_statuses"><void class="placeholder col-12"></void></b>?</span>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              <form method="post" id="reload_student_statuses">
                <input type="hidden" name="id" value="reload_student_statuses">
                <input type="text" name="group_id" class="d-none" pattern="<?=$pattern_id?>" required>
                <input type="submit" class="btn btn-danger" data-bs-dismiss="modal" value="Сбросить">
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="createGroupReportModal" tabindex="-1" aria-labelledby="createGroupReportModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Построить отчет для <span id="title_create_group_report"><void class="placeholder col-12"></void></span></h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="create_group_report">
              <div class="modal-body">
              <input type="hidden" name="id" value="create_group_report">
              <input type="text" name="group_id" class="d-none" pattern="<?=$pattern_id?>" required>
                <div class="input-group mb-1">
                  <label class="input-group-text">Дата начала</label>
                  <input type="date" name="start_date" class="form-control" aria-label="start_date" required>
                </div>
                <div class="input-group">
                  <input type="date" name="end_date" class="form-control" aria-label="end_date" required>
                  <label class="input-group-text">Дата конца</label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <input type="submit" class="btn btn-primary" value="Создать">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div id="duty_planning_layout" class="layout d-none">
      <div class="row align-items-center">
        <div class="col-12 col-md-6">
          <h1 class="mb-0">
            <span id="title_duty_planning_layout"><void class="placeholder col-12"></void></span>
            <span class="btn btn-outline-secondary mb-1" title="Быстрый переход к меню групп" onclick="navigation.viewLayout('group_management_layout')"><i class="fa-solid fa-shuffle"></i></span>
            <span class="btn btn-outline-secondary mb-1" title="Быстрый переход к меню студентов" onclick="navigation.viewLayout('student_list_layout')"><i class="fa-solid fa-shuffle"></i></span>
        </div>
        <div class="col-12 col-md-6 text-start text-md-end">
          <span class="btn btn-outline-primary" title="Запланировать дежурство" onclick="editPlanningModal('clear');" data-bs-target="#addPlanningStudentDutyModal" data-bs-toggle="modal"><i class="fa-solid fa-plus"></i></span>
        </div>
      </div>

      <div id="duty_planning_layout_table" class="d-none">
        <div class="input-group mt-2 mb-1" role="group" aria-label="First group">
          <button type="button" class="btn btn-outline-secondary" onclick="addMonths(-1)">
            <span class="d-sm-none"><</span>
            <span class="d-none d-sm-flex">Назад</span>
          </button>
          <span id="current_month_page" class="input-group-text flex-grow-1 justify-content-center"><void class="placeholder col-12"></void></span>
          <button type="button" class="btn btn-outline-secondary" onclick="addMonths(+1)">
            <span class="d-sm-none">></span>
            <span class="d-none d-sm-flex">Вперед</span>
          </button>
        </div>
        <table class="table table-bordered">
          <thead>
            <tr class="text-center">
              <th>ПН</th>
              <th>ВТ</th>
              <th>СР</th>
              <th>ЧТ</th>
              <th>ПТ</th>
              <th>СБ</th>
              <th>ВС</th>
            </tr>
          </thead>
          <tbody id="planning_list">
            <!-- Элементы будут добавлены при помощи JavaScript -->
            <tr>
              <td><void class="placeholder col-12"></void></td>
              <td><void class="placeholder col-12"></void></td>
              <td><void class="placeholder col-12"></void></td>
              <td><void class="placeholder col-12"></void></td>
              <td><void class="placeholder col-12"></void></td>
              <td><void class="placeholder col-12"></void></td>
              <td><void class="placeholder col-12"></void></td>
            </tr>
          </tbody>
        </table>
        <template id="planning_list_template">
          <tr id="template_week" class="text-center">
            <td id="template_monday"><void class="placeholder col-12"></void></td>
            <td id="template_tuesday"><void class="placeholder col-12"></void></td>
            <td id="template_wednesday"><void class="placeholder col-12"></void></td>
            <td id="template_thursday"><void class="placeholder col-12"></void></td>
            <td id="template_friday"><void class="placeholder col-12"></void></td>
            <td id="template_saturday"><void class="placeholder col-12"></void></td>
            <td id="template_sunday"><void class="placeholder col-12"></void></td>
          </tr>
        </template>
        
        <div id="duty_planning_is_empty" class="mt-2 d-none">
          <p>Вы пока не запланировали ни одного дежурства на этот месяц.</p>
        </div>
        
        <button id="go_to_current_month" class="btn btn-outline-primary d-none" onclick="goToCurrentMonth();">Текущий месяц</button>
      </div>
    </div>

    <div class="modal fade" id="addPlanningStudentDutyModal" tabindex="-1" aria-labelledby="addPlanningStudentDutyModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5">Назначение студента из <span id="title_add_student_duty"><void class="placeholder col-12"></void></span> на дежурство</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" id="add_planning_student_duty">
            <div class="modal-body">
              <input type="hidden" name="id" value="add_planning_student_duty">
              <input type="text" name="group_id" class="d-none" pattern="<?=$pattern_id?>" required>
              <div id="add_planning_student_duty_message" class="alert mb-1 p-2"><void class="placeholder col-12"></void></div>
              <div class="input-group mb-1">
                <label class="input-group-text">Студент:</label>
                <div class="form-floating">
                  <select id="student_list_for_planning" name="student_id" class="form-select" title="Выбрать студента" required>
                    <option></option>
                  </select>
                  <label>Выберите студента из списка:</label>
                </div>
              </div>
              <div class="input-group">
                <label class="input-group-text">Дата дежурства:</label>
                <input type="date" id="duty_date_for_planning" name="duty_date" class="form-control" aria-label="duty_date" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              <input type="submit" class="btn btn-primary" value="Назначить">
            </div>
          </form>
        </div>
      </div>
    </div>

  <?php endif; ?>

  <div class="script d-none">
    <script type="text/javascript" src="https://cdn.eclabs.ru/bootstrap/5.3.1/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.eclabs.ru/bootstrap/5.2.3/js/dist/popover.js"></script>
    <script type="text/javascript" src="https://cdn.eclabs.ru/jquery/jquery-3.3.1.min.js"></script>
    
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script> -->
    <!-- <script src="https://kit.fontawesome.com/e85b9260df.js" crossorigin="anonymous"></script> -->

    <!-- <script type="text/javascript" src="local/jquery-3.7.0.min.js"></script> -->
    <script type="text/javascript" src="js/config.js"></script>
    <script type="text/javascript" src="js/navigation.js"></script>
    <script type="text/javascript" src="js/function.js"></script>
    <script type="text/javascript" src="js/ajax-function.js"></script>
    <script type="text/javascript" src="js/app-front.js"></script>
  </div>
  
</body>

</html>

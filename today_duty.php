<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">

  <title>Журнал</title>
  <link type="image/x-icon" href="image/favicon.ico" rel="shortcut icon">

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

<body>
  <div class="container placeholder-glow">

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
      <div id="message" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div id="data_message" class="toast-body placeholder-glow">
          <void class="placeholder col-6"></void>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
      <div id="error_message" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div id="data_error_message" class="toast-body placeholder-glow">
          <void class="placeholder col-6"></void>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

    <h1 id="group_title" class="mt-3"><void class="placeholder col-6"></void></h1>
    <div id="duty_list">
      <!-- Элементы будут добавлены с помощью JavaScript -->
      <void class="placeholder col-12"></void>
    </div>
    <template id="duty_list_template">
      <h4 id="template_duty_student_title" class="mt-2"><void class="placeholder col-12"></void></h4>
      <ul id="template_today_duty_students" class="list-group mb-3">
        <!-- Элементы будут добавлены с помощью JavaScript -->
        <void class="placeholder col-12"></void>
      </ul>
    </template>
    <template id="today_duty_students_template">
      <li id="template_li" class="list-group-item d-flex align-items-center">
        <span class="rounded-pill me-3">
          <i class="fa-solid fa-broom"></i>
        </span>
        <span id="template_name"><void class="placeholder col-12"></void></span>
      </li>
    </template>

    <div class="form-switch mt-1" title="Цветовой режим">
      <input class="form-check-input" id="color_mode_checkbox" type="checkbox" role="switch" onclick="colorMode()">
      <label class="form-check-label"><i class="fa-solid fa-moon"></i></label>
    </div>
  </div>

  <div class="script d-none">
    <script type="text/javascript" src="https://cdn.eclabs.ru/bootstrap/5.3.1/js/bootstrap.bundle.min.js"></script>
    <!-- <script type="text/javascript" src="https://cdn.eclabs.ru/bootstrap/5.2.3/js/dist/popover.js"></script> -->
    <script type="text/javascript" src="https://cdn.eclabs.ru/jquery/jquery-3.3.1.min.js"></script>
    
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script> -->
    <!-- <script src="https://kit.fontawesome.com/e85b9260df.js" crossorigin="anonymous"></script> -->

    <!-- <script type="text/javascript" src="local/jquery-3.7.0.min.js"></script> -->
    <script type="text/javascript" src="js/config.js"></script>
    <script type="text/javascript" src="js/navigation.js"></script>
    <script type="text/javascript" src="js/function.js"></script>
    <script type="text/javascript" src="js/ajax-function.js"></script>
    <script type="text/javascript" src="js/duty-front.js"></script>
  </div>

</body>
</html>
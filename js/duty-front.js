// список дежурящих сейчас студентов
function post_list_duty_students(dutyData) {
  log.i("post_list_duty_students", "called");

  // Найдем элементы, куда необходимо поместить информацию
  const groupTitleElement = document.getElementById('group_title');
  groupTitleElement.textContent = dutyData.group;

  // формируем новый список для вывода
  // Создаем объект, в который будем группировать данные по датам
  const groupedData = {};

  groupedData[getCurrentDate()] = {
    name: "Сегодня дежурят:",
    students: []
  };

  // Проходим по students и добавляем их в группированный объект
  dutyData.students.forEach(student => {
    groupedData[getCurrentDate()].students.push({
        duty_date: getCurrentDate(),
        name: student.name
      });
  });
  
  // Проходим по элементам planning и группируем их по датам
  dutyData.planning.forEach((item) => {
    const date = item.duty_date;

    // если сегодня - скипаем планировщика
    if (date === getCurrentDate()) {
      return;
    }

    if (!groupedData[date]) {
      // Если дата еще не существует в объекте groupedData, создаем новый элемент
      groupedData[date] = {
        name: `${date === getTomorrowDate() ? 'Завтра' : formatDate(date, "d months")} дежурят:`,
        students: []
      };
    }

    groupedData[date].students.push(item);
  });
  
  // Преобразуем объект groupedData в массив
  const groupedDutyData = Object.values(groupedData);
  // log.w(groupedDutyData);

  // создаем новые шаблоны
  const duty_list = document.getElementById("duty_list");
  const duty_list_template = document.getElementById("duty_list_template");
  const today_duty_students_template = document.getElementById('today_duty_students_template');

  // чистим
  duty_list.innerHTML = null;

  // заполняем новые данные
  groupedDutyData.forEach(function(dutyItem) {
    const template = document.importNode(duty_list_template.content, true);

    // заполняем название
    const template_duty_student_title = template.getElementById("template_duty_student_title");
    template_duty_student_title.textContent = dutyItem.name;

    // получаем контейнер для студентов
    const template_today_duty_students = template.getElementById('template_today_duty_students');

    // чистим
    template_today_duty_students.innerHTML = null;

    // Создадим элементы 
    dutyItem.students.forEach(function(student) {
      // создаем копию
      const template = document.importNode(today_duty_students_template.content, true);

      // подсвечиваем сегодня
      const template_li = template.getElementById("template_li");
      if (dutyItem.name == "Сегодня дежурят:") {
        template_li.classList.add("list-group-item-primary");
      }

      // заполняем
      const studentDiv = template.getElementById("template_name");
      studentDiv.textContent = student.name;

      // Добавляем созданный шаблон в контейнер
      template_today_duty_students.appendChild(template);
    });

    duty_list.appendChild(template);
  });
}

// получить сам список для дежурства
function getСurrentDutyStudents(group_id) {
  log.i("getСurrentDutyStudents", "called");

  var clientData = `id=get_current_duty_students&group_id=${group_id}`;
  ajaxQuery("get_current_duty_students", clientData);
}

// получить айди и загрузить дежурящих
function loadDutyList(group_id) {
  log.i("countInterval", countInterval);
  log.i("loadDutyList", "called");
  countInterval++;
  getСurrentDutyStudents(group_id);
}

// загрузка всего
window.addEventListener('load', function() {
  log.i("onLoad", "loaded");

  // группу создать
  const queryParams = parseQueryString(window.location.search);
  const group_id = queryParams['group_id'];

  // проверка GET
  if (!isDbInt(group_id)) {
    log.w("getСurrentDutyStudents", "group_id not isDbInt");
    document.getElementById("group_title").textContent = "Группа не выбрана";
    document.getElementById("duty_list").textContent = null;
    document.getElementById("duty_student_title").textContent = null;
    document.getElementById("today_duty_students").textContent = null;
    callToast("error", "Айди некорректен");
    return false;
  }

  // таймер
  var loaderTimer = setInterval(function() {
    if (isTabActive()) {
      loadDutyList(group_id);
    }
  }, intervalDuty);

  // Если вкладка становится активной, вызываем loadDutyList()
  document.addEventListener('visibilitychange', function() {
    if (isTabActive()) {
      loadDutyList(group_id);
    }
  });

  // Опционально: Остановить таймер при закрытии/перезагрузке страницы
  window.addEventListener('beforeunload', function() {
    clearInterval(loaderTimer);
  });

  loadDutyList(group_id);
});
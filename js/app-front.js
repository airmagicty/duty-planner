// заполнить данные пользователя
function editMyInfo(user=userBuffer.data.user_data) {
  log.i("editMyInfo", "called")

  const auth_title_p = document.getElementById("auth_title_p");
  auth_title_p.textContent = `${user.name} (${user.login})`;

  const auth_title_i = document.getElementById("auth_title_i");
  switch (user.role) {

    case "group_manager":
      auth_title_i.className = "fa-solid fa-list-check fa-fw";
      break

    case "system_admin":
      auth_title_i.className = "fa-solid fa-screwdriver-wrench fa-fw";
      break;

    default:
      auth_title_i.className = "fa-solid fa-question fa-fw";
  }

  const auth_title_offcanvas = document.getElementById("auth_title_offcanvas");
  auth_title_offcanvas.textContent = `${user.name}`;

  const edit_my_data = document.getElementById("edit_my_data");
    const edit_my_data_my_id = edit_my_data.querySelector('input[name="my_id"]');
    edit_my_data_my_id.value = user.id;

    const my_new_name = edit_my_data.querySelector('input[name="my_new_name"]');
    my_new_name.value = user.name;

    const my_new_about = edit_my_data.querySelector('textarea[name="my_new_about"]');
    my_new_about.value = user.about;
  

  const edit_my_login = document.getElementById('edit_my_login');
    const edit_my_login_my_id = edit_my_login.querySelector('input[name="my_id"]');
    edit_my_login_my_id.value = user.id;

    const my_new_login = edit_my_login.querySelector('input[name="my_new_login"]');
    my_new_login.value = user.login;

  const edit_my_password = document.getElementById('edit_my_password');
    const edit_my_password_my_id = edit_my_password.querySelector('input[name="my_id"]');
    edit_my_password_my_id.value = user.id;
  
  const delete_my_account = document.getElementById('delete_my_account');
  if (delete_my_account) {
    const delete_my_account_my_id = delete_my_account.querySelector('input[name="my_id"]');
    delete_my_account_my_id.value = user.id;
  }
}

// изменение группы
function editGroup(group=activeGroup) {
  log.i("editGroup", "called");

  const edit_group_data = document.getElementById('edit_group_data');
    const group_id = edit_group_data.querySelector('input[name="group_id"]');
    group_id.value = group.id;

    const group_new_name = edit_group_data.querySelector('input[name="group_new_name"]');
    group_new_name.value = group.name;

    const group_new_about = edit_group_data.querySelector('textarea[name="group_new_about"]');
    group_new_about.value = group.about;

  const title_edit_group_data = document.getElementById('title_edit_group_data');
  title_edit_group_data.textContent = `${group.name}`;

  deleteGroup(group);
}

// делиться группой
function shareGroup(group=activeGroup) {
  log.i("shareGroup", "called");

  const input_share_group_key = document.getElementById("input_share_group_key");
  input_share_group_key.value = group.link;

  const reset_group_key = document.getElementById('reset_group_key');
    const group_id = reset_group_key.querySelector('input[name="group_id"]');
    group_id.value = group.id;
  
  
  const title_reset_group_key = document.getElementById("title_reset_group_key");
    title_reset_group_key.textContent = `${group.name}`;
}

// удаление группы
function deleteGroup(group=activeGroup) {
  log.i("deleteGroup", "called");

  const delete_group = document.getElementById('delete_group');
    const group_id = delete_group.querySelector('input[name="group_id"]');
    group_id.value = group.id;
    
  const title_delete_group = document.getElementById('title_delete_group');
    title_delete_group.textContent = `${group.name}`;

  const title_delete_group_status = document.getElementById('title_delete_group_status');
    switch (group.role) {
      case "admin":
        title_delete_group_status.textContent = "и всех её студентов"
        break;

      case "manager":
        title_delete_group_status.textContent = "из списка управляемых вами групп";
        break;

      default: 
        log.w("deleteGroup", "group.role default")
    }
}

// изменить сутедeнта
function editStudent(student) {
  log.i("editStudent", "called")

  const edit_student_data = document.getElementById('edit_student_data');
    const student_id = edit_student_data.querySelector('input[name="student_id"]');
    student_id.value = student.id;

    const student_new_name = edit_student_data.querySelector('input[name="student_new_name"]');
    student_new_name.value = student.name;

    const student_new_duty_count = edit_student_data.querySelector('input[name="student_new_duty_count"]');
    student_new_duty_count.value = student.duty_count;

    deleteStudent(student);
}

// удалить студента
function deleteStudent(student) {
  log.i("deleteStudent", "called");

  const delete_student = document.getElementById('delete_student');
    const student_id = delete_student.querySelector('input[name="student_id"]');
    student_id.value = student.id;  

  const delete_student_message = document.getElementById("delete_student_message");
  delete_student_message.textContent = `${student.name}`;
}

// добавить новго студента
function addNewStudent(group_id, name) {
  log.i("addNewStudent", `called(${group_id})(${name})`);

  const clientData = `id=add_new_student&group_id=${group_id}&name=${name}`;
  ajaxQuery("add_new_student", clientData);
}

// удалить студента
function deleteStudent(student) {
  log.i("deleteStudent", "called");

  const delete_student = document.getElementById('delete_student');
    const student_id = delete_student.querySelector('input[name="student_id"]');
    student_id.value = student.id;  

  const delete_student_message = document.getElementById("delete_student_message");
  delete_student_message.textContent = `${student.name}`;
}

// заполнить айди группы
function addManagedGroupId(group=activeGroup) {
  log.i("addManagedGroupId", "called")

  const title_add_new_student = document.getElementById("title_add_new_student");
  title_add_new_student.textContent = `${group.name}`;

  const add_new_student = document.getElementById('add_new_student');
    const add_new_student_group_id = add_new_student.querySelector('input[name="group_id"]');
    add_new_student_group_id.value = group.id;

  const title_delete_student = document.getElementById("title_delete_student");
  title_delete_student.textContent = `${group.name}`;

  const delete_student = document.getElementById('delete_student');
    const delete_student_group_id = delete_student.querySelector('input[name="group_id"]');
    delete_student_group_id.value = group.id;

  const title_edit_student_data = document.getElementById("title_edit_student_data");
  title_edit_student_data.textContent = `${group.name}`;

  const edit_student_data = document.getElementById('edit_student_data');
    const edit_student_data_group_id = edit_student_data.querySelector('input[name="group_id"]');
    edit_student_data_group_id.value = group.id;

  const title_reload_student_statuses = document.getElementById("title_reload_student_statuses");
  title_reload_student_statuses.textContent = `${group.name}`;

  const create_group_report = document.getElementById('create_group_report');
    const create_group_report_group_id = create_group_report.querySelector('input[name="group_id"]');
    create_group_report_group_id.value = group.id;

  const title_create_group_report = document.getElementById("title_create_group_report");
  title_create_group_report.textContent = `${group.name}`;

  const reload_student_statuses = document.getElementById('reload_student_statuses');
    const reload_student_statuses_group_id = reload_student_statuses.querySelector('input[name="group_id"]');
    reload_student_statuses_group_id.value = group.id;
  
  const title_duty_planning_layout = document.getElementById("title_duty_planning_layout");
  title_duty_planning_layout.textContent = `Планировщик для ${group.name}`;

  const title_add_student_duty = document.getElementById("title_add_student_duty");
  title_add_student_duty.textContent = `${group.name}`;

  const add_planning_student_duty = document.getElementById("add_planning_student_duty");
    const add_planning_student_duty_group_id = add_planning_student_duty.querySelector('input[name="group_id"]');
    add_planning_student_duty_group_id.value = group.id;

  const title_group_manager_layout = document.getElementById("title_group_manager_layout");
  title_group_manager_layout.textContent = `${group.name}`;

  const group_link_input = document.getElementById("group_link_input");
  group_link_input.value = `${pathToCurrentDuty}?group_id=${group.id}`;
}

function editPlanningModal(mode) {
  log.i("editPlanningModal", "called");
  
  const add_planning_student_duty = document.getElementById("add_planning_student_duty");
  const duty_date = add_planning_student_duty.querySelector('input[name="duty_date"]');
  // const student_id = add_planning_student_duty.querySelector('input[name="student_id"]');
  const duty_submit = add_planning_student_duty.querySelector('input[type="submit"]');
  // const duty_message = document.getElementById("add_planning_student_duty_message");
  
  switch (mode) {
    
    case "clear":
      duty_submit.removeAttribute('data-bs-dismiss');
      duty_date.value = null;
      break;
      
    case "student&date":
      duty_submit.setAttribute('data-bs-dismiss', 'modal');
      break;
      
    case "date":
      duty_submit.removeAttribute('data-bs-dismiss');
      break;
      
    default:
      log.w("editPlanningModal", "mode is default");
  }
    
    viewDutyMessage(document.getElementById("duty_date_for_planning").value);
}

// заполнение модалки с logMessage
function setCellLogMessage(logMessage) {
  log.i("setCellLogMessage", "called")

  const log_message = document.getElementById("log_message");
  log_message.innerHTML = formatLogMessage(logMessage);
}

// включение и отключение редактирования
function viewEditStudents(button) {
  log.i("viewEditStudents", "called");

  const editStudents = document.querySelectorAll('.edit_student, .delete_student');
  switch (button.getAttribute('edit-mode')) {

    case "disabled":
      button.setAttribute('edit-mode', 'enabled');
      editStudents.forEach((element) => {
        element.classList.remove('d-none')
      });

      break;

    case "enabled":
      button.setAttribute('edit-mode', 'disabled');
      editStudents.forEach((element) => {
        element.classList.add('d-none')
      });
      
      break;

    default:
      log.w("viewEditStudents", "button.value is default");
  }
}

// Создание нового документа для печати
function generatePrintableDocument(data) {
  log.i("generatePrintableDocument", "called");
  
  let printWindow = window.open('', '_blank');
  printWindow.document.open();

  // Содержимое документа
  let content = `
  <!DOCTYPE html>
  <html>
    <head>
    <title>Отчет для группы ${data.group.name}</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 1cm;
      }
      h2 {
        text-align: center;
      }
      p {
        text-align: justify;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      th, td {
        border: 1px solid black;
        padding: 5px;
        text-align: left;
      }
    </style>
    </head>
    <body>
    <h2>${data.group.name}</h2>
    <p>${data.group.about}</p>
    <table>
      <thead>
      <tr>
        <th>Дата</th>
        <th>Дежурный</th>
      </tr>
      </thead>
      <tbody>
  `;

  // Добавление данных отчетов в таблицу
  data.report.forEach((reportItem) => {
    content += `
      <tr>
        <td>${formatDate(reportItem.date, "dd.mm.yyyy")}</td>
        <td>${reportItem.students}</td>
      </tr>
    `;
  });
  
  // Завершение содержимого документа
  content += `
      </tbody>
    </table>
    </body>
  </html>
  `;

  // Запись содержимого в документ
  printWindow.document.write(content);
  printWindow.document.close();

  // Печать документа
  printWindow.print();
}

// ================== Пост ==================

// кто управляет группой
function post_list_managers(managersArray=managerBuffer.data.managers) {
  log.i("post_list_managers", "called");

  // Получаем ссылку на элемент с id="group_managers_list"
  const groupManagersElement = document.getElementById("group_managers_list");
  const group_managers_list_template = document.getElementById('group_managers_list_template');

  // Очищаем содержимое элемента
  groupManagersElement.innerHTML = null;

  if (managersArray.length === 0) {
    groupManagersElement.textContent = "только вы";
    return false;
  }

  // Циклом перебираем массив и создаем каждый элемент и добавляем его в список
  managersArray.forEach(function(manager) {
    // создаем копию
    const template = document.importNode(group_managers_list_template.content, true);

    // заполняем ее
    const nameDiv = template.getElementById("template_name");

    let text_role = "?";
    switch (manager.role) {
      case "admin":
        text_role = "Создатель";
        break;

      case "manager":
        text_role = "Управляющий";
        break;

      default:
        log.w("post_list_managers", "manager.role default");
    }

    nameDiv.setAttribute("data-bs-title", `${text_role} (${manager.login})`)
    nameDiv.textContent = `${manager.name}`;
    
    // добавляем
    groupManagersElement.appendChild(template);
  });

  // для подсветки
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

// планировщик
function post_planning_list(planningList=planningBuffer.data.planning) {
  log.i("post_planning_list", "called");

  // если планов нет - отобразить предложение их добавить
  const duty_planning_is_empty = document.getElementById("duty_planning_is_empty");
  planningList.planning.length === 0 
  ? duty_planning_is_empty.classList.remove("d-none") 
  : duty_planning_is_empty.classList.add("d-none");

  // Добавляем обработчик события на изменение значения
  const add_planning_student_duty_duty_date = document.getElementById("duty_date_for_planning");
  add_planning_student_duty_duty_date.addEventListener("change", function() {
    viewDutyMessage(this.value, planningList.planning);
  });

  // текущий месяц
  document.getElementById("current_month_page").textContent = planningList.date.text;

  // создание календарного массива
  const currentCalendar = {}; // Создаем объект currentCalendar с днями месяца
  const [year, month] = currentMonthAndYear.split('-'); // Разбиваем currentMonthAndYear на год и месяц
  const lastDay = new Date(year, month, 0).getDate(); // Получаем последний день текущего месяца

  // Создаем план для каждого дня
  for (let day = 1; day <= lastDay; day++) {
    const dayKey = day.toString().padStart(2, '0');
    const date = new Date(year, month - 1, day);

    currentCalendar[parseInt(dayKey)] = {
      year: year,
      month: month,
      day: dayKey,
      dayOfWeek: daysOfWeek[date.getDay()],
      plan: [],
    };

    // Добавляем планы для текущего дня
    planningList.planning.forEach((plan) => {
      const planDay = new Date(plan.duty_date).getDate().toString().padStart(2, '0');
      if (planDay === dayKey) {
        currentCalendar[dayKey] ? currentCalendar[dayKey].plan.push(plan) : false;
      }
    });
  }

  // формируем недели
  const days_length = Object.keys(currentCalendar).length;
  const weeks = Array();
  let week = Array();

  for (const dayKey in currentCalendar) {

    if (currentCalendar.hasOwnProperty(dayKey)) {
      const day = currentCalendar[dayKey];
  
      // Ваш код для обработки дня
      if (day.dayOfWeek != "Sunday" && dayKey != days_length) {
        week.push(day);
    
      } else {
        week.push(day);
        weeks.push(week);
        week = Array();
      }
    }
  } 

  // находим все константы
  const planning_list = document.getElementById('planning_list');
  const planning_list_template = document.getElementById('planning_list_template');

  // очищаем его
  planning_list.innerHTML = null;

  // Формирование HTML-структуры 
  weeks.forEach((week) => {

    // создаем копию
    const template = document.importNode(planning_list_template.content, true);

    // Заполняем
    daysOfWeek.forEach((item) => {
      const dayData = week.find((day) => day.dayOfWeek === item) || false;
      const template_day = template.getElementById(`template_${item.toLowerCase()}`);
      
      if (!dayData) {
        template_day.textContent = null;
        return;
      }

      const current_date_int = parseInt(`${currentYear}${currentMonth}${currentDay}`);
      const this_date_int = parseInt(`${dayData.year}${dayData.month}${dayData.day}`);

      if (current_date_int > this_date_int) {
        template_day.textContent = `${dayData.day}`
        template_day.classList.add("text-decoration-line-through");
        return;
      }

      const this_date = `${dayData.year}-${dayData.month}-${dayData.day}`;
      template_day.textContent = `${dayData.day}`
      template_day.classList.add("template_day", "text-danger");

      template_day.setAttribute("data-bs-toggle", "modal");
      template_day.setAttribute("data-bs-target", "#addPlanningStudentDutyModal");
      template_day.addEventListener("click", () => {
        add_planning_student_duty_duty_date.value = this_date;
        editPlanningModal("date");
        viewDutyMessage(add_planning_student_duty_duty_date.value, planningList.planning);
      });

      if (current_date_int == this_date_int) {
        template_day.classList.add("fw-bold");

        if (studentBuffer) {
          if (studentBuffer.data.students.filter((item) => item.duty_status == "1").length !== 0) {
            template_day.classList.remove("text-danger");
            template_day.classList.add("text-success");

          } else {
            template_day.classList.remove("text-danger");
            template_day.classList.add("text-danger");
          }
        }
      }

      if (dayData.plan.length !== 0) {
        template_day.classList.remove("text-danger");
        template_day.classList.add("text-success");
      }
    });

    // вставляем
    planning_list.appendChild(template);
  });

}

// конструирование группы студентов
function post_list_students_of_this_group(studentList=studentBuffer.data.students) {
  log.i("post_list_students_of_this_group", "called");

  // если студентов нет - отобразить предложение их добавить
  const student_is_empty = document.getElementById("student_is_empty");
  studentList.length === 0 ? student_is_empty.classList.remove("d-none") : student_is_empty.classList.add("d-none");
  
  // контейнеры
  const activeList = document.getElementById("active_list");
  const actualList = document.getElementById("actual_list");
  const madeList = document.getElementById("made_list");
  const otherList = document.getElementById("other_list");
  const globalList = [activeList, actualList, madeList, otherList];
  const item_list_template = document.getElementById("item_list_template");
  
  // проверка на редактирование
  const view_edit_students = document.getElementById("view_edit_students").getAttribute("edit-mode");
  
  // Очистка контейнеров
  globalList.forEach((list) => {
    list.innerHTML = null;
  });
  
  // Создание элементов item_list
  function createItemElement(item) {
    // создаем копию
    const template = document.importNode(item_list_template.content, true);
    
    if (item.planning != "EMPTY") {
      const planningElement = template.getElementById("template_planning");
      planningElement.className = "fa-solid fa-calendar-check me-1";
      planningElement.setAttribute("data-bs-title", `Назначен на ${formatDate(item.planning, "d months yy")}`);
    }
    
    // заполняем данные
    const nameElement = template.getElementById("template_name");
    nameElement.innerHTML = `${item.name}`;
    nameElement.addEventListener("click", () => editStudent(item));
    
    // счетчик
    const countElement = template.getElementById("template_count");
    countElement.textContent = `[${item.duty_count}]`;
    
    // селект1
    // const statusSelectMin = template.getElementById("template_status_select_min");
    const statusSelectMin = template.querySelector('select[name="template_status_select_min"]');
    statusSelectMin.value = item.duty_status;
    
    statusSelectMin.addEventListener("change", function (event) {
      changeStudentStatus(item.group_id, item.id, event.target.value, this, item.duty_status);
    });

    // Отслеживаем событие focus на выпадающем списке
    statusSelectMin.addEventListener('focus', function() {
      focusSelected = true;
    });
    
    // Отслеживаем событие blur на выпадающем списке
    statusSelectMin.addEventListener('blur', function() {
      focusSelected = false;
    });
    
    // селект2
    // const statusSelectMax = template.getElementById("template_status_select_max");
    const statusSelectMax = template.querySelector('select[name="template_status_select_max"]');
    statusSelectMax.value = item.duty_status;
    
    statusSelectMax.addEventListener("change", function (event) {
      changeStudentStatus(item.group_id, item.id, event.target.value, this, item.duty_status);
    });
    
    // Отслеживаем событие focus на выпадающем списке
    statusSelectMax.addEventListener('focus', function() {
      focusSelected = true;
    });
    
    // Отслеживаем событие blur на выпадающем списке
    statusSelectMax.addEventListener('blur', function() {
      focusSelected = false;
    });
    
    // кнопка удаления
    const studentDeleteButton = template.getElementById("template_delete_student");
    view_edit_students === "enabled" ? studentDeleteButton.classList.remove("d-none") : false;
    studentDeleteButton.addEventListener("click", () => deleteStudent(item));

    // кнопка редактирования
    const studentChangeButton = template.getElementById("template_edit_student");
    view_edit_students === "enabled" ? studentChangeButton.classList.remove("d-none") : false;  
    studentChangeButton.addEventListener("click", () => editStudent(item));
    
    // возвращаем результат
    return template;
  }

  // Удалить все существующие элементы <option> с атрибутом 'value'
  const student_list_for_planning = document.getElementById("student_list_for_planning");
  const selectedValue = student_list_for_planning.value;

  // Отслеживаем событие focus на выпадающем списке
  student_list_for_planning.addEventListener('focus', function() {
    focusSelected = true;
  });
  
  // Отслеживаем событие blur на выпадающем списке
  student_list_for_planning.addEventListener('blur', function() {
    focusSelected = false;
  });
  
  for (var i = student_list_for_planning.options.length - 1; i >= 0; i--) {
    // Проверьте, есть ли атрибут "value" у текущего <option> элемента
    if (student_list_for_planning.options[i].hasAttribute("value")) {
      // Удалите <option> элемент, если у него есть атрибут "value"
      student_list_for_planning.remove(i);
    }
  }
  
  // Проходимся по списку и раскидываем по статусам студентов
  studentList.forEach((item) => {
    const itemElement = createItemElement(item);
    const targetListIndex = parseInt(item.duty_status);
    
    if (targetListIndex >= 0 && targetListIndex < globalList.length) {
      globalList[targetListIndex].appendChild(itemElement);
      
    } else {
      log.w("post_list_students_of_this_group", "item.duty_status invalid");
    }
    
    // и добавляем стуеднтов в select
    const newOption = document.createElement("option");
    newOption.value = item.id;

    let prefix = `[${item.duty_count}]`;
    switch (item.duty_status) {
      case "0": prefix = `${prefix} [не дежурил]`; break;
      case "1": prefix = `${prefix} [назначен]`; break;
      case "2": prefix = `${prefix} [отдежурил]`; break;
      case "3": prefix = `${prefix} [освобожден]`; break;
      default: log.w("post_list_students_of_this_group", "switch (item.duty_status) default");
    }

    if (item.planning != "EMPTY") {
      prefix = `${prefix} [запланирован]`;
    }

    newOption.textContent = `${prefix} ${item.name}`;

    student_list_for_planning.appendChild(newOption);
  });
  
  // если уже был выбран - возвращаем его в список выбранных
  selectedValue ? student_list_for_planning.value = selectedValue : false;

  // Если никого нет - вывести, что список пустой
  globalList.forEach((list) => {
    list.innerHTML === "" ? list.innerHTML = "<tr><td>Список пуст</td><td></td><td class='edit_student d-none'></td><td class='delete_student d-none'></td></tr>" : false;
  });

  // для подсветки
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

// конструирование списка групп
function post_list_groups(groupList=groupBuffer.data.groups) {
  log.i("post_list_groups", "called")

  // инициализировать избранную группу
  const favoriteGroup = getCookieValue("DUTY[fav]");
  let currentGroup = false;

  // если групп нет - отобразить предложение их добавить
  const group_is_empty = document.getElementById("group_is_empty");
  groupList.length === 0 ? group_is_empty.classList.remove("d-none") : group_is_empty.classList.add("d-none");

  // Получение ссылки на контейнер и токена
  const groupManagement = document.getElementById("list_of_managed_groups");
  const list_of_managed_groups_template = document.getElementById('list_of_managed_groups_template');

  // очистка
  groupManagement.innerHTML = null;

  // Формирование HTML-структуры
  groupList.forEach((group) => {
    // создаем копию
    const template = document.importNode(list_of_managed_groups_template.content, true);

    // Заполняем
    const groupTitleH3 = template.getElementById("template_group_title");
    groupTitleH3.textContent = `${group.name}`;

    // роль
    const groupRole = template.getElementById("template_group_role");

    let text_role = "?";
    switch (group.role) {
      case "admin":
        text_role = "Вы создатель этой группы";
        groupRole.className = "fa-solid fa-user-tie";
        break;

      case "manager":
        text_role = "Вы управляющий этой группы";
        groupRole.className = "fa-solid fa-user-clock";
        break;

      default:
        log.w("post_list_groups", "group.role default");
    }

    groupRole.setAttribute("data-bs-title", `${text_role}`)

    // о группе
    const groupAboutDiv = template.getElementById("template_group_about");
    groupAboutDiv.textContent = group.about ? group.about : "Описание группы отсутствует";
    groupAboutDiv.addEventListener("click", () => editGroup(group));

    // закреп
    const setFavoriteGroupButton = template.getElementById("template_favorite_group");
    setFavoriteGroupButton.setAttribute('data-is-fav', group.id);
    setFavoriteGroupButton.addEventListener("click", () => setFavoriteGroup(group.id, true));

    if (favoriteGroup && group.id === favoriteGroup ) {
      setFavoriteGroupButton.setAttribute('data-is-fixed', 'true');
      setFavoriteGroupButton.classList.remove("fa-regular");
      setFavoriteGroupButton.classList.add("fa-solid");

      if (!activeGroup) {
        currentGroup = group;
      }
    }

    const getGroupManagementButton = template.getElementById("template_get_group_management");
    getGroupManagementButton.addEventListener("click", () => {
      setFavoriteGroup(group.id, false);
      addManagedGroupId(group);
      getListStudentsOfThisGroup(group);
      navigation.viewLayout('student_list_layout');
    });

    const getGroupPlanningButton = template.getElementById("template_get_group_planning");
    getGroupPlanningButton.addEventListener("click", () => {
      setFavoriteGroup(group.id, false);
      addManagedGroupId(group);
      getPlanningListThisGroup(group);
      navigation.viewLayout('duty_planning_layout');
    });
    
    const editGroupButton = template.getElementById("template_edit_group");
    editGroupButton.addEventListener("click", () => editGroup(group));

    const deleteGroupButton = template.getElementById("template_delete_group");
    deleteGroupButton.addEventListener("click", () => deleteGroup(group));

    const shareGroupButton = template.getElementById("template_share_group");
    shareGroupButton.addEventListener("click", () => shareGroup(group));

    // вставляем
    groupManagement.appendChild(template);
  });

  // задаем выбранную группу текущей в кеш либо по умолчанию активной делаем единственную
  if (!activeGroup) {

    if (currentGroup) {
      getListStudentsOfThisGroup(currentGroup);
  
    } else if (groupList.length === 1) {
      currentGroup = groupList[0];
      getListStudentsOfThisGroup(currentGroup);
    }
  }

  // для подсветки
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

// отобразить лог
function post_log(logList=logBuffer.data) {
  log.i("post_log", "called");

  countPages = parseInt(logList.count_lists);

  // Получаем ссылку на элемент, в котором будет размещена таблица и навигация
  const tablegroupManagement = document.getElementById('log_list');
  const log_list_template = document.getElementById('log_list_template');

  // очистка
  tablegroupManagement.innerHTML = null;

  // идем по логлисту
  logList.log.forEach(function(logItem) {
    // создаем копию
    const template = document.importNode(log_list_template.content, true);
    
    // Заполняем таблицу данными из logList
    const log_list_template_logmessage = template.getElementById("template_logmessage");
    log_list_template_logmessage.addEventListener("click", () => setCellLogMessage(logItem.log_message));

    const timeText = template.getElementById("template_datetime");
    timeText.datetime = logItem.datetime;
    timeText.textContent = getTimeString(logItem.datetime);

    const cellEventKey = template.getElementById("template_eventkey");
    cellEventKey.textContent = logItem.event_key;

    const cellUserLogin = template.getElementById("template_userlogin");
    cellUserLogin.textContent = logItem.user_login != "EMPTY" ? logItem.user_login : null;

    // Добавляем созданный шаблон в контейнер
    tablegroupManagement.appendChild(template);
  });

  // Отображаем текущую страницу
  const currentPageSpan = document.getElementById('current_log_page');
  if (currentPageSpan) {
    currentPageSpan.className = "current_page";
    currentPageSpan.textContent = `${logList.page}/${logList.count_lists}`;
  }
}

// ================== Загрузка ==================

// удаление планировки
function deletePlanning(planning_id) {
  log.i("deletePlanning", "called");

  var clientData = `id=delete_planning_student_duty&planning_id=${planning_id}`;
  ajaxQuery("delete_planning_student_duty", clientData);
}

// сменить статус в бд
function changeStudentStatus(group_id=activeGroup.id, student_id, duty_status, select=null, buffer=null) {
  log.i("changeStudentStatus", "called");

  if (duty_status == 1 && select && buffer) {
    log.i("changeStudentStatus", "duty_status == 1");

    const add_planning_student_duty = document.getElementById("add_planning_student_duty");
      const add_planning_student_duty_group_id = add_planning_student_duty.querySelector('input[name="group_id"]');
      add_planning_student_duty_group_id.value = group_id;
      
      const student_list_for_planning = document.getElementById("student_list_for_planning");
      student_list_for_planning.value = student_id;

      const add_planning_student_duty_duty_date = add_planning_student_duty.querySelector('input[name="duty_date"]');
      add_planning_student_duty_duty_date.value = formatDate(currentDateTime, "yyyy-mm-dd");

    // Получите ссылку на модальное окно по его id и откройте его
    editPlanningModal('student&date');
    viewDutyMessage(document.getElementById("duty_date_for_planning").value);
    const modal = document.getElementById("addPlanningStudentDutyModal");
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();

    // вернуть обратно актуальный селект
    select.value = buffer;

    return false
  }

  const clientData = `id=change_student_status&group_id=${group_id}&student_id=${student_id}&duty_status=${duty_status}`;
  ajaxQuery("change_student_status", clientData);
}

// отобразить список дежурств на данный день
function viewDutyMessage(this_date, planning=planningBuffer.data.planning) {
  log.i("viewDutyMessage", "called");
  let message = "";

  if (this_date) {
    planning.forEach((plan) => {
      if (plan.duty_date == this_date) {
        message += `<br>[${plan.duty_count}] ${plan.name} <a type="button" class="text-reset" onclick="deletePlanning(${plan.id});">[x]</a>`;
      }
    });
  } 

  const duty_message = document.getElementById("add_planning_student_duty_message");
  if (message) {
    duty_message.classList.remove("alert-danger");
    duty_message.classList.add("alert-success");
    duty_message.innerHTML = `На ${formatDate(this_date, "d months yy")} назначены: ${message}`;

  } else if (this_date == formatDate(currentDateTime, "yyyy-mm-dd")) {

    if (studentBuffer.data.students) {
      const actualStudentsList = studentBuffer.data.students.filter((item) => item.duty_status == "1");

      if (actualStudentsList.length !== 0) {
        actualStudentsList.forEach((student) => {
            message += `<br>[${student.duty_count}] ${student.name} <a type="button" class="text-reset" onclick="changeStudentStatus(${activeGroup.id},${student.id}, 0, null, null);">[x]</a>`;
        });
  
        duty_message.classList.remove("alert-danger");
        duty_message.classList.add("alert-success");
        duty_message.innerHTML = `На сегодня назначены: ${message}`;

      } else {
        duty_message.classList.remove("alert-success");
        duty_message.classList.add("alert-danger");
        duty_message.innerHTML = `Сегодня никто не дежурит`;
      }
    }
  
  } else {
    duty_message.classList.remove("alert-success");
    duty_message.classList.add("alert-danger");
    duty_message.textContent = "В этот день никто не дежурит";
  }

  // для подсветки
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

// кто управляет этой группой
function whoManagesThisGroup(group=activeGroup) {
  log.i("whoManagesThisGroup", "called");

  var clientData = `id=who_manages_this_group&group_id=${group.id}`;
  ajaxQuery("who_manages_this_group", clientData);
}

// получить планы на месяц
function getPlanningListThisGroup(group=activeGroup) {
  log.i("getPlanningListThisGroup", "called");

  var clientData = `id=get_planning_list_this_group&group_id=${group.id}&month=${currentMonthAndYear}`;
  ajaxQuery("get_planning_list_this_group", clientData);
}

// получить список студентов заданной группы
function getListStudentsOfThisGroup(group=activeGroup) {
  log.i("getListStudentsOfThisGroup", "called")

  // заполнение данных
  const title_group_manager_layout = document.getElementById("title_group_manager_layout");
  title_group_manager_layout.textContent = `${group.name}`;

  const student_management = document.getElementById("student_management");
  student_management.classList.remove("d-none");

  const group_bottom_menu = document.getElementById("group_bottom_menu");
  group_bottom_menu.classList.remove("d-none");

  const duty_planning_layout_table = document.getElementById("duty_planning_layout_table");
  duty_planning_layout_table.classList.remove("d-none");
  
  // ajax
  const clientData = `id=get_list_students_of_this_group&group_id=${group.id}`;
  ajaxQuery("get_list_students_of_this_group", clientData);
  
  // инициализация
  activeGroup = group;
  setFavoriteGroup(group.id, false);
  whoManagesThisGroup(group);
  getPlanningListThisGroup(group);
  
  addManagedGroupId(group);
}

// получить список своих групп
function getListYourGroups() {
  log.i("getListYourGroups", "called");

  const clientData = "id=get_list_your_groups";
  ajaxQuery("get_list_your_groups", clientData);
}

// загрузить панель юзера
function loadUserManagement() {
  log.i("loadUserManagement", "called");

  getListYourGroups();
  if (activeGroup) {
    getListStudentsOfThisGroup(activeGroup);

  } else {
    document.getElementById("title_group_manager_layout").textContent = "Выберите группу";
    document.getElementById("title_duty_planning_layout").textContent = "Выберите группу"
  }
}

// калькулятор месяца
function addMonths(dis=0) {
  log.i(`addMonths(${dis})`, "called");

  if (dis) {
    // Разбиваем currentMonthAndYear на год и месяц
    const datetime = new Date(currentMonthAndYear);
    const month = parseInt((datetime.getMonth() + 1).toString().padStart(2, '0'));
    const year = parseInt(datetime.getFullYear());
  
    // Учитываем как положительное, так и отрицательное смещение
    const direction = dis >= 0 ? 1 : -1;
    dis = Math.abs(dis);
  
    // Вычисляем новый месяц и год
    const newMonth = ((month + direction * dis - 1) % 12 + 12) % 12 + 1;
    const newYear = year + Math.floor((month + direction * dis - 1) / 12);
  
    // Форматируем результат в нужный формат 'YYYY-MM'
    const formattedDate = `${newYear}-${newMonth.toString().padStart(2, '0')}`;
  
    // возвращаем его
    currentMonthAndYear = formattedDate;
  } 

  currentMonthAndYear == currentMonthStarted
  ? document.getElementById("go_to_current_month").classList.add("d-none") 
  : document.getElementById("go_to_current_month").classList.remove("d-none");
  
  // обновляем активный месяц
  activeGroup
  ? getPlanningListThisGroup(activeGroup) 
  : document.getElementById("title_duty_planning_layout").textContent = "Выберите группу";
}

// вернуться на сегодня
function goToCurrentMonth() {
  currentMonthAndYear = currentMonthStarted;
  addMonths();
}

// посмотреть лог
function viewLog(dis=0) {
  log.i(`viewLog(${dis})`, "called");

  if (dis) {
    const newPage = currentPage + dis;

    if (0 < newPage && newPage <= countPages) {
      currentPage = newPage;
    }
  }

  const clientData = `id=get_list_log&count_pages=${countLogLists}&page=${currentPage}`;
  ajaxQuery("get_list_log", clientData);
}

// загрузить панель администратора
function loadAdminManagement() {
  log.i("loadAdminManagement", "called")

  viewLog();
}

// загрузить информацию обо мне
function loadMyData() {
  log.i("loadMyData", "called");

  if (!getCookieValue("DUTY[token]") || !getCookieValue("DUTY[login]")) {
    log.i("loadMyData", "not token and login");
    clearInterval(loaderTimer);
    return false;
  } 

  const clientData = "id=whose_token_is_this";
  ajaxQuery("whose_token_is_this", clientData);
}

// массовое добавление студентов
function addSomeStudents() {
  // Получаем содержимое textarea
  const textarea = document.getElementById('add_some_students');
  const content = textarea.value;

  // Разделяем содержимое на строки и обрезаем лишние пробелы
  const lines = content.split(/\r?\n|,/).map(line => line.trim());

  // Вызываем функцию addStudent для каждой строки
  lines.forEach(name => {
    name && activeGroup.id ? addNewStudent(activeGroup.id, name) : false;
  });

  // очистка после добавления
  textarea.value = null;
}

// обновить все
function myLoader() {
  log.i("countInterval", countInterval);
  log.i("myLoader", "called");
  countInterval++;

  // Получите все элементы, у которых есть Bootstrap tooltip
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

  // Закройте все tooltip
  tooltipTriggerList.forEach(tooltipTriggerEl => {
    const tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
    if (tooltip) {
        tooltip.hide();
    }
  });

  firstLoad = true;
  loadMyData()
}

// первая загрузка и инициализация
function firstLoadApp() {
  log.i("firstLoadApp", "called");

  // загружаем навигацию
  const favoriteMenu = getCookieValue("DUTY[menu]");
  navigation.viewLayout(favoriteMenu);
  
  // установка отлавливания форм
  $(document).on('submit', listFormId, function() {
    const formId = this.id.value;

    switch (formId) {

      case "edit_my_password":

        if (!checkDoublePassword(formId)) {
          callToast("error", "Пароли не совпадают");
          return false;
        }

        break;

      case "registration":
        const reg_login = document.getElementById("reg_login");
        reg_login.value = reg_login.value.trim();

        if (!checkDoublePassword(formId)) {
          callToast("error", "Пароли не совпадают");
          return false;
        }

        break;

      case "authorization":
        const auth_login = document.getElementById("auth_login");
        auth_login.value = auth_login.value.trim();
        break;

      case "edit_my_login":
        const edit_my_login_new_login = document.getElementById("edit_my_login_new_login");
        edit_my_login_new_login.value = edit_my_login_new_login.value.trim();
        break;
    }

    const clientData = $(this).serialize();
    ajaxQuery(formId, clientData);
    return false;
  });

  // Обработчик события изменения для всех textarea с классом input-note
  $(document).on('input', '.input-note', function() {
    const textareaContent = $(this).val();
    const sanitizedContent = textareaContent.replace(/\n/g, ' '); // Заменяем переносы строк на пробелы
    $(this).val(sanitizedContent); // Обновляем содержимое textarea
  });

  // объект с интревалом обновления
  loaderTimer = setInterval(function() {
    if (isTabActive() && !focusSelected) {
      myLoader();
    }
  }, intervalApp);

  // если вкладка становится активной, вызываем myLoader()
  document.addEventListener('visibilitychange', function() {

    if (isTabActive()) {
      myLoader();
    }
  });

  // опционально: остановить таймер при закрытии/перезагрузке страницы
  window.addEventListener('beforeunload', function() {
    clearInterval(loaderTimer);
  });

  // общая загрузка
  myLoader();
}

// загрузка бутстраповсих преколов
function bootstrapLoad() {
  log.i("bootstrapLoad", "called");
  
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

  tooltipList.forEach(tooltip => {
    tooltip.hide();
  });
}

// загрузка всего
window.addEventListener('load', function() { 
  log.i("addEventListener", "loaded");
  firstLoadApp();
  bootstrapLoad();
});

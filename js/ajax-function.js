// Навешиваем обработчик события отправки на формы по списку id
var listFormId = `
  #authorization,
  #logout,
  #delete_my_account,
  #edit_my_login,
  #edit_my_password,
  #reset_group_key,
  #create_group_report,
  #edit_my_data,
  #add_new_student,
  #delete_student,
  #edit_student_data,
  #reload_student_statuses,
  #add_new_group,
  #add_an_existing_group,
  #edit_group_data,
  #delete_group,
  #registration,
  #reset_user_password,
  #add_planning_student_duty
`;

function ajaxQuery(formId, clientData) {
  log.i("ajaxQuery", `called(${formId})`);

  $.ajax({
    url: pathToServer,
    type: "POST",
    data: clientData,
    
    success: function(html) {
      log.d("success", clientData);
  
      if (!html || html.trim() === '') {
        log.i("success", "Server response is empty");
        callToast("error", "Некорректный ответ от сервера");
        return false;
      }
  
      try {
        var buffer = JSON.parse(html);
  
      } catch (error) {
        log.e("success", "Not JSON Parsed");
        log.d("success", html);
        callToast("error", "Некорректный ответ от сервера");
        return false;
      }

      buffer.status.error ? log.e(formId, buffer) : log.d(formId, buffer);

      if (buffer.status.error) {
        callToast("error", buffer.status.error);

        switch (formId) {
          case "authorization":
          case "whose_token_is_this":
            clearAllCookies();
            break;
        }
      }
  
      if (!buffer.data) {
        log.w("buffer", "buffer.data is empty");
        return false;
      } 
  
      const message = buffer.data.message
      if (message) {
        callToast("success", message);
      }

      switch(formId) {

        case "authorization":
        case "registration":
          document.cookie = `DUTY[login]=${buffer.data.login}; expires=${expirationDate.toUTCString()}`;
          document.cookie = `DUTY[token]=${buffer.data.token}; expires=${expirationDate.toUTCString()}`;
          reloadStatus ? location.reload() : log.i("success", "reloadStatus off");
          break;
        
        case "logout":
        case "delete_my_account":
          clearAllCookies();
          reloadStatus ? location.reload() : log.i("success", "reloadStatus off");
          break;

        case "edit_my_login":
        case "edit_my_password":
          document.cookie = `DUTY[login]=${buffer.data.login}; expires=${expirationDate.toUTCString()}`;
          document.cookie = `DUTY[token]=${buffer.data.token}; expires=${expirationDate.toUTCString()}`;
          loadMyData();
          break;

        case "reset_group_key":
          const input_share_group_key = document.getElementById("input_share_group_key");
          input_share_group_key.value = buffer.data.group_key;
          getListYourGroups();
          break;

        case "create_group_report":
          generatePrintableDocument(buffer.data);
          break;

        case "get_list_your_groups":
          groupBuffer = buffer;
          const progress_groups = Math.round(100 / buffer.data.limit_groups * buffer.data.groups.length);
          const progressbar_groups = document.getElementById("progressbar_groups");
          progressbar_groups.textContent = `${buffer.data.groups.length} / ${buffer.data.limit_groups}`;
          progressbar_groups.style.width = `${progress_groups}%`;      
          post_list_groups(buffer.data.groups);
          break;
        
        case "get_list_log":
          logBuffer = buffer;
          post_log(buffer.data);
          break;
        
        case "who_manages_this_group":
          managerBuffer = buffer;
          post_list_managers(buffer.data.managers);
          break;

        case "get_planning_list_this_group":
          planningBuffer = buffer;
          viewDutyMessage(document.getElementById("duty_date_for_planning").value, buffer.data.planning);
          post_planning_list(buffer.data);
          break

        case "get_list_students_of_this_group":
          studentBuffer = buffer;
          const progress_students = Math.round(100 / buffer.data.limit_students * buffer.data.students.length);
          const progressbar_students = document.getElementById("progressbar_students");
          progressbar_students.textContent = `${buffer.data.students.length} / ${buffer.data.limit_students}`;
          progressbar_students.style.width = `${progress_students}%`;
          post_list_students_of_this_group(buffer.data.students);
          break;
        
        case "whose_token_is_this":
          userBuffer = buffer;
          editMyInfo(buffer.data.user_data);

          if (firstLoad) {
            firstLoad = false;

            switch (buffer.data.user_data.role) {
              case "group_manager":
                loadUserManagement();
                break;
  
              case "system_admin":
                loadAdminManagement()
                break;
  
              default:
                log.w("whose_token_is_this", "invalid role");
            }
          }

          break;

        case "get_current_duty_students":
          dutyBuffer = buffer.data;
          post_list_duty_students(buffer.data);
          break;

        case "edit_my_data":
          loadMyData();
          break;

        case "add_new_student":
          document.getElementById("add_new_student_name_input").value = null;
          getListStudentsOfThisGroup(activeGroup);
          break;

        case "delete_student":
        case "edit_student_data":
        case "reload_student_statuses":
        case "change_student_status":
        case "add_planning_student_duty":
        case "delete_planning_student_duty":
          getListStudentsOfThisGroup(activeGroup);
          break;

        case "add_new_group":
          document.getElementById("group_new_name_input").value = null;
          document.getElementById("group_new_about_input").value = null;
          getListYourGroups();
          break;

        case "add_an_existing_group":
        case "edit_group_data":
          getListYourGroups();
          break;

        case "delete_group":
          getListYourGroups();
          activeGroup = false;
          break;

        case "reset_user_password":
          break;
      }
    }
  })
  
  .fail(function(xhr, status, error) {
    log.e("ajaxQuery", "fail");
    callToast("error", "Отсутствует соединение с сервером");
  });
}
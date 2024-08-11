var navigation = {

  default_layout: "account_layout",
  
  viewLayout: function(id) {
    log.i(`navigation.viewLayout(${id})`, "called");

    // закрепляем в кукисах
    if (id && document.getElementById(id)) {
      layoutName = id;
      document.cookie = `DUTY[menu]=${layoutName}; expires=${expirationDate.toUTCString()}`;

    }  else {
      layoutName = this.default_layout;
    }

    // Создать массив для хранения id элементов по классу layout
    const layoutElements = document.querySelectorAll('.layout');
    const layoutIDs = [];
    layoutElements.forEach((element) => {
      layoutIDs.push(element.id);
    });

    // Скрываем все макеты
    layoutIDs.forEach(id => {
      const layout = document.getElementById(id);
      layout.classList.add("d-none");

      const nav = document.getElementById(`${id}_nav`);
      nav.classList.remove("active");
    });

    const layout = document.getElementById(layoutName);
    layout ? layout.classList.remove("d-none") : false ;

    const nav = document.getElementById(`${layoutName}_nav`);
    nav ? nav.classList.add("active") : false;

    layoutName === this.default_layout ? focusSelected = true : focusSelected = false;
  }
};

var activate = {

  form: function(checkBox, formId) {
    log.i(`activate.form(${formId})`, "called");

    const form = document.getElementById(formId);
    const inputElements = form.querySelectorAll('input, textarea');

    // Итерируемся по всем элементам и устанавливаем им атрибут disabled
    inputElements.forEach(element => {
      element.disabled = checkBox.checked;
    });

    checkBox.checked = checkBox.checked;
  }
};

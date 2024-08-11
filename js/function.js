// вызов уведомлений
function callToast(status, message) {
  log.i("callToast", "called");

  const toastSuccess = document.getElementById('message');
  const toastSuccessTextBox = document.getElementById('data_message');
  const bootstrapToastSuccess = bootstrap.Toast.getOrCreateInstance(toastSuccess);

  const toastError = document.getElementById('error_message');
  const toastErrorTextBox = document.getElementById('data_error_message');
  const bootstrapToastError = bootstrap.Toast.getOrCreateInstance(toastError);

  switch (status) {

    case "success":
      toastSuccessTextBox.textContent = message;
      bootstrapToastSuccess.show();
      break;

    case "error":
      toastErrorTextBox.textContent = message;
      bootstrapToastError.show();
      break;

    default:
      log.w("callToast", "status is default")
  }
}

// получить кукисы
function getCookieValue(cookieName) {
  log.i("getCookieValue", "called");
  
  let data = null;
  const cookies = document.cookie.split(';');
  
  cookies.forEach(function(item) {
    item = item.trim();
    
    // Проверяем, начинается ли кука с заданного имени
    if (item.startsWith(`${cookieName}=`)) { 
      // Если да, возвращаем значение куки
      data = item.substring(cookieName.length + 1); 
    }
  });
  
  // Если кука с заданным именем не найдена, возвращаем null
  return data;
}

// очистка кукисов
function clearAllCookies(cookieNames = ["DUTY[login]", "DUTY[token]"]) {
  log.i("clearAllCookies", "called")
  
  const cookies = document.cookie.split(';');
  
  cookies.forEach(function(item) {
    const cookie = item.trim();
    const cookieParts = cookie.split('=');
    const cookieName = cookieParts[0].trim();
    
    cookieNames.forEach(function(ignoreCookie) {
      cookieName.includes(ignoreCookie) ? document.cookie = `${cookieName}=; expires=-1` : false;
    });
  });
}

// закреп
function setFavoriteGroup(group_id, doubled=true) {
  log.i("setFavoriteGroup", "called")
  
  // Получить все кнопки с классом "favorite_group"
  const favoriteGroupButtons = document.querySelectorAll('.favorite_group');
  let favButton = false;
  
  favoriteGroupButtons.forEach((button) => {
    if (button.getAttribute('data-is-fav') === group_id) {
      favButton = button;
    }
  });
  
  if (favButton.getAttribute('data-is-fixed') === "false") {
    document.cookie = `DUTY[fav]=${group_id}; expires=${expirationDate.toUTCString()}`;
    
    favoriteGroupButtons.forEach(function(element) { 
      element.setAttribute('data-is-fixed', 'false');
      element.classList.remove("fa-solid");
      element.classList.add("fa-regular");
    });
    
    favButton.setAttribute('data-is-fixed', 'true');
    favButton.classList.remove("fa-regular");
    favButton.classList.add("fa-solid");
    
  } else {

    if (doubled) {
      document.cookie = "DUTY[fav]=; expires=-1";
      favButton.setAttribute('data-is-fixed', 'false');
      favButton.classList.remove("fa-solid");
      favButton.classList.add("fa-regular");
    }
  }
}

// переключение темы
function colorMode() {
  log.i("colorMode", "called");
  
  const htmlElement = document.querySelector('html');
  const colorModeCheckbox = document.getElementById("color_mode_checkbox");
  
  let color = colorModeCheckbox.checked ? "dark" : "light";
  document.cookie = `DUTY[color]=${color}; expires=${expirationDate.toUTCString()}`;
  htmlElement.setAttribute('data-bs-theme', color);
}

function rememberMe(button) {
  log.i("rememberMe", "called");

  expirationDate = button.checked
  ? Date(currentDateTime.getFullYear(), currentDateTime.getMonth() + 1, currentDateTime.getDate())
  : Date(currentDateTime.getFullYear(), currentDateTime.getMonth() + 1, currentDateTime.getDate());
}

// копировать из input данные по id
function copyToClipboard(id) {
  log.i("copyToClipboard", "called");
  
  // inputElement.select();
  // document.execCommand('copy');
  // inputElement.type = "text";
  
  const inputElement = document.getElementById(id);
  const text = inputElement.value;
  
  
  navigator.clipboard.writeText(text)
  .then(() => {
    inputElement.select();
    log.i("copyToClipboard", 'Text copied to clipboard');
  })
  .catch(error => {
      log.e("copyToClipboard", `Error in copying text: `, error);
  });
}

// проверка пароля на дубликат
function checkDoublePassword(id) {
  log.i("checkDoublePassword", "called");

  switch (id) {

    case "edit_my_password":
      const edit_my_password = document.getElementById('edit_my_password');
      const my_new_password = edit_my_password.querySelector('input[name="my_new_password"]');
      const edit_my_password_double_password = document.getElementById('edit_my_password_double_password');
      return (my_new_password.value == edit_my_password_double_password.value);

    case "registration":
      const registration = document.getElementById('registration');
      const registration_double_password = document.getElementById('registration_double_password');
      const reg_password = registration.querySelector('input[name="reg_password"]');
      return (reg_password.value == registration_double_password.value);

    default:
      log.w("checkDoublePassword `id` is default");
      return false;
  }

}

// Функция для парсинга параметров из строки запроса
function parseQueryString(queryString) {
  log.i("parseQueryString", "called");

  let params = {};
  const parts = queryString.slice(1).split('&');

  parts.forEach(function(item) {
    const pair = item.split('=');
    const key = decodeURIComponent(pair[0]);
    const value = decodeURIComponent(pair[1]);
    params[key] = value;
  });

  return params;
}

// формат даты
function formatDate(dateString, format) {
  const datetime = new Date(dateString);
  const day = datetime.getDate().toString().padStart(2, '0');
  const month = (datetime.getMonth() + 1).toString().padStart(2, '0');
  const year = datetime.getFullYear();
  const hours = datetime.getHours().toString().padStart(2, '0');
  const minutes = datetime.getMinutes().toString().padStart(2, '0');
  // const seconds = datetime.getSeconds().toString().padStart(2, '0');
  const monthIndex = datetime.getMonth();
  const monthsMin = ['янв', 'фев', 'мар', 'апр', 'май', 'июнь', 'июль', 'авг', 'сент', 'окт', 'ноя', 'дек'];
  const monthsMax = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

  switch (format) {
    case "dd.mm.yyyy": 
      return `${day}.${month}.${year}`;

    case "yyyy-mm-dd":
      return `${year}-${month}-${day}`;

    case "dd.mm.yy":
      return `${day}.${month}.${year.toString().slice(-2)}`;

    case "hh:mm":
      return `${hours}:${minutes}`;

    case "d months yy":
      return `${day} ${monthsMin[monthIndex]} ${year.toString().slice(-2)}г`;

    case "d months":
      return `${day} ${monthsMax[monthIndex]}`;

    default:
      return dateString;
  }
  
}

// Функция для определения строки для тега time
function getTimeString(dateString) {

  const datetime = new Date(dateString);
  const diffInDays = Math.floor((currentDateTime - datetime) / (1000 * 60 * 60 * 24));
  const time = formatDate(dateString, "hh:mm");

  if (diffInDays === 0) {
    return `сегодня в ${time}`;

  } else if (diffInDays === 1) {
    return `вчера в ${time}`;

  } else {
    return `${formatDate(dateString, "dd.mm.yy")} в ${time}`;
  }
}

// Вспомогательная функция для получения текущей даты
function getCurrentDate() {
  const today = new Date();
  return formatDate(today, "yyyy-mm-dd");
}

// Вспомогательная функция для получения даты завтрашнего дня
function getTomorrowDate() {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return formatDate(tomorrow, "yyyy-mm-dd");
}

// формат лога
function formatLogMessage(logMessage) {
  // return logMessage.replace(/(\},)/g, '$1\n');
  const messages = logMessage.split(',');
  return messages.map(message => `<div>${message}</div>`).join('');
}

// если вкладка активна
function isTabActive() {
  return document.visibilityState === 'visible';
}

// валидность isVarChar
function isVarChar(str, size) {
  if (!size || size <= 0 || size > 512) {
    duty.log("isVarchar", "Invalid size");
    return false;
  }

  const pattern = new RegExp(`^.{3,${size}}$`, 'u');

  return pattern.test(str);
}

// валидность isDbInt
function isDbInt(num) {
  if (isNaN(num)) {
    return false;
  }

  const intValue = parseInt(num);

  if (!isNaN(intValue) && intValue >= 0 && intValue <= Number.MAX_SAFE_INTEGER) {
    return true;
  }

  return false;
}

// валидность isValidLogin
function isValidLogin(login) {
  const pattern = /^[a-zA-Z0-9_]{3,20}$/;
  return pattern.test(login);
}

// цвет
const favoriteColor = getCookieValue("DUTY[color]");
const color_mode_checkbox = document.getElementById("color_mode_checkbox");
if (color_mode_checkbox && favoriteColor) {
  switch (favoriteColor) {
    case "light":
      color_mode_checkbox.checked = false;
      colorMode();
      break;

    case "dark":
      color_mode_checkbox.checked = true;
      colorMode();
      break;

    default:
      log.w("firstLoadApp", "favoriteColor is default");
  } 
}
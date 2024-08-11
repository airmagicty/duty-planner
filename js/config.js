// время (для кукисов)
const currentDateTime = new Date(); 
const expirationDate = new Date(currentDateTime.getFullYear(), currentDateTime.getMonth() + 1, currentDateTime.getDate());
const currentDay = currentDateTime.getDate().toString().padStart(2, '0');
const currentMonth = (currentDateTime.getMonth() + 1).toString().padStart(2, '0');
const currentYear = currentDateTime.getFullYear();

// пути
const pathToServer = "php/api-server.php";
var pathToCurrentDuty = "https://duty.eclabs.ru/today_duty.php";
// const pathToCurrentDuty = "http://localhost/atv/today_duty.php";

// кеш
var countInterval = 0;
var firstLoad = true;
var loaderTimer = false;

var currentPage = 1;
var countPages = false;

var currentMonthAndYear = `${currentDateTime.getFullYear()}-${(currentDateTime.getMonth() + 1).toString().padStart(2, '0')}`;
const currentMonthStarted = currentMonthAndYear;
const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

var focusSelected = false;
var activeGroup = false;

// буфферы
var groupBuffer = false;
var studentBuffer = false;
var managerBuffer = false;
var planningBuffer = false;
var logBuffer = false;
var userBuffer = false;
var dutyBuffer = false;

// настройки
var intervalApp = 10000;
var intervalDuty = 10000;
var countLogLists = 10;
var reloadStatus = true;

// лог
const log = {
  loggingEnabled: true, 

  i: function(...args) {
    if (this.loggingEnabled) {
      // console.info(...args);
      console.info(...args);
    }
  },

  d: function(...args) {
    if (this.loggingEnabled) {
      console.log(...args);
    }
  },

  e: function(...args) {
    if (this.loggingEnabled) {
      console.error(...args);
    }
  },

  w: function(...args) {
    if (this.loggingEnabled) {
      console.warn(...args);
    }
  }

};
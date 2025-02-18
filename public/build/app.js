"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["app"],{

/***/ "./assets/app.js":
/*!***********************!*\
  !*** ./assets/app.js ***!
  \***********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_array_index_of_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.array.index-of.js */ "./node_modules/core-js/modules/es.array.index-of.js");
/* harmony import */ var core_js_modules_es_array_index_of_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_index_of_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_array_join_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.array.join.js */ "./node_modules/core-js/modules/es.array.join.js");
/* harmony import */ var core_js_modules_es_array_join_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_join_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es_date_to_string_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es.date.to-string.js */ "./node_modules/core-js/modules/es.date.to-string.js");
/* harmony import */ var core_js_modules_es_date_to_string_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_date_to_string_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_es_object_values_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/es.object.values.js */ "./node_modules/core-js/modules/es.object.values.js");
/* harmony import */ var core_js_modules_es_object_values_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_values_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./styles/app.scss */ "./assets/styles/app.scss");
/* harmony import */ var imask__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! imask */ "./node_modules/imask/esm/index.js");







// import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */


window.addEventListener("load", function () {
  document.querySelector(".emailfield input").tabindex = -1;
  document.querySelector(".emailfield input").tabIndex = -1;
  document.querySelector(".emailfield input").setAttribute("tabIndex", -1);
  document.querySelector(".emailfield input").setAttribute("tabindex", -1);
  var timing = document.querySelector('[name="timing"]').value;
  document.querySelector('.emailfield input[rel="email"]').value = timing * 3 / 2 + "@example.com";
  var element = document.getElementsByClassName("birthday_field");
  var maskOptions = {
    mask: Date,
    lazy: true
  };
  var mask = (0,imask__WEBPACK_IMPORTED_MODULE_8__["default"])(element[0], maskOptions);
  var focusObj = {
    firstname: "0",
    lastname: "0",
    postalcode: "0"
  };
  var roomRequestRadios = document.querySelectorAll('input[name="anmeldung[roomRequest]"]');
  var roomMateElement = document.getElementById('cont_roommate');
  if (roomRequestRadios && roomMateElement) {
    var updateRoomMateVisibility = function updateRoomMateVisibility() {
      var selectedValue = document.querySelector('input[name="anmeldung[roomRequest]"]:checked').value;
      if (selectedValue === "SINGLE") {
        roomMateElement.style.display = "none";
      } else {
        roomMateElement.style.display = "block";
      }
    };
    updateRoomMateVisibility();

    // if(roomRequest == "SINGLE") {

    // }

    roomRequestRadios.forEach(function (radio) {
      radio.addEventListener("change", updateRoomMateVisibility);
    });
  }
  document.querySelectorAll("[data-checkname]").forEach(function (element) {
    element.addEventListener("focus", function (e) {
      var name = e.currentTarget.getAttribute("data-checkname");
      focusObj[name] = "2";
      var ctokenElement = document.querySelector('[name="ctoken"]');
      if (ctokenElement && ctokenElement.value.indexOf("0") != -1) {
        ctokenElement.value = Object.values(focusObj).join("");
      }
    });
  });
});

/***/ }),

/***/ "./assets/styles/app.scss":
/*!********************************!*\
  !*** ./assets/styles/app.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_modules_es_array_for-each_js-node_modules_core-js_modules_es_obj-7bb33f","vendors-node_modules_core-js_modules_es_array_index-of_js-node_modules_core-js_modules_es_arr-5adef6"], () => (__webpack_exec__("./assets/app.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QUFJQSxhQUFhLENBQUN6QixLQUFLLENBQUMwQixPQUFPLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUU7UUFDM0RELGFBQWEsQ0FBQ3pCLEtBQUssR0FBRzJCLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDckIsUUFBUSxDQUFDLENBQUNzQixJQUFJLENBQUMsRUFBRSxDQUFDO01BQ3hEO0lBQ0YsQ0FBQyxDQUFDO0VBQ0osQ0FBQyxDQUFDO0FBQ0osQ0FBQyxDQUFDOzs7Ozs7Ozs7OztBQzFFRiIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Fzc2V0cy9hcHAuanMiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL3N0eWxlcy9hcHAuc2Nzcz84ZjU5Il0sInNvdXJjZXNDb250ZW50IjpbIi8vIGltcG9ydCAnLi9ib290c3RyYXAuanMnO1xuLypcbiAqIFdlbGNvbWUgdG8geW91ciBhcHAncyBtYWluIEphdmFTY3JpcHQgZmlsZSFcbiAqXG4gKiBUaGlzIGZpbGUgd2lsbCBiZSBpbmNsdWRlZCBvbnRvIHRoZSBwYWdlIHZpYSB0aGUgaW1wb3J0bWFwKCkgVHdpZyBmdW5jdGlvbixcbiAqIHdoaWNoIHNob3VsZCBhbHJlYWR5IGJlIGluIHlvdXIgYmFzZS5odG1sLnR3aWcuXG4gKi9cbmltcG9ydCBcIi4vc3R5bGVzL2FwcC5zY3NzXCI7XG5cbmltcG9ydCBJTWFzayBmcm9tIFwiaW1hc2tcIjtcblxud2luZG93LmFkZEV2ZW50TGlzdGVuZXIoXCJsb2FkXCIsIGZ1bmN0aW9uICgpIHtcbiAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcIi5lbWFpbGZpZWxkIGlucHV0XCIpLnRhYmluZGV4ID0gLTE7XG4gIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXCIuZW1haWxmaWVsZCBpbnB1dFwiKS50YWJJbmRleCA9IC0xO1xuICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiLmVtYWlsZmllbGQgaW5wdXRcIikuc2V0QXR0cmlidXRlKFwidGFiSW5kZXhcIiwgLTEpO1xuICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiLmVtYWlsZmllbGQgaW5wdXRcIikuc2V0QXR0cmlidXRlKFwidGFiaW5kZXhcIiwgLTEpO1xuXG4gIHZhciB0aW1pbmcgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdbbmFtZT1cInRpbWluZ1wiXScpLnZhbHVlO1xuICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcuZW1haWxmaWVsZCBpbnB1dFtyZWw9XCJlbWFpbFwiXScpLnZhbHVlID1cbiAgICAodGltaW5nICogMykgLyAyICsgXCJAZXhhbXBsZS5jb21cIjtcblxuICBjb25zdCBlbGVtZW50ID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZShcImJpcnRoZGF5X2ZpZWxkXCIpO1xuICBjb25zdCBtYXNrT3B0aW9ucyA9IHtcbiAgICBtYXNrOiBEYXRlLFxuICAgIGxhenk6IHRydWUsXG4gIH07XG4gIGNvbnN0IG1hc2sgPSBJTWFzayhlbGVtZW50WzBdLCBtYXNrT3B0aW9ucyk7XG5cbiAgdmFyIGZvY3VzT2JqID0ge1xuICAgIGZpcnN0bmFtZTogXCIwXCIsXG4gICAgbGFzdG5hbWU6IFwiMFwiLFxuICAgIHBvc3RhbGNvZGU6IFwiMFwiLFxuICB9O1xuXG4gIGNvbnN0IHJvb21SZXF1ZXN0UmFkaW9zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcbiAgICAnaW5wdXRbbmFtZT1cImFubWVsZHVuZ1tyb29tUmVxdWVzdF1cIl0nXG4gICk7XG4gIHZhciByb29tTWF0ZUVsZW1lbnQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnY29udF9yb29tbWF0ZScpO1xuXG4gIGlmIChyb29tUmVxdWVzdFJhZGlvcyAmJiByb29tTWF0ZUVsZW1lbnQpIHtcbiAgICBmdW5jdGlvbiB1cGRhdGVSb29tTWF0ZVZpc2liaWxpdHkoKSB7XG5cbiAgICAgIGNvbnN0IHNlbGVjdGVkVmFsdWUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFxuICAgICAgICAnaW5wdXRbbmFtZT1cImFubWVsZHVuZ1tyb29tUmVxdWVzdF1cIl06Y2hlY2tlZCdcbiAgICAgICkudmFsdWU7XG4gICAgICBpZiAoc2VsZWN0ZWRWYWx1ZSA9PT0gXCJTSU5HTEVcIikge1xuICAgICAgICByb29tTWF0ZUVsZW1lbnQuc3R5bGUuZGlzcGxheSA9IFwibm9uZVwiO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgcm9vbU1hdGVFbGVtZW50LnN0eWxlLmRpc3BsYXkgPSBcImJsb2NrXCI7XG4gICAgICB9XG4gICAgfVxuXG4gICAgdXBkYXRlUm9vbU1hdGVWaXNpYmlsaXR5KCk7XG5cbiAgICAvLyBpZihyb29tUmVxdWVzdCA9PSBcIlNJTkdMRVwiKSB7XG5cbiAgICAvLyB9XG5cbiAgICByb29tUmVxdWVzdFJhZGlvcy5mb3JFYWNoKChyYWRpbykgPT4ge1xuICAgICAgcmFkaW8uYWRkRXZlbnRMaXN0ZW5lcihcImNoYW5nZVwiLCB1cGRhdGVSb29tTWF0ZVZpc2liaWxpdHkpO1xuICAgIH0pO1xuICB9XG5cbiAgZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIltkYXRhLWNoZWNrbmFtZV1cIikuZm9yRWFjaCgoZWxlbWVudCkgPT4ge1xuICAgIGVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcImZvY3VzXCIsIGZ1bmN0aW9uIChlKSB7XG4gICAgICBjb25zdCBuYW1lID0gZS5jdXJyZW50VGFyZ2V0LmdldEF0dHJpYnV0ZShcImRhdGEtY2hlY2tuYW1lXCIpO1xuICAgICAgZm9jdXNPYmpbbmFtZV0gPSBcIjJcIjtcblxuICAgICAgY29uc3QgY3Rva2VuRWxlbWVudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tuYW1lPVwiY3Rva2VuXCJdJyk7XG4gICAgICBpZiAoY3Rva2VuRWxlbWVudCAmJiBjdG9rZW5FbGVtZW50LnZhbHVlLmluZGV4T2YoXCIwXCIpICE9IC0xKSB7XG4gICAgICAgIGN0b2tlbkVsZW1lbnQudmFsdWUgPSBPYmplY3QudmFsdWVzKGZvY3VzT2JqKS5qb2luKFwiXCIpO1xuICAgICAgfVxuICAgIH0pO1xuICB9KTtcbn0pO1xuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbIklNYXNrIiwid2luZG93IiwiYWRkRXZlbnRMaXN0ZW5lciIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvciIsInRhYmluZGV4IiwidGFiSW5kZXgiLCJzZXRBdHRyaWJ1dGUiLCJ0aW1pbmciLCJ2YWx1ZSIsImVsZW1lbnQiLCJnZXRFbGVtZW50c0J5Q2xhc3NOYW1lIiwibWFza09wdGlvbnMiLCJtYXNrIiwiRGF0ZSIsImxhenkiLCJmb2N1c09iaiIsImZpcnN0bmFtZSIsImxhc3RuYW1lIiwicG9zdGFsY29kZSIsInJvb21SZXF1ZXN0UmFkaW9zIiwicXVlcnlTZWxlY3RvckFsbCIsInJvb21NYXRlRWxlbWVudCIsImdldEVsZW1lbnRCeUlkIiwidXBkYXRlUm9vbU1hdGVWaXNpYmlsaXR5Iiwic2VsZWN0ZWRWYWx1ZSIsInN0eWxlIiwiZGlzcGxheSIsImZvckVhY2giLCJyYWRpbyIsImUiLCJuYW1lIiwiY3VycmVudFRhcmdldCIsImdldEF0dHJpYnV0ZSIsImN0b2tlbkVsZW1lbnQiLCJpbmRleE9mIiwiT2JqZWN0IiwidmFsdWVzIiwiam9pbiJdLCJzb3VyY2VSb290IjoiIn0=
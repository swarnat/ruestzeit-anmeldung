(self["webpackChunk"] = self["webpackChunk"] || []).push([["app"],{

/***/ "./assets/app.js":
/*!***********************!*\
  !*** ./assets/app.js ***!
  \***********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_date_to_string_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.date.to-string.js */ "./node_modules/core-js/modules/es.date.to-string.js");
/* harmony import */ var core_js_modules_es_date_to_string_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_date_to_string_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _bootstrap_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./bootstrap.js */ "./assets/bootstrap.js");
/* harmony import */ var _bootstrap_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_bootstrap_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./styles/app.scss */ "./assets/styles/app.scss");
/* harmony import */ var imask__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! imask */ "./node_modules/imask/esm/index.js");


/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */


window.addEventListener('load', function () {
  document.querySelector('.emailfield input').setAttribute('tabindex', -1);
  var timing = document.querySelector('[name="timing"]').value;
  document.querySelector('.emailfield input').value = timing * 3 / 2 + "@example.com";
  var element = document.getElementsByClassName('birthday_field');
  var maskOptions = {
    mask: Date,
    lazy: true
  };
  var mask = (0,imask__WEBPACK_IMPORTED_MODULE_3__["default"])(element[0], maskOptions);
});

/***/ }),

/***/ "./assets/bootstrap.js":
/*!*****************************!*\
  !*** ./assets/bootstrap.js ***!
  \*****************************/
/***/ (() => {


// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

/***/ }),

/***/ "./assets/styles/app.scss":
/*!********************************!*\
  !*** ./assets/styles/app.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_modules_es_date_to-string_js-node_modules_imask_esm_index_js"], () => (__webpack_exec__("./assets/app.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXBwLmpzIiwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQXdCO0FBQ3hCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUMyQjtBQUNEO0FBRTFCQyxNQUFNLENBQUNDLGdCQUFnQixDQUFDLE1BQU0sRUFBRSxZQUFZO0VBQ3hDQyxRQUFRLENBQUNDLGFBQWEsQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDQyxZQUFZLENBQUMsVUFBVSxFQUFFLENBQUMsQ0FBQyxDQUFDO0VBRXhFLElBQUlDLE1BQU0sR0FBR0gsUUFBUSxDQUFDQyxhQUFhLENBQUMsaUJBQWlCLENBQUMsQ0FBQ0csS0FBSztFQUM1REosUUFBUSxDQUFDQyxhQUFhLENBQUMsbUJBQW1CLENBQUMsQ0FBQ0csS0FBSyxHQUFLRCxNQUFNLEdBQUcsQ0FBQyxHQUFJLENBQUMsR0FBSSxjQUFjO0VBR3ZGLElBQU1FLE9BQU8sR0FBR0wsUUFBUSxDQUFDTSxzQkFBc0IsQ0FBQyxnQkFBZ0IsQ0FBQztFQUNqRSxJQUFNQyxXQUFXLEdBQUs7SUFDbEJDLElBQUksRUFBRUMsSUFBSTtJQUNWQyxJQUFJLEVBQUU7RUFDUixDQUFDO0VBQ0gsSUFBTUYsSUFBSSxHQUFHWCxpREFBSyxDQUFDUSxPQUFPLENBQUMsQ0FBQyxDQUFDLEVBQUVFLFdBQVcsQ0FBQztBQUMvQyxDQUFDLENBQUM7Ozs7Ozs7Ozs7O0FDdEJGO0FBQ0E7Ozs7Ozs7Ozs7OztBQ0ZBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2FwcC5qcyIsIndlYnBhY2s6Ly8vLi9hc3NldHMvYm9vdHN0cmFwLmpzIiwid2VicGFjazovLy8uL2Fzc2V0cy9zdHlsZXMvYXBwLnNjc3M/OGY1OSJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgJy4vYm9vdHN0cmFwLmpzJztcbi8qXG4gKiBXZWxjb21lIHRvIHlvdXIgYXBwJ3MgbWFpbiBKYXZhU2NyaXB0IGZpbGUhXG4gKlxuICogVGhpcyBmaWxlIHdpbGwgYmUgaW5jbHVkZWQgb250byB0aGUgcGFnZSB2aWEgdGhlIGltcG9ydG1hcCgpIFR3aWcgZnVuY3Rpb24sXG4gKiB3aGljaCBzaG91bGQgYWxyZWFkeSBiZSBpbiB5b3VyIGJhc2UuaHRtbC50d2lnLlxuICovXG5pbXBvcnQgJy4vc3R5bGVzL2FwcC5zY3NzJztcbmltcG9ydCBJTWFzayBmcm9tICdpbWFzayc7XG5cbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdsb2FkJywgZnVuY3Rpb24gKCkge1xuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5lbWFpbGZpZWxkIGlucHV0Jykuc2V0QXR0cmlidXRlKCd0YWJpbmRleCcsIC0xKVxuICAgIFxuICAgIHZhciB0aW1pbmcgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdbbmFtZT1cInRpbWluZ1wiXScpLnZhbHVlIFxuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5lbWFpbGZpZWxkIGlucHV0JykudmFsdWUgPSAoKHRpbWluZyAqIDMpIC8gMikgKyBcIkBleGFtcGxlLmNvbVwiO1xuXG5cbiAgICBjb25zdCBlbGVtZW50ID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnYmlydGhkYXlfZmllbGQnKTsgXG4gICAgY29uc3QgbWFza09wdGlvbnMgPSAgIHtcbiAgICAgICAgbWFzazogRGF0ZSxcbiAgICAgICAgbGF6eTogdHJ1ZVxuICAgICAgfTtcbiAgICBjb25zdCBtYXNrID0gSU1hc2soZWxlbWVudFswXSwgbWFza09wdGlvbnMpOyAgICBcbn0pIiwiXG4vLyByZWdpc3RlciBhbnkgY3VzdG9tLCAzcmQgcGFydHkgY29udHJvbGxlcnMgaGVyZVxuLy8gYXBwLnJlZ2lzdGVyKCdzb21lX2NvbnRyb2xsZXJfbmFtZScsIFNvbWVJbXBvcnRlZENvbnRyb2xsZXIpO1xuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbIklNYXNrIiwid2luZG93IiwiYWRkRXZlbnRMaXN0ZW5lciIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvciIsInNldEF0dHJpYnV0ZSIsInRpbWluZyIsInZhbHVlIiwiZWxlbWVudCIsImdldEVsZW1lbnRzQnlDbGFzc05hbWUiLCJtYXNrT3B0aW9ucyIsIm1hc2siLCJEYXRlIiwibGF6eSJdLCJzb3VyY2VSb290IjoiIn0=
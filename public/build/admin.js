"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["admin"],{

/***/ "./assets/admin.js":
/*!*************************!*\
  !*** ./assets/admin.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");
/* harmony import */ var core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_for_each_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _styles_admin_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./styles/admin.scss */ "./assets/styles/admin.scss");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var tom_select_dist_js_tom_select_complete_min__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! tom-select/dist/js/tom-select.complete.min */ "./node_modules/tom-select/dist/js/tom-select.complete.min.js");
/* harmony import */ var tom_select_dist_js_tom_select_complete_min__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(tom_select_dist_js_tom_select_complete_min__WEBPACK_IMPORTED_MODULE_4__);



// 



window.addEventListener("load", function () {
  var checkboxes = document.getElementsByClassName("categoryAssignment");
  for (var i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener("change", function (e) {
      update(e.target);
    });
    colorize(checkboxes[i]);
    checkboxes[i].closest("td").addEventListener("click", function (e) {
      var checkbox = e.target.querySelector("input");
      checkbox.checked = !checkbox.checked;
      update(checkbox);
    });
  }
  function update(element) {
    var categoryId = element.dataset.category;
    var anmeldungId = element.dataset.anmeldung;
    axios__WEBPACK_IMPORTED_MODULE_5__["default"].post('/admin/category/store', {
      category_id: +categoryId,
      anmeldung_id: +anmeldungId,
      value: element.checked ? 1 : 0
    }).then(function (response) {
      console.log(response);
    })["catch"](function (error) {
      console.log(error);
    });
    colorize(element);
  }
  function colorize(element) {
    if (element.checked) {
      element.closest("td").classList.add("checked");
    } else {
      element.closest("td").classList.remove("checked");
    }
    var categoryId = element.dataset.category;
    var count = document.querySelectorAll(".categoryCell.category" + categoryId + ".checked").length;
    document.getElementById("categoryTotal" + categoryId).innerHTML = count + " Teilnehmer";
  }

  // document.getElementsByClassName("categoryAssignment")
  document.querySelectorAll('.custom_select_dnd').forEach(function (autocompleteElement) {
    new (tom_select_dist_js_tom_select_complete_min__WEBPACK_IMPORTED_MODULE_4___default())(autocompleteElement, {
      plugins: ['drag_drop', 'remove_button']
    });
  });
});

/***/ }),

/***/ "./assets/styles/admin.scss":
/*!**********************************!*\
  !*** ./assets/styles/admin.scss ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_modules_es_array_for-each_js-node_modules_core-js_modules_es_obj-7bb33f","vendors-node_modules_tom-select_dist_js_tom-select_complete_min_js-node_modules_axios_lib_axios_js"], () => (__webpack_exec__("./assets/admin.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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RTtNQUNqQ0MsT0FBTyxFQUFFLENBQUMsV0FBVyxFQUFFLGVBQWU7SUFDeEMsQ0FBQyxDQUFDO0VBQ0osQ0FBQyxDQUFDO0FBRUosQ0FBQyxDQUFDOzs7Ozs7Ozs7OztBQ2hFRiIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Fzc2V0cy9hZG1pbi5qcyIsIndlYnBhY2s6Ly8vLi9hc3NldHMvc3R5bGVzL2FkbWluLnNjc3M/ZTI1NCJdLCJzb3VyY2VzQ29udGVudCI6WyIvLyBcbmltcG9ydCAnLi9zdHlsZXMvYWRtaW4uc2Nzcyc7XG5pbXBvcnQgYXhpb3MgZnJvbSAnYXhpb3MnO1xuaW1wb3J0IFRvbVNlbGVjdCBmcm9tIFwidG9tLXNlbGVjdC9kaXN0L2pzL3RvbS1zZWxlY3QuY29tcGxldGUubWluXCI7XG5cbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKFwibG9hZFwiLCBmdW5jdGlvbiAoKSB7XG4gIHZhciBjaGVja2JveGVzID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZShcImNhdGVnb3J5QXNzaWdubWVudFwiKTtcblxuICBmb3IgKHZhciBpID0gMDsgaSA8IGNoZWNrYm94ZXMubGVuZ3RoOyBpKyspIHtcbiAgICBjaGVja2JveGVzW2ldLmFkZEV2ZW50TGlzdGVuZXIoXCJjaGFuZ2VcIiwgKGUpID0+IHtcblxuICAgICAgdXBkYXRlKGUudGFyZ2V0KTtcbiAgICB9KTtcblxuICAgIGNvbG9yaXplKGNoZWNrYm94ZXNbaV0pO1xuXG4gICAgY2hlY2tib3hlc1tpXS5jbG9zZXN0KFwidGRcIikuYWRkRXZlbnRMaXN0ZW5lcihcImNsaWNrXCIsIChlKSA9PiB7XG4gICAgICB2YXIgY2hlY2tib3ggPSBlLnRhcmdldC5xdWVyeVNlbGVjdG9yKFwiaW5wdXRcIik7XG4gICAgICBjaGVja2JveC5jaGVja2VkID0gIWNoZWNrYm94LmNoZWNrZWRcbiAgICAgIFxuICAgICAgdXBkYXRlKGNoZWNrYm94KVxuICAgIH0pO1xuICB9XG5cbiAgZnVuY3Rpb24gdXBkYXRlKGVsZW1lbnQpIHtcbiAgICB2YXIgY2F0ZWdvcnlJZCA9IGVsZW1lbnQuZGF0YXNldC5jYXRlZ29yeTtcbiAgICB2YXIgYW5tZWxkdW5nSWQgPSBlbGVtZW50LmRhdGFzZXQuYW5tZWxkdW5nO1xuXG4gICAgYXhpb3MucG9zdCgnL2FkbWluL2NhdGVnb3J5L3N0b3JlJywge1xuICAgICAgY2F0ZWdvcnlfaWQ6ICtjYXRlZ29yeUlkLFxuICAgICAgYW5tZWxkdW5nX2lkOiArYW5tZWxkdW5nSWQsXG4gICAgICB2YWx1ZTogZWxlbWVudC5jaGVja2VkID8gMSA6IDBcbiAgICB9KVxuICAgIC50aGVuKGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgY29uc29sZS5sb2cocmVzcG9uc2UpO1xuICAgIH0pXG4gICAgLmNhdGNoKGZ1bmN0aW9uIChlcnJvcikge1xuICAgICAgY29uc29sZS5sb2coZXJyb3IpO1xuICAgIH0pO1xuXG4gICAgY29sb3JpemUoZWxlbWVudCk7XG5cbiAgfVxuXG4gIGZ1bmN0aW9uIGNvbG9yaXplKGVsZW1lbnQpIHtcbiAgICBpZihlbGVtZW50LmNoZWNrZWQpIHtcbiAgICAgIGVsZW1lbnQuY2xvc2VzdChcInRkXCIpLmNsYXNzTGlzdC5hZGQoXCJjaGVja2VkXCIpO1xuICAgIH0gZWxzZSB7XG4gICAgICBlbGVtZW50LmNsb3Nlc3QoXCJ0ZFwiKS5jbGFzc0xpc3QucmVtb3ZlKFwiY2hlY2tlZFwiKTtcbiAgICB9XG5cbiAgICB2YXIgY2F0ZWdvcnlJZCA9IGVsZW1lbnQuZGF0YXNldC5jYXRlZ29yeTtcbiAgICB2YXIgY291bnQgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKFwiLmNhdGVnb3J5Q2VsbC5jYXRlZ29yeVwiICsgY2F0ZWdvcnlJZCArIFwiLmNoZWNrZWRcIikubGVuZ3RoXG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXCJjYXRlZ29yeVRvdGFsXCIgKyBjYXRlZ29yeUlkKS5pbm5lckhUTUwgPSBjb3VudCArIFwiIFRlaWxuZWhtZXJcIjtcblxuICB9XG5cbiAgLy8gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZShcImNhdGVnb3J5QXNzaWdubWVudFwiKVxuICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcuY3VzdG9tX3NlbGVjdF9kbmQnKS5mb3JFYWNoKChhdXRvY29tcGxldGVFbGVtZW50KSA9PiB7XG4gICAgbmV3IFRvbVNlbGVjdChhdXRvY29tcGxldGVFbGVtZW50LCB7XG4gICAgICBwbHVnaW5zOiBbJ2RyYWdfZHJvcCcsICdyZW1vdmVfYnV0dG9uJ10sXG4gICAgfSk7XG4gIH0pO1xuXG59KTtcblxuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbImF4aW9zIiwiVG9tU2VsZWN0Iiwid2luZG93IiwiYWRkRXZlbnRMaXN0ZW5lciIsImNoZWNrYm94ZXMiLCJkb2N1bWVudCIsImdldEVsZW1lbnRzQnlDbGFzc05hbWUiLCJpIiwibGVuZ3RoIiwiZSIsInVwZGF0ZSIsInRhcmdldCIsImNvbG9yaXplIiwiY2xvc2VzdCIsImNoZWNrYm94IiwicXVlcnlTZWxlY3RvciIsImNoZWNrZWQiLCJlbGVtZW50IiwiY2F0ZWdvcnlJZCIsImRhdGFzZXQiLCJjYXRlZ29yeSIsImFubWVsZHVuZ0lkIiwiYW5tZWxkdW5nIiwicG9zdCIsImNhdGVnb3J5X2lkIiwiYW5tZWxkdW5nX2lkIiwidmFsdWUiLCJ0aGVuIiwicmVzcG9uc2UiLCJjb25zb2xlIiwibG9nIiwiZXJyb3IiLCJjbGFzc0xpc3QiLCJhZGQiLCJyZW1vdmUiLCJjb3VudCIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJnZXRFbGVtZW50QnlJZCIsImlubmVySFRNTCIsImZvckVhY2giLCJhdXRvY29tcGxldGVFbGVtZW50IiwicGx1Z2lucyJdLCJzb3VyY2VSb290IjoiIn0=
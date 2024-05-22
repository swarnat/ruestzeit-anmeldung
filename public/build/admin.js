"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["admin"],{

/***/ "./assets/admin.js":
/*!*************************!*\
  !*** ./assets/admin.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");

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
    axios__WEBPACK_IMPORTED_MODULE_0__["default"].post('/admin/category/store', {
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
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_axios_lib_axios_js"], () => (__webpack_exec__("./assets/admin.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYWRtaW4uanMiLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7QUFBMEI7QUFFMUJDLE1BQU0sQ0FBQ0MsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLFlBQVk7RUFDMUMsSUFBSUMsVUFBVSxHQUFHQyxRQUFRLENBQUNDLHNCQUFzQixDQUFDLG9CQUFvQixDQUFDO0VBRXRFLEtBQUssSUFBSUMsQ0FBQyxHQUFHLENBQUMsRUFBRUEsQ0FBQyxHQUFHSCxVQUFVLENBQUNJLE1BQU0sRUFBRUQsQ0FBQyxFQUFFLEVBQUU7SUFDMUNILFVBQVUsQ0FBQ0csQ0FBQyxDQUFDLENBQUNKLGdCQUFnQixDQUFDLFFBQVEsRUFBRSxVQUFDTSxDQUFDLEVBQUs7TUFFOUNDLE1BQU0sQ0FBQ0QsQ0FBQyxDQUFDRSxNQUFNLENBQUM7SUFDbEIsQ0FBQyxDQUFDO0lBRUZDLFFBQVEsQ0FBQ1IsVUFBVSxDQUFDRyxDQUFDLENBQUMsQ0FBQztJQUV2QkgsVUFBVSxDQUFDRyxDQUFDLENBQUMsQ0FBQ00sT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDVixnQkFBZ0IsQ0FBQyxPQUFPLEVBQUUsVUFBQ00sQ0FBQyxFQUFLO01BQzNELElBQUlLLFFBQVEsR0FBR0wsQ0FBQyxDQUFDRSxNQUFNLENBQUNJLGFBQWEsQ0FBQyxPQUFPLENBQUM7TUFDOUNELFFBQVEsQ0FBQ0UsT0FBTyxHQUFHLENBQUNGLFFBQVEsQ0FBQ0UsT0FBTztNQUVwQ04sTUFBTSxDQUFDSSxRQUFRLENBQUM7SUFDbEIsQ0FBQyxDQUFDO0VBQ0o7RUFFQSxTQUFTSixNQUFNQSxDQUFDTyxPQUFPLEVBQUU7SUFDdkIsSUFBSUMsVUFBVSxHQUFHRCxPQUFPLENBQUNFLE9BQU8sQ0FBQ0MsUUFBUTtJQUN6QyxJQUFJQyxXQUFXLEdBQUdKLE9BQU8sQ0FBQ0UsT0FBTyxDQUFDRyxTQUFTO0lBRTNDckIsNkNBQUssQ0FBQ3NCLElBQUksQ0FBQyx1QkFBdUIsRUFBRTtNQUNsQ0MsV0FBVyxFQUFFLENBQUNOLFVBQVU7TUFDeEJPLFlBQVksRUFBRSxDQUFDSixXQUFXO01BQzFCSyxLQUFLLEVBQUVULE9BQU8sQ0FBQ0QsT0FBTyxHQUFHLENBQUMsR0FBRztJQUMvQixDQUFDLENBQUMsQ0FDRFcsSUFBSSxDQUFDLFVBQVVDLFFBQVEsRUFBRTtNQUN4QkMsT0FBTyxDQUFDQyxHQUFHLENBQUNGLFFBQVEsQ0FBQztJQUN2QixDQUFDLENBQUMsU0FDSSxDQUFDLFVBQVVHLEtBQUssRUFBRTtNQUN0QkYsT0FBTyxDQUFDQyxHQUFHLENBQUNDLEtBQUssQ0FBQztJQUNwQixDQUFDLENBQUM7SUFFRm5CLFFBQVEsQ0FBQ0ssT0FBTyxDQUFDO0VBRW5CO0VBRUEsU0FBU0wsUUFBUUEsQ0FBQ0ssT0FBTyxFQUFFO0lBQ3pCLElBQUdBLE9BQU8sQ0FBQ0QsT0FBTyxFQUFFO01BQ2xCQyxPQUFPLENBQUNKLE9BQU8sQ0FBQyxJQUFJLENBQUMsQ0FBQ21CLFNBQVMsQ0FBQ0MsR0FBRyxDQUFDLFNBQVMsQ0FBQztJQUNoRCxDQUFDLE1BQU07TUFDTGhCLE9BQU8sQ0FBQ0osT0FBTyxDQUFDLElBQUksQ0FBQyxDQUFDbUIsU0FBUyxDQUFDRSxNQUFNLENBQUMsU0FBUyxDQUFDO0lBQ25EO0lBRUEsSUFBSWhCLFVBQVUsR0FBR0QsT0FBTyxDQUFDRSxPQUFPLENBQUNDLFFBQVE7SUFDekMsSUFBSWUsS0FBSyxHQUFHOUIsUUFBUSxDQUFDK0IsZ0JBQWdCLENBQUMsd0JBQXdCLEdBQUdsQixVQUFVLEdBQUcsVUFBVSxDQUFDLENBQUNWLE1BQU07SUFDaEdILFFBQVEsQ0FBQ2dDLGNBQWMsQ0FBQyxlQUFlLEdBQUduQixVQUFVLENBQUMsQ0FBQ29CLFNBQVMsR0FBR0gsS0FBSyxHQUFHLGFBQWE7RUFFekY7O0VBRUE7QUFDRixDQUFDLENBQUMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvYWRtaW4uanMiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IGF4aW9zIGZyb20gJ2F4aW9zJztcblxud2luZG93LmFkZEV2ZW50TGlzdGVuZXIoXCJsb2FkXCIsIGZ1bmN0aW9uICgpIHtcbiAgdmFyIGNoZWNrYm94ZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKFwiY2F0ZWdvcnlBc3NpZ25tZW50XCIpO1xuXG4gIGZvciAodmFyIGkgPSAwOyBpIDwgY2hlY2tib3hlcy5sZW5ndGg7IGkrKykge1xuICAgIGNoZWNrYm94ZXNbaV0uYWRkRXZlbnRMaXN0ZW5lcihcImNoYW5nZVwiLCAoZSkgPT4ge1xuXG4gICAgICB1cGRhdGUoZS50YXJnZXQpO1xuICAgIH0pO1xuXG4gICAgY29sb3JpemUoY2hlY2tib3hlc1tpXSk7XG5cbiAgICBjaGVja2JveGVzW2ldLmNsb3Nlc3QoXCJ0ZFwiKS5hZGRFdmVudExpc3RlbmVyKFwiY2xpY2tcIiwgKGUpID0+IHtcbiAgICAgIHZhciBjaGVja2JveCA9IGUudGFyZ2V0LnF1ZXJ5U2VsZWN0b3IoXCJpbnB1dFwiKTtcbiAgICAgIGNoZWNrYm94LmNoZWNrZWQgPSAhY2hlY2tib3guY2hlY2tlZFxuICAgICAgXG4gICAgICB1cGRhdGUoY2hlY2tib3gpXG4gICAgfSk7XG4gIH1cblxuICBmdW5jdGlvbiB1cGRhdGUoZWxlbWVudCkge1xuICAgIHZhciBjYXRlZ29yeUlkID0gZWxlbWVudC5kYXRhc2V0LmNhdGVnb3J5O1xuICAgIHZhciBhbm1lbGR1bmdJZCA9IGVsZW1lbnQuZGF0YXNldC5hbm1lbGR1bmc7XG5cbiAgICBheGlvcy5wb3N0KCcvYWRtaW4vY2F0ZWdvcnkvc3RvcmUnLCB7XG4gICAgICBjYXRlZ29yeV9pZDogK2NhdGVnb3J5SWQsXG4gICAgICBhbm1lbGR1bmdfaWQ6ICthbm1lbGR1bmdJZCxcbiAgICAgIHZhbHVlOiBlbGVtZW50LmNoZWNrZWQgPyAxIDogMFxuICAgIH0pXG4gICAgLnRoZW4oZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG4gICAgICBjb25zb2xlLmxvZyhyZXNwb25zZSk7XG4gICAgfSlcbiAgICAuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICBjb25zb2xlLmxvZyhlcnJvcik7XG4gICAgfSk7XG5cbiAgICBjb2xvcml6ZShlbGVtZW50KTtcblxuICB9XG5cbiAgZnVuY3Rpb24gY29sb3JpemUoZWxlbWVudCkge1xuICAgIGlmKGVsZW1lbnQuY2hlY2tlZCkge1xuICAgICAgZWxlbWVudC5jbG9zZXN0KFwidGRcIikuY2xhc3NMaXN0LmFkZChcImNoZWNrZWRcIik7XG4gICAgfSBlbHNlIHtcbiAgICAgIGVsZW1lbnQuY2xvc2VzdChcInRkXCIpLmNsYXNzTGlzdC5yZW1vdmUoXCJjaGVja2VkXCIpO1xuICAgIH1cblxuICAgIHZhciBjYXRlZ29yeUlkID0gZWxlbWVudC5kYXRhc2V0LmNhdGVnb3J5O1xuICAgIHZhciBjb3VudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoXCIuY2F0ZWdvcnlDZWxsLmNhdGVnb3J5XCIgKyBjYXRlZ29yeUlkICsgXCIuY2hlY2tlZFwiKS5sZW5ndGhcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChcImNhdGVnb3J5VG90YWxcIiArIGNhdGVnb3J5SWQpLmlubmVySFRNTCA9IGNvdW50ICsgXCIgVGVpbG5laG1lclwiO1xuXG4gIH1cblxuICAvLyBkb2N1bWVudC5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKFwiY2F0ZWdvcnlBc3NpZ25tZW50XCIpXG59KTtcblxuIl0sIm5hbWVzIjpbImF4aW9zIiwid2luZG93IiwiYWRkRXZlbnRMaXN0ZW5lciIsImNoZWNrYm94ZXMiLCJkb2N1bWVudCIsImdldEVsZW1lbnRzQnlDbGFzc05hbWUiLCJpIiwibGVuZ3RoIiwiZSIsInVwZGF0ZSIsInRhcmdldCIsImNvbG9yaXplIiwiY2xvc2VzdCIsImNoZWNrYm94IiwicXVlcnlTZWxlY3RvciIsImNoZWNrZWQiLCJlbGVtZW50IiwiY2F0ZWdvcnlJZCIsImRhdGFzZXQiLCJjYXRlZ29yeSIsImFubWVsZHVuZ0lkIiwiYW5tZWxkdW5nIiwicG9zdCIsImNhdGVnb3J5X2lkIiwiYW5tZWxkdW5nX2lkIiwidmFsdWUiLCJ0aGVuIiwicmVzcG9uc2UiLCJjb25zb2xlIiwibG9nIiwiZXJyb3IiLCJjbGFzc0xpc3QiLCJhZGQiLCJyZW1vdmUiLCJjb3VudCIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJnZXRFbGVtZW50QnlJZCIsImlubmVySFRNTCJdLCJzb3VyY2VSb290IjoiIn0=
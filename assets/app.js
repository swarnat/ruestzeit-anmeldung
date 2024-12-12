// import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import "./styles/app.scss";

import IMask from "imask";

window.addEventListener("load", function () {
  document.querySelector(".emailfield input").tabindex = -1;
  document.querySelector(".emailfield input").tabIndex = -1;
  document.querySelector(".emailfield input").setAttribute("tabIndex", -1);
  document.querySelector(".emailfield input").setAttribute("tabindex", -1);

  var timing = document.querySelector('[name="timing"]').value;
  document.querySelector('.emailfield input[rel="email"]').value =
    (timing * 3) / 2 + "@example.com";

  const element = document.getElementsByClassName("birthday_field");
  const maskOptions = {
    mask: Date,
    lazy: true,
  };
  const mask = IMask(element[0], maskOptions);

  var focusObj = {
    firstname: "0",
    lastname: "0",
    postalcode: "0",
  };

  document.querySelectorAll("[data-checkname]").forEach((element) => {
    element.addEventListener("focus", function (e) {
      const name = e.currentTarget.getAttribute("data-checkname");
      focusObj[name] = "2";

      const ctokenElement = document.querySelector('[name="ctoken"]');
      if (ctokenElement && ctokenElement.value.indexOf("0") != -1) {
        ctokenElement.value = Object.values(focusObj).join("");
      }
    });
  });
});

import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["birthdate"];

  connect() {
    this.initImask();
    this.initCheckToken();
    
    this.initTimings();
  }

  initTimings() {
    document.querySelector(".emailfield input").tabindex = -1;
    document.querySelector(".emailfield input").tabIndex = -1;
    document.querySelector(".emailfield input").setAttribute("tabIndex", -1);
    document.querySelector(".emailfield input").setAttribute("tabindex", -1);
  
    var timing = document.querySelector('[name="timing"]').value;
    document.querySelector('.emailfield input[rel="email"]').value =
      (timing * 3) / 2 + "@example.com";
  }

  initImask() {
    const elements = document.getElementsByClassName("birthday_field");
    
    for(const element of elements) {
        const maskOptions = {
        mask: Date,
        lazy: true,
        };
        const mask = IMask(element, maskOptions);
    }  
  }

  initCheckToken() {
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
  }
}

/*

import IMask from "imask";

window.addEventListener("load", function () {


  var focusObj = {
    firstname: "0",
    lastname: "0",
    postalcode: "0",
  };

  const roomRequestRadios = document.querySelectorAll(
    'input[name="anmeldung[roomRequest]"]'
  );
  var roomMateElement = document.getElementById('cont_roommate');

  if (roomRequestRadios && roomMateElement) {
    function updateRoomMateVisibility() {

      const selectedValue = document.querySelector(
        'input[name="anmeldung[roomRequest]"]:checked'
      ).value;
      if (selectedValue === "SINGLE") {
        roomMateElement.style.display = "none";
      } else {
        roomMateElement.style.display = "block";
      }
    }

    updateRoomMateVisibility();

    // if(roomRequest == "SINGLE") {

    // }

    roomRequestRadios.forEach((radio) => {
      radio.addEventListener("change", updateRoomMateVisibility);
    });
  }

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
*/

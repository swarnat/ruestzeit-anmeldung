// 
import "./bootstrap.js"

import './styles/admin.scss'; 

import TomSelect from 'tom-select'
import axios from 'axios'

// import axios from 'axios';
// import TomSelect from "tom-select/dist/js/tom-select.complete.min";

let currentTimeout = false;
let currentUpdateCategory = "";

window.addEventListener("load", function () {
  var checkboxes = document.getElementsByClassName("categoryAssignment");

  for (var i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener("change", (e) => {
      e.stopPropagation();
      
      console.log(currentUpdateCategory, e.target.dataset.category);

      if(currentTimeout !== false && currentUpdateCategory == e.target) {
        window.clearTimeout(currentTimeout);
      }

      currentUpdateCategory = e.target;

      currentTimeout = window.setTimeout(() => {
        update(e.target);
      }, 150);
    });

    colorize(checkboxes[i]);

    checkboxes[i].closest("td").addEventListener("click", (e) => {
      e.stopPropagation();

      var checkbox = e.currentTarget.querySelector("input");
      checkbox.checked = !checkbox.checked
      
      var event = new Event('change');
      checkbox.dispatchEvent(event);      
      // update(checkbox)
    });
  }

  function update(element) {
    var categoryId = element.dataset.category;
    var anmeldungId = element.dataset.anmeldung;

    axios.post('/admin/category/store', {
      category_id: +categoryId,
      anmeldung_id: +anmeldungId,
      value: element.checked ? 1 : 0
    })
    .then(function (response) {
      console.log(response);
    })
    .catch(function (error) {
      console.log(error);
    });

    colorize(element);

  }

  function colorize(element) {
    if(element.checked) {
      element.closest("td").classList.add("checked");
    } else {
      element.closest("td").classList.remove("checked");
    }

    var categoryId = element.dataset.category;
    var count = document.querySelectorAll(".categoryCell.category" + categoryId + ".checked").length
    document.getElementById("categoryTotal" + categoryId).innerHTML = count + " Teilnehmer";

  }

  // document.getElementsByClassName("categoryAssignment")
  document.querySelectorAll('.custom_select_dnd').forEach((autocompleteElement) => {
    new TomSelect(autocompleteElement, {
      plugins: ['drag_drop', 'remove_button'],
    });
  });

});


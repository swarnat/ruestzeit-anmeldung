// 
import axios from 'axios';
import TomSelect from "tom-select/dist/js/tom-select.complete.min";

window.addEventListener("load", function () {
  var checkboxes = document.getElementsByClassName("categoryAssignment");

  for (var i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener("change", (e) => {

      update(e.target);
    });

    colorize(checkboxes[i]);

    checkboxes[i].closest("td").addEventListener("click", (e) => {
      var checkbox = e.target.querySelector("input");
      checkbox.checked = !checkbox.checked
      
      update(checkbox)
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


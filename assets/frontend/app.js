import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import "./styles/app.scss";
import 'bulma/css/bulma.min.css';
import '@fortawesome/fontawesome-free/css/all.css';
import 'imask';

import 'toastr';


window.fillForm = function() {
    const randomId = Math.round(Math.random() * 10000)
    
    document.querySelector('[data-checkname="firstname"]').value = "FirstName " + randomId
    document.querySelector('[data-checkname="lastname"]').value = "LastName " + randomId
    document.querySelector('[name="anmeldung[phone]"]').value = "0123/45678"
    console.log(randomId)
  }

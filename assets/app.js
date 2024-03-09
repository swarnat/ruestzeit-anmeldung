import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';
import IMask from 'imask';

window.addEventListener('load', function () {
    document.querySelector('.emailfield input').setAttribute('tabindex', -1)
    
    var timing = document.querySelector('[name="timing"]').value 
    document.querySelector('.emailfield input').value = ((timing * 3) / 2) + "@example.com";


    const element = document.getElementsByClassName('birthday_field'); 
    const maskOptions =   {
        mask: Date,
        lazy: true
      };
    const mask = IMask(element[0], maskOptions);    
})
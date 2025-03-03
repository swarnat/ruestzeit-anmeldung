const uniqueSeed = Date.now().toString();
const getUniqueId = () => Cypress._.uniqueId(uniqueSeed);

describe('Ruestzeit Registration Form', () => {
  beforeEach(() => {
    // Mock the current time for consistent testing
    const now = new Date(2025, 2, 3).getTime(); // March 3, 2025
    cy.clock(now);
  });

  it('should successfully submit a registration form', () => {
    const uniqueId = getUniqueId();

    // Visit a specific ruestzeit page
    // Note: You'll need to replace this with a valid slug for your test environment
    cy.visit('/ruestzeit-test-event');

    // Check if the form is visible and registration is active
    cy.get('form.box').should('be.visible');
    
    // Fill out the form
    cy.get('input[name*="firstname"]').type('Test ' + uniqueId);
    cy.get('input[name*="lastname"]').type('User ' + uniqueId);
    cy.get('input[name*="phone"]').type('0123456789');
    cy.get('input[name$="email]"]').type('test@example.com');
    
    // Fill the birthdate field
    cy.get('input.birthday_field[name*="birthdate"]').type('01.01.2000');
    
    // Fill address information
    cy.get('input[name*="address"]').type('Test Street 123');
    cy.get('input[name*="postalcode"]').type('12345');
    cy.get('input[name*="city"]').type('Test City');
    
    // Check agreement checkboxes
    cy.get('input[name*="agb_agree"]').check();
    cy.get('input[name*="dsgvo_agree"]').check();
    
    // Handle anti-bot measures
    // 1. The hidden email field should be filled with a specific value based on the timing
    cy.window().then((win) => {
      const timing = win.document.querySelector('input[name="timing"]').value;
      const emailValue = (timing * 3) / 2 + '@example.com';
      cy.get('.emailfield input[rel="email"]').should('have.value', emailValue);
    });
    
    // 2. The ctoken field should be updated when certain fields are focused
    cy.get('input[data-checkname="firstname"]').focus();
    cy.get('input[data-checkname="lastname"]').focus();
    cy.get('input[data-checkname="postalcode"]').focus();
    cy.get('input[name="ctoken"]').should('have.value', '222');
    
    cy.debug()
    // Submit the form
    cy.get('button.is-success').click();
    
    // Verify success message
    cy.get('.toast-success').should('be.visible');
    cy.get('.toast-success').should('contain', 'Die Anmeldung wurde erfolgreich gespeichert');
  });

//   it('should validate required fields', () => {
//     // Visit a specific ruestzeit page
//     cy.visit('/ruestzeit-test-event');
    
//     // Submit the form without filling required fields
//     cy.get('button.is-success').click();
    
//     // Check for validation messages
//     cy.get('.notification.is-danger').should('be.visible');
//     cy.get('.notification.is-danger').should('contain', 'Es wurden Fehler im Formular festgestellt');
    
//     // Verify specific field validation
//     cy.get('input[name*="firstname"]').should('have.class', 'is-invalid');
//     cy.get('input[name*="lastname"]').should('have.class', 'is-invalid');
//     cy.get('input[name*="phone"]').should('have.class', 'is-invalid');
//     cy.get('input[name*="email"]').should('have.class', 'is-invalid');
//     cy.get('input.birthday_field[name*="birthdate"]').should('have.class', 'is-invalid');
//     cy.get('input[name*="address"]').should('have.class', 'is-invalid');
//     cy.get('input[name*="postalcode"]').should('have.class', 'is-invalid');
//     cy.get('input[name*="city"]').should('have.class', 'is-invalid');
//   });

//   it('should handle optional fields correctly', () => {
//     // Visit a specific ruestzeit page
//     cy.visit('/ruestzeit-test-event');
    
//     // Fill required fields
//     cy.get('input[name*="firstname"]').type('Test');
//     cy.get('input[name*="lastname"]').type('User');
//     cy.get('input[name*="phone"]').type('0123456789');
//     cy.get('input[name*="email"]').type('test@example.com');
//     cy.get('input.birthday_field[name*="birthdate"]').type('01.01.2000');
//     cy.get('input[name*="address"]').type('Test Street 123');
//     cy.get('input[name*="postalcode"]').type('12345');
//     cy.get('input[name*="city"]').type('Test City');
//     cy.get('input[name*="agb_agree"]').check();
//     cy.get('input[name*="dsgvo_agree"]').check();
    
//     // Fill optional fields if they exist
//     cy.get('textarea[name*="notes"]').type('Test notes for the registration');
    
//     // Check if meal type selection exists and select if it does
//     cy.get('body').then($body => {
//       if ($body.find('input[name*="mealtype"][value="VEGETARIAN"]').length > 0) {
//         cy.get('input[name*="mealtype"][value="VEGETARIAN"]').check();
//       }
//     });
    
//     // Check if room request selection exists and select if it does
//     cy.get('body').then($body => {
//       if ($body.find('input[name*="roomRequest"][value="SINGLE"]').length > 0) {
//         cy.get('input[name*="roomRequest"][value="SINGLE"]').check();
//       }
//     });
    
//     // Handle anti-bot measures as in the first test
//     cy.get('input[data-checkname="firstname"]').focus();
//     cy.get('input[data-checkname="lastname"]').focus();
//     cy.get('input[data-checkname="postalcode"]').focus();
    
//     // Submit the form
//     cy.get('button.is-success').click();
    
//     // Verify success message
//     cy.get('.toast-success').should('be.visible');
//   });

//   it('should handle repeat registration process', () => {
//     // Visit a specific ruestzeit page
//     cy.visit('/ruestzeit-test-event');
    
//     // Fill out the form with test data
//     cy.get('input[name*="firstname"]').type('Test');
//     cy.get('input[name*="lastname"]').type('User');
//     cy.get('input[name*="phone"]').type('0123456789');
//     cy.get('input[name*="email"]').type('test@example.com');
//     cy.get('input.birthday_field[name*="birthdate"]').type('01.01.2000');
//     cy.get('input[name*="address"]').type('Test Street 123');
//     cy.get('input[name*="postalcode"]').type('12345');
//     cy.get('input[name*="city"]').type('Test City');
//     cy.get('input[name*="agb_agree"]').check();
//     cy.get('input[name*="dsgvo_agree"]').check();
    
//     // Check the repeat process checkbox
//     cy.get('input[name="repeat_process"]').check();
    
//     // Handle anti-bot measures
//     cy.get('input[data-checkname="firstname"]').focus();
//     cy.get('input[data-checkname="lastname"]').focus();
//     cy.get('input[data-checkname="postalcode"]').focus();
    
//     // Submit the form
//     cy.get('button.is-success').click();
    
//     // Verify that we're back on the form page with some fields pre-filled
//     cy.get('form.box').should('be.visible');
//     cy.get('input[name*="phone"]').should('have.value', '0123456789');
//     cy.get('input[name*="email"]').should('have.value', 'test@example.com');
//     cy.get('input[name*="address"]').should('have.value', 'Test Street 123');
//     cy.get('input[name*="postalcode"]').should('have.value', '12345');
//     cy.get('input[name*="city"]').should('have.value', 'Test City');
    
//     // But firstname and lastname should be empty
//     cy.get('input[name*="firstname"]').should('have.value', '');
//     cy.get('input[name*="lastname"]').should('have.value', '');
//   });
});

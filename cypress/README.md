# Cypress Tests for Ruestzeit Registration Form

This directory contains Cypress tests for the Ruestzeit registration form functionality.

## Test Structure

The tests are organized to cover the following scenarios:

1. **Successful Form Submission**: Tests that a user can fill out the form correctly and submit it successfully.
2. **Form Validation**: Tests that the form properly validates required fields.
3. **Optional Fields Handling**: Tests that optional fields are handled correctly.
4. **Repeat Registration Process**: Tests the functionality to register multiple participants in sequence.

## Running the Tests

To run the Cypress tests, you need to have the application running locally. Then you can use the following commands:

```bash
# Open Cypress Test Runner (interactive mode)
npx cypress open

# Run tests headlessly
npx cypress run
```

## Test Configuration

The tests are configured to use `http://localhost:8000` as the base URL. If your development server runs on a different URL, you'll need to update the `baseUrl` in `cypress.config.js`.

## Important Notes

1. **Test Data**: The tests use mock data. In a real environment, you might want to use more realistic test data or even set up test fixtures.

2. **Anti-Bot Measures**: The form includes anti-bot measures that the tests handle:
   - A hidden email field that must be filled with a specific value
   - A token system that updates when certain fields are focused

3. **Test Event**: The tests are configured to visit `/ruestzeit-test-event`. You'll need to replace this with a valid event slug in your test environment.

## Customizing Tests

You may need to customize these tests based on your specific environment:

1. Update the event slug in the `cy.visit()` calls
2. Adjust selectors if the form structure changes
3. Modify expected validation messages if they differ in your environment

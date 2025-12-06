// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

Cypress.config('includeShadowDom', true)


// Prevent Sentry / Cloudflare / React errors from failing tests
Cypress.on('uncaught:exception', (err) => {
  return false;
});

Cypress.Commands.add('blockSentry', () => {
  cy.intercept('POST', '**/envelope/**', { statusCode: 204 }).as('sentryEnvelope')
  cy.intercept('POST', '**/sentry**', { statusCode: 204 }).as('sentryBlock')
})


// // ***********************************************
// // This example commands.js shows you how to
// // create various custom commands and overwrite
// // existing commands.
// //
// // For more comprehensive examples of custom
// // commands please read more here:
// // https://on.cypress.io/custom-commands
// // ***********************************************
// //
// //
// // -- This is a parent command --
// // Cypress.Commands.add('login', (email, password) => { ... })
// //
// //
// // -- This is a child command --
// // Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
// //
// //
// // -- This is a dual command --
// // Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
// //
// //
// // -- This will overwrite an existing command --
// // Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
// // cypress/support/commands.js

// // cypress/support/commands.js
// // Cypress.Commands.add('login', () => {
// //   cy.session('userSession', () => {
// //     cy.visit('/login');

// //     cy.get('input[name="username"]', { timeout: 10000 }).type(Cypress.env('USERNAME'));
// //     cy.get('input[name="password"]').type(Cypress.env('PASSWORD'), { log: false });
// //     cy.get('input#submitButton').click(); // fixed selector

// //     // confirm login success
// //     cy.url().should('not.include', '/login'); // or check for a dashboard element
// //   });
// // });

// // ===============================
// //  UNIVERSAL FORM HELPERS
// //  Invoice Ninja – React UI
// // ===============================

// // Type into a text field using its label (works for span and label)
// Cypress.Commands.add('typeField', (label, value) => {
//   cy.contains('span, label', label, { timeout: 8000 })
//     .closest('dt')
//     .next('dd')
//     .find('input, textarea')
//     .first()
//     .scrollIntoView()
//     .clear({ force: true })
//     .type(value, { force: true })
// })

// // Select from React-Select dropdown using label
// Cypress.Commands.add('selectDropdown', (label, option) => {
//   cy.contains('span, label', label, { timeout: 8000 })
//     .closest('dt')
//     .next('dd')
//     .find('[role="combobox"], .css-b62m3t-container, div.cursor-pointer')
//     .first()
//     .click({ force: true })

//   cy.contains('[role="option"], .css-1n7v3ny-option, .dropdown-item', option)
//     .click({ force: true })
// })

// // Toggle switches by label
// Cypress.Commands.add('toggleSwitch', (label) => {
//   cy.contains('span, label', label, { timeout: 8000 })
//     .closest('dt')
//     .next('dd')
//     .find('button, input[type="checkbox"], .switch, .toggle')
//     .first()
//     .click({ force: true })
// })

// // Save button
// Cypress.Commands.add('clickSave', () => {
//   cy.contains('button', 'Save').click({ force: true })
// })


// // =======================================
// // LOGIN COMMAND FOR INVOICE NINJA
// // =======================================

// // =======================================
// // LOGIN COMMAND (Label-based selectors)
// // =======================================

// Cypress.Commands.add('login', () => {
//   cy.session('invoice-ninja-session', () => {

//     cy.visit('https://app.invoicing.co/#/login')

//     cy.get('input[type="email"]').type('ahmedmohna4@gmail.com')
//     cy.get('input[type="password"]').type('ayesha2005')

//     cy.contains('button', 'Login').click({ force: true })

//     // Wait for dashboard
//     cy.url().should('include', '/dashboard')

//     // Close modal if appears
//     cy.get('body').then(($body) => {
//       if ($body.find('div[role="dialog"]').length > 0) {
//         cy.get('div[role="dialog"] svg').last().click({ force: true })
//       }
//     })

//   })
// })

// ───────────────────────────────────────────────
//  GLOBAL SETTINGS
// ───────────────────────────────────────────────

Cypress.config('includeShadowDom', true)

Cypress.on('uncaught:exception', () => false) // ignore Sentry / Cloudflare errors


// ───────────────────────────────────────────────
//  LOGIN COMMAND
// ───────────────────────────────────────────────

Cypress.Commands.add('login', () => {
  cy.visit('https://app.invoicing.co/#/login', { timeout: 100000, failOnStatusCode: false })


  // Email
  cy.contains('label', 'Email address', { timeout: 15000 })
    .parent()
    .find('input')
    .clear({ force: true })
    .type('ayesha@gmail.com', { force: true })

  // Password
  cy.contains('label', 'Password', { timeout: 15000 })
    .parent()
    .find('input')
    .clear({ force: true })
    .type('ayesha2005', { force: true })

  // Login button
  cy.contains('button', 'Login').click({ force: true })

  // Wait for dashboard
  cy.url({ timeout: 30000 }).should('include', '/#/dashboard')

  // // ───── FIXED POPUP CLOSER (NO MORE ERRORS) ─────
  // cy.get('div[id*="headlessui-dialog"]', { timeout: 3000 })
  //   .then(($dialogs) => {
  //     const visibleDialog = $dialogs.filter((i, el) =>
  //       Cypress.$(el).is(':visible')
  //     )

  //     if (visibleDialog.length === 1) {
  //       cy.wrap(visibleDialog)
  //         .find('svg[aria-hidden="true"]')
  //         .first()
  //         .click({ force: true })
  //     }
  //   })

  // Close any additional visible modals (language popup, 2nd overlay)
  // cy.get('div[id*="headlessui-dialog"]', { timeout: 1000 })
  //   .each(($dialog) => {
  //     if (Cypress.$($dialog).is(':visible')) {
  //       cy.wrap($dialog)
  //         .find('svg[aria-hidden="true"]')
  //         .first()
  //         .click({ force: true })
  //     }
  //   })

  cy.get('body').then(() => {
    for (let i = 0; i < 4; i++) {
      cy.get('body').type('{esc}', { force: true })
      cy.wait(200)
    }
  })
})



// ───────────────────────────────────────────────
//  TYPE FIELD COMMAND (ROBUST & FINAL)
// ───────────────────────────────────────────────

// Cypress.Commands.add('typeField', (label, value) => {
//   cy.contains('span.font-medium', label, { timeout: 10000 })
//     .scrollIntoView()
//     .should('be.visible')
//     .parents('dt')
//     .next('dd')
//     .find('input, textarea')
//     .first()
//     .as('field')       // <-- SAVE ALIAS so Cypress RE-QUERIES

//   cy.get('@field')
//     .clear({ force: true })

//   cy.get('@field')
//     .type(value, { force: true })
// })

// Cypress.Commands.add('typeField', (label, value) => {
//   cy.contains('span.font-medium, label', label, { timeout: 10000 })
//     .scrollIntoView()
//     .should('be.visible')
//     .parent()       // works for login
//     .find('input')
//     .first()
//     .clear({ force: true })
//     .type(value, { force: true })
// })

// // Type into a text field using its label (works for span and label)
// Cypress.Commands.add('typeField', (label, value) => {
//   cy.contains('span, label', label, { timeout: 8000 })
//     .closest('dt')
//     .next('dd')
//     .find('input, textarea')
//     .first()
//     .scrollIntoView()
//     .clear({ force: true })
//     .type(value, { force: true })
// })

Cypress.Commands.add('typeField', (label, value) => {
  cy.contains('span, label', label, { timeout: 8000 })
    .closest('dt')
    .next('dd')
    .find('input, textarea')
    .first()
    .scrollIntoView()
    .clear({ force: true })
    .type(value, { force: true });
});



// ───────────────────────────────────────────────
//  SELECT DROPDOWN (React-Select)
// ───────────────────────────────────────────────

Cypress.Commands.add('selectDropdown', (label, option) => {
  const regex = new RegExp(`^\\s*${label}\\s*\\*?\\s*$`, 'i')

  // Open dropdown
  cy.contains('span.font-medium', regex)
    .parents('dt')
    .next('dd')
    .find('[role="combobox"]')
    .click({ force: true })

  // Select option (react-select renders in menu-list portal)
  cy.get('.css-1n7v3ny-option, [role="option"]').contains(option).click({ force: true })
})



// ───────────────────────────────────────────────
//  TOGGLE SWITCH COMMAND
// ───────────────────────────────────────────────

// Cypress.Commands.add('toggleSwitch', (label) => {
//   cy.contains('span.font-medium', label, { timeout: 10000 })
//     .parents('dt')
//     .next('dd')
//     .find('button, input[type="checkbox"], .switch, .toggle')
//     .first()
//     .click({ force: true })
// })


// Cypress.Commands.add('toggleSwitch', (label) => {
//   cy.contains('span.font-medium', label, { timeout: 10000 })
//     .parents('dt')
//     .next('dd')
//     .find('button, input[type="checkbox"], .switch, .toggle')
//     .first()
//     .click({ force: true })
// })

Cypress.Commands.add('toggleSwitch', (label) => {
  cy.contains('span, label', label, { timeout: 8000 })
    .closest('dt')
    .next('dd')
    .find('button[role="switch"]')
    .click({ force: true });
});



// ───────────────────────────────────────────────
//  CLICK SAVE BUTTON
// ───────────────────────────────────────────────

Cypress.Commands.add('clickSave', () => {
  cy.contains('button', 'Save', { timeout: 10000 })
    .scrollIntoView()
    .click({ force: true })
})


Cypress.Commands.add('selectTypeAhead', (label, value) => {
  cy.contains('span.font-medium', label)
    .parents('dt')
    .next('dd')
    .find('input[id*="react-select"], [role="combobox"]')
    .first()
    .click({ force: true })
    .type(value + '{enter}', { delay: 0 })
})


// Cypress.Commands.add('selectReactByLabel', (label, value) => {
//   cy.contains('label', label)
//     .should('be.visible')
//     .next('div')                           // ← the dropdown wrapper
//     .find('.css-b62m3t-container')         // ← the actual React select
//     .should('exist')
//     .click({ force: true });

//   cy.get('input[role="combobox"][aria-autocomplete="list"]')
//     .type(value + '{enter}', { force: true });
// });

Cypress.Commands.add('selectFirstReactSelect', (value) => {
  cy.get('.css-b62m3t-container:visible')
    .first()
    .click({ force: true });

  cy.get('input[role="combobox"]')
    .filter(':visible')
    .first()
    .type(value + '{enter}', { force: true });
});

Cypress.Commands.add('selectReactByIndex', (index, value) => {
  // Click the dropdown
  cy.get('.css-b62m3t-container:visible')
    .eq(index)
    .click({ force: true });

  // Type the option
  cy.get('input[role="combobox"]:visible')
    .eq(index)
    .type(value + '{enter}', { force: true });
});

Cypress.Commands.add('selectReactOption', (label, option) => {
  cy.contains('span, label', label)
    .closest('dt')
    .next('dd')
    .within(() => {
      cy.get('input[role="combobox"]').click({ force: true });
    });

  cy.contains('[role="option"]', option, { timeout: 8000 })
    .click({ force: true });
});

// Select a React/HeadlessUI Combobox by index
Cypress.Commands.add('selectComboByIndex', (index, value) => {
  cy.get('input[data-testid="combobox-input-field"]')
    .eq(index)
    .click({ force: true })
    .type(value + '{enter}', { force: true });
});

// Type into normal input/textareas by index
Cypress.Commands.add('typeInputByIndex', (index, value) => {
  cy.get('input:not([role="combobox"]), textarea')
    .filter(':visible')
    .eq(index)
    .scrollIntoView()
    .clear({ force: true })
    .type(value, { force: true });
});

Cypress.Commands.add('openCombo', (index) => {
  cy.get('button[data-testid="combobox-action-button"]').eq(index).click();
});

Cypress.Commands.add('selectCombo', (index, text) => {
  cy.get('button[data-testid="combobox-action-button"]').eq(index).click();
  cy.get('input[data-testid="combobox-input-field"]').eq(index)
    .click({ force: true })
    .type(`${text}{enter}`, { force: true });
});

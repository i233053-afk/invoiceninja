/// <reference types="cypress" />

// ================================
// Helper: React-Select by index
// ================================
function selectReactIndex(index, option) {
  cy.get('.css-b62m3t-container')
    .eq(index)
    .scrollIntoView()
    .click({ force: true });

  cy.get('input[role="combobox"][aria-autocomplete="list"]')
    .eq(index)
    .type(option + '{enter}', { force: true });
}

// ================================
// Helper: React/HeadlessUI by label
// ================================
function selectReactOption(label, option) {
  cy.contains('span.font-medium, label', label, { timeout: 10000 })
    .closest('dt')
    .next('dd')
    .within(() => {
      cy.get('input[role="combobox"], input[id^="react-select"], input[data-testid="combobox-input-field"]')
        .filter(':visible')
        .first()
        .click({ force: true })
        .clear({ force: true })
        .type(option + '{enter}', { force: true });
    });
}

// ================================
// Helper: type using label
// ================================
function typeByLabel(label, value) {
  cy.get('dt > span.font-medium')
    .contains(label, { timeout: 10000 })
    .scrollIntoView()
    .parents('dt')
    .next('dd')
    .find('input, textarea')
    .filter(':visible')
    .first()
    .click({ force: true })
    .clear({ force: true })
    .type(value, { force: true });
}
function typeByIndex(index, value) {
  cy.get('dt')
    .eq(index)
    .next('dd')
    .find('input, textarea')
    .filter(':visible')
    .first()
    .scrollIntoView()
    .click({ force: true })
    .clear({ force: true })
    .type(value, { force: true });
}

// ================================
// TEST SUITE – EXPENSES MTS (6 TC)
// ================================
describe('Expenses Form – Minimal Test Set', () => {

  beforeEach(() => {
    cy.login();
    cy.visit('https://app.invoicing.co/#/expenses/create');

    cy.get('body').type('{esc}', { force: true });
    cy.contains('span', 'Vendor', { timeout: 20000 }).should('be.visible');
  });

  // // ===========================================
  // // TC01 – Missing Vendor
  // // ===========================================
   it('TC01 – Missing Vendor', () => {
    cy.contains('button', 'Save').click({ force: true });

    cy.url().should('include', 'https://app.invoicing.co/#/expenses/create')


    cy.screenshot('TC_01');
  });

//   // ===========================================
//   // TC02 – Missing Currency
//   // ===========================================
   it('TC02 – Missing Currency', () => {

    selectReactIndex(0, 'Vendor1');  // vendor
   // typeByLabel('Amount', '10');
 typeByIndex(6, '10');
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Successfully created expense', { matchCase: false })
  .should('be.visible')
    cy.screenshot('TC02');
   });

  // // ===========================================
  // // TC03 – Amount = -1
  // // ===========================================
   it('TC03 – Reject negative Amount (-1)', () => {

    selectReactIndex(0, 'Vendor1');  // vendor
    typeByIndex(6, '-1');
    cy.contains('button', 'Save').click({ force: true });
    cy.contains('expense', { matchCase: false })
   .should('be.visible')
    

    cy.screenshot('TC_03');
   });

  // // ===========================================
  // // TC04 – Amount = abc
  // // ===========================================
   it('TC04 – Reject non-numeric Amount (abc)', () => {

    selectReactIndex(0, 'Vendor1');  // vendor
    typeByIndex(6, '-1');

    cy.contains('button', 'Save').click({ force: true });

    cy.url().should('include', 'https://app.invoicing.co/#/expenses/create')

    cy.screenshot('TC_04');
   });

  // // ===========================================
  // // TC05 – Amount = huge 
  // // ===========================================
   it('TC05 – Reject extremely large Amount', () => {

    selectReactIndex(0, 'Vendor1');  // vendor
    typeByIndex(6, '10000000000');

    cy.contains('button', 'Save').click({ force: true });

    cy.url().should('include', 'https://app.invoicing.co/#/expenses/create')

    cy.screenshot('TC_05');
   });

  // ===========================================
  // TC06 – Fully Valid Submission
  // ===========================================
  it('TC06 – Valid Expense', () => {

    typeByIndex(1, 'Vendor1');
    typeByIndex(2, 'Ayesha');
    typeByIndex(3, 'Project1');
    typeByIndex(5, '1000');
    typeByIndex(7, 'Pakistani Rupee (PKR)')

    cy.contains('button', 'Save').click({ force: true });

    cy.url().should('include', 'https://app.invoicing.co/#/expenses/create')
    cy.screenshot('TC_06');
  });

});

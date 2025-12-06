/// <reference types="cypress" />

// ========================================
// Helper for React Select dropdowns
// ========================================
function selectReactOption(index, option) {
  cy.get('.css-b62m3t-container')
    .filter(':visible')
    .should('have.length.gte', index + 1)
    .eq(index)
    .scrollIntoView()
    .click({ force: true });

  cy.get('input[role="combobox"]')
    .filter(':visible')
    .first()
    .type(option + '{enter}', { force: true });
}

// ========================================
// Test Suite
// ========================================
describe('Credits Form – Invalid Input Minimal Set', () => {

  beforeEach(() => {
    cy.login();
    cy.visit('https://app.invoicing.co/#/credits/create');

    cy.get('body').type('{esc}', { force: true });
    cy.get('body').type('{esc}', { force: true });

    cy.get('div[id*="headlessui-dialog"]').should('not.exist');

    cy.contains('span.font-medium', 'Client', { timeout: 10000 }).should('be.visible');
  });

  function typeByLabel(label, value) {
    cy.contains('span.font-medium', label, { timeout: 10000 })
      .scrollIntoView()
      .parents('dt')
      .next('dd')
      .find('input')
      .should('be.visible')
      .then(($input) => {
        cy.wrap($input).clear({ force: true });
        cy.wrap($input).type(value, { force: true });
      });
  }

  // ============================================================
  // TC01 – Partial/Deposit = -1
  // ============================================================
  it('TC01 – Reject negative Partial/Deposit (-1)', () => {
    cy.selectFirstReactSelect('Ayesha');

    typeByLabel('Partial/Deposit', '-1');

    cy.contains('button', 'Save').click({ force: true });
    cy.wait(1000);

    cy.contains("Partial", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_01');
  });

  // ============================================================
  // TC02 – Partial/Deposit = abc
  // ============================================================
  it('TC02 – Reject non-numeric Partial/Deposit ("abc")', () => {
    cy.selectFirstReactSelect('Ayesha');

    typeByLabel('Partial/Deposit', 'abc');

    cy.contains('button', 'Save').click({ force: true });
    cy.wait(1000);

    cy.contains("Partial", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_02');
  });

  // ============================================================
  // TC03 – Credit Number invalid "@@@###"
  // ============================================================
  it('TC03 – Reject invalid Credit Number special chars', () => {
    cy.selectFirstReactSelect('Ayesha');
    typeByLabel('Credit Number', '@@@###');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains("Credit", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_03');
  });

  // ============================================================
  // TC04 – Credit Number empty
  // ============================================================
  it('TC04 – Reject empty Credit Number', () => {
    cy.selectFirstReactSelect('Ayesha');

    // leave empty
    
    cy.contains('button', 'Save').click({ force: true });

    cy.contains("Credit", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_04');
  });

  // ============================================================
  // TC05 – PO # long string
  // ============================================================
  it('TC05 – Reject oversized PO # (500 chars)', () => {

    const longString = 'X'.repeat(500);
    typeByLabel('PO #', longString);

    cy.contains('button', 'Save').click({ force: true });

    cy.contains("Credit", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_05');
  });

  // ============================================================
  // TC06 – Discount = -5
  // ============================================================
  it('TC06 – Reject negative discount (-5)', () => {
    cy.selectFirstReactSelect('Ayesha');

    cy.contains('Discount')
      .parents('dt')
      .next('dd')
      .find('input[inputmode="numeric"]')
      .type('-5', { force: true });

    cy.contains('button', 'Save')
      .filter(':visible')
      .first()
      .click({ force: true });

    cy.screenshot('TC_06');
  });

});

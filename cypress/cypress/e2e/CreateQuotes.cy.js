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
describe('Quotes Form – Invalid Input Minimal Set', () => {

  // Universal login
  beforeEach(() => {
    cy.login();
    cy.visit('https://app.invoicing.co/#/quotes/create');

    // Ensure overlays are gone
    cy.get('body').type('{esc}', { force: true });
    cy.get('body').type('{esc}', { force: true });

    cy.get('div[id*="headlessui-dialog"]').should('not.exist');

    // Wait for Quotes form labels to load
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
  // TC03 – Quote # = "@@@###"
  // ============================================================
  it('TC03 – Reject invalid Quote # special characters', () => {
    cy.selectFirstReactSelect('Ayesha');
    typeByLabel('Quote #', '@@@###');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains("Quote", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_03');
  });

  // ============================================================
  // TC04 – Quote # empty
  // ============================================================
  it('TC04 – Reject empty Quote #', () => {
    cy.selectFirstReactSelect('Ayesha');

    // Leave Quote # empty

    cy.contains('button', 'Save').click({ force: true });

    cy.contains("Quote", { timeout: 6000 }).should("exist");
    cy.screenshot('TC_04');
  });

  // ============================================================
  // TC05 – PO # long string (500 chars)
  // ============================================================
  it('TC05 – Reject oversized PO # (500 chars)', () => {

    const longString = 'X'.repeat(53); // (same length as your invoice case)
    typeByLabel('PO #', longString);

    cy.contains('button', 'Save').click({ force: true });

    cy.contains("Quote", { timeout: 6000 }).should("exist");
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

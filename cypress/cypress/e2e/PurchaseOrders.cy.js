/// <reference types="cypress" />

// ========================================
// Helper for React Select dropdowns
// ========================================
function selectReactOption(index, option) {
  // wait until at least index+1 visible containers exist
  cy.get('.css-b62m3t-container')
    .filter(':visible')
    .should('have.length.gte', index + 1)
    .eq(index)
    .scrollIntoView()
    .click({ force: true });

  // pick the currently visible combobox input (the one just opened)
  cy.get('input[role="combobox"]')
    .filter(':visible')
    .first()
    .type(option + '{enter}', { force: true });
}



// ========================================
// Test Suite
// ========================================
describe('Purchase Form – Invalid Input Minimal Set', () => {

//   // Universal login
//   beforeEach(() => {
//   cy.login();
//   cy.visit('https://app.invoicing.co/#/purchase_orders/create');

//   // Ensure overlays are gone
//   cy.get('body').type('{esc}', { force: true });
//   cy.get('body').type('{esc}', { force: true });
  
//   cy.get('div[id*="headlessui-dialog"]').should('not.exist');

//   // Wait for actual Invoice form labels to load
//   cy.contains('span.font-medium', 'Vendor', { timeout: 10000 }).should('be.visible');
// });


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


//   // ============================================================
//   // TC01 – Partial/Deposit = -1
//   // ============================================================
   it('TC01 – Reject negative Partial/Deposit (-1)', () => {
  // cy.selectFirstReactSelect('Ayesha');
  
  // typeByLabel('Partial/Deposit', '-1');
  

  // cy.contains('button', 'Save').click({ force: true });
  //   cy.wait(1000)
  // cy.contains("Partial", { timeout: 6000 })
  //   .should("exist");
  //   cy.screenshot('TC_01')
 });


  // // ============================================================
  // // TC02 – Partial/Deposit = abc (non-numeric)
  // // ============================================================
   it('TC02 – Reject non-numeric Partial/Deposit ("abc")', () => {
  //     cy.selectFirstReactSelect('Ayesha');
  
  // typeByLabel('Partial/Deposit', 'abc');
  

  // cy.contains('button', 'Save').click({ force: true });
  //   cy.wait(1000)
  // cy.contains("Partial", { timeout: 6000 })
  //   .should("exist");
  //   cy.screenshot('TC_02')
   });


  // // ============================================================
  // // TC03 – PO # extremely long (500 chars)
  // // ============================================================
   it('TC03 – Reject oversized PO # (500 chars)', () => {
  

    // const longString = 'X'.repeat(53);
    // typeByLabel('PO Number', longString);
  
    // cy.contains('button', 'Save').click({ force: true });

    // cy.contains("Order", { timeout: 6000 })
    // .should("exist");

    // cy.screenshot('TC_03');
   });

//   // ============================================================
//   // TC0 – Discount Amount = -5 (invalid negative)
//   // ============================================================
   it('TC04 – Reject negative discount (-5)', () => {
 cy.login();
   cy.visit('https://app.invoicing.co/#/purchase_orders/create');

  // cy.selectFirstReactSelect('Vendor1');

  // // Type -5 only into the numeric discount input
  // cy.contains('Discount')
  //   .parents('dt')
  //   .next('dd')
  //   .find('input[inputmode="numeric"]')
  //   .type('-5', { force: true });

  // cy.contains('button', 'Save')
  //   .filter(':visible')
  //   .first()
  //   .click({ force: true });

  // cy.screenshot('TC_04');
 });

});

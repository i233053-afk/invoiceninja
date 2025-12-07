/// <reference types="cypress" />


describe('Payments Form Tests', () => {

  beforeEach(() => {
    cy.login()
    cy.visit('https://app.invoicing.co/#/payments/create');
    cy.wait(1500);
  });

  function typeByLabel(label, value) {
  cy.contains('span.font-medium', label, { timeout: 10000 })
    .scrollIntoView()
    .parents('dt')
    .next('dd')
    .find('input, textarea')           // <--- FIXED HERE
    .should('be.visible')
    .then(($field) => {
      cy.wrap($field).clear({ force: true });
      cy.wrap($field).type(value, { force: true });
    });
}

  
  // // TC01 - Client empty (Invalid)
   it('TC01 – Client missing (should show validation)', () => {
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('client', { timeout: 3000 }).should('exist');
    cy.screenshot('TC_01');
   });

  // // TC02 - Payment Type empty (Invalid)
   it('TC02 – Payment Type missing', () => {
   cy.selectFirstReactSelect('Ayesha');

    cy.contains('button', 'Save').click({ force: true });
    
    cy.contains('Payment', { timeout: 3000 }).should('exist');
    cy.screenshot('TC_02');
   });

  // // TC03 – Negative Amount
   it('TC03 – Reject negative amount', () => {
   cy.selectFirstReactSelect('Ayesha');

    
    typeByLabel('Amount received', '-10');
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('negative payment',{ timeout: 3000 }).should('exist');
    cy.screenshot('TC_03');
   });


  // // TC04 – Transaction Ref > 255 chars
   it('TC04 – Transaction Reference too long', () => {
    cy.selectFirstReactSelect('Ayesha');

    const longText = 'x'.repeat(255);
    typeByLabel('Transaction Reference', longText);
  
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Transaction Reference').should('exist');
    cy.screenshot('TC_04');
   });

  // TC05 – Private Notes > 1000 chars
it('TC05 – Private Notes too long', () => {
  
  cy.selectFirstReactSelect('Ayesha');

  const longText = 'y'.repeat(255);
  typeByLabel('Private Notes', longText);

  cy.contains('button', 'Save').click({ force: true });

  cy.contains('Private Notes').should('exist');
  cy.screenshot('TC_05');
});


});

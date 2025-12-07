// Type into any text input or textarea located by dt index
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

// Select react/headlessui dropdown (Type, Currency, Bank Account)
function selectDropdownByIndex(index, option) {
  cy.get('dt')
    .eq(index)
    .next('dd')
    .find('input[role="combobox"], input[data-testid="combobox-input-field"], input[id^="react-select"]')
    .filter(':visible')
    .first()
    .scrollIntoView()
    .click({ force: true })      // open dropdown
    .type(option + '{enter}', { force: true });
}

describe('Transactions – Minimal Invalid Test Set', () => {

  beforeEach(() => {
    cy.login()
    cy.visit('https://app.invoicing.co/#/transactions/create');
  });

  // ------------------------------------------------------------------
  // Helpers
  // ------------------------------------------------------------------

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

  function selectDropdownByIndex(index, option) {
    cy.get('dt')
      .eq(index)
      .next('dd')
      .find(
        'input[role="combobox"], input[data-testid="combobox-input-field"], input[id^="react-select"]'
      )
      .filter(':visible')
      .first()
      .scrollIntoView()
      .click({ force: true })
      .type(option + '{enter}', { force: true });
  }

  // // ------------------------------------------------------------------
  // // TC01 – Missing Amount
  // // ------------------------------------------------------------------
   it('TC01 – Missing Amount', () => {
    
    selectFirstReactSelect('Deposit');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Transaction', { matchCase: false }).should('be.visible');

    cy.screenshot('TC_01');
   });

  // // ------------------------------------------------------------------
  // // TC02 – Non-numeric Amount
  // // ------------------------------------------------------------------
   it('TC02 – Non-numeric Amount (abc)', () => {
     cy.selectFirstReactSelect('Deposit');
     typeByIndex(2, 'abc');          // invalid
     typeByIndex(4, '1');
     cy.contains('button', 'Save').click({ force: true });

     cy.contains('Transaction', { matchCase: false }).should('be.visible');

     cy.screenshot('TC_02');
  });

  // ------------------------------------------------------------------
  // TC03 – Negative Amount
  // ------------------------------------------------------------------
  it('TC03 – Negative Amount (-10)', () => {
    cy.selectFirstReactSelect('Deposit');
    typeByIndex(2, '-10');          // invalid
    typeByIndex(4, '1');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Transaction', { matchCase: false }).should('be.visible');

    cy.screenshot('TC_03');
  });


  // ------------------------------------------------------------------
  // TC04 – Missing Bank Account
  // ------------------------------------------------------------------
  it('TC04 – Missing Bank Account', () => {
    cy.selectFirstReactSelect('Deposit');
    typeByIndex(2, '10');          // invalid

    // Bank Account missing

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('The bank integration id field is required.', { matchCase: false }).should('be.visible');

    cy.screenshot('TC_04');
  });


  // ------------------------------------------------------------------
  // TC05 – All Fields Empty
  // ------------------------------------------------------------------
  it('TC05 – All Required Fields Empty', () => {
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Type', { matchCase: false }).should('be.visible');
    cy.contains('Currency', { matchCase: false }).should('be.visible');
    cy.contains('Bank Account', { matchCase: false }).should('be.visible');

    cy.screenshot('TC_05');
  });

});

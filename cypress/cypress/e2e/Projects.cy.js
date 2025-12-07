function typeByIndex(index, value) {
  cy.get('dt')
    .eq(index)
    .next('dd')
    .find('input, textarea')
    .filter(':visible')
    .first()
    .scrollIntoView()
    .click({ force: true })
    .then(($input) => {
      if (value === '{backspace}') {
        cy.wrap($input).type('{backspace}', { force: true });
      } else {
        cy.wrap($input)
          .clear({ force: true })
          .invoke('val', value)
          .trigger('input')
          .trigger('change');
      }
    });
}



function selectFirstReactSelect(value) {
  cy.get('input[data-testid="combobox-input-field"][role="combobox"]')
    .filter(':visible')
    .first()
    .click({ force: true })
    .clear({ force: true })
    .type(value, { force: true });

  // Press Enter to select first option
  cy.get('input[data-testid="combobox-input-field"][role="combobox"]')
    .filter(':visible')
    .first()
    .type('{enter}', { force: true });
}


describe('Projects - Create Project', () => {

  
  beforeEach(() => {
    cy.login()
    cy.visit('https://app.invoicing.co/#/projects/create');
  });

   it('TC01 – Create Project with minimal valid data', () => {
   
    selectFirstReactSelect('Client1');
    typeByIndex(0,'A');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'5');
    typeByIndex(5,'10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Project', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_01');
 });


  // // TC02 – Missing Project Name
   it('TC02 – Missing Project Name', () => {
    selectFirstReactSelect('Client1');
    typeByIndex(0, '{backspace}');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'5');
    typeByIndex(5,'10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('required', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_02');
   });

  // // TC03 – Missing Client
   it('TC03 – Missing Client', () => {
    typeByIndex(0,'A');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'5');
    typeByIndex(5,'10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('required', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_03');
   });

  // // TC04 – Invalid Budgeted Hours (non-numeric)
   it('TC04 – Invalid Budgeted Hours', () => {
    selectFirstReactSelect('Client1');
    typeByIndex(0,'A');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'abc');
    typeByIndex(5,'10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Project', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_04');
   });

  // // TC05 – Project Name too long
   it('TC05 – Project Name exceeds limit', () => {
    const longName = 'X'.repeat(255);

    selectFirstReactSelect('Client1');
    typeByIndex(0,longName);
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'5');
    typeByIndex(5,'10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Project', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_05');
  });

  // // TC06 – Negative Task Rate
   it('TC06 – Negative Task Rate', () => {
    selectFirstReactSelect('Client1');
    typeByIndex(0,'A');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'5');
    typeByIndex(5,'-10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Project', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_06');
   });

  // TC07 – Invalid Date Format
  it('TC07 – Invalid Date Format', () => {
    selectFirstReactSelect('Client1');
    typeByIndex(0,'A');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '04/12/2025');
    typeByIndex(4,'5');
    typeByIndex(5,'10');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Project', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_07');
  });

  // // TC08 – All valid fields
  it('TC08 – Create Project with all valid fields', () => {
    
    selectFirstReactSelect('Client1');
    typeByIndex(0,'Full Project');
    typeByIndex(2,'Ayesha');
    typeByIndex(3, '2025-04-12');
    typeByIndex(4,'120');
    typeByIndex(5,'75');
    typeByIndex(6,'Public Notes');
    typeByIndex(7,'Private Notes');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Project', { matchCase: false }).should('be.visible');
    cy.screenshot('TC_08');
  });

});

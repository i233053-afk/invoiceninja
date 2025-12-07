/// <reference types="cypress" />

const openCombo = (index) => {
  cy.get('[role="combobox"]').eq(index).click();  // click wrapper
};

const chooseCombo = (index, text) => {
  openCombo(index);
  cy.get('input[data-testid="combobox-input-field"]').eq(index)
    .type(`${text}{enter}`, { force: true });
};


// Helpers based on your form structure
const selectCombobox = (index, value) => {
  cy.get('input[data-testid="combobox-input-field"]')
    .eq(index)
    .click()
    .type(`${value}{enter}`);
};

const typeByIndex = (index, text) => {
  cy.get('input, textarea').eq(index).clear().type(text);
};

describe('Invoice Ninja – Task Form Validation Suite', () => {

  beforeEach(() => {
    cy.login()
    cy.visit('https://app.invoicing.co/#/tasks/create');        // user logged in (precondition)
  });

  // --------------------------------------
  // TC01 – Required Project empty
  // --------------------------------------
   it('TC01 – Reject empty Project (required field)', () => {
    chooseCombo(0, 'Client1');

    // ASSIGNED USER — combobox index 2
    //chooseCombo(2, 'Admin');
    // Project is combobox index 1 → leave EMPTY (do not touch)
    // selectCombobox(0, 'Client A');           // client optional
    // selectCombobox(2, 'Admin');              // assigned user optional
    typeByIndex(4, '100');                   // rate normal
    typeByIndex(6, 'Valid description');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('Task', { matchCase: false })  
      .should('be.visible'); // validation toast or message

    cy.screenshot('TC_01');
   });

  // --------------------------------------
  // TC02 – Negative rate
  // --------------------------------------
   it('TC02 – Reject negative Rate (-5)', () => {

    selectCombobox(1, 'Full Project');          // valid project
    typeByIndex(4, '-5');                    // rate (invalid negative)
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('rate', { matchCase: false })
      .should('be.visible');

    cy.screenshot('TC_02');
  });

  it('TC03 – Reject End Time earlier than Start Time (React-safe)', () => {

  // Select Project
  selectCombobox(1, 'Full Project');

  // Click "+ Add Item"
  cy.contains('span', 'Add Item').click({ force: true });

  // Alias first row
  cy.get('table tbody tr').first().as('firstRow');

  // ---- Start Date ----
  cy.get('@firstRow').find('input[type="date"]').first().click({ force: true });
  cy.get('@firstRow').find('input[type="date"]').first().clear({ force: true });
  cy.get('@firstRow').find('input[type="date"]').first().type('2025-12-05', { force: true });

  // ---- Start Time ----
  cy.get('@firstRow').find('input[type="time"]').first().click({ force: true });
  cy.get('@firstRow').find('input[type="time"]').first().clear({ force: true });
  cy.get('@firstRow').find('input[type="time"]').first().type('15:00:00', { force: true });

  // ---- End Time (invalid) ----
  cy.get('@firstRow').find('input[type="time"]').eq(1).click({ force: true });
  cy.get('@firstRow').find('input[type="time"]').eq(1).clear({ force: true });
  cy.get('@firstRow').find('input[type="time"]').eq(1).type('14:00:00', { force: true });

  // ---- Save ----
  cy.contains('button', 'Save').click({ force: true });

  // ---- Validation ----
  cy.contains(/overlapping|end/i, { matchCase: false }).should('be.visible');

  cy.screenshot('TC_03');
 });



  // --------------------------------------
  // TC04 – Task Number 256 chars
  // --------------------------------------
  it('TC04 – Reject Task Number over max length (256 chars)', () => {
    const long256 = 'A'.repeat(256);
      chooseCombo(0, 'Client1');
    
    typeByIndex(3, long256);                   
    typeByIndex(4, '1');                   
    selectCombobox(3, 'Backlog');          // valid status
    cy.get('textarea').type('OK');         // minimal description
    selectCombobox(1, 'Full Project');       // project required

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('task', { matchCase: false })  
      .should('be.visible');

    cy.screenshot('TC_04');
   });

  // --------------------------------------
  // TC05 – Non-numeric rate "abc"
  // --------------------------------------
   it('TC05 – Reject non-numeric Rate (abc)', () => {

    selectCombobox(1, 'Full Project');          // valid project
    typeByIndex(4, 'abc');                    // rate (invalid negative)
    cy.contains('button', 'Save').click({ force: true });

    cy.contains('rate', { matchCase: false })
      .should('be.visible');

    cy.screenshot('TC_05');
   });

  // --------------------------------------
  // TC06 – Extremely long description (UB+1)
  // --------------------------------------
   it('TC06 – Reject overly long description', () => {

    selectCombobox(1, 'Full Project'); 
    const longDesc = 'A'.repeat(500); // far above normal limits
    cy.get('textarea').type(longDesc);

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('description', { matchCase: false })
      .should('be.visible');

    cy.screenshot('TC_06');
   });

  // --------------------------------------
  // TC07 – Invalid status injected
  // --------------------------------------
   it('TC07 – Reject invalid Status option (injected text)', () => {

   selectCombobox(1, 'Full Project'); 

    // direct typing without selecting dropdown item
    cy.get('input[data-testid="combobox-input-field"]').eq(3)
      .click()
      .type('INVALID_STATUS{enter}');

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('status', { matchCase: false })
      .should('be.visible');

    cy.screenshot('TC_07');
   });

  // --------------------------------------
  // TC08 – Nominal minimal valid task
  // --------------------------------------
   it('TC08 – Create task successfully with minimal valid fields', () => {
    chooseCombo(0, 'Client1');
    
    typeByIndex(3, 'B');                   // minimal task number
    typeByIndex(4, '1');                   // rate = 0 allowed
    selectCombobox(3, 'Backlog');          // valid status
    cy.get('textarea').type('OK');         // minimal description
    selectCombobox(1, 'Full Project');       // project required

    cy.contains('button', 'Save').click({ force: true });

    cy.contains('task', { matchCase: false })  
      .should('be.visible');

    cy.screenshot('TC_08');
   });

});

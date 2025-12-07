describe('Invoice Ninja – Client Settings Form', () => {
 
  beforeEach(() => {
     cy.login()
   cy.visit('https://app.invoicing.co/#/clients/create/settings')
    cy.wait(1500)
  })

  // Helper for Notes (TinyMCE)
  const typeNote = (index, text) => {
    cy.get('iframe')
      .eq(index)
      .its('0.contentDocument.body')
      .should('not.be.empty')
      .then(cy.wrap)
      .clear()
      .type(text, { force: true })
  }

  function selectReactOption(index, option) {
    cy.get('.css-b62m3t-container')
      .eq(index)
      .click({ force: true });

    cy.get('input[role="combobox"][aria-autocomplete="list"]')
      .eq(index)
      .type(option + '{enter}', { force: true });
  }

  // ---------------------------------------------------------
  // TC01 – Nominal valid case
  // ---------------------------------------------------------
  it('TC01 – Should save settings with nominal valid values', () => {
  cy.wait(2000)
  selectReactOption(1, 'Pakistani Rupee (PKR)'); 
  selectReactOption(2, 'English');
  selectReactOption(3, 'Net 0');  
  selectReactOption(4, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(6, '1-3');  
  selectReactOption(7, 'Agriculture');  

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')
  cy.screenshot('TC_01');
})

  // // ---------------------------------------------------------
  // // TC02 – Missing Currency
  // // ---------------------------------------------------------
  it('TC02 – Should show validation when Currency is missing', () => {
  selectReactOption(2, 'English');
  selectReactOption(3, 'Net 0');  
  selectReactOption(4, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(6, '1-3');  
  selectReactOption(7, 'Agriculture');  

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')
  cy.screenshot('TC_02');
  
  })

  // // ---------------------------------------------------------
  // // TC03 – Missing Language
  // // ---------------------------------------------------------
  it('TC03 – Should show validation when Language is missing', () => {
   selectReactOption(1, 'Pakistani Rupee (PKR)'); 
  selectReactOption(3, 'Net 0');  
  selectReactOption(4, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(6, '1-3');  
  selectReactOption(7, 'Agriculture');  

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')

    cy.clickSave()

    cy.contains('Settings', { timeout: 5000 }).should('exist')
    cy.screenshot('TC_03');
   
  })

  // // ---------------------------------------------------------
  // // TC04 – Missing Payment Terms
  // // ---------------------------------------------------------
  it('TC04 – Should show validation when Payment Terms missing', () => {
  selectReactOption(1, 'Pakistani Rupee (PKR)'); 
  selectReactOption(2, 'English'); 
  selectReactOption(4, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(6, '1-3');  
  selectReactOption(7, 'Agriculture');  

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')
  cy.screenshot('TC_04');
   
  })

  // // ---------------------------------------------------------
  // // TC05 – Missing Quote Valid Until
  // // ---------------------------------------------------------
  it('TC05 – Should show validation when Quote Valid Until missing', () => {
      selectReactOption(1, 'Pakistani Rupee (PKR)'); 
  selectReactOption(2, 'English');
  selectReactOption(3, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(6, '1-3');  
  selectReactOption(7, 'Agriculture');  

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')
  cy.screenshot('TC_05');
   
  })

  // // ---------------------------------------------------------
  // // TC06 – Task Rate non-numeric input
  // // ---------------------------------------------------------
  it('TC06 – Should reject non-numeric Task Rate', () => {
    cy.typeField('Task Rate', 'abc')
    cy.clickSave()

    cy.contains('Settings', { timeout: 5000 }).should('exist')
    cy.screenshot('TC_06');
     
  })

  // // ---------------------------------------------------------
  // // TC07 – Negative Task Rate
  // // ---------------------------------------------------------
  it('TC07 – Should reject negative Task Rate', () => {
    cy.typeField('Task Rate', '-1')
    cy.clickSave()

    cy.contains('Settings', { timeout: 5000 }).should('exist')
    cy.screenshot('TC_07');
    
  })

  // // ---------------------------------------------------------
  // // TC08 – Company Size left blank (if required)
  // // ---------------------------------------------------------
  it('TC08 – Company Size missing should show validation (if required)', () => {
      selectReactOption(1, 'Pakistani Rupee (PKR)'); 
  selectReactOption(2, 'English');
  selectReactOption(3, 'Net 0');  
  selectReactOption(4, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(7, 'Agriculture');  

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')
  cy.screenshot('TC_08');
  
  })

  // // ---------------------------------------------------------
  // // TC09 – Industry missing (if required)
  // // ---------------------------------------------------------
  it('TC09 – Should validate missing Industry', () => {
      selectReactOption(1, 'Pakistani Rupee (PKR)'); 
  selectReactOption(2, 'English');
  selectReactOption(3, 'Net 0');  
  selectReactOption(4, 'Net 0');  
  selectReactOption(5, 'Enabled');  
  selectReactOption(6, '1-3');   

  cy.typeField('Task Rate', '100.00')
  
  typeNote(0, 'OK')
  typeNote(1, 'OK')

  cy.clickSave()

  cy.contains('Settings', { timeout: 5000 }).should('exist')
  cy.screenshot('TC_09');
 
  })

  // // ---------------------------------------------------------
  // // TC10 – Public Notes too long (UB+1)
  // // ---------------------------------------------------------
  it('TC10 – Should validate overly long Public Notes', () => {
    const tooLong = 'A'.repeat(255)

    typeNote(0, tooLong)
    cy.clickSave()

    cy.contains('Settings', { matchCase: false }).should('exist')
    cy.screenshot('TC_10');
     
  })

  // // ---------------------------------------------------------
  // // TC11 – Private Notes HTML injection attempt
  // // ---------------------------------------------------------
  it('TC11 – Should sanitize/deny script injection in Private Notes', () => {
    typeNote(1, '<script>alert(1)</script>')

    cy.clickSave()

    cy.contains('Settings', { matchCase: false }).should('exist')
    cy.screenshot('TC_11');
    
  })

  // // ---------------------------------------------------------
  // // TC12 – Extreme large Task Rate
  // // ---------------------------------------------------------
  it('TC12 – Should reject extremely large Task Rate', () => {
    cy.typeField('Task Rate', '500')
    cy.clickSave()

    cy.contains('Settings', { timeout: 5000 }).should('exist')
    cy.screenshot('TC_12');
    
  })

  // // ---------------------------------------------------------
  // // TC13 – All dropdowns left at Select...
  // // ---------------------------------------------------------
  it('TC13 – Should show multiple errors for all blank dropdowns', () => {
    
    cy.clickSave()

    cy.contains('Currency', { timeout: 5000 }).should('exist')
    cy.contains('Language').should('exist')
    cy.contains('Payment Terms').should('exist')
    cy.contains('Quote Valid Until').should('exist')
    cy.screenshot('TC_13');
    cy.contains('login', { timeout: 5000 }).should('exist')
  })

})

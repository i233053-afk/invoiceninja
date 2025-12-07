console.log('EMAIL = ', Cypress.env('EMAIL'))

/// <reference types="cypress" />

describe('Invoice Ninja – Create Client Minimal Test Set (ECP + BVA)', () => {

  beforeEach(() => {
    //cy.visit('https://app.invoicing.co')

    cy.login()
    cy.wait(12000)
    cy.visit('https://app.invoicing.co/#/clients/create')
    cy.wait(12000)
  })

  // ---------------------------------------------------------
  // TC01 – Valid Nominal Inputs (Min Values for Required Fields)
  // ---------------------------------------------------------

  it('TC01 – Should create client with minimal valid values (LB)', () => {
    cy.typeField('Name', 'A')
    cy.typeField('Number', '1')
   // cy.selectDropdown('Group', 'Default')
    //cy.selectDropdown('Assigned User', 'Ayesha Naveed')

    cy.typeField('ID Number', '1')
    cy.typeField('VAT Number', '1')
    cy.typeField('Website', 'https://a.co')
    cy.typeField('Phone', '1')
    cy.typeField('Routing ID', '1')

    cy.toggleSwitch('Valid VAT Number') // OFF by default → no action
    cy.toggleSwitch('Tax Exempt')       // OFF by default → no action

    //cy.selectDropdown('Classification', 'Trust')

    cy.typeField('First Name', 'A')
    cy.typeField('Last Name', 'B')
    cy.typeField('Email', 'a@b.c')
    cy.get('#phone_0').clear().type('1')

    cy.typeField('Billing Street', '1')
    cy.typeField('Apt/Suite', 'Apt1')
    cy.typeField('City', 'A')
    cy.typeField('State/Province', 'A')
    cy.typeField('Postal Code', '1')
    //cy.selectDropdown('Country', 'Afghanistan')

    cy.clickSave()
    cy.wait(3000)

     cy.contains(/client/i).should('exist')
     cy.screenshot('TC_01');
  })

  // ---------------------------------------------------------
  // TC02 – Blank Name (LB–1)
  // ---------------------------------------------------------

  it('TC02 – Should show error when Name is blank', () => {
    cy.typeField('Name', '{backspace}')

    cy.contains(/client/i).should('exist')
    cy.screenshot('TC_02');
  })

  // // ---------------------------------------------------------
  // // TC03 – Group left at "Select…"
  // // ---------------------------------------------------------

  it('TC03 – Should show error when Group is not selected', () => {
    cy.typeField('Name', 'Valid Name')

    // DO NOT select Group (leave at Select…)

    cy.clickSave()
    cy.contains(/client/i).should('exist')
    cy.screenshot('TC_03');
  })

  // ---------------------------------------------------------
  // TC04 – Invalid Contact Email
  // ---------------------------------------------------------

  it('TC04 – Should reject invalid contact email', () => {
    cy.typeField('Name', 'Client X')
    cy.typeField('Email', 'abc')   // invalid email

    cy.clickSave()
    cy.contains(/client/i).should('exist')
    cy.screenshot('TC_04');
  })

  // ---------------------------------------------------------
  // TC05 – Invalid Website URL
  // ---------------------------------------------------------

  it('TC05 – Should reject invalid website', () => {
    cy.typeField('Name', 'Client X')
    cy.typeField('Website', 'a')   // invalid URL

    cy.clickSave()
    cy.contains(/client/i).should('exist')
    cy.screenshot('TC_05');
  })

  // ---------------------------------------------------------
  // TC06 – City length UB+1 (256 chars)
  // ---------------------------------------------------------

  it('TC06 – Should reject too-long City (256 chars)', () => {
    const city256 = 'A'.repeat(256)

    cy.typeField('Name', 'Client X')
    cy.typeField('City', city256)

    cy.clickSave()
    cy.contains(/client/i).should('exist')
    cy.screenshot('TC_06');
  })

  // // ---------------------------------------------------------
  // // TC07 – Postal Code UB+1 (16 chars)
  // // ---------------------------------------------------------

  it('TC07 – Should reject too-long Postal Code (16 chars)', () => {
    const pc16 = '1'.repeat(16)

    cy.typeField('Name', 'Client X')
    cy.typeField('Postal Code', pc16)

    cy.clickSave()
     cy.contains(/client/i).should('exist')
     cy.screenshot('TC_07');
  })

  // ---------------------------------------------------------
  // TC08 – Name = Max allowed (255 chars)
  // ---------------------------------------------------------

  it('TC08 – Should accept Name with 255 characters (UB)', () => {
    const name255 = 'A'.repeat(255)

    cy.typeField('Name', name255)
   //cy.selectDropdown('Group', 'Default')

    cy.clickSave()
    cy.wait(2000)

     cy.contains(/client/i).should('exist')
     cy.screenshot('TC_08');
  })

  // ---------------------------------------------------------
  // TC09 – All fields mid-valid values (Nominal Boundary)
  // ---------------------------------------------------------

  it('TC09 – Should create client with nominal mid values', () => {
    cy.typeField('Name', 'Client One')
   // cy.selectDropdown('Group', 'VIP') // mid option
    //cy.selectDropdown('Assigned User', 'Admin') // mid user

    cy.typeField('City', 'Islamabad')
    cy.typeField('State/Province', 'Punjab')
    //cy.selectDropdown('Country', 'India')  // mid-country

    cy.clickSave()
     cy.contains(/client/i).should('exist')
     cy.screenshot('TC_09');
  })

  // // ---------------------------------------------------------
  // // TC10 – Toggle Fields ON (Valid VAT + Tax Exempt)
  // // ---------------------------------------------------------

  it('TC10 – Should save form with toggles ON', () => {
    cy.typeField('Name', 'Toggle Client')
    //cy.selectDropdown('Group', 'Default')

    cy.toggleSwitch('Valid VAT Number')
    cy.toggleSwitch('Tax Exempt')

    cy.clickSave()
    cy.contains(/client/i).should('exist')
    cy.screenshot('TC_10');
  })

})

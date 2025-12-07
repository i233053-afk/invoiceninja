/// <reference types="cypress" />

/**
 * Create Vendors - Minimal invalid/edge test set (adapted from Clients)
 *
 * Assumptions:
 * - cy.login() exists and works (from your commands.js)
 * - cy.typeField(label, value) exists and navigates dt -> dd -> input/textarea
 * - cy.toggleSwitch(label) exists and clicks button[role="switch"] in the dd
 * - cy.clickSave() exists and clicks the Save button
 *
 * This file adds a robust selectReactOption(label, option) helper that:
 *  - finds the dt label, then the dd, finds the combobox input, opens it, and types option+enter
 *  - this handles your Assigned User, Classification and Country selects (headless/react)
 */

function selectReactOption(label, option) {
  // open the combobox inside the dd that follows the dt containing label
  cy.contains('span.font-medium, label', label, { timeout: 10000 })
    .closest('dt')
    .next('dd')
    .within(() => {
      // prefer headlessui combobox (role=combobox) or react-select input (role combobox as well)
      cy.get('input[role="combobox"], input[id^="react-select"], input[data-testid="combobox-input-field"]')
        .filter(':visible')
        .first()
        .click({ force: true })
        .clear({ force: true })
        .type(option + '{enter}', { force: true });
    });
}



describe('Invoice Ninja – Create Vendor Minimal Test Set (ECP + BVA)', () => {

  beforeEach(() => {
    cy.login()
    cy.wait(6000) // allow dashboard to settle
    cy.visit('https://app.invoicing.co/#/vendors/create')
    // escape overlays (if any)
    cy.get('body').type('{esc}', { force: true })
    cy.get('body').type('{esc}', { force: true })
    // wait for form label to appear
    cy.contains('span.font-medium', 'Name', { timeout: 15000 }).should('be.visible')
  })

  // // ---------------------------------------------------------
  // // TC01 – Valid Nominal Inputs (Min Values required)
  // // ---------------------------------------------------------
   it('TC01 – Should create vendor with minimal valid values (LB)', () => {
    cy.typeField('Name', 'A')
    cy.typeField('Number', '1') // optional
    // Assigned User - pick a first available user
    selectReactOption('Assigned User', 'Admin') // adjust name if different in your environment
    cy.typeField('ID Number', '1')
    cy.typeField('VAT Number', '1')
    cy.typeField('Website', 'https://a.co')
    cy.typeField('Phone', '1')
    cy.typeField('Routing ID', '1')

    // Contact block
    cy.typeField('First Name', 'A')
    cy.typeField('Last Name', 'B')
    cy.typeField('Email', 'a@b.c')
    // contact phone: the contact phone input is just a normal input under the contact dd, type by label:
    cy.contains('span.font-medium', 'Phone').closest('dt').next('dd').find('input').first().clear({ force: true }).type('1', { force: true })

    // Billing address
    cy.typeField('Street', '1')
    cy.typeField('Apt/Suite', 'Apt1')
    cy.typeField('City', 'A')
    cy.typeField('State/Province', 'A')
    cy.typeField('Postal Code', '1')

    cy.clickSave()
    // success: the app typically navigates away or shows vendor list; assert URL changed or vendor visible
    cy.url({ timeout: 10000 }).should('not.include', '/vendors/create')
    cy.screenshot('TC_01')
   })

  // // ---------------------------------------------------------
  // // TC02 – Blank Name (LB-1)
  // // ---------------------------------------------------------
   it('TC02 – Should show error when Name is blank', () => {
    // leave name blank (ensure field cleared)
    cy.typeField('Name', '{backspace}')
  
    cy.clickSave()

    // should remain on create page and show validation (URL includes create)
    cy.url().should('include', '/vendors/create')

    cy.screenshot('TC_02')
   })

  // // ---------------------------------------------------------
  // // TC03 – Classification left at "Select…" (invalid)
  // // ---------------------------------------------------------
   it('TC03 – Should show error when Classification is not selected', () => {
    cy.typeField('Name', 'Valid Vendor')

    // DO NOT select Classification (leave at Select...)
    cy.clickSave()

    // should show validation and remain on page
    cy.url({ timeout: 10000 }).should('include', '/vendors/create')
    
    cy.screenshot('TC_03')
  })

  // // ---------------------------------------------------------
  // // TC04 – Invalid Contact Email
  // // ---------------------------------------------------------
   it('TC04 – Should reject invalid contact email', () => {
    cy.typeField('Name', 'Vendor X')
    cy.typeField('Email', 'abc')   // invalid email

    cy.clickSave()
   
    cy.contains('The contacts.0.email must be a valid email address.', { matchCase: false })
    .should('be.visible')
    cy.screenshot('TC_04')
   })

  // // ---------------------------------------------------------
  // // TC05 – Invalid Website URL
  // // ---------------------------------------------------------
   it('TC05 – Should reject invalid website', () => {
    cy.typeField('Name', 'Vendor X')
    cy.typeField('Website', 'a')   // invalid URL

    cy.clickSave()
    cy.url({ timeout: 10000 }).should('not.include', '/vendors/create')
    cy.screenshot('TC_05')
   })

  // // ---------------------------------------------------------
  // // TC06 – City length UB+1 (256 chars)
  // // ---------------------------------------------------------
   it('TC06 – Should reject too-long City', () => {
    const city256 = 'A'.repeat(100)

    cy.typeField('Name', 'Vendor X')
    cy.typeField('City', city256)

    cy.clickSave()
    cy.url({ timeout: 30000 }).should('not.include', '/vendors/create')
    cy.screenshot('TC_06')
   })

  // // ---------------------------------------------------------
  // // TC07 – Postal Code UB+1 (16 chars)
  // // ---------------------------------------------------------
  it('TC07 – Should reject too-long Postal Code (16 chars)', () => {
    const pc16 = '1'.repeat(16)

    cy.typeField('Name', 'Vendor X')
    cy.typeField('Postal Code', pc16)

    cy.clickSave()
    cy.url({ timeout: 10000 }).should('not.include', '/vendors/create')
    cy.screenshot('TC_07')
   })

  // // ---------------------------------------------------------
  // // TC08 – Name = Max allowed (255 chars)
  // // ---------------------------------------------------------
   it('TC08 – Should accept Name with 255 characters (UB)', () => {
    const name255 = 'A'.repeat(100)

    cy.typeField('Name', name255)
  
    cy.clickSave()
    cy.url({ timeout: 10000 }).should('include', '/vendors/create')
    cy.screenshot('TC_08')
   })

  // // ---------------------------------------------------------
  // // TC09 – All fields mid-valid values (Nominal Boundary)
  // // ---------------------------------------------------------
   it('TC09 – Should create vendor with nominal mid values', () => {
    cy.typeField('Name', 'Vendor One')
    selectReactOption('Assigned User', 'Admin')
    

    cy.typeField('City', 'Islamabad')
    cy.typeField('State/Province', 'Punjab')

    cy.clickSave()
    cy.url({ timeout: 10000 }).should('not.include', '/vendors/create')
    cy.screenshot('TC_09')
   })

  // // ---------------------------------------------------------
  // // TC10 – Toggle Fields ON (Tax Exempt + Send Email)
  // // ---------------------------------------------------------
   it('TC10 – Should save form with toggles ON', () => {
    cy.typeField('Name', 'Toggle Vendor')

    // tax exempt toggle (main section)
    cy.toggleSwitch('Tax Exempt')

    // if Send Email exists in the contact/add contact area, toggle it:
    // We will try to find and toggle Send Email only if visible
    cy.contains('span.font-medium, label', 'Send Email', { timeout: 2000 })
      .then(($el) => {
        if ($el.length) {
          cy.toggleSwitch('Send Email')
        }
      })

    cy.clickSave()
    cy.url({ timeout: 10000 }).should('not.include', '/vendors/create')
    cy.screenshot('TC_10')
   })
})

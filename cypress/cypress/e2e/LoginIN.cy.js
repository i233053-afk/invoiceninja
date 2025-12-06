/// <reference types="cypress" />
console.log('EMAIL = ', Cypress.env('EMAIL'))

describe('Invoice Ninja – Login Minimal Test Set (ECP + BVA)', () => {

  beforeEach(() => {
    // Visit login page (do NOT use cy.login since these tests exercise the login form)
    cy.visit('https://app.invoicing.co/#/login', { timeout: 100000, failOnStatusCode: false })
    cy.wait(3000)
  })

//   // ---------------------------------------------------------
//   // TC01 – Valid Nominal Inputs (Nominal)
//   // ---------------------------------------------------------
   it('TC01 – Should login with valid email and password (Nominal)', () => {
    // Replace label strings below with exact labels from the form if they differ
    cy.typeField('Email address', 'ahmedmohna4@gmail.com')
    cy.typeField('Password', 'ayesha2005')

    cy.contains('button', 'Login').click({ force: true })
    // Expect dashboard or successful redirect
    cy.url({ timeout: 30000 }).should('include', '/#/dashboard')
    cy.screenshot('TC_01')
  })

//   // ---------------------------------------------------------
//   // TC02 – Valid Nominal Inputs (Nominal)
//   // ---------------------------------------------------------
   it('TC02 – Should reject invalid email format', () => {
  cy.typeField('Email address', 'inva')   // invalid email
  cy.typeField('Password', 'ValidPass123')

  cy.contains('button', 'Login')
    .click({ force: true })

  // Browser should block submission
  cy.url().should('include', '/#/login')

  // Validate HTML5 native browser error
  cy.get('input[type="email"]')
    .then(($input) => {
      expect($input[0].checkValidity()).to.be.false
      expect($input[0].validationMessage).to.contain("@")
  })

 cy.screenshot('TC_02')
 })


//   // ---------------------------------------------------------
//   // TC03 – Blank Password (LB-1)
//   // ---------------------------------------------------------
  it('TC03 – Should show error when password is blank', () => {

  // Enter email
  cy.typeField('Email address', 'ahmedmohna4@gmail.com')

  // Clear password
  cy.get('input[type="password"]').clear({ force: true })

  cy.contains('button', 'Login').click({ force: true })

  // Should stay on login page
  cy.url().should('include', '/#/login')

  // Check the app’s validation error (NOT HTML5)
  cy.contains('div', 'The password field is required', { matchCase: false })
    .should('be.visible')

  cy.screenshot('TC_03')
 })


  // ---------------------------------------------------------
  // TC04 – Missing Email (LB-1)
  // ---------------------------------------------------------
   it('TC04 – Should show error when email is blank', () => {
    cy.typeField('Email address', '{backspace}')
    cy.typeField('Password', 'ValidPass123')

    cy.contains('button', 'Login').click({ force: true })
    cy.url().should('include', '/#/login')
    
    cy.contains('div', 'The email field is required.', { matchCase: false })
    .should('be.visible')

    cy.screenshot('TC_04')
   })

  // ---------------------------------------------------------
  // TC05 – Very long email (UB+1)
  // ---------------------------------------------------------
   it('TC05 – Should reject overly long email (UB+1)', () => {
    const local = 'a'.repeat(245) // craft >254 when combined with domain
    const longEmail = `${local}@x.co`

    cy.typeField('Email address', longEmail)
    cy.typeField('Password', 'ValidPass123')

    cy.contains('button', 'Login').click({ force: true })
    cy.url().should('include', '/#/login')
    
    cy.contains('div', 'Email not set or not found', { matchCase: false })
   .should('be.visible')
    
    cy.screenshot('TC_05')
  })

  // ---------------------------------------------------------
  // TC06 – Invalid password characters / too short (ECP/Boundary)
  // ---------------------------------------------------------
  it('TC06 – Should reject too-short password or invalid format', () => {
    cy.typeField('Email address', 'ahmedmohna4@gmail.com')
    cy.typeField('Password', 'a') // too short / LB-1

    cy.contains('button', 'Login').click({ force: true })
    
     cy.url().should('include', '/#/login') // still on login page


    cy.screenshot('TC_06')
  })

  // ---------------------------------------------------------
  // TC07 – 2FA invalid token (when 2FA enabled)
  // ---------------------------------------------------------
  it('TC07 – Should reject invalid 2FA OTP (non-numeric/incorrect length)', () => {
     
  cy.typeField('Email address', 'ahmedmohna4@gmail.com')
  cy.typeField('Password', 'ayesha2005')

  // Only type OTP if the field exists
  cy.get('input[placeholder="(optional)"]').then(($otp) => {
    if ($otp.length) {
      cy.wrap($otp).type('abc12', { force: true })
    }
  })

  cy.contains('button', 'Login').click({ force: true })
  cy.url().should('include', '/#/login')

  cy.screenshot('TC_07')
})


})

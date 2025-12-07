/// <reference types="cypress" />

describe("Invoice Ninja – Create Product Form (Minimal Test Set)", () => {

  // before(() => {
  //   cy.login()   // login ONCE for entire spec
    
  // })

  beforeEach(() => {
    // Ensure page loaded before each TC
    cy.login()
    cy.visit('https://app.invoicing.co/#/products/create')
    cy.wait(1500)
  })

  // ----------------------------
  // TC01 – Valid Nominal
  // ----------------------------
  it('TC01 – Should save product with all valid nominal values', () => {
  cy.typeField('Item', 'Laptop Bag')
  cy.typeField('Description', 'A padded laptop bag')
  cy.typeField('Price', '120')
  cy.typeField('Default Quantity', '1')
  cy.typeField('Max Quantity', '10')
  cy.typeField('Tax Category', 'Physical Goods')
  cy.typeField('Image URL', 'https://example.com/image.jpg')

  cy.clickSave()

  cy.contains('Laptop Bag', { timeout: 6000 }).should('exist')
  cy.screenshot('TC_01');
})

  // // ----------------------------
  // // TC02 – Missing Item (Required)
  // // ----------------------------
  it("TC02 – Should show error when Item is empty", () => {
    cy.typeField("Item*", "{backspace}") // clear field fully
    cy.typeField("Price", "150")
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "3")
    cy.typeField("Tax Category", "Physical Goods")

    cy.clickSave()

    cy.contains("Item", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_02');
  })

  // // ----------------------------
  // // TC03 – Price = alphabetic (invalid)
  // // ----------------------------
  it("TC03 – Should reject non-numeric price", () => {
    cy.typeField("Item*", "Test Product")
    cy.typeField("Price", "abc")  // invalid
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "5")

    cy.clickSave()

    cy.contains("Price", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_03');
  })

  // // ----------------------------
  // // TC04 – Price negative
  // // ----------------------------
  it("TC04 – Should reject negative price", () => {
    cy.typeField("Item*", "Test Product")
    cy.typeField("Price", "-5")
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "5")

    cy.clickSave()

    cy.contains("Price", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_04');
  })

  // // ----------------------------
  // // TC05 – Max Quantity < Default Quantity
  // // ----------------------------
  it("TC05 – Should reject Max Quantity lower than Default Quantity", () => {
    cy.typeField("Item*", "Test Product")
    cy.typeField("Price", "100")
    cy.typeField("Default Quantity", "5")
    cy.typeField("Max Quantity", "2") // invalid

    cy.clickSave()

    cy.contains("Quantity", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_05');
  })

  // // ----------------------------
  // // TC06 – Tax Category left default (invalid)
  // // ----------------------------
  it("TC06 – Should show error when Tax Category not selected", () => {
    cy.typeField("Item*", "Test Product")
    cy.typeField("Price", "100")
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "3")

    // Do NOT select dropdown

    cy.clickSave()

    cy.contains("Tax", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_06');
  })

  // // ----------------------------
  // // TC07 – Invalid Image URL
  // // ----------------------------
  it("TC07 – Should reject invalid Image URL", () => {
    cy.typeField("Item*", "Test Product")
    cy.typeField("Price", "120")
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "10")
    cy.typeField("Tax Category", "Physical Goods")
    cy.typeField("Image URL", "not-a-url") // invalid

    cy.clickSave()

    cy.contains("URL", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_07');
  })

  // // ----------------------------
  // // TC08 – Item exceeds max length (256 chars)
  // // ----------------------------
  it("TC08 – Should reject Item name >255 chars", () => {
    const longName = "A".repeat(256)

    cy.typeField("Item*", longName)
    cy.typeField("Price", "100")
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "5")
    cy.typeField("Tax Category", "Physical Goods")

    cy.clickSave()

    cy.contains("Item", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_08');
  })

  // // ----------------------------
  // // TC09 – Extremely long description (10,000 chars)
  // // ----------------------------
  it("TC09 – Should handle extremely long description", () => {
    const longDesc = "X".repeat(256)

    cy.typeField("Item*", "Test Product")
    cy.typeField("Description", longDesc)
    cy.typeField("Price", "100")
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "5")
    cy.typeField("Tax Category", "Physical Goods")

    cy.clickSave()

    cy.contains("Description", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_09');
  })

  // // ----------------------------
  // // TC10 – Very large price (1,000,001)
  // // ----------------------------
  it("TC10 – Should reject extremely large price", () => {
    cy.typeField("Item*", "Test Product")
    cy.typeField("Price", "1000001") // too large
    cy.typeField("Default Quantity", "1")
    cy.typeField("Max Quantity", "5")
    cy.typeField("Tax Category", "Physical Goods")

    cy.clickSave()

    cy.contains("Price", { timeout: 6000 }).should("exist")
    cy.screenshot('TC_10');
  })
})

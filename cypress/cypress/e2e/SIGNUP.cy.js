describe('Register Form – Minimal Test Set', () => {

  // beforeEach(() => {
  //   cy.visit('https://app.invoicing.co/#/register');
  // });

 function fillRegisterForm(email, pass, confPass) {
  if (email !== null && email !== '') {
    cy.get('#email').clear().type(email, { force: true });
  } else {
    cy.get('#email').clear(); // leave empty
  }

  if (pass !== null && pass !== '') {
    cy.get('#password').clear().type(pass, { force: true });
  } else {
    cy.get('#password').clear(); // leave empty
  }

  if (confPass !== null && confPass !== '') {
    cy.get('#password_confirmation').clear().type(confPass, { force: true });
  } else {
    cy.get('#password_confirmation').clear(); // leave empty
  }
}


//   // ------------------------- TC01 -------------------------
   it('TC01 – All fields empty (Required Fields Validation)', () => {
//   fillRegisterForm('', '', '');  // NO typing, stays empty

//   cy.contains('button', 'Register').click({ force: true });

//   cy.contains('Email').should('be.visible');
//   cy.contains('Password').should('be.visible');
//   cy.contains('Confirm your password').should('be.visible');

//   cy.screenshot('TC_01');
});


  // // ------------------------- TC02 -------------------------
   it('TC02 – Invalid email format', () => {
  //   fillRegisterForm('bad-email', 'ValidPass123!', 'ValidPass123!');
  //   cy.contains('button', 'Register').click({ force: true });
  //   cy.wait(6000)
  //   cy.contains('email', { matchCase: false }).should('be.visible');

  //   cy.screenshot('TC_02');
   });

  // // ------------------------- TC03 -------------------------
   it('TC03 – Password too short', () => {
  //   fillRegisterForm('user@gmail.com', 's1', 's1');
  //   cy.contains('button', 'Register').click({ force: true });

  //   cy.contains('Password', { matchCase: false }).should('be.visible');

  //   cy.screenshot('TC_03');
   });

  // // ------------------------- TC04 -------------------------
   it('TC04 – Password mismatch', () => {
  //   fillRegisterForm('user@gmail.com', 'ValidPass1!', 'Different1!');
  //   cy.contains('button', 'Register').click({ force: true });

  //   cy.contains('Password', { matchCase: false }).should('be.visible');

  //   cy.screenshot('TC_04');
   });

  // // ------------------------- TC05 -------------------------
   it('TC05 – Email exceeds max length (255 chars)', () => {
  //   const longEmail = 'a'.repeat(250) + '@ex.com'; // 255+ chars
  //   fillRegisterForm(longEmail, 'ValidPass1!', 'ValidPass1!');

  //   cy.contains('button', 'Register').click({ force: true });

  //   cy.contains('Email', { matchCase: false }).should('be.visible');

  //   cy.screenshot('TC_05');
   });

  // // ------------------------- TC06 -------------------------
   it('TC06 – Valid registration (Nominal Case)', () => {
  //   fillRegisterForm('validuserTest@example.com', 'ValidPass1!', 'ValidPass1!');

  //   cy.contains('button', 'Register').click({ force: true });

  //   // Expect: either verification page or login page
  //   cy.url().should('include', '/register');

  //   cy.screenshot('TC_06');
   });

  // // ------------------------- TC07 -------------------------
   it('TC07 – Simple (weak) password (if complexity enforced)', () => {
  //   fillRegisterForm('user@example.com', 'password', 'password');
  //   cy.contains('button', 'Register').click({ force: true });

  //   cy.contains('Password', { matchCase: false }).should('be.visible');

  //   cy.screenshot('TC_07');
  });

 // ------------------------- TC08 -------------------------
 it('TC08 – Click "Sign in with Google"', () => {

  cy.visit('https://app.invoicing.co/#/register');
//   // Stub window.open so Google page does NOT really open
//   cy.window().then((win) => {
//     cy.stub(win, 'open').as('windowOpen');
//   });

//   cy.contains('Sign in with Google').click({ force: true });

//   // Assert the button opened the Google OAuth URL
//   cy.get('@windowOpen').should('be.called');
//   cy.get('@windowOpen').should('be.calledWithMatch', /accounts\.google\.com/);

//   cy.screenshot('TC_08');
 });


});

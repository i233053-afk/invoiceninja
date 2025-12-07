module.exports = {
  chromeWebSecurity: false,
  userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119 Safari/537.36"
}
const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    baseUrl: "https://app.invoicing.co",
    chromeWebSecurity: false,
    viewportWidth: 1366,
    viewportHeight: 768,

    setupNodeEvents(on, config) {
      // Node events here (optional)
    },
  },

  env: {
    EMAIL: "ahmedmohna4@gmail.com",
    PASSWORD: "ayesha2005"
  }
});

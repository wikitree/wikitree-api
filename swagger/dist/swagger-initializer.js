window.onload = function() {
  window.ui = SwaggerUIBundle({
    url: "./wikitreeAPI.yaml",
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "BaseLayout",
    validatorUrl: null,
    validate: false,
  });
};

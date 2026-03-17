document.addEventListener("DOMContentLoaded", () => {
  // Form submit feedback and AJAX implementation (PHP Backend)
  const form = document.getElementById("lead-form");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerText;

      // 1. Validaciones Frontend de Seguridad
      const formData = new FormData(form);
      const nombre = formData.get("nombre");
      const telefono = formData.get("telefono");
      
      // Verificación de Honeypot (trampa para bots)
      if (formData.get("web_site_url")) {
        console.warn("Petición bloqueada preventivamente.");
        return; // Bot detectado, abortar silenciosamente
      }

      // Validación de longitud y vacíos
      if (!nombre || nombre.trim().length < 2 || nombre.trim().length > 30) {
        alert("Por favor ingresa un nombre válido (entre 2 y 30 caracteres).");
        return;
      }

      // Validación estricta del número telefónico usando Regex
      const phoneRegex = /^[0-9\+\-\s]{7,20}$/;
      if (!telefono || !phoneRegex.test(telefono.trim())) {
        alert("Por favor ingresa un teléfono válido (entre 7 y 20 números).");
        return;
      }

      submitBtn.innerText = "ENVIANDO...";
      submitBtn.classList.add("opacity-75", "cursor-not-allowed");
      submitBtn.disabled = true;

      try {
        const response = await fetch("enviar.php", {
          method: "POST",
          body: formData
        });

        // Intentar parsear el JSON de respuesta
        const data = await response.json();

        if (response.ok && data.status === "success") {
          submitBtn.innerText = "¡ENVIADO CORRECTAMENTE!";
          submitBtn.setAttribute("aria-live", "polite");
          submitBtn.classList.remove("bg-accent", "hover:bg-accent/90", "opacity-75", "cursor-not-allowed");
          submitBtn.classList.add("bg-green-600", "hover:bg-green-700");
          form.reset();
        } else {
          throw new Error(data.message || "Error en el servidor");
        }
      } catch (error) {
        submitBtn.innerText = "ERROR AL ENVIAR";
        submitBtn.classList.remove("opacity-75", "cursor-not-allowed");
        submitBtn.classList.add("bg-red-600", "hover:bg-red-700");
      }

      setTimeout(() => {
        submitBtn.innerText = originalText;
        submitBtn.disabled = false;
        submitBtn.classList.add("bg-accent", "hover:bg-accent/90");
        submitBtn.classList.remove("bg-green-600", "hover:bg-green-700", "opacity-75", "cursor-not-allowed", "bg-red-600", "hover:bg-red-700");
      }, 4000);
    });
  }

  // FAQ: sync aria-expanded with details open state
  document.querySelectorAll("details").forEach((details) => {
    const summary = details.querySelector("summary");
    if (!summary) return;
    summary.setAttribute(
      "aria-expanded",
      details.open ? "true" : "false",
    );
    details.addEventListener("toggle", () => {
      summary.setAttribute(
        "aria-expanded",
        details.open ? "true" : "false",
      );
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // Form submit feedback
  const form = document.getElementById("lead-form");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerText;

      submitBtn.innerText = "¡ENVIADO CORRECTAMENTE!";
      submitBtn.setAttribute("aria-live", "polite");
      submitBtn.classList.remove("bg-accent", "hover:bg-accent/90");
      submitBtn.classList.add("bg-green-600", "hover:bg-green-700");

      form.reset();

      setTimeout(() => {
        submitBtn.innerText = originalText;
        submitBtn.classList.add("bg-accent", "hover:bg-accent/90");
        submitBtn.classList.remove("bg-green-600", "hover:bg-green-700");
      }, 3000);
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

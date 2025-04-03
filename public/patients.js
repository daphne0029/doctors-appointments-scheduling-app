document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("patientForm");
  const messageDiv = document.getElementById("message");

  form.addEventListener("submit", function (event) {
      event.preventDefault();

      const name = document.getElementById("name").value.trim();
      const email = document.getElementById("email").value.trim();

      if (!email) {
          messageDiv.innerHTML = `<p style="color: red;">Email is required.</p>`;
          return;
      }

      if (name) {
          // New Patient Signup
          fetch("/api/patients", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ name, email }),
          })
          .then(res => res.json())
          .then(data => {
              if (data.patient) {
                  localStorage.setItem("patient_token", data.patient.token);
                  localStorage.setItem("patient_id", data.patient.id);
                  localStorage.setItem("patient_name", data.patient.name);
                  window.location.href = `/appointments?token=${data.patient.token}`;
              } else {
                  messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
              }
          })
          .catch(() => messageDiv.innerHTML = `<p style="color: red;">Error occurred.</p>`);
      } else {
          // Login Existing Patient
          fetch(`/api/patients/login`, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ email }),
          })
          .then(res => res.json())
          .then(data => {
              if (data.patient) {
                  localStorage.setItem("patient_token", data.patient.token);
                  localStorage.setItem("patient_id", data.patient.id);
                  localStorage.setItem("patient_name", data.patient.name);
                  window.location.href = `/appointments?token=${data.patient.token}`;
              } else {
                  messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
              }
          })
          .catch(() => messageDiv.innerHTML = `<p style="color: red;">Error occurred.</p>`);
      }
  });
});

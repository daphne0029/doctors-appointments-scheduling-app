<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Patients</title>

      <!-- Include jQuery and FullCalendar -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="/js/patients.js"></script>
      <link rel="stylesheet" href="/css/app.css">

  </head>
  <body>
      <h2>Add/Sign in as a new patient</h2>
      <form id="patientForm">
        <label for="name">Name (leave empty if logging in):</label>
        <input type="text" id="name" name="name">
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <button type="submit" class="login-btn">Continue</button>
      </form>

      <div id="message"></div>

  </body>
</html>
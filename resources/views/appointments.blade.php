<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>

    <!-- Include jQuery and FullCalendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script> -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
    <script src="/appointments.js"></script>
    <link rel="stylesheet" href="/app.css">

</head>
<body>

    <h2>Doctor's Appointments</h2>

    <div id="greeting"></div>

    <button onclick="handleSignOut()" class="sign-out-btn">
      Sign Out
    </button>


    <div>
      <div class="block">
        <h3>Your Upcoming Appointments:</h3>
        <div id="appointments-list">
            <p>Loading...</p>
        </div>
      </div>

      <div class="block appointment-block">
        <h3>Book Your Next Appointment:</h3>
        <label for="appointmentType">Appointment Type:</label>
        <select id="appointmentType" onchange="updateAvailableTimes()">
            <option value="new_patient">New Patient Consultation</option>
            <option value="consultation">Regular Consultation</option>
            <option value="follow_up">Follow-up Consultation</option>
        </select>

        <!-- Dropdown for selecting a date (will be populated dynamically) -->
        <label for="appointmentDate">Select Date:</label>
        <select id="appointmentDate" onchange="updateAvailableTimes()">
            <option value="" disabled selected>Select a date</option>
        </select>

        <input type="hidden" id="appointmentTime" name="appointmentTime" value="">
        <input type="hidden" id="appointmentDoctor" name="appointmentDoctor" value="">
        <div id="appointmentSlots">
          <!-- Time slots will be dynamically displayed here -->
        </div>

        <button class="submit-slot-button" id="addAppointmentBtn" onclick="addAppointment()">Submit</button>
      </div>
    <div>

    <hr>
    <div class="clinic-calendar">
      <h2>Clinic's Full Schedules:</h2>
      <div id="calendar"></div>
    </div>
</body>
</html>

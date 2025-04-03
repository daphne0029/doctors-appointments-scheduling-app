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

    <style>
      .appointment-times {
          flex-wrap: wrap;
          gap: 10px; /* Space between buttons */
          margin-top: 20px;
          margin-left: 5px;
      }

      .appointment-times button {
          padding: 10px 20px;
          font-size: 16px;
          cursor: pointer;
          border: 1px solid #ccc;
          border-radius: 5px;
          background-color: #f0f0f0;
          transition: background-color 0.3s;
      }

      .appointment-times button:hover {
          background-color: #007bff;
          color: white;
      }

      .appointment-times button.selected {
          background-color: #28a745;
          color: white;
      }
    </style>


</head>
<body>

    <h2>Doctor's Appointments</h2>

    <div id="greeting" style="display: none; font-size: 20px; margin-bottom: 20px;"></div>

    <h3>Your Upcoming Appointments:</h3>
    <div id="appointments-list">
        <p>Loading...</p>
    </div>

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

    <div id="appointmentSlots">
      <!-- Time slots will be dynamically displayed here -->
    </div>

    <hr>
    <hr>
    <h2>Clinic Full Schedules:</h2>
    <div style="margin-top: 20px;" id="calendar"></div>

</body>
</html>

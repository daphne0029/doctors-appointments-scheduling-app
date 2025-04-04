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

      .block{
        display: inline-block;
        vertical-align:top;
      }

      .appointment-block {
        margin-left: 20px;
        width: 50%;
      }

      .cancel-button {
        background-color: #ff4d4d;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
      }

      .time-slot-button {
        background-color:rgb(52, 127, 165);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
      }

      .submit-slot-button {
        background-color:rgb(52, 165, 76);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
      }

      .disabled-btn {
        background-color: gray;
      }

      .card {
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 16px; 
        margin-bottom: 12px; 
        background-color: #f9f9f9; 
        box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
      }

      .sign-out-btn{
        position: absolute;
        top: 10px;
        right: 20px;
        padding: 5px 10px;
        background-color: #ff4d4d; 
        border-radius: 5px;
        color: white;
        border: none;
        cursor: pointer;
      }
    </style>

</head>
<body>

    <h2>Doctor's Appointments</h2>

    <div id="greeting" style="display: none; font-size: 20px; margin-bottom: 20px;"></div>

    <button onclick="handleSignOut()" class="sign-out-btn" style="">
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

        <button class="submit-slot-button" style="margin-top: 20px; display: none;" 
          id="addAppointmentBtn" onclick="addAppointment()">Submit</button>
      </div>
    <div>

    <hr>
    <hr>
    <h2>Clinic Full Schedules:</h2>
    <div style="margin-top: 20px;" id="calendar"></div>

</body>
</html>

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
        .form-container {
            display: none; /* Initially hidden */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            margin-top: 10px;
        }

        .form-container input, 
        .form-container select, 
        .form-container button {
            display: block;
            width: 100%;
            margin: 5px 0;
            padding: 8px;
        }

        .toggle-btn {
            background: #20b2aa;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            width: 100%;
            text-align: left;
            border-radius: 5px;
        }

        .toggle-btn:focus {
            outline: none;
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

    <button class="toggle-btn" onclick="toggleForm()"> + Add Appointment</button>

    <div style="display: none;" class="form-container" id="appointmentForm">
        <label for="doctor">Select Doctor:</label>
        <select id="doctor">
            <option value="1">Dr. Harry Potter</option>
            <option value="2">Dr. Hermione Granger</option>
            <option value="3">Dr. Ron Weasley</option>
            <option value="4">Dr. Draco Malfoy</option>
        </select>

        <label for="appointmentType">Appointment Type:</label>
        <select id="appointmentType" onchange="updateDurationInfo()">
            <option value="new_patient" data-duration="30">New Patient Consultation (30 mins)</option>
            <option value="consultation" data-duration="60">Regular Consultation (60 mins)</option>
            <option value="follow_up" data-duration="20">Follow-up Consultation (20 mins)</option>
        </select>
        <p class="duration-info" id="durationInfo">Duration: 30 minutes</p>

        <label for="date">Select Date:</label>
        <input style="width: 20%;" type="date" id="date">

        <label for="time">Select Time:</label>
        <input style="width: 20%;" type="time" id="time">

        <button onclick="addAppointment()">Submit</button>
    </div>

    <hr>
    <hr>
    <h2>Clinic Full Schedules:</h2>
    <div style="margin-top: 20px;" id="calendar"></div>

</body>
</html>

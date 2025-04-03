document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem("patient_token");
    const patientName = localStorage.getItem("patient_name");
    const patientId = localStorage.getItem("patient_id");

    if (!token) {
        window.location.href = "/patients"; // Redirect to login if not logged in
        return;
    }

    // Show greeting message
    if (patientName) {
        document.getElementById("greeting").innerHTML = `Welcome, <strong>${patientName}</strong>!`;
        document.getElementById("greeting").style.display = "block";
    }

    // Fetch upcoming appointments
    fetch(`/api/patients/${patientId}/appointments/upcomings`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${token}`
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        const appointmentList = document.getElementById("appointments-list");
        appointmentList.innerHTML = "";

        if (data.appointments.length === 0) {
            appointmentList.innerHTML = "<p>No upcoming appointments.</p>";
            return;
        }

        data.appointments.forEach(appoint => {
            const appointmentItem = document.createElement("div");
            appointmentItem.innerHTML = `
                <div style="
                    border: 1px solid #ddd; 
                    border-radius: 8px; 
                    padding: 16px; 
                    margin-bottom: 12px; 
                    background-color: #f9f9f9; 
                    box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
                ">
                    <p style="margin: 0 0 8px;">Appointment: <strong>${appoint.appointment_type}</strong> with Doctor ${appoint.doctor_name}</p>
                    <p style="margin: 0 0 8px;">Date: ${new Date(appoint.start_time).toLocaleString()}</p>
                    <button style="background-color: #ff4d4d; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;" 
                        onclick="deleteAppointment(${appoint.id})">Cancel</button>
                </div>
            `;
            appointmentList.appendChild(appointmentItem);
        });
    })
    .catch(() => {
        document.getElementById("appointments-list").innerHTML = "<p>Error loading appointments.</p>";
    });

    // Initialize Calendar view
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'listWeek',
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('/api/appointments')  // Adjust this URL if needed
                .then(response => response.json())
                .then(data => {
                    let events = data.appointments.data.map(appointment => ({
                        id: appointment.id,
                        title: appointment.appointment_type,
                        start: appointment.start_time,
                        end: appointment.end_time
                    }));

                    successCallback(events); // Add events to the calendar
                })
                .catch(error => {
                    console.error('Error fetching appointments:', error);
                    failureCallback(error);
                });
        }
    });

    calendar.render();

    fetchAppointments();
});

function addAppointment() {
    const token = localStorage.getItem("patient_token");
    const patientId = localStorage.getItem("patient_id");
    if (!token) {
        alert("You must be logged in to book an appointment.");
        return;
    }

    const doctor_id = document.getElementById("doctor").value;
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;
    const appointment_type = document.getElementById("appointmentType").value;
    const selectedOption = appointmentType.options[appointmentType.selectedIndex];
    const duration = selectedOption.getAttribute("data-duration");

    const startTimeStr = `${date} ${time}:00`;
    const tempEndTime = new Date(new Date(startTimeStr).getTime() + duration * 1000 * 60).toTimeString().split(' ')[0];
    const endTimeStr  = `${date} ${tempEndTime}`;

    fetch(`/api/patients/${patientId}/appointments`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify({
            patient_id: patientId,
            doctor_id: doctor_id,
            appointment_type: appointment_type,
            start_time: startTimeStr,
            end_time: endTimeStr
        })
    })
    .then(response => {
        if (response.ok) {
            alert("Appointment added!");
            location.reload();
        } else {
            response.json().then(data => {
                alert(`Failed to add appointment: ${data.error}`);
            });
        }
    })
    .catch(() => alert("Error adding appointment."));
};

function deleteAppointment(appointmentId) {
    const token = localStorage.getItem("patient_token");
    const patient_id = localStorage.getItem("patient_id");

    if (!token) {
        alert("You must be logged in to delete an appointment.");
        return;
    }

    fetch(`/api/patients/${patient_id}/appointments/${appointmentId}`, {
        method: "DELETE",
        headers: {
            "Authorization": `Bearer ${token}`
        }
    })
    .then(response => {
        if (response.ok) {
            alert("Appointment deleted!");
            location.reload();
        } else {
            alert("Failed to delete appointment.");
        }
    })
    .catch(() => alert("Error deleting appointment."));
}

function toggleForm() {
    const form = document.getElementById("appointmentForm");
    form.style.display = form.style.display === "block" ? "none" : "block";
}

function updateDurationInfo() {
    const selectedOption = document.getElementById("appointmentType").selectedOptions[0];
    const duration = selectedOption.getAttribute("data-duration");
    document.getElementById("durationInfo").textContent = `Duration: ${duration} minutes`;
}

let availableAppointments = {}; // Store available times fetched from API

// Fetch available appointments and render them
async function fetchAppointments() {
    const response = await fetch("/api/available-appointments");
    availableAppointments = await response.json();
    renderTimeSlots();
    // $('div.appointment-times-group.new_patient').css('display', 'block');
}

function renderTimeSlots() {
    const container = document.getElementById("appointmentSlots");
    container.innerHTML = ""; // Clear existing slots

    console.log(availableAppointments);

    const distinctDates = [];

    Object.keys(availableAppointments).forEach(type => {
        const typeDiv = document.createElement("div");
        typeDiv.classList.add("appointment-times-group", type);
        typeDiv.setAttribute("id", type);
        typeDiv.style.display = "none";
        container.appendChild(typeDiv);

        availableAppointments[type].forEach(entry => {
            if(!distinctDates.includes(entry.date)) {
                distinctDates.push(entry.date);
            }

            // render time slots
            const dateDiv = document.createElement("div");
            dateDiv.classList.add("slot-date", type);
            dateDiv.setAttribute('data-date', entry.date);
            dateDiv.style.display = "none";
            typeDiv.appendChild(dateDiv);

            // render doctor name
            const doctorSpan = document.createElement('span');
            doctorSpan.textContent = entry.doctor;
            dateDiv.appendChild(doctorSpan);

            entry.available_start_time.forEach(time => {
                const button = document.createElement("button");
                button.classList.add("appointment-times");
                button.textContent = time;
                button.classList.add("time-slot", type);
                button.onclick = () => selectTime(entry.date, time);
                dateDiv.appendChild(button);
            });
        });
    });

    // populate date dropdown
    distinctDates.forEach(d => {
        const dateOption = document.createElement('option');
        dateOption.textContent = d;
        dateOption.setAttribute('disable', true);
        $('#appointmentDate').append(dateOption);
    });
}

function updateAvailableTimes() {
    const selectedType = $("#appointmentType").val();
    const selectedDate = $("#appointmentDate").val();

    $(`div.slot-date`).css("display", "none");
    $("div.appointment-times-group").css("display", "none");

    $(`div.appointment-times-group.${selectedType}`).css("display", "block");
    $(`div.slot-date.${selectedType}[data-date="${selectedDate}"]`).css("display", "block");
}

function selectTime(date, time) {
    alert(`Selected appointment on ${date} at ${time}`);
}

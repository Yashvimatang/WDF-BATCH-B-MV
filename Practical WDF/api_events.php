<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Event Management</title>

    <style>
        body {
            font-family: Georgia, serif;
            background: #c9dbec url("commonbackground.png") center / cover fixed;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1, h2 {
            color: #3f607d;
        }

        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 7px 0;
            box-sizing: border-box;
            font-family: inherit;
        }

        button {
            background: #3f607d;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #29465f;
        }

        .event {
            border: 1px solid #7792aa;
            padding: 15px;
            margin: 12px 0;
            border-radius: 6px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions button {
            flex: 1;
        }

        .poster {
            max-width: 220px;
            width: 100%;
            display: block;
            margin: 10px 0;
        }

        #message {
            font-weight: bold;
            overflow-wrap: anywhere;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>StudentHub | Event Management</h1>
    <p id="message" role="status"></p>

    <h2 id="formTitle">Add Event</h2>

    <form id="eventForm" enctype="multipart/form-data">
        <input type="hidden" id="event_id">

        <label for="title">Event Title *</label>
        <input type="text" id="title" required maxlength="150">

        <label for="description">Description</label>
        <textarea id="description" rows="3"></textarea>

        <label for="event_date">Event Date *</label>
        <input type="date" id="event_date" required>

        <label for="event_time">Event Time *</label>
        <input type="time" id="event_time" required>

        <label for="venue">Venue *</label>
        <input type="text" id="venue" required maxlength="200">

        <label for="poster">Event Poster (JPG, PNG or WebP; max 2 MB)</label>
        <input type="file" id="poster"
               accept="image/jpeg,image/png,image/webp">

        <img id="posterPreview" class="poster"
             alt="Poster preview" style="display:none;">

        <p id="posterNote"></p>

        <button type="submit" id="saveButton">Add Event</button>

        <button type="button" onclick="resetForm()">Cancel / Clear</button>
    </form>

    <hr>

    <h2>All Events</h2>
    <div id="eventList">Loading events...</div>
</div>

<script>
const apiUrl = "api/events_api.php";
const manageUrl = "api/manage_events_api.php";

const eventList = document.getElementById("eventList");
const message = document.getElementById("message");
const eventForm = document.getElementById("eventForm");
const posterInput = document.getElementById("poster");
const posterPreview = document.getElementById("posterPreview");
const posterNote = document.getElementById("posterNote");

let currentPoster = "";
let previewUrl = null;

function showMessage(text, isError = false) {
    message.textContent = text;
    message.style.color = isError ? "red" : "green";
}

function clearPreviewUrl() {
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    }
}

async function readJson(response) {
    let result;

    try {
        result = await response.json();
    } catch {
        throw new Error("The server returned an invalid response.");
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Request failed.");
    }

    return result;
}

posterInput.addEventListener("change", function () {
    const file = posterInput.files[0];

    if (!file) {
        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type) ||
        file.size > 2 * 1024 * 1024) {
        showMessage("Choose JPG, PNG or WebP up to 2 MB.", true);
        posterInput.value = "";
        return;
    }

    clearPreviewUrl();
    previewUrl = URL.createObjectURL(file);
    posterPreview.src = previewUrl;
    posterPreview.style.display = "block";
    posterNote.textContent = "New poster selected.";
});

async function loadEvents() {
    eventList.textContent = "Loading events...";

    try {
        const response = await fetch(apiUrl);
        const result = await readJson(response);

        eventList.replaceChildren();

        if (!result.data || result.data.length === 0) {
            eventList.textContent = "No events found.";
            return;
        }

        result.data.forEach(event => {
            const card = document.createElement("div");
            card.className = "event";

            const title = document.createElement("h3");
            title.textContent = event.title;

            const description = document.createElement("p");
            description.textContent = event.description || "No description";

            const details = document.createElement("p");
            details.textContent =
                `Date: ${event.event_date} | Time: ${event.event_time} | Venue: ${event.venue}`;

            card.append(title, description, details);

            if (event.poster) {
                const poster = document.createElement("img");
                poster.src = "uploads/events/" +
                    encodeURIComponent(event.poster.split("/").pop());
                poster.alt = "Poster for " + event.title;
                poster.className = "poster";
                card.appendChild(poster);
            }

            const actions = document.createElement("div");
            actions.className = "actions";

            const editButton = document.createElement("button");
            editButton.type = "button";
            editButton.textContent = "Edit";
            editButton.addEventListener("click", () => editEvent(event));

            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.textContent = "Delete";
            deleteButton.addEventListener("click",
                () => deleteEvent(event.event_id));

            actions.append(editButton, deleteButton);
            card.appendChild(actions);
            eventList.appendChild(card);
        });
    } catch (error) {
        eventList.textContent = "Could not load events.";
        showMessage(error.message, true);
    }
}

eventForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    if (!eventForm.reportValidity()) {
        return;
    }

    const id = document.getElementById("event_id").value;
    const formData = new FormData();

    formData.append("action", id ? "update" : "add");

    if (id) {
        formData.append("event_id", id);
    }

    formData.append("title",
        document.getElementById("title").value.trim());
    formData.append("description",
        document.getElementById("description").value.trim());
    formData.append("event_date",
        document.getElementById("event_date").value);
    formData.append("event_time",
        document.getElementById("event_time").value);
    formData.append("venue",
        document.getElementById("venue").value.trim());

    if (posterInput.files[0]) {
        formData.append("poster", posterInput.files[0]);
    }

    try {
        const response = await fetch(manageUrl, {
            method: "POST",
            body: formData
        });

        const result = await readJson(response);

        showMessage(result.message);
        resetForm();
        await loadEvents();
    } catch (error) {
        showMessage(error.message, true);
    }
});

function editEvent(event) {
    resetForm();

    document.getElementById("event_id").value = event.event_id;
    document.getElementById("title").value = event.title;
    document.getElementById("description").value = event.description || "";
    document.getElementById("event_date").value = event.event_date;
    document.getElementById("event_time").value =
        (event.event_time || "").substring(0, 5);
    document.getElementById("venue").value = event.venue;

    currentPoster = event.poster || "";

    if (currentPoster) {
        posterPreview.src = "uploads/events/" +
            encodeURIComponent(currentPoster.split("/").pop());
        posterPreview.style.display = "block";
        posterNote.textContent =
            "Current poster shown. Select another file to replace it.";
    } else {
        posterNote.textContent = "No current poster.";
    }

    document.getElementById("formTitle").textContent = "Edit Event";
    document.getElementById("saveButton").textContent = "Update Event";

    window.scrollTo({ top: 0, behavior: "smooth" });
}

async function deleteEvent(id) {
    if (!confirm("Delete this event and its poster permanently?")) {
        return;
    }

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("event_id", id);

    try {
        const response = await fetch(manageUrl, {
            method: "POST",
            body: formData
        });

        const result = await readJson(response);

        showMessage(result.message);

        if (document.getElementById("event_id").value === String(id)) {
            resetForm();
        }

        await loadEvents();
    } catch (error) {
        showMessage(error.message, true);
    }
}

function resetForm() {
    eventForm.reset();
    document.getElementById("event_id").value = "";
    document.getElementById("formTitle").textContent = "Add Event";
    document.getElementById("saveButton").textContent = "Add Event";

    currentPoster = "";
    clearPreviewUrl();
    posterPreview.style.display = "none";
    posterPreview.removeAttribute("src");
    posterNote.textContent = "";
}

loadEvents();
</script>
</body>
</html>

let selection = {
    course: null,
    group: null,
    subjectClass: null,
    day: null,
    venue: null,
    timeslot: null
};

// 1. Course Selected -> Enable Group
function handleCourseChange(select) {
    selection.course = select.value;
    document.getElementById('group-dropdown').disabled = false;
    
    // Reset downstream
    document.getElementById('group-dropdown').selectedIndex = 0;
    document.getElementById('subject-dropdown').disabled = true;
    document.getElementById('subject-dropdown').selectedIndex = 0;
    disableTargetSection();
}

// 2. Group Selected -> Enable Subject Timeslot
function handleGroupChange(select) {
    selection.group = select.value;
    document.getElementById('subject-dropdown').disabled = false;
    
    // Reset downstream
    document.getElementById('subject-dropdown').selectedIndex = 0;
    disableTargetSection();
}

// 3. Subject Timeslot Selected -> Enable the "To" Section
function handleSubjectChange(select) {
    selection.subjectClass = select.value;
    
    if (selection.subjectClass) {
        enableLayer('day-container');
    } else {
        disableTargetSection();
    }
}

// 4. Handle "To" Section Clicks (Day -> Venue -> Time)
function handleSelection(type, element, value) {
    // UI Update
    const container = element.parentElement;
    Array.from(container.getElementsByClassName('pill-btn')).forEach(btn => btn.classList.remove('selected'));
    element.classList.add('selected');

    // Logic Flow
    if (type === 'day') {
        enableLayer('venue-container');
        resetLayer('venue-container');
        resetLayer('timeslot-container');
    } 
    else if (type === 'venue') {
        enableLayer('timeslot-container');
        resetLayer('timeslot-container');
    }
    else if (type === 'timeslot') {
        document.getElementById('save-btn').disabled = false;
    }
}

// --- Helpers ---

function enableLayer(id) {
    const container = document.getElementById(id);
    Array.from(container.getElementsByClassName('pill-btn')).forEach(btn => btn.classList.remove('disabled'));
}

function resetLayer(id) {
    const container = document.getElementById(id);
    Array.from(container.getElementsByClassName('pill-btn')).forEach(btn => {
        btn.classList.remove('selected');
        btn.classList.add('disabled');
    });
}

function disableTargetSection() {
    resetLayer('day-container');
    resetLayer('venue-container');
    resetLayer('timeslot-container');
    document.getElementById('save-btn').disabled = true;
}
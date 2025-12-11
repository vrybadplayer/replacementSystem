// --- State Management ---
// Tracks what is currently selected
let selection = {
    subject: null,
    day: null,
    venue: null,
    timeslot: null
};

// --- Step 1: Dropdown Change ---
function handleSubjectChange(selectElement) {
    selection.subject = selectElement.value;
    
    // Enable Layer 2 (Days)
    if (selection.subject) {
        enableSection('day-container');
    }
    
    // Reset subsequent layers if user changes subject
    resetSection('day-container'); 
    resetSection('venue-container');
    resetSection('timeslot-container');
    document.getElementById('submit-btn').disabled = true;
}

// --- Generic Click Handler for Pills ---
function handleSelection(type, element, value) {
    // 1. Update State
    selection[type] = value;

    // 2. Update UI (Visual Selection)
    // Remove 'selected' from all siblings
    const container = element.parentElement;
    const buttons = container.getElementsByClassName('pill-btn');
    Array.from(buttons).forEach(btn => btn.classList.remove('selected'));
    
    // Add 'selected' to clicked button
    element.classList.add('selected');

    // 3. Logic: Trigger Next Layer
    if (type === 'day') {
        enableSection('venue-container');
        // Simulate filtering: Reset future steps
        resetSection('venue-container', true); // true = keep enabled but clear selection
        resetSection('timeslot-container'); 
    } 
    else if (type === 'venue') {
        enableSection('timeslot-container');
        resetSection('timeslot-container', true);
    }
    else if (type === 'timeslot') {
        // All done, enable submit
        document.getElementById('submit-btn').disabled = false;
    }
}

// --- Helpers ---

function enableSection(containerId) {
    const container = document.getElementById(containerId);
    const buttons = container.getElementsByClassName('pill-btn');
    Array.from(buttons).forEach(btn => {
        btn.classList.remove('disabled');
    });
}

// Resets a section to unselected state
function resetSection(containerId, keepEnabled = false) {
    const container = document.getElementById(containerId);
    const buttons = container.getElementsByClassName('pill-btn');
    Array.from(buttons).forEach(btn => {
        btn.classList.remove('selected');
        if (!keepEnabled) {
            btn.classList.add('disabled');
        }
    });
}
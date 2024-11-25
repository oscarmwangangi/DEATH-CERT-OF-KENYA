document.addEventListener('DOMContentLoaded', () => {
    const formatInput = (input) => {
        return input.replace(/\b\w+/g, (word) => {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        });
    };

    const inputsToFormat = [
        'Death_in_the', 
        'District_in_the', 
        'Entry_no', 
        'Place_of_Death', 
        'Occupation', 
        'Name_and_Surname_of_Deceased', 
        'Cause_of_Death', 
        'Name_and_Description_of_Informant', 
        'Name_of_Registering_Officer',
        'District_Assistance',
        'Registrar',
        'year_year',
        'month_of_year',
        'day_of_week',
        'Residence',
        'Age_of_Deceased',
        'sex'
       
    ];

    inputsToFormat.forEach(id => {
        let inputElement = document.getElementById(id);
        if (inputElement) {
            inputElement.addEventListener('blur', (event) => {
                event.target.value = formatInput(event.target.value);
            });
        }
    });

    // Load the saved positions
    loadPositions();
});

function previewForm() {
    const fields = [
        'Death_in_the', 
        'District_in_the', 
        'Entry_no', 
        'Place_of_Death', 
        'Occupation', 
        'Date_of_death', 
        'sex', 
        'Name_and_Surname_of_Deceased', 
        'Cause_of_Death', 
        'Name_and_Description_of_Informant', 
        'Name_of_Registering_Officer', 
        'Date_of_Registration', 
        'District_Assistance', 
        'Registrar', 
        'year_year', 
        'month_of_year', 
        'day_of_week', 
        'Residence',
        'Age_of_Deceased',
        'Date',
       
    ];

    fields.forEach(field => {
        const inputElement = document.getElementById(field);
        const previewElement = document.getElementById('preview' + field);
        if (inputElement && previewElement) {
            previewElement.textContent = inputElement.value;
        }
    });

    document.getElementById("previewContainer").style.display = "block";
    makeElementsDraggable();
}

function makeElementsDraggable() {
    const draggable = document.querySelectorAll(".draggable");
    const parent = document.querySelector(".preview-container");

    draggable.forEach(node => {
        let offsetX, offsetY;
        let isDragging = false;

        node.addEventListener("mousedown", (e) => {
            isDragging = true;
            offsetX = e.clientX - node.offsetLeft;
            offsetY = e.clientY - node.offsetTop;
            node.style.cursor = "grabbing";
        });

        document.addEventListener("mousemove", (e) => {
            if (isDragging) {
                let newX = e.clientX - offsetX;
                let newY = e.clientY - offsetY;

                // Ensure the node stays within the parent bounds
                newX = Math.max(0, Math.min(newX, parent.clientWidth - node.clientWidth));
                newY = Math.max(0, Math.min(newY, parent.clientHeight - node.clientHeight));

                node.style.left = newX + "px";
                node.style.top = newY + "px";

                // Save the new position
                savePosition(node, newX, newY);
            }
        });

        document.addEventListener("mouseup", () => {
            isDragging = false;
            node.style.cursor = "grab";
        });

        // Load saved position
        loadPosition(node);
    });
}

function savePosition(node, x, y) {
    const id = node.id;
    if (id) {
        localStorage.setItem(`position_${id}`, JSON.stringify({ x, y }));
    }
}

function loadPosition(node) {
    const id = node.id;
    if (id) {
        const savedPosition = localStorage.getItem(`position_${id}`);
        if (savedPosition) {
            const { x, y } = JSON.parse(savedPosition);
            node.style.left = `${x}px`;
            node.style.top = `${y}px`;
        }
    }
}

function loadPositions() {
    const draggable = document.querySelectorAll(".draggable");
    draggable.forEach(node => {
        loadPosition(node);
    });
}

function printPreview() {
    const printContent = document.getElementById("previewContainer").innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload(); // Reload the page to reset the script
}

document.getElementById("Date_of_death").max = new Date().toISOString().split("T")[0];
document.getElementById("Date_of_Registration").max = new Date().toISOString().split("T")[0];
document.getElementById("Date").max = new Date().toISOString().split("T")[0];


//search functionality

function searchEntry() {
    const entryNo = document.getElementById("searchEntryNo").value;
    if (!entryNo) {
        alert("Please enter an entry number");
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "search_entry.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log(xhr.responseText); // Log the response for debugging
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                const data = response.data;
                console.log(data); // Log data to inspect what is received

                document.getElementById("Death_in_the").value = data.Death_in_the || '';
                document.getElementById("Name_of_the_filler").value = data.Name_of_the_filler || '';
                document.getElementById("District_in_the").value = data.District_in_the || '';
                document.getElementById("Entry_no").value = data.Entry_no || '';
                document.getElementById("Place_of_Death").value = data.Place_of_Death || '';
                document.getElementById("Occupation").value = data.Occupation || '';
                document.getElementById("Date_of_death").value = data.Date_of_death || '';
                document.getElementById("sex").value = data.sex || '';
                document.getElementById("Name_and_Surname_of_Deceased").value = data.Name_and_Surname_of_Deceased || '';
                document.getElementById("Cause_of_Death").value = data.Cause_of_Death || '';
                document.getElementById("Name_and_Description_of_Informant").value = data.Name_and_Description_of_Informant || '';
                document.getElementById("Name_of_Registering_Officer").value = data.Name_of_Registering_Officer || '';
                document.getElementById("Date_of_Registration").value = data.Date_of_Registration || '';
                document.getElementById("District_Assistance").value = data.District_Assistance || '';
                document.getElementById("Registrar").value = data.Registrar || '';
                document.getElementById("year_year").value = data.year_year || '';
                document.getElementById("month_of_year").value = data.month_of_year || '';
                document.getElementById("day_of_week").value = data.day_of_week || '';
                document.getElementById("Residence").value = data.Residence || '';
                document.getElementById("Age_of_Deceased").value = data.Age_of_Deceased || '';
                document.getElementById("Date").value = data.Date || '';
            } else {
                alert(response.message);
            }
        }
    };
    xhr.send(`Entry_no=${entryNo}`);
}


document.addEventListener('DOMContentLoaded', () => {
    const formatInput = (input) => {
        return input.replace(/\b\w+/g, (word) => {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        });
    };

    const inputsToFormat = [
        'Name_of_the_filler',
        'Death_in_the', 
        'District_in_the', 
        'Entry_no', 
        'Place_of_Death', 
        'Occupation', 
        'Name_and_Surname_of_Deceased', 
        'Cause_of_Death', 
        'Name_and_Description_of_Informant', 
        'Name_of_Registering_Officer',
        'District_Assistance',
        'Registrar',
        'year_year',
        'month_of_year',
        'day_of_week',
        'Residence',
        'Age_of_Deceased'
    ];

    inputsToFormat.forEach(id => {
        let inputElement = document.getElementById(id);
        if (inputElement) {
            inputElement.addEventListener('blur', (event) => {
                event.target.value = formatInput(event.target.value);
            });
        }
    });
});

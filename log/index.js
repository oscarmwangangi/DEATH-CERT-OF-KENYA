

$(document).ready(function(){
    $('#Entry_no').on('input', function(){
        let entryNo = $(this).val();
        if (entryNo.length <= 12 ) {
            $.ajax({
                url: 'Entry.php',
                method: 'POST',
                data: {Entry_no: entryNo},
                success: function(response) {
                    if(response === 200||OK) {
                        $('#message').text('Entry No not found. Please fill in manually.');
                        $('input[type="text"]').not('#Entry_no').val('');
                    } else {
                        let data = JSON.parse(response);
                        $('#message').text('');
                        $('#Death_in_the').val(data.Death_in_the);
                        $('#District_in_the').val(data.District_in_the);
                        // add other fields as needed
                        $('#Entry_no').val(data.Entry_no);
                        $('#Place_of_Death').val(data.Place_of_Death);
                        $('#Occupation').val(data.Occupation);
                        $('#Date_of_death').val(data.Date_of_death);
                        $('#sex').val(data.sex);
                        $('#Name_and_Surname_of_Deceased').val(data.Name_and_Surname_of_Deceased);
                        $('#Cause_of_Death').val(data.Cause_of_Death);
                        $('#Name_and_Description_of_Informant').val(data.Name_and_Description_of_Informant);
                        $('#Name_of_Registering_Officer').val(data.Name_of_Registering_Officer);
                        $('#Date_of_Registration').val(data.Date_of_Registration);
                        $('#District_Assistance').val(data.District_Assistance);
                        $('#Registrar').val(data.Registrar);
                        $('#Residence').val(data.Residence);
                        $('#Age_of_Deceased').val(data.Age_of_Deceased);
                        $('#Date').val(data.Date);
                     
                    }
                }
            });
        }
    });
});

//draggable
function previewForm() {
    const Death_in_the = document.getElementById('Death_in_the').value;
    const District_in_the = document.getElementById('District_in_the').value;
    const Entry_no = document.getElementById('Entry_no').value;
    const Place_of_Death = document.getElementById('Place_of_Death').value;
    const Name = document.getElementById('Occupation').value;
    const Date_of_death = document.getElementById('Date_of_death').value;
    const sex = document.getElementById('sex').value;
    const Name_and_Surname_of_Deceased = document.getElementById('Name_and_Surname_of_DeceasedName_and_Surname_of_Deceased').value;
    const Cause_of_Death = document.getElementById('Cause_of_Death').value;
    const Name_and_Description_of_Informant = document.getElementById('Name_and_Description_of_Informant').value;
    const Name_of_Registering_Officer = document.getElementById('Name_of_Registering_Officer').value;
    const Date_of_Registration = document.getElementById('Date_of_Registration').value;
    const District_Assistance = document.getElementById('District_Assistance').value;
    const Registrar = document.getElementById('Registrar').value;
    const Residence = document.getElementById('Residence').value;
    const Age_of_Deceased = document.getElementById('Age_of_Deceased').value;
    const Date = document.getElementById('Date').value;
    


    document.getElementById('previewDeath_in_the').innerText = Death_in_the;
    document.getElementById('previewDistrict_in_the').innerText = District_in_the;
    document.getElementById('previewEntry_no').innerText = Entry_no;
    document.getElementById('previewPlace_of_Death').innerText = Place_of_Death;
    document.getElementById('previewOccupation').innerText = Occupation;
    document.getElementById('previewDate_of_death').innerText = Date_of_death;
    document.getElementById('previewsex').innerText = sex;
    document.getElementById('previewName_and_Surname_of_DeceasedName_and_Surname_of_Deceased').innerText = Name_and_Surname_of_DeceasedName_and_Surname_of_Deceased;
    document.getElementById('previewCause_of_Death').innerText = Cause_of_Death;
    document.getElementById('previewName_and_Description_of_Informant').innerText = Name_and_Description_of_Informant;
    document.getElementById('previewName_of_Registering_Officer').innerText = Name_of_Registering_Officer;
    document.getElementById('previewDate_of_Registration').innerText = Date_of_Registration;
    document.getElementById('previewDistrict_Assistance').innerText = District_Assistance;
    document.getElementById('previewRegistrar').innerText = Registrar;
    document.getElementById('previewResidence').innerText = Residence;
    document.getElementById('previewAge_of_Deceased').innerText = Age_of_Deceased;
    document.getElementById('previewDate').innerText = Date;

    document.getElementById('previewContainer').style.display = 'block';

    // Make each preview element draggable
    dragElement(document.getElementById('previewDistrict_in_the'));
    dragElement(document.getElementById('previewDeath_in_the'));
    dragElement(document.getElementById('previewEntry_no'));
    dragElement(document.getElementById('previewPlace_of_Death'));
    dragElement(document.getElementById('previewOccupation'));
    dragElement(document.getElementById('previewDate_of_death'));
    dragElement(document.getElementById('previewsex'));
    dragElement(document.getElementById('previewName_and_Surname_of_DeceasedName_and_Surname_of_Deceased'));
    dragElement(document.getElementById('previewCause_of_Death'));
    dragElement(document.getElementById('previewName_and_Description_of_Informant'));
    dragElement(document.getElementById('previewName_of_Registering_Officer'));
    dragElement(document.getElementById('previewDate_of_Registration'));
    dragElement(document.getElementById('previewDistrict_Assistance'));
    dragElement(document.getElementById('previewRegistrar'));
    dragElement(document.getElementById('Residence'));
    dragElement(document.getElementById('Age_of_Deceased'));
    dragElement(document.getElementById('previewDate'));
}

function printPreview() {
    const printContent = document.getElementById('previewContent').innerHTML;
    const originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();  // Reload the page to reset the script
}

function dragElement(element) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

    element.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closedragElement;
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        element.style.top = (element.offsetTop - pos2) + "px";
        element.style.left = (element.offsetLeft - pos1) + "px";
    }

    function closedragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

//popup    
const hamburer = document.querySelector(".hamburger");
const navList = document.querySelector(".nav-list");

if (hamburer) {
hamburer.addEventListener("click", () => {
navList.classList.toggle("open");
});
}

// Popup
const popup = document.querySelector(".popup");
const closePopup = document.querySelector(".popup-close");

if (popup) {
closePopup.addEventListener("click", () => {
popup.classList.add("hide-popup");
});

window.addEventListener("load", () => {
setTimeout(() => {
  popup.classList.remove("hide-popup");
}, 1000);
});
}
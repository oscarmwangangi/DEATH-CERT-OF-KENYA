<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ../index.php");
    exit(); // Ensure no further code is executed
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta Name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./log/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap-grid.min.css">
     
    <title>DEATH CERTIFICATE OF KENYA</title>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 0%;
        }
        .btn-submit, .logout {
            background-color: #007bff;
            color: white;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
        }
        .btn-submit:hover, .logout:hover {
            background-color: #0056b3;
        }
        .preview-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .draggable {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .img-fluid {
            max-width: 150px;
            height: auto;
        }
        .form-group label {
            margin-bottom: 0.5rem;
        }
        .text-bg-primary{
         background-color: #007bff;
         border-radius: 8px;
         border: none;
         color: white;
        }
        .text-bg-primary:hover{
            cursor: pointer;
            background-color: #0056b3;
        }
    </style>

</head>

<body>
   <div>
    <div class="container p-4">
        <h2 class="text-center">REPUBLIC OF KENYA</h2>
        <hr>


        <h1 class="text-center">CERTIFICATE OF DEATH</h1>
        <form action="submission.php" method="POST" onsubmit="alert('Are you sure you want to submit this record?')">
            <h3 style="color:red;
            font-family: sans-serif;">This a Correction page</h3>
            <!-- // session messages -->
            <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']); // Clear the message after displaying it
            }

            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']); // Clear the message after displaying it
            }
            ?>
            
            <!-- Search Box -->
         <!-- Search Box -->
         <div class="row mt-4">
            <div class="col-md-3">
                <input type="text" id="searchEntryNo" placeholder="Enter Entry No">
            </div>
            <div class="col-md-3">
                <button type="button" onclick="searchEntry()" class="p-3 mb-3 text-bg-primary rounded-3">Search</button>
            </div> 
            <div class="ml-3">
                <label for="Name of the filler"> Name of the filler </label>
            </div>
            <div class="col col-md-3">
                <input type="text" required Name="Name_of_the_filler" id="Name_of_the_filler">
            </div>
        </div>
        
            <div class="row mt-4">
                <div class="col-flex ml-3">
                    <label for="County">Death in the </label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Death_in_the" placeholder="DISTRICT" id="Death_in_the">
                </div>
                <div class="col-md-2">
                    <label for="District in the">District in the </label>
                </div>
                <div class="col-md-4">
                    <input type="text" required Name="District_in_the" placeholder="PROVINCE" id="District_in_the">
                </div>
                <div class="col-md-1">
                    Province
                </div>
               

            </div>

            <div class="row mt-4">
                <div class="col-md-1">
                    <label for="Entry no">Entry no </label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Entry_no" placeholder="L12345678/12" id="Entry_no">
                </div>

                <div class="col-md-2">
                    <label for="father_Name">Name and Surname of Deceased </label>
                </div>
                <div class="col-md-5">
                    <input type="text" required Name="Name_and_Surname_of_Deceased" id="Name_and_Surname_of_Deceased">
                </div>

            </div>

            <div class="row mt-4">
                <div class="col-md-1">
                    <label for="sex"> Sex </label>
                </div>

                <div class="col-md-3">
                    
                   <input type="text" required Name="sex" id="sex">
                    <!-- <select Name="sex" id="sex">
                        <option value="MALE">MALE</option>
                        <option value="FEMALE">FEMALE</option>
                    </select> -->
                </div>
                <div class="col-md-1">
                    <label for="Age_of_Deceased">Age<label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Age_of_Deceased" id="Age_of_Deceased">
                </div>
                <div class="col-md-1">
                    <label for="Occupation">Occupation</label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Occupation" id="Occupation">
                </div>               
            </div>

            <div class="row mt-4">
              
            <div class="col-md-1">
                    <label for="Date of death"> Date of death<label>
                </div>
                <div class="col-md-3">
                    <input type="date" required Name="Date_of_death" id="Date_of_death">
                </div>
                <div class="col-md-1">
                    <label for="Place of Death"> Place of Death </label>
                </div>

                <div class="col-md-3">
                    <input type="text" required Name="Place_of_Death" id="Place_of_Death">
                </div>
                <div class="col-md-1">
                    <label for="Residence">Residence </label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Residence" id="Residence">
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-1">
                    <label for="Cause of Death"> Cause of Death </label>
                </div>
                <div class="col">
                    <input type="text" required Name="Cause_of_Death" id="Cause_of_Death">
                </div>
            </div>
            <div class="row mt-4">
              
            <div class="col-md-1">
                    <label for="Cause of Death"> Name and Description of Informant </label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Name_and_Description_of_Informant" id="Name_and_Description_of_Informant">
                </div>
                <div class="col-md-1">
                    <label for="Name registering officer">Name of registering officer </label>
                </div>
                <div class="col-md-3">
                    <input type="text" required Name="Name_of_Registering_Officer" id="Name_of_Registering_Officer">
                </div>
                <div class="col-md-1">
                    <label for="Date of registration"> Date of registration </label>
                </div>
                <div class="col-md-3">
                    <input type="date" required Name="Date_of_Registration"
                    id="Date_of_Registration">
                </div>
            </div>
            <div class="row mt-4">
                
            </div>

            <div class="row mt-4">
               

                
            </div>

            <div class="row mt-1">
                <div class="col-12">
                    <label for="Districtassistance">I
                        <input type="text" required Name="District_Assistance" class="w-unset" id="District_Assistance">
                        District/Assistant</label>
                </div>
                <div class="col-12 mt-1">
                    <p>Register for<label for="registrar"> <input type="text" required Name="Registrar"
                                class="w-unset w-50" id="Registrar">District, hereby
                            certify that this certificate
                            is compiled from an entry/return in the Register of Deaths in the District</label></p>
                </div>
            </div>
             <!-- <p>Given under the seal of the Director of Civil Registration on the <label for="date"> <input type="date"
                        required Name="Date" class="w-unset w-50" id="Date"></label></p> -->

                        <div class="row mt-1">
            <div class="col-md-12">
                <p>
            <label for="day_of_week">
                Given under the seal of the Director of Civil Registration on the                
                <input type="text" required name="day_of_week" class="form-control d-inline-block" style="width: 200px !important;" id="day_of_week">
            </label>

            <label for="month_of_year">
                day of
                <input type="text" required name="month_of_year" class="form-control d-inline-block" style="width: 200px !important;" id="month_of_year">
            </label>

            <label for="year_year">.
                <input type="text" required name="year_year" class="form-control d-inline-block" style="width: 200px !important;" id="year_year">
            </label>
        </p>
    </div>
</div>
            <div class="row justify-content-start mb-6 mt-4">
                <div class="col-md-12">
                    <button class="btn-submit" type="submit">save</button>
                </div>
            </div>
            

            <!-- <p>This Certificate is issued in pursuance of ther Deaths Registration Act(Cap.149) which provides
                that
                a certified copy of any entry in any register oor return purporting to be sealed or stamped with
                the
                Seal of Director of civil Registration shall be received as evidence of the dates and facts
                therein
                contained without any or other proof of such entry. </p> -->


            <hr>
            <p class="">
                GPK(1) 281-40M bKS-10/2023
            </p>
            <button type="button" onclick="previewForm()" class="logout">Preview</button>
        </form>

        <!-- <div id="previewContainer" class="preview-container"> -->
            <!-- <h2>Form Preview</h2>
            <div id="previewContent" class="preview-content">
                <p id="previewEntry_no" class="draggable"></p>
            </div>
            <button onclick="printPreview()">Print</button>
        </div> -->
       <h2>Form Preview</h2>
        <div id="previewContainer" class="preview-container">
           
            <div id="previewContent" class="preview-content">
                <p id="previewDeath_in_the" class="draggable"></p>
                <p id="previewDistrict_in_the" class="draggable"></p>                
                <p id="previewEntry_no" class="draggable"></p>
                <p id="previewPlace_of_Death" class="draggable"></p>
                <p id="previewOccupation" class="draggable"></p>
                <p id="previewDate_of_death" class="draggable"></p>
                <p id="previewsex" class="draggable"></p>
                <p id="previewName_and_Surname_of_Deceased" class="draggable"></p>
                <p id="previewCause_of_Death" class="draggable"></p>
                <p id="previewName_and_Description_of_Informant" class="draggable"></p>
                <p id="previewName_of_Registering_Officer" class="draggable"></p>
                <p id="previewDate_of_Registration" class="draggable"></p>
                <p id="previewDistrict_Assistance" class="draggable"></p>
                <p id="previewRegistrar" class="draggable"></p>
                <p id="previewyear_year" class="draggable"></p>
                <p id="previewmonth_of_year" class="draggable"></p>
                <p id="previewday_of_week" class="draggable"></p>
                <p id="previewResidence" class="draggable"></p>
                <p id="previewAge_of_Deceased" class="draggable"></p>
                <p id="previewDate" class="draggable"></p>
            </div>
            
        </div>
                <button onclick="printPreview()" class="logout">Print</button>

        <script src="./log/script.js"></script>
       
    
        <a href="./index.php" class="btn btn-dark btn-lg shadow rounded-pill">Back</a>
    </div>
        <!-- <script src="./script.js" charset="utf-8"></script> 
           
</body> -->

          
</html>



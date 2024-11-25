
<?php 
// Database configuration
// $serverName = "localhost";
// $userName = "root";
// $password = "";
// $dbName = "deathcertificate_db";

// Create a connection
// $conn = new mysqli($serverName, $userName, $password, $dbName);

// Check the connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// Death_in_the	District_in_the	Entry_no	Place_of_Death	Name	Date_of_death	Sex	Name_and_Surnam_of_Father	Name_and_Description_of_Informant	Name_of_Registering_Officer	Date_of_Registration	District_Assistance	Registrar	Date	user_id	
// Get form data
//$Birth = $_POST['Death in the'];
// $District = $_POST['District in the'];
// $entry = $_POST['Entry no'];
// $where = $_POST['Place of Death'];
// $yourName = $_POST['Name'];
// $DOB = $_POST['Date of death'];
// $gender = $_POST['Sex'];
// $Nameoffather = $_POST['Name_and_SurName_ofFather'];
// $Nameofmother = $_POST['Cause of Death'];
// $informant = $_POST['Name and Description of Informant'];
// $officer = $_POST['Name of registering officer'];
// $registration = $_POST['Date of registration'];
// $DisAs= $_POST['District/Assistance'];
// $Regfor = $_POST['Register for'];
// $submit = $_POST['submit'];


// Insert form data into the database
// $sql = "INSERT INTO deathcertificate_information (Death in the, District in the) VALUES ('$Name', '$email')";

// if ($conn->query($sql) === TRUE) {
//     echo "New record created successfully";
// } else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
// }

// Close the connection
// $conn->close();

// if get request to file redirect to index
require "config.php.";


// Create connection
// $conn = new mysqli($serverName, $userName, $password, $dbName);
$db_conn = mysqli_connect("localhost", "root", "", "deathcertificate_db");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind
// $stmt = $conn->prepare

// $stmt->bind_param("ssssssssssssssi", $Death_in_the, $District_in_the, $Entry_no, $Place_of_Death, $Name, $Date_of_death, $Sex, $Name_and_Surname_of_Deceased, $Name_and_Description_of_Informant, $Name_of_Registering_Officer, $Date_of_Registration, $District_Assistance, $Registrar, $Date, $user_id);

// Set parameters and execute
$Death_in_the = $_POST['Death_in_the'];
$Death_in_the = $_POST['Name_of_the_filler'];
$District_in_the = $_POST['District_in_the'];
$Entry_no = $_POST['Entry_no'];
$Place_of_Death = $_POST['Place_of_Death'];
$Occupation= $_POST['Occupation'];
$Date_of_death = $_POST['Date_of_death'];
$sex = $_POST['sex'];
$Name_and_Surname_of_Deceased = $_POST['Name_and_Surname_of_Deceased'];
$Cause_of_Death = $_POST['Cause_of_Death'];
$Name_and_Description_of_Informant = $_POST['Name_and_Description_of_Informant'];
$Name_of_Registering_Officer = $_POST['Name_of_Registering_Officer'];
$Date_of_Registration = $_POST['Date_of_Registration'];
$District_Assistance = $_POST['District_Assistance'];
$Registrar = $_POST['Registrar'];
$Date = $_POST['Date'];
$user_id = $_POST['user_id'];

if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

$sql=("INSERT INTO deathcertificate_information (Death_in_the, District_in_the, Name_of_the_filler, Entry_no, Place_of_Death, Date_of_death, sex, Cause_of_Death, Name_and_Surname_of_Deceased, Name_and_Description_of_Informant, Name_of_Registering_Officer, Date_of_Registration, District_Assistance, Registrar, user_id) VALUES ($Death_in_the, $District_in_the, $Name_of_the_filler $Entry_no, $Place_of_Death, $Occupation, $Date_of_death, $sex, $Name_and_Surname_of_Deceased, $Cause_of_Death, $Name_and_Description_of_Informant, $Name_of_Registering_Officer, $Date_of_Registration, $District_Assistance, $Registrar, $Date, $user_id)");


    // Code to save $userInput to the database goes here
    // You'll need to use database-specific code or a database abstraction layer like PDO or MySQLi

    // Remember to handle any necessary validations, sanitizations, and error checking before saving to the database


$stmt->close();
$conn->close();


?>

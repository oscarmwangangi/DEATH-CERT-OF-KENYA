<?php
// Start output buffering at the very beginning to capture any unintentional output
ob_start();

set_include_path(get_include_path() . PATH_SEPARATOR . 'path/to/config/directory');
require './log/config.php';

// Start session to use session variables
session_start();

// if get request to file redirect to index
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: fill.php');
    exit();  // Use exit() to ensure no further code is executed
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $Death_in_the = filter_input(INPUT_POST, 'Death_in_the', FILTER_SANITIZE_SPECIAL_CHARS);
    $Name_of_the_filler = filter_input(INPUT_POST, 'Name_of_the_filler', FILTER_SANITIZE_SPECIAL_CHARS);
    $District_in_the = filter_input(INPUT_POST, 'District_in_the', FILTER_SANITIZE_SPECIAL_CHARS);
    $Entry_no = filter_input(INPUT_POST, 'Entry_no', FILTER_SANITIZE_SPECIAL_CHARS);
    $Place_of_Death = filter_input(INPUT_POST, 'Place_of_Death', FILTER_SANITIZE_SPECIAL_CHARS);
    $Occupation = filter_input(INPUT_POST, 'Occupation', FILTER_SANITIZE_SPECIAL_CHARS);
    $Date_of_death = filter_input(INPUT_POST, 'Date_of_death', FILTER_SANITIZE_SPECIAL_CHARS);
    $sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_SPECIAL_CHARS);
    $Name_and_Surname_of_Deceased = filter_input(INPUT_POST, 'Name_and_Surname_of_Deceased', FILTER_SANITIZE_SPECIAL_CHARS);
    $Cause_of_Death = filter_input(INPUT_POST, 'Cause_of_Death', FILTER_SANITIZE_SPECIAL_CHARS);
    $Name_and_Description_of_Informant = filter_input(INPUT_POST, 'Name_and_Description_of_Informant', FILTER_SANITIZE_SPECIAL_CHARS);
    $Name_of_Registering_Officer = filter_input(INPUT_POST, 'Name_of_Registering_Officer', FILTER_SANITIZE_SPECIAL_CHARS);
    $Date_of_Registration = filter_input(INPUT_POST, 'Date_of_Registration', FILTER_SANITIZE_SPECIAL_CHARS);
    $District_Assistance = filter_input(INPUT_POST, 'District_Assistance', FILTER_SANITIZE_SPECIAL_CHARS);
    $Registrar = filter_input(INPUT_POST, 'Registrar', FILTER_SANITIZE_SPECIAL_CHARS);
    $year_year = filter_input(INPUT_POST, 'year_year', FILTER_SANITIZE_SPECIAL_CHARS);
    $month_of_year = filter_input(INPUT_POST, 'month_of_year', FILTER_SANITIZE_SPECIAL_CHARS);
    $day_of_week = filter_input(INPUT_POST, 'day_of_week', FILTER_SANITIZE_SPECIAL_CHARS);
    $Residence = filter_input(INPUT_POST, 'Residence', FILTER_SANITIZE_SPECIAL_CHARS);
    $Age_of_Deceased = filter_input(INPUT_POST, 'Age_of_Deceased', FILTER_SANITIZE_SPECIAL_CHARS);
    $Date = filter_input(INPUT_POST, 'Date', FILTER_SANITIZE_SPECIAL_CHARS);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    // Additional validation if necessary
    // Example: Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $Date_of_Registration)) {
        $_SESSION['error'] = 'Invalid date format for Date of Registration.';
        header('Location: form_page.php');
        exit();
    }


    // Update data in the database
    $sql = "UPDATE deathcertificate_information SET
                Death_in_the = :Death_in_the,
                District_in_the = :District_in_the,
                Place_of_Death = :Place_of_Death,
                Occupation = :Occupation,
                Date_of_death = :Date_of_death,
                sex = :sex,
                Name_and_Surname_of_Deceased = :Name_and_Surname_of_Deceased,
                Cause_of_Death = :Cause_of_Death,
                Name_and_Description_of_Informant = :Name_and_Description_of_Informant,
                Name_of_Registering_Officer = :Name_of_Registering_Officer,
                Date_of_Registration = :Date_of_Registration,
                District_Assistance = :District_Assistance,
                Registrar = :Registrar,
                year_year = :year_year,
                month_of_year = :month_of_year,
                day_of_week = :day_of_week,
                Residence = :Residence,
                Age_of_Deceased = :Age_of_Deceased,
                Date = :Date,
                user_id = :user_id
            WHERE Entry_no = :Entry_no";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':Death_in_the' => $Death_in_the,
        ':District_in_the' => $District_in_the,
        ':Place_of_Death' => $Place_of_Death,
        ':Occupation' => $Occupation,
        ':Date_of_death' => $Date_of_death,
        ':sex' => $sex,
        ':Name_and_Surname_of_Deceased' => $Name_and_Surname_of_Deceased,
        ':Cause_of_Death' => $Cause_of_Death,
        ':Name_and_Description_of_Informant' => $Name_and_Description_of_Informant,
        ':Name_of_Registering_Officer' => $Name_of_Registering_Officer,
        ':Date_of_Registration' => $Date_of_Registration,
        ':District_Assistance' => $District_Assistance,
        ':Registrar' => $Registrar,
        ':year_year' => $year_year,
        ':month_of_year' => $month_of_year,
        ':day_of_week' => $day_of_week,
        ':Residence' => $Residence,
        ':Age_of_Deceased' => $Age_of_Deceased,
        ':Date' => $Date,
        ':user_id' => $user_id,
        ':Entry_no' => $Entry_no // Ensure the correct Entry_no is used for the WHERE clause
    ]);
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':Death_in_the' => $Death_in_the,
        ':District_in_the' => $District_in_the,
        ':Entry_no' => $Entry_no,
        ':Place_of_Death' => $Place_of_Death,
        ':Occupation' => $Occupation,
        ':Date_of_death' => $Date_of_death,
        ':sex' => $sex,
        ':Name_and_Surname_of_Deceased' => $Name_and_Surname_of_Deceased,
        ':Cause_of_Death' => $Cause_of_Death,
        ':Name_and_Description_of_Informant' => $Name_and_Description_of_Informant,
        ':Name_of_Registering_Officer' => $Name_of_Registering_Officer,
        ':Date_of_Registration' => $Date_of_Registration,
        ':District_Assistance' => $District_Assistance,
        ':Registrar' => $Registrar,
        ':year_year' => $year_year,
        ':month_of_year' => $month_of_year,
        ':day_of_week' => $day_of_week,
        ':Residence' => $Residence,
        ':Age_of_Deceased' => $Age_of_Deceased,
        ':Date' => $Date,
        ':user_id' => $user_id
    ]);

    $_SESSION['success'] = "Record updated successfully!";
    header('Location: correction_form.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: correction_form.php');
    exit();
}
?>

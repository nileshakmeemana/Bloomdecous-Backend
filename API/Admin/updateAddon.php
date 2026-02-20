<?php

require '../../API/Connection/BackEndPermission.php';

// Fetch POST data
$Id = $_POST['Id'];
$Addon_Name = $_POST['Addon_Name'];
$Addon_description = $_POST['Addon_description'];
$Addon_Price = $_POST['Addon_Price'];
$existing_img = $_POST['existing_img'] ?? ''; // Hidden field from the form

$response = new stdClass();

// Basic validation
if (empty($Id) || empty($Addon_Name) || empty($Addon_description)) {
    $response->success = 'false';
    $response->error = 'empty';
    echo json_encode($response);
    exit;
}

// Check if Id exists
$checkQuery = "SELECT * FROM `tbl_addon` WHERE `Id` = '$Id'";
$result = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($result) === 0) {
    $response->success = 'false';
    $response->error = 'no_addon_data';
    echo json_encode($response);
    exit;
}

if (isset($_FILES['Img']) && $_FILES['Img']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg','image/jpg','image/png'];
    if (in_array($_FILES['Img']['type'], $allowedTypes)) {
        $fileExtension = pathinfo($_FILES['Img']['name'], PATHINFO_EXTENSION);
        $uploadDir = '../../Images/Addons/';
        $newFileName = $Id . '.' . $fileExtension;
        $fullPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['Img']['tmp_name'], $fullPath)) {
            $imgPath = 'Images/Addons/' . $newFileName . '?v=' . time(); // relative path
        }
    }
} else {
    // Keep existing image relative
    if (!empty($existing_img)) {
        $existing_img = preg_replace('#.*/(Images/.*)$#i', '$1', $existing_img);
        $imgPath = $existing_img;
    }
}

// Update the database
$updateQuery = "UPDATE `tbl_addon` 
                SET `Addon_Name` = '$Addon_Name', 
                    `Addon_description` = '$Addon_description', 
                    `Addon_Price` = '$Addon_Price', 
                    `Img` = '$imgPath' 
                WHERE `Id` = '$Id'";

if (mysqli_query($conn, $updateQuery)) {
    $response->success = 'true';
} else {
    $response->success = 'false';
}

echo json_encode($response);
?>
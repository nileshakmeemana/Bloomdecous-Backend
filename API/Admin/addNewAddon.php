<?php

require '../../API/Connection/BackEndPermission.php';

$Addon_Name = mysqli_real_escape_string($conn, $_POST['Addon_Name']);
$Addon_description = mysqli_real_escape_string($conn, $_POST['Addon_description']);
$Addon_Price = mysqli_real_escape_string($conn, $_POST['Addon_Price']);
$Img = ""; // default empty

if (empty($Addon_Name) || empty($Addon_description )) {
    $myObj = new \stdClass();
    $myObj->success = 'false';
    $myObj->error = 'empty';
    echo json_encode($myObj);
    exit();
} 

// Check if Addon_Name already exists
$checkQuery = "SELECT * FROM `tbl_addon` WHERE `Addon_Name`='$Addon_Name'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {

    // ================= UPDATE =================
    $row = mysqli_fetch_assoc($checkResult);
    $Id = $row['Id']; // get existing ID

    // Upload image if provided
    if (isset($_FILES['Img']) && $_FILES['Img']['error'] === UPLOAD_ERR_OK) {

        $fileTmpPath = $_FILES['Img']['tmp_name'];
        $fileName = $_FILES['Img']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {

            $uploadDir = "../../Images/Addons/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imageLocation = $uploadDir . $Id . "." . $fileExtension;
            $imagePath = "Images/Addons/" . $Id . "." . $fileExtension;
            $Img = $imagePath . '?v=' . time();

            move_uploaded_file($fileTmpPath, $imageLocation);
        }
    }

    if (!empty($Img)) {
        $sql = "UPDATE `tbl_addon`
                SET `Img` = '$Img',
                    `Addon_description` = '$Addon_description',
                    `Addon_Price` = '$Addon_Price'
                WHERE `Addon_Name` = '$Addon_Name'";
    } else {
        $sql = "UPDATE `tbl_addon`
                SET `Addon_description` = '$Addon_description',
                    `Addon_Price` = '$Addon_Price'
                WHERE `Addon_Name` = '$Addon_Name'";
    }

    if (mysqli_query($conn, $sql)) {
        $myObj = new \stdClass();
        $myObj->success = 'true';
        echo json_encode($myObj);
    } else {
        $myObj = new \stdClass();
        $myObj->success = 'false';
        $myObj->error = 'update_failed_details';
        echo json_encode($myObj);
    }

} else {

    // ================= INSERT =================

    $sql1 = "INSERT INTO `tbl_addon`
             (`Addon_Name`, `Addon_description`, `Addon_Price`)
             VALUES ('$Addon_Name', '$Addon_description', '$Addon_Price')";

    if (mysqli_query($conn, $sql1)) {

        $Id = mysqli_insert_id($conn); // get new ID

        // Upload image if provided
        if (isset($_FILES['Img']) && $_FILES['Img']['error'] === UPLOAD_ERR_OK) {

            $fileTmpPath = $_FILES['Img']['tmp_name'];
            $fileName = $_FILES['Img']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExtensions)) {

                $uploadDir = "../../Images/Addons/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $imageLocation = $uploadDir . $Id . "." . $fileExtension;
                $imagePath = "Images/Addons/" . $Id . "." . $fileExtension;
                $Img = $imagePath . '?v=' . time();

                move_uploaded_file($fileTmpPath, $imageLocation);

                // Update image path in DB
                mysqli_query($conn, "UPDATE `tbl_addon` SET `Img`='$Img' WHERE `Id`='$Id'");
            }
        }

        $myObj = new \stdClass();
        $myObj->success = 'true';
        echo json_encode($myObj);

    } else {

        $myObj = new \stdClass();
        $myObj->success = 'false';
        $myObj->error = 'insert_failed_addon';
        echo json_encode($myObj);
    }
}

mysqli_close($conn);
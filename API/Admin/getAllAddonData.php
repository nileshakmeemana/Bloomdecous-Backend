<?php

require '../../API/Connection/BackEndPermission.php';
include '../Connection/uploadurl.php';

// Fetch products with Qty > 0 and Inventort_Updated = 'True'
$sql = "SELECT 
        a.Id, 
        a.Addon_Name,
        a.Img,
        a.Addon_description,
        a.Addon_Price
    FROM 
        tbl_addon a
    ORDER BY 
        a.Id ASC";

$result = $conn->query($sql);

$dataset = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        $imgPath = $row["Img"];
        // Check if the imgPath is 'C:' and set default image path accordingly
        if ($imgPath === 'C:') {
            $imgPath = 'Images/Admins/admin.jpg'; // Set default image path
            $img_url = $base_url . $imgPath;
        } else {
            $img_url = $base_url . $imgPath;
        }

        array_push($dataset, array(
            "Id" => $row["Id"],
            "Addon_Name" => $row["Addon_Name"],
            "Img" => $img_url,
            "Addon_description" => $row["Addon_description"],
            "Addon_Price" => $row["Addon_Price"]
        ));
    }
}

echo json_encode($dataset);
mysqli_close($conn);

?>
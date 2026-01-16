<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require '../../API/Connection/config.php';

$dataset = array();

$sqlPopular = "
    SELECT 
        p.Package_Id,
        p.Package_Name,
        p.Package_Description,
        p.Price,
        COUNT(o.Id) AS order_count
    FROM tbl_package p
    LEFT JOIN tbl_orders o 
        ON p.Package_Id = o.Package_Id
    GROUP BY p.Package_Id
    ORDER BY order_count DESC
    LIMIT 3
";

$resultPopular = $conn->query($sqlPopular);

if ($resultPopular && $resultPopular->num_rows >= 3) {

    while ($row = $resultPopular->fetch_assoc()) {
        $dataset[] = array(
            "Package_Id" => $row["Package_Id"],
            "Package_Name" => $row["Package_Name"],
            "Package_Description" => $row["Package_Description"],
            "Price" => $row["Price"]
        );
    }

} else {

    $sqlFallback = "
        SELECT 
            Package_Id,
            Package_Name,
            Package_Description,
            Price
        FROM tbl_package
        ORDER BY Package_Id ASC
        LIMIT 3
    ";

    $resultFallback = $conn->query($sqlFallback);

    if ($resultFallback && $resultFallback->num_rows > 0) {
        while ($row = $resultFallback->fetch_assoc()) {
            $dataset[] = array(
                "Package_Id" => $row["Package_Id"],
                "Package_Name" => $row["Package_Name"],
                "Package_Description" => $row["Package_Description"],
                "Price" => $row["Price"]
            );
        }
    }
}

echo json_encode($dataset);
mysqli_close($conn);

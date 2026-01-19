<?php
// getDashboardSuperAdminData.php
require '../../API/Connection/config.php';
header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => 'false', 'message' => 'unauthorized']);
    exit;
}

// 1) Counts (Safe Queries)
function getCount($conn, $table) {
    $q = $conn->query("SELECT COUNT(*) AS c FROM $table");
    if ($q && $row = $q->fetch_assoc()) return (int)$row['c'];
    return 0;
}

$counts = [];

$counts['Count_Pending_Orders'] = getCount($conn, "tbl_orders WHERE Status = 'Pending'");
$counts['Count_Approved_Orders'] = getCount($conn, "tbl_orders WHERE Status = 'Approved'");
$counts['Count_Completed_Orders'] = getCount($conn, "tbl_orders WHERE Status = 'Completed'");
$counts['Count_Canceled_Rejected_Orders'] = getCount($conn, "tbl_orders WHERE Status IN ('Canceled', 'Rejected')");

$counts['Count_Customers'] = getCount($conn, "tbl_customers");
$counts['Count_Users'] = getCount($conn, "tbl_user");
$counts['Count_Roles'] = getCount($conn, "tbl_roles");
$counts['Count_Packages'] = getCount($conn, "tbl_package");
$counts['Count_Addon'] = getCount($conn, "tbl_addon");
$counts['Count_Reviews'] = getCount($conn, "tbl_reviews");

// 2) Fast Moving Packages (Top 10 by number of orders)
$fastPackages = [];

$q = $conn->query("
    SELECT 
        p.Package_Id,
        p.Package_Name,
        p.Package_Description,
        p.Price,
        COUNT(o.Order_Id) AS orders_count
    FROM tbl_orders o
    INNER JOIN tbl_package p 
        ON o.Package_Id = p.Package_Id
    GROUP BY p.Package_Id
    HAVING orders_count > 0
    ORDER BY orders_count DESC
    LIMIT 10
");

if ($q) {
    while ($r = $q->fetch_assoc()) {
        $fastPackages[] = [
            'package_id'    => $r['Package_Id'],
            'package_name'  => $r['Package_Name'],
            'description'   => $r['Package_Description'],
            'price'         => (float)$r['Price'],
            'orders_count'  => (int)$r['orders_count']
        ];
    }
}

// 3) Daily Orders of Last Month (Last 30 Days)
$dailyOrders = [];

$q = $conn->query("
    SELECT 
        DATE(Order_Date) AS order_date,
        COUNT(Id) AS total_orders
    FROM tbl_orders
    WHERE Order_Date >= CURDATE() - INTERVAL 30 DAY
    GROUP BY DATE(Order_Date)
    ORDER BY order_date ASC
");

if($q){
    while($r = $q->fetch_assoc()){
        $dailyOrders[] = [
            'date' => $r['order_date'],
            'total_orders' => (int)$r['total_orders']
        ];
    }
}

// 4) Customer based Fast Moving Addons (Top 10 by number of orders)
$customerAddons = [];

$q = $conn->query("
    SELECT
        c.Customer_Id,
        c.Customer_Name,
        a.Id AS addon_id,
        a.Addon_Name AS addon_name,
        a.Addon_Price AS price,
        COUNT(DISTINCT oa.Order_Id) AS orders_count
    FROM tbl_order_addons oa
    INNER JOIN tbl_orders o 
        ON oa.Order_Id = o.Order_Id
    INNER JOIN tbl_customers c 
        ON o.Customer_Id = c.Customer_Id
    INNER JOIN tbl_addon a 
        ON oa.Addon_Id = a.Id
    GROUP BY
        c.Customer_Id,
        c.Customer_Name,
        a.Id,
        a.Addon_Name,
        a.Addon_Price
    HAVING orders_count > 0
    ORDER BY orders_count DESC
    LIMIT 10
");

if ($q) {
    while ($r = $q->fetch_assoc()) {
        $customerAddons[] = [
            'customer_id'   => $r['Customer_Id'],
            'customer_name' => $r['Customer_Name'],
            'addon_id'      => $r['addon_id'],
            'addon_name'    => $r['addon_name'],
            'orders_count'  => (int)$r['orders_count'],
            'price'         => (float)$r['price']
        ];
    }
}

// 5) Top 10 Customers (Highest Ordering)
$topCustomers = [];

$q = $conn->query("
    SELECT 
        c.Customer_Id,
        c.Customer_Name,
        COALESCE(c.Customer_Address, 'N/A') AS Customer_Address,
        COALESCE(c.Customer_Contact, 'N/A') AS Customer_Contact,
        COALESCE(c.Customer_Email, 'N/A') AS Customer_Email,
        COUNT(o.Order_Id) AS order_count
    FROM tbl_customers c
    INNER JOIN tbl_orders o 
        ON o.Customer_Id = c.Customer_Id
    GROUP BY c.Customer_Id
    ORDER BY 
        order_count DESC
    LIMIT 10
");

if ($q) {
    while ($r = $q->fetch_assoc()) {
        $topCustomers[] = [
            'customer_id'   => $r['Customer_Id'],
            'customer_name' => $r['Customer_Name'],
            'address'       => $r['Customer_Address'],
            'contact_no'    => $r['Customer_Contact'],
            'email'         => $r['Customer_Email'],
            'order_count'   => (int)$r['order_count']
        ];
    }
}


// FINAL RESPONSE
echo json_encode([
    'success' => 'true',
    'pageData' => $counts,
    'fastPackages' => $fastPackages,
    'dailyOrders' => $dailyOrders,
    'customerAddons' => $customerAddons,
    'topCustomers' => $topCustomers
]);

$conn->close();

?>
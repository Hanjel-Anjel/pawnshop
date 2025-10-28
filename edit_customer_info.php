<?php
session_start();
@include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login_page.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";

// Function to fetch name from PSGC API based on code
function fetchNameFromAPI($url)
{
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        return $data['name'] ?? null;
    }
    return null;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name = trim($_POST['last_name']);
    $suffix = trim($_POST['suffix']);
    $birthday = $_POST['birthday'];
    $phone_no = trim($_POST['phone_no']);

    // Validate Birthday (18+)
    $dob = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    if ($age < 18) {
        $error_message = "You must be at least 18 years old.";
    } else {
        // Address fields
        $regionCode = $_POST['region'] ?? '';
        $provinceCode = $_POST['province'] ?? '';
        $cityCode = $_POST['city'] ?? '';
        $barangayCode = $_POST['barangay'] ?? '';
        $street = trim($_POST['street']);
        $houseNumber = trim($_POST['houseNumber']);

        // Fetch full names
        $region = fetchNameFromAPI("https://psgc.gitlab.io/api/regions/$regionCode/");
        $province = ($regionCode === "130000000") ? "Metro Manila" : fetchNameFromAPI("https://psgc.gitlab.io/api/provinces/$provinceCode/");
        $city = fetchNameFromAPI("https://psgc.gitlab.io/api/cities-municipalities/$cityCode/");
        $barangay = fetchNameFromAPI("https://psgc.gitlab.io/api/barangays/$barangayCode/");

        // Update query
        $stmt = $conn->prepare("UPDATE custumer_info SET first_name=?, middle_initial=?, last_name=?, suffix=?, birthday=?, phone_no=?, region=?, province=?, city=?, barangay=?, street=?, house_number=? WHERE user_id=?");
        $stmt->bind_param("ssssssssssssi", $first_name, $middle_initial, $last_name, $suffix, $birthday, $phone_no, $region, $province, $city, $barangay, $street, $houseNumber, $user_id);

        if ($stmt->execute()) {
            // Redirect back to dashboard after successful update
            header("Location: customer_view_profile.php?update=success");
            exit();
        } else {
            $error_message = "Update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch customer info
$stmt = $conn->prepare("SELECT * FROM custumer_info WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer Information</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <style>
        .form-select, .form-control {
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 8px;
            font-size: 16px;
        }
        .form-select:focus, .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
    </style>
</head>

<body>
          <nav class="navbar navbar-expand-lg bg-primary navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="fas fa-mobile-alt"></i>
                <span>ArMaTech Pawnshop</span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="customer_dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
            </div>
        </div>
    </nav>


<div class="container mt-5">
    <form method="post" class="p-4 border rounded bg-light" onsubmit="return validateAge()">
        <h2 class="text-center mb-4"><b>Edit Customer Information</b></h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success">Profile updated successfully!</div>
        <?php endif; ?>

        <label><b>First Name:</b></label>
        <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>"><br>

        <label><b>Middle Initial:</b></label>
        <input type="text" name="middle_initial" class="form-control" value="<?= htmlspecialchars($profile['middle_initial'] ?? '') ?>"><br>

        <label><b>Last Name:</b></label>
        <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>"><br>

        <label><b>Suffix:</b></label>
        <input type="text" name="suffix" class="form-control" value="<?= htmlspecialchars($profile['suffix'] ?? '') ?>"><br>

        <label><b>Birthday:</b></label>
        <input type="date" id="birthday" name="birthday" class="form-control" required value="<?= htmlspecialchars($profile['birthday'] ?? '') ?>">
        <small id="ageError" class="text-danger"></small><br>

        <label><b>Phone Number:</b></label>
        <input type="text" name="phone_no" class="form-control" required value="<?= htmlspecialchars($profile['phone_no'] ?? '') ?>"><br>

        <h3 class="mb-3">Address</h3>

        <label><b>Region:</b></label>
        <select id="region" name="region" class="form-select mb-3"></select>

        <label><b>Province:</b></label>
        <select id="province" name="province" class="form-select mb-3"></select>

        <label><b>City/Municipality:</b></label>
        <select id="city" name="city" class="form-select mb-3"></select>

        <label><b>Barangay:</b></label>
        <select id="barangay" name="barangay" class="form-select mb-3"></select>

        <label><b>Street:</b></label>
        <input type="text" name="street" class="form-control" value="<?= htmlspecialchars($profile['street'] ?? '') ?>" required><br>

        <label><b>House Number:</b></label>
        <input type="text" name="houseNumber" class="form-control" value="<?= htmlspecialchars($profile['house_number'] ?? '') ?>" required><br>

        <button type="submit" class="btn btn-primary w-100 mt-3">Update Information</button>
    </form>
</div>

<script>
    async function fetchData(url) {
        const response = await fetch(url);
        return await response.json();
    }

    async function populateSelect(url, selectId, defaultText) {
        const data = await fetchData(url);
        const select = document.getElementById(selectId);
        select.innerHTML = `<option value="">${defaultText}</option>`;
        data.forEach(item => {
            const option = document.createElement("option");
            option.value = item.code;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        populateSelect("https://psgc.gitlab.io/api/regions/", "region", "Select Region");

        document.getElementById("region").addEventListener("change", function() {
            const regionCode = this.value;
            if (regionCode === "130000000") {
                document.getElementById("province").innerHTML = `<option value="NCR">Metro Manila</option>`;
                populateSelect("https://psgc.gitlab.io/api/regions/130000000/cities-municipalities/", "city", "Select City");
            } else {
                populateSelect(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`, "province", "Select Province");
            }
        });

        document.getElementById("province").addEventListener("change", function() {
            populateSelect(`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`, "city", "Select City");
        });

        document.getElementById("city").addEventListener("change", function() {
            populateSelect(`https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`, "barangay", "Select Barangay");
        });
    });

    function validateAge() {
        const birthdayInput = document.getElementById("birthday").value;
        const errorElement = document.getElementById("ageError");
        errorElement.textContent = "";

        if (!birthdayInput) return true;
        const birthDate = new Date(birthdayInput);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) age--;

        if (age < 18) {
            errorElement.textContent = "You must be at least 18 years old.";
            return false;
        }
        return true;
    }
</script>
</body>
</html>

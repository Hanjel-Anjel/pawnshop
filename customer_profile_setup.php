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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $birthday = $_POST['birthday'] ?? '';
    $phone_no = trim($_POST['phone_no'] ?? '');

    // Validate Birthday (Must be 18+ years old)
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
        $street = trim($_POST['street'] ?? '');
        $houseNumber = trim($_POST['houseNumber'] ?? '');

        // Fetch full address names
        $region = fetchNameFromAPI("https://psgc.gitlab.io/api/regions/$regionCode/");
        $province = ($regionCode === "130000000") ? "Metro Manila" : fetchNameFromAPI("https://psgc.gitlab.io/api/provinces/$provinceCode/");
        $city = fetchNameFromAPI("https://psgc.gitlab.io/api/cities-municipalities/$cityCode/");
        $barangay = fetchNameFromAPI("https://psgc.gitlab.io/api/barangays/$barangayCode/");

        // Retrieve Email
        $stmt = $conn->prepare("SELECT email FROM customer_users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($gmail);
        $stmt->fetch();
        $stmt->close();

        // Check if profile exists
        $stmt = $conn->prepare("SELECT customer_id FROM custumer_info WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE custumer_info SET first_name=?, middle_initial=?, last_name=?, suffix=?, phone_no=?, birthday=?, email=?, region=?, province=?, city=?, barangay=?, street=?, house_number=? WHERE user_id=?");
            $stmt->bind_param("sssssssssssssi", $first_name, $middle_initial, $last_name, $suffix, $phone_no, $birthday, $gmail, $region, $province, $city, $barangay, $street, $houseNumber, $user_id);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO custumer_info (user_id, first_name, middle_initial, last_name, suffix, phone_no, birthday, region, province, city, barangay, street, house_number, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssssssss", $user_id, $first_name, $middle_initial, $last_name, $suffix, $phone_no, $birthday, $region, $province, $city, $barangay, $street, $houseNumber, $gmail);
        }

        if ($stmt->execute()) {
            header("Location: customer_login_page.php");
            exit();
        } else {
            $error_message = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve profile details
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
    <title>Profile Setup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">

    <style>
        .form-select {
            background-color: #f8f9fa;
            /* Light gray background */
            border: 2px solid #007bff;
            /* Blue border */
            border-radius: 5px;
            /* Rounded corners */
            padding: 8px;
            font-size: 16px;
        }

        .form-select:focus {
            border-color: #0056b3;
            /* Darker blue border on focus */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            async function fetchData(url) {
                try {
                    const response = await fetch(url);
                    return await response.json();
                } catch (error) {
                    console.error("Error fetching data:", error);
                }
            }

            async function populateSelect(url, selectId, defaultText, key = "name") {
                const data = await fetchData(url);
                const select = document.getElementById(selectId);
                select.innerHTML = `<option value="">${defaultText}</option>`;
                if (data) {
                    data.forEach(item => {
                        const option = document.createElement("option");
                        option.value = item.code;
                        option.textContent = item[key];
                        select.appendChild(option);
                    });
                }
            }

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

        // bootstrap 
        src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
    </script>
</head>

<body>
    <div class="container mt-5">

        <form method="post" class="p-4 border rounded bg-light" onsubmit="return validateAge()">
            <b><h2 class="text-center mb-4">COMPLETE YOUR PROFILE</h2></b>

            <b><label for="first_name">First Name:</label></b>
            <input type="text" id="first_name" name="first_name" class="form-control" required>
            <br>
            <b><label for="middle_initial">Middle Initial:</label></b>
            <input type="text" id="middle_initial" name="middle_initial" class="form-control">
            <br>
            <b><label for="last_name">Last Name:</label></b>
            <input type="text" id="last_name" name="last_name" class="form-control" required>
            <br>
            <b><label for="suffix">Suffix (Optional):</label></b>
            <input type="text" id="suffix" name="suffix" class="form-control">
            <br>
            <b><label for="birthday">Birthday:</label></b>
            <input type="date" id="birthday" name="birthday" class="form-control" required value="<?= htmlspecialchars($birthday ?? '') ?>">
            <small id="ageError" class="text-danger"><?= $error_message ?? '' ?></small>

            <br>
            <br>
            <b><label for="phone_no">Phone Number:</label></b>
            <input type="text" id="phone_no" name="phone_no" class="form-control" required>

            <br>
            <h3 class="mb-4">ADDRESS</h3>

            <b><label for="region" class="form-label">Region:</label></b>
            <select id="region" name="region" class="form-select mb-3"></select>

            <b><label for="province" class="form-label">Province:</label></b>
            <select id="province" name="province" class="form-select mb-3"></select>

            <b><label for="city" class="form-label">City/Municipality:</label></b>
            <select id="city" name="city" class="form-select mb-3"></select>

            <b><label for="barangay" class="form-label">Barangay:</label></b>
            <select id="barangay" name="barangay" class="form-select mb-3"></select>


            <b><label for="street">Street Address:</label></b>
            <input type="text" id="street" name="street" class="form-control" required>

            <b><label for="houseNumber">House Number:</label></b>
            <input type="text" id="houseNumber" name="houseNumber" class="form-control" required>

            <button type="submit" name="save_profile" class="btn btn-primary w-100 mt-3">Save Profile</button>
        </form>
    </div>


</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelector("form").addEventListener("submit", function(event) {
            if (!validateAge()) {
                event.preventDefault(); // Stop form submission
            }
        });
    });

    function validateAge() {
        const birthdayInput = document.getElementById("birthday").value;
        const errorElement = document.getElementById("ageError");

        // Clear previous error message
        errorElement.textContent = "";

        if (!birthdayInput) return true;

        const birthDate = new Date(birthdayInput);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 18) {
            errorElement.textContent = "You must be at least 18 years old to register."; // Print error message
            return false; // Prevent form submission
        }

        return true; // Allow form submission if age is 18 or above
    }
</script>
<?php 
include('d:\xampp\htdocs\pawnshop\config.php'); 
ob_start();
include('header.php');
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .container {
            padding-top: 2%;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">EMPLOYEE LIST</h1>

        <div class="d-flex justify-content-end ">
            <button id="myBtn" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#myModal">Add New Employee</button>
        </div>

        <div class="table-responsive" style="text-align: center;">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Employee ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>MI</th>
                        <th>Position</th>
                        <th>Salary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM employee_details";
                    $result = $conn->query($sql);

                    if (!$result) {
                        die("Invalid query: " . $conn->error);
                    }
                    
                    $id = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                        <td>" . $id++ . "</td>
                        <td>" . $row['last_name'] . "</td>
                        <td>" . $row['first_name'] . "</td>
                        <td>" . $row['MI'] . "</td>
                        <td>" . $row['Position'] . "</td>
                        <td>₱" . number_format($row['Salary']) . "</td>
                        <td>
                            <button class='btn btn-success btn-sm update-btn' data-id='" . $row['id'] . "' data-bs-toggle='modal' data-bs-target='#updateEmployeeModal'>Update</button>
                            <a href='?delete_id=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this employee?\")' class='btn btn-danger btn-sm'>Delete</a>
                        </td>
                    </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add New Employee Modal -->
    <div id="myModal" class="modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="first_emp_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_emp_name" name="first_emp_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_emp_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_emp_name" name="last_emp_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="mi_emp_name" class="form-label">Middle Initial</label>
                            <input type="text" class="form-control" id="mi_emp_name" name="mi_emp_name">
                        </div>
                        <div class="mb-3">
                            <label for="emp_position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="emp_position" name="emp_position" required>
                        </div>
                        <div class="mb-3">
                            <label for="emp_salary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="emp_salary" name="emp_salary" required>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Employee Modal -->
    <div class="modal fade" id="updateEmployeeModal" tabindex="-1" aria-labelledby="updateEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateEmployeeModalLabel">Update Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateEmployeeForm" action="" method="POST">
                        <input type="hidden" name="id" id="employeeId">
                        <div class="mb-3">
                            <label for="update_first_emp_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="update_first_emp_name" name="first_emp_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="update_last_emp_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="update_last_emp_name" name="last_emp_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="update_mi_emp_name" class="form-label">Middle Initial</label>
                            <input type="text" class="form-control" id="update_mi_emp_name" name="mi_emp_name">
                        </div>
                        <div class="mb-3">
                            <label for="update_emp_position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="update_emp_position" name="emp_position" required>
                        </div>
                        <div class="mb-3">
                            <label for="update_emp_salary" class="form-label">Salary</label>
                            <input type="number" class="form-control" id="update_emp_salary" name="emp_salary" required>
                        </div>
                        <button type="submit" name="update_employee" class="btn btn-success">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['submit'])) {
        $lastname = $_POST['last_emp_name'];
        $firstname = $_POST['first_emp_name'];
        $MIname = $_POST['mi_emp_name'];
        $salary = $_POST['emp_salary'];
        $position = $_POST['emp_position'];

        $insert = "INSERT INTO employee_details (last_name, first_name, MI, Salary, Position) 
                    VALUES ('$lastname', '$firstname', '$MIname', '$salary', '$position')";

        $result = mysqli_query($conn, $insert);

        if ($result) {
            header('Location: employee_page.php');
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['update_employee'])) {
        $id = $_POST['id']; 
        $lastname = $_POST['last_emp_name'];
        $firstname = $_POST['first_emp_name'];
        $MIname = $_POST['mi_emp_name'];
        $salary = $_POST['emp_salary'];
        $position = $_POST['emp_position'];

        $update = "UPDATE employee_details SET last_name='$lastname', first_name='$firstname', MI='$MIname', Salary='$salary', Position='$position' WHERE id='$id'";
        
        if (mysqli_query($conn, $update)) {
            header('Location: employee_page.php');
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    if (isset($_GET['delete_id'])) {
        $employeeId = $_GET['delete_id'];
        $delete = "DELETE FROM employee_details WHERE id=$employeeId";
        mysqli_query($conn, $delete);
        header('Location: employee_page.php');
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.update-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const employeeId = this.getAttribute('data-id');

                    // Fetch employee data based on ID
                    fetch(`get_employee.php?id=${employeeId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Populate the update form fields
                            document.getElementById('employeeId').value = data.id; 
                            document.getElementById('update_first_emp_name').value = data.first_name;
                            document.getElementById('update_last_emp_name').value = data.last_name;
                            document.getElementById('update_mi_emp_name').value = data.MI;
                            document.getElementById('update_emp_position').value = data.Position;
                            document.getElementById('update_emp_salary').value = data.Salary;
                        });
                });
            });
        });
    </script>
</body>
</html>

<?php
include('footer.php');
ob_end_flush(); 
?>
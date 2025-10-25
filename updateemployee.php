<?php include('c:\xampp\htdocs\pawnshop\config.php'); ?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .container {
            padding-top: 2%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h1>Update Employee</h1>

                <?php
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];

                    $sql = "SELECT * FROM employee_details WHERE id = '$id'";

                    $result = mysqli_query($conn, $sql);

                    if (!$result) {
                        die("Error: " . mysqli_error($conn));
                    } else {
                        $row = mysqli_fetch_assoc($result);
                    }
                }
                ?>


                <?php

                if (isset($_POST['update_employee'])) {

                    if(isset($_GET['id_new'])){
                        $idnew = $_GET['id_new'];
                    }

                    $firstname = $_POST['first_emp_name'];
                    $lastname = $_POST['last_emp_name'];
                    $mi = $_POST['mi_emp_name'];
                    $salary = $_POST['emp_salary'];
                    $position = $_POST['emp_position'];

                    $sql = "UPDATE employee_details SET first_name = '$firstname', last_name = '$lastname', MI = '$mi',
                        Salary = '$salary', Position = '$position' WHERE id = '$idnew'";
                    
                    $result = mysqli_query($conn, $sql);

                    if (!$result) {
                        die("Error: " . mysqli_error($conn));
                    } else {
                        header('location:employee_page.php');
                    }
                }


                ?>


                <form action="updateemployee.php?id_new=<?php echo $id; ?>"method="post">
                    <div class="form-group">
                        <label for="first_emp_name">First Name:</label>
                        <input type="text" id="first_emp_name" class="form-control" name="first_emp_name" value="<?php echo $row['first_name'] ?>"><br><br>
                    </div>
                    <div class="form-group">
                        <label for="last_emp_name">Last Name:</label>
                        <input type="text" id="last_emp_name" class="form-control" name="last_emp_name" value="<?php echo $row['last_name'] ?>"><br><br>
                    </div>
                    <div class="form-group">
                        <label for="mi_emp_name">Middle initial:</label>
                        <input type="text" id="mi_emp_name" class="form-control" name="mi_emp_name" value="<?php echo $row['MI'] ?>"><br><br>
                    </div>
                    <div class="form-group">
                        <label for="emp_position">Position:</label>
                        <input type="text" id="emp_position" class="form-control" name="emp_position" value="<?php echo $row['Position'] ?>"><br><br>
                    </div>
                    <div class="form-group">
                        <label for="emp_salary">Salary:</label>
                        <input type="text" id="emp_salary" class="form-control" name="emp_salary" value="<?php echo $row['Salary'] ?>"><br><br>
                    </div>
                    <input type="submit" class="btn btn-success" name="update_employee" value="Update">
                    <a class="btn btn-danger" href="employee_page.php" role="button">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
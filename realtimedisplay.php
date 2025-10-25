<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Date and Time</title>
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: true 
            };
            document.getElementById('dateTime').textContent = now.toLocaleString('en-US', options);
        }

        setInterval(updateDateTime, 1000);
    </script>
</head>
<body>
    <h2>Current Date and Time:</h2>
    <p id="dateTime">
        <?php
        echo date("F j, Y, g:i:s A");
        ?>
    </p>

    <script>
        updateDateTime();
    </script>
</body>
</html>

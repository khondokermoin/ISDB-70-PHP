<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - Delete Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #bc761a;
            color: white;
            text-align: center;
            padding: 15px 0;
            font-size: 32px;
            font-style: italic;
            font-weight: bold;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
        }
        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 12px 20px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        .navbar a:hover {
            background-color: #555;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            min-height: 400px;
        }
        h2 {
            margin-top: 0;
            color: #111;
        }
        
        /* Form Specific Styles */
        .form-wrapper {
            background-color: #f2f2f2;
            padding: 30px;
            width: 50%;
            margin: 0 auto;
            border-radius: 5px;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .form-group label {
            width: 100px;
            font-weight: bold;
            color: #000;
        }
        .form-group input[type="text"] {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .btn-delete {
            background-color: #333;
            color: white;
            padding: 8px 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            margin-left: 100px; /* Aligns button with inputs */
            border-radius: 3px;
        }
        .btn-delete:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

    <div class="header">CRUD</div>
    <div class="navbar">
        <a href="index.php">HOME</a>
        <a href="add.php">ADD</a>
        <a href="update.php">UPDATE</a>
        <a href="delete.php">DELETE</a>
    </div>

    <div class="container">
        <h2>Delete Record</h2>
        
        <div class="form-wrapper">
            <form action="deletedata.php" method="POST">
                <div class="form-group">
                    <label for="id">Id</label>
                    <input type="text" id="id" name="id" required>
                </div>
                
                <button type="submit" class="btn-delete" name="delete">DELETE</button>
            </form>
        </div>
    </div>

</body>
</html>
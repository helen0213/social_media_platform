<html>
<title> Choose Login </title>
<head>
        <style>
            div.round {
                border-radius: 25px;
                margin-bottom: 40px;
            }

            button {
                margin-top: 100px;
                margin-bottom: 80px;
            }

            .container {
            height: 500px;
            position: relative;
            }

            .center {
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            }
        </style>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    </head>

    <body>
        <?php 
        // initializeDB(); 
        // erase previous user from userConfig
        file_put_contents("userConfig.php", "");
        file_put_contents("sponsorConfig.php", ""); 
        ?>

        <form method="POST" action="start-login.php">
        <div class="container">
            <div class="center">
                <div class="button">
            <button type="submit" class="btn btn-outline-info" margin-bottom="100px" name="UserLogin"> Login as User </button>
            <button type="submit" class="btn btn-outline-info" margin-bottom="100px" name= "SponsorLogin"> Login as Sponsor </button>
                </div>
            </div>
        </div>
        </form>

        <?php
        if (isset($_POST['SponsorLogin'])) {
            header("Location: sponsor-login.php");
        } else if ((isset($_POST['UserLogin']))) {
            header("Location: user-login.php");
        }
        ?>

    </body>
</html>
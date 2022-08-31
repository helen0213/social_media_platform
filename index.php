<html lang="en">
    <head>
        <style>
            div.round {
                border-radius: 25px;
                margin-bottom: 40px;
            }

            button {
                margin-top: 50px;
                margin-bottom: 40px;
            }

            .center {
                margin: auto;
                width: 60%;
                padding: 10px;
            }

            .container-md {
                padding: 10px;
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
        <nav class="navbar navbar-expand-xl navbar-light bg-light py-3">
            <a class="navbar-brand" href="#">Group 42</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php if($page=="Profile") {echo "active";}?>" href="user-profile.php">Profile </a>
                    </li>
                    <li class="nav-item <?php if($page=="Feeds") {echo "active";}?>">
                        <a class="nav-link" href="feeds.php">Feeds </a>
                    </li>
                    <li class="nav-item <?php if($page=="Groups") {echo "active";}?>">
                        <a class="nav-link" href="groups-content.php">Groups</a>
                    </li>
                    <li class="nav-item <?php if($page=="Chat") {echo "active";}?>">
                        <a class="nav-link" href="chat.php">Chat</a>
                    </li>
                    <li class="nav-item <?php if($page=="Explore") {echo "active";}?>">
                        <a class="nav-link" href="explore.php">Explore</a>
                    </li>
                </ul>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="start-login.php" >Log out</a>
                    </li>
                </ul>
            </div>
        </nav>
    </body>
</html>
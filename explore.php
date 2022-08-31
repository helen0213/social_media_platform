    <?php
        $title = 'Explore';
        $page = 'Explore';
        include_once('index.php');
        $tableName = $_POST['tables'];
    ?>
    <style>
        .center {
            margin: auto;
            width: 60%;
            padding: 0px;
        }
        .center-l {
            margin: auto;
            width: 80%;
            padding: 0px;
        }
        .middle {
            text-align: center;
        }

        .container-md {
            padding: 10px;
        }

        .check-box {
            margin-left: 10px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#load').click(function() {
                if(!$("input[type=checkbox]:checked").length) {
                    alert("You must select at least one attribute to run the query.");
                    return false;
                }
            });
        });
    </script>
    <div class="container-md center">
    <?php connectToDB(); ?>
    <form action="explore.php" method="post" class="container-md center">
        <button type="submit" name="displayUserTopics" id="displayUserTopics" class="btn btn-outline-info"> Show User & Topics Table </button>
        <button type="submit" name="displayUserChats" id="displayUserChats" class="btn btn-outline-info"> Show User & Chats Table </button>
        <button type="submit" name="displayUserPost" id="displayUserPost" class="btn btn-outline-info"> Show User & Posts Table </button>
    </form>

    <?php 
    if (isset($_POST['displayUserTopics'])) {
        if(connectToDB()){
            generateUserTopicsTable();
        }
        disconnectFromDB();
    } else if (isset($_POST['displayUserChats'])) {
        if(connectToDB()){
            generateUserChatsTable();
        }
        disconnectFromDB();
    } else if (isset($_POST['displayUserPost'])) {
        if(connectToDB()){
            generateUserPostTable();
        }
        disconnectFromDB();
    }
    ?>
    </div>
    <br>
    <br>
    <br>
    <div class="container-md center">
    <div class="round p-4 bg-light">
    <h4> Run a query </h4>
    <form id="tableSelection" action="explore.php" method="post">
        <label for="select">Please select a table that you want to query:</label>
        <select class="form-control" id="tables" name="tables" onchange="printAttributes(this);">
            <option value="Topics" <?php echo $_POST['tables'] === "Topics" ? "selected" : "" ?>>Topics</option>
            <option value="Badges" <?php echo $_POST['tables'] === "Badges" ? "selected" : "" ?>>Badges</option>
            <option value="Content" <?php echo $_POST['tables'] === "Content" ? "selected" : "" ?>>Content</option>
        </select>
        <br>
        <button type="submit" name="select" class="btn btn-outline-info"> Load Attributes</button>
    </form>
    
    <!-- </div> -->
    <?php

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr);
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

            return $statement;
        }


        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_ek0101", "a10679835", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
            
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function generateUserPostTable() {
            global $db_conn;
            $result = executePlainSQL(
                "SELECT username, COUNT(*) as num FROM Content
                GROUP BY username
                HAVING 0 < (SELECT COUNT(*)
                FROM Creates_Topics
                WHERE Content.username = Creates_Topics.username)
                ORDER BY num Desc"
            );
            printResult($result, "UserPosts");
        }

        function generateUserTopicsTable() {
            global $db_conn;
            $result = executePlainSQL(
                "SELECT username, COUNT(*) as num FROM Creates_Topics
                GROUP BY username
                ORDER BY num Desc"
            );
            printResult($result, "UserTopics");
        }

        function generateUserChatsTable() {
            global $db_conn;
            $result = executePlainSQL(
                "SELECT senderName as username, COUNT(*) as num FROM Messages1
                GROUP BY senderName
                HAVING COUNT(*)>4
                ORDER BY num Desc"
            );
            printResult($result, "UserChats");
        }

        function printAttributes($tableName) {
            global $db_conn;
            $result = executePlainSQL("SELECT gname FROM Groups");
            echo '<form action="explore.php" method="post">';
            if ($tableName == 'Topics') {
                echo '<input type="checkbox" name="checked[]" value="tname">';
                echo '<label class="check-box" for="tname"> Topic Name </label><br>';
                echo '<input type="checkbox" name="checked[]" value="numberOfPost">';
                echo '<label class="check-box" for="numberOfPost"> Number Of Post</label><br>';
                echo '<label >For Topics have greater than </label>';
                echo '<input type="number" class="check-box" name="constraint" required>';
                echo '<label class="check-box"> posts</label><br>';
            } else if ($tableName == 'Badges') {
                echo '<input type="checkbox" name="checked[]" value="bname">';
                echo '<label class="check-box" for="bname"> Badge Name </label><br>';
                echo '<input type="checkbox" name="checked[]" value="description">';
                echo '<label class="check-box" for="description"> Badge Description</label><br>';
            } else if ($tableName == 'Content') {
                echo '<input type="checkbox" name="checked[]" value="pid">';
                echo '<label class="check-box" for="pid"> Content ID </label><br>';
                echo '<input type="checkbox" name="checked[]" value="ptext">';
                echo '<label class="check-box" for="ptext"> Content Text </label><br>';
                echo '<input type="checkbox" name="checked[]" value="timeCreated">';
                echo '<label class="check-box" for="timeCreated"> Time Created </label><br>';
                echo '<input type="checkbox" name="checked[]" value="username">';
                echo '<label class="check-box" for="username"> Username </label><br>';
                echo '<input type="checkbox" name="checked[]" value="gname">';
                echo '<label class="check-box" for="gname"> Group Name </label><br>';
                echo '<label for="select">From:</label>';
                echo '<select class="form-control" id="groups" name="groups">';
                echo '<option value="All">All</option>';
                echo '<option value="Public">Public</option>';
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo '<option value= "'.$row['GNAME'].'">'.$row['GNAME'].'</option>';
                }
                echo '</select>';
            }
            echo '<br>';
            echo '<input type="hidden" name="tables" value='.$_POST['tables'].'>';
            echo '<button type="submit" id="load" name="load" class="btn btn-outline-info"> Load Data</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }

        function printResult($result, $table) { //prints results from a select statement
            global $db_conn;
            echo "<br>";

            if ($table == "UserPosts" || $table == "UserTopics" || $table == "UserChats") {
                echo '<div class="container-md center">';
                echo '<div class="round p-4 bg-info text-white">';
                if ($table == "UserPosts") {
                    echo "<h5>Number of posts by users who have previously created a topic: </h5>";
                } else if ($table == "UserTopics"){
                    echo "<h5>Number of topics created by users: </h5>";
                } else {
                    echo "<h5>Number of messages sent by active users (users need to send at least 5 messages to be considered as an active user): </h5>";
                }
                echo "<br>";
                echo "<table class='table text-white'>";
                echo "<thead>";
                echo "<tr><th>Username</th><th>Number</th></tr>";
            }  else if ($table == "Topics") {
                echo '<div class="container-md center-l">';
                echo "<h5>Retrieved data from Topics table: </h5>";
                echo "<br>";
                echo "<table class='table'>";
                echo "<thead>";
                echo "<tr><th>Topics Name</th><th>Number of Posts</th></tr>";
            } else if ($table == "Badges") {
                echo '<div class="container-md center-l">';
                echo "<h5>Retrieved data from Badges table: </h5>";
                echo "<br>";
                echo "<table class='table'>";
                echo "<thead>";
                echo "<tr><th>Badge Name</th><th>Badge Description</th></tr>";
            } else {
                echo '<div class="container-md center-l">';
                echo "<h5>Retrieved data from Content table: </h5>";
                echo "<br>";
                echo "<table class='table'>";
                echo "<thead>";
                echo "<tr><th>Content ID</th><th>Content Text</th><th>Time Created</th><th>Username</th><th>Group Name</th></tr>";
            }
            echo "</thead>";
            echo "<tbody>";
            if ($table == "UserPosts" || $table == "UserTopics" || $table == "UserChats") {
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>" . $row["USERNAME"] . "</td><td>" . $row["NUM"] . "</td></tr>"; //or just use "echo $row[0]"
                }
            } else if ($table == "Topics") {
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>" . $row["TNAME"] . "</td><td>" . $row["NUMBEROFPOST"] . "</td></tr>"; //or just use "echo $row[0]"
                }
            } else if ($table == "Badges") {
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>" . $row["BNAME"] . "</td><td>" . $row["DESCRIPTION"] . "</td></tr>"; //or just use "echo $row[0]"
                }
            } else {
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>" . $row["PID"] . "</td><td>" . $row["PTEXT"] . "</td><td>" . $row["TIMECREATED"] . "</td><td>" . $row["USERNAME"] . "</td><td>" . $row["GNAME"] . "</td></tr>"; //or just use "echo $row[0]"
                }
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        }

        function queryTable($attributes, $tableName, $constraint, $groups) {
            global $db_conn;
            $query = "SELECT";
            foreach ($attributes as $item) {
                if ($item == "pid") {
                    $query = $query." Content.pid, ";
                } else {
                    $query = $query." ".$item.", ";
                }
            }
            $query = rtrim($query, ", ");
            if($tableName == "Content") {
                $query = $query." FROM ".$tableName." JOIN Posts ON Content.pid = Posts.pid";
                if($groups == "Public") {
                    $query = $query." WHERE gname IS NULL";
                } else if($groups != "All") {
                    $query = $query." WHERE gname = '".$groups."'";
                }
            } else {
                $query = $query." FROM ".$tableName;
            }
            if ($tableName == "Topics") {
                $query = $query." WHERE numberOfPost > ".$constraint;
            }
            $result = executePlainSQL($query);
            printResult($result, $tableName);
        }

        if (isset($_POST['select'])) {
            if(connectToDB()){
                printAttributes($_POST['tables']);
            }
            disconnectFromDB();
        } else if (isset($_POST['load'])) {
            if(connectToDB()){
                if (!empty($_POST['checked'])) {
                    queryTable($_POST['checked'], $_POST['tables'], $_POST['constraint'], $_POST['groups']);
                }
            }
            disconnectFromDB();
        }

		?>
	</body>
</html>

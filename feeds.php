    <?php
        $title = 'Feeds';
        $page = 'Feeds';
        include_once('index.php');
    ?>

    <br/>
        
    <div class="text-center">
        <button type="button" class="btn btn-outline-info" margin-bottom="50px" onclick="window.location.href= 'Create_Content.php'"> Create New Content </button>
    </div>

    <?php //initializeDB() ?>

    <?php connectToDB() ?>

    <?php generateFeeds() ?>

    <?php generateAds() ?>

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

        function printFeeds($result) {
            echo '<div class="container-md center">';
            echo "<br/>";
            //echo '<div class="row">';
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<div class="round p-3 bg-info text-white">';
                if (strncmp($row["ID"], "N", 1) === 0) {
                    echo "<h5> News </h5>";
                } else {
                    echo "<h5> Post </h5>";
                }
                echo "<h4>" . $row["NAME"]."</h4>";
                echo "<p>" . $row["TIMECREATED"] . "</p>";
                echo "<p>" . $row["PTEXT"] . "</p>";
                if ($row["TNAME"]) {
                    echo "<p> #" . $row["TNAME"] . "</p>";
                }
                echo "</div>"; 
            }
            echo "</div>"; 
            echo "</div>"; 
        }

        function addBadge() {
            global $db_conn;
            $name = $_POST['addBadge'];
            $loggedInUser = require("userConfig.php");
            executePlainSQL("INSERT INTO Gets_Badges VALUES ( '${loggedInUser}','${name}')");
            echo "<meta http-equiv='refresh' content='0'>";
            OCICommit($db_conn);
        }

        function printAds($result) {
            //get badges
            global $db_conn;
            //get all owned badges
            $loggedInUser = require("userConfig.php");
            $badgeResult = executePlainSQL("SELECT bname FROM Gets_Badges WHERE username = '{$loggedInUser}'");
            $badges = array();
            while($row = OCI_Fetch_Array($badgeResult, OCI_BOTH))
            {
                $badges[] = $row["BNAME"];
            }
            echo '<div class="container-md center">';
            echo "<h4> Here're our sponsors and their ads: </h4>";
            echo "<br/>";
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                $days = $row["DISPLAYTIMEINDAYS"];
                $date = strtotime($row["TIMECREATED"]. ' + '.$days.' days');
                $name = $row["BNAME"];
                if($date >= time()) {
                    echo '<div class="round p-3 bg-info text-white">';
                    echo "<h4>" . $row["TITLE"] . "</h4>";
                    echo "<p>" . $row["TIMECREATED"] . "</p>";
                    echo "<p>" . $row["DESCRIPTION"] . "</p>";
                    echo "<p> ( Sponsor: " . $row["SNAME"].")</p>";
                    echo "<p>" . $row["BNAME"] . "</p>";
                    if (in_array($name, $badges)) {
                        echo "<button type = \"button\" class = \"btn btn-primary disabled\">Badge Owned</button>";
                    } else if ($name) {
                        echo "<form action=\"feeds.php\" method=\"post\">";
                        echo "<input type=\"text\" hidden id=\"addBadge\" name=\"addBadge\" value=".$name.">";
                        echo "<button type = \"submit\" class = \"btn btn-primary \" name=\"submit\" value=\"submit\">Get Badge</button>";
                        echo "</form>";
                    }
                    echo "</div>"; 
                }
            }
            echo "</div>"; 
            echo "</div>"; 
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

        function initializeDB() {
            global $db_conn;
            connectToDB();
            // Drop old table
            
            executePlainSQL("DROP TABLE Messages1");
            executePlainSQL("DROP TABLE Messages0");
            executePlainSQL("DROP TABLE Has_Chats");
            executePlainSQL("DROP TABLE Chats");
            executePlainSQL("DROP TABLE Relates");
            executePlainSQL("DROP TABLE Creates_Topics");
            executePlainSQL("DROP TABLE Topics");
            executePlainSQL("DROP TABLE Offers_Badges");
            executePlainSQL("DROP TABLE Receives_Ads");
            executePlainSQL("DROP TABLE Creates_Ads1");
            executePlainSQL("DROP TABLE Creates_Ads0");
            executePlainSQL("DROP TABLE Sponsors");
            executePlainSQL("DROP TABLE Gets_Badges");
            executePlainSQL("DROP TABLE Badges");
            executePlainSQL("DROP TABLE Receives_News");
            executePlainSQL("DROP TABLE News");
            executePlainSQL("DROP TABLE Content");
            executePlainSQL("DROP TABLE Posts");
            executePlainSQL("DROP TABLE Joins_Groups");
            executePlainSQL("DROP TABLE Groups");
            executePlainSQL("DROP TABLE Users");


            $statement = file_get_contents('create-db.sql');
            // Create new table
            $queries = explode(";", $statement);
            foreach ($queries as $query) {
                executePlainSQL($query);
            }
            OCICommit($db_conn);
        }

        function generateFeeds() {
            global $db_conn;
            // alter timestamp format
            executePlainSQL("alter session set nls_timestamp_format = 'YYYY-MM-DD HH24:MI:SS'");

            $combinedResult = executePlainSQL(
                "SELECT News.pid AS id, reporter AS name, NULL AS gname, ptext, timeCreated, tname FROM News 
                JOIN Posts ON News.pid = Posts.pid 
                LEFT JOIN Relates ON News.pid = Relates.pid
                UNION 
                SELECT Content.pid AS id, username AS name, gname, ptext, timeCreated, tname FROM Content
                JOIN Posts ON Content.pid = Posts.pid
                LEFT JOIN Relates ON Content.pid = Relates.pid
                WHERE gname IS NULL
                Order by timeCreated Desc"
                );

            printFeeds($combinedResult);
        }

        function generateAds() {
            global $db_conn;
            $loggedInUser = require("userConfig.php");
            // alter timestamp format
            executePlainSQL("alter session set nls_timestamp_format = 'YYYY-MM-DD HH24:MI:SS'");

            $combinedResult = executePlainSQL(
                "SELECT Receives_Ads.sid AS sid, Receives_Ads.title AS title, Receives_Ads.username AS username,
                Creates_Ads1.cost AS cost, Creates_Ads1.description AS description, Creates_Ads1.timeCreated AS timeCreated,
                Creates_Ads0.displayTimeInDays AS displayTimeInDays, sname, bname FROM Receives_Ads
                JOIN Sponsors ON Receives_Ads.sid = Sponsors.sid
                JOIN Creates_Ads1 ON Receives_Ads.sid = Creates_Ads1.sid and Receives_Ads.title = Creates_Ads1.title
                JOIN Creates_Ads0 ON Creates_Ads1.cost = Creates_Ads0.cost
                LEFT JOIN Offers_Badges ON Offers_Badges.sid = Receives_Ads.sid and Offers_Badges.title = Receives_Ads.title
                WHERE Receives_Ads.username='{$loggedInUser}'
                Order by timeCreated Desc"
                );

            printAds($combinedResult);
        }

        if (isset($_POST['submit'])) {
            if(connectToDB()){
                addBadge();
            }
            disconnectFromDB();
        }
		?>
	</body>
</html>

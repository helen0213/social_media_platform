    <?php session_start();?>
    <?php
        $title = 'Chat';
        $page = 'Chat';
        include_once('index.php');
    ?>
    <style>
        .center {
            margin: auto;
            width: 20%;
            padding: 0px;
        }
        .middle {
            text-align: center;
        }

        .container-md {
            padding: 10px;
        }
    </style>
    <?php connectToDB(); ?>
    <?php generateChat(); ?>

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

        function generateChat() {
            global $db_conn;
            $username = require("userConfig.php");
            $combinedResult = executePlainSQL(
                "SELECT cname FROM Has_Chats WHERE username='{$username}'"
            );

            printChat($combinedResult);
        }

        function printChat($result) {
            echo '<div class="container-md center">';
            echo "<br/>";
            echo '<div class="middle">';
            echo '<button type="button" class="btn btn-outline-info" onclick="window.location.href = \'createChat.php\';"> Create New Chat </button>';
            echo '</div>';
            echo '<br/>';
            echo '<h4>Chat list:</h4>';
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<div class="middle">';
                echo "<br/>";
                echo "<form action=\"message.php\" method=\"post\">";
                echo "<input type=\"hidden\" id=\"selectChat\" name=\"selectChat\" value=".$row["CNAME"].">";
                echo "<button type = \"select\" class = \"btn btn-outline-info \" name=\"select\" value=\"select\">".$row["CNAME"]."</button>";
                echo "</form>";
                echo "</div>"; 
            }
            echo "</div>"; 
            echo "</div>"; 
        }
        
        if (isset($_POST['submit'])) {
            if(connectToDB()){
                header("Location: message.php");
            }
            disconnectFromDB();
        }

		?>
	</body>
</html>

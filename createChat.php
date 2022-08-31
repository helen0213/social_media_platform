<?php
        $title = 'CreateChat';
        $page = 'CreateChat';
        include_once('index.php');
    ?>

    <br/>
        
    <div class="container-md center">
        <br/>
        <div class="round p-3 bg-info text-white">
        <h4>Create New Chat</h4>
        <p>Please select a user that you don't have a chat with from dropdown <p>

        <form action="createChat.php" method="post">
                <label for="select">User list:</label>
                
                    <?php
                        echo "<select class=\"form-control\" id=\"users\" name=\"users\">";
                        connectToDB();
                        $username = require("userConfig.php");
                        $result = executePlainSQL(
                            "SELECT username AS name FROM Users where username != '{$username}'
                            MINUS SELECT receiverName as name FROM Messages0 where senderName = '{$username}'");
                        if  ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                            echo '<option value= "'.$row['NAME'].'">'.$row['NAME'].'</option>';
                            $empty = 0;
                        } else {
                            $empty = 1;
                        }
                        while ($row = OCI_Fetch_Array($result, OCI_BOTH)){
                            echo '<option value= "'.$row['NAME'].'">'.$row['NAME'].'</option>';
                        }
                        echo "</select>";
                        if($empty === 1) {echo "<p class=\"text-dark\"> * None of the users are available, please click cancel below<p>";}
                        echo "<br>";
                        echo "<label for=\"select\">Create a chat name:</label>";
                        echo "<input type=\"text\" class=\"form-control\" id=\"chatName\" name=\"chatName\" required placeholder=\"Enter chat name here...\">";
                        echo "<br>";
                        if($empty === 0) {echo "<button id=\"submit\" type=\"submit\" name=\"submit\" class=\"btn bg-info text-white\"> Create </button>";}
                        echo "<button type=\"button\" class=\"btn bg-info text-white\" onclick=\"window.location.href = 'chat.php';\"> Cancel </button>";
                    ?>
        </form>
        </div>
    </div>

    <?php connectToDB() ?>

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

        function createChat(){
            global $db_conn;
            $cname = $_POST['chatName'];
            $user1Name = require("userConfig.php");
            $user2Name = $_POST['users'];
            echo("<script>console.log('PHP: " . $cname . "');</script>");
            echo("<script>console.log('PHP: " . $user1Name . "');</script>");
            echo("<script>console.log('PHP: " . $user2Name . "');</script>");
            executePlainSQL("INSERT INTO Chats VALUES ('${cname}')");
            executePlainSQL("INSERT INTO Messages0 VALUES ('${cname}', '${user1Name}', '${user2Name}')");
            executePlainSQL("INSERT INTO Messages0 VALUES ('${cname}', '${user2Name}', '${user1Name}') ");
            executePlainSQL("INSERT INTO Has_Chats VALUES ('${user1Name}', '${cname}')");
            executePlainSQL("INSERT INTO Has_Chats VALUES ('${user2Name}', '${cname}') ");
            OCICommit($db_conn);
            echo "<script type=\"text/javascript\">
                    window.location = \"chat.php\";
                    </script>";    
        }

        if (isset($_POST['submit'])) {
            if(connectToDB()){
                createChat();
            }
            disconnectFromDB();
        }
		?>
	</body>
</html>

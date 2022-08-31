<html>
<style>
h1 {text-align: center;}
p {text-align: center;}
div {text-align: center;}
</style>

    <?php
        $title = 'Customize Groups';
        $page = 'Customize Groups';
        include_once('index.php');
    ?>

    <body>
        <?php  
        //initializeDB();
        ?>
        
        <div class="container-md center">
        <h2>Join Group</h2>
        <form method="POST" action="customize-groups.php"> <!--refresh page when submitted-->
            <label for="groups">Groups not joined yet:</label>
            <select name="notYetJoinedGroups" id="notYetJoinedGroups">
            <option value=""> --- Select ---</option>
            <?php
            connectToDB();
            makeJoinGroupsDropdown();
            ?>
            </select>
            <input type="hidden" id="joinGroupRequest" name="joinGroupRequest">
            <input type="submit" value="Join Group" name="joinGroup"></p>
        </form>
        </div>

        <div class="container-md center">
        <h2>Create New Group</h2>
        <form method="POST" action="customize-groups.php"> <!--refresh page when submitted-->
            New Group Name: <input type="text" name="groupName">
            <input type="hidden" id="createGroupRequest" name="createGroupRequest">
            <input type="submit" value="Create" name="createGroupSubmit"></p>
        </form>
        </div>

        <div class="container-md center">
        <h2>Delete Group</h2>
        <form method="POST" action="customize-groups.php"> <!--refresh page when submitted-->
            Group to Delete: <input type="text" name="groupToDelete">
            <input type="hidden" id="deleteGroupRequest" name="deleteGroupRequest">
            <input type="submit" value="Delete" name="deleteGroup"></p>
        </form>
        </div>

        <?php
        //$result2 = executePlainSQL("SELECT * FROM Groups");
        //printResult($result2, "GNAME", "TIMECREATED");
        //$result3 = executePlainSQL("SELECT * FROM Joins_Groups");
        //printResult($result3, "GNAME", "USERNAME");
        disconnectFromDB();
        ?>

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

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

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResult($result, $col1, $col2) { //prints results from a select statement
            echo "<br>Retrieved data from table Users:<br>";
            echo "<table>";
            echo "<tr><th>$col1</th><th>$col2</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[$col1] . "</td><td>" . $row[$col2] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
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

        function makeJoinGroupsDropdown() {
            global $db_conn;
            $loggedInUser = require("userConfig.php");
            $result = executePlainSQL("SELECT gname FROM Joins_Groups MINUS (SELECT gname FROM Joins_Groups where username = '{$loggedInUser}')");
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<option value= "'.$row['GNAME']. '">'.$row['GNAME'].'</option>';
            }
        }

        function makeDeleteGroupsDropdown() {
            global $db_conn;
            $loggedInUser = require("userConfig.php");
            $result = executePlainSQL("SELECT gname FROM Joins_Groups where username = '{$loggedInUser}'");
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<option value= "'.$row['GNAME']. '">'.$row['GNAME'].'</option>';
            }
        }

        function handleJoinGroupRequest() {
            global $db_conn;
            $loggedInUser = require("userConfig.php");
            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $loggedInUser,
                ":bind2" => $_POST['notYetJoinedGroups']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into Joins_Groups values (:bind1, :bind2)", $alltuples);
            echo "<meta http-equiv='refresh' content='0'>";
            OCICommit($db_conn);

            //handleDisplayRequest();
        }

        function handleDeleteGroupRequest() {
            global $db_conn;
            $g = $_POST['groupToDelete'];

            executePlainSQL("DELETE FROM Groups WHERE gname = '{$g}'");
            echo "<meta http-equiv='refresh' content='0'>";
            OCICommit($db_conn);

            //handleDisplayRequest();
        }

        function handleCreateGroupRequest() {
            global $db_conn;
            $loggedInUser = require("userConfig.php");

            //Getting the values from user and insert data into the table
            
            $groupName =  $_POST['groupName'];

            executePlainSQL("INSERT INTO Groups VALUES ('{$groupName}', CURRENT_TIMESTAMP)");

            $tuple = array (
                ":bind1" => $loggedInUser,
                ":bind2" => $groupName
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into Joins_Groups values (:bind1, :bind2)", $alltuples);
            echo "<meta http-equiv='refresh' content='0'>";
            OCICommit($db_conn);
        }

        function handleDisplayRequest() {
            global $db_conn;
            $loggedInUser = require("userLogin.php");

            $result2 = executePlainSQL("SELECT * FROM Groups");

            printResult($result2, "GNAME", "TIMECREATED");

            $result3 = executePlainSQL("SELECT * FROM Joins_Groups where username = '{$loggedInUser}'");

            printResult($result3, "USERNAME", "GNAME");

            $result4 = executePlainSQL("SELECT gname FROM Groups MINUS (SELECT gname FROM Joins_Groups where username = '{$loggedInUser}')");

            printResult($result4, "USERNAME", "GNAME");

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

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('createTupleRequest', $_POST)) {
                    handleCreateRequest();
                } else if (array_key_exists('joinGroupRequest', $_POST)) {
                    handleJoinGroupRequest();
                } else if (array_key_exists('createGroupRequest', $_POST)) {
                    handleCreateGroupRequest();
                } else if (array_key_exists('deleteGroupRequest', $_POST)) {
                    handleDeleteGroupRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('displayTuples', $_GET)) {
                    handleDisplayRequest();
                }

                disconnectFromDB();
            }
        }

		if ( isset($_POST['joinGroupRequest']) || isset($_POST['createGroupRequest']) || isset($_POST['deleteGroupRequest'])) {
            handlePOSTRequest();
        } else if (isset($_GET['displayTupleRequest'])) {
            handleGETRequest();
        }
		?>
</html>

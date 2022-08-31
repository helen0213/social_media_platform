<html>
    <head>
        <title>CPSC 304 PHP/Oracle Demonstration</title>
    </head>

    <body>
    <?php $groupChoice =''; ?>
    <h2>Reset</h2>
        <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

        <form method="POST" action="testing.php">
            <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

        <hr />
         

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP
    
        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())
        connectToDB();
    
        
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

        function printResult($result_c,$result_t,$result_ct,$result_p,$result_r,$result_ca,$result_ra,
        $result_ba,$result_ob, $result_gb) { //prints results from a select statement
            echo "<br>Retrieved data from table Content:<br>";
            echo "<table>";
            echo "<tr><th>pid</th><th>username</th><th>gname</th></tr>";

            while ($row = OCI_Fetch_Array($result_c, OCI_BOTH)) {
                echo "<tr><td>" . $row['PID'] . "</td><td>" . $row['USERNAME'] . "</td><td>" . $row['GNAME'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "<br>Retrieved data from table Topics:<br>";
            echo "<table>";
            echo "<tr><th>tname</th><th>numberofpost</th></tr>";

            while ($row = OCI_Fetch_Array($result_t, OCI_BOTH)) {
                echo "<tr><td>" . $row['TNAME'] . "</td><td>" . $row['NUMBEROFPOST'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "<br>Retrieved data from table Creates_Topics:<br>";
            echo "<table>";
            echo "<tr><th>tname</th><th>username</th></tr>";

            while ($row = OCI_Fetch_Array($result_ct, OCI_BOTH)) {
                echo "<tr><td>" . $row['TNAME'] . "</td><td>" . $row['USERNAME'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "<br>Retrieved data from table Posts:<br>";
            echo "<table>";
            echo "<tr><th>pid</th><th>ptext</th><th>time</th></tr>";

            while ($row = OCI_Fetch_Array($result_p, OCI_BOTH)) {
                echo "<tr><td>" . $row['PID'] . "</td><td>" . $row['PTEXT'] . "</td><td>" . $row['TIMECREATED'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "</table>";

            echo "<br>Retrieved data from table Relates:<br>";
            echo "<table>";
            echo "<tr><th>pid</th><th>tname</th></tr>";

            while ($row = OCI_Fetch_Array($result_r, OCI_BOTH)) {
                echo "<tr><td>" . $row['PID'] . "</td><td>" . $row['TNAME'] . "</td><td>" . $row['TIMECREATED'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "</table>";

            echo "<br>Retrieved data from table Creates_Ads1:<br>";
            echo "<table>";
            echo "<tr><th>sid</th><th>title</th><th>cost</th><th>description</th><th>time</th></tr>";

            while ($row = OCI_Fetch_Array($result_ca, OCI_BOTH)) {
                echo "<tr><td>" . $row['SID'] . "</td><td>" . $row['TITLE'] . "</td><td>" . $row['COST'] . "</td><td>" . $row['DESCRIPTION'] . "</td><td>" . $row['TIMECREATED'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "</table>";

            echo "<br>Retrieved data from table Receives_Ads:<br>";
            echo "<table>";
            echo "<tr><th>sid</th><th>title</th><th>user</th></tr>";

            while ($row = OCI_Fetch_Array($result_ra, OCI_BOTH)) {
                echo "<tr><td>" . $row['SID'] . "</td><td>" . $row['TITLE'] . "</td><td>" . $row['USERNAME'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
            echo "</table>";

            echo "<br>Retrieved data from table Badges:<br>";
            echo "<table>";
            echo "<tr><th>bname</th><th>description</th></tr>";

            while ($row = OCI_Fetch_Array($result_ba, OCI_BOTH)) {
                echo "<tr><td>" . $row['BNAME'] . "</td><td>" . $row['DESCRIPTION'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            echo "</table>";

            echo "<br>Retrieved data from table Offers_Badges:<br>";
            echo "<table>";
            echo "<tr><th>sid</th><th>title</th><th>bname</th></tr>";

            while ($row = OCI_Fetch_Array($result_ob, OCI_BOTH)) {
                echo "<tr><td>" . $row['SID'] . "</td><td>" . $row['TITLE'] . "</td><td>" . $row['BNAME'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
            echo "</table>";

            echo "<br>Retrieved data from table Gets_Badges:<br>";
            echo "<table>";
            echo "<tr><th>username</th><th>bname</th></tr>";

            while ($row = OCI_Fetch_Array($result_gb, OCI_BOTH)) {
                echo "<tr><td>" . $row['USERNAME'] . "</td><td>" . $row['BNAME'] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";

            
        }

        function handlePrintRequest() {
            global $db_conn;

            $result_c = executePlainSQL("SELECT * FROM Content");
            $result_t = executePlainSQL("SELECT * FROM Topics");
            $result_ct = executePlainSQL("SELECT * FROM Creates_Topics");
            $result_p = executePlainSQL("SELECT * FROM Posts");
            $result_r = executePlainSQL("SELECT * FROM Relates");
            $result_ca = executePlainSQL("SELECT * FROM Creates_Ads1");
            $result_ra = executePlainSQL("SELECT * FROM Receives_Ads");
            $result_ba = executePlainSQL("SELECT * FROM Badges");
            $result_ob = executePlainSQL("SELECT * FROM Offers_Badges");
            $result_gb = executePlainSQL("SELECT * FROM Gets_Badges");

            printResult($result_c,$result_t,$result_ct,$result_p,$result_r,$result_ca,$result_ra,$result_ba,
            $result_ob,$result_gb);
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

        function handleUpdateRequest() {
            global $db_conn;

            $old_name = $_POST['oldName'];
            $new_name = $_POST['newName'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
            OCICommit($db_conn);
        }

        function handleResetRequest() {
            global $db_conn;
            // Drop old table
            executePlainSQL("DROP TABLE Receives_Ads");
            executePlainSQL("DROP TABLE Offers_Badges");
            executePlainSQL("DROP TABLE Gets_Badges");
            executePlainSQL("DROP TABLE Creates_Ads1");
            executePlainSQL("DROP TABLE Creates_Ads0");
            executePlainSQL("DROP TABLE Badges");
            executePlainSQL("DROP TABLE Sponsors");
            executePlainSQL("DROP TABLE Content");
            executePlainSQL("DROP TABLE Creates_Topics");
            executePlainSQL("DROP TABLE Relates");
            executePlainSQL("DROP TABLE Topics");
            executePlainSQL("DROP TABLE Receives_News");
            executePlainSQL("DROP TABLE News");
            executePlainSQL("DROP TABLE Posts");
            executePlainSQL("DROP TABLE Messages1");
            executePlainSQL("DROP TABLE Messages0");
            executePlainSQL("DROP TABLE Has_Chats");
            executePlainSQL("DROP TABLE Chats");
            executePlainSQL("DROP TABLE Joins_Groups");
            executePlainSQL("DROP TABLE Users");
            executePlainSQL("DROP TABLE Groups");
            
            $statement = file_get_contents('create-db.sql');
            $queries = explode(";", $statement);
            foreach ($queries as $query) {
                executePlainSQL($query);
            }
 

            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            $uniqueid = mt_rand(100000000,999999999);
            //Getting the values from user and insert data into the table
            $id = 'C'.$uniqueid;
            $content = $_POST['ptext'];
            $topic = $_POST['tname'];
            $group = $_POST['gname'];
            $username = 'user1';

            executePlainSQL("INSERT INTO Posts VALUES ('${id}', '${content}', CURRENT_TIMESTAMP)");
            insertTopic($id);
            handleGroupExist($id, $username, $group);
            
            OCICommit($db_conn);
    }

    function insertTopic($id){
        $c = 1;
        $topic = $_POST['tname'];

        $tuple1 = array (
            ":bind1" => $_POST['tname'],
            ":bind2" => $c
        );

        $alltuples1 = array (
            $tuple1
        );

        $exist = executePlainSQL("SELECT Count(*) FROM Topics WHERE tname = '${topic}'");

        $restr = 1;
        $row = oci_fetch_row($exist);
        
        if (empty($topic)) {

        } else {
            if (($row!= false) && ($row[0] >= 1)) {
                executePlainSQL("UPDATE Topics SET numberOfPost = numberOfPost+1 
               WHERE tname = '${topic}'");
            } else {
               executeBoundSQL("insert into Topics values (:bind1, :bind2)", $alltuples1);
               handleCreateTopic();
               executePlainSQL("INSERT INTO Relates VALUES ('${id}', '${topic}')");
            };
        };

        
    }

    function handleCreateTopic() {
            $user = 'user1';

            $tuple_ct = array (
                ":bind1" => $_POST['tname'],
                ":bind2" => $user
                //$_POST['username']
            );
    
            $alltuples_ct = array (
                $tuple_ct
            );
    
             executeBoundSQL("insert into Creates_Topics values (:bind1, :bind2)", $alltuples_ct);

    }

    function handleGroupExist($id, $username, $group){

        $exist = executePlainSQL("SELECT Count(*) FROM Groups WHERE gname = '${group}'");
        $row = oci_fetch_row($exist);
        
        if (empty($group)) {
            executePlainSQL("INSERT INTO Content VALUES ('${id}', '${username}','')");
        }  else {
        if (($row != false) && ($row[0] < 1)) {
                echo "group does not joined";
                executePlainSQL("INSERT INTO Content VALUES ('${id}', '${username}','')");
            } else {
                executePlainSQL("INSERT INTO Content VALUES ('${id}', '${username}','${group}')");
             };
            }
    }

    function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM Posts");
            $result_t = executePlainSQL("SELECT Count(*) FROM Topics");
            $result_r = executePlainSQL("SELECT Count(*) FROM Relates");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in Posts: " . $row[0] . "<br>";
            }

            if (($row = oci_fetch_row($result_t)) != false) {
                echo "<br> The number of tuples in Topics: " . $row[0] . "<br>";
            }

            if (($row = oci_fetch_row($result_r)) != false) {
                echo "<br> The number of tuples in Relates: " . $row[0] . "<br>";
            }

    }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    if(!isset($_POST['ptext']) || trim($_POST['ptext']) == '') {
                        echo "You did not fill out the content.";
                    } else {
                        handleInsertRequest();
                    }
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countTuples', $_GET)) {
                    handleCountRequest();
                }

                if (array_key_exists('printResults', $_GET)) {
                    handlePrintRequest();
                }
      

                disconnectFromDB();
            }
        }

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTupleRequest'])|| isset($_GET['printResultRequest'])) {
            handleGETRequest();
        }

    
		?>
	</body>
</html>


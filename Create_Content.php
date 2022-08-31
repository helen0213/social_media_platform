<?php
        $title = 'Create Contents';
        $page = 'Create Contents';
        include_once('index.php');
    ?>

<html lang="en">
    <head>
    </head>

    <body>
    <?php $groupChoice =''; ?>
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


        function handleInsertRequest() {
            global $db_conn;

            $uniqueid = mt_rand(100000000,999999999);
            //Getting the values from user and insert data into the table
            $id = 'C'.$uniqueid;
            $content = $_POST['ptext'];
            $topic = $_POST['tname'];
            $group = $_POST['gname'];
            $username = require("userConfig.php");

            executePlainSQL("INSERT INTO Posts VALUES ('${id}', '${content}', CURRENT_TIMESTAMP)");
            handleGroupExist($id, $username, $group);
            insertTopic($id);
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
        } else {if (($row!= false) && ($row[0] >= 1)) {
            echo "here";
             executePlainSQL("UPDATE Topics SET numberOfPost = numberOfPost+ 1 
            WHERE tname = '${topic}'");
         } else {
            executeBoundSQL("insert into Topics values (:bind1, :bind2)", $alltuples1);
            handleCreateTopic();
         };
         executePlainSQL("INSERT INTO Relates VALUES ('${id}', '${topic}')");
        };

        
    }

    function handleCreateTopic() {
            $user = require("userConfig.php");

            $tuple_ct = array (
                ":bind1" => $_POST['tname'],
                ":bind2" => $user
            );
    
            $alltuples_ct = array (
                $tuple_ct
            );
    
             executeBoundSQL("insert into Creates_Topics values (:bind1, :bind2)", $alltuples_ct);
             echo "You have created topic ". $_POST['tname'] . " ";

    }

    function handleGroupExist($id, $username, $group){

        $exist = executePlainSQL("SELECT Count(*) FROM Groups WHERE gname = '${group}'");
        $row = oci_fetch_row($exist);
        
        if (empty($group)) {
            executePlainSQL("INSERT INTO Content VALUES ('${id}', '${username}','')");
            echo "posted in public";
        }  else {
        if (($row != false) && ($row[0] < 1)) {
                echo "group is not joined, posted in public";
                executePlainSQL("INSERT INTO Content VALUES ('${id}', '${username}','')");
            } else {
                executePlainSQL("INSERT INTO Content VALUES ('${id}', '${username}','${group}')");
                echo "Post in ". $group . "!";
             };
            }
    }


        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('insertQueryRequest', $_POST)) {
                    if(!isset($_POST['ptext']) || trim($_POST['ptext']) == '') {
                        echo "You did not fill out the content.";
                    } else {
                        handleInsertRequest();
                        
                }

                disconnectFromDB();
                //change here
                header("Location: feeds.php");
            }
        }
    }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                disconnectFromDB();
            }
        }

		if ( isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        } else {
            handleGETRequest();
        }

    
		?>

    <style> 
    form { 
        margin: 0 auto; 
        width:400px;
        font-family: verdana;
        font-size: 100%;
    }
    h2   {
        color: white;
        font-family: verdana;
        font-size: 300%;
        text-align: center;
        margin-top: 25px;
    }
    body {
        background-image: linear-gradient(270deg, powderblue, darkcyan,lightblue);
    }

    div.topic {
        

    }
    h3 {
        font-family: verdana;
        text-align: center;
    }



    </style>

    <h2>Create Content</h2>
    <form method="POST" action="Create_Content.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            <input type="hidden" name="username" 
            value=<?php $loggedInUser = require("userConfig.php"); echo "{$loggedInUser}" ?>>
            Content:<br /><br />
             <input type="text" name="ptext" size="50" required> <br /><br />
            Topic: <br /><br />
            <input type="text" name="tname"> <br /><br />
            Group: <br /><br />
                    <input type="text" id="optional" name="gname"> 
                    <br /><br />
            
            <input type="submit" value="post" name="insertSubmit"></p>
    </form>

    <div class = "center">
    <h3>Trending Topics</h3>

    <?php
    connectToDB();
    $number = 1;
    $hot = executePlainSQL("SELECT tname FROM Topics ORDER BY numberOfPost DESC FETCH FIRST 3 ROWS ONLY");
    while ($row = OCI_Fetch_Array($hot, OCI_BOTH)) {
        echo    "<h3>" . $number . "." . "<tr><td>" . $row['TNAME'] . "</td></tr>" . "</h3>"  ;
        $number = $number + 1;
    }
    disconnectFromDB();
    ?>

    </div>

    
	</body>

</html>


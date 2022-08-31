<html>
<title> Sponsor Sign Up </title>
<style>
h1 {text-align: center;}
p {text-align: center;}
div {text-align: center;}
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

    <?php 
    connectToDB();
    ?>

    <form method="POST" action="sponsor-signup.php">
    <div class="container-md center">
        <h2>Sign Up</h2>
            New Sponsor Name: <input type="text" name="currentSponsorName"> <br /><br />
            New Password: <input type="text" name="password"> <br /><br />
            <input type="hidden" id="sponsorSignUpRequest" name="sponsorSignUpRequest">
            <input type="submit" value="Sign Up" name="sponsorSignUpSubmit"></p> <br /><br />
    </div>
    </form>

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
        disconnectFromDB();
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

    function handlesponsorSignUpRequest() {
        global $db_conn;
        $enteredSName = $_POST['currentSponsorName'];
        $enteredPassword = $_POST['password'];

        $listOfSID = NULL;
        $id;

        while (!isset($listOfSID)) {
            $uniqueid = mt_rand(1,999999999);
            $id = 's' . $uniqueid;
            $listOfSID = executePlainSQL("SELECT sid FROM Sponsors WHERE sid = '{$uniqueid}'");
        }
        
        $tuple = array (
            ":bind1" => $id,
            ":bind2" => $enteredSName,
            ":bind3" => $enteredPassword
        );

        $alltuples = array (
            $tuple
        );

        executeBoundSQL("insert into Sponsors values (:bind1, :bind2, :bind3)", $alltuples);
        OCICommit($db_conn);
        header("Location: sponsor-login.php");
        
    }

    function handleDisplayRequest() {
        global $db_conn;

        $result2 = executePlainSQL("SELECT * FROM Sponsors");

        printResult($result2, "SID", "PASSWORD");

    }

    function handlePOSTRequest() {
        if (connectToDB()) {
            if (array_key_exists('sponsorSignUpRequest', $_POST)) {
                handlesponsorSignUpRequest();
            }
            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handleGETRequest() {
        if (connectToDB()) {
                disconnectFromDB();
            }
            disconnectFromDB();
    }

    if (isset($_POST['sponsorSignUpSubmit'])) {
        handlePOSTRequest();
    } else {
        handleGETRequest();
    }
    ?>


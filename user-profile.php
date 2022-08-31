<style>
.res-circle {
    width: 20%;
    border-radius: 50%;
    background: #0d5863;
    line-height: 0;
    position: relative;
    height: 60px;
    width: 60px;
    display: table-cell;
    text-align: center;
    vertical-align: middle;
}
</style>

<?php
$title = 'User Profile';
$page = 'User Profile';
include_once('index.php');
connectToDB();
?>

<html lang="en">

<div class="container mt-4 mb-4 p-3 d-flex justify-content-center"> 
    <div class="card p-4"> <div class=" image d-flex flex-column justify-content-center align-items-center"> <button class="btn btn-secondary"> 
        </button> 
        <span class="name mt-3">
        <?php
            $u = require("userConfig.php");
            echo "<tr><td>" . $u . "</td></tr>"; 
            ?>   
        </span> 
        <span class="idd"> 
        <?php echo "@" . require("userConfig.php"); ?>
        </span> 
        <div class="d-flex flex-row justify-content-center align-items-center gap-2"> 
             <span><i class="fa fa-copy"></i></span> </div> 
             <div class="d-flex flex-row justify-content-center align-items-center mt-3"> 
                </div>
                      <div class=" px-2 rounded mt-4 date "> <span class="join">User</span> </div> </div> </div>
</div>

<?php 
displayUserBadges();
?>

<form method="POST" action="user-profile.php">
<div class="text-center">
<input type="hidden" id="showUsersRequest" name="showUsersRequest">
<button type="submit" class="btn btn-outline-info" margin-bottom="50px" name="ShowUsersSubmit"> Show Users With All Badges </button>
</div>
</form>

<form method="POST" action="user-profile.php">
<h2>Delete User</h2>
Password: <input type="text" name="password"> <br /><br />
<input type="hidden" id="deleteUserRequest" name="deleteUserRequest">
<input type="submit" value="Delete User" name="DeleteUserSubmit"></p> <br /><br />
</form>

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

function displayUserBadges() {
    global $db_conn;
    echo '<div class="container-md center">';
    $u = require("userConfig.php");
    echo "<h4> " . $u . "'s Badges: </h4>";
    echo "<br/>";
    $badges = executePlainSQL("SELECT Gets_Badges.bname AS badgeName, username, description
    FROM Gets_Badges, Badges
    WHERE Gets_Badges.bname = Badges.bname AND Gets_Badges.username = '{$u}'");
    echo '<div class="round p-3 bg-info text-white">';

    while ($row = OCI_Fetch_Array($badges, OCI_BOTH)) {
        echo "<div class=res-circle>";
        echo "<div-class=circle-txt>" . $row["BADGENAME"].trim() . "</div>";
        echo "<p>" . $row["DESCRIPTION"] . "</p>";
    }
    echo "</div>";
    echo "</div>";
    disconnectFromDB();
}

function displayUsersWithAllBadges() {
    global $db_conn;
    echo "<br>";
    echo '<div class="container-md center">';
    echo '<div class="round p-4 bg-info text-white">';
    echo "<h4>Leadership Board: Users who have received all Badges </h4>";
    echo "<br>";
    echo "<table class='table text-white'>";
    echo "<thead>";
    echo "<tr><th>Username</th></tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $result = executePlainSQL("SELECT username FROM Users WHERE NOT EXISTS ((SELECT Badges.bname FROM Badges) MINUS (SELECT Gets_Badges.bname FROM Gets_Badges WHERE Gets_Badges.username = Users.username))");
    
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        echo "<tr><td>" . $row["USERNAME"] . "</td></tr>"; //or just use "echo $row[0]"
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
}

function handleDeleteUserRequest() {
    global $db_conn;
    $u = require("userConfig.php");
    $enteredPassword = $_POST['password'];

    $result = executePlainSQL("SELECT password from Users where username = '{$u}' AND password = '{$enteredPassword}'");

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        if (!isset($row[0])) {
            echo "Username or password is incorrect.";
        } else {
            executePlainSQL("DELETE FROM Users where username = '{$u}'");
            OCICommit($db_conn);
            file_put_contents("userConfig.php", "");
            header("Location: start-login.php");
        }
    }
    
}



// HANDLE ALL POST ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
function handlePOSTRequest() {
    if (connectToDB()) {
        if (array_key_exists('deleteUserRequest', $_POST)) {
            handleDeleteUserRequest();    
        } else if (array_key_exists('showUsersRequest', $_POST)) {
            displayUsersWithAllBadges();
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
}

if ( isset($_POST['DeleteUserSubmit']) ||  isset($_POST['ShowUsersSubmit'])) {
    handlePOSTRequest();
} else {
    handleGETRequest();
}

?>

<style>
form { 
        margin: 0 auto; 
        width:200px;
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

    div.display-none {
      display:none;
    }

    h3 {
        font-family: verdana;
        text-align: center;
    }

    * {
    margin: 0;
    padding: 0
}

body {
    background-color: #000
}

.card {
    width: 350px;
    background-color: #efefef;
    border: none;
    cursor: pointer;
    transition: all 0.5s;
}

.image img {
    transition: all 0.5s
}

.card:hover .image img {
    transform: scale(1.5)
}

.btn {
    height: 140px;
    width: 140px;
    border-radius: 50%
}

.name {
    font-size: 22px;
    font-weight: bold
}

.idd {
    font-size: 14px;
    font-weight: 600
}

.idd1 {
    font-size: 12px
}

.number {
    font-size: 22px;
    font-weight: bold
}

.follow {
    font-size: 12px;
    font-weight: 500;
    color: #444444
}

.btn1 {
    height: 40px;
    width: 150px;
    border: none;
    background-color: #000;
    color: #aeaeae;
    font-size: 15px
}

.text span {
    font-size: 13px;
    color: #545454;
    font-weight: 500
}

.icons i {
    font-size: 19px
}

hr .new1 {
    border: 1px solid
}

.join {
    font-size: 14px;
    color: #a0a0a0;
    font-weight: bold
}

.date {
    background-color: #ccc
}
</style>


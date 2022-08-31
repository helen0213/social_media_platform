<?php
        $title = 'Sponsor Profile';
        $page = 'Sponsor Profile';
    ?>

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

        

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('insertQueryRequest', $_POST)) {
                    if(!isset($_POST['title']) || !isset($_POST['ades'])) {
                        echo "You did not fill out the information.";
                    } else {
                        handleInsertRequest();
                        
                }

                disconnectFromDB();
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

<nav class="navbar navbar-expand-xl navbar-light bg-light py-3">
            <a class="navbar-brand" href="#">Group 42</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php if($page=="Profile") {echo "active";}?>" href="s-profile.php">Profile </a>
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

<div class="container mt-4 mb-4 p-3 d-flex justify-content-center"> 
    <div class="card p-4"> <div class=" image d-flex flex-column justify-content-center align-items-center"> <button class="btn btn-secondary"> 
        </button> 
        <span class="name mt-3">
        <?php 
            connectToDB();
            $u = require("sponsorConfig.php");
            $id = executePlainSQL("SELECT sname from Sponsors where sid = '$u'");
            $row = OCI_Fetch_Array($id, OCI_BOTH);
            echo "<tr><td>" . $row['SNAME'] . "</td></tr>"; 
            disconnectFromDB();?>   
        </span> 
        <span class="idd"> 
        <?php echo "@" . require("sponsorConfig.php"); ?>
        </span> 
        <div class="d-flex flex-row justify-content-center align-items-center gap-2"> 
             <span><i class="fa fa-copy"></i></span> </div> 
             <div class="d-flex flex-row justify-content-center align-items-center mt-3"> 
                </div>
                 <div class=" d-flex mt-2"> 
                    <button class="btn1 btn-dark" onclick="window.location.href='edit_ads.php'">Edit Ads</button>
                 </div> 
                 <div class=" d-flex mt-2"> 
                    <button class="btn1 btn-dark" onclick="window.location.href='create_Ad.php'">Create Ads</button>
                 </div> 
                      <div class=" px-2 rounded mt-4 date "> <span class="join">Sponsor</span> </div> </div> </div>
</div>


<?php
 connectToDB();
 $uid = require("sponsorConfig.php");
 $ad = executePlainSQL("SELECT title, cost, description, timeCreated from Creates_Ads1 where sid = '${uid}' ");
 while ($row = OCI_Fetch_Array($ad, OCI_BOTH)) {
    echo '<div class="container mt-4 mb-4 p-3 d-flex justify-content-center"> ';
    echo '<div class="card p-4">';
    echo  "<tr><td>" . $row['TITLE'] . "</td></tr>" ;
    echo " <br /><br />";
    echo "<tr><td>" . $row['DESCRIPTION'] . "</td></tr>";
    echo " <br /><br />";
    echo "<tr><td>" . $row['TIMECREATED'] . "</td></tr>";
    echo '<div class=" px-2 rounded mt-4 date "> <span class="join">';
    echo "<tr><td>" . $row['COST'] . "</td></tr>";
    echo '</span> </div>';
    echo '</div>';
    echo '</div>';
}
 disconnectFromDB();

?>



    <table>
    </table>


    
	</body>

</html>
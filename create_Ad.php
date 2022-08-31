<?php
        $title = 'Create Ads';
        $page = 'Create Ads';
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
        $has_badge = '';
    
        
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

            $cost = $_POST['plan'];
            // $display = 0;
            // if ($cost  = 100) {$display = 5;}
            // else if ($cost  = 500) {$display = 30;}
            // else if ($cost  = 1000){$display = 65;}
            // else if ($cost  = 2000){$display = 140;}
            // else if ($cost  = 4000){$display = 300;}
            $sid=$_POST['sponsorid'];
            $title = $_POST['title'];
            $ades = $_POST['ades'];

            //insert create_ads1
            if (empty($_POST['title'])) {
                echo "please fill out the title";
            } else if (empty($_POST['ades'])){
                echo "please fill out the description of ads";
            } else {
                executePlainSQL("INSERT INTO Creates_Ads1 VALUES ('${sid}', '${title}', '${cost}','${ades}',CURRENT_TIMESTAMP)");
                 //insert receives_ads (send to all users)
                $users = executePlainSQL("SELECT * FROM Users");
                while ($row = OCI_Fetch_Array($users, OCI_BOTH)) {
                $u = $row['USERNAME'];
                executePlainSQL("INSERT INTO Receives_Ads VALUES ('${sid}', '${title}', '${u}')");
                }
                if(!empty($_POST['bname']) && !empty($_POST['bdes'])) 
                {createBadge();} else {
                    echo "cannot create an empty badge";
                }
                }
            OCICommit($db_conn);
    }

    function createBadge() {
        $bname = $_POST['bname'];
        $bdes = $_POST['bdes'];
        $tuple = array (
            ":bind1" => $_POST['bname'],
            ":bind2" => $_POST['bdes']
        );
        $alltuples = array (
            $tuple
        );
        //insert badges (if bname already exist give warning)
        executeBoundSQL("insert into Badges values (:bind1, :bind2)", $alltuples);
        //insert offer_badges
        $tuple1 = array (
            ":bind1" => $_POST['sponsorid'],
            ":bind2" => $_POST['title'],
            ":bind3" => $_POST['bname']
        );
        $alltuples1 = array (
            $tuple1
        );
        executeBoundSQL("insert into Offers_Badges values (:bind1, :bind2, :bind3)", $alltuples1);

        // //insert gets_badges
        // $users = executePlainSQL("SELECT * FROM Users");
        //     while ($row = OCI_Fetch_Array($users, OCI_BOTH)) {
        //         $u = $row['USERNAME'];
        //         $tuple2 = array (
        //             ":bind1" => $u,
        //             ":bind2" => $_POST['bname'],
        //         );
        //         $alltuples2 = array (
        //             $tuple2
        //         );
        //         executeBoundSQL("insert into Gets_Badges values (:bind1, :bind2)", $alltuples2);
        //     }
         
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
                header("Location: s-profile.php");
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
    <div class="card p-4"> 
    
    <h2>Create Ads</h2>
    <form method="POST" action="create_Ad.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            <input type="hidden" name="sponsorid" 
            value=<?php $loggedInUser = require("sponsorConfig.php"); echo "{$loggedInUser}" ?>>
            Title:<br /><br />
             <input type="text" name="title" required> <br /><br />
            Description: <br /><br />
            <input type="text" name="ades" required> <br /><br />
            Display Plan: <br /><br />
            <select name="plan" id="plan">  
            <optgroup label="economic">
                <option value="100">5 days for $100</option>  
                <option value="500">30 days for $500</option> 
            </optgroup>
            <optgroup label="deluxe">
                <option value="1000">65 days for $1000</option>  
                <option value="2000">140 days for $2000</option>  
            </optgroup>
            <optgroup label="supreme">
                <option value="4000">300 days for $4000</option>
            </optgroup>  
            </select>   <br /><br />
            Add a badge (optional): 
            Badge Name:<br /><br />
            <input type="text" name="bname"> <br /><br />
            Badge Description:<br /><br />
            <input type="text" name="bdes"> <br /><br />
            
            <input type="submit" value="create" name="insertSubmit"></p>
    </form>
</div></div>


    
	</body>

</html>
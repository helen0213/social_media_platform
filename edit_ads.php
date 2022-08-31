<?php
        $title = 'Edit Ads';
        $page = 'Edit Ads';
        include_once('index.php');
    ?>

<html lang="en">
    <head>
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


        function handleUpdateRequest() {
            global $db_conn;

            $title = $_POST['title'];
            $ades = $_POST['ades'];
            $cost = $_POST['plan'];

            if (isset($_POST['cost'] )) {
            executePlainSQL("UPDATE Creates_Ads1 
            SET cost =' $cost ' WHERE title='" . $title . "'");
            };


            if (isset($_POST['des'])) {
            executePlainSQL("UPDATE Creates_Ads1 
            SET description='" . $ades . "' WHERE title='" . $title . "'");
            };
            
            OCICommit($db_conn);
        
    }
    

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('insertQueryRequest', $_POST)) {
                    if(!isset($_POST['ades'])) {
                        echo "You did not fill out the description.";
                    } else {
                        handleUpdateRequest();
                        
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

<div class="container mt-4 mb-4 p-3 d-flex justify-content-center"> 
    <div class="card p-4"> 
    
    <h2>Edit Ads</h2>
    <form method="POST" action="edit_ads.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            Title:<br /><br />
            <select name="title" id="title">
            <?php
            connectToDB();
            $uid = require("sponsorConfig.php");
            $ad = executePlainSQL("SELECT title from Creates_Ads1 where sid = '${uid}' ");
            while ($row = OCI_Fetch_Array($ad, OCI_BOTH)) {
                echo '<option value="';
                echo $row['TITLE'];
                echo '">';
                echo $row['TITLE'];
                echo '</option>';
            }
            disconnectFromDB();
            ?>
            </select>
            <br /><br />
            Want to update: </br>
            <input type="checkbox" id="cost" name="cost" value="yes">
            <label for="cost"> display plan</label><br>
            <input type="checkbox" id="des" name="des" value="yes">
            <label for="des">Description</label><br>
            Change display plan: <br /><br />
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
            Description: <br /><br />
            <input type="text" name="ades"> <br /><br />
            <input type="submit" value="create" name="insertSubmit"></p>
    </form>
</div></div>

    
	</body>

</html>
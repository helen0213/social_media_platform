<?php
        $title = 'Message';
        $page = 'Message';
        include_once('index.php');
        $chatname = $_POST["selectChat"];
        ?>
    <?php connectToDB(); ?>
    <style>
        h1.middle {
            margin: auto;
            width: 30%;
            padding: 0px;
        }
        button.btn {
            margin-bottom: 30px;
        }

        .chat-window{
            height:calc(100vh - 80px);
            width:100vw;
        }

        .chat-section .command {
            width: 280px;
            height:100%;
            position: absolute;
            left: 0;
            top: 0;
            padding: 20px;
            z-index: 7
        }

        .chat-section .chat {
            margin-left: 280px;
            height:100%;
            border-left: 1px solid #eaeaea
        }

        .chat .chat-header {
            padding: 15px 20px;
            border-bottom: solid #f4f7f6
        }

        .chat .chat-history {
            padding: 20px;
            border-bottom: 2px solid #fff
        }

        .chat .chat-history ul {
            padding: 0
        }

        .chat .chat-history ul li {
            list-style: none;
            margin-bottom: 30px
        }


        .chat .chat-history .message-data {
            margin-bottom: 15px
        }

        .chat .chat-history .message-data-time {
            color: #434651;
            padding-left: 6px
        }

        .chat .chat-history .message {
            color: #444;
            padding: 18px 20px;
            line-height: 26px;
            font-size: 16px;
            border-radius: 7px;
            display: inline-block;
            position: relative
        }

        .chat .chat-history .my-message {
            background: #efefef
        }

        .chat .chat-history .other-message {
            background: #e8f1f3;
            text-align: right
        }

        .chat .chat-message {
            padding: 20px
        }

        @media only screen {
            .chat-section .chat-list {
                height: 80%;
                overflow-x: auto
            }
            .chat-section .chat-history {
                height: calc(100vh - 72px - 98px - 58px);
                overflow-x: auto
            }
        }
    </style>
    <div class="row">
        <div class="col-lg-12 chat">
            <div class="chat-window chat-section">
                <div id="plist" class="command">
                    <button type="button" class="btn btn-outline-info" onclick="window.location.href = 'chat.php';"> Back </button>
                    <form action="message.php" method="post">
                        <input type="hidden" id="selectChat" name="selectChat" value="<?php echo $chatname;?>">
                        <button id="load" type="load" name="load" class="btn btn-outline-info"> Load Messages</button>
                    </form>
                </div>
                
                <div class="chat">
                    <div class="chat-header clearfix">
                        <div class="row">
                            <div class="col-lg-6">
                                <h6 class="m-b-0"><?php echo $chatname; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history">
                        <ul class="m-b-0">
                            <?php generateMessages()?>                             
                        </ul>
                    </div>
                    <div class="chat-message">
                        <form action="message.php" method="post">
                            <div class="input-group mb-0">
                                <input type="text" class="form-control" id="message" name="message" placeholder="Enter text here...">
                                <input type="hidden" id="selectChat" name="selectChat" value="<?php echo $chatname;?>">
                                <button id="submit" type="submit" name="submit" class="btn btn-outline-info"> Send </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        function generateMessages() {
            global $db_conn, $chatname;
            // alter timestamp format
            executePlainSQL("alter session set nls_timestamp_format = 'YYYY-MM-DD HH24:MI:SS'");
            $combinedResult = executePlainSQL(
                "SELECT time, mtext, senderName FROM Messages1 WHERE cname= '${chatname}' ORDER BY time Asc"
            );

            printMessages($combinedResult);
        }

        function printMessages($result) {
            $username = require("userConfig.php");
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo '<li class="clearfix">';
                if(strncmp($row["SENDERNAME"], $username, 5) === 0) {
                    echo '<div class="message-data text-right">';
                } else {
                    echo '<div class="message-data">';
                }
                echo '<span class="message-data-time">'.$row['TIME'].'</span>';
                echo "</div>"; 
                if(strncmp($row["SENDERNAME"], $username, 5) === 0) {
                    echo '<div class="message other-message float-right">'.$row['MTEXT'].'</div>';
                } else {
                    echo '<div class="message my-message">'.$row['MTEXT'].'</div>';
                }
                echo "</li>";
            } 
        }

        function sendMessage(){
            global $db_conn, $chatname;
            $cname = $chatname;
            $mtext = $_POST['message'];
            $senderName = require("userConfig.php");
            executePlainSQL("INSERT INTO Messages1 VALUES ('${cname}', CURRENT_TIMESTAMP, '${mtext}', '${senderName}') ");
            OCICommit($db_conn);
        }
        
        if (isset($_POST['submit'])) {
            if(connectToDB()){
                sendMessage();
            }
            disconnectFromDB();
        } else if (isset($_POST['load'])) {
            header("Location: message.php");
        }

		?>
	</body>
</html>

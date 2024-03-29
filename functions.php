<?php

    session_start();

    $link = mysqli_connect("localhost", "root", "password", "twitter");

    if (mysqli_connect_errno()) {

        print_r (mysqli_connect_error());
        exit;
 
    }

    if (isset($_GET['function'])) {

        if ($_GET['function'] == "logout") {

            session_unset();

        }

    }

    /*function time_since($since) {
        $chunks = array(
            array(60 * 60 * 24 * 365 , 'year'),
            array(60 * 60 * 24 * 30 , 'month'),
            array(60 * 60 * 24 * 7, 'week'),
            array(60 * 60 * 24 , 'day'),
            array(60 * 60 , 'hour'),
            array(60 , 'min'),
            array(1 , 'sec')
        );
    
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($since / $seconds)) != 0) {
                break;
            }
        }
    
        $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
        return $print;
    }*/

    function get_time_ago( $time ) {
        
    $time_difference = time() - $time;

    if( $time_difference < 1 ) { return 'less than 1 second ago'; }
    $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
    );

    foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;

        if( $d >= 1 )
        {
            $t = round( $d );
            return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
        }
    }
}



    function displayTweets($type) {

        global $link; // adding global allows function to access variable that is scoped outside of it
        global $whereClause;
        
        if ($type == 'public') {
            $whereClause = "";

        } else if ($type == 'isfollowing') {

            $query = "SELECT * FROM isfollowing WHERE follower = ". mysqli_real_escape_string($link, $_SESSION['id'])."";
            $result = mysqli_query($link, $query);

            $whereClause = "";
    
            while ($row = mysqli_fetch_assoc($result)) {

                if ($whereClause =="") $whereClause = "WHERE";
                else $whereClause.= " OR ";
                $whereClause = " userid = ".$row['isFollowing'];
            } 

        } else if ($type == 'yourtweets') {

            $whereClause = "WHERE userid = ". mysqli_real_escape_string($link, $_SESSION['id']);

        } else if ($type == 'search') {

            echo "<p>Showing results for '". mysqli_real_escape_string($link, $_GET['q'])."':</p>";

            $whereClause = "WHERE tweet LIKE '%". mysqli_real_escape_string($link, $_GET['q'])."%'";

        } else if (is_numeric($type)) {

            $userQuery = "SELECT * FROM users WHERE id = ".mysqli_real_escape_string($link, $type)." LIMIT 1";
            $userQueryResult = mysqli_query($link, $userQuery);
            $user = mysqli_fetch_assoc($userQueryResult);

            echo "<h2>". mysqli_real_escape_string($link, $user['email'])."'s tweets</h2>";

            $whereClause = "WHERE userid = ". mysqli_real_escape_string($link, $type);


        }


        $query = "SELECT * FROM tweets ". $whereClause ." ORDER BY `datetime` DESC LIMIT 10";

        $result = mysqli_query($link, $query);

        if (mysqli_num_rows($result) == 0) {

            echo "There are no tweets to display";

        } else {

            while ($row = mysqli_fetch_assoc($result)) {

                $userQuery = "SELECT * FROM users WHERE id = ".mysqli_real_escape_string($link, $row['userid'])." LIMIT 1";
                $userQueryResult = mysqli_query($link, $userQuery);
                $user = mysqli_fetch_assoc($userQueryResult);

                echo "<div class='tweet'><p><a href='?page=publicprofiles&userid=".$user['id']."'>" .$user['email']. "</a><span class='time'>".get_time_ago(strtotime($row['datetime']))." ago</span>:</p>";

                echo "<p>".$row['tweet']."</p>";

                echo "<p><a class='toggleFollow' data-userID='".$row['userid']."'>";

                $isFollowingQuery = "SELECT * FROM isfollowing WHERE follower = ". mysqli_real_escape_string($link, $_SESSION['id'])." AND isFollowing = ".mysqli_real_escape_string($link, $row['userid'])." LIMIT 1";
                $isFollowingQueryResult = mysqli_query($link, $isFollowingQuery);
                if (mysqli_num_rows($isFollowingQueryResult) > 0) {

                    echo "Unfollow";
                } else {
                    echo "Follow";
                }      
                
                echo "</a></p></div>";

            }


        }

    }

    function displaySearch() {

        echo '<form class="form-inline">
        <label class="sr-only" for="inlineFormInputName2">Search</label>
        <input type="hidden" name="page" value="search">
        <input type="text" name="q" class="form-control mb-2 mr-sm-2" id="search" placeholder="Search">           
        <button type="submit" class="btn btn-primary mb-2">Search Tweets</button>
        </form>';

    }

    function displayTweetBox() {

        if ($_SESSION['id'] > 0) {

            echo '<div id="tweetSuccess" class="alert alert-success">Your tweet was posted successfully.</div>
            <div id="tweetFail" class="alert alert-danger"></div>  
            <div class="form">
            <div class="textbox"><label for="textarea">Post a tweet</label>
            <textarea class="form-control" id="tweetContent" rows="3"></textarea>
            </div>
            <button id="postTweetButton" class="btn btn-primary mb-2">Post Tweet</button>
            </div>';

        } else { echo 'please log in';}

    }

    function displayUsers() {

        global $link;

        $query = "SELECT * FROM users LIMIT 10";

        $result = mysqli_query($link, $query);

            while ($row = mysqli_fetch_assoc($result)) {

                echo  "<p><a href='?page=publicprofiles&userid=".$row['id']."'>".$row['email']."</a></p>";
            }

    }

?>
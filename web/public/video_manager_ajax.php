<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/s/classes/config.inc.php"); ?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/s/classes/db_helper.php"); ?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/s/classes/time_manip.php"); ?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/s/classes/user_helper.php"); ?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/s/classes/video_helper.php"); ?>
<?php $__server->page_title = "test"; ?>
<?php $__video_h = new video_helper($__db); ?>
<?php $__user_h = new user_helper($__db); ?>
<?php $__db_h = new db_helper(); ?>
<?php $__time_h = new time_helper(); ob_start(); ?>
<?php
    $search = $_SESSION['siteusername'];

    if($_GET['filter'] == "time") {
        $stmt = $__db->prepare("SELECT * FROM videos WHERE author = :username ORDER BY id DESC");
        $stmt->bindParam(":username", $_SESSION['siteusername']);
        $stmt->execute();
    } else if($_GET['filter'] == "title") {
        $stmt = $__db->prepare("SELECT * FROM videos WHERE author = :username ORDER BY title DESC");
        $stmt->bindParam(":username", $_SESSION['siteusername']);
        $stmt->execute();
    }

    $results_per_page = 12;
    $number_of_result = $stmt->rowCount();
    $number_of_page = ceil ($number_of_result / $results_per_page);  

    if (!isset ($_GET['page']) ) {  
        $page = 1;  
    } else {  
        $page = (int)$_GET['page'];  
    }  

    $page_first_result = ($page - 1) * $results_per_page;  

    if($_GET['filter'] == "time") {
        $stmt6 = $__db->prepare("SELECT * FROM videos WHERE author = :search ORDER BY id DESC LIMIT :pfirst, :pper");
        $stmt6->bindParam(":search", $search);
        $stmt6->bindParam(":pfirst", $page_first_result);
        $stmt6->bindParam(":pper", $results_per_page);
        $stmt6->execute();
    } else if($_GET['filter'] == "title") {
        $stmt6 = $__db->prepare("SELECT * FROM videos WHERE author = :search ORDER BY title LIMIT :pfirst, :pper");
        $stmt6->bindParam(":search", $search);
        $stmt6->bindParam(":pfirst", $page_first_result);
        $stmt6->bindParam(":pper", $results_per_page);
        $stmt6->execute();
    }
?>              
<table style="width: 100%;">
    <tr>
        <th style="width: 80%;">
            <small class="video-filter-options">
                Sort by:  
                <a id="selector-title" onclick="changeFilter_Title();" <?php if($_GET['filter'] == "title") { ?>class="selected"<?php } ?>>Title</a> | 
                    <a id="selector-time" onclick="changeFilter_Time();" <?php if($_GET['filter'] == "time") { ?>class="selected"<?php } ?>>Time</a>
            </small>
        </th>
        <th style="margin: 5px; width: 20%;"></th>
    </tr>
    
    <?php
        while($video = $stmt6->fetch(PDO::FETCH_ASSOC)) { 
            $video['video_responses'] = $__video_h->get_video_responses($video['rid']);
            $video['age'] = $__time_h->time_elapsed_string($video['publish']);		
            $video['duration'] = $__time_h->timestamp($video['duration']);
            $video['views'] = $__video_h->fetch_video_views($video['rid']);
            $video['author'] = htmlspecialchars($video['author']);		
            $video['title'] = htmlspecialchars($video['title']);
            $video['description'] = $__video_h->shorten_description($video['description'], 50);

            if($video['thumbnail'] == ".png" && $video['filename'] == ".mp4") {
                $status = "Corrupted";
            } else if($video['visibility'] == "v") {
                $status = "Approved";
            } else if($video['visibility'] == "n") {
                $status = "Approved";
            } else if($video['visibility'] == "o") {
                $status = "Disapproved";
            } else {
                $status = "Unknown";
            }                      
            
            if($video['commenting'] == "a") 
                $video['commentstatus'] = "Commenting allowed";
            else 
                $video['commentstatus'] = "Commenting disallowed";
    ?> 
    <tr style="margin-top: 5px;" id="videoslist">
        <td class="video-manager-left">
            <ul>
                <li class="video-list-item "><a href="/watch?v=<?php echo $video['rid']; ?>" class="video-list-item-link yt-uix-sessionlink" data-sessionlink="ei=CNLr3rbS3rICFSwSIQodSW397Q%3D%3D&amp;feature=g-sptl%26cid%3Dinp-hs-ytg"><span class="ux-thumb-wrap contains-addto "><span class="video-thumb ux-thumb yt-thumb-default-120 "><span class="yt-thumb-clip"><span class="yt-thumb-clip-inner"><img src="http://s.ytimg.com/yt/img/pixel-vfl3z5WfW.gif" alt="<?php echo $video['title']; ?>" data-thumb="/dynamic/thumbs/<?php echo $video['thumbnail']; ?>" width="120"><span class="vertical-align"></span></span></span></span><span class="video-time"><?php echo $video['duration']; ?></span>
                    <button onclick=";return false;" title="Watch Later" type="button" class="addto-button video-actions addto-watch-later-button-sign-in yt-uix-button yt-uix-button-default yt-uix-button-short yt-uix-tooltip" data-button-menu-id="shared-addto-watch-later-login" data-video-ids="yuTBQ86r8o0" role="button"><span class="yt-uix-button-content">  <img src="//s.ytimg.com/yt/img/pixel-vfl3z5WfW.gif" alt="Watch Later">
                    </span><img class="yt-uix-button-arrow" src="//s.ytimg.com/yt/img/pixel-vfl3z5WfW.gif" alt=""></button>
                    </span><span dir="ltr" class="title" title="<?php echo $video['title']; ?>"><?php echo $video['title']; ?></span><span class="stat">by <span class="yt-user-name " dir="ltr"><?php echo $video['author']; ?></span></span><span class="stat view-count">  <span class="viewcount"><?php echo $video['views']; ?> views</span>
                    </span></a>
                </li>
            </ul>
        </div>
                
            <a href="/edit_video?id=<?php echo $video['rid']; ?>">
                <button type="button" class=" yt-uix-button yt-uix-button-default" role="button">
                    Edit
                </button>
            </a>
            <a href="/get/delete_video?id=<?php echo $video['rid']; ?>">
                <button type="button" class=" yt-uix-button yt-uix-button-default" role="button">
                    Delete
                </button>
            </a>
            <a href="/get/toggle_comment?id=<?php echo $video['rid']; ?>">
                <button type="button" class=" yt-uix-button yt-uix-button-default" role="button">
                    Toggle Commenting
                </button>
            </a>

            <span style="margin-left:10px">
                <img src="/s/img/world.png"> <span style="font-size: 11px;position: relative;bottom: 2px;left: 5px;">Public</span>
            </span>
        </td>
        <td class="video-manager-stats">
            <span class="video-manager-span" style="width:140px;display: inline-block;margin-bottom: 4px;">
            <span class="small-text">Views: </span><span style="float:right;"><?php echo $__video_h->fetch_video_views($video['rid']); ?></span>
            </span><br>

            <span class="video-manager-span" style="width:140px;display: inline-block;margin-bottom: 4px;">
            <span class="small-text">Comments: </span><span style="float:right;"><?php echo $__video_h->get_comments_from_video($video['rid']); ?></span>
            </span><br>

            <span class="video-manager-span" style="width:140px;display: inline-block;margin-bottom: 4px;">
            <span class="small-text">Video Responses: </span><span style="float:right;"><?php echo $video['video_responses']; ?></span>
            </span><br>
        </td>
    </tr>
    <?php } ?>
</table> 
<?php
$content = ob_get_clean();
header('Content-Type: application/json');
echo '{"content_html": ' . json_encode($content) . '}';
exit();
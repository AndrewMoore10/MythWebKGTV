<?php
/**
 * Show recorded programs.
 *
 * @license     GPL
 *
 * @package     MythWeb
 * @subpackage  TV
 *
/**/

// Set the desired page title 
    $page_title = 'KGTV MythTV - Live View';

// Custom headers
//    $headers[] = '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">';
    
    $headers[] = '<script type="text/javascript" src="/mythweb/js/libs/flowplayer/flowplayer.js" /></script>';
    $headers[] = '<script type="text/javascript">
        function confirm_change(){
            if( confirm("This will change channels for all viewers watching this stream"))  $(\'change_channel\').submit();
            
        }
        </script>';

// Print the page header
    require 'modules/_shared/tmpl/'.tmpl.'/header.php';

// Global variables used here
    global $All_Shows, $Total_Programs, $Total_Time, $Total_Used, $Groups, $Program_Titles, $db;

// Show the recgroup?
    if (count($Groups) > 1)
        $recgroup_cols = 1;
    else
        $recgroup_cols = 0;

// Setup for grouping by various sort orders
    $group_field = $_SESSION['recorded_sortby'][0]['field'];
    if ($group_field == "")
        $group_field = "airdate";
    elseif ( ! (($group_field == "title") || ($group_field == "channum") || ($group_field == "airdate") || ($group_field == "recgroup")) )
        $group_field = "";

    
    
/**
 * Prints a <select> of the available channels
/**/
    function channel_select($params = '', $selected='', $id=000) {
        $channels = Channel::getChannelList();
        echo "<select name=\"chan_$id\" $params>";
        foreach ($channels as $chanid) {
            $channel =& Channel::find($chanid);
        // Not visible?
            if (empty($channel->visible) || $channel->chanid == 91010)
                continue;
        // Print the option
            echo '<option value="', $channel->chanid, '"',
                 ' title="', html_entities($channel->name), '"';
        // Selected?
            if (($channel->chanid == $selected) ||
                ($channel->chanid == $_GET['chanid']))
                echo ' SELECTED';
        // Print the rest of the content
            echo '>';
            if ($_SESSION["prefer_channum"])
                echo $channel->channum.'&nbsp;&nbsp;('.html_entities($channel->callsign).')';
            else
                echo html_entities($channel->callsign).'&nbsp;&nbsp;('.$channel->channum.')';
            echo '</option>';
        }
        echo '</select>';
    }
?>

<div>    <div style="width:800px; height:450px; margin: auto;">
 <a href="" id="player">
            </a> 
<form id="change_channel" action="<?php root_url.'tv/live' ?>" method="post" style="margin:auto;">             
    <select name="channel">
        <option id="All recordings" value=""><?php echo t('All channels') ?></option>
<?php
        $channels = Channel::getChannelList();
        foreach ($channels as $chanid) {
            $channel =& Channel::find($chanid);
        // Not visible?
            if (empty($channel->visible))
                continue;
        // Print the option
            echo '<option value="', $channel->chanid, '"',
                 ' title="', html_entities($channel->name), '"';
        // Selected?
            if (($channel->chanid == $selected) ||
                ($channel->chanid == $_GET['chanid']))
                echo ' SELECTED';
        // Print the rest of the content
            echo '>';
            if ($_SESSION["prefer_channum"])
                echo $channel->channum.'&nbsp;&nbsp;('.html_entities($channel->callsign).')';
            else
                echo html_entities($channel->callsign).'&nbsp;&nbsp;('.$channel->channum.')';
            echo '</option>';
        }
?>
    </select>
    <input type="button" value="change" onclick="confirm_change();" />
</form>
    </div>
    <script>
                var timerStart = Date.now();
                flowplayer(
                    "player",
                    "http://kgtv-news-dvr02.ad.ewsad.net/mythweb/js/libs/flowplayer/flowplayer.swf", {
                        playlist: [
                            {
                                url: "http://kgtv-news-dvr02:8080/live.flv",
                                duration: 0,
                                autoPlay: true,
                                scaling: 'fit',
                                // Would be nice to auto-buffer, but we don't want to
                                // waste bandwidth and CPU on the remote machine.
                                autoBuffering: false,
                                wmode: 'transparent'
                                }
                            ],
                        // player events are defined directly to "root" (not inside a clip)
                        onBufferEmpty: function() {
                            if(Date.now() - timerStart > 5000){
                                //alert("onBufferEmpty triggered: Channel may have chaned. Refreshing page.");
                                window.setTimeout("location.reload(true);", 5000);
                            }
                        },
                        plugins: {
                            controls: null
                        }
                        
                    });
            </script>
            <br>
<?php
// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';

    ?>
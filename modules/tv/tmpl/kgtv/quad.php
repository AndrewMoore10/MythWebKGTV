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
    $page_title = 'KGTV MythTV - '.t('Recorded Programs');

// Custom headers
//    $headers[] = '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">';
    $headers[] = '<link rel="stylesheet" type="text/css" href="'.skin_url.'/tv_recorded.css">';
    $headers[] = '<link rel="stylesheet" type="text/css" href="'.skin_url.'/status.css">';
    $headers[] = '<style type="text/css"> 
            .quadViews img{
                border-radius:10px;
                width:50% !important;
	    }
            .quadViews th {
		text-align: center !important;
		font-size: 12pt;
		font-weight: bold !important;
		border-color: #000000;
		color: #2A6061;
	    }
            .quadViews {
		padding-top: 20px;
                border-radius:20px;
                width:80% !important;
		background-color: #c5d8e9
	    }
            #recorded_list {
                width:80% !important;
	    }
        </style>
';
// Rss links
    $headers[] = '<link rel="alternate" type="application/rss+xml" href="'.str_replace(root_url, root_url.'rss/', $_SERVER['REQUEST_URI']).'">';

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
        echo "\n<select name=\"chan_$id\" $params>";
        foreach ($channels as $chanid) {
            $channel =& Channel::find($chanid);
        // Not visible?
            if (empty($channel->visible) || $channel->chanid == 91010)
                continue;
        // Print the option
            echo '
                <option value="', $channel->chanid, '"',
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

<div>    
    <table class="quadViews" id="recorded_list" border="0" cellpadding="0" cellspacing="0" style="width:100%; margin: auto" class="list small" >
        <tr class="menu">
            <th class="x-null" style="border-bottom: solid 2px">&nbsp;</th>
            <th class="x-title" style="border-bottom: solid 2px">Time Slot</th>
            <th class="x-subtitle" style="border-bottom: solid 2px">Upper Left</th>
            <th class="x-subtitle" style="border-bottom: solid 2px">Upper Right</th>
            <th class="x-subtitle" style="border-bottom: solid 2px">Lower Left</th>
            <th class="x-subtitle" style="border-bottom: solid 2px">Lower Right</th>
            <th class="x-subtitle" style="border-bottom: solid 2px">Duration</th>
            <th class="x-subtitle" style="border-bottom: solid 2px">&nbsp;</th>
            <th class="x-null" style="border-bottom: solid 2px">&nbsp;</th>
        </tr>
     <?php
     for($i=0; $i < count($quadviews);$i++ ) {
         echo '<tr  style=" text-align:center;" id="quadrow_'.$i.'">';
         echo '   <td style="width:auto;" ></td>';
         echo '   <td style="width:100px;" id="sTime_'.$i.'" text-align:center;">'.$quadviews[$i]["startTime"].'</td>';
         for($j=0; $j < 4; $j++){
         echo '   <td style="width:200px;" id="sel'.$i.'_'.$j.'">';
         echo        channel_select("style='display:none'", $quadviews[$i]["chan".($j +1)],$j);
         echo '      <img class="channelicon" title="'.$quadviews[$i]["chan".($j +1)].'" src="'.Channel::find($quadviews[$i]["chan".($j +1)])->icon.'">';
         echo '   </td>';
         }
//         echo '   <td style="width:200px;" id="UR_'.$i.'"><img class="channelicon" src="'.Channel::find($quadviews[$i]["chan2"])->icon.'">'.'</td>';
//         echo '   <td style="width:200px;" id="LL_'.$i.'"><img class="channelicon" src="'.Channel::find($quadviews[$i]["chan3"])->icon.'">'.'</td>';
//         echo '   <td style="width:200px;" id="LR_'.$i.'"><img class="channelicon" src="'.Channel::find($quadviews[$i]["chan4"])->icon.'">'.'</td>';
         echo '   <td style="width:100px;" id="Dur_'.$i.'">'.$quadviews[$i]["duration"].'</td>';
         echo '   <td style="width:100px;" id="quadButtons_'.$i.'" class="x-commands commands" >';
         echo '      <a id="quad_edit'.$i.'" onclick="javascript:edit('.$i.')" title="Edit QuadView">Edit</a>'; 
         echo '      <a onclick="javascript:confirm_delete('.$i.', false)" title="Delete QuadView">Delete</a>';
         echo '   </td>';
         echo '   <td style="width:auto;"></td>';
         echo '</tr>';
         
     }
     ?>
      
    </table>
</div>

<div class="list small" style="width: 40% !important; margin: auto; margin-bottom: 30px;padding-top: 10px;">
    <a onclick="javascript:$('quadAddForm').toggle();" ><h2>Create Quad View</h2></a>
    <form name ="quadAddForm" id="quadAddForm" action="<?php echo root_url ?>tv/quad" method="post" style="display:none;">
        <table id="addQuad" style="margin: auto;">
            <tr>
                <td style="width:100px;"></td>
                <td colspan="2" ></td>
                <td style="width:100px"></td>
            </tr>
            <tr>
                <td></td>
                <td>Channel 1: </td>
                <td><?php channel_select('', '12089',1);?></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Channel 2: </td>
                <td><?php channel_select('', '12087',2);?></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Channel 3: </td>
                <td><?php channel_select('', '12085',3);?></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Channel 4: </td>
                <td><?php channel_select('', '12088',4);?></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Start Time: </td>
                <td><input type="time" width="200px" name="start" value="12:00:00"/></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>Duration: </td>
                <td>
                    <select name="duration">
                        <option value ="00:30:00">30 minutes</option>
                        <option value ="01:00:00">1 hours</option>
                        <option value ="01:30:00">1.5 hours</option>
                        <option value ="02:00:00">2 hours</option>
                        <option value ="03:00:00">3 hours</option>
                    </select>
                </td>
                <td></td>
<!--                <td>30 minutes<input type="hidden" width="200px" name="end"/></td>-->
            
            </tr>
            <tr>
                <td></td>
                <td>Daily :</td>
                <td><input type="checkbox" name="daily"/></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="hidden" name="quad" value="yes" /></td>
                <td><input type="submit" value="Create" onclick="javascript:create_quad()"></input><input type="reset"/></td>
                <td></td>
            </tr>
         </table>
    </form>
</div>



<table id="title_choices" class="commandbox commands" border="0" cellspacing="0" cellpadding="4"  style="display: none">
<tr>
<?php if (count($Groups) > 1) { ?>
    <td class="x-group"><?php echo t('Show group') ?>:</td>
    <td><select name="recgroup" onchange="$('change_title').submit()">
        <option value=""><?php echo t('All groups') ?></option><?php
        foreach($Groups as $recgroup => $count) {
            echo '<option id="Group '.htmlspecialchars($recgroup).'" value="'.htmlspecialchars($recgroup).'"';
            if ($_REQUEST['recgroup'] == $recgroup)
                echo ' SELECTED';
            echo '>'.html_entities($recgroup)
                .' ('.tn('$1 recording', '$1 recordings', $count)
                .')</option>';
        }
        ?>
    </select></td>
<?php
    }
?>
<!--    <td class="x-recordings"><?php echo t('Show recordings') ?>:</td>
    <td><select name="title" onchange="$('change_title').submit()">
        <option id="All recordings" value=""><?php echo t('All recordings') ?></option>
<?php
        foreach($Program_Titles as $title => $count) {
            echo '<option id="Title '.htmlspecialchars($title).'" value="'.htmlspecialchars($title).'"';
            if ($_REQUEST['title'] == $title)
                echo ' SELECTED';
            echo '>'.html_entities($title)
                .($count > 1 ? ' ('.tn('$1 episode', '$1 episodes', $count).')' : "")
                ."</option>\n";
        }
?>-->
    </select></td>
</tr>
</table>
</form>
<table id="recorded_list" border="0" cellpadding="0" cellspacing="0" class="list small"  style="display: block">
<tr class="menu">
    <th colspan="2" style="width:10%;"></th>
    <th class="x-title" style="width:50%;"><?php             echo get_sort_link('title',           t('Title'))            ?></th>
    <th class="x-airdate" style="width:10%;"><?php           echo get_sort_link('airdate',         t('Airdate'))          ?></th>
    <th class="x-length" style="width:10%;"><?php            echo get_sort_link('length',          t('Length'));          ?></th>
</tr><?php
    $row     = 0;
    $section = -1;

    $prev_group = '';
    $cur_group  = '';

    $offset = $_REQUEST['offset'];
    $rows = $offset;

    foreach ($All_Shows as $show) {
        flush();
        if ($_SESSION['recorded_paging'] > 0 && $rows > 0) {
            $rows--;
            continue;
        }

    // Print a dividing row if grouping changes
        switch ($group_field) {
            case 'airdate':
                $cur_group = strftime($_SESSION['date_listing_jump'], $show->starttime);
                break;
            case 'recgroup':
                $cur_group = $show->recgroup;
                break;
            case 'channum':
                $cur_group = $show->channel->channum.' - '.$show->channel->name;
                break;
            case 'title':
                $cur_group = $show->title;
                break;
        }

        if ( $cur_group != $prev_group && $group_field != '' ) {
            $section++;
            $colspan = 6 + $recgroup_cols;
            print <<<EOM
<tr id="breakrow_$section" class="list_separator">
    <td colspan="$colspan" class="list_separator">$cur_group</td>
</tr>
EOM;
        }

        echo "<tr id=\"inforow_$row\" class=\"recorded inforow\">\n";
        if ($group_field != "")
            echo "    <td class=\"list\" rowspan=\"2\">&nbsp;</td>\n";
?>
    <td rowspan="2" class="x-pixmap<?php
        if (true) { ?>"><?php
        $padding = 39 - (50 / $show->getAspect());
        if ($padding > 0) { ?>
        <div style="height: <?php echo $padding; ?>px; width: 100px; float: left;">
        </div><?php } ?>
        <a class="x-pixmap" href="<?php echo root_url ?>tv/detail/<?php echo $show->chanid, '/', $show->recstartts ?>" title="<?php echo t('Recording Details') ?>"
            ><img src="<?php echo $show->thumb_url(100,0) ?>" width="100" height="<?php echo floor(100 / $show->getAspect()); ?>"></a>
<?php   }
        else
            echo ' -noimg">';
?>
        <a class="x-download"
            href="<?php echo video_url($show, true) ?>" title="<?php echo t('ASX Stream'); ?>"
            ><img height="24" width="24" src="<?php echo skin_url ?>/img/play_sm.png" alt="<?php echo t('ASX Stream'); ?>"></a>
        <a class="x-download"
            href="<?php echo $show->url ?>" title="<?php echo t('Direct Download'); ?>"
            ><img height="24" width="24" src="<?php echo skin_url ?>/img/video_sm.png" alt="<?php echo t('Direct Download'); ?>"></a>
        </td>
    <td class="x-title"><?php echo '<a href="', root_url, 'tv/detail/', $show->chanid, '/', $show->recstartts, '"'
                    .($_SESSION['recorded_pixmaps'] ? '' : " name=\"$row\"")
                    .' title="', t('Recording Details'), '"'
                    .'>'.$show->title.'</a>' ?></td>
    <td class="x-airdate"><?php echo strftime($_SESSION['date_recorded'], $show->starttime) ?></td>
    <td class="x-length"><?php echo nice_length($show->length) ?></td>

</tr><tr id="statusrow_<?php echo $row ?>" class="recorded statusrow">
    <td colspan="4" valign="top"><?php echo $show->description ?></td>
</tr><?php
        $prev_group = $cur_group;
    // Keep track of how many shows are visible in each section
        $row_count[$section]++;
    // Keep track of which shows are in which section
        $row_section[$row] = $section;
    // Increment row last
        $row++;
    // Only display as many as requested
        if ($_SESSION['recorded_paging'] > 0 && $row >= $_SESSION['recorded_paging'])
            break;
    }
?>

</table>

<div id="recorded_pager">
<?php
    if ($_SESSION['recorded_paging'] > 0) {
        echo 'Pages - ';
        $total_pages = ceil(count($All_Shows)/$_SESSION['recorded_paging']);
        $current_page = $_REQUEST['offset'] / $_SESSION['recorded_paging'];
        for ($i = 1; $i <= $total_pages; $i++) {
            $query = '';
            foreach($_GET as $key => $value) {
                if ($key == 'offset')
                    continue;
                $query .= urlencode($key).'='.urlencode($value).'&';
            }
            $query .= 'offset='.(($i-1)*$_SESSION['recorded_paging']);
            if ($i != 1)
                echo ' | ';
            echo '<a href="'.root_url.'tv/recorded?'.$query.'">'.$i.'</a>';
        }
    }
?>
</div>



<script type="text/javascript">
<!--

// Some initial values for global counters
    var diskused       = parseInt('<?php echo addslashes(disk_used) ?>');
    var programcount   = parseInt('<?php echo addslashes($Total_Programs) ?>');
    var programs_shown = parseInt('<?php echo count($All_Shows) ?>');
    var totaltime      = parseInt('<?php echo addslashes($Total_Time) ?>');

// Initialize some variables that will get set after the page table is printed
    var rowcount     = new Array();
    var rowsection   = new Array();
    var titles       = new Object;
    var groups       = new Object;

// Load the known shows
    var file  = null;
    var files = new Array();
    
    var quad = null;
    var quads = new Array();

<?php
    foreach ($All_Shows as $show) {
?>
    file = new Object();
    file.title      = '<?php echo addslashes($show->title)                  ?>';
    file.subtitle   = '<?php echo addslashes($show->subtitle)               ?>';
    file.chanid     = '<?php echo addslashes($show->chanid)                 ?>';
    file.starttime  = '<?php echo addslashes($show->recstartts)             ?>';
    file.recgroup   = '<?php echo addslashes(str_replace('%2F', '/', rawurlencode($show->recgroup)))    ?>';
    file.filename   = '<?php echo addslashes(str_replace('%2F', '/', rawurlencode($show->filename)))    ?>';
    file.size       = '<?php echo addslashes($show->filesize)               ?>';
    file.length     = '<?php echo addslashes($show->recendts - $show->recstartts) ?>';
    file.autoexpire = <?php echo $show->auto_expire ? 1 : 0                 ?>;
    files.push(file);

<?php
    }
?>
    

<?php    
     for($i=0; $i < count($quadviews);$i++ ) {
?>
    quad = new Object();
    quad.starttime = '<?php echo addslashes($quadviews[$i]["startTime"])                  ?>';
    quad.id = '<?php echo addslashes($quadviews[$i]["id"])                  ?>';
    quads.push(quad);
    
    
<?php
    }
?>

// Set the autoexpire flag
    function set_autoexpire(id) {
        var file = files[id];
        var r = new Ajax.Request('<?php echo root_url ?>tv/detail/' + file.chanid + '/' + file.starttime,
                                 {
                                    parameters: 'toggle_autoexpire='+(1 - file.autoexpire),
                                  asynchronous: false
                                 });
        if (r.transport.responseText == 'success') {
        // Update to the new value
            file.autoexpire = 1 - file.autoexpire;
        // Fix the images
            $('autoexpire_'+id).src = '<?php echo skin_url, '/img/flags/' ?>'
                                      + (file.autoexpire
                                         ? ''
                                         : 'no_')
                                      + 'autoexpire.png';
            if (file.autoexpire)
                $('autoexpire_'+id).title = '<?php echo addslashes(t('Click to disable Auto Expire')) ?>';
            else
                $('autoexpire_'+id).title = '<?php echo addslashes(t('Click to enable Auto Expire')) ?>';
        }
        else if (r.transport.responseText) {
            alert('Error: '+r.transport.responseText);
        }
    }

    function edit(row) {
        var quad = quads[row];
//        console.log($j('#sTime_'+row));
        if($j('#quad_edit'+row).html() == "Edit") {
            $j('#quad_edit'+row).html("Save");
            $j('#sTime_'+row).html('<input id="newTime" type="time" width="100px" name="start" value="'+$j('#sTime_'+row).html()+'"/>');
            $j('#quadrow_'+row+" img").hide();
            $j('#quadrow_'+row+" select").show();
            $j('#Dur_'+row).html('<input id="newDur" type="text" width="100px" name="duration" value="'+$j('#Dur_'+row).html()+'"/>');
            console.log($j('#newDur').val());
        }
        else{
                new Ajax.Request('<?php echo root_url; ?>tv/quad',
                                 {
                                    method: 'post',
                                    onSuccess: update_success,
                                    onFailue: http_failure,
                                    parameters: { ajax:       'yes',
                                                  'edit':   'yes',
                                                  id:       quad.id,
                                                  row:      row,
                                                  start:    $j('#newTime').val(),
                                                  0:        $j('#sel'+row+'_0 select').val(),
                                                  1:        $j('#sel'+row+'_1 select').val(),
                                                  2:        $j('#sel'+row+'_2 select').val(),
                                                  3:        $j('#sel'+row+'_3 select').val(),
                                                  duration: $j('#newDur').val()
                                                }
                                });
        }
    }
    function update_success(result){
//        alert("HI");
        console.log(result);
//        var row   = result.responseJSON['row'];
//        var sql   = result.responseJSON['SQL'];
        if(result.responseJSON != null){
            var SQL = result.responseJSON['SQL'];
//        alert(SQL);
            location.reload();
        }
        else bannerAlert("ERROR: " + result.responseText);
    }
    function confirm_delete(row, forget_old) {
        var quad = quads[row];
        if (confirm("<?php echo t('Are you sure you want to delete the following show?')
                    ?>\n\n     "+quad.starttime )) {
        // Do the actual deletion
            if (programs_shown == 1)
                location.href = '<?php echo root_url ?>tv/quad?delete=yes&id='+quad.id;
            else {
                ajax_add_request();
                new Ajax.Request('<?php echo root_url; ?>tv/quad',
                                 {
                                    method: 'post',
                                    onSuccess: http_success,
                                    onFailue: http_failure,
                                    parameters: { ajax:       'yes',
                                                  'delete':   'yes',
                                                  id:         quad.id,
                                                  row:      row
                                                }
                                });
            }
        // Debug statements - uncomment to verify that the right file is being deleted. Now with firebug goodness
            //console.log('row number ' + id + ' belonged to section ' + section + ' which now has ' + rowcount[section] + ' elements');
            //console.log('just deleted an episode of "' + title + '" which now has ' + episode_count + ' episodes left');
        }
    }
    function create_quad() {
//        var file = files[id];
//        if (confirm("\n\n     "+file.title + ((file.subtitle == '') ? "" : ": " +file.subtitle))) {
        // Do the actual deletion
        ajax_add_request();
//        console.log($().jquery);
        new Ajax.Request('<?php echo root_url; ?>tv/quad',
                        {
                            method: 'post',
                            onSuccess: http_success,
                            onFailue: http_failure,
                            parameters: { ajax:       'yes',
                                        'quad':   'yes'
                                        }
                        });
        // Debug statements - uncomment to verify that the right file is being deleted. Now with firebug goodness
            //console.log('row number ' + id + ' belonged to section ' + section + ' which now has ' + rowcount[section] + ' elements');
            //console.log('just deleted an episode of "' + title + '" which now has ' + episode_count + ' episodes left');
//        }
    }
    function http_success(result) {
        var row   = result.responseJSON['row'];
    // Hide the row from view
        $('quadrow_' + row).toggle();
        ajax_remove_request();
        alert(row);
    }

    function http_failure(err, errstr) {
        var id = result.responseJSON['id'].evalJSON();
        var st = result.responseJSON['starttime'].evalJSON();
        alert("Can't delete "+st+".\nHTTP Error:  " + errstr + ' (' + err + ')');
        ajax_remove_request();
    }

// -->
</script>

<script type="text/javascript">
<?php
    foreach ($row_count as $count)
        echo 'rowcount.push(['.escape($count)."]);\n";

    foreach ($row_section as $section)
        echo 'rowsection.push(['.escape($section)."]);\n";

    foreach($Program_Titles as $title => $count)
        echo 'titles['.escape($title).'] = '.escape($count).";\n";

    foreach($Groups as $recgroup => $count)
        echo 'groups['.escape($recgroup).'] = '.escape($count).";\n";
?>
</script>

<?php

    echo '<div style="padding-right: 75px; text-align: right; float: right; padding-top: 1em;">'
        .t('$1 programs, using $2 ($3) out of $4 ($5 free).',
           '<span id="programcount">'.t($Total_Programs).'</span>',
           '<span id="diskused">'.nice_filesize($Total_Used).'</span>',
           '<span id="totaltime">'.nice_length($Total_Time).'</span>',
           '<span id="disksize">'.nice_filesize(disk_size).'</span>',
           '<span id="diskfree">'.nice_filesize(disk_size - disk_used).'</span>'
          )
        .'</div>';

    echo '<div id="feed_buttons"><a href="rss'.$_SERVER['REQUEST_URI'].'"><img src="'.skin_url.'/img/rss2.0.gif"></a></div>';

// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';

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
    $page_title = 'MythWeb - '.t('Recorded Programs');

// Custom headers
    $headers[] = '<link rel="stylesheet" type="text/css" href="'.skin_url.'/tv_recorded.css">';

// Rss links
    $headers[] = '<link rel="alternate" type="application/rss+xml" href="'.str_replace(root_url, root_url.'rss/', $_SERVER['REQUEST_URI']).'">';
    

// Print the page header
    require 'modules/_shared/tmpl/'.tmpl.'/header.php';

// Global variables used here
    global $All_Shows, $Total_Programs, $Total_Time, $Total_Used, $Groups, $Program_Titles;

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

?>

<form id="change_title" action="<?php echo root_url ?>tv/recorded" method="get">
<table id="title_choices" class="commandbox commands" border="0" cellspacing="0" cellpadding="4">
<tr>


    <td class="x-recordings"><?php echo t('Channels') ?>:</td>
    <td><select name="chanid" onchange="$('change_title').submit()">
        <option id="All recordings" value=""><?php echo t('All channels') ?></option>
<?php
        $selChan = null;
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
                ($channel->chanid == $_GET['chanid'])){
                 echo ' SELECTED';
                 $selChan = $channel;
            }
        // Print the rest of the content
            echo '>';
            if ($_SESSION["prefer_channum"])
                echo $channel->channum.'&nbsp;&nbsp;('.html_entities($channel->callsign).')';
            else
                echo html_entities($channel->callsign).'&nbsp;&nbsp;('.$channel->channum.')';
            echo '</option>';
        }
?>
    </select></td>
</tr>
</table>
</form>
<table id="recorded_list" border="0" cellpadding="0" cellspacing="0" class="list small">
<tr class="menu">
    <td class="list"><?php echo $selChan->callsign ?>&nbsp;</td>
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
            $colspan = 2 + $recgroup_cols;
            print <<<EOM
<tr id="breakrow_$section" class="list_separator">
    <td colspan="$colspan" class="list_separator">$cur_group</td>
</tr>
EOM;
        }
?>
<tr id ="<?php echo "inforow_$row" ?>" class="recorded inforow">
    <td rowspan="1" class="x-pixmap"><?php
        $padding = 39 - (50 / $show->getAspect());
        if ($padding > 0) { ?>
        <div style="height: <?php echo $padding; ?>px; width: 100%; float: left;">
        </div><?php } ?>
        <a class="x-pixmap" href="<?php echo root_url ?>tv/detail/<?php echo $show->chanid, '/', $show->recstartts ?>" title="<?php echo t('Recording Details') ?>">
            <img src="<?php echo $show->thumb_url(100,0) ?>"><img class="x-pixmap chanright" src="<?php echo $show->channel->icon ?>"><br>
            <?php echo $show->title; ?></a><br>
            <?php echo strftime($_SESSION['date_recorded'], $show->starttime) ?>
        </td>    
</tr>
    <?php
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
<script type="text/javascript">
    function goToPage(){
        var page = document.getElementById("page_selector").value;
        <?php
            $total_pages = ceil(count($All_Shows)/$_SESSION['recorded_paging']);
            $current_page = $_REQUEST['offset'] / $_SESSION['recorded_paging'];
            $query = '';
            foreach($_GET as $key => $value) {
                if ($key == 'offset')
                    continue;
                $query .= urlencode($key).'='.urlencode($value).'&';
            }
            $query .= 'offset=';
        ?>
        window.location = "<?php echo root_url.'tv/recorded?'.$query; ?>"+ ((page -1) * <?php echo $_SESSION['recorded_paging']; ?>);
    }

</script>
<?php
    if ($_SESSION['recorded_paging'] > 0) {
        echo 'Page ';
        echo '<select id="page_selector" onChange="javascript:goToPage();" style="width:100px">';
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
            $sel = '';
            if(($i-1)*$_SESSION['recorded_paging'] == $_GET[offset]) $sel = "SELECTED ";
            echo '<option value='.$i.' '.$sel.'>'.$i.'</option>'; //<a href="'.root_url.'tv/recorded?'.$query.'">'.$i.'</a>';
        }
        echo '</select><br/>';
    }
?>
<?php
    if (false) {
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

    function confirm_delete(id, forget_old) {
        var file = files[id];
        if (confirm("<?php echo t('Are you sure you want to delete the following show?')
                    ?>\n\n     "+file.title + ((file.subtitle == '') ? "" : ": " +file.subtitle))) {
        // Do the actual deletion
            if (programs_shown == 1)
                location.href = '<?php echo root_url ?>tv/recorded?delete=yes&chanid='+file.chanid
                                +'&starttime='+file.starttime
                                +(forget_old
                                    ? '&forget_old=yes'
                                    : ''
                                 );
            else {
                ajax_add_request();
                new Ajax.Request('<?php echo root_url; ?>tv/recorded',
                                 {
                                    method: 'post',
                                    onSuccess: http_success,
                                    onFailue: http_failure,
                                    parameters: { ajax:       'yes',
                                                  'delete':   'yes',
                                                  chanid:     file.chanid,
                                                  starttime:  file.starttime,
                                                  forget_old: (forget_old ? 'yes' : ''),
                                                  id:         id,
                                                  file:       Object.toJSON(file)
                                                }
                                });
            }
        // Debug statements - uncomment to verify that the right file is being deleted. Now with firebug goodness
            //console.log('row number ' + id + ' belonged to section ' + section + ' which now has ' + rowcount[section] + ' elements');
            //console.log('just deleted an episode of "' + title + '" which now has ' + episode_count + ' episodes left');
        }
    }

    function http_success(result) {
        var id   = result.responseJSON['id'];
        var file = result.responseJSON['file'].evalJSON();
    // Hide the row from view
        $('inforow_' + id).toggle();
        $('statusrow_' + id).toggle();
    // decrement the number of rows in a section
        var section   = rowsection[id];
        rowcount[section]--;
    // Decrement the number of episodes for this title
        titles[file.title]--;
        var episode_count = titles[file.title]
    // If we just hid the only row in a section, then hide the section break above it as well
        if (rowcount[section] == 0) {
            $('breakrow_' + section).toggle();
        }
// UGLY! but works well enough...
    <?php if (count($Groups) > 1) { ?>
    // Change the recording groups dropdown on the fly.
        if (file.recgroup) {
        // Decrement the number of episodes for this group
            groups[file.recgroup]--;
            var group_count = groups[file.recgroup]
        // Change the groups dropdown menu on the fly
            if (group_count == 0) {
                $('Group ' + file.recgroup).toggle();
            }
            else {
                var group_text;
                group_text = (group_count > 1) ? ' (' + group_count + ' episodes)' : '';
                $('Group ' + file.recgroup).innerHTML = file.recgroup + group_text;
            }
        }
    <?php } ?>
    // Change the recordings dropdown menu on the fly
        if (episode_count == 0) {
            $('Title ' + file.title).toggle();
        }
        else {
            var count_text;
            count_text = (episode_count > 1) ? ' (' + episode_count + ' episodes)' : '';
            $('Title ' + file.title).innerHTML = file.title + count_text;
        }
    // Decrement the total number of shows and update the page
        programs_shown--;
        programcount--;
        $('programcount').innerHTML = programcount;
    // Decrease the total amount of time by the amount of the show
        totaltime -= file.length;
        $('totaltime').innerHTML = nice_length(totaltime, <?php
                                                         echo "'", addslashes(t('$1 hr')),   "', ",
                                                              "'", addslashes(t('$1 hrs')),  "', ",
                                                              "'", addslashes(t('$1 min')),  "', ",
                                                              "'", addslashes(t('$1 mins')), "'";
                                                         ?>);
    // Decrease the disk usage indicator by the amount of the show
        diskused -= file.size;
        $('diskused').innerHTML = nice_filesize(diskused);
    // Adjust the freespace shown
        $('diskfree').innerHTML = nice_filesize(<?php echo disk_size; ?> - diskused);
        // Eventually, we should perform the removal-from-the-list here instead
        // of in confirm_delete()
        ajax_remove_request();
    }

    function http_failure(err, errstr) {
        var file = result.responseJSON['file'].evalJSON();
        alert("Can't delete "+file.title+': '+file.subtitle+".\nHTTP Error:  " + errstr + ' (' + err + ')');
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

//    echo '<div id="feed_buttons"><a href="rss'.$_SERVER['REQUEST_URI'].'"><img src="'.skin_url.'/img/rss2.0.gif"></a></div>';

// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';

<?php
/**
 * PLUGIN NAME: Dynamic timeline
 * DESCRIPTION: Display Record data as a dynamic timeline
 * VERSION: 2.1.1
 * AUTHOR: Yec'han Laizet <y.laizet@bordeaux.unicancer.fr>
 *
 */


$project_json_error = "";
$definition = json_decode($module->getProjectSetting('parameters'), true);

// Manage project settings json errors
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        $project_json_error = '';
    break;
    case JSON_ERROR_DEPTH:
        $project_json_error = ' - Maximum stack depth exceeded';
    break;
    case JSON_ERROR_STATE_MISMATCH:
        $project_json_error = ' - Underflow or the modes mismatch';
    break;
    case JSON_ERROR_CTRL_CHAR:
        $project_json_error = ' - Unexpected control character found';
    break;
    case JSON_ERROR_SYNTAX:
        $project_json_error = ' - Syntax error, malformed JSON';
    break;
    case JSON_ERROR_UTF8:
        $project_json_error = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
    break;
    default:
        $project_json_error = ' - Unknown error';
    break;
}

if (isset($_GET['id'])) {

    // from redcap_vx.y.z/Classes/DataExport.php
    // Limit to user's DAG (if user is in a DAG), and if not in a DAG, then limit to the DAG filter
    $userInDAG = (isset($user_rights['group_id']) && is_numeric($user_rights['group_id']));
    $dags = ($userInDAG) ? $user_rights['group_id'] : $report['limiter_dags'];

    //Options
    $events = null;
    $groups = $dags;
    $combine_checkbox_values = false;
    $exportDataAccessGroups = false;
    $exportSurveyFields = false;
    $filterLogic = null;
    $exportAsLabels = true;

    //Get data
    $json_content = json_decode(Records::getData(PROJECT_ID, 'json', $_GET['id'], NULL,
        $events, $groups, $combine_checkbox_values, $exportDataAccessGroups, $exportSurveyFields, $filterLogic, $exportAsLabels), True);

    // Format data
    $group_map = array();
    foreach ($definition['groups'] as $g) {
        $group_map[$g['content']] = $g['id'];
    }
    $items = array();
    $i = 0;
    foreach ($json_content as $line) {
        $redcap_event_name = ($line['redcap_event_name'] ? $line['redcap_event_name'] : "");
        $redcap_repeat_instrument = ($line['redcap_repeat_instrument'] ? $line['redcap_repeat_instrument'] : "");
        $redcap_repeat_scope = $redcap_event_name."_".$redcap_repeat_instrument;
        foreach ($definition['data'][$redcap_repeat_scope] as $item_def) {
            $group = $group_map[$item_def['group_field']];
            $content_values = array();
            foreach ($item_def['fields'] as $field) {
                $content_values[] = $line[$field];
            }
            $content = vsprintf($item_def['content_format'], $content_values);
            $start = $line[$item_def['start_field']];
            $end = $line[$item_def['end_field']];
            $items[] = array(
                "redcap_repeat_scope" => $redcap_repeat_scope,
                "id" => $i,
                "group" => $group,
                "content" => $content,
                "start" => $start,
                "end" => $end
            );
            $i++;
        }
    }
}

$arm = getArm();
$records = Records::getRecordList($project_id, $user_rights['group_id'], true, false, $arm);
$new_url = $module->getUrl(basename(__FILE__)) . '&id=';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-timeline-graph2d.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-timeline-graph2d.min.css" rel="stylesheet" type="text/css" />

<h3 style="color:#800000;">
    Timeline
</h3>

<div class="chklist">
    <label for="record_id"><?php print $lang['grid_31']; ?>:
    <input id="record_id" value="<?php print $_GET['id']; ?>" placeholder="Type a record id or click to select" style="min-width: 16em;"></input>
</div>

<?php if (empty($definition)): ?>
<div class="red">
    No Timeline configuration detected for this project.
    Go to the left `Application` panel and click on `External Modules` to set the json configuration or ask an administrator if you do not have the permissions.
</div>
<?php endif; ?>

<?php if (!empty($project_json_error)): ?>
<div class="red">
    The project json configuration has the following error:
    <?php print $project_json_error; ?>
</div>
<?php endif; ?>


<script>
    var move_to = function(record_id){
        window.location.href = "<?php print $new_url; ?>" + record_id;
    };

    $('#record_id').autocomplete({
        source: <?php print json_encode(array_values($records)); ?>,
        minLength: 0,
        delay: 0,
        select: function( event, ui ) {
            console.log(ui.item.value);
            move_to(ui.item.value);
            //$(this).val(ui.item.value).trigger('blur');
            //return false;
        }
    })
    .focus(function() {
        // Show dropdown on focus
        $(this).autocomplete("search", "");
    })
    .data('ui-autocomplete')._renderItem = function(ul, item) {
        return $("<li></li>")
            .data("item", item)
            .append("<a>"+item.label+"</a>")
            .appendTo(ul);
    };
</script>

<br>

<div id="visualization"></div>

<script>
    var groups = <?php print json_encode($definition['groups']); ?>;
    var timedata = <?php print json_encode($items); ?>;

    // create visualization
    var container = document.getElementById('visualization');
    var options = {
        // option groupOrder can be a property name or a sort function
        // the sort function must compare two groups and return a value
        //     > 0 when a > b
        //     < 0 when a < b
        //       0 when a == b
        //groupOrder: function (a, b) {
            //return a.value - b.value;
        //},
        groupOrder: "order",
        editable: false
    };

    var timeline = new vis.Timeline(container);
    timeline.setOptions(options);
    timeline.setGroups(groups);
    timeline.setItems(new vis.DataSet(timedata));
</script>

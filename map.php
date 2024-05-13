<?php
ob_start();

require_once('includes/main/database.class.inc.php');
$db = new database();

function ip()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) // Proxy
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR']; // Normal ips
    }

    return $ip;
}

function mask_breaks($input)
{
    $output = str_replace("\n", '[br]', $input);
    return $output;
}

function ubb($input)
{
    $output = trim($input);
    $output = str_replace("[br]", '<br />', $output);


    $output = preg_replace("/\[img=(.+?)\]/si", "<img style=\"max-width:100% !important;\" src=\"\\1\">", $output);
    $output = preg_replace("/\[video=(.+?)\]/si", "<div class=\"iframeContainer\" ><iframe width=\"560\" height=\"315\" src=\"\\1\" frameborder=\"0\" allowfullscreen></iframe></div>", $output);

    $output = preg_replace("/\[b\](.+?)\[\/b\]/si", "<b>\\1</b>", $output);
    $output = preg_replace("/\[i\](.+?)\[\/i\]/si", "<i>\\1</i>", $output);
    $output = preg_replace("/\[u\](.+?)\[\/u\]/si", "<u>\\1</u>", $output);
    return $output;
}

?>
<link rel="stylesheet" href="includes/style/map.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<?php
//////////////////////////////////////////
// LOCALISATION
// If you want to add a language just add a new file to the lang folder and name it countrycode.lang.php (for example fr.lang.php) then the code in edx is &amp;lang=fr
//////////////////////////////////////////

$lang = $_GET['lang'];

//Load in lang file
require_once('lang/' . $lang . '.' . 'lang.php');

//I need to use these values in Javascript so I printed them on the page so I can retrieve them later. Kind of hacky but it works
echo '<div style="display:none;"><span id="hack_span_done">' . STR_INS_CTRL_DONE . '</span><span id="hack_span_cancel">' . STR_INS_CTRL_CANCEL . '</span>
<span id="hack_span_20a">' . STR_INS_SUBM_WORD_COUNT_20A . '</span>
<span id="hack_span_20b">' . STR_INS_SUBM_WORD_COUNT_20B . '</span>
<span id="hack_span_under_20a">' . STR_INS_SUBM_WORD_COUNT_UNDER_20A . '</span>
<span id="hack_span_under_20b_s">' . STR_INS_SUBM_WORD_COUNT_UNDER_20B_SINGULAR . '</span>
<span id="hack_span_under_20b_p">' . STR_INS_SUBM_WORD_COUNT_UNDER_20B_PLURAL . '</span>
<span id="hack_span_0">' . STR_INS_SUBM_WORD_COUNT_0 . '</span></div>';

//////////////////////////////////////////
// SHOW MAP
//////////////////////////////////////////

$course_id_sanitized = filter_var($_GET['course_id'] ?? '', FILTER_SANITIZE_STRING);
$course_id_sanitized = htmlspecialchars($course_id_sanitized, ENT_QUOTES, 'UTF-8', false);
$map_id_sanitized = filter_var($_GET['map_id'] ?? '', FILTER_SANITIZE_STRING);
$map_id_sanitized = htmlspecialchars($map_id_sanitized, ENT_QUOTES, 'UTF-8', false);
$user_id_sanitized = filter_var($_GET['user_id'] ?? '', FILTER_SANITIZE_STRING);
$user_id_sanitized = htmlspecialchars($user_id_sanitized, ENT_QUOTES, 'UTF-8', false);

// Now you can check if the action is 'show' and if the required parameters are set
if ($_GET['action'] == 'show' and !empty($course_id_sanitized) and isset($map_id_sanitized) and isset($user_id_sanitized)) {
    // Use the sanitized $course_id_sanitized variable in your queries
    $q_map = $db->query("SELECT * FROM mooc_map WHERE id = '" . addslashes($map_id_sanitized) . "' AND course_id = '" . $course_id_sanitized . "'", __LINE__);
    $q_maps = $db->query("SELECT * FROM mooc_map WHERE course_id = '" . $course_id_sanitized . "' AND visible = 1", __LINE__);
    if ($db->rows($q_map) == 0) {
        die('Bad request');
    }
?>
    <select style="margin-bottom: 10px; min-width: 550px; display: none;" aria-label="Select map" onchange="if(this.value){ window.location.href=this.value; }">
        <?php
        while ($data_map = $db->assoc($q_maps)) {
            echo '<option' . (($map_id_sanitized == $data_map['id']) ? ' selected="selected"' : '') . ' value="map.php?action=show&course_id=' . $data_map['course_id'] . '&map_id=' . $data_map['id'] . '&user_id=' . addslashes(htmlspecialchars($user_id_sanitized)) . '&lang=' . addslashes(htmlspecialchars($_GET['lang'])) . '">' . $data_map['name'] . $data_map['editable'] . '</option>';
        }
        ?>
    </select>
    <?php
    $q_map_entry = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "'", __LINE__);

    if (!defined('STR_SHOW_MAP_ARIA')) {
        define('STR_SHOW_MAP_ARIA', 'AAAAAA');
    }
    echo '<div id="map_canvas" aria-label="' . STR_SHOW_MAP_ARIA . '" class="map" style="height: 535px;"></div>'; ?>

    <!-- SHOW DELETE BUTTON -->
    <?php
    $q_map_entry_2 = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "'", __LINE__);

    $data_map_2 = $db->assoc($q_map);
    
    $data_map_entry_2 = array();

    if ($db->rows($q_map_entry_2) != 0) {
        $data_map_entry_2 = $db->assoc($q_map_entry_2);

        if (isset($data_map_2['editable']) && $data_map_2['editable'] == '1') {
            $entryId = isset($data_map_entry_2['id']) ? $data_map_entry_2['id'] : '';

            $TUD_delete_link = "location.href='map.php?action=delete&course_id=" . $course_id_sanitized . "&map_id=" . $map_id_sanitized . "&user_id=" . $user_id_sanitized . "&id=" . $entryId . "&lang=" . $_GET['lang'] . "'";
            $TUD_edit_link = "location.href='map.php?action=insert&course_id=" . $course_id_sanitized . "&map_id=" . $map_id_sanitized . "&user_id=" . $user_id_sanitized . "&id=" . $entryId . "&lang=" . $_GET['lang'] . "'";

            echo '<input type="button" title="' . STR_SHOW_DELETE_ENTRY_DESCR . '" aria-pressed="false" aria-label="' . STR_SHOW_DELETE_ENTRY_DESCR . '" id="TUD_delete" style="font-size: 17px; margin:1em 1em 1em 0;" tabi="0" onclick="' . $TUD_delete_link . '" value="' . STR_SHOW_DELETE_ENTRY . '"/>';
            echo '<input type="button" title="' . STR_SHOW_EDIT_ENTRY_DESCR . '" aria-pressed="false" aria-label="' . STR_SHOW_EDIT_ENTRY_DESCR . '" id="TUD_edit" style="font-size: 17px; margin:1em 1em 1em 0;" tabi="0" onclick="' . $TUD_edit_link . '" value="' . STR_SHOW_EDIT_ENTRY . '"/>';
        }
    } else if (isset($data_map_2['editable']) && $data_map_2['editable'] == '1') {
        $entryId = isset($data_map_entry_2['id']) ? $data_map_entry_2['id'] : '';
        $TUD_new_link = "location.href='map.php?action=insert&course_id=" . $course_id_sanitized . "&map_id=" . $map_id_sanitized . "&user_id=" . $user_id_sanitized . "&id=" . $entryId . "&lang=" . $_GET['lang'] . "'";

        echo '<input type="button" title="' . STR_SHOW_NEW_ENTRY_DESCR . '" aria-label="' . STR_SHOW_NEW_ENTRY_DESCR . '" aria-pressed="false" id="TUD_new" style="font-size: 17px; margin:1em 1em 1em 0;" tabi="0" onclick="' . $TUD_new_link . '" value="' . STR_SHOW_NEW_ENTRY . '"/>';
    }
    ?>

    <!--    INITIALISE MAP -->
    <link rel="stylesheet" href="https://js.arcgis.com/4.11/esri/themes/light/main.css">
    <script src="https://js.arcgis.com/4.11/"></script>
    <script type="text/javascript">
        $(function() {

            require([
                "esri/Map",
                "esri/views/MapView",
                "esri/layers/FeatureLayer",
                "esri/Graphic",
                "esri/layers/GraphicsLayer"
            ], function(Map, MapView, FeatureLayer, Graphic, GraphicsLayer) {

                var map = new Map({
                    basemap: "topo"
                });

                var view = new MapView({
                    container: "map_canvas",
                    map: map,
                    center: [0, 0], // longitude, latitude
                    zoom: 2
                });

                var graphicsLayer = new GraphicsLayer();

                map.featureReduction = {
                    type: "cluster",
                    custerRadius: 100
                }

                map.add(graphicsLayer);

                //When the view is loaded add the graphics here in a PHP loop
                view.when(function() {
                    <?php
                    $iteration = 0;
                    while ($data = $db->assoc($q_map_entry)) {
                    ?>

                        var g = new Graphic({
                            geometry: {
                                type: "point",
                                longitude: <?php echo $data['longitude'] ?>,
                                latitude: <?php echo $data['latitude'] ?>
                            },
                            attributes: {
                                ObjectID: <?php echo $iteration ?>,
                                DepArpt: "KATL",
                                MsgTime: Date.now(),
                                FltId: "UAL1"
                            },
                            symbol: {
                                type: "simple-marker",
                                color: [255, 255, 255, 0.8],
                                outline: {
                                    width: 2,
                                    color: [0, 166, 214],
                                },
                                size: "10px"
                            },
                            popupTemplate: {
                                "title": "<?php echo (empty($data['name']) ? 'Anonymous' : htmlspecialchars(preg_replace('/^\s+|\n|\r|\s+$/m', '', addslashes($data['name'])))) ?>",
                                "content": '<?php echo ubb(htmlspecialchars(preg_replace('/^\s+|\n|\r|\s+$/m', '', addslashes(mask_breaks($data['answer']))))) ?>'
                            }
                        });

                        graphicsLayer.add(g);

                    <?php
                        $iteration++;
                    }
                    ?>
                });
            });
        });
    </script>
<?php


}
//////////////////////////////////////////
// DELETE AND FORWARD
//////////////////////////////////////////
//Delete function which is called on submit or resubmit
elseif ($_GET['action'] == 'editdelete' and isset($map_id_sanitized) and isset($user_id_sanitized)) {
    $q_map = $db->query("SELECT * FROM mooc_map WHERE id = '" . addslashes($map_id_sanitized) . "'", __LINE__);
    if ($db->rows($q_map) != 1) {
        die('Bad request');
    }
    $data_map = $db->assoc($q_map);

    if ($data_map['editable'] == '1') {
        $q_map_entry = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "'", __LINE__);

        //Only delete if more than 2 entries. Stops new entry from immediately being deleted
        if ($db->rows($q_map_entry) > 1) {
            //Delete
            $db->query("DELETE FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "' AND datetime IS NOT NULL order by datetime asc LIMIT 1", __LINE__);
            //Forward
            header('Location: map.php?action=show&course_id=' . $course_id_sanitized . '&map_id=' . $map_id_sanitized . '&user_id=' . $user_id_sanitized . "&lang=" . $_GET['lang']);
        } else {
            header('Location: map.php?action=show&course_id=' . $course_id_sanitized . '&map_id=' . $map_id_sanitized . '&user_id=' . $user_id_sanitized . "&lang=" . $_GET['lang']);
        }
    } else {
        die('Map is no longer editable');
    }
}

//Delete function that is called on delete entry button click
elseif ($_GET['action'] == 'delete' and isset($map_id_sanitized) and isset($user_id_sanitized) and isset($_GET['id'])) {
    $q_map = $db->query("SELECT * FROM mooc_map WHERE id = '" . addslashes($map_id_sanitized) . "'", __LINE__);
    if ($db->rows($q_map) != 1) {
        die('Bad request');
    }
    $data_map = $db->assoc($q_map);

    if ($data_map['editable'] == '1') {
        $q_map_entry = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "' AND id = '" . addslashes($_GET['id']) . "'", __LINE__);
        if ($db->rows($q_map_entry) == 1) {
            //Delete
            $db->query("DELETE FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "' AND id = '" . addslashes($_GET['id']) . "'", __LINE__);
            //Forward
            header('Location: map.php?action=show&course_id=' . $course_id_sanitized . '&map_id=' . $map_id_sanitized . '&user_id=' . $user_id_sanitized . "&lang=" . $_GET['lang']);
        } else {
            die('Bad request');
        }
    } else {
        die('Map is no longer editable');
    }
}
//////////////////////////////////////////
// INSERT
//////////////////////////////////////////
elseif ($_GET['action'] == 'insert' and isset($map_id_sanitized) and isset($user_id_sanitized)) {
    $q_map = $db->query("SELECT * FROM mooc_map WHERE id = '" . addslashes($map_id_sanitized) . "'", __LINE__);
    if ($db->rows($q_map) != 1) {
        die('Bad request');
    }
    $data_map = $db->assoc($q_map);
    $q_map_entry = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "'", __LINE__);


    // Redirect if empty
    if ($data_map['editable'] != '1') {
        header('Location: map.php?action=show&course_id=' . $course_id_sanitized . '&map_id=' . $map_id_sanitized . '&user_id=' . $user_id_sanitized . "&lang=" . $_GET['lang']);
    } else {
        // Check if submission already has been made
        // If form is posted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!empty($_POST['location']) and !empty($_POST['answer']) and strlen($_POST['answer']) >= 20) {
                $explode_gps = explode(',', addslashes($_POST['gps']));
                $latitude = round(trim(ltrim($explode_gps[0], '(')), 7);
                $longitude = round(trim(rtrim($explode_gps[1], ')')), 7);
                $q_location_exists = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND latitude = '" . $latitude . "' AND longitude = '" . $longitude . "'");

                if ($db->rows($q_location_exists) != 0) {
                    $a_gps_count = 0;
                    $distance = 0.0001;
                    $a_gps[] = array(1, 1);
                    $a_gps[] = array(1, 0);
                    $a_gps[] = array(1, -1);
                    $a_gps[] = array(0, -1);
                    $a_gps[] = array(-1, -1);
                    $a_gps[] = array(-1, 0);
                    $a_gps[] = array(-1, 1);
                    $a_gps[] = array(0, 1);

                    while (!isset($unique_location)) {
                        $latitude = $latitude + $a_gps[$a_gps_count][1] * $distance;
                        $longitude = $longitude + $a_gps[$a_gps_count][0] * $distance;
                        $q_location_exists = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND latitude = '" . $latitude . "' AND longitude = '" . $longitude . "'");

                        if ($db->rows($q_location_exists) == 0) {
                            $unique_location = true;
                        } else {
                            if ($a_gps_count < (count($a_gps) - 1)) {
                                $a_gps_count++;
                            } else {
                                $a_gps_count = 0;
                                $distance = $distance + 0.0001;
                            }
                        }
                    }
                }


                $db->query("INSERT INTO mooc_map_entry (map_id, edx_user_id, ip, datetime, name, location, latitude, longitude, answer) VALUES ('" . addslashes($map_id_sanitized) . "', '" . addslashes($user_id_sanitized) . "', '" . ip() . "', NOW(), '" . addslashes($_POST['name']) . "', '" . addslashes($_POST['location']) . "', '" . round($latitude, 7) . "', '" . round($longitude, 7) . "', '" . addslashes($_POST['answer']) . "')", __LINE__);

                $form_hide = true;

                header('Location: map.php?action=editdelete&course_id=' . $course_id_sanitized . '&map_id=' . $map_id_sanitized . '&user_id=' . $user_id_sanitized . "&lang=" . $_GET['lang']);
            } else {
                if ($_POST['answer'] < 20) {
                    echo '<script type="text/javascript"> $(document).ready(function() {document.getElementById("wrong_location").innerHTML = "<p><b>Wrong input</b>, your submission needs to be at least 20 characters.</p>";});</script>';
                } else {
                    echo '<script type="text/javascript"> $(document).ready(function() {document.getElementById("wrong_location").innerHTML = "<p><b>Wrong input</b>, please fill in the form correctly and provide your location again.</p>";});</script>';
                }
            }
        }
    }
?>

    <!-- SHOW MAP -->

    <link rel="stylesheet" href="https://js.arcgis.com/4.11/esri/themes/light/main.css">
    <script src="https://js.arcgis.com/4.11/"></script>

    <script type="text/javascript">
        var map;
        var search;

        function initialise() {
            require([
                "esri/Map",
                "esri/views/MapView",
                "esri/Graphic",
                "esri/layers/GraphicsLayer",
                "esri/widgets/Search"
            ], function(Map, MapView, Graphic, GraphicsLayer) {

                map = new Map({
                    basemap: "dark-gray-vector"
                });

                var view = new MapView({
                    container: "map_canvas",
                    map: map,
                    center: [-118.80500, 34.02700], // longitude, latitude
                    zoom: 3
                });

                var graphicsLayer = new GraphicsLayer();

            });
        }

        function submitform() {
            document.forms["sp3"].submit();
        }
    </script>

    <body onload="initialise()">

        <div id="map_canvas" aria-label="Map Canvas" style="width:900px; height:600px; display: none;"></div>

        <?php
        if (!isset($form_hide)) {
        ?>
            <form name=sp3 action="map.php?action=insert&course_id=<?php echo addslashes(htmlspecialchars($course_id_sanitized)) ?>&map_id=<?php echo addslashes(htmlspecialchars($map_id_sanitized)) ?>&user_id=<?php echo addslashes(htmlspecialchars($user_id_sanitized)) ?>&lang=<?php echo addslashes(htmlspecialchars($_GET['lang'])) ?>" method="post">

                <?php
                $nameString = "";
                $descriptionString = "";

                $q_map_entry = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "'", __LINE__);

                if ($db->rows($q_map_entry) != 0) {
                    $data_map_entry = $db->assoc($q_map_entry);

                    $q_map_entry = $db->query("SELECT * FROM mooc_map_entry WHERE map_id = '" . addslashes($map_id_sanitized) . "' AND edx_user_id = '" . addslashes($user_id_sanitized) . "'", __LINE__);
                    $data = $db->assoc($q_map_entry);

                    $nameString = (empty($data['name']) ? '' : htmlspecialchars(preg_replace('/^\s+|\n|\r|\s+$/m', '', addslashes($data['name']))));

                    $locationString = htmlspecialchars(preg_replace('/^\s+|\n|\r|\s+$/m', '', addslashes(mask_breaks($data['latitude'])))) . ', ' . htmlspecialchars(preg_replace('/^\s+|\n|\r|\s+$/m', '', addslashes(mask_breaks($data['longitude']))));

                    $descriptionString = htmlspecialchars(preg_replace('/^\s+|\n|\r|\s+$/m', '', addslashes(mask_breaks($data['answer']))));
                    $descriptionString = str_replace('[br]', "\n", $descriptionString);
                }
                ?>

                <!-- Echo HTML and fill in fields -->
                <div>
                    <label for="TUD_name_field">
                        <div class="TUD_title_box">
                            <span role="heading"><?php echo STR_INS_NAME_TITLE; ?></span>
                            <p><?php echo STR_INS_NAME_DESCR; ?></p>
                        </div>
                    </label>
                    <?php echo '<input id="TUD_name_field" type="text" role="textbox" name="name" aria-label="' . STR_INS_NAME_ARIA . '" aria-multiline="false" value="' . htmlspecialchars(stripslashes($nameString)) . '" type="text" />'; ?>
                </div>

                <div>
                    <label for="location">
                        <div class="TUD_title_box">
                            <span role="heading"><?php echo STR_INS_LOCATION_TITLE; ?></span>
                            <p id="location_check_text"><?php echo STR_INS_LOCATION_DESCR; ?></p>
                        </div>
                    </label>
                    <?php echo '<input type="text" role="textbox" aria-label="' . STR_INS_LOCATION_ARIA . '" title="' . STR_INS_LOCATION_ARIA . '" aria-multiline="false" aria-required="true" name="location" id="location" value="' . htmlspecialchars(stripslashes($locationString)) . '"/><div role="status" aria-live="assertive" class="location_check"></div>'; ?>
                    <input type="hidden" name="gps" id="gps" />
                </div>
                <div>

                    <label for="TUD_submission_textarea">
                        <div class="TUD_title_box">
                            <span role="heading"><?php echo STR_INS_SUBM_TITLE; ?></span>
                            <p><?php echo STR_INS_SUBM_DESCR; ?></p>
                            <p role="status" aria-live="off" style="margin-top:-0.5em;" id="TUD_subm_req"></p>
                        </div>
                    </label>

                    <ul class="TUD_text_edit" role="menubar">
                        <?php echo '<a role="menuitem" tabi="0" title="' . STR_INS_CTRL_BOLD . '" aria-label="' . STR_INS_CTRL_BOLD_ARIA . '" aria-pressed="false" onclick="addMarkUp(\'B\')"><li id="TUD_text_bold"><i class="fa fa-bold" aria-hidden="true"></i></li></a>'; ?>
                        <?php echo '<a role="menuitem" tabi="0" title="' . STR_INS_CTRL_ITALIC . '" aria-label="' . STR_INS_CTRL_ITALIC_ARIA . '" aria-pressed="false" onclick="addMarkUp(\'I\')"><li id="TUD_text_italic"><i class="fa fa-italic" aria-hidden="true"></i></li></a>'; ?>
                        <?php echo '<a role="menuitem" tabi="0" title="' . STR_INS_CTRL_UNDERLINE . '" aria-label="' . STR_INS_CTRL_UNDERLINE_ARIA . '" aria-pressed="false" onclick="addMarkUp(\'U\')"><li id="TUD_text_underline"><i class="fa fa-underline" aria-hidden="true"></i></li></a>'; ?>
                        <?php echo '<a role="menuitem" tabi="0" title="' . STR_INS_CTRL_IMG . '" aria-label="' . STR_INS_CTRL_IMG_ARIA . '" aria-haspopup="true" onclick="addMarkUp(\'Image\')"><li role="presentation" id="TUD_text_image"><i class="fa fa-picture-o" aria-hidden="true"></i><input role="textbox" aria-multiline="false" type="url" id="TUD_text_edit_image" aria-label="' . STR_INS_CTRL_IMG_URL . '" placeholder="' . STR_INS_CTRL_IMG_URL . '" onclick="sP(event)"/><input id="TUD_button_edit_image" type="button" aria-pressed="false" aria-label="Confirm" value="' . STR_INS_CTRL_CANCEL . '" onclick="sP(event); addMarkUp(\'ImageDone\');"/></li></a>'; ?>
                        <?php echo '<a role="menuitem" tabi="0" title="' . STR_INS_CTRL_YT . '" aria-label="' . STR_INS_CTRL_YT_ARIA . '" aria-haspopup="true" onclick="addMarkUp(\'Video\')"><li role="presentation" id="TUD_text_video"><i class="fa fa-video-camera" aria-hidden="true"></i><input role="textbox" aria-multiline="false" type="url" id="TUD_text_edit_video" aria-label="' . STR_INS_CTRL_YT_URL . '" placeholder="' . STR_INS_CTRL_YT_URL . '" onclick="sP(event)"/><input id="TUD_button_edit_video" type="button" aria-pressed="false" aria-label="Confirm" value="' . STR_INS_CTRL_CANCEL . '" onclick="sP(event); addMarkUp(\'VideoDone\')"/></li></a>'; ?>
                    </ul>

                    <?php echo '<textarea role="textbox" id="TUD_submission_textarea" name="answer" aria-label="' . STR_INS_SUBM_ARIA . '" aria-multiline="true" aria-required="true" alt="' . STR_INS_SUBM_ALT . '" style="width: 100%; height: 200px; clear:both;">' . htmlspecialchars(stripslashes($descriptionString)) . '</textarea>' ?>
                </div>
                <div class="clearfix" style="padding-top: 10px; text-align:top;">
                    <?php

                    if ($db->rows($q_map_entry) != 0) {

                        $TUD_submit_title = STR_EDIT_SUBMIT;
                        $TUD_submit_description = STR_EDIT_SUBMIT_DESCR;
                        $TUD_cancel_title = STR_EDIT_CANCEL;
                    } else {

                        $TUD_submit_title = STR_INS_SUBMIT;
                        $TUD_submit_description = STR_INS_SUBMIT_DESCR;
                        $TUD_cancel_title = STR_INS_CANCEL;
                    }

                    $TUD_cancel_link = "map.php?action=show&course_id=" . $course_id_sanitized . "&map_id=" . $map_id_sanitized . "&user_id=" . $user_id_sanitized . "&lang=" . $_GET['lang'];

                    echo '<input tabi="0" title="' . $TUD_submit_description . '" aria-label="' . $TUD_submit_description . '" id="TUD_submit" aria-live="assertive" style="font-size: 17px; float:left; margin-right:1em;" type="button" aria-pressed="false" tabi="0" onClick="codeAddress();" value="' . $TUD_submit_title . '"/>';

                    echo '<a href="' . $TUD_cancel_link . '">
                        <input tabi="1" title="' . $TUD_cancel_title . '" aria-label="' . $TUD_cancel_title . '" id="TUD_cancel" style="font-size: 17px; float:left; margin-right:1em;" type="button" tabi="0" value="' . $TUD_cancel_title . '"/>
                        </a>';

                    ?>

                    <span style="float:left; width:calc(100% - 100px); padding:0; margin:0;" id="wrong_location"></span>

                </div>
            </form>

            <script src="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
            <script type="text/javascript">
                function isNumeric(n) {
                    return !isNaN(parseFloat(n)) && isFinite(n);
                }

                //BUTTON CLICK CODE
                function addMarkUp(type) {
                    var targ_markup = "";
                    var submission_obj = 'TUD_submission_textarea';
                    $("#TUD_text_image").removeClass("edit");
                    $("#TUD_text_video").removeClass("edit");
                    $("#TUD_text_youtube").removeClass("edit");
                    switch (type) {
                        case 'B':
                            targ_markup = "[b]  [/b]";
                            insertText(submission_obj, targ_markup, true);
                            countChar();
                            break;
                        case "I":
                            targ_markup = "[i]  [/i]";
                            insertText(submission_obj, targ_markup, true);
                            countChar();
                            break;
                        case "U":
                            targ_markup = "[u]  [/u]";
                            insertText(submission_obj, targ_markup, true);
                            countChar();
                            break;
                        case "Image":
                            $("#TUD_text_image").toggleClass("edit");
                            $("#TUD_text_edit_image").focus();
                            bindFunction('image');
                            break;
                        case "ImageDone":
                            if ($("#TUD_text_edit_image").val() === null || $("#TUD_text_edit_image").val() == "") {
                                $("#TUD_text_image").removeClass("edit");
                            } else {
                                $("#TUD_text_image").removeClass("edit");
                                targ_markup = ['[img=', $('input#TUD_text_edit_image').val(), ']'].join("");
                                insertText(submission_obj, targ_markup, false);
                                $('input#TUD_text_edit_image').val("");
                                $("#TUD_button_edit_image").val("Cancel");
                            }
                            $("#TUD_submission_textarea").focus();
                            countChar();
                            break;
                        case "Video":
                            $("#TUD_text_video").toggleClass("edit");
                            $("#TUD_text_edit_video").focus();
                            bindFunction('video');
                            break;
                        case "VideoDone":
                            var cool = true;
                            if ($("#TUD_text_edit_video").val() === null || $("#TUD_text_edit_video").val() == "") {
                                $("#TUD_text_video").removeClass("edit");
                            } else {

                                var videoLink = $('input#TUD_text_edit_video').val().toString();
                                var videoProvider = "";

                                if (videoLink.indexOf("iframe") !== -1) {
                                    videoLink = videoLink.split('src="').pop().split('"').shift();
                                }

                                var videoID = videoLink.replace(/.*\.be|.*\.com|.*\.nl|.*\.eu|.*\.co\.uk|.*\.fr|.*\.it|.*\.org|.*\.xxx|.*\.gov|.*\.edu|.*\.net|.*\.int|.*\.mil|.*\.info|video|embed|watch\?.*\=|\#.*|\/|\?t.*| /ig, '');

                                if (videoLink.indexOf("vimeo") !== -1 || isNumeric(videoID)) {
                                    videoProvider = "https://player.vimeo.com/video/";
                                } else if (videoLink.indexOf("youtu") !== -1 || (!isNumeric(videoID) && videoID.length == 11)) {
                                    videoProvider = "https://www.youtube.com/embed/";
                                } else {
                                    alert("Can't determine video source. Video may not work properly.\n\nIf you are trying to add a video from a different source rather than YouTube or Vimeo. You can try and add the video by getting the embed code of the video and then looking for a url in the src property of the iframe given.\n\nOr you can upload your video to YouTube or Vimeo. Both websites are guaranteed to work.");
                                    videoID = videoLink;
                                }

                                if (cool) {
                                    targ_markup = ['[video=', videoProvider, videoID, ']'].join("");
                                    insertText(submission_obj, targ_markup, false);
                                } else {
                                    cool = true;
                                }

                                $("#TUD_text_video").removeClass("edit");
                                $('input#TUD_text_edit_video').val("");
                                $("#TUD_button_edit_video").val("Cancel");
                            }

                            $("#TUD_submission_textarea").focus();
                            countChar();
                            break;
                        default:
                            targ_markup = ""
                            break;
                    }
                }

                //GENERIC FUNCTIONS
                function sP(event) {
                    event = event || window.event;
                    event.stopPropagation();
                    return;
                }

                String.prototype.capitalizeFirstLetter = function() {
                    return this.charAt(0).toUpperCase() + this.slice(1);
                }

                var locationStatus;
                var $locationInput = $('input#location');
                var textarea = $('#TUD_submission_textarea');
                var min_num = 20;

                var originalLocation = "";
                var newLocation = "";
                var iconRan = false;
                var TUD_location_timer;

                function codeAddress() {
                    if (!$('#TUD_submit').hasClass("disabled")) {
                        document.getElementById('wrong_location').innerHTML = '';
                        var sAddress = document.getElementById("location").value;

                        if (sAddress != "" && locationStatus > 0) {
                            //Submit form if location exist
                            submitform();
                        } else {
                            //Set text if location is wrong. In theory this will not show up because the button is disabled but it exists as a safety check.
                            document.getElementById('wrong_location').innerHTML = '<p role="status"><b>Location not found</b>, please provide a more specific description of your location.</p>';
                        }
                    } else {
                        if (textarea.val().length < min_num) {
                            textarea.focus();
                        } else {
                            $locationInput.focus();
                        }
                    }
                }

                function locationCheck() {
                    //Reset text and icons
                    iconRan = false;
                    document.getElementById('wrong_location').innerHTML = '';
                    let loc = document.getElementById('location').value;
                    var $locationIcon = $('div.location_check');

                    // NOMINATIM Location Search
                    // This script searches if the locationt that the users inputs exists. Then updates page elements
                    var xmlhttp = new XMLHttpRequest();
                    var url = "https://nominatim.openstreetmap.org/search?q=" + loc + "&format=json&limit=1&zoom=18";

                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            var myArr = JSON.parse(this.responseText);
                            locationStatus = myArr.length;
                            if (locationStatus <= 0) {
                                //Print to console if not found
                                console.log("Could not find place" + myArr.length);

                                //Handle page elements
                                $locationIcon.replaceWith('<div class="location_check"><i class="fa fa-times location_check_icon" style="color:red;" title="Location Not Found" alt="Location Not Found" aria-hidden="true"></i><p>Location Not Found</p></div>');
                                toggleSubmit(textarea, min_num, locationStatus);
                            } else {
                                //Get city name from json file
                                let name = myArr[0].display_name;
                                console.log("Found place: " + name);

                                //Get GPS coordinates and set to hidden element
                                document.getElementById('gps').value = myArr[0].lat + ", " + myArr[0].lon;

                                //Set full completed name as value
                                document.getElementById('location').value = name;

                                //Handle page elements
                                $locationIcon.replaceWith('<div class="location_check"><i class="fa fa-check location_check_icon" style="color:green;" title="Location Found" alt="Location Found" aria-hidden="true"></i><p>Location Found</p></div>');
                                toggleSubmit(textarea, min_num, locationStatus);
                            }
                        }
                    };

                    xmlhttp.open("GET", url, true);
                    xmlhttp.send();
                }


                $('document').ready(function() {

                    var $locationIcon = $('div.location_check');
                    $locationIcon.replaceWith('<div class="location_check"></div>');

                    //TODO: LOOK FOR LOCATION OF USER and reverse geocode this or some bullshit
                    if ($('#location').val() == "" || $('#location').val().length === 0) {
                        console.log("empty location");
                        if (!!navigator.geolocation) {

                            var $locationIcon = $('div.location_check');
                            $locationIcon.replaceWith('<div class="location_check"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw location_check_icon"></i></div>');

                            navigator.geolocation.getCurrentPosition(function(position) {
                                $('#location').val(position.coords.latitude + ", " + position.coords.longitude);
                                locationCheck();
                            }, geoLocationErrorHandling);
                        }
                    } else {
                        console.log("location is filled");
                        locationCheck();
                    }

                    function geoLocationErrorHandling(error) {
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                console.log("User denied the request for Geolocation.");
                                break;
                            case error.POSITION_UNAVAILABLE:
                                console.log("Location information is unavailable.");
                                break;
                            case error.TIMEOUT:
                                console.log("The request to get user location timed out.");
                                break;
                            case error.UNKNOWN_ERROR:
                                console.log("An unknown error occurred.");
                                break;
                        }
                    }

                    $('#TUD_name_field').focus();

                    //IF LOCATION FIELD CHANGES DO LOCATION CHECK
                    $('body').on("change", $locationInput, function() {
                        clearTimeout(TUD_location_timer);

                        locationCheck();
                    });


                    //IF TEXT HAS CHANGED SET NEW LOCATION
                    $locationInput.on("keyup", function() {
                        newLocation = $locationInput.val();

                        if (originalLocation != newLocation) {
                            clearTimeout(TUD_location_timer);

                            TUD_location_timer = setTimeout(locationCheck, 1500);

                            if (!iconRan) {
                                iconRan = true;
                                var $locationIcon = $('div.location_check');
                                $locationIcon.replaceWith('<div class="location_check"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw location_check_icon"></i></div>');
                            }

                            originalLocation = newLocation;
                        }
                    });

                    $('ul.TUD_text_edit').children('a').keypress(function(e) {
                        if (e.which == 13) {
                            $(this).click();
                        }
                    });

                    countChar();
                    toggleSubmit(textarea, min_num, locationStatus);

                    $('#TUD_submission_textarea').on('keyup', function() {

                        countChar();

                    });

                });

                function countChar() {
                    var num = textarea.val().length;

                    if (num < min_num) {

                        if (num == 0) {
                            $('#TUD_subm_req').text($('#hack_span_20a').text() + " " + (min_num - num) + " " + $('#hack_span_20b').text());
                        } else {
                            $('#TUD_subm_req').text($('#hack_span_under_20a').text() + " " + (min_num - num) + " " + ((min_num - num) > 1 ? $('#hack_span_under_20b_p').text() : $('#hack_span_under_20b_s').text()));
                        }
                    } else {
                        $('#TUD_subm_req').text($('#hack_span_0').text());
                    }

                    toggleSubmit(textarea, min_num, locationStatus);
                }

                function toggleSubmit(textarea, min_num, locationStatus) {
                    var num = textarea.val().length;

                    if (locationStatus > 0 && num >= min_num) {
                        if ($('#TUD_submit').hasClass("disabled")) {
                            $('#TUD_submit').removeClass("disabled");
                        };
                    } else {
                        if (!$('#TUD_submit').hasClass("disabled")) {
                            $('#TUD_submit').addClass("disabled");
                        };
                    }
                }

                function bindFunction(name, event) {
                    var ta_name = '#TUD_text_edit_' + String(name),
                        b_name = '#TUD_button_edit_' + String(name);
                    $(ta_name).bind('input propertychange', function() {
                        if (this.value.length > 0) {
                            $(b_name).val($('#hack_span_done').text());
                        } else {
                            $(b_name).val($('#hack_span_cancel').text());
                        }
                    });

                    var function_name = name.capitalizeFirstLetter() + 'Done';

                    $(ta_name).on('keydown', function(event) {
                        if (event.which == 13) {
                            addMarkUp(function_name);
                        }
                    });
                }

                //String back to editable code
                function descrToEditable(str) {
                    var str = str.replaceAll("<b>", "[b]").replaceAll("</b>", "[/b]");

                    return str;
                }

                //TEXT MANIPULATION CODE
                function insertText(obj, ctext, select) {
                    var element = document.getElementById(obj);
                    var text = element.value;
                    var caretPos = getCaretPosition(element);
                    var textBefore = text.substring(0, caretPos),
                        textAfter = text.substring(caretPos, text.length);

                    var selectedtext = getSelectionText(element);

                    if (selectedtext != "" && select) {
                        var ctext = ctext.split("  ");
                        var ctext = ctext[0] + selectedtext + ctext[1];
                        element.value = textBefore + ctext + textAfter.replace(selectedtext, "");
                    } else {
                        if (!checkBeforeCaret(obj, ' ') && select) {
                            ctext = [" ", ctext].join("");
                        }
                        if (!checkBeforeCaret(obj, "\n") && !select) {
                            ctext = ["\n", ctext].join("");
                        }
                        element.value = textBefore + ctext + textAfter;
                    }

                    if (select) {
                        selectTextRange(obj, ctext, caretPos);
                    } else {
                        moveCaretTo(obj, caretPos + ctext.length);
                    }
                }

                function getSelectionText(activeEl) {
                    var text = "";
                    var activeElTagName = activeEl ? activeEl.tagName.toLowerCase() : null;
                    if (
                        (activeElTagName == "textarea") || (activeElTagName == "input" &&
                            /^(?:text|search|password|tel|url)$/i.test(activeEl.type)) &&
                        (typeof activeEl.selectionStart == "number")
                    ) {
                        text = activeEl.value.slice(activeEl.selectionStart, activeEl.selectionEnd);
                    } else if (window.getSelection) {
                        text = window.getSelection().toString();
                    }
                    return text;
                }

                function selectTextRange(obj, txt, CaretPos) {
                    var txt = txt.replace(/\[(.*?)\]/ig, '');
                    if (txt.charAt(0) === " ") {
                        var txt = txt.substring(1);
                    }

                    var input = document.getElementById(obj);
                    var text = input.value;
                    input.focus();
                    input.setSelectionRange(text.indexOf(String(txt), CaretPos), text.indexOf(String(txt), CaretPos) + txt.length);
                }

                function moveCaretTo(el, caretPos) {
                    var el = document.getElementById(el);
                    if (typeof el.selectionStart == "number") {
                        el.selectionStart = el.selectionEnd = caretPos;
                    } else if (typeof el.createTextRange != "undefined") {
                        el.focus();
                        var range = el.createTextRange();
                        range.collapse(false);
                        range.select();
                    }
                }

                function returnWord(text, caretPos) {
                    var i = text.indexOf(caretPos);
                    var preText = text.substring(0, caretPos);
                    if (preText.indexOf(" ") > 0) {
                        preText = preText.replace(/ /gi, "§ ");
                        var words = preText.split("§");
                        return words[words.length - 1];
                    } else {
                        return preText;
                    }
                }

                function checkBeforeCaret(obj, ctext) {
                    var text = document.getElementById(obj);
                    var caretPos = getCaretPosition(text);
                    var word = returnWord(text.value, caretPos);
                    if (String(word) === String(ctext) || String(word) === "" || String(word.substring(word.length - 1)) === "\n") {
                        return true;
                    } else {
                        return false;
                    }
                }

                function getCaretPosition(ctrl) {
                    var CaretPos = 0; // IE Support
                    if (document.selection) {
                        ctrl.focus();
                        var Sel = document.selection.createRange();
                        Sel.moveStart('character', -ctrl.value.length);
                        CaretPos = Sel.text.length;
                    }
                    // Firefox support
                    else if (ctrl.selectionStart || ctrl.selectionStart == '0')
                        CaretPos = ctrl.selectionStart;

                    return (CaretPos);
                }
            </script>
        <?php
        }

        ?>
    </body>
<?php
}

ob_end_flush();
?>
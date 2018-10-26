<?php
// Dit is het Engelstalige bestand voor uw blok. Als u geen Engelse spreker bent, kunt u 'en'
// vervangen door uw juiste taalcode. Alle taalbestanden voor blokken gaan onder de / lang-submap
// van de installatiemap van het blok.
// Moodle 2.0 en hoger vereist een naam voor onze plug-in die wordt weergegeven op de upgradepagina.
// We stellen deze waarde in, samen met de mogelijkheden die we hebben gecreëerd en eventuele andere
// taalstrings die we binnen het blok willen gebruiken, in een taalpakket zoals eerder genoemd
// (hetzelfde bestand waarin we onze reeks plaatsen voor de plugin-titel).

// De mogelijkheden die hierboven zijn toegevoegd, hebben beschrijvingen nodig voor pagina's waarop
// functies kunnen worden ingesteld. Deze moeten ook aan het taalbestand worden toegevoegd.

/**
 * Version details
 *
 * @package    block_multi_cohorts
 * @copyright  K.V. Moret <k.moret@agriholland.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
global $DB, $OUTPUT, $PAGE;
$selected_cor = '';
$selected_coh = '';
$selected_gro = '';
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'multi_cohorts_sync', $courseid);
}
require_login($course);
$PAGE->set_url('/blocks/multi_cohorts_sync/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('linkname', 'block_multi_cohorts'));
$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_multi_cohorts'));
$editurl = new moodle_url('/blocks/multi_cohorts_sync/view.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('addpage', 'block_multi_cohorts'), $editurl);
$editnode->make_active();
echo $OUTPUT->header();
echo "<div class='block-left'><h3>".get_string('selectcohort', 'block_multi_cohorts').":</h3>";
$sql = "SELECT id, name
        FROM {cohort}";
$result = $DB->get_recordset_sql($sql);
$selectcohort = "<div class='getCohorts'><form action='#' method='post'><select name='cohort'>";
$selectcohort .= "<option value=''>".get_string('selectcohort', 'block_multi_cohorts')."</option>";
foreach ($result as $record) {
    $selectcohort .= "<option value='$record->id|$record->name'>$record->name</option>";
}
$selectcohort .= "</select></div>";
echo $selectcohort;
echo "<br>";
echo "<h3>".get_string('selectcourse', 'block_multi_cohorts').":</h3>";
$sql = "SELECT id, fullname
        FROM {course}";
$result = $DB->get_recordset_sql($sql);
$totalrecords = $DB->count_records('course');
$height = 21.25 * $totalrecords;
$selectcourses = "<div class='getCourses'><select name='course[]' multiple='multiple' style='height:$height"."px'>";
foreach ($result as $record) {
    $selectcourses .= "<option value='$record->id|$record->fullname'>$record->fullname</option>";
}
$selectcourses .= "</select></div><br>";
echo $selectcourses;
echo "<h3>".get_string('selectgroup', 'block_multi_cohorts').":</h3><div class='getGroups'><select name='group'>";
$selected_coh = $_POST['cohort'];
$selectgroupes = "<option value='nogroup'>".get_string('nogroup', 'block_multi_cohorts')."</option>";
$selectgroupes .= "<option value='newgroup'>".get_string('newgroup', 'block_multi_cohorts')."</option>";
$selectgroupes .= "</select></div><br>";
echo $selectgroupes;
echo "<input type='submit' name='submit' value='".get_string('submit', 'block_multi_cohorts')."' /></form></div>";
echo "<div class='block-right'>";
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
if(isset($_POST['submit'])){
  $selected_coh = $_POST['cohort'];
  $selected_coh = explode('|', $selected_coh);
  $selected_cor = $_POST['course'];
  $selected_gro = $_POST['group'];
    if($selected_coh == ""){
      echo get_string('nocohort', 'block_multi_cohorts');
    }else {
        if (!isset($selected_cor)){
          echo get_string('nocourse', 'block_multi_cohorts');
        }else {
          echo get_string('cohort', 'block_multi_cohorts')." <b>" .$selected_coh[1]."</b> ".get_string('synchronized', 'block_multi_cohorts').":<br><br>";
          foreach ($selected_cor as $course) {
              $course = explode('|', $course);
              echo "- <a href='$actual_link"."/enrol/instances.php?id=".$course[0]."' target='_blank'>".$course[1]."</a><br>";
              $coursenumber[] = $course[0];
           }
           $records = array();
           $j = 1;
           foreach ($selected_cor as $course) {
             //echo "<br> ronde ".$j;
             $course = explode('|', $course);
             if($selected_gro == 'newgroup'){
               //echo "<br> Newgroup";
               $groupadd = get_string('groupname', 'block_multi_cohorts');
               $selected_gro_name = $selected_coh[1].$groupadd;
               $i = 1;
               $records = array();
               $coursesid = array();
               foreach ($coursenumber as $coursenum) {
                 if(!$DB->record_exists('groups', array('courseid'=>$coursenum,'name'=>$selected_gro_name))){
                   $record = '$record'.$i;
                   $record = new stdClass();
                   $record->courseid = $coursenum;
                   $record->name = $selected_gro_name;
                   $record->timecreated = time();
                   $record->timemodified = time();
                   $records[] = $record;
                   $coursesid[] = $coursenum;
                   $i++;
                 }
               }
               if(isset($records)){
                 $DB->insert_records('groups', $records);
               }
                 $result_coh_members = $DB->get_records_sql('SELECT userid FROM {cohort_members} WHERE cohortid =?', array($selected_coh[0]));
                 //echo "<br> courseid ".$course[0]." / ".$selected_gro_name;
                 $result_gro_id = $DB->get_records_sql('SELECT id FROM {groups} WHERE courseid =? AND name=?', array($course[0], $selected_gro_name));
                 foreach ($result_gro_id as $gro_id){
                   $groupid = $gro_id->id;
                 }
                 //echo "<br> groupid: ".$groupid;
                 $i = 1;
                 $records = array();
                 foreach ($result_coh_members as $coh_members) {
                   //echo "<br> members: ".$coh_members->userid;
                   $record = '$record'.$i;
                   $record = new stdClass();
                   $record->groupid = $groupid;
                   $record->userid = $coh_members->userid;
                   $record->timeadded = time();
                   $record->component = 'enrol_cohort';
                   $records[] = $record;
                   $i++;
                 }
                 if(isset($records)){
                   $DB->insert_records('groups_members', $records);
                 }
             $result = $DB->get_records_sql('SELECT id FROM {groups} WHERE courseid = ? AND name =?', array($course[0],$selected_gro_name));
               foreach ($result as $groupid){
                 $selected_gro_id = $groupid->id;
               }
             }else {
               $selected_gro_id = '0';
               $selected_gro_name = get_string('nogroup', 'block_multi_cohorts');
             }
             //echo "<br> ID: ".$selected_gro_id;
             //echo "<br> ID: ".$selected_coh[0];
             //echo "<br> ID: ".$course[0];
             $i = 1;
             $records = array();
             if(!$DB->record_exists('enrol', array('courseid'=>$course[0],'customint1'=>$selected_coh[0]))){
                    $record = '$record'.$i;
                    $record = new stdClass();
                    $record->enrol = 'cohort';
                    $record->status = '0';
                    $record->courseid = $course[0];
                    $record->roleid = '5';
                    $record->customint1 = $selected_coh[0];
                    $record->customint2 = $selected_gro_id;
                    $record->timecreated = time();
                    $record->timemodified = time();
                    $records[] = $record;
                    $i++;
             }
             else {
               $timecreated = time();
               $timemodified = time();
               $DB->execute('UPDATE {enrol} SET customint2 =?, timecreated =?, timemodified =? WHERE courseid =? AND customint1 =?', array($selected_gro_id, $timecreated, $timemodified, $course[0], $selected_coh[0]));
               if($selected_gro_id == '0'){
                 $groupremove = get_string('groupname', 'block_multi_cohorts');
                 $selected_gro_remo = $selected_coh[1].$groupremove;
                 $result = $DB->get_records_sql('SELECT id FROM {groups} WHERE courseid = ? AND name =?', array($course[0],$selected_gro_remo));
                   foreach ($result as $groupid){
                     $selected_gro_id = $groupid->id;
                   }
                 $DB->delete_records('groups', array('courseid' => $course[0], 'name' => $selected_gro_remo));
                 $DB->delete_records('groups_members', array('groupid' => $selected_gro_id));
               }
             }
             if(isset($records)){
               $DB->insert_records('enrol', $records);
             }
             $result_enr_id = $DB->get_records_sql('SELECT id FROM {enrol} WHERE courseid = ? AND customint1 = ?', array($course[0],$selected_coh[0]));
             foreach ($result_enr_id as $enrolid){
               $enrolid = $enrolid->id;
             }
             $result_coh_userid = $DB->get_records_sql('SELECT userid FROM {cohort_members} WHERE cohortid =?', array($selected_coh[0]));
             $i = 1;
             $records = array();
             foreach ($result_coh_userid as $userid) {
               if(!$DB->record_exists('user_enrolments', array('enrolid'=>$enrolid,'userid'=>$userid->userid))){
               $record = '$record'.$i;
               $record = new stdClass();
               $record->status = '0';
               $record->enrolid = $enrolid;
               $record->userid = $userid->userid;
               $record->timestart = '0';
               $record->timeend = '0';
               $record->modifierid = '2';
               $record->timecreated = time();
               $record->timemodified = time();
               $records[] = $record;
               $i++;
               }
             }
             if(isset($records)){
               $DB->insert_records('user_enrolments', $records);
             }
             $j++;
           }
          echo "<br>".get_string('groupadded', 'block_multi_cohorts').":<br><br>- <b>".$selected_gro_name."</b>";
        }
    }
}
echo "</div>";
echo $OUTPUT->footer();
?>

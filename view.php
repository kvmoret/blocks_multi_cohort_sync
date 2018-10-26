<?php

require_once('../../config.php');

global $DB, $OUTPUT, $PAGE;

$selected_cor = '';
$selected_coh = '';
$selected_gro = '';

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);

$blockid = required_param('blockid', PARAM_INT);

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_multi_cohorts', $courseid);
}

require_login($course);

$PAGE->set_url('/blocks/multi_cohorts/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('linkname', 'block_multi_cohorts'));

$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_multi_cohorts'));
$editurl = new moodle_url('/blocks/multi_cohorts/view.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
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
              if($selected_gro == 'newgroup'){
                $groupadd = get_string('groupname', 'block_multi_cohorts');
                $selected_gro = $selected_coh[1].$groupadd;
                $i = 1;
                $j = 1;
                $records = array();
                $records2 = array();
                $coursesid = array();
                foreach ($coursenumber as $coursenum) {
                  if(!$DB->record_exists('groups', array('courseid'=>$coursenum,'name'=>$selected_gro))){
                    echo "<br>TEST".$coursenum." / ".$selected_coh[0];
                    if(!$DB->record_exists('enrol', array('enrol'=>'cohort','courseid'=>$coursenum,'customint1'=>$selected_coh[0]))){
                      //$record = '$record'.$i;
                      $record = new stdClass();
                      $record->courseid = $coursenum;
                      $record->name = $selected_gro;
                      $record->timecreated = time();
                      $record->timemodified = time();
                      //$records[] = $record;
                      $coursesid[] = $coursenum;
                      $DB->insert_record('groups', $record);
                      //$i++;
                    }else{
                      echo "<br>EXCIST!!! ".$selected_gro." / ".$coursenum;
                      $records_id = $DB->get_records_sql('SELECT id FROM {groups} WHERE courseid =? AND name =?', array($coursenum,$selected_gro));

                      foreach ($records_id as $custom_id){
                        $customint2 = $custom_id->id;
                        echo "<br>EXCIST!!! ".$customint2;
                      }
                      $timecreated = time();
                      $timemodified = time();
                      //echo "<br>EXCIST!!! ".$customint2[0];
                      //$coursesid[] = $coursenum;
                      if(!isset($customint2)){
                        $customint2 = '1';
                      }
                      $DB->execute('UPDATE {enrol} SET customint2 =?, timecreated =?, timemodified =? WHERE courseid =? AND customint1 =?', array($customint2, $timecreated, $timemodified, '57', '11'));
                    }
                  }
                }
                if(isset($records)){
                  //$DB->insert_records('groups', $records);
                }
                $result_coh_members = $DB->get_records_sql('SELECT userid FROM {cohort_members} WHERE cohortid =?', array($selected_coh[0]));
                foreach ($coursesid as $coursesid) {
                  //echo "<br>Courseid: ".$coursesid;
                  $result_gro_id = $DB->get_records_sql('SELECT id FROM {groups} WHERE courseid =? AND name =?', array($coursesid,$selected_gro));
                  foreach ($result_gro_id as $result_gro_id) {
                    $groupid = $result_gro_id->id;
                    //echo "<br>Groupid: ".$groupid;
                  }
                  $i = 1;
                  $records = array();
                  foreach ($result_coh_members as $userid) {
                    //echo "<br> Userid: ".$userid->userid;
                    $record = '$record'.$i;
                    $record = new stdClass();
                    $record->groupid = $groupid;
                    $record->userid = $userid->userid;
                    $record->timeadded = time();
                    $record->component = 'enrol_cohort';
                    $records[] = $record;
                    $i++;
                  }
                  if(isset($records)){
                    $DB->insert_records('groups_members', $records);
                  }
                }
              }
          $records = array();
          $user_enrol_courses = array();
          $i = 1;
          foreach ($selected_cor as $course) {
            $course = explode('|', $course);
            if($selected_gro != 'nogroup'){
            $result = $DB->get_records_sql('SELECT id FROM {groups} WHERE courseid = ? AND name =?', array($course[0],$selected_gro));
              foreach ($result as $groupid){
                $selected_gro_id = $groupid->id;
              }
            }else {
              $selected_gro_id = '0';
            }
            if(!$DB->record_exists('enrol', array('courseid'=>$course[0],'customint1'=>$selected_coh[0],'customint2'=>$selected_gro_id))){
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
                   // user_enrollments
                   $user_enrol_courses[] = $course[0];
                   $i++;
            }
          }
          if(isset($records)){
            $DB->insert_records('enrol', $records);
          }
            $result = $DB->get_records_sql('SELECT userid FROM {cohort_members} WHERE cohortid =?', array($selected_coh[0]));
            foreach ($result as $userid) {
              //echo "<br>userid = ".$userid->userid;
              //echo "<br>arraylength = ".sizeof($user_enrol_courses);
              $i = 1;
              $records = array();
              foreach ($user_enrol_courses as $couseid) {
                $user = $userid->userid;
                $tmp = $DB->get_records_sql('SELECT id FROM {enrol} WHERE courseid = ? AND customint1 = ?', array($couseid,$selected_coh[0]));
                foreach ($tmp as $enrolid){
                  $enrolid = $enrolid->id;
                }
                $record = '$record'.$i;
                $record = new stdClass();
                $record->status = '0';
                $record->enrolid = $enrolid;
                $record->userid = $user;
                $record->timestart = '0';
                $record->timeend = '0';
                $record->modifierid = '2';
                $record->timecreated = time();
                $record->timemodified = time();
                $records[] = $record;
                $i++;
              }
              if(isset($records)){
                $DB->insert_records('user_enrolments', $records);
              }
            }
            if ($selected_gro == 'nogroup'){
              $selected_gro = get_string('nogroup', 'block_multi_cohorts');
            }
            echo "<br>".get_string('groupadded', 'block_multi_cohorts').":<br><br>- <b>".$selected_gro."</b>";
        }
    }
}
echo "</div>";

echo $OUTPUT->footer();

?>

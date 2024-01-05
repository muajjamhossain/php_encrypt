<?php

use Illuminate\Support\Facades\DB;

use App\OutgoingEMAIL;
use App\SMSEmail;
use App\Reference;

define('PAGINATION_NUMBER', 15);
define('CONFIRMATION', serialize(array("Yes" => "Yes", "No" => "No")));
define('DISABLEENABLE', serialize(array("Enable" => "Enable", "Disable" => "Disable")));
define('ADDORSUBTRACT', serialize(array("Add" => "Add", "Subtract" => "Subtract")));
define('USERROLE', serialize(array("1" => "Admin", "2" => "Executive")));
define('PRIORITY', serialize(array("Normal" => "Normal", "Medium" => "Medium", "High" => "High")));
define('ISSUEFROM', serialize(array("complaint" => "Complaint", "wform" => "Service Request")));
define('TARATYPE', serialize(array("1" => "TARA", "0" => "Non TARA")));


define('MEMOLIST', serialize(array(
    "Card account Name updated through API from CSRCMS" => "Card account Name updated through API from CSRCMS",
    "Card account Title updated  through API from CSRCMS" => "Card account Title updated  through API from CSRCMS",
    "CIF number updated through API from CSRCMS" => "CIF number updated through API from CSRCMS",
    "Company address updated  through API from CSRCMS" => "Company address updated  through API from CSRCMS",
    "Credit limit changed through API from CSRCMS" => "Credit limit changed through API from CSRCMS",
    "Credit Shield Enrollment/De Enrollment through API from CSRCMS" => "Credit Shield Enrollment/De Enrollment through API from CSRCMS",
    "Date Of Birth changed  through API from CSRCMS" => "Date Of Birth changed  through API from CSRCMS",
    "Email address updated  through API from CSRCMS" => "Email address updated  through API from CSRCMS",
    "Father\'s name updated through API from CSRCMS" => "Father\'s name updated through API from CSRCMS",
    "Home address  updated  through API from CSRCMS" => "Home address  updated  through API from CSRCMS",
    "Mobile number  updated  through API from CSRCMS" => "Mobile number  updated  through API from CSRCMS",
    "Mother\'s name updated through API from CSRCMS" => "Mother\'s name updated through API from CSRCMS",
    "NID number updated through API from CSRCMS" => "NID number updated through API from CSRCMS",
    "Permanent Address updated through API from CSRCMS" => "Permanent Address updated through API from CSRCMS",
    "Priority Pass number updated through API from CSRCMS" => "Priority Pass number updated through API from CSRCMS",
    "SMS alert activated/de-activated  through API from CSRCMS" => "SMS alert activated/de-activated  through API from CSRCMS",
    "Spouse name updated through API from CSRCMS" => "Spouse name updated through API from CSRCMS",
    "TIN number updated through API from CSRCMS" => "TIN number updated through API from CSRCMS",
    "Title change through API from CSRCMS" => "Title change through API from CSRCMS",
    "Travel Quota Limit updated through API from CSRCMS" => "Travel Quota Limit updated through API from CSRCMS",
    "Other" => "Other"
)));


define('SALESTYPE', serialize(array("1" => "Distributor", "2" => "Customer")));


function userIdPadLeftWith0($input, $digit, $padString)
{
    return str_pad($input, $digit, $padString, STR_PAD_LEFT);
}

function userIdPadRightWith0($input, $digit, $padString)
{
    return str_pad($input, $digit, $padString, STR_PAD_RIGHT);
}

function pr($data = array())
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

function prd($data = array())
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    die;
}

function cvEnToBn($input)
{
    $english = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, "am", "pm", "AM", "PM", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December", "Jan", "Feb", "March", "April", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Year", "Month", "Day");
    $bangla = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০', 'পূর্বাহ্ন', 'অপরাহ্ন', 'পূর্বাহ্ন', 'অপরাহ্ন', 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'বছর', 'মাস', 'দিন');
    $converted = str_replace($english, $bangla, $input);
    return $converted;
}

function validDate($date)
{

    if (empty($date)) {
        return false;
    }
    $temp = $date;
    $hypen = strpos($date, '-');
    $slash = strpos($date, '/');

    if (!$hypen && !$slash) {
        $temp = date('Y-m-d', $date);
    }

    if ($temp == "1970-01-01" || $temp == "0000-00-00" || $temp == "01-01-1970" || $temp == "00-00-0000" || $temp == '0') {
        return false;
    }

    return true;
}

function xss_cleaner($str, $is_image = FALSE)
{
    // Is the string an array?
    if (is_array($str)) {

        foreach ($str as $key => $value) {
            $str[$key] = xss_cleaner($value);
        }
        /*
        // each() function is depricated thats why it comment out
        while (list($key) = each($str))
        {
            $str[$key] = xss_cleaner($str[$key]);
        }
        */
        return $str;
    }

    $str = strip_tags($str);
    $str = str_replace('<!--', '', $str);
    $str = trim($str);
    $str = htmlspecialchars($str);
    $str = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
    $filtered_words = array('alert', 'readfile', 'prompt', 'confirm', 'cmd', 'passthru', 'eval', 'exec', 'expression', 'system', 'fopen', 'fsockopen', 'file_get_contents', 'file', 'unlink', 'javascript');
    $str = str_replace($filtered_words, '', $str);

    return $str;
}

function apiGenerator($data1 = array(), $data2 = array(), $data3 = array())
{
    // Is the string an array?
    if (is_array($data1)) {
        foreach ($data1 as $key => $value) {
            if (isset($data2[$key])) {
                // $value = array_merge($value,$data2[$key]);
                //$value = $data2[$key];
                $value = (!empty($data2[$key])) ? $data2[$key] : " ";
            }
            if (!is_array($value)) {
                if (array_key_exists($value, $data3)) {
                    $value = (!empty($data3[$value])) ? $data3[$value] : "";
                }
            }
            $data1[$key] = apiGenerator($value, $data2, $data3);
        }
        return $data1;
    }
    return $data1;
}

function formatMobileNumber($mnumber)
{
    $tmp = '';
    if (preg_match("/^\+/", trim($mnumber))) {
        $tmp = $mnumber;
    } else if (preg_match("/^00/", trim($mnumber))) {
        $tmp = preg_replace("/^0088/", "+88", $mnumber);
    } else if (preg_match("/^19/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^18/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^17/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^16/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^15/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^14/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^13/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^11/", trim($mnumber))) {
        $tmp = '+880' . $mnumber;
    } else if (preg_match("/^019/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^018/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^017/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^016/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^015/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^014/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^013/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } else if (preg_match("/^011/", trim($mnumber))) {
        $tmp = '+88' . $mnumber;
    } /* else if(preg_match("/^[1-9][0-9]+/", trim($mnumber))) {
            $tmp = '+880'.$mnumber;
        } else if(preg_match("/^[0][1-9][0-9]+/", trim($mnumber))) {
            $tmp = '+88'.$mnumber;
        } */
    else {
        return false;
    }
    $mnumber = $tmp;
    return $mnumber;
}

function user_session_start()
{
    return \App\UserSession::create([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'session_id' => session()->getId()
    ]);
}

function user_session_end()
{
    $model = \App\UserSession::where('user_id', \Illuminate\Support\Facades\Auth::id());
    $rows = $model->get();

    //$req = \Illuminate\Support\Facades\DB::table('sessions')->where('user_id',\Illuminate\Support\Facades\Auth::id())->delete();

    foreach ($rows as $k) {
        @unlink(storage_path('framework/sessions/' . $k->session_id));
    }
    return $model->delete();
}

function dayCounts($endDate, $startDate)
{
    $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
    return $days;
}

function user_password_change()
{
    $row = \App\Setting::first();
    if ($row) {
        $user = \App\User::find(\Illuminate\Support\Facades\Auth::id());
        $days = dayCounts(date('Y-m-d h:m:s'), $user->password_changed_at);

        if ($row->password_change_time < $days) {
            return false;
        } else {
            return true;
        }

    }
    return false;

}

function extract_roles($sel)
{
    if ($sel instanceof \Illuminate\Database\Eloquent\Collection) {
        $sel = $sel->toArray();
    }
    return array_column($sel, 'id');
}

function chk_roles($name, $sel = '', $extract = true)
{
    $str = '';

    if ($sel && $extract) {
        $sel = extract_roles($sel);
    }

    $roles = \App\Role::all();
    foreach ($roles as $v) {
        $checked = ($sel && in_array($v->id, $sel)) ? 'checked="checked"' : '';
        $str .= '<div class="checkbox1"><label><input name="' . $name . '[]" type="checkbox"
                    value="' . $v->id . '" ' . $checked . '>' . $v->name . '</label></div>';
    }

    return $str;
}

function select_role($name, $sel = '', $extract = true)
{
    $role_id = 0;
    if (!empty($name)) {
        foreach ($name as $v) {
            $role_id = $v['id'];
        }
    }
    return $role_id;
}

function extract_units($sel)
{
    if ($sel instanceof \Illuminate\Database\Eloquent\Collection) {
        $sel = $sel->toArray();
    }
    return array_column($sel, 'id');
}

function chk_units($name, $sel = '', $extract = true)
{
    $str = '';

    if ($sel && $extract) {
        $sel = extract_units($sel);
    }

    $units = \App\Unit::all();
    foreach ($units as $v) {
        $checked = ($sel && in_array($v->id, $sel)) ? 'checked="checked"' : '';
        $str .= '<div class="checkbox1"><label><input name="' . $name . '[]" type="checkbox"
                    value="' . $v->id . '" ' . $checked . '>' . $v->name . '</label></div>';
    }

    return $str;
}

function user_unit($group_id)
{

    $row = \App\UserUnit::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();

    if ($row) {
        $unit = \App\Unit::find($row->unit_id);
        return $unit->name;
    }
    return false;
}

function issueWork_flow($ref)
{
    $reference = \App\Reference::where('reference_number', $ref)->first();
    $row = \App\IssueWorkflow::where('issue_id', $reference->issue_id)->first();
    $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)->where('is_touch_point', '<>', 1)->orderby('issue_group_workflow_id', 'desc')->first();
    $unit = \App\UserUnit::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
    $subgroup_info = \App\SubgroupInfo::where('id', $unit->subgroup_info_id)->first();
    if ($unit->subgroup_info_id == $subgroup_info->id) {
        if ($workflow->touch_checker == 1) {
            $close_label = 2;
        } else {
            $close_label = 1;
        }
        return $close_label;
    }
    return false;
}

function user_unit_label()
{
    $unit = \App\UserUnit::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
    return $unit;
}

function user_permission()
{
    $unit = \App\UserUnit::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
    //$workflow = \App\IssueGroupWorkflow::where('group_info_id',$unit->subgroup_info_id)->first();
    $workflow = \Illuminate\Support\Facades\DB::table('subgroup_info')
        ->join('group_info', 'subgroup_info.group_info_id', '=', 'group_info.id')
        ->where('subgroup_info.id', $unit->subgroup_info_id)
        ->first();
    if ($workflow) {
        return $workflow;
    }
    return false;
}

function user_touch_permission()
{
    $unit = \App\UserUnit::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
    $workflow = \Illuminate\Support\Facades\DB::table('subgroup_info')
        ->join('group_info', 'subgroup_info.group_info_id', '=', 'group_info.id')
        ->where('group_info.group_level_id', '=', 1)
        ->where('subgroup_info.id', $unit->subgroup_info_id)
        ->first();
    if ($workflow) {
        return true;
    }
    return false;
}

function getUserGroup($id)
{
    $row = DB::table('user_units')
        ->select('user_units.*', 'subgroup_info.name as subgroup')
        ->join('subgroup_info', 'subgroup_info.id', '=', 'user_units.subgroup_info_id')
        ->where('user_units.user_id', $id)
        ->first();
    if ($row) {
        return $row->subgroup;
    }
    return '';
}

function getUserGroupName($id)
{
    $row = DB::table('user_units')
        ->select('user_units.*', 'group_info.name as groupName')
        ->join('group_info', 'group_info.id', '=', 'user_units.group_info_id')
        ->where('user_units.user_id', $id)
        ->first();
    if ($row) {
        return $row->groupName;
    }
    return '';
}

function getUserDeptName($id)
{
    $row = DB::table('user_units')
        ->select('user_units.*', 'departments.name as deptName')
        ->join('departments', 'departments.id', '=', 'user_units.department_id')
        ->where('user_units.user_id', $id)
        ->first();
    if ($row) {
        return $row->deptName;
    }
    return '';
}

function getUserDivName($id)
{
    $row = DB::table('user_units')
        ->select('user_units.*', 'divisions.name as divName')
        ->join('divisions', 'divisions.id', '=', 'user_units.division_id')
        ->where('user_units.user_id', $id)
        ->first();
    if ($row) {
        return $row->divName;
    }
    return '';
}

function get_question_input_field($key, $mapping, $creation_id)
{
    $districts = DB::table('districts')->get();
    $thanas = DB::table('thanas')->get();
    $input = '';
    switch ($mapping->field_type) {
        case 'address':
            $address_options_array = explode(',', $mapping->options);
            $input = '<input type="hidden" name="input_type"  value="address">';
            $input .= '<input type="hidden" name="input[' . $key . '][answer-' . $mapping->ques_id . ']" value="address" placeholder="Enter Answer" class="field input-type">';

            if (in_array("name", $address_options_array)) {
                $input .= '<label>Enter Name</label>';
                $input .= '<input type="text" name="name" placeholder="Enter Name" value="' . get_old_address($mapping, $creation_id)->name . '" class="field name">';
            }


            $input .= '<label>Enter Address</label>';
            $input .= '<textarea name="address" placeholder="Enter Address" class="field addr" >' . get_old_address($mapping, $creation_id)->address . '</textarea>';
            $input .= '<label>Select District</label>';
            $input .= '<select name="district"  placeholder="Enter Address" class="field district">';

            foreach ($districts as $district) {
                if (get_old_district_of_address($mapping, $creation_id) == $district->id) {
                    $input .= '<option selected="selected" value="' . $district->id . '">' . $district->district_name . '</option>';
                } else {
                    $input .= '<option value="' . $district->id . '">' . $district->district_name . '</option>';
                }
            }

            $input .= '</select>';
            $input .= '<label>Select Thana</label>';
            $input .= '<select name="thana" class="field city">';
            foreach ($thanas as $thana) {
                if (get_old_city_of_address($mapping, $creation_id) == $thana->id) {
                    $input .= '<option value="' . $thana->id . '" selected="selected">' . $thana->thana_name . '</option>';
                } else {
                    $input .= '<option value="' . $thana->id . '">' . $thana->thana_name . '</option>';
                }
            }

            $input .= '</select>';

            if (in_array("post_code", $address_options_array)) {
                $input .= '<label>Enter Post Code</label>';
                $input .= '<input type="text" name="post_code" placeholder="Enter Post Code" value="' . get_old_address($mapping, $creation_id)->post_code . '" class="field post_code">';
            }


            break;

        case 'text':
            $input = '<input type="text" name="input[' . $key . '][answer-' . $mapping->ques_id . ']" value="' . get_old_value($mapping, $creation_id) . '" placeholder="Enter Answer" class="field">';
            break;
        case 'radio':
            $options = explode(",", $mapping->options);
            $selected_options = get_old_value($mapping, $creation_id);
            $input .= '<ul>';
            foreach ($options as $k => $option) {
                if ($selected_options) {
                    $selected_as_array = explode(',', $selected_options);
                    if (in_array($option, $selected_as_array)) {
                        $input .= '<li><input type="radio" checked name="input[' . $key . '][answer-' . $mapping->ques_id . ']" id="option-' . $k . '" value="' . $option . '" class="field"><label for="option-' . $k . '">' . $option . '</label></li>';
                    } else {
                        $input .= '<li><input type="radio" name="input[' . $key . '][answer-' . $mapping->ques_id . ']" id="option-' . $k . '" value="' . $option . '" class="field"><label for="option-' . $k . '">' . $option . '</label></li>';
                    }
                } else {
                    $input .= '<li><input type="radio" name="input[' . $key . '][answer-' . $mapping->ques_id . ']" id="option-' . $k . '" value="' . $option . '" class="field"><label for="option-' . $k . '">' . $option . '</label></li>';
                }
            }
            $input .= '</ul>';
            break;
        case 'checkbox':
            $options = explode(",", $mapping->options);
            $input .= '<ul>';
            $selected_options = get_old_value($mapping, $creation_id);
            //dd($selected_options);
            foreach ($options as $k => $option) {
                if ($selected_options) {
                    $selected_as_array = explode(',', $selected_options);
                    if (in_array($option, $selected_as_array)) {
                        $input .= '<li><input type="checkbox" checked name="input[' . $key . '][answer-' . $mapping->ques_id . '][' . $k . ']" id="option-' . $k . '" value="' . $option . '" class="field"><label for="option-' . $k . '">' . $option . '</label></li>';
                    } else {
                        $input .= '<li><input type="checkbox" name="input[' . $key . '][answer-' . $mapping->ques_id . '][' . $k . ']" id="option-' . $k . '" value="' . $option . '" class="field"><label for="option-' . $k . '">' . $option . '</label></li>';
                    }
                } else {
                    $input .= '<li><input type="checkbox" name="input[' . $key . '][answer-' . $mapping->ques_id . '][' . $k . ']" id="option-' . $k . '" value="' . $option . '" class="field"><label for="option-' . $k . '">' . $option . '</label></li>';
                }
            }
            $input .= '</ul>';
            break;
        case 'date':
            $input = '<input type="text" name="input[' . $key . '][answer-' . $mapping->ques_id . ']" class="datepicker field" value="' . get_old_value($mapping, $creation_id) . '"  data-date-format="dd/mm/yyyy">';
            break;
        case 'textarea':
            $input = '<textarea name="input[' . $key . '][answer-' . $mapping->ques_id . ']" class="field">' . get_old_value($mapping, $creation_id) . '</textarea>';
            break;
        case 'boolean':
            $input = '<textarea name="input[' . $key . '][answer-' . $mapping->ques_id . ']" class="field"></textarea>';
            break;
        case 'dropdown':
            $options = explode(",", $mapping->options);
            $input = '<select name="input[' . $key . '][answer-' . $mapping->ques_id . ']" class="field">';
            foreach ($options as $k => $option) {
                $input .= '<option value="' . $option . '">' . $option . '</option>';
            }
            $input .= '</select>';
            break;
        default:
            $input = '<input type="text" name="input[' . $key . '][answer-' . $mapping->ques_id . ']"  placeholder="Enter Answer">';
    }
    return $input;
}

function get_issue_type($id)
{
    $row = \App\UnitItem::where('id', $id)->first();
    return $row->issues_from;
}

function ccMasking($number, $maskingCharacter = 'X')
{
    return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 8) . substr($number, -4);
}

function get_service_request_value($issue_id)
{
    $rows = \App\IssueConfig::where('issue_id', $issue_id)->get();
    $a = array();
    foreach ($rows as $row) {
        $a[] = $row->field_name;
    }
    return $a;
}

function get_service_request_label($issue_id)
{
    $rows = \App\IssueConfig::where('issue_id', $issue_id)->get();
    $b = array();
    foreach ($rows as $row) {
        $b[] = $row->label_name;
    }
    return $b;
}

function get_checklist_request_value($issue_id)
{
    $rows = \App\IssueCheckListConfig::where('issue_id', $issue_id)->get();
    $a = array();
    foreach ($rows as $row) {
        $a[] = $row->field_name;
    }
    return $a;
}

function get_checklist_request_label($issue_id)
{
    $rows = \App\IssueCheckListConfig::where('issue_id', $issue_id)->get();
    $b = array();
    foreach ($rows as $row) {
        $b[] = $row->label_name;
    }
    return $b;
}

function issue_breach($reference, $time)
{

    $issue = \Illuminate\Support\Facades\DB::table('reference')
        ->join('issue_workflows', 'reference.issue_id', '=', 'issue_workflows.issue_id')
        ->where('reference_number', $reference)
        ->first();

    $issue_email = \Illuminate\Support\Facades\DB::table('reference')
        ->join('issue_workflows', 'reference.issue_id', '=', 'issue_workflows.issue_id')
        ->join('issue_group_workflows', 'issue_workflows.issue_workflow_id', '=', 'issue_group_workflows.issue_workflow_id')
        ->where('issue_group_workflows.group_info_id', $issue->subgroup_id)
        ->where('reference_number', $reference)
        ->first();
    //dd($issue_email);
    $user_unit = \Illuminate\Support\Facades\DB::table('user_units')
        ->where('subgroup_info_id', $issue->subgroup_id)
        ->where('is_email_allow', 1)
        ->orderBy('unit_id')
        ->get();

    if (!empty($issue)) {

        if ($issue->flow_type == \App\Enum\FlowEnum::FORWARD) {

            $r = \App\Setting::first();
            $compslatime = $issue->complain_sla_time;
            $forwardtime = $r->forward_time;

            //pr($issue);

            //$group =1;

            $issue_breaches = \App\IssueBreach::where('reference_no', $reference)->get();

            //pr($time);
            //prd($compslatime);
            //pr($forwardtime);

            if (!empty($compslatime)) {
                if (count($issue_breaches) < 4) {
                    $additional_time = $compslatime;
                    $sent = 1;
                    if (count($issue_breaches) >= 1) {
                        $sent = 1 + count($issue_breaches);
                        foreach ($issue_breaches as $issue_breach) {
                            $additional_time += $forwardtime;
                        }
                    }

                    if ($time > $additional_time) {
                        $dept = \App\GroupInfo::find($issue->subgroup_id);
                        $div = \App\Department::find($dept->department_id);
                        //prd($div->toArray());
                        $row = \App\IssueBreach::create([
                            'issue_id' => $issue->issue_id,
                            'reference_no' => $reference,
                            'subgroup_id' => $issue->sub_group_info_id,
                            'group_id' => $issue->subgroup_id,
                            'department_id' => $dept->department_id,
                            'division_id' => $div->division_id,
                            'breach_time' => date("Y-m-d h:i:s"),
                            'time' => $additional_time,
                            'is_sent_subgroup' => ($sent == 1) ? 1 : 0,
                            'is_sent_group' => ($sent == 2) ? 1 : 0,
                            'is_sent_department' => ($sent == 3) ? 1 : 0,
                            'is_sent_division' => ($sent == 4) ? 1 : 0,
                        ]);

                        //prd($issue->sub_group_info_id);
                        //prd($sent);

                        if (!empty($issue->sub_group_info_id) && $sent == 1) {
                            sentSubgroup($reference, $issue->sub_group_info_id);
                        } elseif (!empty($issue->subgroup_id) && $sent == 2) {
                            sentGroup($reference, $issue->subgroup_id);
                        } elseif (!empty($dept->department_id) && $sent == 3) {
                            sentDepartment($reference, $dept->department_id);
                        } elseif (!empty($div->division_id) && $sent == 4) {
                            sentDivision($reference, $div->division_id);
                        } else {

                        }
                    }
                }
            }
        } else {
            if(!empty($issue_email)) {
            $total_time = $issue_email->sla_maker + $issue_email->sla_checker;

            $issue_breaches = \App\IssueBreach::where('reference_no', $reference)->where('subgroup_id', $issue_email->sub_group_info_id)->get();

            //pr($total_time);
            //pr($issue_email->sub_group_info_id);
            //prd(count($issue_breaches));

            if (count($issue_breaches) < 4) {
                $additional_time = $total_time;
                $sent = 1;
                if (count($issue_breaches) >= 1) {
                    $sent = 1 + count($issue_breaches);
                    foreach ($issue_breaches as $issue_breach) {
                        $additional_time += $total_time;
                    }
                }

                if ($time > $additional_time) {
                    $dept = \App\GroupInfo::find($issue_email->group_info_id);
                    $div = \App\Department::find($dept->department_id);
                    $row = \App\IssueBreach::create([
                        'issue_id' => $issue->issue_id,
                        'reference_no' => $reference,
                        'subgroup_id' => $issue_email->sub_group_info_id,
                        'group_id' => $issue_email->group_info_id,
                        'department_id' => $dept->department_id,
                        'division_id' => $div->division_id,
                        'breach_time' => date("Y-m-d h:i:s"),
                        'time' => $additional_time,
                        'is_sent_subgroup' => ($sent == 1) ? 1 : 0,
                        'is_sent_group' => ($sent == 2) ? 1 : 0,
                        'is_sent_department' => ($sent == 3) ? 1 : 0,
                        'is_sent_division' => ($sent == 4) ? 1 : 0,
                    ]);

                    //dd($sent);

                    if (!empty($issue_email->sub_group_info_id) && $sent == 1) {
                        sentSubgroup($reference, $issue_email->sub_group_info_id);
                    } elseif (!empty($issue_email->group_info_id) && $sent == 2) {
                        sentGroup($reference, $issue_email->group_info_id);
                    } elseif (!empty($dept->department_id) && $sent == 3) {
                        sentDepartment($reference, $dept->department_id);
                    } elseif (!empty($div->division_id) && $sent == 4) {
                        sentDivision($reference, $div->division_id);
                    } else {

                    }
                }
            }
            }
        }

        return true;
    }
}

function sentSubgroup($reference, $subgroup_id)
{
    // $issue_breaches = \Illuminate\Support\Facades\DB::table('issue_breaches')
    //     ->join('subgroup_info','subgroup_info.group_info_id','=','issue_breaches.subgroup_id')
    // ->join('user_units','user_units.subgroup_info_id','=','subgroup_info.id')
    // ->join('users','users.id','=','user_units.user_id')
    //  ->where('is_email_allow',1)
    //     ->where('issue_breaches.reference_no',$reference)
    // ->get();

    $issue_breaches = \Illuminate\Support\Facades\DB::table('issue_breaches')
        ->where('issue_breaches.reference_no', $reference)
        ->where('issue_breaches.is_sent_subgroup', '=', 1)
        ->where('issue_breaches.subgroup_id', '=', $subgroup_id)->get();
    //pr($reference);
    //pr($department_id);
    $users_email = \Illuminate\Support\Facades\DB::table('users')
        ->join('user_units', 'users.id', '=', 'user_units.user_id')
        ->where('user_units.is_email_allow', '=', 1)
        ->where('user_units.subgroup_info_id', '=', $subgroup_id)->get();
    //prd($issue_breaches);
    if (!empty($issue_breaches)) {

        foreach ($users_email as $usermail) {

            emailOutgoing($reference, $usermail->email);
        }
    }
    return true;
}

function sentGroup($reference, $group_id)
{
    // $issue_breaches = \Illuminate\Support\Facades\DB::table('issue_breaches')
    //     ->Join('user_units','user_units.group_info_id','=','issue_breaches.subgroup_id')
    //     ->join('users','users.id','=','user_units.user_id')
    //     ->where('issue_breaches.reference_no',$reference)
    //     ->where('issue_breaches.is_sent_group','=',1)
    //     ->where('user_units.is_group_info_head','=',1)
    //     ->get();

    $issue_breaches = \Illuminate\Support\Facades\DB::table('issue_breaches')
        ->where('issue_breaches.reference_no', $reference)
        ->where('issue_breaches.is_sent_group', '=', 1)
        ->where('issue_breaches.group_id', '=', $group_id)->get();
    //pr($reference);
    //pr($department_id);
    $users_email = \Illuminate\Support\Facades\DB::table('users')
        ->join('user_units', 'users.id', '=', 'user_units.user_id')
        ->where('user_units.is_group_info_head', '=', 1)
        ->where('user_units.group_info_id', '=', $group_id)->get();

    if (!empty($issue_breaches)) {
        foreach ($users_email as $usermail) {
            emailOutgoing($reference, $usermail->email);
        }
    }
    return true;
}

function sentDepartment($reference, $department_id)
{

    $issue_breaches = \Illuminate\Support\Facades\DB::table('issue_breaches')
        ->where('issue_breaches.reference_no', $reference)
        ->where('issue_breaches.is_sent_department', '=', 1)
        ->where('issue_breaches.department_id', '=', $department_id)->get();
    //pr($reference);
    //pr($department_id);
    $users_email = \Illuminate\Support\Facades\DB::table('users')
        ->join('user_units', 'users.id', '=', 'user_units.user_id')
        ->where('user_units.is_department_head', '=', 1)
        ->where('user_units.department_id', '=', $department_id)->get();
    //dd($users_email);
    if (!empty($issue_breaches)) {
        foreach ($users_email as $usermail) {
            emailOutgoing($reference, $usermail->email);
        }
    }
    return true;
}

function sentDivision($reference, $division_id)
{

    $issue_breaches = \Illuminate\Support\Facades\DB::table('issue_breaches')
        ->where('issue_breaches.reference_no', $reference)
        ->where('issue_breaches.is_sent_division', '=', 1)
        ->where('issue_breaches.division_id', '=', $division_id)->get();
    //pr($reference);
    //pr($department_id);
    $users_email = \Illuminate\Support\Facades\DB::table('users')
        ->join('user_units', 'users.id', '=', 'user_units.user_id')
        ->where('user_units.is_division_head', '=', 1)
        ->where('user_units.division_id', '=', $division_id)->get();
    //dd($users_email);
    if (!empty($issue_breaches)) {
        foreach ($users_email as $usermail) {
            emailOutgoing($reference, $usermail->email);
        }
    }
    return true;
}

function issue_breach_email_send($r, $i, $group)
{

    \App\IssueBreachEmailHistory::create([
        'reference_no' => $r,
        'issue_id' => $i,
        'sent_group' => $group,
        'is_sent' => 0
    ]);
    return true;
}

function emailOutgoing($reference, $email)
{

    $referenceData = \Illuminate\Support\Facades\DB::table('reference')
        ->select('reference.unit_id', 'unit_items.name as issuename', 'subgroup_info.name as subgroupname')
        ->join('unit_items', 'unit_items.id', '=', 'reference.issue_id')
        ->join('subgroup_info', 'subgroup_info.id', '=', 'reference.sub_group_info_id')
        ->where('reference.reference_number', $reference)
        ->get();

    $unit_id = "";
    $issuename = "";
    $subgroupname = "";

    if (!empty($referenceData)) {
        foreach ($referenceData as $refData) {
            $unit_id = ($refData->unit_id == 1) ? " (Maker) " : " (Checker)";
            $issuename = $refData->issuename;
            $subgroupname = $refData->subgroupname . $unit_id;
        }
    }

    //prd($referenceData->toArray());

    $mail = "";
    $smsEmailModel = new SMSEmail();
    $smsEmailData = $smsEmailModel->orderBy('id', 'DESC')->first();
    if (!empty($smsEmailData)) {
        $mail = $smsEmailData['escalation_email'];
    }
    if (!empty($mail)) {
        $mail = str_replace("{reference_no}", $reference, $mail);
        $mail = str_replace("{form_request}", $issuename, $mail);
        $mail = str_replace("{group_name}", $subgroupname, $mail);
    }
    //prd($mail);
    if (!empty($email) && !empty($mail)) {
        $outgoingEMAILModel = new OutgoingEMAIL;
        $outgoingEMAILModel->subject = 'Escalation Email';
        $outgoingEMAILModel->body = $mail;
        $outgoingEMAILModel->savetime = date("Y-m-d H:i:s");
        $outgoingEMAILModel->senttime = '';
        $outgoingEMAILModel->status = '3';
        $outgoingEMAILModel->email_address = $email;
        $outgoingEMAILModel->reference_number = $reference;
        $outgoingEMAILModel->save();
    }
}

function getWorkingDaysRef($startdate = "")
{
    $todayDate = date('Y-m-d');
    $totalWDays = DB::table('working_days')
        ->select(DB::raw('count(dates) AS total_working_days'))
        ->where('dates', '>', $startdate)
        ->where('dates', '<', $todayDate)
        ->first();
    return $totalWDays->total_working_days;
}

function getCommentsInDateTimeRef($reference_no = '')
{
    $commentData = array();
    $commentDataObj = DB::table('comments')
        ->select(
            'comments.id', 'comments.reference_number', 'comments.user_id', 'comments.time', 'comments.issendback', 'comments.created_at', 'comments.group_id'
        )
        ->where('comments.reference_number', 'LIKE', $reference_no)
        ->orderBy('id', 'DESC')
        ->first();
    if ($commentDataObj) {
        $commentData = $commentDataObj;
    }
    $dataForView['last_comment'] = $commentData;

    $commentInData = array();
    $commentInDataObj = DB::table('comments')
        ->select('id', 'reference_number', 'user_id', 'time', 'issendback', 'sendbacksms')
        ->where('comments.reference_number', 'LIKE', $reference_no)
        ->where('isapproved', '1')
        ->orderBy('time', 'DESC')
        ->first();
    if ($commentInDataObj) {
        $commentInData = $commentInDataObj;
    }
    $dataForView['in_date_time'] = $commentInData;

    $dataForView = json_decode(json_encode($dataForView, true), true);

    return $dataForView;
}

function getCommentsActionTimeAndCloseComment($reference_no = '')
{
    $commentData = DB::table('comments')
        ->select(
            'comments.time AS action_time',
            'comments.comments AS close_comments',
            'comments.action'
        )
        ->where('comments.reference_number', 'LIKE', $reference_no)
        ->orderBy('comments.id', 'DESC')
        ->first();
    $dataForView['action_time'] = '';
    $dataForView['close_comments'] = '';
    if (!empty($commentData)) {
        $dataForView['action_time'] = $commentData->action_time;
        if ($commentData->action == 'Close') {
            $dataForView['close_comments'] = $commentData->close_comments;
        }
    }

    return $dataForView;
}

function getAPIUpdateActionComment($reference_no = '')
{
    $commentData = DB::table('comments')
        ->select(
            'comments.comments AS comments',
            'comments.time AS action_time',
            'comments.action'
        )
        ->where('comments.action', 'LIKE', '%API Update%')
        ->where('comments.reference_number', 'LIKE', $reference_no)
        ->orderBy('comments.id', 'DESC')
        ->first();
    $dataForView['comments'] = '';
    $dataForView['action_time'] = '';
    if (!empty($commentData)) {
        $dataForView['comments'] = $commentData->comments;
        $dataForView['action_time'] = $commentData->action_time;
    }
    return $dataForView;
}

function is_priority()
{


    $subgroupList = (!empty(Auth::user()->user_unit)) ? Auth::user()->user_unit->subgroup_info_id : 'N/A';
    $subgroupArr = array();

    $subgroupStr = "";
    $priority = 0;
    if (!empty($subgroupList)) {

        $subgroupArr = explode(',', $subgroupList);
        $subgroup = DB::table('subgroup_info')->select('id', 'name', 'group_info_id')->whereIn('id', $subgroupArr)->pluck('name')->toArray();
        $group = DB::table('subgroup_info')
            ->join('group_info', 'subgroup_info.group_info_id', '=', 'group_info.id')
            ->where('subgroup_info.id', $subgroupList)
            ->first();
        $priority = $group->group_level_id;

    }

    return $priority;
}

function is_sendback($reference_no = '')
{
    $userId = Auth::user()->id;
    $subgroup = DB::table('user_units')->select('id', 'subgroup_info_id')->where('user_id', $userId)->first();
    $subGroupId = (!empty($subgroup->subgroup_info_id)) ? $subgroup->subgroup_info_id : 0;
    $issendback = DB::table('comments')->where('subgroup_id', $subGroupId)->where('issendback', 1)->where('reference_number', $reference_no)->count();
    // prd($issendback);
    return $issendback;
}

function userUnits()
{

    $userUnitList = (!empty(Auth::user()->user_unit)) ? Auth::user()->user_unit->unit_id : 'N/A';
    $subgroupList = (!empty(Auth::user()->user_unit)) ? Auth::user()->user_unit->subgroup_info_id : 'N/A';
    $userUnitArr = array();
    $subgroupArr = array();
    $unitListStr = "";
    $subgroupStr = "";

    if (!empty($userUnitList)) {
        $userUnitArr = explode(',', $userUnitList);

        $subgroupArr = explode(',', $subgroupList);
        $unitList = DB::table('units')->select('id', 'name')->whereIn('id', $userUnitArr)->pluck('id')->toArray();
        $allRole = DB::table('roles')->select('id', 'name')->pluck('name')->toArray();;
        //dd($allRole);
        $subgroup = DB::table('subgroup_info')->select('id', 'name')->whereIn('id', $subgroupArr)->pluck('name')->toArray();
        if (!empty($unitList)) {
            $unitListStr = implode(',', $unitList);
        }
        if (!empty($subgroup)) {
            $subgroupStr = implode(',', $subgroup);
            Session::put('subgroupStr', $subgroupStr . ' [ ' . $unitListStr . ' ]');
        }
    }

    $aaa = "";
    if (!empty($userUnitList)) {
        $aaa = array_intersect($userUnitArr, $unitList);
    } else {
        $aaa = $userUnitList;
    }
    return $aaa;
}

function allUnits()
{
    $all_unit = \App\Unit::pluck('id')->toArray();
    return $all_unit;
}

function mainUnit()
{
    $mainUnit = [1, 2];
    return $mainUnit;
}

function userRoles()
{
    if (empty($_SESSION['user_roles_name'])) {
        $user_id = Auth::user()->id;
        $rolename = DB::table('model_has_roles')->select('model_has_roles.role_id', 'roles.name AS role_name')->leftJoin('roles', 'roles.id', 'model_has_roles.role_id')->where('model_has_roles.user_id', $user_id)->first();

        if (!empty($rolename->role_name)) {
            $_SESSION['user_roles_name'] = $rolename->role_name;
        } else {
            $_SESSION['user_roles_name'] = 'N/A';
        }
    }
    return $_SESSION['user_roles_name'];
}

function isApiPush($issue_id = NULL, $group_id = NULL, $unit_id = NULL)
{
    if ($unit_id == 2) {
        $cifWorkflowExists = DB::table('cif_workflow')->where('cif_workflow.issue_id', $issue_id)->where('cif_workflow.group_info_id', $group_id)->where('cif_workflow.status', 1)->count();
        if ($cifWorkflowExists > 0) {
            return true;
        }
    }
    return false;
}

function array_flatten($array)
{
    if (!is_array($array)) {
        return FALSE;
    }
    $result = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, array_flatten($value));
        } else {
            $result[$key] = $value;
        }
    }
    return $result;
}

function array_flatten_enq($var, $array)
{
    if (!is_array($array)) {
        return FALSE;
    }
    $result = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $arrayList = array_flatten_enq($var, $value);
            foreach ($arrayList as $key2 => $listItem) {
                ++$var;
                $result[$var . $key2] = $listItem;
                // $result[] = $listItem;
            }
        } else {
            $result[$key] = $value;
        }
    }
    return $result;
}

function cifApiNullExtractor($request)
{
    $doc = new DomDocument();
    $doc->loadXML($request);
    $xpath = new DomXPath($doc);
    $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2_$3", $request);
    $xmlArray = simplexml_load_string($xml);
    $xmlArray = json_decode(json_encode($xmlArray), TRUE);
    $xmlArray = array_flatten($xmlArray);

    foreach ($xmlArray as $key => $value) {
        if (str_contains($key, 'password')) {
            // unset($xmlArray[$key]);
        } elseif (preg_match('~[#](.+?)[#]~', $value) || preg_match('~[|](.+?)[|]~', $value)) {
            // unset($xmlArray[$key]);
            //$request = str_replace("<".$key.">".$value."</".$key.">","",$request);
            $key = str_replace('_', ':', $key);
            $nodes = $xpath->query('//' . $key);

            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                if ($node->nodeValue == $value) {
                    $node->parentNode->removeChild($node);
                }
            }
        } elseif (preg_match('#[~](.+?)[~]#', $value)) {
            $key = str_replace('_', ':', $key);

            $nodes = $xpath->query('//' . $key);
            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                if ($node->nodeValue == $value) {
                    // $node->parentNode->removeChild($node);
                    $node->parentNode->parentNode->removeChild($node->parentNode);
                }
            }
        }
    }

    while (($node_list = $xpath->query('//*[not(*) and not(@*) and not(text()[normalize-space()])]')) && $node_list->length) {
        foreach ($node_list as $node) {
            $node->parentNode->removeChild($node);
        }
    }
    /*$nodes = $xpath->query('//text()');
    foreach ($nodes as $node) {
        $node->nodeValue = preg_replace('~\s+~u', '', $node->textContent);
    }*/


    return $doc->saveXML();
}

function cifApiNullExtractorAcc($request, $extraFieldWithIssueConfig)
{
    $doc = new DomDocument();
    $doc->loadXML($request);
    $xpath = new DomXPath($doc);
    $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2_$3", $request);
    $xmlArray = simplexml_load_string($xml);
    $xmlArray = json_decode(json_encode($xmlArray), TRUE);
    $xmlArray = array_flatten($xmlArray);

    $extraFieldWithIssueConfig['name'] = '';
    $extraFieldWithIssueConfig['acctName'] = '';
    $extraFieldWithIssueConfig['acctShortName'] = '';
    $extraFieldWithIssueConfig['schmCode'] = '';
    $extraFieldWithIssueConfig['schmType'] = '';
    $extraFieldWithIssueConfig['acctCurr'] = '';
    $extraFieldWithIssueConfig['branchId'] = '';
    $extraFieldWithIssueConfig['acctStmtMode'] = '';
    $extraFieldWithIssueConfig['startDt'] = '';
    $extraFieldWithIssueConfig['modeOfOperCode'] = '';
    $extraFieldWithIssueConfig['wTaxAmountScopeFlg'] = '';
    $extraFieldWithIssueConfig['atmFlg'] = '';
    $extraFieldWithIssueConfig['cenBkSectorCode'] = '';
    $extraFieldWithIssueConfig['norPsoUnitId'] = '';
    $extraFieldWithIssueConfig['monitoringPsoUnitId'] = '';
    $extraFieldWithIssueConfig['ccCode'] = '';
    $extraFieldWithIssueConfig['tpCashDepTranCnt'] = '';
    $extraFieldWithIssueConfig['tpCashDepTranAmtHigh'] = '';
    $extraFieldWithIssueConfig['tpCashDepTranAmtTot'] = '';
    $extraFieldWithIssueConfig['tpXferDepTranCnt'] = '';
    $extraFieldWithIssueConfig['tpXferDepTranAmtHigh'] = '';
    $extraFieldWithIssueConfig['tpXferDepTranAmtTot'] = '';
    $extraFieldWithIssueConfig['tpTotlDepTranCnt'] = '';
    $extraFieldWithIssueConfig['tpTotlDepTranAmtHigh'] = '';
    $extraFieldWithIssueConfig['tpTotlDepTranAmtTot'] = '';
    $extraFieldWithIssueConfig['tpCashWdrTranCnt'] = '';
    $extraFieldWithIssueConfig['tpCashWdrTranAmtHigh'] = '';
    $extraFieldWithIssueConfig['tpCashWdrTranAmtTot'] = '';
    $extraFieldWithIssueConfig['tpXferWdrTranCnt'] = '';
    $extraFieldWithIssueConfig['tpXferWdrTranAmtHigh'] = '';
    $extraFieldWithIssueConfig['tpXferWdrTranAmtTot'] = '';
    $extraFieldWithIssueConfig['tpTotlWdrTranCnt'] = '';
    $extraFieldWithIssueConfig['tpTotlWdrTranAmtHigh'] = '';
    $extraFieldWithIssueConfig['tpTotlWdrTranAmtTot'] = '';
    // $extraFieldWithIssueConfig['regNum'] = '';
    // $extraFieldWithIssueConfig['nomineeName'] = '';
    // $extraFieldWithIssueConfig['relType'] = '';
    // $extraFieldWithIssueConfig['nomineeTelephoneNum'] = '';
    // $extraFieldWithIssueConfig['nomineeFaxNum'] = '';
    // $extraFieldWithIssueConfig['nomineeTelexNum'] = '';
    // $extraFieldWithIssueConfig['nomineeEmailAddr'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypeAddress1'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypeAddress2'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypeAddress3'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypeCity'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypeStateProv'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypePostalCode'] = '';
    // $extraFieldWithIssueConfig['nomineeAddressTypeCountry'] = '';
    // $extraFieldWithIssueConfig['nomineeAddrType'] = '';
    // $extraFieldWithIssueConfig['nomineeMinorFlg'] = '';
    // $extraFieldWithIssueConfig['nomineeBirthDt'] = '';
    // $extraFieldWithIssueConfig['nomineePercent'] = '';
    // $extraFieldWithIssueConfig['guardianCode'] = '';
    // $extraFieldWithIssueConfig['guardianName'] = '';
    // $extraFieldWithIssueConfig['guardianTelephoneNum'] = '';
    // $extraFieldWithIssueConfig['guardianFaxNum'] = '';
    // $extraFieldWithIssueConfig['guardianTelexNum'] = '';
    // $extraFieldWithIssueConfig['guardianEmailAddr'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypeAddress1'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypeAddress2'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypeAddress3'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypeCity'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypeStateProv'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypePostalCode'] = '';
    // $extraFieldWithIssueConfig['guardianAddressTypeCountry'] = '';
    // $extraFieldWithIssueConfig['guardianAddrType'] = '';

    foreach ($xmlArray as $key => $value) {
        if (str_contains($key, 'password')) {
            // unset($xmlArray[$key]);
        } elseif (preg_match('~[#](.+?)[#]~', $value) || preg_match('~[|](.+?)[|]~', $value)) {
            // unset($xmlArray[$key]);
            //$request = str_replace("<".$key.">".$value."</".$key.">","",$request);
            $key = str_replace('_', ':', $key);
            $tmpkeyarr = explode(':', $key);
            $tmpkey = !empty($tmpkeyarr[1]) ? $tmpkeyarr[1] : '';
            $nodes = $xpath->query('//' . $key);

            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                if ($node->nodeValue == $value) {
                    if (array_key_exists($tmpkey, $extraFieldWithIssueConfig)) {
                        $node->nodeValue = '';
                    } else {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        } elseif (preg_match('#[~](.+?)[~]#', $value)) {
            $key = str_replace('_', ':', $key);
            $tmpkeyarr = explode(':', $key);
            $tmpkey = !empty($tmpkeyarr[1]) ? $tmpkeyarr[1] : '';
            $nodes = $xpath->query('//' . $key);

            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                if ($node->nodeValue == $value) {
                    // $node->parentNode->removeChild($node);
                    //$node->parentNode->parentNode->removeChild($node->parentNode);
                    if (array_key_exists($tmpkey, $extraFieldWithIssueConfig)) {
                        $node->nodeValue = '';
                    } else {
                        $node->parentNode->parentNode->removeChild($node->parentNode);
                    }
                }
            }
        }
    }

    /*while (($node_list = $xpath->query('//*[not(*) and not(@*) and not(text()[normalize-space()])]')) && $node_list->length) {
        foreach ($node_list as $node) {
            $node->parentNode->removeChild($node);
        }
    }*/
    /*$nodes = $xpath->query('//text()');
    foreach ($nodes as $node) {
        $node->nodeValue = preg_replace('~\s+~u', '', $node->textContent);
    }*/


    return $doc->saveXML();
}

function cifApiNodeRemover($request, $addressType)
{
    $doc = new DomDocument();
    $doc->loadXML($request);
    $xpath = new DomXPath($doc);

    /********************
     * v1:homeAddress
     * v1:companyAddress
     * v1:permanentFields
     ***********************/

    $removeNodeArr = array();
    if ($addressType == 'Home~Permanent') {
        $removeNodeArr[] = 'v1:companyAddress';
        $removeNodeArr[] = 'v1:homeAddress';
    } elseif ($addressType == 'Work~Work/Business') {
        $removeNodeArr[] = 'v1:homeAddress';
        $removeNodeArr[] = 'v1:permanentFields';
    } elseif ($addressType == 'PRESENT') {
        $removeNodeArr[] = 'v1:permanentFields';
        $removeNodeArr[] = 'v1:companyAddress';
    } else {
        $removeNodeArr[] = 'v1:homeAddress';
        $removeNodeArr[] = 'v1:companyAddress';
        $removeNodeArr[] = 'v1:permanentFields';
    }

    foreach ($removeNodeArr as $removeNode) {
        $nodes = $xpath->query('//' . $removeNode);
        for ($i = 0; $i < $nodes->length; $i++) {
            $node = $nodes->item($i);
            if ($node->nodeName == $removeNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    return $doc->saveXML();
}

function getBetween($string, $start = "", $end = "")
{
    if (strpos($string, $start)) {
        $startCharCount = strpos($string, $start) + strlen($start);
        $firstSubStr = substr($string, $startCharCount, strlen($string));
        $endCharCount = strpos($firstSubStr, $end);
        if ($endCharCount == 0) {
            $endCharCount = strlen($firstSubStr);
        }
        return substr($firstSubStr, 0, $endCharCount);
    } else {
        return '';
    }
}

function validateDate($date, $format = 'd-m-Y')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function getApiJsonResponse($ref)
{
    $re = [];
    $i = 1;
    $data = DB::table('cif_request_response')
        ->select('json_node')
        ->where('reference_number', $ref)
        ->where('status_code', '000')
        //->where('url', 'like', '%http://bracbank.com/BBLCardDataUpdateES/V1%')
        ->where('type', 2)
        ->get()
        ->toArray();
    foreach ($data as $da) {
        $doc = new DomDocument();
        $doc->loadXML($da->json_node);
        $resp = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $da->json_node);
        $xmlArray = simplexml_load_string($resp);
        $array = json_decode(json_encode($xmlArray), TRUE);
        if (!empty($array['soapenvBody'])) {
            foreach ($array['soapenvBody'] as $key => $value) {
                foreach ($value as $ke => $val) {
                    if (!empty($val['ns1cardDetails'])) {
                        foreach ($val['ns1cardDetails'] as $v) {
                            foreach ($v as $c) {
                                foreach ($c as $d => $e) {
                                    if ($d == 'ns1customerId') {
                                        $re[$i . '. Customer ID'] = $e;
                                    }
                                    if ($d == 'ns1cardNumber') {
                                        $re[$i . '. Card Number'] = $e;
                                    }
                                    if ($d == 'ns1cifNumber') {
                                        $re[$i . '. CIF Number'] = $e;
                                    }
                                }
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($array['soapBody'])) {
            foreach ($array['soapBody'] as $key => $value) {
                foreach ($value as $ke => $val) {
                    if (!empty($val['ns1cardDetails'])) {
                        foreach ($val['ns1cardDetails'] as $v) {
                            foreach ($v as $c) {
                                foreach ($c as $d => $e) {
                                    if ($d == 'ns1customerId') {
                                        $re[$i . '. Customer ID'] = $e;
                                    }
                                    if ($d == 'ns1cardNumber') {
                                        $re[$i . '. Card Number'] = $e;
                                    }
                                    if ($d == 'ns1cifNumber') {
                                        $re[$i . '. CIF Number'] = $e;
                                    }
                                }
                                $i++;
                            }
                        }
                    }
                }
            }
        }
    }
    //prd($re);
    return $re;
}

function arrayNodeSearch($array, $key)
{
    $results = array();
    if (is_array($array)) {
        if (isset($array[$key])) {
            $results = $array[$key];
        }
        foreach ($array as $subarray) {
            $results = array_merge($results, arrayNodeSearch($subarray, $key));
        }
    }
    return $results;
}

function getDMSRemarks($attachment_id, $reference_no = '')
{
    $dmsData = DB::table('dms_request_response')
                ->select(
                    'dms_request_response.created_at as upload_date',
                    'dms_request_response.msg as dms_remark',
                )
                ->where('dms_request_response.reference_number', 'LIKE', $reference_no)
                ->where('dms_request_response.attachment_id', 'LIKE', $attachment_id)
                ->orderBy('dms_request_response.id', 'DESC')
                ->first();
    $dataForView['upload_date'] = '';
    $dataForView['dms_remark'] = '';
    if (!empty($dmsData)) {
        $dataForView['upload_date'] = $dmsData->upload_date;
        $dataForView['dms_remark'] = $dmsData->dms_remark;
    }
    return $dataForView;
}

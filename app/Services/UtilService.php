<?php


namespace App\Services;


use App\AttachmentHistory;
use App\Department;
use App\Enum\RoleEnum;
use App\GroupInfo;
use App\GroupLevel;
use App\SubgroupInfo;
use App\Unit;
use App\Division;
use App\UnitItem;
use App\RequestType;
use App\SalesLead;
use App\Profession;
use App\IssueCategories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

class UtilService
{
    public static function getAllGroups($sel = ''){
        $rows = GroupInfo::where("is_active", '=', '1')->orderBy('name', 'ASC')->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;

    }

    public static function getAllDivisions($sel=''){
        $rows = Division::where("status", '=', '1')->orderBy('name', 'ASC')->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }

    public static function getAllDepartments($sel=''){
        $rows = Department::where("status", '=', '1')->orderBy('name', 'ASC')->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
    public static function getAllSubGroups($sel=''){
        $rows = SubgroupInfo::where("is_active", '=', '1')->orderBy('name', 'ASC')->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
    public static function getAllUnits($sel=''){
        $rows = Unit::where("status", '=', '1')->orderBy('name', 'ASC')->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' checked="checked"' : '';
            $opt .= '<label class="checkbox-inline"><input type="checkbox" name=units[] value="' . $v->id . '"' . $attr . '>' . $v->name. '</label>';
        }
        return $opt;
    }
    public static function getAllUnitOrPermission($sel=array()){
        $rows = Unit::whereNotIn('is_head',[1,2,3])->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = (in_array($v->id, $sel)) ? ' checked' : '';

            $opt .= '<label class="checkbox-inline"><input type="checkbox" name="unit_id[]" value="' . $v->id . '"' .
                $attr . '> ' . $v->name. '</label> &nbsp;&nbsp;&nbsp;';
        }
        return $opt;
    }

    public static function getAllIssues($sel=''){
        $rows = UnitItem::all();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
	 public static function getIssueByID($id=''){
		 $rows = \App\UnitItem::where('id', '=', $id)->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $id) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
    public static function getAllGroupLevels($sel=''){
        $rows = GroupLevel::all();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->group_level_id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->group_level_id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;
    }
    public static function getAllTouchGroupList(){
        $rows = GroupInfo::where('is_active','=',1)->where('group_level_id',1)->get();
        return $rows;
    }
    public static function getAllGroupList(){
        $rows = GroupInfo::where('is_active','=',1)->where('group_level_id','<>',1)->orderBy('name', 'ASC')->get();
        return $rows;
    }
    public static function getAllRequestType(){
        $rows = RequestType::where('status','=',1)->orderBy('id', 'ASC')->get();
        return $rows;
    }
    public static function getAllSalesLead(){
        $rows = SalesLead::where('status','=',1)->orderBy('id', 'ASC')->get();
        return $rows;
    }
    public static function getAllProfession(){
        $rows = Profession::where('status','=',1)->orderBy('id', 'ASC')->get();
        return $rows;
    }
    public static function getAllWform(){
        /*$rows = DB::select('SELECT * FROM unit_items
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )
                JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
                 AND unit_items.status="1" AND issues_from="wform"');*/
        $group_info_id = Auth::user()->user_unit->subgroup_info_id;
        $subgroup_id = SubgroupInfo::find($group_info_id);

        $rows = DB::select('SELECT * FROM unit_items
    JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
    JOIN issue_group_workflows ON issue_workflows.issue_workflow_id=issue_group_workflows.`issue_workflow_id` AND issue_group_workflows.`group_info_id`="'.$subgroup_id->group_info_id.'"
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )

                 AND unit_items.status="1" AND issues_from="wform" ');

        return $rows;
    }
	public static function getAllWformDummy(){
        /*$rows = DB::select('SELECT * FROM unit_items WHERE unit_items.id IN(SELECT issue_id FROM issue_workflows ) JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id AND unit_items.status="1" AND issues_from="wform"');*/

        $group_info_id = Auth::user()->user_unit->subgroup_info_id;
        $subgroup_id = SubgroupInfo::find($group_info_id);

        $rows = DB::select(
         'SELECT * FROM unit_items
          JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
          WHERE
            unit_items.id IN(SELECT issue_id FROM issue_workflows ) AND
            unit_items.status="1" AND
            issues_from="wform" '
        );

        return $rows;
    }

	public static function getAllWformWithProd($prodid){
        /*$rows = DB::select('SELECT * FROM unit_items
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )
                JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
                 AND unit_items.status="1" AND issues_from="wform"');*/
        $group_info_id = Auth::user()->user_unit->subgroup_info_id;
        $subgroup_id = SubgroupInfo::find($group_info_id);

        $rows = DB::select('SELECT * FROM unit_items
    JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
    JOIN issue_group_workflows ON issue_workflows.issue_workflow_id=issue_group_workflows.`issue_workflow_id` AND issue_group_workflows.`group_info_id`="'.$subgroup_id->group_info_id.'"
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )

                 AND unit_items.status="1" AND unit_items.product_type_id = "'.$prodid.'" AND issues_from="wform" order by unit_items.name');

        return $rows;
    }

    public static function getAllComplaint(){
        /*$rows = DB::select('SELECT * FROM unit_items
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )
                JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
                 AND unit_items.status="1" AND issues_from="wform"');*/
        $group_info_id = Auth::user()->user_unit->subgroup_info_id;
        $subgroup_id = SubgroupInfo::find($group_info_id);

        $rows = DB::select('SELECT * FROM unit_items
    JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
    JOIN issue_group_workflows ON issue_workflows.issue_workflow_id=issue_group_workflows.`issue_workflow_id` AND issue_group_workflows.`group_info_id`="'.$subgroup_id->group_info_id.'"
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )

                 AND unit_items.status="1" AND issues_from="complaint" ');

        return $rows;
    }

	public static function getAllComplaintWithProd($prodid){
        /*$rows = DB::select('SELECT * FROM unit_items
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )
                JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
                 AND unit_items.status="1" AND issues_from="wform"');*/
        $group_info_id = Auth::user()->user_unit->subgroup_info_id;
        $subgroup_id = SubgroupInfo::find($group_info_id);

        $rows = DB::select('SELECT * FROM unit_items
    JOIN issue_workflows ON unit_items.id=issue_workflows.issue_id
    JOIN issue_group_workflows ON issue_workflows.issue_workflow_id=issue_group_workflows.`issue_workflow_id` AND issue_group_workflows.`group_info_id`="'.$subgroup_id->group_info_id.'"
                WHERE unit_items.id IN(
                SELECT issue_id FROM issue_workflows
                )

                 AND unit_items.status="1" AND unit_items.product_type_id = "'.$prodid.'" AND issues_from="complaint" ');

        return $rows;
    }

    public static function attachmentCount($reference_number){
        $attachment_history = AttachmentHistory::where('reference_number',$reference_number)->where('user_id',Auth::id())->get();
        if (!empty($attachment_history)){
            return count($attachment_history);
        }
        return 0;
    }

    public static function groupUser($group_id){

        if(Auth::user()->hasRole([RoleEnum::LOGGER,RoleEnum::EXECUTIVE])) {
            $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
            $unit_id = Auth::user()->user_unit->unit_id;
            $group_info_id = SubgroupInfo::find($subgroup_id);

            if($group_id == $group_info_id->group_info_id && $unit_id==1 ){
                return true;
            }
            else return false;
        }
        return false;
    }
    public static function formHistory(){
        $rows = DB::table('form_status')
            ->join('users','form_status.user_id','=','users.id')
            ->leftJoin('comments','form_status.id','=','comments.form_status')
            ->get();
        return $rows;
    }

	public static function getComplaintCategory($product_id = ''){
        $rows = DB::table('issue_categories')
            ->where('issues_from','=','complaint')
            ->where('product_type_id','=',$product_id)
            ->where("status", '=', '1')
			->orderBy('name', 'Asc')
            ->get();
        return $rows;
    }

    public static function getAllComplaintCategory(){
        $rows = DB::table('issue_categories')
            ->where('issues_from','=','complaint')
            ->where("status", '=', '1')
            ->orderBy('name', 'Asc')
            ->get();
        return $rows;
    }

    public static function getServiceCategory($product_id = ''){
        $rows = DB::table('issue_categories')
            ->where('issues_from','=','wform')
            ->where('product_type_id','=',$product_id)
            ->where("status", '=', '1')
            ->orderBy('name', 'Asc')
            ->get();
        return $rows;
    }

    public static function getAllServiceCategory(){
        $rows = DB::table('issue_categories')
            ->where('issues_from','=','wform')
            ->where("status", '=', '1')
            ->orderBy('name', 'Asc')
            ->get();
        return $rows;
    }

    public static function getServiceCategoryByService($ServicesType = ''){
        $rows = DB::table('issue_categories')
            ->where('issues_from','=',$ServicesType)
            ->where("status", '=', '1')
            ->orderBy('name', 'Asc')
            ->get();
        return $rows;
    }

	public static function getAllIssueCategories($sel = ''){
        $rows = IssueCategories::where("status", '=', '1')->orderBy("name","ASC")->get();
        $opt = '';
        foreach ($rows as $v) {
            $attr = ($v->id == $sel) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $v->id . '"' . $attr . '>' . $v->name. '</option>';
        }
        return $opt;

    }

    public function queueDurationCalculator($start_date = "", $end_date = "") {
        $workingDays = array();
        $dataForView = array();
        //pr($start_date);
        if (!empty($end_date)) {
            $workingDayDataObj = DB::table('working_days')->where([
                ['dates','>=',date('Y-m-d',strtotime($start_date))],
                ['dates','<=',date('Y-m-d',strtotime($end_date))],
                ['status',1]])->pluck('dates','dates');

            // $workingDayDataObj = DB::table('working_days')->whereBetween('dates',array($start_date,$end_date))->where([['status',1]])->pluck('dates','dates');
            if (!empty($workingDayDataObj)) {
                $workingDays = $workingDayDataObj->toArray();
            }
        }

        //pr($workingDays);

        $workingHours = DB::table('working_hours')->first();
        $workingHours = json_decode(json_encode($workingHours),true);

        if (!empty($workingHours['office_from'])) {
            $dataForView['office_from'] = $workingHours['office_from'];
            $dataForView['office_from_str'] = substr($dataForView['office_from'], 0,2).':'.substr($dataForView['office_from'], 2,2).':'.substr($dataForView['office_from'], 4,2);
        } else {
            $dataForView['office_from'] = '100000';
            $dataForView['office_from_str'] = '10:00:00';
        }
        if (!empty($workingHours['office_to'])) {
            $dataForView['office_to'] = $workingHours['office_to'];
            $dataForView['office_to_str'] = substr($dataForView['office_to'], 0,2).':'.substr($dataForView['office_to'], 2,2).':'.substr($dataForView['office_to'], 4,2);
        } else {
            $dataForView['office_to'] = '180000';
            $dataForView['office_to_str'] = '18:00:00';
        }

        $startDayHour = 0;
        $startDayMinutes = 0;
        $startDaySeconds = 0;

        $endDayHour = 0;
        $endDayMinutes = 0;
        $endDaySeconds = 0;


        $startDate = Carbon::parse($start_date)->format('Y-m-d');
        $startTime = Carbon::parse($start_date)->format('H:i:s');
        $end_date1 = Carbon::parse($end_date)->format('Y-m-d');
        $startDateTime = Carbon::parse($start_date)->format('Y-m-d H:i:s');
        //echo "SELECT count(dates) AS total_working_days FROM working_days where dates > '{$start_date}' and dates < '{$end_date1}'";
        $totalWorkingDaysQueue = array();
        $totalWorkingDaysQueueObj = DB::select("SELECT count(dates) AS total_working_days FROM working_days where dates > '{$start_date}' and dates < '{$end_date1}'");
        //pr($totalWorkingDaysQueueObj);
        $totalWorkingDaysOnThisReq = (!empty($totalWorkingDaysQueueObj[0])) ? $totalWorkingDaysQueueObj[0]->total_working_days  : 0 ;
        $totalWorkingHoursOnThisReq = (!empty($totalWorkingDaysOnThisReq)) ? $totalWorkingDaysOnThisReq * 8 : 0;
        //echo $totalWorkingDaysOnThisReq;
        $startDateForCalc = str_replace('-', '', $startDate);
        $startDateTimeNumb = str_replace("-", "", str_replace(" ", "", str_replace(":", "", $startDateTime)));


        $startDateForCalcMin = $startDateForCalc.$dataForView['office_from'];
        $startDateForCalcMax = $startDateForCalc.$dataForView['office_to'];


        date_default_timezone_set('Asia/Dhaka');
        $endDateCalc = date("Ymd",strtotime($end_date));
        $endDateTimeCalc = date("YmdHis",strtotime($end_date));
        $endDateCalcMin = $endDateCalc.$dataForView['office_from'];
        $endDateCalcMax = $endDateCalc.$dataForView['office_to'];

        $endDateTime = date('Y-m-d H:i:s',strtotime($end_date));
        $endDate = date('Y-m-d',strtotime($end_date));

        if (!empty($workingDays[$startDate])) {
            if (($startDateTimeNumb >= $startDateForCalcMin && $startDateTimeNumb <= $startDateForCalcMax)) {
                $startDateTimeObj = new DateTime($startDateTime);

                if ($startDate != $endDate) {
                    $startDateLastObj = new DateTime($startDate.' '.$dataForView['office_to_str']);
                } else {
                    if ($endDateTimeCalc >= $endDateCalcMin && $endDateTimeCalc <= $endDateCalcMax) {
                        $startDateLastObj = new DateTime($endDateTime);
                    } else {
                        $startDateLastObj = new DateTime($endDate.' '.$dataForView['office_to_str']);
                    }
                }

                $interval = $startDateTimeObj->diff($startDateLastObj);

                $startDayHour = $interval->format('%h');
                $startDayMinutes = $interval->format('%i');
                $startDaySeconds = $interval->format('%s');
            }
        }


        if(!empty($workingDays[$endDate])) {

            if (($endDateTimeCalc >= $endDateCalcMin && $endDateTimeCalc <= $endDateCalcMax) && ($startDate != $endDate)) {
                //echo 'fffff';
                $endDateTimeObj = new DateTime($endDateTime);
                $endDateLastObj = new DateTime($endDate.' '.$dataForView['office_from_str']);

                $interval = $endDateTimeObj->diff($endDateLastObj);

                $endDayHour = $interval->format('%h');
                $endDayMinutes = $interval->format('%i');
                $endDaySeconds = $interval->format('%s');
            } elseif (($endDateTimeCalc > $endDateCalcMax) && ($startDate != $endDate)) {
                //echo 'ddddd';
                $endDateTimeObj = new DateTime($endDate.' '.$dataForView['office_to_str']);
                $endDateLastObj = new DateTime($endDate.' '.$dataForView['office_from_str']);

                $interval = $endDateTimeObj->diff($endDateLastObj);

                $endDayHour = $interval->format('%h');
                $endDayMinutes = $interval->format('%i');
                $endDaySeconds = $interval->format('%s');
            }
        }


        $totalDaysOnThisQueue = 0;
        $totalHoursOnThisQueue = $startDayHour + $totalWorkingHoursOnThisReq + $endDayHour;
        $totalMinutesOnThisQueue = $startDayMinutes + $endDayMinutes;
        $totalSecondsOnThisQueue = $startDaySeconds + $endDaySeconds;

        if ($totalSecondsOnThisQueue > 60) {
            $tmpExistsMinutes = round($totalSecondsOnThisQueue / 60,0);
            $totalMinutesOnThisQueue += $tmpExistsMinutes;
            $tmpRemainSeconds  = $totalSecondsOnThisQueue % 60;
            $totalSecondsOnThisQueue = $tmpRemainSeconds;
        }

        if ($totalMinutesOnThisQueue > 60) {
            $tmpExistsHour = round($totalMinutesOnThisQueue / 60,0);
            $totalHoursOnThisQueue += $tmpExistsHour;
            $tmpRemainMinutes  = $totalMinutesOnThisQueue % 60;
            $totalMinutesOnThisQueue = $tmpRemainMinutes;
        }

        if ($totalHoursOnThisQueue > 8) {
            $tmpExistsDays = round($totalHoursOnThisQueue / 8,0);
            $totalDaysOnThisQueue += $tmpExistsDays;
            $tmpRemainHours  = $totalHoursOnThisQueue % 8;
            $totalHoursOnThisQueue = $tmpRemainHours;
        }


        $queueDurationInMinutes = ($totalHoursOnThisQueue * 60) + $totalMinutesOnThisQueue;
        //echo $startDayHour .'--'. $totalWorkingHoursOnThisReq .'---'. $endDayHour.'---'.$totalMinutesOnThisQueue;
        $totalDaysOnThisQueue = sprintf("%02d", $totalDaysOnThisQueue);
        $totalHoursOnThisQueue = sprintf("%02d", $totalHoursOnThisQueue);
        $totalMinutesOnThisQueue = sprintf("%02d", $totalMinutesOnThisQueue);
        $totalSecondsOnThisQueue = sprintf("%02d", $totalSecondsOnThisQueue);

        $queue_duration = $totalDaysOnThisQueue.':'.$totalHoursOnThisQueue.':'.$totalMinutesOnThisQueue.':'.$totalSecondsOnThisQueue;

        return $queue_duration;
    }
    public function getMailSMSStatus($reference_no = "") {
        $smsRow = DB::table('outgoingsmstable')
            ->select('support_status')
            ->where('outgoingsmstable.reference_number',$reference_no)
            ->whereIn('outgoingsmstable.support_status',[0,11])
            ->first();

        $mailRow = DB::table('outgoingemailtable')
            ->select('support_status')
            ->where('outgoingemailtable.reference_number',$reference_no)
            ->whereIn('outgoingemailtable.support_status',[0,11])
            ->first();

        $returnArr = array();
        $returnArr['is_send_sms'] = 'No';
        $returnArr['is_send_mail'] = 'No';
        if (!empty($smsRow)) {
            $returnArr['is_send_sms'] = 'Yes';
        }
        if (!empty($mailRow)) {
            $returnArr['is_send_mail'] = 'Yes';
        }
        return $returnArr;
    }

    public static function getAllComments($reference_number=''){

        /*$rows = DB::select("
                        SELECT
                            cm.subgroup_id,
                            COALESCE(
                                (SELECT cm1.subgroup_id FROM comments cm1 WHERE cm1.id < cm.id AND cm1.reference_number LIKE cm.reference_number ORDER BY cm1.id DESC LIMIT 1),
                                cm.subgroup_id
                            ) AS prev_subgroup,
                            users.name,
                            subgroup_info.name AS subgroup_name,
                            cm.*
                        FROM
                            comments cm
                        LEFT JOIN users ON (cm.user_id = users.user_id)
                        LEFT JOIN subgroup_info ON (cm.subgroup_id = subgroup_info.id)

                        WHERE cm.reference_number LIKE '$reference_number'
                        ORDER BY cm.time ASC
                    ");*/
        $rows = DB::table('comments')
                    ->select(
                        'comments.*',
                        'users.name',
                        'subgroup_info.name AS subgroup_name'
                    )
                    ->where('reference_number',$reference_number)
                    ->leftJoin('users','comments.user_id','=','users.user_id')
                    ->leftJoin('subgroup_info','comments.subgroup_id','=','subgroup_info.id')
                    ->orderBy('comments.time','ASC')
                    ->get();
        // prd($rows);

        $data = json_decode(json_encode($rows),true);

        return $data;
    }
}

<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 5/10/2020.
 */

namespace App\Services;


use App\Enum\FlowEnum;
use App\Enum\RoleEnum;
use App\GroupInfo;
use App\IssueSubgroupWorkflow;
use App\IssueWorkflow;
use App\Reference;
use App\SubgroupInfo;
use App\UnitItem;
use App\UserUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class WorkFlowService
{
    /**
     * Main Workflow
     * @param $reference
     * @return array|mixed
     */
    public static function workflowStage($reference){
        $issue = Reference::where('reference_number',$reference)->first();
        $row  = \App\IssueWorkflow::where('issue_id',$issue->issue_id)->first();
        //dd($issue);
        if(!empty(Auth::user()->user_unit) && Auth::user()->hasRole([RoleEnum::LOGGER,RoleEnum::EXECUTIVE]) || in_array(2, userUnits())||in_array(1, userUnits())) {

            $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
            $group_info_id = SubgroupInfo::find($subgroup_id);

            $special_workflow =self::getSpecialWorkflow($row->issue_workflow_id, $issue->issue_id, $subgroup_id);

            if($special_workflow){

                $workflow = self::workWithSpecialWorkflow($row->issue_workflow_id, $issue->issue_id, $subgroup_id);
            }else {
                if ( in_array(1, userUnits()) && $issue->unit_id == 1) {
                    $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                        ->where('group_info_id', $group_info_id->group_info_id)
                        ->select('touch_maker as touch', 'hold_maker as hold', 'sla_maker as sla', 'attach_maker as attach', 'attach_maker_item as attach_item')
                        ->first();
                } elseif (in_array(2, userUnits()) && $issue->unit_id == 2) {

                    $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                        ->where('group_info_id', $group_info_id->group_info_id)
                        ->select('touch_checker as touch', 'hold_checker as hold', 'sla_checker as sla', 'attach_checker as attach', 'attach_checker_item as attach_item')
                        ->first();

                } else {
                    //die;
                    $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                        ->where('group_info_id', $group_info_id->group_info_id)
                        ->first();
                }
            }
            return $workflow;

        }else{
            return $workflow=[
                'touch'=>'',
                'hold'=>'',
                'sla'=>'',
                'attach'=>'',
                'attach_item'=>'',
            ];
        }
    }

    /**
     * This is Work flow step
     * @param $reference
     * @return bool
     */
    public function workflowLastStep($reference){
        $issue = Reference::where('reference_number',$reference)->first();
        $row  = \App\IssueWorkflow::where('issue_id',$issue->issue_id)->first();
        
        //dd(Auth::user()->hasRole([RoleEnum::LOGGER,RoleEnum::EXECUTIVE]));
        
        if(Auth::user()->hasRole([RoleEnum::LOGGER,RoleEnum::EXECUTIVE])) {
            //dd($reference);
            $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
            $unit_id = $issue->unit_id; //Auth::user()->user_unit->unit_id;

            $group_info_id = SubgroupInfo::find($subgroup_id);
            $status=false;
            $checker='';
            $maker='';
            //pr($group_info_id->group_info_id);
            $issue_only_touch = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)->where('is_touch_point','<>',1)->get();
            //dd(count($issue_only_touch));
            if(count($issue_only_touch)==0){
                if($unit_id==2){
                       return true;
                   }else{
                       return false;
                   }
            }

            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)->orderBy('issue_group_workflow_id',"DESC")->first();
            if($workflow->group_info_id==$group_info_id->group_info_id){

                if ($workflow->touch_checker==1) {
                   if($unit_id==2){
                       return true;
                   }else{
                       return false;
                   }

                }else {
                   return true;
                }
            }

            return false;

        }else{
            return false;
        }
    }
    public static function workFlowStep($reference,$group_id){

        $issue = Reference::where('reference_number',$reference)->first();
        $row  = \App\IssueWorkflow::where('issue_id',$issue->issue_id)->first();
        $unit_id = Auth::user()->user_unit->unit_id;

        $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
        //if (Auth::user()->user_unit->unit_id == 1) {
        if ($issue->unit_id == 1) {

            $group_info_id = SubgroupInfo::find($subgroup_id);
            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
            if($workflow->touch_checker==0){
                $workflow2 = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('issue_group_workflow_id','>', $workflow->issue_group_workflow_id)
                    ->first();
                return $workflow2->group_info_id;
            }else{
                $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('group_info_id', $group_info_id->group_info_id)
                    ->first();
                return $workflow->group_info_id;
            }
        }
        //if (Auth::user()->user_unit->unit_id == 2) {
        if ($issue->unit_id == 2) {
            //
            $group_info_id = SubgroupInfo::find($subgroup_id);
            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
                $workflow2 = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('issue_group_workflow_id','>', $workflow->issue_group_workflow_id)
                    ->where('is_touch_point','<>',1)
                    ->first();
                    //dd($workflow->issue_group_workflow_id);
                return $workflow2->group_info_id;

        }

        return 0;
    }
    public static function workFlowSubGroup($reference,$group_id){

        $issue = Reference::where('reference_number',$reference)->first();
        $row  = \App\IssueWorkflow::where('issue_id',$issue->issue_id)->first();
        $unit_id = Auth::user()->user_unit->unit_id;

        $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
        //if (Auth::user()->user_unit->unit_id == 1) {
        if ($issue->unit_id == 1) {
            //
            $group_info_id = SubgroupInfo::find($subgroup_id);
            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
            if($workflow->touch_checker==0){
                $workflow2 = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('issue_group_workflow_id','>', $workflow->issue_group_workflow_id)
                    ->first();
                $subgroup_info = \App\SubgroupInfo::where('group_info_id', $workflow2->group_info_id)
                ->first();
                return $subgroup_info->id;
            }else{
                $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('group_info_id', $group_info_id->group_info_id)
                    ->first();
                $subgroup_info = \App\SubgroupInfo::where('group_info_id', $workflow->group_info_id)
                ->first();
                return $subgroup_info->id;
            }
        }
        //if (Auth::user()->user_unit->unit_id == 2) {
        if ($issue->unit_id == 2) {
            //
            $group_info_id = SubgroupInfo::find($subgroup_id);

            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
                $workflow2 = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('issue_group_workflow_id','>', $workflow->issue_group_workflow_id)
                    ->where('is_touch_point','<>',1)
                    ->first();
                // return $workflow2->group_info_id;
                $subgroup_info = \App\SubgroupInfo::where('group_info_id', $workflow2->group_info_id)
                ->first();
                return $subgroup_info->id;

        }

        return 0;
    }
    public static function workFlowUnit($reference,$group_id){
        $issue = Reference::where('reference_number',$reference)->first();
        $row  = \App\IssueWorkflow::where('issue_id',$issue->issue_id)->first();
        $unit_id = Auth::user()->user_unit->unit_id;
        $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
        //if (Auth::user()->user_unit->unit_id == 1) {
        if ($issue->unit_id == 1) {
            //
            $group_info_id = SubgroupInfo::find($subgroup_id);
            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
            if($workflow->touch_checker==0){
                $workflow2 = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('issue_group_workflow_id','>', $workflow->issue_group_workflow_id)
                    ->first();
                return 1;
            }else{
                $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                    ->where('group_info_id', $group_info_id->group_info_id)
                    ->first();
                return 2;
            }
        }
        //if (Auth::user()->user_unit->unit_id == 2) {
        if ($issue->unit_id == 2) {

            $group_info_id = SubgroupInfo::find($subgroup_id);
            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
            $workflow2 = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('issue_group_workflow_id','>', $workflow->issue_group_workflow_id)
                ->first();
            return 1;

        }

        return 0;
    }
    public static function subFlowSubGroup($group_id){
        $subgroup_info = \App\SubgroupInfo::where('group_info_id', $group_id)
            ->first();
        return $subgroup_info->id;
    }
    public static function subFlowUnit($reference,$group_id){
        $issue = Reference::where('reference_number',$reference)->first();
        $row  = \App\IssueWorkflow::where('issue_id',$issue->issue_id)->first();
        $unit_id = Auth::user()->user_unit->unit_id;
        $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
        if ($issue->unit_id == 1) {
            $workflow = \App\IssueGroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_id)
                ->first();
            if($workflow->touch_checker==0){
                return 1;
            }else{
                return 2;
            }
        }
        if ($issue->unit_id == 2) {
            return 1;
        }
        return 0;
    }
    
    /**
     * Special Workflow Status Check
     * @param $issue_workflow_id
     * @param $issue_id
     * @param $subgroup_id
     * @return mixed
     */
    public static function getSpecialWorkflow($issue_workflow_id,$issue_id,$subgroup_id){
        $special_workflow = IssueSubgroupWorkflow::where('subgroup_info_id',$subgroup_id)
            ->where('issue_id',$issue_id)
            ->where('issue_workflow_id',$issue_workflow_id)
            ->first();
        return $special_workflow;
    }

    /**
     * Special Workflow Checking
     * @param $issue_workflow_id
     * @param $issue_id
     * @param $subgroup_id
     * @return mixed
     */
    public static function workWithSpecialWorkflow($issue_workflow_id,$issue_id,$subgroup_id){
        $row  = \App\IssueWorkflow::where('issue_id',$issue_id)->first();
        $group_info_id = SubgroupInfo::find($subgroup_id);
        if(Auth::user()->user_unit->unit_id == 1) {
            $workflow = \App\IssueSubgroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->select('touch_maker as touch', 'hold_maker as hold', 'sla_maker as sla', 'attach_maker as attach', 'attach_maker_item as attach_item')
                ->first();
        } elseif (Auth::user()->user_unit->unit_id == 2) {
            $workflow = \App\IssueSubgroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->select('touch_checker as touch', 'hold_checker as hold', 'sla_checker as sla', 'attach_checker as attach', 'attach_checker_item as attach_item')
                ->first();

        } else {
            $workflow = \App\IssueSubgroupWorkflow::where('issue_workflow_id', $row->issue_workflow_id)
                ->where('group_info_id', $group_info_id->group_info_id)
                ->first();
        }
        return $workflow;
    }
    public static function getFlowType($id){

        $row = IssueWorkflow::where('issue_id',$id)->first();

        return (!empty($row->flow_type))? $row->flow_type:'';

    }
    public static function getFlowTypeCheck($reference_id){

        //dd($reference_id);
        $reference = DB::table('reference')
            ->join('issue_workflows','reference.issue_id','=','issue_workflows.issue_id')
            ->where('reference_number',$reference_id)->first();
        if($reference){
            return $reference->flow_type;
        }
        return '';

    }
    public static function getFlowTypeForward($id){
        $row = IssueWorkflow::where('issue_id',$id)->where('flow_type',FlowEnum::FORWARD)->first();

        return $row->flow_type;
    }
    public function getAllGroupList(){
        //$rows =  GroupInfo::all();
		$rows = GroupInfo::where('is_active','=',1)->where('group_level_id','<>',1)->orderBy('name', 'ASC')->get();
        return $rows;
    }
    public function checkExistsNextLevelGroup($group_id, $issue_id) {
        $existsGroupInfo = DB::table('reference')
                                ->where('reference.subgroup_id',$group_id)
                                ->where('reference.issue_id',$issue_id)
                                ->where('reference.form_status','<>','11')
                                ->count();
        return $existsGroupInfo;
    }

    public function subFlowList($issue_id=NULL) {
        $existingSubFlow = DB::select(
            "SELECT 
                issue_sub_workflows.options,
                group_info.id AS group_id,
                group_info.name AS group_name
            FROM issue_sub_workflows
            LEFT JOIN group_info ON (group_info.id = issue_sub_workflows.group_info_id)
            WHERE 
                issue_sub_workflows.issue_id = $issue_id
            "
        );
     
        return $existingSubFlow;
    }

}

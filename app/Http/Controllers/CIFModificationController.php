<?php

namespace App\Http\Controllers;

use App\ApiCommonConfig;
use App\Comment;
use App\DynamicAPICredential;
use App\IssueConfigMapping;
use App\SubgroupInfo;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Reference;
use App\WForm;
use App\WFormType;
use App\Complaint;
use App\ComplaintType;
use App\ComplaintFormType;

use App\CIFModificationUrl;
use App\CIFParentUrl;
use App\CIFWorkflow;
use App\CIFApi;
use App\CIFRequestResponse;

use GuzzleHttp\Client;
use DateTime;
use Illuminate\Support\Facades\Session;
use Storage;
use SimpleXMLElement;
use DomXPath;
use DomDocument;

class CIFModificationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['role_or_permission:superadmin|admin|accessCIFModification'])
            ->only('CIFUrlList', 'CIFUrlAdd', 'CIFUrlStore', 'CIFUrlEdit', 'CIFUrlUpdate', 'CIFUrlStatus');
    }

    public function index(Request $request)
    {
        $cifModel = new CIFModificationUrl();
        $cifDataArr = $cifModel
            ->where('name', '=', $request->name)
            ->where('status', '=', 1)
            ->select('name', 'url', 'request')
            ->first();
        $soapName = $cifDataArr->name;
        $soapUrl = $cifDataArr->url;
        $soapRequest = $cifDataArr->request;

        $look_folder = $request->look_folder;
        $customer = $request->customer;
        $folder_index = $request->folder_index;
        $parent_folder_index = $request->parent_folder_index;
        $folder_name = $request->folder_name;
        $document_name = $request->document_name;
        $file_content = $request->file_content;

        if (!empty($soapRequest)) {
            $soapRequest = str_replace("#look_folder#", $look_folder, $soapRequest);
            $soapRequest = str_replace("#customer#", $customer, $soapRequest);
            $soapRequest = str_replace("#folder_index#", $folder_index, $soapRequest);
            $soapRequest = str_replace("#parent_folder_index#", $parent_folder_index, $soapRequest);
            $soapRequest = str_replace("#folder_name#", $folder_name, $soapRequest);
            $soapRequest = str_replace("#document_name#", $document_name, $soapRequest);
            $soapRequest = str_replace("#file_content#", $file_content, $soapRequest);
        }
        $header = array(
            "Content-type: text/soap+xml;charset=\"utf-8\";action:\"urn:$soapName\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: " . strlen($soapRequest),
        );

        $soapClient = new SoapHelper();
        $response = $soapClient->soapCall($soapUrl, $soapRequest, $header, 'Response');
        echo '<pre>';
        print_r($response);
    }

    public function CIFUrlList()
    {
        $title = "CIF Url Request List";
        $title_for_layout = "CIF Url Request List";
        $cifModelName = new CIFModificationUrl;
        $tblData = array();
        $dataObj = $cifModelName
            ->select("cif_modification_url.id", "cif_modification_url.name", "cif_modification_url.url", "cif_modification_url.request", "cif_parent_url.name as parent",
                DB::raw("CASE WHEN cif_modification_url.status = 1 THEN 'Active' WHEN cif_modification_url.status = 0 THEN 'Inactive' ELSE 'Invalid' END AS status_name"), "cif_modification_url.status")
            ->join('cif_parent_url', 'cif_modification_url.parent_id', 'cif_parent_url.id')
            ->orderBy("cif_modification_url.id", "DESC")
            ->get();
        if (!empty($dataObj)) {
            $tblData = $dataObj->toArray();
        }
        return view('CIFModification.list', compact('title', 'title_for_layout', 'tblData', 'dataObj'));
    }

    public function CIFUrlAdd()
    {
        $title = "Add CIF Url Request";
        $title_for_layout = "Add CIF Url Request";
        $cifParent = CIFParentUrl::select("id", "name")
            ->where("status", 1)
            ->orderBy("id", "DESC")
            ->get();
        return view('CIFModification.add', compact('title', 'title_for_layout', 'cifParent'));
    }

    public function CIFUrlStore(Request $request)
    {
        $this->validate($request, [
            'parent' => 'required',
            'name' => 'required',
            'url' => 'required',
            'request' => 'required',
        ]);
        $cifModelName = new CIFModificationUrl;
        if ($request->isMethod('post')) {
            $cifModelName->parent_id = $request->get('parent');
            $cifModelName->name = $request->get('name');
            $cifModelName->url = $request->get('url');
            //$cifModelName->request = $request->get('request');
            $cifModelName->request = trim(preg_replace('/\s\s+/', ' ', $request->get('request')));
            $cifModelName->date_format = $request->get('date_format');;
            $cifModelName->status = 1;
            if ($cifModelName->save()) {
                flash('CIF Url has been stored successfully', 'success');
                return redirect('/CIFModification/CIFUrl/list');
            } else {
                flash('Failed to insert data', 'danger');
                return redirect('/CIFModification/CIFUrl/add');
            }
        }
    }

    public function CIFUrlEdit($id = null)
    {
        $title = "Edit CIF Url Request";
        $title_for_layout = 'Edit CIF Url Request';
        $cifModelName = new CIFModificationUrl;
        $dataForView = $cifModelName->where('id', decrypt($id))->first();
        if ($dataForView->status == 0) {
            abort(403, 'Edit Not Allowed !!!');
        }
        $cifParent = CIFParentUrl::select("id", "name")
            ->where("status", 1)
            ->orderBy("id", "DESC")
            ->get();
        return view('CIFModification.edit', compact('title', 'title_for_layout', 'dataForView', 'cifParent'));
    }

    public function CIFUrlUpdate($id = null, Request $request)
    {
        $this->validate($request, [
            'parent' => 'required',
            'name' => 'required',
            'url' => 'required',
            'request' => 'required',
        ]);
        $cifModelName = new CIFModificationUrl;
        if ($request->isMethod('post')) {
            $cifModelName = $cifModelName->where('id', decrypt($id))->first();
            $cifModelName->parent_id = $request->get('parent');
            $cifModelName->name = $request->get('name');
            $cifModelName->url = $request->get('url');
            //$cifModelName->request = $request->get('request');
            $cifModelName->request = trim(preg_replace('/\s\s+/', ' ', $request->get('request')));
            $cifModelName->date_format = $request->get('date_format');;
            $cifModelName->status = 1;
            if ($cifModelName->save()) {
                flash('CIF Url has been updated successfully', 'success');
                return redirect('/CIFModification/CIFUrl/list');
            } else {
                flash('Failed to update data', 'danger');
                return redirect()->back();
            }
        }
    }

    public function CIFUrlStatus($id = null, $status)
    {
        $data = CIFModificationUrl::find(decrypt($id));
        if (!empty($data)) {
            $data->update(['status' => $status]);
            if ($status == 1) {
                flash('CIF Url has been activated successfully', 'success');
            } else {
                flash('CIF Url has been inactivated !!', 'warning');
            }
        } else {
            flash('Not Found', 'danger');
        }
        return redirect()->back();
    }

    public function cifModificationWorkFlow()
    {
        $title = "CIF Workflow List";
        $title_for_layout = 'CIF Workflow List';

        // $issueItems = DB::select("SELECT unit_items.id, unit_items.master_id, unit_items.name, issue_categories.name AS issue_cat_name FROM issue_workflows LEFT JOIN unit_items ON (unit_items.id = issue_workflows.issue_id) LEFT JOIN issue_categories ON (issue_categories.id = unit_items.issue_categories_id) LEFT JOIN issue_group_workflows ON (issue_group_workflows.issue_workflow_id = issue_workflows.issue_workflow_id) WHERE issue_group_workflows.group_info_id IN (SELECT group_info.id FROM group_info WHERE group_info.group_level_id<>1)AND unit_items.status=1 GROUP BY issue_workflows.issue_workflow_id HAVING COUNT(issue_workflows.issue_workflow_id) > 1 ORDER BY issue_categories.name DESC, unit_items.name ASC ");

        $issueItems = DB::select(
            "SELECT
                            unit_items.id,
                            unit_items.master_id,
                            unit_items.name,
                            issue_categories.name AS issue_cat_name
                        FROM issue_workflows
                        LEFT JOIN unit_items ON (unit_items.id = issue_workflows.issue_id)
                        LEFT JOIN issue_categories ON (issue_categories.id = unit_items.issue_categories_id)
                        WHERE
                            unit_items.status=1
                        GROUP BY
                            issue_workflows.issue_workflow_id
                        ORDER BY
                            issue_categories.name DESC,
                            unit_items.name ASC
                        "
        );
        // prd($issueItems);
        return view('CIFModification.cif_workflow_index', compact('issueItems', 'title', 'title_for_layout'));
    }

    public function setCIFModificationWorkFlow($id = null)
    {
        $title = "Set CIF Workflow";
        $title_for_layout = 'Set CIF Workflow';
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            abort(403, 'Un-Authorize Access!!!');
        }
        $tmpIssueItems = DB::select(
            "SELECT
                            issue_workflows.issue_workflow_id,
                            unit_items.id,
                            unit_items.master_id,
                            unit_items.name,
                            issue_categories.name AS issue_cat_name
                        FROM issue_workflows

                        LEFT JOIN unit_items ON (unit_items.id = issue_workflows.issue_id)
                        LEFT JOIN issue_categories ON (issue_categories.id = unit_items.issue_categories_id)
                        WHERE
                            unit_items.id = $id
                        ORDER BY
                            issue_categories.name DESC,
                            unit_items.name ASC
                        "
        );
        if (!empty($tmpIssueItems[0])) {
            $issueItems = $tmpIssueItems[0];
        } else {
            $issueItems = '';
            abort(403, 'Un-Authorize Access');
        }
        $wFlowNextLevelGroup = DB::select(
            "SELECT
                issue_group_workflows.group_info_id,
                group_info.name AS group_name,
                group_info.group_level_id,
                (CASE WHEN group_info.group_level_id = 1 THEN 'Touch Group' WHEN group_info.group_level_id = 0 THEN 'Next Level Group' ELSE 'Invalid' END) AS group_level
            FROM issue_workflows
            LEFT JOIN issue_group_workflows ON (issue_group_workflows.issue_workflow_id = issue_workflows.issue_workflow_id)
            LEFT JOIN group_info ON (group_info.id = issue_group_workflows.group_info_id)
            WHERE
                issue_workflows.issue_id = $id
            ORDER BY
                issue_group_workflows.issue_group_workflow_id ASC
            "
        );
        $issue_id = encrypt($id);
        // prd($wFlowNextLevelGroup);
        /*
         AND group_info.group_level_id<>1
        */
        return view('CIFModification.cif_workflow_set', compact('issueItems', 'wFlowNextLevelGroup', 'issue_id', 'title', 'title_for_layout'));
    }

    public function updateCIFModificationWorkFlow(Request $request, $id = null)
    {
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            abort(403, 'Un-Authorize Access!!!');
        }
        if ($request->isMethod('post')) {
            $group_info_id = $request->newdata;
            if (!empty($group_info_id)) {
                CIFWorkflow::where('issue_id', $id)->delete();
                foreach ($group_info_id as $group_info) {
                    $modelName = new CIFWorkflow();
                    $modelName->issue_id = $id;
                    $modelName->group_info_id = $group_info['group_info_id'];
                    $modelName->status = !empty($group_info['status']) ? 1 : 0;
                    $modelName->save();
                }
                flash('CIF Workflow has been updated successfully', 'success');
                return redirect('/CIFModification/cif-workflow');
            } else {
                flash('Failed to update data', 'danger');
                return redirect()->back();
            }
        }
    }

    public function CIFParentUrlList()
    {
        $title = "CIF Parent Url Request List";
        $title_for_layout = "CIF Parent Url Request List";
        $cifModelName = new CIFParentUrl;
        $tblData = array();
        $dataObj = $cifModelName
            ->select("id", "name", "details", DB::raw("CASE WHEN status = 1 THEN 'Active' WHEN status = 0 THEN 'Inactive' ELSE 'Invalid' END AS status_name"), DB::raw("CASE WHEN type = 2 THEN 'Yes' ELSE 'No' END AS type_name"), "status")
            ->orderBy("id", "DESC")
            ->get();
        if (!empty($dataObj)) {
            $tblData = $dataObj->toArray();
        }
        return view('CIFModification.parent_list', compact('title', 'title_for_layout', 'tblData', 'dataObj'));
    }

    public function CIFParentUrlAdd()
    {
        $title = "Add CIF Parent Url Request";
        $title_for_layout = "Add CIF Parent Url Request";
        return view('CIFModification.parent_add', compact('title', 'title_for_layout'));
    }

    public function CIFParentUrlStore(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'details' => 'required',
        ]);
        $cifModelName = new CIFParentUrl;
        if ($request->isMethod('post')) {
            $cifModelName->name = $request->get('name');
            $cifModelName->details = $request->get('details');
            $cifModelName->status = 1;
            $cifModelName->type = !empty($request->get('type')) ? $request->get('type') : 1;
            if ($cifModelName->save()) {
                flash('CIF Parent Url has been stored successfully', 'success');
                return redirect('/CIFParentUrl/list');
            } else {
                flash('Failed to insert data', 'danger');
                return redirect('/CIFParentUrl/add');
            }
        }
    }

    public function CIFParentUrlEdit($id = null)
    {
        $title = "Edit CIF Parent Url Request";
        $title_for_layout = 'Edit CIF Parent Url Request';
        $cifModelName = new CIFParentUrl;
        $dataForView = $cifModelName->where('id', decrypt($id))->first();
        if ($dataForView->status == 0) {
            abort(403, 'Edit Not Allowed !!!');
        }
        return view('CIFModification.parent_edit', compact('title', 'title_for_layout', 'dataForView'));
    }

    public function CIFParentUrlUpdate($id = null, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'details' => 'required',
        ]);
        $cifModelName = new CIFParentUrl;
        if ($request->isMethod('post')) {
            $cifModelName = $cifModelName->where('id', decrypt($id))->first();
            $cifModelName->name = $request->get('name');
            $cifModelName->details = $request->get('details');
            $cifModelName->status = 1;
            $cifModelName->type = !empty($request->get('type')) ? $request->get('type') : 1;
            if ($cifModelName->save()) {
                flash('CIF Parent Url has been updated successfully', 'success');
                return redirect('/CIFParentUrl/list');
            } else {
                flash('Failed to update data', 'danger');
                return redirect()->back();
            }
        }
    }

    public function CIFParentUrlStatus($id = null, $status)
    {
        $data = CIFParentUrl::find(decrypt($id));
        if (!empty($data)) {
            $data->update(['status' => $status]);
            if ($status == 1) {
                flash('CIF Parent Url has been activated successfully', 'success');
            } else {
                flash('CIF Parent Url has been inactivated !!', 'warning');
            }
        } else {
            flash('Not Found', 'danger');
        }
        return redirect()->back();
    }

    public function setCIFModificationAPI($id = null)
    {
        $title = "Set CIF API";
        $title_for_layout = 'Set CIF API';
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            abort(403, 'Un-Authorize Access!!!');
        }
        $tmpIssueItems = DB::select(
            "SELECT
                            issue_workflows.issue_workflow_id,
                            unit_items.id,
                            unit_items.master_id,
                            unit_items.name,
                            issue_categories.name AS issue_cat_name
                        FROM issue_workflows

                        LEFT JOIN unit_items ON (unit_items.id = issue_workflows.issue_id)
                        LEFT JOIN issue_categories ON (issue_categories.id = unit_items.issue_categories_id)
                        WHERE
                            unit_items.id = $id
                        ORDER BY
                            issue_categories.name DESC,
                            unit_items.name ASC
                        "
        );
        if (!empty($tmpIssueItems[0])) {
            $issueItems = $tmpIssueItems[0];
        } else {
            $issueItems = '';
            abort(403, 'Un-Authorize Access');
        }

        $issue_id = encrypt($id);
        $cifParent = CIFParentUrl::select("id", "name")
            ->where("status", 1)
            ->where("type", '!=', 2)
            ->orderBy("id", "DESC")
            ->get();
        return view('CIFModification.cif_api_set', compact('issueItems', 'issue_id', 'title', 'title_for_layout', 'cifParent'));
    }

    public function updateCIFModificationAPI(Request $request, $id = null)
    {
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            abort(403, 'Un-Authorize Access!!!');
        }
        if ($request->isMethod('post')) {
            $parent_api = $request->parent_api;
            if (!empty($parent_api)) {
                CIFApi::where('issue_id', $id)->where('type', 1)->delete();
                foreach ($parent_api as $api) {
                    $modelName = new CIFApi();
                    $modelName->issue_id = $id;
                    $modelName->parent_api = $api;
                    $modelName->type = 1;
                    $modelName->save();
                }
                flash('CIF API has been updated successfully', 'success');
                return redirect('/CIFModification/cif-workflow');
            } else {
                CIFApi::where('issue_id', $id)->delete();
                flash('CIF API cleared successfully', 'success');
                return redirect('/CIFModification/cif-workflow');
            }
        } else {
            flash('Failed to update data', 'danger');
            return redirect()->back();
        }
    }

    public function setCIFInquiryAPI(Request $request, $id = null)
    {
        $title = "CIF Inquiry API";
        $title_for_layout = 'CIF Inquiry API';
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            abort(403, 'Un-Authorize Access!!!');
        }
        $tmpIssueItems = DB::select(
            "SELECT
                            issue_workflows.issue_workflow_id,
                            unit_items.id,
                            unit_items.master_id,
                            unit_items.name,
                            issue_categories.name AS issue_cat_name
                        FROM issue_workflows

                        LEFT JOIN unit_items ON (unit_items.id = issue_workflows.issue_id)
                        LEFT JOIN issue_categories ON (issue_categories.id = unit_items.issue_categories_id)
                        WHERE
                            unit_items.id = $id
                        ORDER BY
                            issue_categories.name DESC,
                            unit_items.name ASC
                        "
        );
        if (!empty($tmpIssueItems[0])) {
            $issueItems = $tmpIssueItems[0];
        } else {
            $issueItems = '';
            abort(403, 'Un-Authorize Access');
        }

        $issue_id = encrypt($id);
        $cifParent = CIFParentUrl::select("id", "name")
            ->where("status", 1)
            ->where("type", 2)
            ->orderBy("id", "DESC")
            ->get();
        return view('CIFModification.cif_inquiry_api', compact('issueItems', 'issue_id', 'title', 'title_for_layout', 'cifParent'));
    }

    public function updateCIFInquiryAPI(Request $request, $id = null)
    {
        try {
            $id = decrypt($id);
        } catch (DecryptException $e) {
            abort(403, 'Un-Authorize Access!!!');
        }
        if ($request->isMethod('post')) {
            $parent_api = $request->parent_api;
            if (!empty($parent_api)) {
                CIFApi::where('issue_id', $id)->where('type', 2)->delete();
                foreach ($parent_api as $api) {
                    $modelName = new CIFApi();
                    $modelName->issue_id = $id;
                    $modelName->parent_api = $api;
                    $modelName->type = 2;
                    $modelName->save();
                }
                flash('CIF Inquiry API has been updated successfully', 'success');
                return redirect('/CIFModification/cif-workflow');
            } else {
                CIFApi::where('issue_id', $id)->delete();
                flash('CIF Inquiry API cleared successfully', 'success');
                return redirect('/CIFModification/cif-workflow');
            }
        } else {
            flash('Failed to update data', 'danger');
            return redirect()->back();
        }
    }

    public function apiUpdate(Request $request)
    {
        $response = array();
        $response['success'] = 0;
        $response['msg'] = '';
        $response['failed_api'] = [];
        $status = [];
        $tmp_status = 1;
        $status_code = 0;
        $status_msg = '';
        $globalTransactionId = '';
        $msg = '';
        $ref_no = '';
        $req_from = '';
        $cif_no = '';
        $account_number = '';
        $failed_api_arr = array();
        $subgroup_id = 0;
        $group_info_id = 0;
        $extraNode = '';
        $extraNodeArray = [];
        $type = '';
        $num = '';
        $eml = '';
        $cun = '';
        $cty = '';
        $pref = '';
        $add1 = '';
        $add2 = '';
        $add3 = '';
        $stat = '';
        $twn = '';
        $posCode = '';
        $prefAddr = '';
        $addressType = '';
        $placeOfIssue = '';
        $nreplaceOfIssue = '';
        $refNum = '';
        $nrerefNum = '';
        $nrecountryOfIssue = '';
        $countryOfIssue = '';
        $docExpDt = '';
        $docIssueDt = '';
        $nredocIssueDt = '';
        $isCustNRE = '';
        $nreBecomingDt = '';
        $firstname = '';
        $middlename = '';
        $lastname = '';
        $fullName = '';
        $shortName = '';
        $shortName1 = '';
        $shortName2 = '';
        $shortName3 = '';
        $accRes = [];
        $segment = '';
        $tpTotlDepTranCnt = 0;
        $tpTotlDepTranAmtHigh = 0;
        $tpTotlDepTranAmtTot = 0;
        $tpTotlWdrTranCnt = 0;
        $tpTotlWdrTranAmtHigh = 0;
        $tpTotlWdrTranAmtTot = 0;
        $tpRemtDepTranAmtTot = 0;
        $tpRemtWdrTranAmtTot = 0;
        $memo = 'Data updated from AYS';

        try {
            $encrypted_ref_no = xss_cleaner($request->ref_no);
            $encrypted_cif_no = xss_cleaner($request->cif_no);
            $encrypted_account_number = xss_cleaner($request->account_number);
            $req_from = xss_cleaner($request->req_from);
            $ref_no = decrypt($encrypted_ref_no);
            $cif_no = decrypt($encrypted_cif_no);
            $account_number = decrypt($encrypted_account_number);
            $failed_api = xss_cleaner($request->failed_api);
        } catch (DecryptException $e) {
            $response['success'] = 0;
            $response['msg'] = 'Something went wrong!!!. Please refresh this page';
            return response(['msg' => $response['msg'], 'status' => $response['success'], 'failed_api' => $response['failed_api']]);
        }

        $referenceModel = new Reference();
        $referenceData = $referenceModel->where('reference_number', $ref_no)->first();

        if (!empty($referenceData)) {
            if ($referenceData->api_status == 1) {
                $response['success'] = 1;
                $response['msg'] = 'API data already updated !!!';
                return response(['msg' => $response['msg'], 'status' => $response['success'], 'failed_api' => $response['failed_api']]);
            } elseif ($referenceData->failed_retry >= 2) {
                $response['success'] = 2;
                $response['msg'] = 'You have already tried updating this API data. Please Contact with Administrator !!!';
                return response(['msg' => $response['msg'], 'status' => $response['success'], 'failed_api' => $response['failed_api']]);
            } else {
                $failed_api = $referenceData->failed_api;
            }
        }
        $failed_api_arr = explode(',', $failed_api);

        $dataObj = array();
        $dataForView = array();
        if ($req_from == "wform") {
            $wCompModel = new Reference;
            $dataObj = $wCompModel
                ->select('w_form.*', 'w_form_type.extra_field', 'reference.issue_id', 'reference.memo')
                ->with(['issueConfigForApi'])
                ->leftJoin('w_form', 'reference.reference_number', '=', 'w_form.reference_number')
                ->leftJoin('w_form_type', 'w_form_type.reference_number', '=', 'w_form.reference_number')
                ->where('reference.reference_number', $ref_no)
                ->first();

        } elseif ($req_from == "complaint") {
            $wCompModel = new Reference;
            $dataObj = $wCompModel
                ->select('complaint.*', 'complaint_form_type.extra_field', 'reference.issue_id', 'reference.memo')
                ->with(['issueConfigForApi'])
                ->leftJoin('complaint', 'reference.reference_number', '=', 'complaint.reference_number')
                ->leftJoin('complaint_form_type', 'complaint_form_type.reference_number', '=', 'complaint.reference_number')
                ->where('reference.reference_number', $ref_no)
                ->first();
        }

        if (!empty($dataObj)) {

            $dataForSave = array();
            $dataForArr = $dataObj->toArray();

            $extraField = json_decode($dataForArr['extra_field'], true);
            $extraFieldIdx = array();

            $issue_id = $dataForArr['issue_id'];
            $segment = $dataForArr['segment'];
            $segment = substr($segment, 0, 1);

            $getChildApi = DB::table('cif_api')
                ->select('cif_modification_url.*')
                ->leftJoin('cif_parent_url', 'cif_parent_url.id', 'cif_api.parent_api')
                ->leftJoin('cif_modification_url', 'cif_parent_url.id', 'cif_modification_url.parent_id')
                ->where('cif_api.issue_id', $issue_id)
                ->where('cif_api.type', 1)
                ->where('cif_parent_url.status', 1)
                ->where('cif_modification_url.status', 1);
            if (!empty($failed_api)) {
                $getChildApi = $getChildApi->whereIn('cif_modification_url.id', $failed_api_arr);
            }
            /*if (!empty($segment)) {
                if ($segment == 3) {
                    // Retail CIF
                    $getChildApi = $getChildApi->where('cif_modification_url.id', 8);
                } elseif ($segment == 4) {
                    // Corporate CIF
                    $getChildApi = $getChildApi->where('cif_modification_url.id', 17);
                }
            }*/
            $getChildApi = $getChildApi
                ->get()
                ->toArray();

            $issueConfigMap = DB::table('issue_config_mapping')
                ->select('issue_config_mapping.*', 'issue_config.label_name')
                ->leftJoin('issue_config', function ($join) {
                    $join->on('issue_config_mapping.issue_id', '=', 'issue_config.issue_id');
                    $join->on('issue_config_mapping.field_name', '=', 'issue_config.field_name');
                })
                ->where('issue_config_mapping.issue_id', $issue_id)
                ->get()
                ->toArray();

            $issueAPICommonConfig = DB::table('api_common_config')
                ->select('api_common_config.*')
                ->where('api_common_config.issue_id', $issue_id)
                ->where('api_common_config.type', 1)
                ->pluck('value', 'api_parameter')
                ->toArray();

            $getChildApi = json_decode(json_encode($getChildApi), true);
            $issueConfigMap = json_decode(json_encode($issueConfigMap), true);

            $extraFieldWithIssueConfig = array();

            foreach ($extraField as $ext) {
                if (!empty($ext['Address Type'])) {
                    $addressType = $ext['Address Type'];
                }
                if (!empty($ext['First Name'])) {
                    $firstname = $ext['First Name'];
                }
                if (!empty($ext['Middle Name'])) {
                    $middlename = $ext['Middle Name'];
                }
                if (!empty($ext['Last Name'])) {
                    $lastname = $ext['Last Name'];
                }
                /* Finacle Api value | Cardpro Api value | Frontend value show
                Ex:- 0004|BD~Bangladesh */
                foreach ($issueConfigMap as $issueCfg) {
                    if (isset($ext[$issueCfg['label_name']])) {
                        $tmpExtValue = $ext[$issueCfg['label_name']];
                        if (str_contains($tmpExtValue, '~')) {
                            $tmpExtValueArr = explode('~', $tmpExtValue);
                            $tmpExtValue = (!empty($tmpExtValueArr[0])) ? $tmpExtValueArr[0] : $tmpExtValue;
                        }
                        $extraFieldWithIssueConfig[$issueCfg['api_parameter']] = $tmpExtValue;
                    }
                }
            }
            if (!empty($firstname)) {
                $fullName .= $firstname;
                $fullName .= ' ';
                $shortName1 = substr($firstname, 0, 1);
                $shortName = $shortName1;
            }
            if (!empty($middlename)) {
                $fullName .= $middlename;
                $fullName .= ' ';
                $shortName2 = substr($middlename, 0, 1);
                $shortName = $shortName.$shortName2;
            }
            if (!empty($lastname)) {
                $fullName .= $lastname;
                $shortName3 = substr($lastname, 0, 8);
                $shortName = $shortName3.$shortName;
            }

            $testArr = array();
            $requestToPush = array();
            foreach ($getChildApi as $childRow) {
                $api_id = $childRow['id'];
                $url = $childRow['url'];
                $req = $childRow['request'];
                $date_format = !empty($childRow['date_format']) ? $childRow['date_format'] : 'Y-m-d\TH:i:s.v';
                $password = DynamicAPICredential::where('api', $api_id)->first();
                $password = !empty($password->password) ? decrypt($password->password) : '';
                $sourceTimestamp = now()->format('Y-m-d\TH:i:s.v');
                $acctStmtNxtPrintDt = $sourceTimestamp;
                if ($sourceTimestamp >= date('Y-m-d', strtotime("01/01")) && $sourceTimestamp <= date('Y-m-d', strtotime("06/30"))) {
                    $acctStmtNxtPrintDt = date('Y-m-d\TH:i:s.v',strtotime("this year June 30th"));
                } elseif ($sourceTimestamp > date('Y-m-d', strtotime("06/30")) && $sourceTimestamp <= date('Y-m-d', strtotime("12/31"))) {
                    $acctStmtNxtPrintDt = date('Y-m-d\TH:i:s.v',strtotime("this year December 31th"));
                }
                $global_transaction_id = 'AYS' . now()->format('YmdHisv');
                $req = str_replace("#cif_number#", $cif_no, $req);
                $req = str_replace("#account_number#", $account_number, $req);
                $req = str_replace("#global_transaction_id#", $global_transaction_id, $req);
                $req = str_replace("#sourceTimestamp#", $sourceTimestamp, $req);

                /***************************
                 * Address Issue ID UAT : 1019,1078,1091   *
                 * Card Address API ID UAT : 14 *
                 ****************************/
                if (($issue_id == 1019 || $issue_id == 1078) && $api_id == 14) {
                    $req = cifApiNodeRemover($req, $addressType);
                }
                /***************************
                 * Name change Issue ID: 128,1080  *
                 ****************************/
                if ($issue_id == 128 || $issue_id == 1080) {
                    $req = str_replace("#fullName#", $fullName, $req);
                    $req = str_replace("#name#", $fullName, $req);
                    $req = str_replace("#firstName#", 'firstName', $req);
                    $req = str_replace("#middleName#", 'middleName', $req);
                    $req = str_replace("#lastName#", 'lastName', $req);
                    $req = str_replace("#shortName#", 'shortName', $req);
                }
                /***************************
                 * Memo In card update api *
                 ****************************/
                if (!empty($referenceData->memo)) {
                    $req = str_replace("#memo#", $referenceData->memo, $req);
                } else {
                    $memoname = DB::table('unit_items')
                        ->where('master_id', $issue_id)
                        ->select('name')
                        ->first();
                    if (!empty($memoname)) {
                        $memo = substr($memoname->name,0,15) . ' ' . $memo;
                    }
                    $req = str_replace("#memo#", $memo, $req);
                    $referenceData->memo = $memo;
                }

                /* Finacle Api value | Cardpro Api value ~ Frontend value show
                Ex:- 0004|BD~Bangladesh */
                foreach ($extraFieldWithIssueConfig as $keyField => $configVal) {
                    if (validateDate($configVal)) {
                        $configVal = date($date_format, strtotime($configVal));
                    }
                    if (str_contains($configVal, '|')) {
                        $configValArr = explode('|', $configVal);
                        $configVal1 = (!empty($configValArr[0])) ? $configValArr[0] : $configVal;
                        $configVal2 = (!empty($configValArr[1])) ? $configValArr[1] : $configVal;
                        /* Cardpro API ID UAT : 13, 14 */
                        if ($api_id == 13 || $api_id == 14) {
                            $configVal = $configVal2;
                        } else {
                            $configVal = $configVal1;
                        }
                    }
                    /*  Cardpro addrline missing Address Change Issue ID UAT 1019,1078
                    API ID UAT : 14  */
                    if (($issue_id == 1019 || $issue_id == 1078) && ($api_id == 14)) {
                        if (str_contains($configVal, '#')) {
                            $configVal = str_replace('#', '`', $configVal);
                        }
                    }
                    /*
                        Phone Number Change Issue: ID 119,518,1081
                        Address Change Issue ID: 1019,1078
                        Foreign Address Change Issue ID: 1091
                        Email Address Change Issue: ID 1077,120
                    */
                    $iIds = [119, 120, 518, 1019, 1077, 1078, 1081, 1091];
                    /*  Finacle API ID UAT : 8  */
                    if (in_array($issue_id, $iIds) && ($api_id == 8)) {
                        $extraNodeArray[$keyField] = $configVal;
                    } else {
                        $req = str_replace('#' . $keyField . '#', $configVal, $req);
                    }
                    /*  Spouse Name Issue IDs UAT : 127,129,523  */
                    if (($issue_id == 127 || $issue_id == 129 || $issue_id == 523) && ($api_id == 8)) {
                        if ($keyField == 'spouseName') {
                            $req = str_replace("<v1:spouseName>#spouseName#</v1:spouseName>", "<v1:spouseName>null</v1:spouseName>", $req);
                            $req = str_replace("<v1:spouseName></v1:spouseName>", "<v1:spouseName>null</v1:spouseName>", $req);
                        }
                        if ($keyField == 'spousesName') {
                            $req = str_replace("<v1:spousesName>#spousesName#</v1:spousesName>", "<v1:spousesName>null</v1:spousesName>", $req);
                            $req = str_replace("<v1:spousesName></v1:spousesName>", "<v1:spousesName>null</v1:spousesName>", $req);
                        }
                    }
                    /*  Employer Name Issue IDs UAT : 891  */
                    if ($issue_id == 891 && $api_id == 8) {
                        if ($keyField == 'nameOfEmployer') {
                            $req = str_replace("<v1:nameOfEmployer>#nameOfEmployer#</v1:nameOfEmployer>", "<v1:nameOfEmployer>null</v1:nameOfEmployer>", $req);
                            $req = str_replace("<v1:nameOfEmployer></v1:nameOfEmployer>", "<v1:nameOfEmployer>null</v1:nameOfEmployer>", $req);
                        }
                        if ($keyField == 'designation') {
                            $req = str_replace("<v1:designation>#designation#</v1:designation>", "<v1:designation>null</v1:designation>", $req);
                            $req = str_replace("<v1:designation></v1:designation>", "<v1:designation>null</v1:designation>", $req);
                        }
                    }
                    /* Address change Data Nullify Issue IDs UAT : 1019,1078  CardPro API ID UAT : 14 */
                    if (($issue_id == 1019 || $issue_id == 1078) && ($api_id == 14)) {
                        if ($keyField == 'address1') {
                            $req = str_replace("<v1:address1>#address1#</v1:address1>", "<v1:address1>null</v1:address1>", $req);
                            $req = str_replace("<v1:address1></v1:address1>", "<v1:address1>null</v1:address1>", $req);
                        }
                        if ($keyField == 'address2') {
                            $req = str_replace("<v1:address2>#address2#</v1:address2>", "<v1:address2>null</v1:address2>", $req);
                            $req = str_replace("<v1:address2></v1:address2>", "<v1:address2>null</v1:address2>", $req);
                        }
                        if ($keyField == 'address3') {
                            $req = str_replace("<v1:address3>#address3#</v1:address3>", "<v1:address3>null</v1:address3>", $req);
                            $req = str_replace("<v1:address3></v1:address3>", "<v1:address3>null</v1:address3>", $req);
                        }
                        if ($keyField == 'address4') {
                            $req = str_replace("<v1:address4>#address4#</v1:address4>", "<v1:address4>null</v1:address4>", $req);
                            $req = str_replace("<v1:address4></v1:address4>", "<v1:address4>null</v1:address4>", $req);
                        }
                        if ($keyField == 'address5') {
                            $req = str_replace("<v1:address5>#address5#</v1:address5>", "<v1:address5>null</v1:address5>", $req);
                            $req = str_replace("<v1:address5></v1:address5>", "<v1:address5>null</v1:address5>", $req);
                        }
                        if ($keyField == 'city') {
                            $req = str_replace("<v1:city>#city#</v1:city>", "<v1:city>null</v1:city>", $req);
                            $req = str_replace("<v1:city></v1:city>", "<v1:city>null</v1:city>", $req);
                        }
                        if ($keyField == 'state') {
                            $req = str_replace("<v1:state>#state#</v1:state>", "<v1:state>null</v1:state>", $req);
                            $req = str_replace("<v1:state></v1:state>", "<v1:state>null</v1:state>", $req);
                        }
                        if ($keyField == 'postCode') {
                            $req = str_replace("<v1:postCode>#postCode#</v1:postCode>", "<v1:postCode>null</v1:postCode>", $req);
                            $req = str_replace("<v1:postCode></v1:postCode>", "<v1:postCode>null</v1:postCode>", $req);
                        }
                        if ($keyField == 'country') {
                            $req = str_replace("<v1:country>#country#</v1:country>", "<v1:country>null</v1:country>", $req);
                            $req = str_replace("<v1:country></v1:country>", "<v1:country>null</v1:country>", $req);
                        }
                        if ($keyField == 'designation') {
                            $req = str_replace("<v1:designation>#designation#</v1:designation>", "<v1:designation>null</v1:designation>", $req);
                            $req = str_replace("<v1:designation></v1:designation>", "<v1:designation>null</v1:designation>", $req);
                        }
                        if ($keyField == 'companyName') {
                            $req = str_replace("<v1:companyName>#companyName#</v1:companyName>", "<v1:companyName>null</v1:companyName>", $req);
                            $req = str_replace("<v1:companyName></v1:companyName>", "<v1:companyName>null</v1:companyName>", $req);
                        }
                    }
                    /*  Transaction Profile Update (A) Issue IDs UAT : 890  */
                    if ($issue_id == 890 && $api_id == 16) {
                        if ($keyField == 'tpCashDepTranCnt') {
                            $tpTotlDepTranCnt += $configVal;
                        }
                        if ($keyField == 'tpXferDepTranCnt') {
                            $tpTotlDepTranCnt += $configVal;
                        }
                        if ($keyField == 'tpRemtDepTranCnt') {
                            $tpTotlDepTranCnt += $configVal;
                        }
                        if ($keyField == 'tpCashDepTranAmtHigh') {
                            $tpTotlDepTranAmtHigh += $configVal;
                        }
                        if ($keyField == 'tpXferDepTranAmtHigh') {
                            $tpTotlDepTranAmtHigh += $configVal;
                        }
                        if ($keyField == 'tpRemtDepTranAmtHigh') {
                            $tpTotlDepTranAmtHigh += $configVal;
                        }
                        if ($keyField == 'tpCashDepTranAmtTot') {
                            $tpTotlDepTranAmtTot += $configVal;
                        }
                        if ($keyField == 'tpXferDepTranAmtTot') {
                            $tpTotlDepTranAmtTot += $configVal;
                        }
                        if ($keyField == 'tpRemtDepTranAmtTot') {
                            $tpTotlDepTranAmtTot += $configVal;
                        }
                        if ($keyField == 'tpRemtDepTranAmtTot') {
                            $tpRemtDepTranAmtTot = !empty($configVal) ? $configVal : 0;
                        }
                        if ($keyField == 'tpRemtWdrTranAmtTot') {
                            $tpRemtWdrTranAmtTot = !empty($configVal) ? $configVal : 0;
                        }
                        // if ($keyField == 'tpCashWdrTranCnt') {
                        //     $tpTotlWdrTranCnt += $configVal;
                        // }
                        // if ($keyField == 'tpXferWdrTranCnt') {
                        //     $tpTotlWdrTranCnt += $configVal;
                        // }
                        // if ($keyField == 'tpRemtWdrTranCnt') {
                        //     $tpTotlWdrTranCnt += $configVal;
                        // }
                        if ($keyField == 'tpCashWdrTranAmtHigh') {
                            $tpTotlWdrTranAmtHigh += $configVal;
                        }
                        if ($keyField == 'tpXferWdrTranAmtHigh') {
                            $tpTotlWdrTranAmtHigh += $configVal;
                        }
                        if ($keyField == 'tpRemtWdrTranAmtHigh') {
                            $tpTotlWdrTranAmtHigh += $configVal;
                        }
                        if ($keyField == 'tpCashWdrTranAmtTot') {
                            $tpTotlWdrTranAmtTot += $configVal;
                        }
                        if ($keyField == 'tpXferWdrTranAmtTot') {
                            $tpTotlWdrTranAmtTot += $configVal;
                        }
                        if ($keyField == 'tpRemtWdrTranAmtTot') {
                            $tpTotlWdrTranAmtTot += $configVal;
                        }
                    }

                }
                $req = str_replace("<v1:tpTotlDepTranCnt>#tpTotlDepTranCnt#</v1:tpTotlDepTranCnt>", '<v1:tpTotlDepTranCnt>'. $tpTotlDepTranCnt .'</v1:tpTotlDepTranCnt>', $req);
                $req = str_replace("<v1:tpTotlDepTranAmtHigh>#tpTotlDepTranAmtHigh#</v1:tpTotlDepTranAmtHigh>", '<v1:tpTotlDepTranAmtHigh>'. $tpTotlDepTranAmtHigh .'</v1:tpTotlDepTranAmtHigh>', $req);
                $req = str_replace("<v1:tpTotlDepTranAmtTot>#tpTotlDepTranAmtTot#</v1:tpTotlDepTranAmtTot>", '<v1:tpTotlDepTranAmtTot>'. $tpTotlDepTranAmtTot .'</v1:tpTotlDepTranAmtTot>', $req);
                $req = str_replace("<v1:tpTotlWdrTranCnt>#tpTotlWdrTranCnt#</v1:tpTotlWdrTranCnt>", '<v1:tpTotlWdrTranCnt>0</v1:tpTotlWdrTranCnt>', $req);
                $req = str_replace("<v1:tpTotlWdrTranAmtHigh>#tpTotlWdrTranAmtHigh#</v1:tpTotlWdrTranAmtHigh>", '<v1:tpTotlWdrTranAmtHigh>0</v1:tpTotlWdrTranAmtHigh>', $req);
                $req = str_replace("<v1:tpTotlWdrTranAmtTot>#tpTotlWdrTranAmtTot#</v1:tpTotlWdrTranAmtTot>", '<v1:tpTotlWdrTranAmtTot>'. $tpTotlWdrTranAmtTot .'</v1:tpTotlWdrTranAmtTot>', $req);
                $tpAsonDate = now()->format('d-m-Y');
                $req = str_replace("#tpAsonDate#", $tpAsonDate, $req);

                foreach ($issueAPICommonConfig as $ikeyField => $commConfigVal) {
                    $req = str_replace($ikeyField, $commConfigVal, $req);
                }
                /*  Acc Lvl API ID UAT : 16  */
                if ($api_id == 16) {
                    $accRes = $this->accInquiryApiResponse(['issue_id' => $issue_id, 'acc_no' => $account_number, 'ref_no' => $ref_no, 'cif_no' => $cif_no]);
                    if (!empty($accRes)) {
                        $req = str_replace('#acctStmtNxtPrintDt#', $acctStmtNxtPrintDt, $req);
                        $req = str_replace('#name#', $accRes['name'], $req);
                        $req = str_replace('#schmCode#', $accRes['schmCode'], $req);
                        $req = str_replace('#schmType#', $accRes['schmType'], $req);
                        $req = str_replace('#acctCurr#', $accRes['acctInqAcctCurr'], $req);
                        $req = str_replace('#branchId#', $accRes['branchId'], $req);
                        $req = str_replace('#acctName#', $accRes['acctName'], $req);
                        $req = str_replace('#acctShortName#', $accRes['acctShortName'], $req);
                        $req = str_replace('#acctStmtMode#', $accRes['acctStmtMode'], $req);
                        $req = str_replace('#cal#', 'G', $req);
                        $req = str_replace('#type#', 'H', $req);
                        $req = str_replace('#startDt#', '31', $req);
                        $req = str_replace('#holStat#', 'P', $req);
                        $req = str_replace('#weekDay#', '0', $req);
                        $req = str_replace('#weekNum#', '', $req);
                        if (!empty($data['relEmailAddress'])) {
                            $req = str_replace('#despatchMode#', 'A', $req);
                        } else {
                            $req = str_replace('#despatchMode#', 'P', $req);
                        }
                        $req = str_replace('#modeOfOperCode#', $accRes['modeOfOperCode'], $req);
                        $req = str_replace('#wTaxAmountScopeFlg#', 'P', $req);
                        $req = str_replace('#atmFlg#', $accRes['atmFlg'], $req);
                        $req = str_replace('#cenBkSectorCode#', $accRes['cenBkSectorCode'], $req);
                        $req = str_replace('#norPsoUnitId#', $accRes['norPsoUnitId'], $req);
                        $req = str_replace('#monitoringPsoUnitId#', $accRes['monitoringPsoUnitId'], $req);
                        $req = str_replace('#ccCode#', $accRes['ccCode'], $req);
                        $req = str_replace('#tpCashDepTranCnt#', 0, $req);
                        $req = str_replace('#tpCashDepTranAmtHigh#', 0, $req);
                        $req = str_replace('#tpCashDepTranAmtTot#', $accRes['tpCashDepTranAmtTot'], $req);
                        $req = str_replace('#tpXferDepTranCnt#', 0, $req);
                        $req = str_replace('#tpXferDepTranAmtHigh#', 0, $req);
                        $req = str_replace('#tpXferDepTranAmtTot#', $accRes['tpXferDepTranAmtTot'], $req);
                        $req = str_replace('#tpTotlDepTranCnt#', 0, $req);
                        $req = str_replace('#tpTotlDepTranAmtHigh#', 0, $req);
                        $req = str_replace('#tpTotlDepTranAmtTot#', $accRes['tpTotlDepTranAmtTot'], $req);
                        $req = str_replace('#tpCashWdrTranCnt#', 0, $req);
                        $req = str_replace('#tpCashWdrTranAmtHigh#', 0, $req);
                        $req = str_replace('#tpCashWdrTranAmtTot#', $accRes['tpCashWdrTranAmtTot'], $req);
                        $req = str_replace('#tpXferWdrTranCnt#', 0, $req);
                        $req = str_replace('#tpXferWdrTranAmtHigh#', 0, $req);
                        $req = str_replace('#tpXferWdrTranAmtTot#', $accRes['tpXferWdrTranAmtTot'], $req);
                        $req = str_replace('#tpTotlWdrTranCnt#', 0, $req);
                        $req = str_replace('#tpTotlWdrTranAmtHigh#', 0, $req);
                        $req = str_replace('#tpRemtDepTranAmtTot#', $tpRemtDepTranAmtTot, $req);
                        $req = str_replace('#tpRemtWdrTranAmtTot#', $tpRemtWdrTranAmtTot, $req);
                        $req = str_replace('#tpTotlWdrTranAmtTot#', $accRes['tpTotlWdrTranAmtTot'], $req);
                        /*  Nominee Update Issue ID UAT 544  */
                        if ($issue_id == 544) {
                            if (!empty($accRes['nomineeinfo'])) {
                                foreach($accRes['nomineeinfo'] AS $nomineeInfo) {
                                    if (trim($nomineeInfo['regNum']) != '') {
                                        $extraNode .= '<v1:nomineeInfoDetails>
                                                  <v1:regNum>' . $nomineeInfo['regNum'] . '</v1:regNum>
                                                  <v1:nomineeName>' . $nomineeInfo['nomineeName'] . '</v1:nomineeName>
                                                  <v1:relType>' . $nomineeInfo['relType'] . '</v1:relType>
                                                  <v1:NomineeContactInfo>
                                                     <v1:nomineeTelephoneNumDetails>
                                                        <v1:nomineeTelephoneNum>' . $nomineeInfo['nomineeTelephoneNum'] . '</v1:nomineeTelephoneNum>
                                                     </v1:nomineeTelephoneNumDetails>
                                                     <v1:nomineeEmailAddr>' . $nomineeInfo['nomineeEmailAddr'] . '</v1:nomineeEmailAddr>
                                                     <v1:nomineeFaxNum>' . $nomineeInfo['nomineeFaxNum'] . '</v1:nomineeFaxNum>
                                                     <v1:nomineeTelexNum>' . $nomineeInfo['nomineeTelexNum'] . '</v1:nomineeTelexNum>
                                                     <v1:nomineePostAddrdetails>
                                                        <v1:nomineeAddr1>' . $nomineeInfo['nomineeAddressTypeAddress1'] . '</v1:nomineeAddr1>
                                                        <v1:nomineeAddr2>' . $nomineeInfo['nomineeAddressTypeAddress2'] . '</v1:nomineeAddr2>
                                                        <v1:nomineeAddr3>' . $nomineeInfo['nomineeAddressTypeAddress3'] . '</v1:nomineeAddr3>
                                                        <v1:nomineeCity>' . $nomineeInfo['nomineeAddressTypeCity'] . '</v1:nomineeCity>
                                                        <v1:nomineeStateProv>' . $nomineeInfo['nomineeAddressTypeStateProv'] . '</v1:nomineeStateProv>
                                                        <v1:nomineePostalCode>' . $nomineeInfo['nomineeAddressTypePostalCode'] . '</v1:nomineePostalCode>
                                                        <v1:nomineeCountry>' . $nomineeInfo['nomineeAddressTypeCountry'] . '</v1:nomineeCountry>
                                                        <v1:nomineeAddrType>' . $nomineeInfo['nomineeAddrType'] . '</v1:nomineeAddrType>
                                                     </v1:nomineePostAddrdetails>
                                                  </v1:NomineeContactInfo>
                                                  <v1:nomineeMinorFlg>' . $nomineeInfo['nomineeMinorFlg'] . '</v1:nomineeMinorFlg>
                                                  <v1:nomineeBirthDt>' . $nomineeInfo['nomineeBirthDt'] . '</v1:nomineeBirthDt>
                                                  <v1:nomineePercentValue>' . $nomineeInfo['nomineePercent'] . '</v1:nomineePercentValue>
                                                  <v1:recDelFlg>Y</v1:recDelFlg>
                                                  <v1:guardianInfoDetails>
                                                     <v1:guardianCode>' . $nomineeInfo['guardianCode'] . '</v1:guardianCode>
                                                     <v1:guardianName>' . $nomineeInfo['guardianName'] . '</v1:guardianName>
                                                     <v1:guardianContactInfoDetails>
                                                        <v1:guardianTelephoneNum>' . $nomineeInfo['guardianTelephoneNum'] . '</v1:guardianTelephoneNum>
                                                     </v1:guardianContactInfoDetails>
                                                     <v1:guardianEmailAddr>' . $nomineeInfo['guardianEmailAddr'] . '</v1:guardianEmailAddr>
                                                     <v1:guardianFaxNum>' . $nomineeInfo['guardianFaxNum'] . '</v1:guardianFaxNum>
                                                     <v1:guardianTelexNum>' . $nomineeInfo['guardianTelexNum'] . '</v1:guardianTelexNum>
                                                     <v1:guardianPostAddrDetails>
                                                        <v1:guardianAddr1>' . $nomineeInfo['guardianAddressTypeAddress1'] . '</v1:guardianAddr1>
                                                        <v1:guardianAddr2>' . $nomineeInfo['guardianAddressTypeAddress2'] . '</v1:guardianAddr2>
                                                        <v1:guardianAddr3>' . $nomineeInfo['guardianAddressTypeAddress3'] . '</v1:guardianAddr3>
                                                        <v1:guardianCity>' . $nomineeInfo['guardianAddressTypeCity'] . '</v1:guardianCity>
                                                        <v1:guardianStateProv>' . $nomineeInfo['guardianAddressTypeStateProv'] . '</v1:guardianStateProv>
                                                        <v1:guardianPostalCode>' . $nomineeInfo['guardianAddressTypePostalCode'] . '</v1:guardianPostalCode>
                                                        <v1:guardianCountry>' . $nomineeInfo['guardianAddressTypeCountry'] . '</v1:guardianCountry>
                                                        <v1:guardianAddrType>' . $nomineeInfo['guardianAddrType'] . '</v1:guardianAddrType>
                                                     </v1:guardianPostAddrDetails>
                                                  </v1:guardianInfoDetails>
                                               </v1:nomineeInfoDetails>';
                                    }
                                }
                            }
                        }
                    }
                    $extraNode = trim(preg_replace('/\s\s+/', ' ', $extraNode));
                    $req = str_replace("<extraNode>extraNode</extraNode>", $extraNode, $req);
                    $req = cifApiNullExtractorAcc($req, $extraFieldWithIssueConfig);
                } elseif ($api_id == 18) {
                /*  Lien Update Issue ID 893 &  API ID UAT : 18  */
                    $accRes = $this->accInquiryApiResponse(['issue_id' => $issue_id, 'acc_no' => $account_number, 'ref_no' => $ref_no, 'cif_no' => $cif_no]);
                    if (!empty($accRes)) {
                        $req = str_replace('#name#', $accRes['name'], $req);
                        $req = str_replace('#schmCode#', $accRes['schmCode'], $req);
                        $req = str_replace('#schmType#', $accRes['schmType'], $req);
                        $req = str_replace('#acctCurr#', $accRes['acctCurr'], $req);
                        $req = str_replace('#currencyCode#', $accRes['acctCurr'], $req);
                        $req = str_replace('#bankId#', $accRes['bankId'], $req);
                        $req = str_replace('#branchId#', $accRes['branchId'], $req);
                        $req = str_replace('#branchName#', $accRes['branchName'], $req);
                        $req = str_replace('#originalGlobalTransactionId#', '', $req);
                        $req = str_replace('#originalTimestamp#', '', $req);
                        $req = str_replace('#addr1#', $accRes['addr1'], $req);
                        $req = str_replace('#addr2#', $accRes['addr2'], $req);
                        $req = str_replace('#addr3#', $accRes['addr3'], $req);
                        $req = str_replace('#city#', $accRes['city'], $req);
                        $req = str_replace('#stateProv#', $accRes['stateProv'], $req);
                        $req = str_replace('#postalCode#', $accRes['postalCode'], $req);
                        $req = str_replace('#country#', $accRes['country'], $req);
                        $req = str_replace('#addrType#', $accRes['addrType'], $req);
                    }
                    $req = cifApiNullExtractorAcc($req, $extraFieldWithIssueConfig);
                } else {
                    $req = cifApiNullExtractor($req);
                }
                /*  Cardpro addrline missing Address Change Issue ID UAT 1019,1078  */
                if (($issue_id == 1019 || $issue_id == 1078) && ($api_id == 14)) {
                    $req = str_replace('`', '#', $req);
                }
                $req = str_replace("cif_api_password", $password, $req);

                /*  Name change Issue ID UAT : 128,1080  */
                if (($issue_id == 128 || $issue_id == 1080) && ($api_id == 8)) {
                    if (!empty($firstname)) {
                        $req = str_replace("<v1:firstName>firstName</v1:firstName>", '<v1:firstName>'.$firstname.'</v1:firstName>', $req);
                    } else {
                        $req = str_replace("<v1:firstName>firstName</v1:firstName>", '<v1:firstName>null</v1:firstName>', $req);
                    }
                    if (!empty($middlename)) {
                        $req = str_replace("<v1:middleName>middleName</v1:middleName>", '<v1:middleName>'.$middlename.'</v1:middleName>', $req);
                    } else {
                        $req = str_replace("<v1:middleName>middleName</v1:middleName>", '<v1:middleName>null</v1:middleName>', $req);
                    }
                    if (!empty($lastname)) {
                        $req = str_replace("<v1:lastName>lastName</v1:lastName>", '<v1:lastName>'.$lastname.'</v1:lastName>', $req);
                    } else {
                        $req = str_replace("<v1:lastName>lastName</v1:lastName>", '', $req);
                    }
                    if (!empty($shortName)) {
                        $req = str_replace("<v1:shortName>shortName</v1:shortName>", '<v1:shortName>'.$shortName.'</v1:shortName>', $req);
                    } else {
                        $req = str_replace("<v1:shortName>shortName</v1:shortName>", '', $req);
                    }
                }
                /*  Mobile Phone Change Issue ID UAT 119,518,1081  */
                if (($issue_id == 119 || $issue_id == 518 || $issue_id == 1081) && ($api_id == 8)) {
                    foreach ($extraNodeArray as $k => $v) {
                        if ($k == 'phoneEmailType') {
                            $type = $v;
                        }
                        if ($k == 'phoneNumCountryCode') {
                            $cun = $v;
                        }
                        if ($k == 'phoneNumCityCode') {
                            $cty = $v;
                        }
                        if ($k == 'phoneNum') {
                            $num = $v;
                        }
                    }
                    if ($type == 'CELLPH') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>CELLPH</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>COMMPH1</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>Y</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } elseif ($type == 'COMMPH1') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>CELLPH</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>COMMPH1</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>Y</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } elseif ($type == 'COMMPH2') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>COMMPH2</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } elseif ($type == 'CPPH1') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>CPPH1</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } elseif ($type == 'CPPH2') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>CPPH2</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } elseif ($type == 'FORGNPHN01') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>' . $type . '</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>' . $cun . '</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>' . $cty . '</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>Y</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } else {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>PHONE</v1:phoneOrEmail>
                                        <v1:phoneEmailType>' . $type . '</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:phoneNum>' . $num . '</v1:phoneNum>
                                        <v1:phoneNumCountryCode>88</v1:phoneNumCountryCode>
                                        <v1:phoneNumCityCode>00</v1:phoneNumCityCode>
                                        <v1:phoneNumLocalCode>' . $num . '</v1:phoneNumLocalCode>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    }
                    $extraNode = trim(preg_replace('/\s\s+/', ' ', $extraNode));
                    $req = str_replace("<extraNode>extraNode</extraNode>", $extraNode, $req);
                }
                /*  Email Address Change Issue ID UAT 1077,120  */
                if (($issue_id == 1077 || $issue_id == 120) && ($api_id == 8)) {
                    foreach ($extraNodeArray as $k => $v) {
                        if ($k == 'phoneEmailType') {
                            $type = $v;
                        }
                        if ($k == 'emailInfo') {
                            $eml = $v;
                        }
                    }
                    if ($type == 'COMMEML') {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>EMAIL</v1:phoneOrEmail>
                                        <v1:phoneEmailType>' . $type . '</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:emailInfo>' . $eml . '</v1:emailInfo>
                                        <v1:prefFlag>Y</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    } else {
                        $extraNode .= '<v1:phoneEmailDetailsList>';
                        $extraNode .= '<v1:phoneEmailDetails>
                                        <v1:phoneOrEmail>EMAIL</v1:phoneOrEmail>
                                        <v1:phoneEmailType>' . $type . '</v1:phoneEmailType>
                                        <v1:startDt>' . $sourceTimestamp . '</v1:startDt>
                                        <v1:emailInfo>' . $eml . '</v1:emailInfo>
                                        <v1:prefFlag>N</v1:prefFlag>
                                    </v1:phoneEmailDetails>';
                        $extraNode .= '</v1:phoneEmailDetailsList>';
                    }
                    $extraNode = trim(preg_replace('/\s\s+/', ' ', $extraNode));
                    $req = str_replace("<extraNode>extraNode</extraNode>", $extraNode, $req);
                }
                /*  Address Change Issue ID UAT 1019,1078  */
                if (($issue_id == 1019 || $issue_id == 1078) && ($api_id == 8)) {
                    foreach ($extraNodeArray as $k => $v) {
                        if ($k == 'addrCategory') {
                            $type = $v;
                        }
                        if ($k == 'prefAddr') {
                            $pref = $v;
                        }
                        if ($k == 'addrLine1') {
                            $add1 = $v;
                        }
                        if ($k == 'addrLine2') {
                            $add2 = $v;
                        }
                        if ($k == 'addrLine3') {
                            $add3 = $v;
                        }
                        if ($k == 'country') {
                            $cun = $v;
                        }
                        if ($k == 'city') {
                            $cty = $v;
                        }
                        if ($k == 'state') {
                            $stat = $v;
                        }
                        if ($k == 'postalCode') {
                            $posCode = $v;
                        }
                        if ($k == 'town') {
                            $twn = $v;
                        }
                    }
                    if (empty($add1)) {
                        $add1 = 'null';
                    }
                    if (empty($add2)) {
                        $add2 = 'null';
                    }
                    if (empty($add3)) {
                        $add3 = 'null';
                    }
                    if ($pref == 'Y') {
                        $extraNode .= '<v1:addressDetailsList>';
                        $extraNode .= '<v1:addressDetails>
                                        <v1:addrLine1>' . $add1 . '</v1:addrLine1>
                                        <v1:addrLine2>' . $add2 . '</v1:addrLine2>
                                        <v1:addrLine3>' . $add3 . '</v1:addrLine3>
                                        <v1:addrStartDt>' . $sourceTimestamp . '</v1:addrStartDt>
                                        <v1:addrCategory>' . $type . '</v1:addrCategory>
                                        <v1:freeTextLabel>' . $type . '</v1:freeTextLabel>
                                        <v1:city>' . $cty . '</v1:city>
                                        <v1:country>' . $cun . '</v1:country>
                                        <v1:holdMailFlag>N</v1:holdMailFlag>
                                        <v1:prefAddr>N</v1:prefAddr>
                                        <v1:prefFormat>FREE_TEXT_FORMAT</v1:prefFormat>
                                        <v1:state>' . $stat . '</v1:state>
                                        <v1:town>' . $twn . '</v1:town>
                                        <v1:postalCode>' . $posCode . '</v1:postalCode>
                                    </v1:addressDetails>';
                        $extraNode .= '<v1:addressDetails>
                                        <v1:addrLine1>' . $add1 . '</v1:addrLine1>
                                        <v1:addrLine2>' . $add2 . '</v1:addrLine2>
                                        <v1:addrLine3>' . $add3 . '</v1:addrLine3>
                                        <v1:addrStartDt>' . $sourceTimestamp . '</v1:addrStartDt>
                                        <v1:addrCategory>Mailing</v1:addrCategory>
                                        <v1:freeTextLabel>Mailing</v1:freeTextLabel>
                                        <v1:city>' . $cty . '</v1:city>
                                        <v1:country>' . $cun . '</v1:country>
                                        <v1:holdMailFlag>N</v1:holdMailFlag>
                                        <v1:prefAddr>Y</v1:prefAddr>
                                        <v1:prefFormat>FREE_TEXT_FORMAT</v1:prefFormat>
                                        <v1:state>' . $stat . '</v1:state>
                                        <v1:town>' . $twn . '</v1:town>
                                        <v1:postalCode>' . $posCode . '</v1:postalCode>
                                    </v1:addressDetails>';
                        $extraNode .= '</v1:addressDetailsList>';
                    } else {
                        $extraNode .= '<v1:addressDetailsList>';
                        $extraNode .= '<v1:addressDetails>
                                        <v1:addrLine1>' . $add1 . '</v1:addrLine1>
                                        <v1:addrLine2>' . $add2 . '</v1:addrLine2>
                                        <v1:addrLine3>' . $add3 . '</v1:addrLine3>
                                        <v1:addrStartDt>' . $sourceTimestamp . '</v1:addrStartDt>
                                        <v1:addrCategory>' . $type . '</v1:addrCategory>
                                        <v1:freeTextLabel>' . $type . '</v1:freeTextLabel>
                                        <v1:city>' . $cty . '</v1:city>
                                        <v1:country>' . $cun . '</v1:country>
                                        <v1:holdMailFlag>N</v1:holdMailFlag>
                                        <v1:prefAddr>N</v1:prefAddr>
                                        <v1:prefFormat>FREE_TEXT_FORMAT</v1:prefFormat>
                                        <v1:state>' . $stat . '</v1:state>
                                        <v1:town>' . $twn . '</v1:town>
                                        <v1:postalCode>' . $posCode . '</v1:postalCode>
                                    </v1:addressDetails>';
                        $extraNode .= '</v1:addressDetailsList>';
                    }
                    $extraNode = trim(preg_replace('/\s\s+/', ' ', $extraNode));
                    $req = str_replace("<extraNode>extraNode</extraNode>", $extraNode, $req);
                }
                /*  Foreign Address Change Issue ID UAT 1091  */
                if ($issue_id == 1091 && $api_id == 8) {
                    foreach ($extraNodeArray as $k => $v) {
                        if ($k == 'prefAddr') {
                            $prefAddr = $v;
                        }
                        if ($k == 'placeOfIssue') {
                            $placeOfIssue = $v;
                        }
                        if ($k == 'addrLine1') {
                            $add1 = $v;
                        }
                        if ($k == 'addrLine2') {
                            $add2 = $v;
                        }
                        if ($k == 'addrLine3') {
                            $add3 = $v;
                        }
                        if ($k == 'country') {
                            $cun = $v;
                        }
                        if ($k == 'city') {
                            $cty = $v;
                        }
                        if ($k == 'state') {
                            $stat = $v;
                        }
                        if ($k == 'postalCode') {
                            $posCode = $v;
                        }
                        if ($k == 'countryOfIssue') {
                            $countryOfIssue = $v;
                        }
                        if ($k == 'nrecountryOfIssue') {
                            $nrecountryOfIssue = $v;
                        }
                        if ($k == 'docExpDt') {
                            $docExpDt = $v;
                        }
                        if ($k == 'docIssueDt') {
                            $docIssueDt = $v;
                        }
                        if ($k == 'refNum') {
                            $refNum = $v;
                        }
                        if ($k == 'nredocIssueDt') {
                            $nredocIssueDt = $v;
                        }
                        if ($k == 'nreplaceOfIssue') {
                            $nreplaceOfIssue = $v;
                        }
                        if ($k == 'nrerefNum') {
                            $nrerefNum = $v;
                        }
                        if ($k == 'isCustNRE') {
                            $isCustNRE = $v;
                        }
                        if ($k == 'nreBecomingDt') {
                            $nreBecomingDt = $v;
                        }
                    }
                    if (empty($add1)) {
                        $add1 = 'null';
                    }
                    if (empty($add2)) {
                        $add2 = 'null';
                    }
                    if (empty($add3)) {
                        $add3 = 'null';
                    }
                    $extraNode .= '<v1:isCustNRE>' . $isCustNRE . '</v1:isCustNRE>';
                    $extraNode .= '<v1:nreBecomingDt>' . $nreBecomingDt . '</v1:nreBecomingDt>';
                    if ($prefAddr == 'Y') {
                        $extraNode .= '<v1:addressDetailsList>';
                        $extraNode .= '<v1:addressDetails>
                                        <v1:addrLine1>' . $add1 . '</v1:addrLine1>
                                        <v1:addrLine2>' . $add2 . '</v1:addrLine2>
                                        <v1:addrLine3>' . $add3 . '</v1:addrLine3>
                                        <v1:addrStartDt>' . $sourceTimestamp . '</v1:addrStartDt>
                                        <v1:addrCategory>NRERelative</v1:addrCategory>
                                        <v1:freeTextLabel>NRERelative</v1:freeTextLabel>
                                        <v1:city>' . $cty . '</v1:city>
                                        <v1:country>' . $cun . '</v1:country>
                                        <v1:holdMailFlag>N</v1:holdMailFlag>
                                        <v1:prefAddr>N</v1:prefAddr>
                                        <v1:prefFormat>FREE_TEXT_FORMAT</v1:prefFormat>
                                        <v1:state>' . $stat . '</v1:state>
                                        <v1:postalCode>' . $posCode . '</v1:postalCode>
                                    </v1:addressDetails>';
                        $extraNode .= '<v1:addressDetails>
                                        <v1:addrLine1>' . $add1 . '</v1:addrLine1>
                                        <v1:addrLine2>' . $add2 . '</v1:addrLine2>
                                        <v1:addrLine3>' . $add3 . '</v1:addrLine3>
                                        <v1:addrStartDt>' . $sourceTimestamp . '</v1:addrStartDt>
                                        <v1:addrCategory>Mailing</v1:addrCategory>
                                        <v1:freeTextLabel>Mailing</v1:freeTextLabel>
                                        <v1:city>' . $cty . '</v1:city>
                                        <v1:country>' . $cun . '</v1:country>
                                        <v1:holdMailFlag>N</v1:holdMailFlag>
                                        <v1:prefAddr>Y</v1:prefAddr>
                                        <v1:prefFormat>FREE_TEXT_FORMAT</v1:prefFormat>
                                        <v1:state>' . $stat . '</v1:state>
                                        <v1:postalCode>' . $posCode . '</v1:postalCode>
                                    </v1:addressDetails>';
                        $extraNode .= '</v1:addressDetailsList>';
                    } else {
                        $extraNode .= '<v1:addressDetailsList>';
                        $extraNode .= '<v1:addressDetails>
                                        <v1:addrLine1>' . $add1 . '</v1:addrLine1>
                                        <v1:addrLine2>' . $add2 . '</v1:addrLine2>
                                        <v1:addrLine3>' . $add3 . '</v1:addrLine3>
                                        <v1:addrStartDt>' . $sourceTimestamp . '</v1:addrStartDt>
                                        <v1:addrCategory>NRERelative</v1:addrCategory>
                                        <v1:freeTextLabel>NRERelative</v1:freeTextLabel>
                                        <v1:city>' . $cty . '</v1:city>
                                        <v1:country>' . $cun . '</v1:country>
                                        <v1:holdMailFlag>N</v1:holdMailFlag>
                                        <v1:prefAddr>N</v1:prefAddr>
                                        <v1:prefFormat>FREE_TEXT_FORMAT</v1:prefFormat>
                                        <v1:state>' . $stat . '</v1:state>
                                        <v1:postalCode>' . $posCode . '</v1:postalCode>
                                    </v1:addressDetails>';
                        $extraNode .= '</v1:addressDetailsList>';
                    }
                    $extraNode .= '<v1:entityDocDetailsList>';
                    $extraNode .= '<v1:entityDocDetails>
                                    <v1:countryOfIssue>' . $countryOfIssue . '</v1:countryOfIssue>
                                    <v1:docCode>PP</v1:docCode>
                                    <v1:docExpDt>' . $docExpDt . '</v1:docExpDt>
                                    <v1:docIssueDt>' . $docIssueDt . '</v1:docIssueDt>
                                    <v1:docTypeCode>RCID</v1:docTypeCode>
                                    <v1:docTypeDesc>RCIF-ID DOC</v1:docTypeDesc>
                                    <v1:entityType>CIFRetCust</v1:entityType>
                                    <v1:identificationType>Passport Number</v1:identificationType>
                                    <v1:placeOfIssue>' . $placeOfIssue . '</v1:placeOfIssue>
                                    <v1:refNum>' . $refNum . '</v1:refNum>
                                    <v1:status>Received</v1:status>
                                    <v1:preferredUniqueId>Y</v1:preferredUniqueId>
                                    <v1:type>CIF</v1:type>
                                    <v1:isMandatory>N</v1:isMandatory>
                                    <v1:docDescr>PASSPORTNO</v1:docDescr>
                                </v1:entityDocDetails>';
                    // $extraNode .= '<v1:entityDocDetails>
                    //                 <v1:countryOfIssue>' . $nrecountryOfIssue . '</v1:countryOfIssue>
                    //                 <v1:docCode>NID</v1:docCode>
                    //                 <v1:docIssueDt>' . $nredocIssueDt . '</v1:docIssueDt>
                    //                 <v1:docRmks>National Card Number</v1:docRmks>
                    //                 <v1:docTypeCode>RCID</v1:docTypeCode>
                    //                 <v1:docTypeDesc>RCIF-ID DOC</v1:docTypeDesc>
                    //                 <v1:entityType>CIFRetCust</v1:entityType>
                    //                 <v1:identificationType>National id card</v1:identificationType>
                    //                 <v1:placeOfIssue>' . $nreplaceOfIssue . '</v1:placeOfIssue>
                    //                 <v1:refNum>' . $nrerefNum . '</v1:refNum>
                    //                 <v1:status>Received</v1:status>
                    //                 <v1:preferredUniqueId>Y</v1:preferredUniqueId>
                    //                 <v1:type>CIF</v1:type>
                    //                 <v1:idIssuedOrganisation>GOVT</v1:idIssuedOrganisation>
                    //                 <v1:scanRequired>Y</v1:scanRequired>
                    //                 <v1:isMandatory>Y</v1:isMandatory>
                    //                 <v1:docDescr>NationalID</v1:docDescr>
                    //             </v1:entityDocDetails>';
                    $extraNode .= '</v1:entityDocDetailsList>';
                    $extraNode = trim(preg_replace('/\s\s+/', ' ', $extraNode));
                    $req = str_replace("<extraNode>extraNode</extraNode>", $extraNode, $req);
                }
                /*  Address change Data Nullify Issue IDs UAT : 1019,1078  CardPro API ID UAT : 14  */
                if (($issue_id == 1019 || $issue_id == 1078) && ($api_id == 14)) {
                    $req = str_replace("<v1:address1>null</v1:address1>", "<v1:address1> </v1:address1>", $req);
                    $req = str_replace("<v1:address2>null</v1:address2>", "<v1:address2> </v1:address2>", $req);
                    $req = str_replace("<v1:address3>null</v1:address3>", "<v1:address3> </v1:address3>", $req);
                    $req = str_replace("<v1:address4>null</v1:address4>", "<v1:address4> </v1:address4>", $req);
                    $req = str_replace("<v1:address5>null</v1:address5>", "<v1:address5> </v1:address5>", $req);
                    $req = str_replace("<v1:city>null</v1:city>", "<v1:city> </v1:city>", $req);
                    $req = str_replace("<v1:state>null</v1:state>", "<v1:state> </v1:state>", $req);
                    $req = str_replace("<v1:postCode>null</v1:postCode>", "<v1:postCode> </v1:postCode>", $req);
                    $req = str_replace("<v1:country>null</v1:country>", "<v1:country> </v1:country>", $req);
                    $req = str_replace("<v1:designation>null</v1:designation>", "<v1:designation> </v1:designation>", $req);
                    $req = str_replace("<v1:companyName>null</v1:companyName>", "<v1:companyName> </v1:companyName>", $req);
                }

                /**** Remove Extra Nodes before sending API ****/
                $req = str_replace("<extraNode>extraNode</extraNode>", '', $req);
                $req = str_replace("<v1:tdsTbl>TDS-0</v1:tdsTbl>", '', $req);
                $req = str_replace("<v1:tdsTbl>.</v1:tdsTbl>", '', $req);
                $req = str_replace("<v1:strText10>0</v1:strText10>", '', $req);
                $req = str_replace("<v1:strText10>.</v1:strText10>", '', $req);

                $tmpArr['api_id'] = $api_id;
                $tmpArr['url'] = $url;
                $tmpArr['request'] = $req;
                $tmpArr['global_transaction_id'] = $global_transaction_id;
                $requestToPush[] = $tmpArr;
            }

            foreach ($requestToPush as $data) {
                $api_id = $data['api_id'];
                $url = $data['url'];
                $xml_post_string = $data['request'];
                $gtid = $data['global_transaction_id'];
                $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 1, 'url' => $url, 'json_node' => $xml_post_string, 'account_number' => $account_number, 'cif_number' => $cif_no, 'global_transaction_id' => $gtid]);

                $headers = array("Content-type: text/xml;charset=\"utf-8\"", "Accept: text/xml", "Cache-Control: no-cache", "Pragma: no-cache", "SOAPAction: corporateCustomerInquiryReq", "Content-length: " . strlen($xml_post_string));
                $msc = microtime(true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $resp = curl_exec($ch);
                curl_close($ch);

                $msc = microtime(true) - $msc;
                $execution_time = number_format($msc, 2);

                if (!empty($resp)) {
                    $doc = new DomDocument();
                    $doc->loadXML($resp);
                    $res = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $resp);
                    $xmlArray = simplexml_load_string($res);
                    $array = json_decode(json_encode($xmlArray), TRUE);
                    $array = array_flatten($array);
                    foreach ($array as $key => $value) {
                        $ke = str_ireplace(['v11', 'v1', 'v2', 'ns1', 'ns2', 'ns3', 'ns4'], '', $key);
                        if ($ke == 'errorCode') {
                            $status_code = $value;
                        }
                        if ($ke == 'errorMessage') {
                            $status_msg = $value;
                        }
                        if ($ke == 'globalTransactionId') {
                            $globalTransactionId = $value;
                        }
                    }
                } else {
                    $status_code = '';
                    $status_msg = 'No API Response';
                    $globalTransactionId = '';
                }
                if (!empty($status_msg)) {
                    /* Finacle API ID UAT : 8 */
                    if ($api_id == 8) {
                        $msg .= '<b>Finacle :</b> ' . $status_msg . '. <br>';
                    } /* CardPro API ID UAT : 13,14 */
                    elseif ($api_id == 13 || $api_id == 14) {
                        $msg .= '<b>Cardpro :</b> ' . $status_msg . '. <br>';
                    } else {
                        $msg .= $status_msg . '. <br>';
                    }
                }
                if ($status_code != '000') {
                    $status[$api_id] = $api_id;
                    $tmp_status = 0;
                }
                $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 2, 'url' => $url, 'json_node' => $resp, 'execution_time' => $execution_time, 'global_transaction_id' => $globalTransactionId, 'account_number' => $account_number, 'cif_number' => $cif_no, 'status_code' => $status_code, 'status_msg' => $status_msg]);
            }
            $refs = $referenceData->failed_retry;
            $referenceData->failed_retry = $refs + 1;
            $subgroup_id = Auth::user()->user_unit->subgroup_info_id;
            $group_info_id = SubgroupInfo::find($subgroup_id);
            if ($tmp_status == 1) {
                $this->audit(['reference_number' => $ref_no, 'unit_id' => $referenceData->unit_id,
                    'group_id' => $group_info_id->group_info_id, 'user_id' => Auth::user()->user_id,
                    'action' => "API Update", 'comments' => 'Success', 'subgroup_id' => $subgroup_id]);
                $response['success'] = 1;
                $referenceData->api_status = 1;
                $referenceData->failed_api = '';
            } else {
                if ($referenceData->failed_retry == 2) {
                    $this->audit(['reference_number' => $ref_no, 'unit_id' => $referenceData->unit_id,
                        'group_id' => $group_info_id->group_info_id, 'user_id' => Auth::user()->user_id,
                        'action' => "API Update Attempted Twice", 'comments' => $msg, 'subgroup_id' => $subgroup_id]);
                    $response['success'] = 2;
                    $response['msg'] = $msg;
                } else {
                    $this->audit(['reference_number' => $ref_no, 'unit_id' => $referenceData->unit_id,
                        'group_id' => $group_info_id->group_info_id, 'user_id' => Auth::user()->user_id,
                        'action' => "API Update Attempted Once", 'comments' => $msg, 'subgroup_id' => $subgroup_id]);
                    $response['success'] = 0;
                    $response['msg'] = $msg;
                    $response['failed_api'] = implode(',', $status);
                    $referenceData->failed_api = implode(',', $status);
                }
            }
            $referenceData->save();
        }
        return response(['msg' => $response['msg'], 'status' => $response['success'], 'failed_api' => $response['failed_api']]);
    }

    public function audit($params = array())
    {
        $commentModel = new Comment;
        $commentModel->reference_number = (!empty($params['reference_number'])) ? $params['reference_number'] : '';
        $commentModel->comments = (!empty($params['comments'])) ? $params['comments'] : '';
        $commentModel->time = strtotime(date('Y-m-d H:i:s'));
        $commentModel->user_id = (!empty($params['user_id'])) ? $params['user_id'] : 0;
        $commentModel->unit_id = (!empty($params['unit_id'])) ? $params['unit_id'] : 0;
        $commentModel->group_id = (!empty($params['group_id'])) ? $params['group_id'] : 0;
        $commentModel->action = (!empty($params['action'])) ? $params['action'] : 'INVALID';
        $commentModel->duration_in_minutes = (!empty($params['duration_in_minutes'])) ? $params['duration_in_minutes'] : 0;
        $commentModel->isapproved = (!empty($params['isapproved'])) ? $params['isapproved'] : 0;
        $commentModel->issendback = (!empty($params['issendback'])) ? $params['issendback'] : 0;
        $commentModel->subgroup_id = (!empty($params['subgroup_id'])) ? $params['subgroup_id'] : 0;
        $commentModel->ip = $this->getClientIp();
        $commentModel->save();
    }

    public function api_update_check(Request $request)
    {
        $status = 0;
        $ids = array();
        $failed_api = '';
        $ref_no = '';
        try {
            $encrypted_ref_no = xss_cleaner($request->ref_no);
            $ref_no = decrypt($encrypted_ref_no);
        } catch (DecryptException $e) {
            return response(['status' => $status, 'ids' => $ids]);
        }
        $referenceModel = new Reference();
        $referenceData = $referenceModel->where('reference_number', $ref_no)->first();
        if (!empty($referenceData)) {
            if ($referenceData->failed_retry == 1) {
                $status = 1;
                $failed_api = $referenceData->failed_api;
            } elseif ($referenceData->failed_retry >= 2) {
                $status = 2;
            }
        }
        $ids = explode(',', $failed_api);
        return response(['status' => $status, 'ids' => $ids]);
    }

    public function isInquiryApi($id = null)
    {
        $exists = DB::table('cif_workflow')
            ->where('cif_workflow.issue_id', $id)
            ->where('cif_workflow.status', 1)
            ->first();
        return !empty($exists) ? 1 : 0;
    }

    public function requestResponsePayload($params = array())
    {
        /*type 1 = CIF Update request,
            2 = CIF Update response,
            3 = Inquiry API request,
            4 = Inquiry API response*/

        $modelName = new CIFRequestResponse;
        $modelName->reference_number = (!empty($params['reference_number'])) ? $params['reference_number'] : '';
        $modelName->account_number = (!empty($params['account_number'])) ? $params['account_number'] : '';
        $modelName->type = (!empty($params['type'])) ? $params['type'] : '';
        $modelName->json_node = (!empty($params['json_node'])) ? $params['json_node'] : '';
        $modelName->url = (!empty($params['url'])) ? $params['url'] : '';
        $modelName->execution_time = (!empty($params['execution_time'])) ? $params['execution_time'] : '';
        $modelName->requested_by = (!empty(Auth::user()->user_id)) ? Auth::user()->user_id : '';
        $modelName->global_transaction_id = (!empty($params['global_transaction_id'])) ? $params['global_transaction_id'] : '';
        $modelName->cif_number = (!empty($params['cif_number'])) ? $params['cif_number'] : '';
        $modelName->status_code = (!empty($params['status_code'])) ? $params['status_code'] : '';
        $modelName->status_msg = (!empty($params['status_msg'])) ? $params['status_msg'] : '';
        $modelName->save();
    }

    public function inquiryApi($issue_id = null, $acc_no = null, $ref_no = null, $cif_no = null)
    {
        $data = "Inquiry data not found, Please Contact with Administrator";
        $status = 0;
        $status_code = '';
        $status_msg = 'No Inquiry API Response';

        $inquiryAll = DB::table('cif_api')
            ->leftJoin('cif_modification_url', 'cif_api.parent_api', 'cif_modification_url.parent_id')
            ->where('cif_api.issue_id', $issue_id)
            ->where('cif_api.type', 2)
            ->where('cif_modification_url.status', 1)
            ->select('cif_modification_url.url', 'cif_modification_url.request', 'cif_modification_url.id')
            ->get();

        if (!empty($inquiryAll)) {
            $data = array();
            foreach ($inquiryAll as $inquiry) {
                $inquiry_id = $inquiry->id;
                $api = $inquiry->request;
                $url = $inquiry->url;
                $sourceTimestamp = now()->format('Y-m-d\TH:i:s.v');
                $password = DynamicAPICredential::where('api', $inquiry_id)->first();
                $password = !empty($password->password) ? decrypt($password->password) : '';
                $global_transaction_id = 'AYS' . now()->format('YmdHisv');
                $api = str_replace("#global_transaction_id#", $global_transaction_id, $api);
                $api = str_replace("cif_api_password", $password, $api);
                $api = str_replace("#sourceTimestamp#", $sourceTimestamp, $api);
                $api = str_replace("#cb_idno#", $acc_no, $api);
                $api = str_replace("#account_number#", $acc_no, $api);
                $api = str_replace("#cif_number#", $cif_no, $api);
                $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 3, 'json_node' => $api, 'account_number' => $acc_no, 'url' => $url, 'cif_number' => $cif_no, 'global_transaction_id' => $global_transaction_id]);

                $headers = array("Content-type: text/xml;charset=\"utf-8\"", "Accept: text/xml", "Cache-Control: no-cache", "Pragma: no-cache", "SOAPAction: corporateCustomerInquiryReq", "Content-length: " . strlen($api));

                $msc = microtime(true);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $api);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($ch);
                curl_close($ch);

                $msc = microtime(true) - $msc;
                $execution_time = number_format($msc, 2);

                if (!empty($response)) {
                    $doc = new DomDocument();
                    $doc->loadXML($response);
                    $res = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
                    $xmlArray = simplexml_load_string($res);
                    $xmlArray = json_decode(json_encode($xmlArray), TRUE);
                    $xmlArray = array_flatten($xmlArray);
                    foreach ($xmlArray as $key => $value) {
                        $ke = str_ireplace(['v11', 'v1', 'v2', 'ns1', 'ns2', 'ns3', 'ns4', 'brac'], '', $key);
                        if ($ke == 'errorCode' || $ke == 'responseCode') {
                            $status_code = $value;
                        }
                        if ($ke == 'errorMessage') {
                            $status_msg = $value;
                        }
                    }

                    if ($status_code == '000') {
                        $resp = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2_$3", $response);
                        $doc = new DomDocument();
                        $doc->loadXML($resp);
                        $xpath = new DomXPath($doc);

                        $commonCfgParentInquiryAll = DB::table('api_common_config_inquiry')
                            ->where('cif_modification_url_id', $inquiry_id)
                            ->where('issue_id', $issue_id)
                            ->where('parent_id', 0)
                            ->where('status', 1)
                            ->get()
                            ->toArray();

                        if (!empty($commonCfgParentInquiryAll)) {
                            $commonCfgParentInquiryAll = json_decode(json_encode($commonCfgParentInquiryAll), true);

                            foreach ($commonCfgParentInquiryAll as $commonCfgParentInquiry) {
                                $parentSerachNodeIdx = $commonCfgParentInquiry['search_idx'];
                                $parentNodeIdx = $commonCfgParentInquiry['node_idx'];
                                $parentNodeValue = $commonCfgParentInquiry['node_value'];

                                $commonCfgChildInquiry = array();
                                if (!empty($commonCfgParentInquiry)) {
                                    $commonCfgParentId = $commonCfgParentInquiry['id'];
                                    $commonCfgChildInquiry = DB::table('api_common_config_inquiry')
                                        ->where('cif_modification_url_id', $inquiry_id)
                                        ->where('issue_id', $issue_id)
                                        ->where('parent_id', $commonCfgParentId)
                                        ->where('status', 1)
                                        ->get()
                                        ->toArray();
                                }

                                if($parentSerachNodeIdx == "ns1_LienDtlsRec") {
                                    $fullLienNode = array();
                                    libxml_use_internal_errors(true);
                                    $lienXML = simplexml_load_string($resp);
                                    $lienXML = json_decode(json_encode($lienXML), true);
                                    if ($lienXML !== false) {
                                        $fullLienNode = arrayNodeSearch($lienXML, $parentSerachNodeIdx);
                                    }
                                    if (!empty($fullLienNode)) {
                                        if (empty($fullLienNode[0])) {
                                            $tmpFullLienNode = $fullLienNode;
                                            $fullLienNode = array();
                                            $fullLienNode[0] = $tmpFullLienNode;
                                        }
                                        foreach ($fullLienNode as $key => $lienNodeVal) {
                                            $counter=0;
                                            if (!empty($commonCfgChildInquiry)) {
                                                $commonCfgChildInquiry = json_decode(json_encode($commonCfgChildInquiry), true);
                                                foreach ($commonCfgChildInquiry as $commonCfgVal) {
                                                    $childNodeIdx = $commonCfgVal['node_idx'];
                                                    $childNodeValue = $commonCfgVal['node_value'];
                                                    if (isset($lienNodeVal[$parentNodeIdx])) {
                                                        if (isset($lienNodeVal[$parentNodeIdx][$childNodeIdx])) {
                                                            $data['lienenqury'][$key][$childNodeValue] = $lienNodeVal[$parentNodeIdx][$childNodeIdx];
                                                        }
                                                    }
                                                }
                                            } else {
                                                if (isset($lienNodeVal[$parentNodeIdx])) {
                                                    if (isset($lienNodeVal[$parentNodeIdx])) {
                                                        $data['lienenqury'][$key][$parentNodeValue] = $lienNodeVal[$parentNodeIdx];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $nodeArray = array();
                                    if (!empty($parentSerachNodeIdx)) {
                                        $nodes = $xpath->query('//' . $parentSerachNodeIdx . '/*');
                                        for ($i = 0; $i < $nodes->length; $i++) {
                                            $node = $nodes->item($i);
                                            $nodeArray[$node->nodeName][] = $node->nodeValue;
                                        }
                                    }

                                    $returnSfEDkey = NULL;
                                    if (!empty($nodeArray[$parentNodeIdx])) {
                                        foreach ($nodeArray[$parentNodeIdx] as $sfEDkey => $searchForExactData) {
                                            if ($searchForExactData == $parentNodeValue) {
                                                $returnSfEDkey = $sfEDkey;
                                                break;
                                            }
                                        }
                                    }

                                    if (!empty($commonCfgChildInquiry)) {
                                        $commonCfgChildInquiry = json_decode(json_encode($commonCfgChildInquiry), true);
                                        foreach ($commonCfgChildInquiry as $commonCfgVal) {
                                            $childNodeIdx = $commonCfgVal['node_idx'];
                                            $childNodeValue = $commonCfgVal['node_value'];
                                            if (!empty($nodeArray)) {
                                                if (isset($nodeArray[$childNodeIdx][$returnSfEDkey])) {
                                                    $data[$childNodeValue] = $nodeArray[$childNodeIdx][$returnSfEDkey];
                                                }
                                            }
                                        }
                                    } else {
                                        if (!empty($parentNodeIdx)) {
                                            $nodes = $xpath->query('//' . $parentNodeIdx);
                                            for ($i = 0; $i < $nodes->length; $i++) {
                                                $node = $nodes->item($i);
                                                $data[$parentNodeValue] = $node->nodeValue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $status = 1;
                    } else {
                        $data = "Inquiry data not found, Please Contact with Administrator";
                    }
                    $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 4, 'json_node' => $response, 'account_number' => $acc_no, 'url' => $url, 'execution_time' => $execution_time, 'cif_number' => $cif_no, 'global_transaction_id' => $global_transaction_id, 'status_code' => $status_code, 'status_msg' => $status_msg]);
                }
            }
        }
        return response(['data' => $data, 'status' => $status]);
    }

    public function accInquiryApiResponse($params = array())
    {
        $issue_id = $params['issue_id'];
        $acc_no = $params['acc_no'];
        $ref_no = $params['ref_no'];
        $cif_no = $params['cif_no'];
        $data = [];
        $data['name'] = '';
        $data['acctName'] = '';
        $data['acctShortName'] = '';
        $data['schmCode'] = '';
        $data['schmType'] = '';
        $data['acctCurr'] = '';
        $data['acctInqAcctCurr'] = '';
        $data['branchId'] = '';
        $data['branchName'] = '';
        $data['bankId'] = '';
        $data['acctStmtMode'] = '';
        $data['startDt'] = '';
        $data['modeOfOperCode'] = '';
        $data['wTaxAmountScopeFlg'] = '';
        $data['atmFlg'] = '';
        $data['cenBkSectorCode'] = '';
        $data['norPsoUnitId'] = '';
        $data['monitoringPsoUnitId'] = '';
        $data['ccCode'] = '';
        $data['tpCashDepTranCnt'] = '';
        $data['tpCashDepTranAmtHigh'] = '';
        $data['tpCashDepTranAmtTot'] = '';
        $data['tpXferDepTranCnt'] = '';
        $data['tpXferDepTranAmtHigh'] = '';
        $data['tpXferDepTranAmtTot'] = '';
        $data['tpTotlDepTranCnt'] = '';
        $data['tpTotlDepTranAmtHigh'] = '';
        $data['tpTotlDepTranAmtTot'] = '';
        $data['tpCashWdrTranCnt'] = '';
        $data['tpCashWdrTranAmtHigh'] = '';
        $data['tpCashWdrTranAmtTot'] = '';
        $data['tpXferWdrTranCnt'] = '';
        $data['tpXferWdrTranAmtHigh'] = '';
        $data['tpXferWdrTranAmtTot'] = '';
        $data['tpTotlWdrTranCnt'] = '';
        $data['tpTotlWdrTranAmtHigh'] = '';
        $data['tpTotlWdrTranAmtTot'] = '';
        $data['regNum'] = '';
        $data['nomineeName'] = '';
        $data['relType'] = '';
        $data['nomineeTelephoneNum'] = '';
        $data['nomineeFaxNum'] = '';
        $data['nomineeTelexNum'] = '';
        $data['nomineeEmailAddr'] = '';
        $data['nomineeAddressTypeAddress1'] = '';
        $data['nomineeAddressTypeAddress2'] = '';
        $data['nomineeAddressTypeAddress3'] = '';
        $data['nomineeAddressTypeCity'] = '';
        $data['nomineeAddressTypeStateProv'] = '';
        $data['nomineeAddressTypePostalCode'] = '';
        $data['nomineeAddressTypeCountry'] = '';
        $data['nomineeAddrType'] = '';
        $data['nomineeMinorFlg'] = '';
        $data['nomineeBirthDt'] = '';
        $data['nomineePercent'] = '';
        $data['guardianCode'] = '';
        $data['guardianName'] = '';
        $data['guardianTelephoneNum'] = '';
        $data['guardianFaxNum'] = '';
        $data['guardianTelexNum'] = '';
        $data['guardianEmailAddr'] = '';
        $data['guardianAddressTypeAddress1'] = '';
        $data['guardianAddressTypeAddress2'] = '';
        $data['guardianAddressTypeAddress3'] = '';
        $data['guardianAddressTypeCity'] = '';
        $data['guardianAddressTypeStateProv'] = '';
        $data['guardianAddressTypePostalCode'] = '';
        $data['guardianAddressTypeCountry'] = '';
        $data['guardianAddrType'] = '';
        $data['relEmailAddress'] = '';
        $data['addr1'] = '';
        $data['addr2'] = '';
        $data['addr3'] = '';
        $data['city'] = '';
        $data['stateProv'] = '';
        $data['postalCode'] = '';
        $data['country'] = '';
        $data['addrType'] = '';
        $nomineeData = array();

        $inquiry = DB::table('cif_api')
            ->leftJoin('cif_modification_url', 'cif_api.parent_api', 'cif_modification_url.parent_id')
            ->where('cif_api.issue_id', $issue_id)
            ->where('cif_api.type', 2)
            ->where('cif_modification_url.status', 1)
            ->select('cif_modification_url.url', 'cif_modification_url.request', 'cif_modification_url.id')
            ->first();

        if (!empty($inquiry)) {
            $inquiry_id = $inquiry->id;
            $api = $inquiry->request;
            $url = $inquiry->url;
            $sourceTimestamp = now()->format('Y-m-d\TH:i:s.v');
            $password = DynamicAPICredential::where('api', $inquiry_id)->first();
            $password = !empty($password->password) ? decrypt($password->password) : '';
            $global_transaction_id = 'AYS' . now()->format('YmdHisv');
            $api = str_replace("#global_transaction_id#", $global_transaction_id, $api);
            $api = str_replace("cif_api_password", $password, $api);
            $api = str_replace("#sourceTimestamp#", $sourceTimestamp, $api);
            $api = str_replace("#cb_idno#", $acc_no, $api);
            $api = str_replace("#account_number#", $acc_no, $api);
            $api = str_replace("#cif_number#", $cif_no, $api);
            $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 5, 'json_node' => $api, 'account_number' => $acc_no, 'url' => $url, 'cif_number' => $cif_no, 'global_transaction_id' => $global_transaction_id]);

            $headers = array("Content-type: text/xml;charset=\"utf-8\"", "Accept: text/xml", "Cache-Control: no-cache", "Pragma: no-cache", "SOAPAction: corporateCustomerInquiryReq", "Content-length: " . strlen($api));
            $msc = microtime(true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $api);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            curl_close($ch);

            $msc = microtime(true) - $msc;
            $execution_time = number_format($msc, 2);

            if (!empty($response)) {
                $response = str_replace('&','&amp;',$response);

                $doc = new DomDocument();
                $doc->loadXML($response);
                $res = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
                $xmlArray = simplexml_load_string($res);
                $xmlArray = json_decode(json_encode($xmlArray), TRUE);
                $array = array_flatten($xmlArray);
                foreach ($array as $key => $value) {
                    $ke = str_ireplace(['v11', 'v1', 'v2', 'ns1', 'ns2', 'ns3', 'ns4'], '', $key);
                    if ($ke == 'name') {
                        $data['name'] = $value;
                    }
                    if ($ke == 'acctInqSchmCode') {
                        $data['schmCode'] = $value;
                    }
                    if ($ke == 'acctInqSchmType') {
                        $data['schmType'] = $value;
                    }
                    if ($ke == 'acctInqAcctCurr') {
                        $data['acctInqAcctCurr'] = $value;
                    }
                    if ($ke == 'AcctCurr') {
                        $data['acctCurr'] = $value;
                    }
                    if ($ke == 'acctInqBranchId') {
                        $data['branchId'] = $value;
                    }
                    if ($ke == 'acctInqBranchName') {
                        $data['branchName'] = $value;
                    }
                    if ($ke == 'acctInqBankId') {
                        $data['bankId'] = $value;
                    }
                    if ($ke == 'acctInqName') {
                        $data['acctName'] = $value;
                    }
                    if ($ke == 'acctShortName') {
                        $data['acctShortName'] = $value;
                    }
                    if ($ke == 'acctStmtMode') {
                        $data['acctStmtMode'] = $value;
                    }
                    if ($ke == 'startDt') {
                        $data['startDt'] = $value;
                    }
                    if ($ke == 'modeOfOper') {
                        $data['modeOfOperCode'] = $value;
                    }
                    if ($ke == 'taxCategory') {
                        $data['wTaxAmountScopeFlg'] = $value;
                    }
                    if ($ke == 'atmFlg') {
                        $data['atmFlg'] = $value;
                    }
                    if ($ke == 'cenBkSectorCode') {
                        $data['cenBkSectorCode'] = $value;
                    }
                    if ($ke == 'norPsoUnitId') {
                        $data['norPsoUnitId'] = $value;
                    }
                    if ($ke == 'monitoringPsoUnitId') {
                        $data['monitoringPsoUnitId'] = $value;
                    }
                    if ($ke == 'ccCode') {
                        $data['ccCode'] = $value;
                    }
                    if ($ke == 'tpCashDepTranCnt') {
                        $data['tpCashDepTranCnt'] = $value;
                    }
                    if ($ke == 'tpCashDepTranAmtHigh') {
                        $data['tpCashDepTranAmtHigh'] = $value;
                    }
                    if ($ke == 'tpCashDepTranAmtTot') {
                        $data['tpCashDepTranAmtTot'] = $value;
                    }
                    if ($ke == 'tpXferDepTranCnt') {
                        $data['tpXferDepTranCnt'] = $value;
                    }
                    if ($ke == 'tpXferDepTranAmtHigh') {
                        $data['tpXferDepTranAmtHigh'] = $value;
                    }
                    if ($ke == 'tpXferDepTranAmtTot') {
                        $data['tpXferDepTranAmtTot'] = $value;
                    }
                    if ($ke == 'tpTotlDepTranCnt') {
                        $data['tpTotlDepTranCnt'] = $value;
                    }
                    if ($ke == 'tpTotlDepTranAmtHigh') {
                        $data['tpTotlDepTranAmtHigh'] = $value;
                    }
                    if ($ke == 'tpTotlDepTranAmtTot') {
                        $data['tpTotlDepTranAmtTot'] = $value;
                    }
                    if ($ke == 'tpCashWdrTranCnt') {
                        $data['tpCashWdrTranCnt'] = $value;
                    }
                    if ($ke == 'tpCashWdrTranAmtHigh') {
                        $data['tpCashWdrTranAmtHigh'] = $value;
                    }
                    if ($ke == 'tpCashWdrTranAmtTot') {
                        $data['tpCashWdrTranAmtTot'] = $value;
                    }
                    if ($ke == 'tpXferWdrTranCnt') {
                        $data['tpXferWdrTranCnt'] = $value;
                    }
                    if ($ke == 'tpXferWdrTranAmtHigh') {
                        $data['tpXferWdrTranAmtHigh'] = $value;
                    }
                    if ($ke == 'tpXferWdrTranAmtTot') {
                        $data['tpXferWdrTranAmtTot'] = $value;
                    }
                    if ($ke == 'tpTotlWdrTranCnt') {
                        $data['tpTotlWdrTranCnt'] = $value;
                    }
                    if ($ke == 'tpTotlWdrTranAmtHigh') {
                        $data['tpTotlWdrTranAmtHigh'] = $value;
                    }
                    if ($ke == 'tpTotlWdrTranAmtTot') {
                        $data['tpTotlWdrTranAmtTot'] = $value;
                    }
                    if ($ke == 'nomineeRegNum') {
                        $data['regNum'] = $value;
                    }
                    if ($ke == 'nomineeName') {
                        $data['nomineeName'] = $value;
                    }
                    if ($ke == 'relType') {
                        $data['relType'] = $value;
                    }
                    if ($ke == 'nomineeTelephoneNum') {
                        $data['nomineeTelephoneNum'] = $value;
                    }
                    if ($ke == 'nomineeFaxNum') {
                        $data['nomineeFaxNum'] = $value;
                    }
                    if ($ke == 'nomineeTelexNum') {
                        $data['nomineeTelexNum'] = $value;
                    }
                    if ($ke == 'nomineeEmailAddr') {
                        $data['nomineeEmailAddr'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypeAddress1') {
                        $data['nomineeAddressTypeAddress1'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypeAddress2') {
                        $data['nomineeAddressTypeAddress2'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypeAddress3') {
                        $data['nomineeAddressTypeAddress3'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypeCity') {
                        $data['nomineeAddressTypeCity'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypeStateProv') {
                        $data['nomineeAddressTypeStateProv'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypePostalCode') {
                        $data['nomineeAddressTypePostalCode'] = $value;
                    }
                    if ($ke == 'nomineeAddressTypeCountry') {
                        $data['nomineeAddressTypeCountry'] = $value;
                    }
                    if ($ke == 'nomineeAddrType') {
                        $data['nomineeAddrType'] = $value;
                    }
                    if ($ke == 'nomineeMinorFlg') {
                        $data['nomineeMinorFlg'] = $value;
                    }
                    if ($ke == 'nomineeBirthDt') {
                        $data['nomineeBirthDt'] = $value;
                    }
                    if ($ke == 'nomineePercent') {
                        $data['nomineePercent'] = $value;
                    }
                    if ($ke == 'guardianCode') {
                        $data['guardianCode'] = $value;
                    }
                    if ($ke == 'guardianName') {
                        $data['guardianName'] = $value;
                    }
                    if ($ke == 'guardianTelephoneNum') {
                        $data['guardianTelephoneNum'] = $value;
                    }
                    if ($ke == 'guardianFaxNum') {
                        $data['guardianFaxNum'] = $value;
                    }
                    if ($ke == 'guardianTelexNum') {
                        $data['guardianTelexNum'] = $value;
                    }
                    if ($ke == 'guardianEmailAddr') {
                        $data['guardianEmailAddr'] = $value;
                    }
                    if ($ke == 'guardianAddressTypeAddress1') {
                        $data['guardianAddressTypeAddress1'] = $value;
                    }
                    if ($ke == 'guardianAddressTypeAddress2') {
                        $data['guardianAddressTypeAddress2'] = $value;
                    }
                    if ($ke == 'guardianAddressTypeAddress3') {
                        $data['guardianAddressTypeAddress3'] = $value;
                    }
                    if ($ke == 'guardianAddressTypeCity') {
                        $data['guardianAddressTypeCity'] = $value;
                    }
                    if ($ke == 'guardianAddressTypeStateProv') {
                        $data['guardianAddressTypeStateProv'] = $value;
                    }
                    if ($ke == 'guardianAddressTypePostalCode') {
                        $data['guardianAddressTypePostalCode'] = $value;
                    }
                    if ($ke == 'guardianAddressTypeCountry') {
                        $data['guardianAddressTypeCountry'] = $value;
                    }
                    if ($ke == 'guardianAddrType') {
                        $data['guardianAddrType'] = $value;
                    }
                    if ($ke == 'relEmailAddress') {
                        $data['relEmailAddress'] = $value;
                    }
                    if ($ke == 'addressType_address1') {
                        $data['addr1'] = $value;
                    }
                    if ($ke == 'addressType_address2') {
                        $data['addr2'] = $value;
                    }
                    if ($ke == 'addressType_address3') {
                        $data['addr3'] = $value;
                    }
                    if ($ke == 'addressType_city') {
                        $data['city'] = $value;
                    }
                    if ($ke == 'addressType_stateProv') {
                        $data['stateProv'] = $value;
                    }
                    if ($ke == 'addressType_postalCode') {
                        $data['postalCode'] = $value;
                    }
                    if ($ke == 'addressType_country') {
                        $data['country'] = $value;
                    }
                }

                $fullLienNode = arrayNodeSearch($xmlArray, 'ns1NomineeInfoDetailType');
                if (!empty($fullLienNode)) {
                    if (empty($fullLienNode[0])) {
                        $tmpFullLienNode = $fullLienNode;
                        $fullLienNode = array();
                        $fullLienNode[0] = $tmpFullLienNode;
                    }

                    foreach ($fullLienNode as $fln1Key => $fLNode) {

                        $nomineeData[$fln1Key]['regNum'] ='';
                        $nomineeData[$fln1Key]['nomineeName'] ='';
                        $nomineeData[$fln1Key]['relType'] ='';
                        $nomineeData[$fln1Key]['nomineeTelephoneNum'] ='';
                        $nomineeData[$fln1Key]['nomineeEmailAddr'] ='';
                        $nomineeData[$fln1Key]['nomineeFaxNum'] ='';
                        $nomineeData[$fln1Key]['nomineeTelexNum'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypeAddress1'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypeAddress2'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypeAddress3'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypeCity'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypeStateProv'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypePostalCode'] ='';
                        $nomineeData[$fln1Key]['nomineeAddressTypeCountry'] ='';
                        $nomineeData[$fln1Key]['nomineeAddrType'] ='';
                        $nomineeData[$fln1Key]['nomineeMinorFlg'] ='';
                        $nomineeData[$fln1Key]['nomineeBirthDt'] ='';
                        $nomineeData[$fln1Key]['nomineePercent'] ='';
                        $nomineeData[$fln1Key]['guardianCode'] ='';
                        $nomineeData[$fln1Key]['guardianName'] ='';
                        $nomineeData[$fln1Key]['guardianTelephoneNum'] ='';
                        $nomineeData[$fln1Key]['guardianEmailAddr'] ='';
                        $nomineeData[$fln1Key]['guardianFaxNum'] ='';
                        $nomineeData[$fln1Key]['guardianTelexNum'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypeAddress1'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypeAddress2'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypeAddress3'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypeCity'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypeStateProv'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypePostalCode'] ='';
                        $nomineeData[$fln1Key]['guardianAddressTypeCountry'] ='';
                        $nomineeData[$fln1Key]['guardianAddrType'] ='';

                        $fLNode1 = array_flatten($fLNode);

                        foreach ($fLNode1 as $fln2Key => $value) {
                            $ke = str_ireplace(['v11', 'v1', 'v2', 'ns1', 'ns2', 'ns3', 'ns4'], '', $fln2Key);

                            if ($ke == 'nomineeRegNum') {
                                $nomineeData[$fln1Key]['regNum'] = $value;
                            }
                            if ($ke == 'nomineeName') {
                                $nomineeData[$fln1Key]['nomineeName'] = $value;
                            }
                            if ($ke == 'relType') {
                                $nomineeData[$fln1Key]['relType'] = $value;
                            }
                            if ($ke == 'nomineeTelephoneNum') {
                                $nomineeData[$fln1Key]['nomineeTelephoneNum'] = $value;
                            }
                            if ($ke == 'nomineeFaxNum') {
                                $nomineeData[$fln1Key]['nomineeFaxNum'] = $value;
                            }
                            if ($ke == 'nomineeTelexNum') {
                                $nomineeData[$fln1Key]['nomineeTelexNum'] = $value;
                            }
                            if ($ke == 'nomineeEmailAddr') {
                                $nomineeData[$fln1Key]['nomineeEmailAddr'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypeAddress1') {
                                $nomineeData[$fln1Key]['nomineeAddressTypeAddress1'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypeAddress2') {
                                $nomineeData[$fln1Key]['nomineeAddressTypeAddress2'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypeAddress3') {
                                $nomineeData[$fln1Key]['nomineeAddressTypeAddress3'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypeCity') {
                                $nomineeData[$fln1Key]['nomineeAddressTypeCity'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypeStateProv') {
                                $nomineeData[$fln1Key]['nomineeAddressTypeStateProv'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypePostalCode') {
                                $nomineeData[$fln1Key]['nomineeAddressTypePostalCode'] = $value;
                            }
                            if ($ke == 'nomineeAddressTypeCountry') {
                                $nomineeData[$fln1Key]['nomineeAddressTypeCountry'] = $value;
                            }
                            if ($ke == 'nomineeAddrType') {
                                $nomineeData[$fln1Key]['nomineeAddrType'] = $value;
                            }
                            if ($ke == 'nomineeMinorFlg') {
                                $nomineeData[$fln1Key]['nomineeMinorFlg'] = $value;
                            }
                            if ($ke == 'nomineeBirthDt') {
                                $nomineeData[$fln1Key]['nomineeBirthDt'] = $value;
                            }
                            if ($ke == 'nomineePercent') {
                                $nomineeData[$fln1Key]['nomineePercent'] = $value;
                            }
                            if ($ke == 'guardianCode') {
                                $nomineeData[$fln1Key]['guardianCode'] = $value;
                            }
                            if ($ke == 'guardianName') {
                                $nomineeData[$fln1Key]['guardianName'] = $value;
                            }
                            if ($ke == 'guardianTelephoneNum') {
                                $nomineeData[$fln1Key]['guardianTelephoneNum'] = $value;
                            }
                            if ($ke == 'guardianFaxNum') {
                                $nomineeData[$fln1Key]['guardianFaxNum'] = $value;
                            }
                            if ($ke == 'guardianTelexNum') {
                                $nomineeData[$fln1Key]['guardianTelexNum'] = $value;
                            }
                            if ($ke == 'guardianEmailAddr') {
                                $nomineeData[$fln1Key]['guardianEmailAddr'] = $value;
                            }
                            if ($ke == 'guardianAddressTypeAddress1') {
                                $nomineeData[$fln1Key]['guardianAddressTypeAddress1'] = $value;
                            }
                            if ($ke == 'guardianAddressTypeAddress2') {
                                $nomineeData[$fln1Key]['guardianAddressTypeAddress2'] = $value;
                            }
                            if ($ke == 'guardianAddressTypeAddress3') {
                                $nomineeData[$fln1Key]['guardianAddressTypeAddress3'] = $value;
                            }
                            if ($ke == 'guardianAddressTypeCity') {
                                $nomineeData[$fln1Key]['guardianAddressTypeCity'] = $value;
                            }
                            if ($ke == 'guardianAddressTypeStateProv') {
                                $nomineeData[$fln1Key]['guardianAddressTypeStateProv'] = $value;
                            }
                            if ($ke == 'guardianAddressTypePostalCode') {
                                $nomineeData[$fln1Key]['guardianAddressTypePostalCode'] = $value;
                            }
                            if ($ke == 'guardianAddressTypeCountry') {
                                $nomineeData[$fln1Key]['guardianAddressTypeCountry'] = $value;
                            }
                            if ($ke == 'guardianAddrType') {
                                $nomineeData[$fln1Key]['guardianAddrType'] = $value;
                            }
                        }
                    }
                }
                $data['nomineeinfo'] = $nomineeData;

                $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 6, 'json_node' => $response, 'account_number' => $acc_no, 'url' => $url, 'execution_time' => $execution_time, 'cif_number' => $cif_no, 'global_transaction_id' => $global_transaction_id]);
            }
        }
        return $data;
    }

    public function inquiryApiOld($issue_id = null, $acc_no = null, $ref_no = null, $cif_no = null)
    {
        $data = "Inquiry data not found, Please Contact with Administrator";
        $status = 0;
        $inquiry = DB::table('cif_api')
            ->leftJoin('cif_modification_url', 'cif_api.parent_api', 'cif_modification_url.parent_id')
            ->where('cif_api.issue_id', $issue_id)
            ->where('cif_api.type', 2)
            ->where('cif_modification_url.status', 1)
            ->select('cif_modification_url.url', 'cif_modification_url.request', 'cif_modification_url.id')
            ->first();
        $inquiry_config = ApiCommonConfig::where('issue_id', $issue_id)
            ->where('type', 2)
            ->get();
        if (!empty($inquiry)) {
            $inquiry_id = $inquiry->id;
            $api = $inquiry->request;
            $url = $inquiry->url;
            $sourceTimestamp = now()->format('Y-m-d\TH:i:s.v');
            $password = DynamicAPICredential::where('api', $inquiry_id)->first();
            $password = !empty($password->password) ? decrypt($password->password) : '';
            $global_transaction_id = 'AYS' . now()->format('YmdHisv');
            $xml_post_string = str_replace("#cif_number#", $cif_no, $api);
            $xml_post_string = str_replace("#global_transaction_id#", $global_transaction_id, $xml_post_string);
            $xml_post_string = str_replace("cif_api_password", $password, $xml_post_string);
            $xml_post_string = str_replace("#sourceTimestamp#", $sourceTimestamp, $xml_post_string);
            $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 3, 'json_node' => $xml_post_string, 'account_number' => $acc_no, 'url' => $url, 'cif_number' => $cif_no, 'global_transaction_id' => $global_transaction_id]);
            $headers = array("Content-type: text/xml;charset=\"utf-8\"", "Accept: text/xml", "Cache-Control: no-cache", "Pragma: no-cache", "SOAPAction: corporateCustomerInquiryReq", "Content-length: " . strlen($xml_post_string));
            $msc = microtime(true);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_errno($ch);
                print_r($error_msg);
                echo "<br/>";
                print_r(curl_error($ch));
            }
            curl_close($ch);
            if (isset($error_msg)) {
            }
            $msc = microtime(true) - $msc;
            $execution_time = number_format($msc, 2);
            if (!empty($response)) {
                $resp = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
                $xml = new SimpleXMLElement($resp);
                $body = $xml->xpath('//soapenvBody')[0];
                $array = json_decode(json_encode((array)$body), TRUE);
                $array = array_flatten_enq(1, $array);
                $data = [];
                foreach ($array as $key => $value) {
                    if (!empty($inquiry_config)) {
                        foreach ($inquiry_config as $val) {
                            if ($key == $val->api_parameter) {
                                $data[$val->value] = $value;
                            } else {
                            }
                        }
                    }
                }
                $status = 1;
                $this->requestResponsePayload(['reference_number' => $ref_no, 'type' => 4, 'json_node' => $response, 'account_number' => $acc_no, 'url' => $url, 'execution_time' => $execution_time, 'cif_number' => $cif_no, 'global_transaction_id' => $global_transaction_id]);
            }
        }
        return response(['data' => $data, 'status' => $status]);
    }

}

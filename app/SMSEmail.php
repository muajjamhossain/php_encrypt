<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSEmail extends Model
{
	protected $table = 'sms_email_templates';
    protected $fillable = ['issue_opening_sms_wform','issue_opening_sms_complaint',  'issue_opening_email_wform', 'issue_opening_email_complaint', 'issue_closing_sms_wform','issue_closing_sms_complaint', 'issue_closing_email_wform', 'issue_closing_email_complaint','send_back_sms', 'send_back_email', 'unreachable_cust_sms', 'unreachable_cust_email', 'non_cust_sms', 'non_cust_email', 'escalation_email', 'created_by', 'modified_by', 'ip'];
}
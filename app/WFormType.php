<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WFormType extends Model
{
	protected $table = 'w_form_type';
	protected $primaryKey = 'reference_number';
	public $incrementing = false;
	public $timestamps = false;
    protected $fillable = [
        'reference_number',
        'auto_debit_type',
        'debit_partner_name',
        'acount_number2',
        'billing_date',
        'auto_debit_type',
        'cell_phone',
        'qubee_account_no',
        'alico_policy_number',
        'beneficiary_name',
        'beneficiary_account_number',
        'billing_date', 'package_name', 'billing_date_from',
        'billing_date_to', 'block_reason', 'reinstate_reason',
        'branch_service_type', 'replacement_reason', 'replacement_required',
        'charge', 'card_block', 'existing_user', 'issuance_bank',
        'card_block', 'closure_reason', 'debit_balance',
        'credit_balance', 'card_pin_replacement', 'replacement_reason',
        'renewal_type', 'card_expiry_date', 'request_type',
        'existing_limit', 'proposed_limit', 'category',
        'data_to_be_insert', 'data_tobe_insert2', 'date_from',
        'date_to', 'duplicate_charge', 'delivery_option',
        'email_address', 'email_updated', 'captured_email',
        'epay_enable_reason', 'epay_enable', 'txn_amount',
        'txn_type', 'txn_date', 'tenor',
        'transaction_details', 'closure_reason', 'tenor',
        'ezypay_amount', 'emi_paid', 'principle_outstanding',
        'charge_type', 'fees_and_charge_reason', 'loan_amount',
        'tenor', 'beneficiary_name', 'bnfcr_bank',
        'beneficiary_account_number', 'branch_name',
        'routing_no', 'transfer_type', 'card_expiry_date',
        'loan_type', 'closure_type', 'loan_closure_reason',
        'llid', 'loan_outstanding_amount', 'emi_amount',
        'current_contact_number', 'email_updated',
        'captured_email', 'captured_mobile', 'cr_card_type',
        'points_to_be_redeem', 'charge_amount', 'renewal_fee_date',
        'reversal_type', 'reversal_reason', 'note_if_other_reason',
        'reversal_amount', 'available_reward_point', 'point_to_reedem',
        'product_code', 'reward_description', 'amount',
        'available_reward_point', 'point_to_reedem',
        'product_code', 'reward_description', 'available_reward_point',
        'point_to_reedem', 'product_code', 'reward_description',
        'profession', 'designation_company', 'salary_amount_credit_turnover',
        'contact_mobile_number', 'security_item_type', 'security_item_type',
        'resend_to', 'mobile_updated', 'reason_for_stop',
        'issuance_bank', 'cheque_amount', 'cheque_date',
        'cheque_serial_number', 'tdr_type', 'amount',
        'tdr_tenor', 'mode_of_payment', 'txns_number_in_year',
        'charge_amount', 'renewal_fee_date',
        'extra_field','check_list'];


}

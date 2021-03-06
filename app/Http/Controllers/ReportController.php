<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Courier;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\User;
use App\Models\User_profile;
use App\Models\Courier_payment;
use App\Models\Manifest;
use App\Models\Company;
use App\Models\Manifest_bulk_payment;


use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AgentPaymentExport;
use App\Exports\WalkingCustomerExport;
use App\Exports\ManifestReportExport;
use App\Exports\PaymentExpenseExport;
use App\Exports\CompanyReportExport;






class ReportController extends Controller
{
    public function index(){
        $data=[];
        $user_type = \Auth::user()->user_type;
        if($user_type == 'admin'){
            return view('admin.reports.index',$data);

        }else if($user_type == 'store'){
            $data['user_id']=\Auth::user()->id;
            return view('store.reports.index',$data);

        }
    }

    public function generateReport(Request $request){

        $input = $request->all();
        $user_id= $input['user_id'];

        $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
        $end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';
        if($user_id > 0){
            $where[] = ['couriers.user_id', $user_id];

        }


       $courier_joins = Courier::with(['agent','status','shippment','courier_charge','receiver_country']);


        if( $user_id > 0 && $from_date !="" && $end_date != ""){

            $couriers= $courier_joins
                ->whereDate('updated_at','>=', $from_date)
                ->whereDate('updated_at', '<=',$end_date)
                ->where($where)
                ->OrderBy('updated_at','desc');
        }else{

            $couriers= $courier_joins
                ->whereDate('updated_at','>=', $from_date)
                ->whereDate('updated_at', '<=',$end_date)
                ->OrderBy('updated_at','desc');
        }

        $courier_data = $couriers->paginate(50);

        $total_amount=0;
        $total_pickup_charge=0;
        $total=0;
        if($courier_data->total() > 0 ){
            foreach ($courier_data as $c_data){
                if($c_data->courier_charge != null){
                    $total_amount+=$c_data->courier_charge->amount;
                    $total_pickup_charge+=$c_data->courier_charge->pickup_charge;
                    $total+=$c_data->courier_charge->total;
                }
            }
        }
        $response_data['total_amount']=$total_amount;
        $response_data['total_pickup_charge']=$total_pickup_charge;
        $response_data['total']=$total;
        $response_data['courier_data']=$courier_data;

        return response()->json($response_data);


    }


    public function generatePaymentExpense(Request $request){


        $input = $request->all();
        $user_id= isset($input['user_id'])?$input['user_id']:"";
        $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
        $end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';
       
        if($user_id > 0){
            $where_p[] = ['payments.created_by', $user_id];
             $where_ex[] = ['expenses.user_id', $user_id];

        }

      
        if( $user_id > 0 && $from_date !="" && $end_date != ""){

            $payments= Payment::with('user')
                                ->whereDate('payment_date','>=', $from_date)
                                ->whereDate('payment_date', '<=',$end_date)
                                ->where($where_p)
                                ->where('payment_user_type','agent_store')
                                ->OrderBy('payment_date','desc');

            $walking_payments= Payment::with('user')
                                ->whereDate('payment_date','>=', $from_date)
                                ->whereDate('payment_date', '<=',$end_date)
                                ->where($where_p)
                                ->where('payment_user_type','walking_customer')
                                ->OrderBy('payment_date','desc');

            $courier_Ids = Courier::where('user_id',$user_id)->pluck('id')->toArray();

            $courier_payments = Courier_payment::with(['courier','user'])->where('user_id',$user_id)
                                            ->whereIn('courier_id',$courier_Ids)
                                             ->whereDate('payment_date','>=', $from_date)
                                             ->whereDate('payment_date', '<=',$end_date)
                                            ->orderBy('payment_date','desc');



            $expenses= Expense::with(['expense_type','user','vendor','company'])

                                ->whereDate('expense_date','>=', $from_date)
                                ->whereDate('expense_date', '<=',$end_date)
                                ->where($where_ex)
                                ->OrderBy('expense_date','desc');

        }else{

            $payments= Payment::with('user')
                                            ->OrderBy('payment_date','desc')
                                            ->whereDate('payment_date','>=', $from_date)
                                            ->whereDate('payment_date', '<=',$end_date)
                                            ->where('payment_user_type','agent_store');


            $walking_payments= Payment::with('user')->OrderBy('payment_date','desc')
                                        ->whereDate('payment_date','>=', $from_date)
                                        ->whereDate('payment_date', '<=',$end_date)
                                        ->where('payment_user_type','walking_customer');



            $courier_payments = Courier_payment::with(['courier','user'])->whereDate('payment_date','>=', $from_date)
                                                ->whereDate('payment_date', '<=',$end_date)
                                                ->whereNotNull('pay_amount')
                                                ->orderBy('payment_date','desc');


            $expenses= Expense::with(['expense_type','user','vendor','company'])
                                ->OrderBy('expense_date','desc')
                                ->whereDate('expense_date','>=', $from_date)
                                ->whereDate('expense_date', '<=',$end_date);     

        }




        $payment_data = $payments->get();
        $walking_payment_data = $walking_payments->get();
        $courier_payment_data = $courier_payments->get();


        $expense_data = $expenses->get();


        $payment_grouped = $payment_data->groupBy('payment_date');
        $walking_payment_grouped = $walking_payment_data->groupBy('payment_date');
        $courier_payment_grouped = $courier_payment_data->groupBy('payment_date');

        $expense_grouped = $expense_data->groupBy('expense_date');


        $total_payment = $payments->sum('amount');

        $total_expense = $expenses->sum('amount');

        $total_courier_payment = $courier_payments->sum('pay_amount');

        $total_walking_payments = $walking_payments->sum('amount');
        //echo $total_walking_payments;exit;

        $all_total = $total_payment+$total_courier_payment+$total_walking_payments;
       // dd($expense_grouped->toArray());

        $payment_expense_arr = array_merge_recursive($payment_grouped->toArray(),
                                                     $walking_payment_grouped->toArray(),
                                                     $courier_payment_grouped->toArray(),
                                                     $expense_grouped->toArray()
                                                    );

        $payments_expense_data=[];
        foreach ($payment_expense_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
               $payments_expense_data[]=$value;
            }
           
        }
       // dd($payments_expense_data);
         $response_data['total_payment']=$all_total;
         $response_data['total_expense']=$total_expense;
         $response_data['total']=$all_total - $total_expense;
        $response_data['payments_expense_data']=$payments_expense_data;

        return response()->json($response_data);

    }


    public function downloadPaymentExpense(Request $request){


        $input = $request->all();
        $user_id= isset($input['user_id'])?$input['user_id']:"";
        $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
        $end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';

        if($user_id > 0){
            $where_p[] = ['payments.created_by', $user_id];
            $where_ex[] = ['expenses.user_id', $user_id];

        }


        if( $user_id > 0 && $from_date !="" && $end_date != ""){

            $payments= Payment::with('user')->OrderBy('updated_at','desc')
                ->whereDate('payment_date','>=', $from_date)
                ->whereDate('payment_date', '<=',$end_date)
                ->where($where_p)
                ->where('payment_user_type','agent_store');

            $walking_payments= Payment::with('user')->OrderBy('updated_at','desc')
                ->whereDate('payment_date','>=', $from_date)
                ->whereDate('payment_date', '<=',$end_date)
                ->where($where_p)
                ->where('payment_user_type','walking_customer');

            $courier_Ids = Courier::where('user_id',$user_id)->pluck('id')->toArray();

            $courier_payments = Courier_payment::with(['courier','user'])
                                                ->where('user_id',$user_id)
                                                ->whereIn('courier_id',$courier_Ids)
                                                ->whereDate('payment_date','>=', $from_date)
                                                ->whereDate('payment_date', '<=',$end_date)
                                                ->orderBy('payment_date','desc');



            $expenses= Expense::with(['expense_type','user','vendor','company'])->OrderBy('updated_at','desc')
                ->whereDate('expense_date','>=', $from_date)
                ->whereDate('expense_date', '<=',$end_date)
                ->where($where_ex);

        }else{

            $payments= Payment::with('user')->OrderBy('updated_at','desc')
                ->whereDate('payment_date','>=', $from_date)
                ->whereDate('payment_date', '<=',$end_date)
                ->where('payment_user_type','agent_store');


            $walking_payments= Payment::with('user')->OrderBy('updated_at','desc')
                ->whereDate('payment_date','>=', $from_date)
                ->whereDate('payment_date', '<=',$end_date)
                ->where('payment_user_type','walking_customer');



            $courier_payments = Courier_payment::with(['courier','user'])
                                                    ->whereDate('payment_date','>=', $from_date)
                                                    ->whereDate('payment_date', '<=',$end_date)
                                                    ->whereNotNull('pay_amount')
                                                    ->orderBy('payment_date','desc');


            $expenses= Expense::with(['expense_type','user','vendor','company'])
                ->OrderBy('updated_at','desc')
                ->whereDate('expense_date','>=', $from_date)
                ->whereDate('expense_date', '<=',$end_date);

        }




        $payment_data = $payments->get();
        $walking_payment_data = $walking_payments->get();
        $courier_payment_data = $courier_payments->get();


        $expense_data = $expenses->get();


        $payment_grouped = $payment_data->groupBy('payment_date');
        $walking_payment_grouped = $walking_payment_data->groupBy('payment_date');
        $courier_payment_grouped = $courier_payment_data->groupBy('payment_date');

        $expense_grouped = $expense_data->groupBy('expense_date');


        $total_payment = $payments->sum('amount');

        $total_expense = $expenses->sum('amount');

        $total_courier_payment = $courier_payments->sum('pay_amount');

        $total_walking_payments = $walking_payments->sum('amount');
        //echo $total_walking_payments;exit;

        $all_total = $total_payment+$total_courier_payment+$total_walking_payments;
        // dd($expense_grouped->toArray());

        $payment_expense_arr = array_merge_recursive($payment_grouped->toArray(),
            $walking_payment_grouped->toArray(),
            $courier_payment_grouped->toArray(),
            $expense_grouped->toArray()
        );

        $payments_expense_data=[];
        foreach ($payment_expense_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $payments_expense_data[]=$value;
            }

        }
        // dd($payments_expense_data);
        $response_data['total_payment']=$all_total;
        $response_data['total_expense']=$total_expense;
        $response_data['total']=$all_total - $total_expense;
        $response_data['payments_expense_data']=$payments_expense_data;
        //dd($response_data);

        $t="payment_expense".time().".xlsx";
        return Excel::download(new PaymentExpenseExport($response_data), $t);

    }

    public function walkingCustomer(){

        $data['user_id']=\Auth::user()->id;
        $data['user_type']=\Auth::user()->user_type;
        $user_type = \Auth::user()->user_type;
        if($user_type == 'admin'){
            return view('admin.reports.walking_customer',$data);

        }else if($user_type == 'store'){
           return view('store.reports.walking_customer',$data);

        }
    }

    public function agentPayment(){

        $user_type = \Auth::user()->user_type;
        $data['user_id']=\Auth::user()->id;
        $data['user_type']=\Auth::user()->user_type;
        if($user_type == 'admin'){
           return view('admin.reports.agent_payment',$data);
        }else if($user_type == 'store'){
            return view('store.reports.agent_payment',$data);
        }else if($user_type == 'agent'){

            return view('agent.reports.agent_payment',$data);
        }
    }

    public function paymentExpense(){
        $data=[];
        $user_type = \Auth::user()->user_type;
        if($user_type == 'admin'){
            return view('admin.reports.payment_expense',$data);

        }else if($user_type == 'store'){
            $data['user_id']=\Auth::user()->id;
            return view('store.reports.payment_expense',$data);

        }
    }

    public function getAgentPayment(Request $request){

    $input = $request->all();
    $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
    $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
    $end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';
    $agent_id = isset($input['agent_id'])?$input['agent_id']:'';
    $user_type = isset($input['user_type'])?$input['user_type']:'';

        if(!empty($agent_id) && $agent_id > 0){
        $agent_ids = [$agent_id];

    }else{
        if($user_type == 'store'){
            $agent_ids = User_profile::where('store_id',$logged_user_id)->pluck('user_id')->toArray();
        }else if($user_type == 'admin'){
            $agent_ids = User::where('user_type','agent')->pluck('id')->toArray();
        }
    }

    $courier_payments = Courier_payment::with('agent')
                                        ->whereIn('user_id',$agent_ids)
                                        ->whereDate('payment_date','>=', $from_date)
                                        ->whereDate('payment_date', '<=',$end_date)
                                        ->orderBy('payment_date','desc')
                                        ->get();
    $agent_payments =   Payment::with('agent')
                                ->whereIn('user_id',$agent_ids)
                                ->whereDate('payment_date','>=', $from_date)
                                ->whereDate('payment_date', '<=',$end_date)
                                ->orderBy('payment_date','desc')
                                ->get();

    $courier_payment_agent = Courier_payment::with('agent')
                                             ->whereIn('user_id',$agent_ids)->sum('total');

    $agent_all_payments = Payment::with('agent')
                         ->whereIn('user_id',$agent_ids)->sum('amount');


    $total_amount = $courier_payments->sum('total');
    $total_paid_amount = $agent_payments->sum('amount');

    $cp_grouped = $courier_payments->groupBy('payment_date');

    $ap_grouped = $agent_payments->groupBy('payment_date');


    $agent_payment_arr = array_merge_recursive($cp_grouped->toArray(),$ap_grouped->toArray());

    $agent_payment_data=[];
    foreach ($agent_payment_arr as $key => $pe) {
        foreach ($pe as $key => $value) {
            $agent_payment_data[]=$value;
        }

    }

    $response_data['agent_payment_data']=$agent_payment_data;

    $response_data['total_amount']=$total_amount;
    $response_data['total_paid_amount']=$total_paid_amount;
    $response_data['remaining_amount']=$total_amount-$total_paid_amount;

    $response_data['all_total_amount']=$courier_payment_agent;
    $response_data['all_total_paid_amount']=$agent_all_payments;
    $response_data['all_remaining_amount']=$courier_payment_agent-$agent_all_payments;


    return response()->json($response_data);



}

    public function downloadAgentPayment(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
        $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
        $end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';
        $agent_id = isset($input['agent_id'])?$input['agent_id']:'';
        $user_type = isset($input['user_type'])?$input['user_type']:'';

        if(!empty($agent_id) && $agent_id > 0){
            $agent_ids = [$agent_id];

        }else{
            if($user_type == 'store'){
                $agent_ids = User_profile::where('store_id',$logged_user_id)->pluck('user_id')->toArray();
            }else if($user_type == 'admin'){
                $agent_ids = User::where('user_type','agent')->pluck('id')->toArray();
            }
        }

        $courier_payments = Courier_payment::with('agent')
            ->whereIn('user_id',$agent_ids)
            ->whereDate('payment_date','>=', $from_date)
            ->whereDate('payment_date', '<=',$end_date)
            ->orderBy('payment_date','desc')
            ->get();
        $agent_payments =   Payment::with('agent')
            ->whereIn('user_id',$agent_ids)
            ->whereDate('payment_date','>=', $from_date)
            ->whereDate('payment_date', '<=',$end_date)
            ->orderBy('payment_date','desc')
            ->get();

        $courier_payment_agent = Courier_payment::with('agent')
            ->whereIn('user_id',$agent_ids)->sum('total');

        $agent_all_payments = Payment::with('agent')
            ->whereIn('user_id',$agent_ids)->sum('amount');


        $total_amount = $courier_payments->sum('total');
        $total_paid_amount = $agent_payments->sum('amount');

        $cp_grouped = $courier_payments->groupBy('payment_date');

        $ap_grouped = $agent_payments->groupBy('payment_date');


        $agent_payment_arr = array_merge_recursive($cp_grouped->toArray(),$ap_grouped->toArray());

        $agent_payment_data=[];
        foreach ($agent_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $agent_payment_data[]=$value;
            }

        }

        $response_data['agent_payment_data']=$agent_payment_data;

        $response_data['total_amount']=$total_amount;
        $response_data['total_paid_amount']=$total_paid_amount;
        $response_data['remaining_amount']=$total_amount-$total_paid_amount;

        $response_data['all_total_amount']=$courier_payment_agent;
        $response_data['all_total_paid_amount']=$agent_all_payments;
        $response_data['all_remaining_amount']=$courier_payment_agent-$agent_all_payments;

        $t="agent_payment_".time().".xlsx";
        return Excel::download(new AgentPaymentExport($response_data), $t);


    }

    public function getWalkingCustomerPayment(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
       // $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
        //$end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';
        $customer_phone = isset($input['customer_phone'])?$input['customer_phone']:'';
        $userType = isset($input['user_type'])?$input['user_type']:'';

        if(!empty($customer_phone)){
            $courier_Ids = Courier::where('s_phone',$customer_phone)->pluck('id')->toArray();

        }else{
            $courier_Ids = Courier::where('user_id',$logged_user_id)->pluck('id')->toArray();
        }

        if($userType == 'store'){


            $courier_payments = Courier_payment::with('courier')
                                                ->where('user_id',$logged_user_id)
                                                ->whereIn('courier_id',$courier_Ids)
                                                // ->whereDate('payment_date','>=', $from_date)
                                                // ->whereDate('payment_date', '<=',$end_date)
                                                ->orderBy('payment_date','desc')
                                                ->get();


            $walking_payments =   Payment::with('courier')->where('created_by',$logged_user_id)
                                                            ->where('payment_user_type','walking_customer')
                                                            ->where('customer_phone',$customer_phone)
                                                            // ->whereDate('payment_date','>=', $from_date)
                                                            //->whereDate('payment_date', '<=',$end_date)
                                                            ->orderBy('payment_date','desc')
                                                            ->get();

        }else if($userType == 'admin'){


            $courier_payments = Courier_payment::with('courier')
                                               ->whereIn('courier_id',$courier_Ids)
                                            // ->whereDate('payment_date','>=', $from_date)
                                            // ->whereDate('payment_date', '<=',$end_date)
                                            ->orderBy('payment_date','desc')
                                            ->get();
            $walking_payments =   Payment::with('courier')
                                        ->where('payment_user_type','walking_customer')
                                        ->where('customer_phone',$customer_phone)
                                        // ->whereDate('payment_date','>=', $from_date)
                                        //->whereDate('payment_date', '<=',$end_date)
                                        ->orderBy('payment_date','desc')
                                        ->get();

        }



        $total_courier_amount = $courier_payments->sum('total');
        $total_courier_paid_amount = $courier_payments->sum('pay_amount');
        $total_courier_discount = $courier_payments->sum('discount');

        $total_walking_payment = $walking_payments->sum('amount');
        $total_walking_discount = $walking_payments->sum('discount');

        $cp_grouped = $courier_payments->groupBy('payment_date');

        $wp_grouped = $walking_payments->groupBy('payment_date');


        $walking_payment_arr = array_merge_recursive($cp_grouped->toArray(),$wp_grouped->toArray());

        $walking_payment_data=[];
        foreach ($walking_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $walking_payment_data[]=$value;
            }

        }

        $response_data['walking_payment_data']=$walking_payment_data;

        $response_data['total_amount']=$total_courier_amount;
        $response_data['total_paid_amount']=$total_courier_paid_amount+$total_walking_payment;
        $response_data['total_discount']=$total_courier_discount+$total_walking_discount;
        $response_data['total_remaining']= $response_data['total_amount']-($response_data['total_paid_amount']+$response_data['total_discount']);

        return response()->json($response_data);



    }

    public function downloadWalkingCustomer(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
        // $from_date = isset($input['from_date'])?date('Y-m-d',strtotime($input['from_date'])):'';
        //$end_date = isset($input['end_date'])?date('Y-m-d',strtotime($input['end_date'])):'';
        $customer_phone = isset($input['customer_phone'])?$input['customer_phone']:'';
        $userType = isset($input['user_type'])?$input['user_type']:'';

        if(!empty($customer_phone)){
            $courier_Ids = Courier::where('s_phone',$customer_phone)->pluck('id')->toArray();

        }else{
            $courier_Ids = Courier::where('user_id',$logged_user_id)->pluck('id')->toArray();
        }

        if($userType == 'store'){


            $courier_payments = Courier_payment::with('courier')
                ->where('user_id',$logged_user_id)
                ->whereIn('courier_id',$courier_Ids)
                // ->whereDate('payment_date','>=', $from_date)
                // ->whereDate('payment_date', '<=',$end_date)
                ->orderBy('payment_date','desc')
                ->get();


            $walking_payments =   Payment::with('courier')->where('created_by',$logged_user_id)
                ->where('payment_user_type','walking_customer')
                ->where('customer_phone',$customer_phone)
                // ->whereDate('payment_date','>=', $from_date)
                //->whereDate('payment_date', '<=',$end_date)
                ->orderBy('payment_date','desc')
                ->get();

        }else if($userType == 'admin'){


            $courier_payments = Courier_payment::with('courier')
                ->whereIn('courier_id',$courier_Ids)
                // ->whereDate('payment_date','>=', $from_date)
                // ->whereDate('payment_date', '<=',$end_date)
                ->orderBy('payment_date','desc')
                ->get();
            $walking_payments =   Payment::with('courier')
                ->where('payment_user_type','walking_customer')
                ->where('customer_phone',$customer_phone)
                // ->whereDate('payment_date','>=', $from_date)
                //->whereDate('payment_date', '<=',$end_date)
                ->orderBy('payment_date','desc')
                ->get();

        }



        $total_courier_amount = $courier_payments->sum('total');
        $total_courier_paid_amount = $courier_payments->sum('pay_amount');
        $total_courier_discount = $courier_payments->sum('discount');

        $total_walking_payment = $walking_payments->sum('amount');
        $total_walking_discount = $walking_payments->sum('discount');

        $cp_grouped = $courier_payments->groupBy('payment_date');

        $wp_grouped = $walking_payments->groupBy('payment_date');


        $walking_payment_arr = array_merge_recursive($cp_grouped->toArray(),$wp_grouped->toArray());

        $walking_payment_data=[];
        foreach ($walking_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $walking_payment_data[]=$value;
            }

        }

        $response_data['walking_payment_data']=$walking_payment_data;

        $response_data['total_amount']=$total_courier_amount;
        $response_data['total_paid_amount']=$total_courier_paid_amount+$total_walking_payment;
        $response_data['total_discount']=$total_courier_discount+$total_walking_discount;
        $response_data['total_remaining']= $response_data['total_amount']-($response_data['total_paid_amount']+$response_data['total_discount']);

        $t="walking_customer_".time().".xlsx";
        return Excel::download(new WalkingCustomerExport($response_data), $t);



    }

    public function manifestPayment(){

        $data['user_id']=\Auth::user()->id;
        $data['user_type']=\Auth::user()->user_type;
        return view('admin.reports.manifest_report',$data);
    }

    public function getManifestPayment(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
        $userType = isset($input['user_type'])?$input['user_type']:'';
        $vendor_id = isset($input['vendor_id'])?$input['vendor_id']:'';

        if($userType == 'store'){


            $manifest_payments = Manifest::with('vendor')->where('created_by',$logged_user_id)
                                            ->where('vendor_id',$vendor_id)
                                            ->orderBy('payment_date','desc')
                                            ->get();

            $vendor_expenses =   Expense::with('vendor')->where('user_id',$logged_user_id)
                                         ->where('vendor_id',$vendor_id)
                                         ->orderBy('expense_date','desc')
                                         ->get();

        }else if($userType == 'admin'){


            $manifest_payments = Manifest::with('vendor')->where('vendor_id',$vendor_id)
                                            ->orderBy('payment_date','desc')
                                            ->get();


            $vendor_expenses =   Expense::with('vendor')->where('vendor_id',$vendor_id)
                                        ->orderBy('expense_date','desc')
                                        ->get();

        }



        $total_manifest_amount = $manifest_payments->sum('amount');
        $total_manifest_paid_amount = $vendor_expenses->sum('amount');

        $mp_grouped = $manifest_payments->groupBy('payment_date');

        $ve_grouped = $vendor_expenses->groupBy('expense_date');


        $manifest_payment_arr = array_merge_recursive($mp_grouped->toArray(),$ve_grouped->toArray());

        $manifest_payment_data=[];
        foreach ($manifest_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $manifest_payment_data[]=$value;
            }

        }

        $response_data['manifest_payment_data']=$manifest_payment_data;
        $response_data['total_amount']=$total_manifest_amount;
        $response_data['total_paid_amount']=$total_manifest_paid_amount;
        $response_data['total_remaining']= $total_manifest_amount-$total_manifest_paid_amount;

        return response()->json($response_data);



    }


    public function downloadManifestReport(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
        $userType = isset($input['user_type'])?$input['user_type']:'';
        $vendor_id = isset($input['vendor_id'])?$input['vendor_id']:'';

        if($userType == 'store'){


            $manifest_payments = Manifest::with('vendor')->where('created_by',$logged_user_id)
                ->where('vendor_id',$vendor_id)
                ->orderBy('payment_date','desc')
                ->get();

            $vendor_expenses =   Expense::with('vendor')->where('user_id',$logged_user_id)
                ->where('vendor_id',$vendor_id)
                ->orderBy('expense_date','desc')
                ->get();

        }else if($userType == 'admin'){


            $manifest_payments = Manifest::with('vendor')->where('vendor_id',$vendor_id)
                ->orderBy('payment_date','desc')
                ->get();


            $vendor_expenses =   Expense::with('vendor')->where('vendor_id',$vendor_id)
                ->orderBy('expense_date','desc')
                ->get();

        }



        $total_manifest_amount = $manifest_payments->sum('amount');
        $total_manifest_paid_amount = $vendor_expenses->sum('amount');

        $mp_grouped = $manifest_payments->groupBy('payment_date');

        $ve_grouped = $vendor_expenses->groupBy('expense_date');


        $manifest_payment_arr = array_merge_recursive($mp_grouped->toArray(),$ve_grouped->toArray());

        $manifest_payment_data=[];
        foreach ($manifest_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $manifest_payment_data[]=$value;
            }

        }

        $response_data['manifest_payment_data']=$manifest_payment_data;
        $response_data['total_amount']=$total_manifest_amount;
        $response_data['total_paid_amount']=$total_manifest_paid_amount;
        $response_data['total_remaining']= $total_manifest_amount-$total_manifest_paid_amount;

        $t="manifest_report".time().".xlsx";
        return Excel::download(new ManifestReportExport($response_data), $t);


    }

    public function companyPayment(){

        $data['user_id']=\Auth::user()->id;
        $data['user_type']=\Auth::user()->user_type;
        $data['companies']=Company::pluck('name', 'id')->toArray();
        return view('admin.reports.company_report',$data);
    }


    public function getCompanyPayment(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
        $userType = isset($input['user_type'])?$input['user_type']:'';
        $company_id = isset($input['company_id'])?$input['company_id']:'';
        $manifest_bulk_payments = Manifest_bulk_payment::with(['company','manifest'])->where('company_id',$company_id)
                                                        ->orderBy('payment_date','desc')
                                                        ->get();


        $company_expenses =   Expense::with('company')
                                    ->where('company_id',$company_id)
                                    ->orderBy('expense_date','desc')
                                    ->get();





        $total_company_amount = $manifest_bulk_payments->sum('amount');
        $total_company_paid_amount = $company_expenses->sum('amount');

        $mp_grouped = $manifest_bulk_payments->groupBy('payment_date');

        $ce_grouped = $company_expenses->groupBy('expense_date');


        $manifest_payment_arr = array_merge_recursive($mp_grouped->toArray(),$ce_grouped->toArray());

        $company_payment_data=[];
        foreach ($manifest_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $company_payment_data[]=$value;
            }

        }

        $response_data['company_payment_data']=$company_payment_data;
        $response_data['total_amount']=$total_company_amount;
        $response_data['total_paid_amount']=$total_company_paid_amount;
        $response_data['total_remaining']= $total_company_amount-$total_company_paid_amount;

        return response()->json($response_data);



    }


    public function downloadCompanyReport(Request $request){

        $input = $request->all();
        $logged_user_id= isset($input['logged_user_id'])?$input['logged_user_id']:"";
        $userType = isset($input['user_type'])?$input['user_type']:'';
        $company_id = isset($input['company_id'])?$input['company_id']:'';
        $manifest_bulk_payments = Manifest_bulk_payment::with(['company','manifest'])->where('company_id',$company_id)
            ->orderBy('payment_date','desc')
            ->get();


        $company_expenses =   Expense::with('company')
            ->where('company_id',$company_id)
            ->orderBy('expense_date','desc')
            ->get();





        $total_company_amount = $manifest_bulk_payments->sum('amount');
        $total_company_paid_amount = $company_expenses->sum('amount');

        $mp_grouped = $manifest_bulk_payments->groupBy('payment_date');

        $ce_grouped = $company_expenses->groupBy('expense_date');


        $manifest_payment_arr = array_merge_recursive($mp_grouped->toArray(),$ce_grouped->toArray());

        $company_payment_data=[];
        foreach ($manifest_payment_arr as $key => $pe) {
            foreach ($pe as $key => $value) {
                $company_payment_data[]=$value;
            }

        }

        $response_data['company_payment_data']=$company_payment_data;
        $response_data['total_amount']=$total_company_amount;
        $response_data['total_paid_amount']=$total_company_paid_amount;
        $response_data['total_remaining']= $total_company_amount-$total_company_paid_amount;

        $t="company_report".time().".xlsx";
        return Excel::download(new CompanyReportExport($response_data), $t);


    }

}

@extends('layouts.admin')
@section('date-styles')
    {!! Html::style("/assets/vendor/bootstrap-datepicker/css/datepicker3.css") !!}
    <link href='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' rel='stylesheet' type='text/css'>

@endsection

@section('content')

    <header class="page-header">
        <h2>Payment</h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="javascript:void(0);">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Payment</span></li>
                <li><span>Edit</span></li>
            </ol>

            <a class="sidebar-right-toggle" data-open="sidebar-right"></a>
        </div>
    </header>

    <!-- start: page -->
    {!! Form::model($payment,['method' => 'PATCH', 'action' => ['PaymentController@update', $payment->id ] ]) !!}
        {{csrf_field()}}
            <div class="row">
                <div class="col-md-12">

                    <section class="panel">
                        <header class="panel-heading">

                            <h2 class="panel-title">Payment Details</h2>


                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('user_id')) has-error @endif">
                                        <label class="col-sm-4 control-label">Agent Name: </label>
                                        <div class="col-sm-8">

                                            <select  class="form-control populate" id="agentSelect" name="user_id">
                                                <option value="{{$payment->user_id}}">{{$payment->agent->name}} - {{$payment->agent->profile->company_name}}</option>
                                            </select>
                                            @if ($errors->has('user_id'))
                                                <label for="user_id" class="error">{{ $errors->first('user_id') }}</label>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('amount')) has-error @endif">
                                        <label class="col-sm-4 control-label">Payment Amount: </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="amount" value="{{$payment->amount}}">

                                            @if ($errors->has('amount'))
                                                <label for="amount" class="error">{{ $errors->first('amount') }}</label>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('payment_by')) has-error @endif">
                                        <label class="col-sm-4 control-label">Payment By: </label>
                                        <div class="col-sm-8">
                                            {!! Form::select('payment_by', $payment_types, 'cash', ['class'=>'form-control mb-md','placeholder' => 'Select Payment by','v-model'=>'payment_type','@change'=>'showPaymentDetails']); !!}
                                            @if ($errors->has('payment_by'))
                                                <label for="payment_by" class="error">{{ $errors->first('payment_by') }}</label>
                                            @endif

                                        </div>
                                    </div>



                                </div>

                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('payment_date')) has-error @endif">
                                        <label class="col-sm-4 control-label">Payment Date: </label>
                                        <div class="col-sm-8">
                                            <input type="text" data-plugin-datepicker  name="payment_date" class="form-control" value="{{date('m/d/Y',strtotime($payment->payment_date))}}">

                                            @if ($errors->has('payment_date'))
                                                <label for="payment_date" class="error">{{ $errors->first('payment_date') }}</label>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">TDS: </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="tds" class="form-control"  value="{{$payment->tds}}" placeholder="">
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('bank_id')) has-error @endif">
                                        <label class="col-sm-4 control-label">Deposited in Bank: </label>
                                        <div class="col-sm-8">
                                            {!! Form::select('bank_id', $banks, $payment->bank_id, ['class'=>'form-control mb-md','placeholder' => 'Select Bank Name']); !!}
                                            @if ($errors->has('bank_id'))
                                                <label for="bank_id" class="error">{{ $errors->first('bank_id') }}</label>
                                            @endif

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </section>

                </div>
            </div>

            <div class="row">
                <div class="col-md-12">

                    <section class="panel">
                        <header class="panel-heading">

                            <h2 class="panel-title">@{{payment_name}} Details</h2>

                        </header>
                        <div class="panel-body">
                            <div class="row"  v-show="cash_details">
                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('receiver_cash_name')) has-error @endif">
                                        <label class="col-sm-4 control-label">Name: </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="receiver_cash_name" value="{{$payment->reciver_name}}">
                                            @if ($errors->has('receiver_cash_name'))
                                                <label for="receiver_cash_name" class="error">Receiver Name field is required.</label>
                                            @endif
                                        </div>
                                    </div>



                                </div>


                            </div>

                            <div class="row"  v-show="cheque_details">
                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('cheque_no')) has-error @endif">
                                        <label class="col-sm-4 control-label">Cheque No: </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="cheque_no" value="{{$payment->cheque_no}}">
                                            @if ($errors->has('cheque_no'))
                                                <label for="cheque_no" class="error">{{ $errors->first('cheque_no') }}</label>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('cheque_bank_name')) has-error @endif">
                                        <label class="col-sm-4 control-label">Bank Name: </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="cheque_bank_name" value="{{$payment->cheque_bank_name}}">

                                            @if ($errors->has('cheque_bank_name'))
                                                <label for="cheque_bank_name" class="error">Amount Name field is required.</label>
                                            @endif
                                        </div>
                                    </div>



                                </div>

                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('cheque_date')) has-error @endif">
                                        <label class="col-sm-4 control-label">Cheque Date: </label>
                                        <div class="col-sm-8">
                                            <input type="text" data-plugin-datepicker name="cheque_date" class="form-control" value="{{date('m/d/Y',strtotime($payment->cheque_date))}}" >
                                            @if ($errors->has('cheque_date'))
                                                <label for="cheque_date" class="error">{{ $errors->first('cheque_date') }}</label>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group @if ($errors->has('reference_no')) has-error @endif">
                                        <label class="col-sm-4 control-label">Reference No#: </label>
                                        <div class="col-sm-8">
                                            <input type="text"  name="reference_no" class="form-control" value="{{$payment->reference_no}}" >

                                            @if ($errors->has('reference_no'))
                                                <label for="reference_no" class="error">{{ $errors->first('reference_no') }}</label>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row"  v-show="net_banking">
                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('transaction_id')) has-error @endif">
                                        <label class="col-sm-4 control-label">Transaction ID: </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="transaction_id" value="{{$payment->transaction_id}}">

                                            @if ($errors->has('transaction_id'))
                                                <label for="transaction_id" class="error">{{ $errors->first('transaction_id') }}</label>
                                            @endif
                                        </div>
                                    </div>


                                </div>

                                <div class="col-md-6">

                                    <div class="form-group @if ($errors->has('net_banking_name')) has-error @endif">
                                        <label class="col-sm-4 control-label">Name: </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="net_banking_name" class="form-control"  value="{{$payment->reciver_name}}">

                                            @if ($errors->has('net_banking_name'))
                                                <label for="net_banking_name" class="error">Receiver  Name field is required.</label>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </section>

                </div>
            </div>

            <div class="row">
                <div class="col-md-12">

                    <section class="panel">
                        <header class="panel-heading">

                            <h2 class="panel-title">Remarks:</h2>

                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Remarks: </label>
                                        <div class="col-sm-8">
                                            <textarea name="remark" rows="5" cols="100">{{$payment->remark}}</textarea>
                                        </div>
                                    </div>

                                </div>


                            </div>

                        </div>

                    </section>

                </div>

            </div>


        <footer class="panel-footer center">
            <button class="btn btn-primary">Submit</button>
        </footer>
    </form>


    <!-- end: page -->

@endsection

@section('scripts')

    <script type="text/javascript">

        jQuery(document).ready(function($) {


            $("#agentSelect").select2({
                placeholder: "Select a Agent Name",
                allowClear: true,
                minimumInputLength:2,
                ajax: {
                    url: "/api/get_user_name",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });



        });;

        const oapp = new Vue({
            el:'#app',
            data:{
                cash_details:true,
                net_banking:false,
                cheque_details:false,
                payment_type:"{{$payment->payment_by}}",
                payment_name:"{{ucfirst($payment_types[$payment->payment_by])}}"


            },
            created(){
                if(this.payment_type == 'cash'){
                    this.cash_details =true;
                    this.net_banking =false;
                    this.cheque_details =false;
                }else if(this.payment_type == 'cheque'){
                    this.cash_details =false;
                    this.net_banking =false;
                    this.cheque_details =true;
                }else if(this.payment_type == 'net_banking'){
                    this.cash_details =false;
                    this.net_banking =true;
                    this.cheque_details =false;
                }
            },


            methods: {

                showPaymentDetails(){

                    if(this.payment_type == 'cash'){
                        this.cash_details =true;
                        this.net_banking =false;
                        this.cheque_details =false;
                    }else if(this.payment_type == 'cheque'){
                        this.cash_details =false;
                        this.net_banking =false;
                        this.cheque_details =true;
                    }else if(this.payment_type == 'net_banking'){
                        this.cash_details =false;
                        this.net_banking =true;
                        this.cheque_details =false;
                    }
                },

            },

            computed: {

            }

        });


    </script>

@endsection

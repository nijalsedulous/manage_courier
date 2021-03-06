@extends('layouts.admin')

@section('date-styles')
    <link href='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' rel='stylesheet' type='text/css'>

@endsection

@section('content')

    <header class="page-header">
        <h2>Manage Manifest</h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="javascript:void(0);">
                        <i class="fa fa-home"></i>
                    </a>
                </li>

                <li><span>Manifest</span></li>
            </ol>

            <a class="sidebar-right-toggle" data-open="sidebar-right"></a>
        </div>
    </header>

    @if (Session::has('error_message'))
        <div class="alert alert-danger">
            <strong> {{ Session::get('error_message') }}</strong>
        </div>
    @endif

    <?php   $item_exists=0;
            if(Session::has('manifest_data')){
                $menifest_data = Session::get('manifest_data');
                $item_exists=1;
                $courier_ids = $menifest_data['courier_ids'];
//                echo "<pre>";
//                print_r($menifest_data);
//                exit;

            }
    ?>


    <section class="panel">
        <div class="panel-body">
            <div class="row">

               <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Agent Name</label>
                        <select  class="form-control populate" id="agentSelect" name="agent_id">
                            @if(isset($agent))
                                <option value="{{$agent->id}}">{{$agent->name}}</option>
                            @endif
                        </select>
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">From Date</label>
                        <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </span>
                            <input type="text" id="from_date" name="from_date" data-plugin-datepicker="" class="form-control" value="{{$from_date}}">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">End Date</label>
                        <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </span>
                            <input type="text" id="end_date" name="end_date" data-plugin-datepicker="" class="form-control" value="{{$end_date}}">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <button type="button" class="btn btn-success" onclick="filterAgent();" style="margin-top: 25px;"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="panel">
        {!! Form::open(['url' => 'admin/manifest/create_manifest','method'=>'post','id'=>'frmCreateManifest']) !!}
        {{csrf_field()}}
        <input type="hidden" name="from_date" value="{{$from_date}}">
        <input type="hidden" name="end_date" value="{{$end_date}}">
        <input type="hidden" name="company_id" value="" id="hnCompanyId">
        @if(isset($agent))
            <input type="hidden" name="filter_agent_id" value="{{$agent->id}}">
        @endif

        <header class="panel-heading">

            <input name="bulk" class="btn btn-primary pull-right hide" type="button" onclick="openCompanyModel();" value="Create a Bulk" id="btn_bulk" >
            <input name="item" style="margin-right: 10px;"  class="btn btn-primary pull-right hide" id="btn_item" type="submit" value="Add Item">
            <h2 class="panel-title">Manage Couriers</h2>
        </header>
        <div class="panel-body">
            <table class="table table-no-more table-bordered table-striped mb-none">
                <thead>
                <tr>
                    <th><input type="checkbox" id="selectall" class="checkbox-custom chekbox-primary" ></th>
                    <th >Id</th>
                    <th>Agent Name</th>
                    <th>Customer Name</th>
                    <th>Created Date</th>
                    <th >Status</th>
                    <th >Country</th>
                    <th>Item/Bulked</th>

                </tr>
                </thead>
                <tbody>
                    @foreach($couriers as $courier)
                        <tr>
                            <td>
                                <?php if($item_exists && in_array($courier->id, $courier_ids)) {

                                }else { ?>
                                <input type="checkbox" name="courier_id[]" class="case"  value="{{$courier->id}}">
                                <?php }?>
                            </td>
                            <td>{{$courier->unique_name}}</td>
                            <td>{{$courier->agent->name}}</td>
                            <td>{{$courier->r_name}}</td>
                            <td>{{$courier->courier_date}}</td>
                            <td><span style="color:{{$courier->status->color_code}};">{{$courier->status->name}}</span></td>
                            <td>{{$courier->receiver_country->name}}</td>
                            <td>
                                <?php if($item_exists){

                                    if(isset($menifest_data['items'])){
                                        $items = $menifest_data['items'];
                                        if(in_array($courier->id, $items)){
                                            echo "<strong>Item Added</strong>";
                                        }
                                    }
                                    if(isset($menifest_data['bulk_items'])){

                                        $bulk_items = $menifest_data['bulk_items'];
                                        $count_bulk=count($bulk_items);
                                        if($count_bulk > 0){
                                            foreach($bulk_items as $key=> $bi){
                                                $bulk_no = $key+1;
                                                if(in_array($courier->id, $bi['courier_ids'])){
                                                    echo "<strong>Bulk".$bulk_no." </strong>";
                                                }
                                            }
                                        }
                                    }


                                } ?>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {!! Form::close() !!}



        <footer class="panel-footer">


            {!! Form::open(['url' => 'admin/manifest/save_manifest','method'=>'post','class'=>'form-inline']) !!}
            {{csrf_field()}}
                <div class="form-group">
                    <label class="sr-only" for="exampleInputUsername2">Vendor<span class="text-danger">*</span></label>

                    {!! Form::select('vendor_id', $vendors, old('vendor_id'), ['class'=>'form-control ',
                                                                                        'placeholder' => 'Select Vendor',
                                                                                        'onchange'=>'enableVendor();',
                                                                                        'id'=>'selectVendor'
                                                                                        ]); !!}

                    @if ($errors->has('vendor_id'))
                        <label for="name" class="error">{{ $errors->first('vendor_id') }}</label>
                    @endif
                </div>
                <div class="form-group">
                    <label class="sr-only" for="exampleInputPassword2">Amount<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="amount" name="amount" placeholder="Amount">
                </div>
            <div class="form-group">
                <label class="sr-only" for="exampleInputPassword2">Manifest Date<span class="text-danger">*</span></label>
                <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </span>
                    <input type="text" id="manifest_date" name="manifest_date" data-plugin-datepicker="" class="form-control" value="{{date('m/d/Y')}}">
                </div>
            </div>

                <div class="visible-sm clearfix mt-sm mb-sm"></div>
                 <button class="btn btn-primary" disabled type="submit" id="btnSave">Save</button>


            {!! Form::close() !!}




        </footer>


    </section>
    <!-- end: page -->


    <div id="modalForm" class="modal-block modal-block-primary mfp-hide">
        <section class="panel">
            <header class="panel-heading">
                <h2 class="panel-title">Companies</h2>
            </header>
            <div class="panel-body">
                    <div class="form-group mt-lg">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <select class="form-control" v-model="company_id" @change="selectedCompany">
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{$company->id}}">{{$company->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group" v-if="companyAddres">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <b>@{{companyAddres}}</b>
                        </div>
                    </div>


            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button class="btn btn-primary modal-confirm" onclick="submitBulkform();">Submit</button>
                        <button class="btn btn-default modal-dismiss" onclick="closeBulkform();">Cancel</button>
                    </div>
                </div>
            </footer>
        </section>
    </div>

@endsection

@section('scripts')

    <script>




        const oapp = new Vue({
            el:'#app',

            data:{

                companies:@json($companies),
                companyAddres:null,
                company_id:"",

            },


            methods: {

                selectedCompany(){
                    var id = this.company_id;
                    if(id > 0){
                        var item = this.companies.filter(function(item){ return item.id == id;} ).pop();
                        console.log(item.address);
                        this.companyAddres = item.address;
                        $('#hnCompanyId').val(id);
                    }else{
                        this.companyAddres = null;
                    }

                }

            },

            computed: {

            }

        });


        var user_type = "{{Auth::user()->user_type}}";
        var logged_user_id = "{{Auth::user()->id}}";
        jQuery(document).ready(function($) {


            if(user_type == 'admin'){
                var apiUrl = "/api/get_user_name";
            }else if(user_type == 'store'){
                var apiUrl = "/api/get_store_agent?user_store_id="+logged_user_id;
            }

            $("#agentSelect").select2({
                placeholder: "Search Agent Name",
                allowClear: true,
                minimumInputLength:2,
                ajax: {
                    url: apiUrl,
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


        });

        $(function(){

            // add multiple select / deselect functionality
            $("#selectall").click(function () {
                //alert(this.checked);
                if(this.checked){
                    $("#btn_bulk").removeClass("hide");
                    $("#btn_item").addClass("hide");
                }else{
                    $("#btn_item").addClass("hide");
                    $("#btn_bulk").addClass("hide");
                }
                $(".case").prop("checked",$(this).prop("checked"));
            });

            // if all checkbox are selected, check the selectall checkbox
            // and viceversa
            $(".case").click(function(){

                if($(".case:checked").length == 1){
                    $("#btn_item").removeClass("hide");
                    $("#btn_bulk").addClass("hide");
                }
                if($(".case:checked").length > 1){
                    $("#btn_bulk").removeClass("hide");
                    $("#btn_item").addClass("hide");
                }

                if($(".case:checked").length == 0){
                    $("#btn_item").addClass("hide");
                    $("#btn_bulk").addClass("hide");
                }

                if($(".case").length == $(".case:checked").length) {
                    $("#selectall").attr("checked", "checked");
                } else {
                    $("#selectall").removeAttr("checked");
                }

            });
        });

        function enableVendor(){
            var vendor = $('#selectVendor').val();
            if(vendor !=""){
                $('#btnSave').removeAttr('disabled');
            }
        }

        function openCompanyModel(){

            $.magnificPopup.open({
                items: {
                    src: '#modalForm'
                },
                type: 'inline'
            });
        }

        function submitBulkform(){
            if( $('#hnCompanyId').val() >0){
                event.preventDefault();
                document.getElementById('frmCreateManifest').submit();
            }else{
                alert("Please select Company");
            }

        }

        function closeBulkform(){
            $.magnificPopup.close();
        }

        function filterAgent(){
            var agent_id = $("#agentSelect").val();
            var from_date = $("#from_date").val();
            var end_date = $("#end_date").val();

            window.location.href ="/"+user_type+"/manifest/create?agent_id="+agent_id+"&from_date="+from_date+"&end_date="+end_date;


        }



    </script>

@endsection

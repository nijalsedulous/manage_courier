@extends('layouts.admin')

@section('content')

    <header class="page-header">
        <h2>Courier Details</h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="javascript:void(0);">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Courier</span></li>
                <li><span>{{$courier->unique_name}}</span></li>
            </ol>

            <a class="sidebar-right-toggle" data-open="sidebar-right"></a>
        </div>
    </header>


    <section class="panel">
        <div class="panel-body">
            <div class="invoice">
                <header class="clearfix">
                    <div class="row">
                        <div class="col-sm-6 mt-md">
                            <h2 class="h2 mt-none mb-sm text-dark text-bold">INVOICE</h2>
                            <h4 class="h4 m-none text-dark text-bold">{{$courier->unique_name}}</h4>
                            <h5 class="h5 mt-none mb-sm text-dark text-bold">STATUS: <span style=" color:{{$courier->status->color_code  }}">{{ $courier->status->name }}</span></h5>
                        </div>

                        <div class="col-sm-6 text-right mt-md mb-md">

                            <address class="ib mr-xlg">
                                <p class="h5 mb-xs text-dark text-semibold">Sender Details:</p>
                                <br>
                                <b class="text-uppercase">{{$courier->s_name}}</b>
                                <br>
                                {{$courier->s_company}}
                                <br>
                                {{$courier->s_address1}}, {{$courier->s_state}}, {{$courier->s_city}}
                                <br>
                                {{$courier->sender_country->name}}
                                <br>
                                Phone: {{$courier->s_phone}}
                                <br>
                                {{$courier->s_email}}
                            </address>
                            {{--<div class="ib">--}}
                                {{--<img src="assets/images/invoice-logo.png" alt="OKLER Themes">--}}
                            {{--</div>--}}
                        </div>
                    </div>
                </header>
                <div class="bill-info">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="bill-to">
                                <p class="h5 mb-xs text-dark text-semibold">Recivier Details:</p>
                                <address>
                                    <b class="text-uppercase">{{$courier->r_name}}</b>
                                    <br>
                                    {{$courier->r_company}}
                                    <br>
                                    {{$courier->r_address1}}, {{$courier->r_state}}, {{$courier->r_city}}
                                    <br>
                                    {{$courier->receiver_country->name}}, {{$courier->r_zip_code}}
                                    <br>
                                    Phone: {{$courier->r_phone}}
                                    <br>
                                    {{$courier->r_email}}
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bill-data text-right">
                                <p class="mb-none">
                                    <span class="text-dark">Invoice Date:</span>
                                    <span class="value">NA</span>
                                </p>
                                <p class="mb-none">
                                    <span class="text-dark">COUNTRY OF ORIGIN :</span>
                                    <span class="value"><b>{{$courier->sender_country->name}}</b></span>
                                </p>
                                <p class="mb-none">
                                    <span class="text-dark">FINAL DESTINATION :</span>
                                    <span class="value"><b>{{$courier->receiver_country->name}}</b></span>
                                </p>
                                <p class="mb-none">
                                    <span class="text-dark">NO. OF BOX :</span>
                                    <span class="value"><b>{{$courier->no_of_boxes}}</b></span>
                                </p>
                                <p class="mb-none">
                                    <span class="text-dark">TOTAL WEIGHT :</span>
                                    <span class="value"><b>@if(isset($courier->shippment)){{$courier->shippment->weight}} KGS @endif</b></span>
                                </p>
                                <p class="mb-none">
                                    <span class="text-dark text-uppercase">Carriage Value :</span>
                                    <span class="value"><b>@if(isset($courier->shippment)){{$courier->shippment->carriage_value}} @endif</b></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="h4 text-dark text-bold">Box Details</h4>
                    </div>
                </div>

                 @foreach($courier->courier_boxes as $cp)
                     <div class="row">
                         <div class="col-md-12"><h5 class="h5 mt-none mb-sm text-dark text-bold">{{$cp->box_name}}</h5></div>
                     </div>
                     <div class="row">
                         <div class="col-md-3"><h6 class="h6 mt-none mb-sm text-dark text-bold">Weight: {{$cp->weight}}</h6></div>
                         <div class="col-md-3"><h6 class="h6 mt-none mb-sm text-dark text-bold">Breadth:{{$cp->breadth}}</h6></div>
                         <div class="col-md-3"><h6 class="h6 mt-none mb-sm text-dark text-bold">Width:{{$cp->width}}</h6></div>
                         <div class="col-md-3"><h6 class="h6 mt-none mb-sm text-dark text-bold">Height:{{$cp->height}}</h6></div>
                     </div>
                     <div class="row">
                         <div class="col-md-12">

                             <div class="table-responsive">
                                 <table class="table mb-none">
                                     <thead>
                                     <tr>
                                         <th>No.</th>
                                         <th>Item Name</th>
                                         <th>Unit</th>
                                         <th>Qty</th>
                                     </tr>
                                     </thead>
                                     <tbody>
                                     @foreach($cp->courier_box_items as $key=> $cbi)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>{{$cbi->content_type->name}}</td>
                                             <td>{{$cbi->unit_type}}</td>
                                             <td>{{$cbi->qty}}</td>
                                         </tr>
                                     @endforeach


                                     </tbody>
                                 </table>
                             </div>

                         </div>

                     </div>


                 @endforeach




            </div>

        </div>
    </section>

@endsection

@section('scripts')


    <script type="text/javascript">

        jQuery(document).ready(function($) {

        });


    </script>


@endsection

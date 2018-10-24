@extends('layouts.admin')

@section('content')

    <header class="page-header">
        <h2>Manage Agents</h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="index.html">
                        <i class="fa fa-home"></i>
                    </a>
                </li>

                <li><span>Agents</span></li>
            </ol>

            <a class="sidebar-right-toggle" data-open="sidebar-right"></a>
        </div>
    </header>

    @if (Session::has('message'))
    <div class="alert alert-success">
       <strong> {{ Session::get('message') }}</strong>
    </div>
    @endif


    <section class="panel">
        <header class="panel-heading">

                <a href="{{route('agents.create')}}" class="btn btn-primary pull-right">Create Agent</a>
                <h2 class="panel-title">Manage Agents</h2>
        </header>
        <div class="panel-body">
            <table class="table table-no-more table-bordered table-striped mb-none">
                <thead>
                <tr>
                    <th>Company Name</th>
                    <th class="hidden-xs hidden-sm">Name</th>
                    <th class="text-right">Email</th>
                    <th class="text-right hidden-xs hidden-sm">Phone</th>
                    <th class="text-right">Country</th>
                    <th class="text-right">Address</th>
                    {{--<th class="text-right hidden-xs hidden-sm">Gender</th>--}}
                    <th class="text-right hidden-xs hidden-sm">Created</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($agents as $key=> $agent)

                <tr>
                    <td data-title="Company Name">{{$agent->profile->company_name}}</td>
                    <td data-title="Name" class="hidden-xs hidden-sm">{{$agent->profile->first_name}} {{$agent->profile->last_name}}</td>
                    <td data-title="Email" class="text-right">{{$agent->email}}</td>
                    <td data-title="Phone" class="text-right hidden-xs hidden-sm">{{$agent->profile->phone}}</td>
                    <td data-title="Country" class="text-right">
                       @if($agent->profile->country != null)
                        {{$agent->profile->country->name}}
                       @endif
                    </td>
                    <td data-title="Address" class="text-right">{{$agent->profile->address}}</td>
                    {{--<td data-title="High" class="text-right hidden-xs hidden-sm">{{$agent->profile->gender}}</td>--}}
                    <td data-title="Created" class="text-right hidden-xs hidden-sm">{{date('d-M-Y',strtotime($agent->created_at))}}</td>
                    <td data-title="Actions" class="text-right actions">

                        {!! Form::model($agent,['method' => 'DELETE', 'action' => ['AgentController@destroy', $agent->id ], 'id'=>'frmdeleteAgent_'.$agent->id ]) !!}
                          <button class="delete-row" type="button" onclick="deleteAgents('{{$agent->id}}')"><i class="fa fa-trash-o"></i></button>
                        {!! Form::close() !!}
                        <a href="{{route('agents.edit',$agent->id)}}" style="float:right" ><i class="fa fa-pencil"></i></a>

                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="pull-right">{{ $agents->links() }}</div>


    </section>
    <!-- end: page -->


@endsection

@section('scripts')

    <script>
        function deleteAgents(agentId){

        var status= confirm('Are you sure want to delete this agent?');
         if(status == true){

             event.preventDefault();
             document.getElementById('frmdeleteAgent_'+agentId).submit();
             return true;
         }else{
             return false;
         }


        }
        jQuery(document).ready(function($) {

        });

    </script>

@endsection
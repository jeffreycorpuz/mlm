@extends('layouts.master')

@section('title')
    Universal Binary | MLM
@endsection

@section('nav')
    <li>
        <a href="/client-dashboard">
            <i class="now-ui-icons business_chart-bar-32"></i>
            <p>Dashboard</p>
        </a>
    </li>
    <li>
        <a href="/client-manual">
            <i class="now-ui-icons design_vector"></i>
            <p>Manual Binary</p>
        </a>
    </li>
    <li  class="active ">
        <a href="/client-universal">
            <i class="now-ui-icons design_vector"></i>
            <p>Universal Binary</p>
        </a>
    </li>
    <li>
        <a href="/use-refcode/account">
            <i class="now-ui-icons business_badge"></i>
            <p>Add Account</p>
        </a>
    </li>
    <li>
        <a href="/use-refcode/member">
            <i class="now-ui-icons business_badge"></i>
            <p>Add Member</p>
        </a>
    </li>
    <li>
        <a href="/transaction-record">
            <i class="now-ui-icons business_badge"></i>
            <p>Transaction Record</p>
        </a>
    </li>
@endsection

@section('profile_name')
    <?php echo $member->first_name?>
@endsection

@section('css_design')
    <style>
        table{
            border-collapse: separate !important;
        }

        #chart_div{
            height: 75vh;
            /* background-color: lightblue; */
            overflow-x: auto;
            overflow-y: auto;
        }

        .nodelink{
            -webkit-appearance: button;
            -moz-appearance: button;
            appearance: button;

            text-decoration: none !important;
            color: black !important;

            display:inline-block;
            width: 100%;
            height: 100%;
            border: 0px;
            
            background-color: #DDEFF6;
            font-family: arial,helvetica;
        }

        @media (max-width: 575.98px) {
            #chart_div::-webkit-scrollbar{
                width: 0;
            }

            h3{
                font-size: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <?php $counter = 0; ?>
        @foreach($all_accounts as $account)
        
        <div class="col-md-6 mt-5">
            <div class="card">
                <div class="card-header">
                <h4 class="card-title">{{$account->full_name}} </h4>
                <p class="category"> {{$account->serial_number}} </p>
                </div>
                <div class="card-body">
                    <p> LEFT NODE: {{$all_accounts_nodes[$counter][0]}} </p>
                    <p> RIGHT NODE: {{$all_accounts_nodes[$counter][1]}} </p>
                </div>
            </div>
        </div>

        <?php $counter = $counter + 1; ?>
        @endforeach
    </div>
@endsection

@section('scripts')

@endsection
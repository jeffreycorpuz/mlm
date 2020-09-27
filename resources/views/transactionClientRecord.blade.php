@extends('layouts.master')

@section('title')
    View Serial Numbers | MLM
@endsection

@section('css_design')
    <style>
        .container {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 22px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        }

        /* Hide the browser's default checkbox */
        .container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
        }

        /* Create a custom checkbox */
        .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        background-color: #eee;
        }

        /* On mouse-over, add a grey background color */
        .container:hover input ~ .checkmark {
        background-color: #ccc;
        }

        /* When the checkbox is checked, add a blue background */
        .container input:checked ~ .checkmark {
        background-color: #274C77;
        }

        /* Create the checkmark/indicator (hidden when not checked) */
        .checkmark:after {
        content: "";
        position: absolute;
        display: none;
        }

        /* Show the checkmark when checked */
        .container input:checked ~ .checkmark:after {
        display: block;
        }

        /* Style the checkmark/indicator */
        .container .checkmark:after {
        left: 9px;
        top: 5px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 3px 3px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
        }
    </style>
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
    <li>
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
    <li class="active ">
        <a href="/transaction-record">
            <i class="now-ui-icons design_bullet-list-67"></i>
            <p>Transaction Record</p>
        </a>
    </li>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"> Serial Number's Table</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <th scope="col" class="font-weight-bold"> Transaction ID</th>
                            <th scope="col" class="font-weight-bold"> Amount</th>
                            <th scope="col" class="font-weight-bold"> Date</th>
                        </thead>
                        <tbody id="table-data">

                                @foreach($client_transaction as $transaction)
                                    <tr>
                                        <td> {{ $transaction->id }} </td>
                                        <td> {{ $transaction->amount }} </td>
                                        <td> <?php echo date_format($transaction->created_at,"Y/m/d"); ?> </td>
                                        
                                    </tr>
                                @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.master')

@section('title')
    Update Member | MLM
@endsection

@section('css_design')
    <style>
        .form-group input[type=file] {
            opacity: 100 !important;
            position: relative !important;
        }
        @media (max-width: 575.98px) {
            h1 {
                font-size: 2rem;
            }
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
    <li>
        <a href="/transaction-record">
            <i class="now-ui-icons design_bullet-list-67"></i>
            <p>Transaction Record</p>
        </a>
    </li>
@endsection

@section('profile_name')
    <?php echo $member->first_name?>
@endsection

@section('content')

    <div class="position-ref full-height">
        <div class="content">
            <div class="title m-b-md">
                MLM
            </div>                         

            <div class="container-fluid mt-3">

                <div class="row">

                    <div class="col-lg-6 col-sm-12 mx-auto">
                        
                        <h1 class="text-center font-weight-bold">{!! $title !!}</h1>

                        @if(count($errors) > 0)
                        <div class="alert alert-danger pt-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{$error}}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(\Session::has('error'))
                        <div class="alert alert-danger pt-4">
                            <p>{{\Session::get('error')}}</p>
                        </div>
                        @endif

                        @if(\Session::has('success'))
                        <div class="alert alert-success pt-4">
                            <p>{{\Session::get('success')}}</p>
                        </div>
                        @endif
                        
                        <div class="card">

                            <div class="card-header font-weight-bold">Member Information</div>

                            <div class="card-body">

                            <form method="post" action="{{url('member/update')}}" enctype="multipart/form-data">
                                {{csrf_field()}}
                                @method('PUT')
                                <div class="form-group">
                                    <label class="font-weight-bold">Profile Picture: </label><br /><input type="file" name="image">
                                    <br /><br />
                                    <label class="font-weight-bold">First Name: </label><input type="text" name="first_name" class="form-control" value="{{ $member->first_name }}">
                                    <label class="font-weight-bold">Last Name: </label><input type="text" name="last_name" class="form-control" value="{{ $member->last_name }}" >
                                    <label class="font-weight-bold">Contact Number: </label><input type="text" name="contact_number" class="form-control" value="{{ $member->contact_number }}">
                                    <label class="font-weight-bold">Bank: </label><input type="text" name="bank" class="form-control" value="{{ $member->bank }}">
                                    <label class="font-weight-bold">Bank Account Name: </label><input type="text" name="bank_account_name" class="form-control" value="{{ $member->bank_account_name }}">
                                    <label class="font-weight-bold">Bank Account No. : </label><input type="text" name="bank_account_number" class="form-control" value="{{ $member->bank_account_number }}">
                                    <label class="font-weight-bold">Bank Account Type: </label><input type="text" name="bank_account_type" class="form-control" value="{{ $member->bank_account_type }}">
                                    <label class="font-weight-bold">G-Cash: </label><input type="text" name="gcash" class="form-control" value="{{ $member->gcash }}">
                                </div>

                                <button type="submit" class="btn btn-info btn-block">Update</button>
                            </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
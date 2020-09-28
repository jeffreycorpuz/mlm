<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Member;
use App\GoldMember;
use App\CompanyMember;
use App\SerialNumber;
use App\ClientTransaction;
use App\IncomeChartFilter;
use Prophecy\Exception\Doubler\ReturnByReferenceException;

//pagination of collection data
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use function PHPSTORM_META\map;

class MemberController extends Controller
{

    public function manualAdminPanel(Request $request) {
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] != 'admin'){
            return redirect('/');
        }

        if($request->batch == ''){
            return redirect('/admin-manual?batch=1');
        }
        
        $check_batch = Member::where('batch', $request->batch)->get();

        if ($check_batch->count() == 0){
            return redirect('/admin-manual?batch=1');
        }

        $batch_head = Member::where('batch', $request->batch)
            ->where('parent_node','head')
            ->get();

        if($request->child == ''){
            $head_member = Member::where('batch', $request->batch)
                ->where('parent_node','head')
                ->get();
        }else if($request->batch == $batch_head[0]->batch  && $request->child == $batch_head[0]->id){
            return redirect('/admin-manual?batch='.$batch_head[0]->batch);
        }else{
            function checkChildNodesMA($givenNode, $childNode, $switch){
                $arrNode = []; 
    
                foreach($givenNode as $id){
                    $parentNode =  Member::findOrFail($id);
                    
                    if ($parentNode->left_node != ''){
                        array_push($arrNode, intval($parentNode->left_node));
                        if ($childNode == $parentNode->left_node){
                            if ($switch == 0){
                                return 'false';
                            } else {
                                return 'true';
                            }
                        }
                    }
    
                    if ($parentNode->right_node != ''){
                        array_push($arrNode, intval($parentNode->right_node));
                        if ($childNode == $parentNode->right_node){
                            if ($switch == 0){
                                return 'false';
                            } else {
                                return 'true';
                            }
                        }
                    }
                }
    
                if (count($arrNode) != 0){
                    if ($switch == 0){
                        return checkChildNodesMA($arrNode, $childNode, 1);
                    } else {
                        return checkChildNodesMA($arrNode, $childNode, 0);
                    }
                }
    
                return 'false';
            }
    
            $node_list = checkChildNodesMA([$batch_head[0]->id], $request->child, 0);
            
            if ($node_list == 'true'){
                $head_member = Member::where('batch', $request->batch)
                    ->where('id', $request->child)
                    ->get();
            }else{
                return redirect('/admin-manual?batch='.$batch_head[0]->batch);
            }
        }

        $members[0] = $head_member[0]; 

        if($members[0]->left_node != ''){
            $members[1] = Member::findOrFail($members[0]->left_node);
        } else {
            $members[1] = array(
                "id" => 0
            );
        }
        
        if($members[0]->right_node != ''){
            $members[2] = Member::findOrFail($members[0]->right_node);
        } else {
            $members[2] = array(
                "id" => 0
            );
        }

        if ($members[1]['id'] == 0){
            $members[3] = array(
                "id" => 0
            );

            $members[4] = array(
                "id" => 0
            );
        } else {
            if($members[1]->left_node != ''){
                $members[3] = Member::findOrFail($members[1]->left_node);
            } else {
                $members[3] = array(
                    "id" => 0
                );
            }
            
            if($members[1]->right_node != ''){
                $members[4] = Member::findOrFail($members[1]->right_node);
            } else {
                $members[4] = array(
                    "id" => 0
                );
            }
        }

        if ($members[2]['id'] == 0){
            $members[5] = array(
                "id" => 0
            );

            $members[6] = array(
                "id" => 0
            );
        } else {
            if($members[2]->left_node != ''){
                $members[5] = Member::findOrFail($members[2]->left_node);
            } else {
                $members[5] = array(
                    "id" => 0
                );
            }
            
            if($members[2]->right_node != ''){
                $members[6] = Member::findOrFail($members[2]->right_node);
            } else {
                $members[6] = array(
                    "id" => 0
                );
            }
        }

        if ($head_member[0]->parent_node != 'head'){
            $parent_node = Member::findOrFail($head_member[0]->parent_node);
            $head_node = Member::findOrFail($parent_node->parent_node);
            $goback =  $head_node->id;
        } else {
            $goback = 0;
        }

        $batch = DB::table('members')->select('batch')->distinct()->get();
        $batch = collect($batch)->sortBy('batch');
        $batch_no = $request->batch;

        return view('manualAdminPanel',compact(
            'members',
            'goback',
            'batch',
            'batch_no'
        ));
    }

    public function universalAdminPanel(Request $request) {
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] != 'admin'){
            return redirect('/');
        }

        if($request->child == ''){
            $head_member = GoldMember::where('parent_node', 'head')->get();
        }else if($request->child == '1'){
            return redirect('/admin-universal');
        }else{
            function checkChildNodesUA($givenNode, $childNode, $switch){
                $arrNode = []; 
    
                foreach($givenNode as $id){
                    $parentNode =  GoldMember::findOrFail($id);
                    
                    if ($parentNode->left_node != ''){
                        array_push($arrNode, intval($parentNode->left_node));
                        if ($childNode == $parentNode->left_node){
                            if ($switch == 0){
                                return 'false';
                            } else {
                                return 'true';
                            }
                        }
                    }
    
                    if ($parentNode->right_node != ''){
                        array_push($arrNode, intval($parentNode->right_node));
                        if ($childNode == $parentNode->right_node){
                            if ($switch == 0){
                                return 'false';
                            } else {
                                return 'true';
                            }
                        }
                    }
                }
    
                if (count($arrNode) != 0){
                    if ($switch == 0){
                        return checkChildNodesUA($arrNode, $childNode, 1);
                    } else {
                        return checkChildNodesUA($arrNode, $childNode, 0);
                    }
                }
    
                return 'false';
            }
    
            $node_list = checkChildNodesUA(['1'], $request->child, 0);
            
            if ($node_list == 'true'){
                $head_member = GoldMember::where('id', $request->child)->get();
            }else{
                return redirect('/admin-universal');
            }
        }

        $members[0] = $head_member[0]; 

        if($members[0]->left_node != ''){
            $members[1] = GoldMember::findOrFail($members[0]->left_node);
        } else {
            $members[1] = array(
                "id" => 0
            );
        }
        
        if($members[0]->right_node != ''){
            $members[2] = GoldMember::findOrFail($members[0]->right_node);
        } else {
            $members[2] = array(
                "id" => 0
            );
        }

        if ($members[1]['id'] == 0){
            $members[3] = array(
                "id" => 0
            );

            $members[4] = array(
                "id" => 0
            );
        } else {
            if($members[1]->left_node != ''){
                $members[3] = GoldMember::findOrFail($members[1]->left_node);
            } else {
                $members[3] = array(
                    "id" => 0
                );
            }
            
            if($members[1]->right_node != ''){
                $members[4] = GoldMember::findOrFail($members[1]->right_node);
            } else {
                $members[4] = array(
                    "id" => 0
                );
            }
        }

        if ($members[2]['id'] == 0){
            $members[5] = array(
                "id" => 0
            );

            $members[6] = array(
                "id" => 0
            );
        } else {
            if($members[2]->left_node != ''){
                $members[5] = GoldMember::findOrFail($members[2]->left_node);
            } else {
                $members[5] = array(
                    "id" => 0
                );
            }
            
            if($members[2]->right_node != ''){
                $members[6] = GoldMember::findOrFail($members[2]->right_node);
            } else {
                $members[6] = array(
                    "id" => 0
                );
            }
        }

        if ($head_member[0]->parent_node != 'head'){
            $parent_node = GoldMember::findOrFail($head_member[0]->parent_node);
            $head_node = GoldMember::findOrFail($parent_node->parent_node);
            $goback =  $head_node->id;
        } else {
            $goback = 0;
        }

        return view('universalAdminPanel',compact(
            'members',
            'goback'
        ));
    }

    public function manualClientPanel(Request $request) {
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        

        if($request->child == ''){
            $head_member = Member::where('id', session('data')['id'])->get();
        }else if($request->child == session('data')['id']){
            return redirect('/client-manual');
        }else{
            function checkChildNodesMC($givenNode, $childNode){
                $arrNode = []; 

                foreach($givenNode as $id){
                    $parentNode =  Member::findOrFail($id);
                    
                    if ($parentNode->left_node != ''){
                        array_push($arrNode, intval($parentNode->left_node));
                        if ($childNode == $parentNode->left_node){
                            return 'true';
                        }
                    }
    
                    if ($parentNode->right_node != ''){
                        array_push($arrNode, intval($parentNode->right_node));
                        if ($childNode == $parentNode->right_node){
                            return 'true';
                        }
                    }
                }
    
                if (count($arrNode) != 0){
                    return checkChildNodesMC($arrNode, $childNode);
                }
    
                return 'false';
            }
    
            $node_list = checkChildNodesMC([session('data')['id']], $request->child);

    

            if ($node_list == 'true'){
                $head_member = Member::where('id', $request->child)->get();
            }else{
                return redirect('/client-manual');
            }
        }
        
        $members[0] = $head_member[0]; 

        if($members[0]->left_node != ''){
            $members[1] = Member::findOrFail($members[0]->left_node);
        } else {
            $members[1] = array(
                "id" => 0
            );
        }
    
        if($members[0]->right_node != ''){
            $members[2] = Member::findOrFail($members[0]->right_node);
        } else {
            $members[2] = array(
                "id" => 0
            );
        }

        if ($members[1]['id'] == 0){
            $members[3] = array(
                "id" => 0
            );

            $members[4] = array(
                "id" => 0
            );
        } else {
            if($members[1]->left_node != ''){
                $members[3] = Member::findOrFail($members[1]->left_node);
            } else {
                $members[3] = array(
                    "id" => 0
                );
            }
            
            if($members[1]->right_node != ''){
                $members[4] = Member::findOrFail($members[1]->right_node);
            } else {
                $members[4] = array(
                    "id" => 0
                );
            }
        }

        if ($members[2]['id'] == 0){
            $members[5] = array(
                "id" => 0
            );

            $members[6] = array(
                "id" => 0
            );
        } else {
            if($members[2]->left_node != ''){
                $members[5] = Member::findOrFail($members[2]->left_node);
            } else {
                $members[5] = array(
                    "id" => 0
                );
            }
            
            if($members[2]->right_node != ''){
                $members[6] = Member::findOrFail($members[2]->right_node);
            } else {
                $members[6] = array(
                    "id" => 0
                );
            }
        }

        $member = Member::findOrFail(session('data')['id']);

        return view('manualClientPanel',compact('member','members'));
    }

    public function paginate($items, $perPage = 10, $page = null, $options = []){
        //paginate collection or json file
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function universalClientPanel(Request $request) {

        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $member = Member::findOrFail(session('data')['id']);

        $all_accounts = GoldMember::where('email', session('data')['email'])->get();
        $all_accounts_nodes = [];

        function countHeadGoldNodes($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  GoldMember::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countHeadGoldNodes($arrNode, 0);
            }

            return $length;  
        }

        function countLeftAndRightNode($node){
            $leftNodeLength = 0;
            $rightNodeLength = 0;

            $account_nodes = [];
            
            $parent_node = GoldMember::where('id', $node)->get();

            if ($parent_node[0]->left_node != '' && $parent_node[0]->right_node != '' ){ 
                $leftNodeLength = countHeadGoldNodes([$parent_node[0]->left_node], $leftNodeLength);
                $rightNodeLength = countHeadGoldNodes([$parent_node[0]->right_node], $rightNodeLength);
            }

            $account_nodes = [$leftNodeLength, $rightNodeLength];

            return $account_nodes;
        }

        foreach ($all_accounts as & $account) {
            array_push($all_accounts_nodes, countLeftAndRightNode($account->id));
        }

        return view('universalClientPanel',compact(
            'member',
            'all_accounts',
            'all_accounts_nodes'
        ));
    }

    public function dashboardClientPanel() {
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $member = Member::findOrFail(session('data')['id']);
        
        function countManual($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  Member::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countManual($arrNode, 0);
            }

            return $length;  
        }

        function countUniversal($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  GoldMember::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countManual($arrNode, 0);
            }

            return $length;  
        }

        $client_transaction = ClientTransaction::where('email',session('data')['email'])
            ->where('transaction_type','withdraw')
            ->get();
        $total_withdrawal_transactions = 0;
        
        foreach($client_transaction as $transaction){
            $total_withdrawal_transactions += $transaction->amount;
        }

        $chartfilter = IncomeChartFilter::all();

        $current_to_date = date('Y-m-d', strtotime($chartfilter[4]->to));
        $date_today = date("Y-m-d");
        // $date_today = date("Y-m-d", strtotime("2020-09-02"));
        $days = 0;

        if( $date_today > $current_to_date ){
            while( $date_today > $current_to_date ){
                $days += 7;
                $current_to_date = date('Y-m-d', strtotime($current_to_date. ' + 7 days'));
            }

            foreach($chartfilter as $filter){
                $filter->from = date('Y-m-d', strtotime($filter->from. ' + '.$days.' days'));
                $filter->to = date('Y-m-d', strtotime($filter->to. ' + '.$days.' days'));
                $filter->save();
            }
        }

        $filter1_name = date('F j', strtotime($chartfilter[0]->from)) .' to '.date('F j', strtotime($chartfilter[0]->to));
        $filter2_name = date('F j', strtotime($chartfilter[1]->from)) .' to '.date('F j', strtotime($chartfilter[1]->to));
        $filter3_name = date('F j', strtotime($chartfilter[2]->from)) .' to '.date('F j', strtotime($chartfilter[2]->to));
        $filter4_name = date('F j', strtotime($chartfilter[3]->from)) .' to '.date('F j', strtotime($chartfilter[3]->to));
        $filter5_name = date('F j', strtotime($chartfilter[4]->from)) .' to '.date('F j', strtotime($chartfilter[4]->to));

        function countWeeklyIncome($filter){
            $total_week_income = 0;
            foreach($filter as $week){
                $total_week_income += $week->amount;
            }

            return $total_week_income;
        }

        $filter1 = ClientTransaction::where('email',session('data')['email'])->where('transaction_type','income')->whereBetween('created_at', [$chartfilter[0]->from, $chartfilter[0]->to])->get();
        $filter2 = ClientTransaction::where('email',session('data')['email'])->where('transaction_type','income')->whereBetween('created_at', [$chartfilter[1]->from, $chartfilter[1]->to])->get();
        $filter3 = ClientTransaction::where('email',session('data')['email'])->where('transaction_type','income')->whereBetween('created_at', [$chartfilter[2]->from, $chartfilter[2]->to])->get();
        $filter4 = ClientTransaction::where('email',session('data')['email'])->where('transaction_type','income')->whereBetween('created_at', [$chartfilter[3]->from, $chartfilter[3]->to])->get();
        $filter5 = ClientTransaction::where('email',session('data')['email'])->where('transaction_type','income')->whereBetween('created_at', [$chartfilter[4]->from, $chartfilter[4]->to])->get();

        $filter1_income = countWeeklyIncome($filter1);
        $filter2_income = countWeeklyIncome($filter2);
        $filter3_income = countWeeklyIncome($filter3);
        $filter4_income = countWeeklyIncome($filter4);
        $filter5_income = countWeeklyIncome($filter5);

        $title = "Client Dashboard";

        $all_member_income = Member::where('email',session('data')['email'])->get('income');
        $total_member_income = 0;

        foreach($all_member_income as $income){
            $total_member_income= $total_member_income + $income->income;
        }

        $all_goldmember_income = GoldMember::where('email',session('data')['email'])->get('income');
        $total_goldmember_income = 0;

        foreach($all_goldmember_income as $income){
            $total_goldmember_income= $total_goldmember_income + $income->income;
        }

        $total_income = $total_member_income+$total_goldmember_income;

        $total_member = countManual([session('data')['id']], 0 ) - 1;
        $total_goldmember = countUniversal([session('data')['id']], 0 ) - 1;
        $total_referred_members = count(Member::where('referred_by',session('data')['email'])->get());
        $total_balance = $total_income - $total_withdrawal_transactions;
        

        return view('dashboardClientPanel',compact(
            'member',
            'title',
            'total_income',
            'total_member',
            'total_goldmember',
            'total_referred_members',
            'total_balance',
            'filter1_income',
            'filter1_name',
            'filter2_income',
            'filter2_name',
            'filter3_income',
            'filter3_name',
            'filter4_income',
            'filter4_name',
            'filter5_income',
            'filter5_name',
        ));
    }

    public function withdrawForm(){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $member = Member::findOrFail(session('data')['id']);

        $client_transaction = ClientTransaction::where('email',session('data')['email'])
            ->where('transaction_type','withdraw')
            ->get();

        $total_withdrawal_transactions = 0;

        foreach($client_transaction as $transaction){
            $total_withdrawal_transactions += $transaction->amount;
        }

        $all_member_income = Member::where('email',session('data')['email'])->get('income');
        $total_member_income = 0;

        foreach($all_member_income as $income){
            $total_member_income= $total_member_income + $income->income;
        }

        $all_goldmember_income = GoldMember::where('email',session('data')['email'])->get('income');
        $total_goldmember_income = 0;

        foreach($all_goldmember_income as $income){
            $total_goldmember_income= $total_goldmember_income + $income->income;
        }

        $total_income = $total_member_income+$total_goldmember_income;
        
        $total_balance = $total_income - $total_withdrawal_transactions;

        $title = "Transaction Form";

        return view('withdrawClientPanel',compact('member','title','total_balance'));
    }

    public function withdrawTransaction(Request $request){
        $this->validate($request, [
            'email' => "required",
            'password' => "required",
            'amount' => "required"
        ]);
        
        $member = Member::findOrFail(session('data')['id']);

        if($request->total_balance >= $request->amount){
            if(session('data')['email'] == $request->get('email') && session('data')['password'] == $request->get('password')){
                $transaction = new ClientTransaction([
                    'email' => $request->get('email'),
                    'bank' => session('data')['bank'],
                    'bank_account_name' => session('data')['bank_account_name'],
                    'bank_account_number' => session('data')['bank_account_number'],
                    'bank_account_type' => session('data')['bank_account_type'],
                    'gcash' => session('data')['gcash'],
                    'transaction_type' => 'withdraw',
                    'amount' => intval($request->get('amount')),
                ]);
        
                $transaction->save();

                return redirect()->route('withdrawform')->with('success','Transaction Complete');
            }else{
                return redirect()->route('withdrawform')->with('error','Invalid Email or Password');
            }
        }else{
            return redirect()->route('withdrawform')->with('member','error','Invalid Amount');
        }
    }

    public function addmember(Request $request){

        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] != 'admin'){
            $member = Member::findOrFail(session('data')['id']);
        }

        $check_code = SerialNumber::where('input_code',$request->get('code'))->get();

        if ( $check_code->count() == 0 ){
            return redirect('/use-refcode/member')->with('error','Invalid Serial Number');
        }


        if($check_code[0]->status == '1'){
            return redirect('/use-refcode/member')->with('error','This code has already been used');
        }else if($check_code[0]->code_type == 'account'){
            return redirect('/use-refcode/member')->with('error','This code is not for adding member');
        }else {
            $input_code = $request->code;
        }
        
        $title = "Add Member";
        if(session('data')['role'] != 'admin'){
            return view('addmember',compact('member','title','input_code'));
        } else {
            return view('addmember',compact('title','input_code'));
        }
        
    }

    public function store(Request $request){

        $this->validate($request, [
            'first_name' => "required",
            'last_name' => "required",
            'email' => "required",
            'contact_number' => "required",
            'referred_by' => "required",
            'upline_to' => "required",
            'node' => "required"
        ]);
        
        //Checking if Serial number is valid
        $check_code = SerialNumber::where('input_code',$request->get('input_code'))->get();

        if($check_code[0]->status == '1'){
            return redirect('/use-refcode/member')->with('error','This code has already been used');
        }

        if($check_code[0]->code_type != 'member'){
            return redirect('/use-refcode/head')->with('error','This code is not for adding member');
        }

        //Checking if Email is already exist
        $check_email = count(Member::where('email',$request->get('email'))->get());
        $check_email_admin = count(CompanyMember::where('email',$request->get('email'))->get());

        if ($check_email != 0 || $check_email_admin != 0){ //isnotempty
            return redirect('/member/add?code='.$request->get('input_code'))->with('error','An account with Email '.$request->get('email').' is already exist');
        }

        //Checking if Upline is valid
        $parent_node = Member::where('id',$request->get('upline_to'))->get();

        if ($parent_node->count() == 0 ){
            return redirect('/member/add?code='.$request->get('input_code'))->with('error','Invalid Upline ID');
        }else{
            $batch = $parent_node[0]->batch;
        }
        
        //Checking if Referral Email is Valid
        $referral_name = Member::where('email',$request->get('referred_by'))->get();

        if (count($referral_name) == 0){
            return redirect('/member/add?code='.$request->get('input_code'))->with('error','Invalid Referral Email');
        }

        //FOR Manual Binary
        function addMember($givenNode, $request){
            $arrNode = [];

            foreach ($givenNode as & $id) {
                $parentNode =  Member::findOrFail($id);

                if ($parentNode->left_node == '' ){
                    $left_node = new Member([
                        'first_name' => $request->get('first_name'),
                        'last_name' => $request->get('last_name'),
                        'email' => $request->get('email'),
                        'password' => '123123123',
                        'account_type' => 'main',
                        'contact_number' => $request->get('contact_number'),
                        'bank' => $request->get('bank'),
                        'bank_account_name' => $request->get('bank_account_name'),
                        'bank_account_number' => $request->get('bank_account_number'),
                        'bank_account_type' => $request->get('bank_account_type'),
                        'gcash' => $request->get('gcash'),
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => $request->get('referred_by'),
                        'income' => 0,
                        'batch' => $parentNode->batch,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $left_node->save();

                    $left_node = Member::where('serial_number',$request->get('input_code'))->get();
                    
                    $parentNode->left_node = $left_node[0]->id;
                    $parentNode->save();
                    addIncome($parentNode->id); // for income

                    return;

                } else if($parentNode->right_node == '' ){
                    $right_node = new Member([
                        'first_name' => $request->get('first_name'),
                        'last_name' => $request->get('last_name'),
                        'email' => $request->get('email'),
                        'password' => '123123123',
                        'account_type' => 'main',
                        'contact_number' => $request->get('contact_number'),
                        'bank' => $request->get('bank'),
                        'bank_account_name' => $request->get('bank_account_name'),
                        'bank_account_number' => $request->get('bank_account_number'),
                        'bank_account_type' => $request->get('bank_account_type'),
                        'gcash' => $request->get('gcash'),
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => $request->get('referred_by'),
                        'income' => 0,
                        'batch' => $parentNode->batch,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $right_node->save();

                    $right_node = Member::where('serial_number',$request->get('input_code'))->get();
                    
                    $parentNode->right_node = $right_node[0]->id;
                    $parentNode->save();
                    addIncome($parentNode->id); // for income

                    return;
                } else {
                    array_push($arrNode, intval($parentNode->left_node),  intval($parentNode->right_node));
                }
            }

            
            return addMember($arrNode, $request);
        }

        function countNodes($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  Member::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countNodes($arrNode, 0);
            }

            return $length;  
        }

        function addIncome($node){
            $income = 0;
            $leftNodeLength = 0;
            $rightNodeLength = 0;
            
            $parent_node = Member::where('id', $node)->get();

            if ($parent_node[0]->left_node != '' && $parent_node[0]->right_node != '' ){ 
                $leftNodeLength = countNodes([$parent_node[0]->left_node], $leftNodeLength);
                $rightNodeLength = countNodes([$parent_node[0]->right_node], $rightNodeLength);
            }

            if($leftNodeLength  < $rightNodeLength){
                $income = 100 * $leftNodeLength;
            } else if($leftNodeLength  > $rightNodeLength){
                $income = 100 * $rightNodeLength;
            } else { //kapag pantay
                $income = 100 * $leftNodeLength;
            }

            //count referral only for main accounts
            if($parent_node[0]->account_type == 'main'){
                $count_referral = Member::where('referred_by', session('data')['email'])->count();
                $income = $income + (50 * $count_referral );
            }
            

            if ($parent_node[0]->income < $income ){
                $amount = $income - intval($parent_node[0]->income);

                $transaction = new ClientTransaction([
                    'email' => $parent_node[0]->email,
                    'transaction_type' => 'income',
                    'bank' => $parent_node[0]->bank,
                    'bank_account_name' => $parent_node[0]->bank_account_name,
                    'bank_account_number' => $parent_node[0]->bank_account_number,
                    'bank_account_type' => $parent_node[0]->bank_account_type,
                    'gcash' => $parent_node[0]->gcash,
                    'amount' => $amount,
                ]);
        
                $transaction->save();
            }
            $parent_node[0]->income = $income;
            
            $parent_node[0]->save();

            if($parent_node[0]->parent_node != 'head'){
                addIncome($parent_node[0]->parent_node);
            } else {
                return;
            }
        }

        if($request->get('node') == "right"){
            if ($parent_node[0]->right_node == ''){
                $right_node = new Member([
                    'first_name' => $request->get('first_name'),
                    'last_name' => $request->get('last_name'),
                    'email' => $request->get('email'),
                    'password' => '123123123',
                    'account_type' => 'main',
                    'contact_number' => $request->get('contact_number'),
                    'bank' => $request->get('bank'),
                    'bank_account_name' => $request->get('bank_account_name'),
                    'bank_account_number' => $request->get('bank_account_number'),
                    'bank_account_type' => $request->get('bank_account_type'),
                    'gcash' => $request->get('gcash'),
                    'serial_number' => $request->get('input_code'),
                    'referred_by' => $request->get('referred_by'),
                    'income' => 0,
                    'batch' => $batch,
                    'parent_node' => $parent_node[0]->id,
                    'left_node' => '',
                    'right_node' => ''
                ]);
        
                $right_node->save();

                $right_node = Member::where('serial_number',$request->get('input_code'))->get();

                $parent_node[0]->right_node = $right_node[0]->id;
                $parent_node[0]->save();
                addIncome($parent_node[0]->id);
            }else{
                $parent_node = Member::where('id',$parent_node[0]->right_node)->get();
                addMember([$parent_node[0]->id], $request);
            }
        }else{
            if ($parent_node[0]->left_node == ''){
                $left_node = new Member([
                    'first_name' => $request->get('first_name'),
                    'last_name' => $request->get('last_name'),
                    'email' => $request->get('email'),
                    'password' => '123123123',
                    'account_type' => 'main',
                    'contact_number' => $request->get('contact_number'),
                    'bank' => $request->get('bank'),
                    'bank_account_name' => $request->get('bank_account_name'),
                    'bank_account_number' => $request->get('bank_account_number'),
                    'bank_account_type' => $request->get('bank_account_type'),
                    'gcash' => $request->get('gcash'),
                    'serial_number' => $request->get('input_code'),
                    'referred_by' => $request->get('referred_by'),
                    'income' => 0,
                    'batch' => $batch,
                    'parent_node' => $parent_node[0]->id,
                    'left_node' => '',
                    'right_node' => ''
                ]);
        
                $left_node->save();

                $left_node = Member::where('serial_number',$request->get('input_code'))->get();
                
                $parent_node[0]->left_node = $left_node[0]->id;
                $parent_node[0]->save();
                addIncome($parent_node[0]->id); //add income
            }else{
                $parent_node = Member::where('id',$parent_node[0]->left_node)->get();
                addMember([$parent_node[0]->id], $request);
            }
        }

        //FOR Univeral Binary
        $parent_node = GoldMember::where('parent_node','head')->get();

        function addGoldMember($givenNode, $request){
            $arrNode = [];

            foreach ($givenNode as & $id) {
                $parentNode =  GoldMember::findOrFail($id);

                if ($parentNode->left_node == '' ){
                    $left_node = new GoldMember([
                        'first_name' => $request->get('first_name'),
                        'last_name' => $request->get('last_name'),
                        'email' => $request->get('email'),
                        'contact_number' => $request->get('contact_number'),
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => $request->get('referred_by'),
                        'income' => 0,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => '' 
                    ]);
            
                    $left_node->save();

                    $left_node = GoldMember::where('serial_number',$request->get('input_code'))->get();

                    $parentNode->left_node = $left_node[0]->id;
                    $parentNode->save();
                    
                    addGoldIncome($parentNode->id); //add gold income
                    return;

                } else if($parentNode->right_node == '' ){
                    $right_node = new GoldMember([
                        'first_name' => $request->get('first_name'),
                        'last_name' => $request->get('last_name'),
                        'email' => $request->get('email'),
                        'contact_number' => $request->get('contact_number'),
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => $request->get('referred_by'),
                        'income' => 0,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $right_node->save();

                    $right_node = GoldMember::where('serial_number',$request->get('input_code'))->get();

                    $parentNode->right_node = $right_node[0]->id;
                    $parentNode->save();
                    
                    addGoldIncome($parentNode->id); //add gold income
                    return;
                } else {
                    array_push($arrNode, intval($parentNode->left_node),  intval($parentNode->right_node));
                }
            }

            return addGoldMember($arrNode, $request);
        }
        
        function countGoldNodes($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  GoldMember::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countGoldNodes($arrNode, 0);
            }

            return $length;  
        }

        function addGoldIncome($node){
            $income = 0;
            $leftNodeLength = 0;
            $rightNodeLength = 0;
            
            $parent_node = GoldMember::where('id', $node)->get();

            if ($parent_node[0]->left_node != '' && $parent_node[0]->right_node != '' ){ 
                $leftNodeLength = countGoldNodes([$parent_node[0]->left_node], $leftNodeLength);
                $rightNodeLength = countGoldNodes([$parent_node[0]->right_node], $rightNodeLength);
            }

            if($leftNodeLength  < $rightNodeLength){
                $length = ($leftNodeLength - ($leftNodeLength%3))/3;
                $income =  $length * 500;
            } else if($leftNodeLength  > $rightNodeLength){
                $length = ($rightNodeLength - ($rightNodeLength%3))/3;
                $income =  $length * 500;
            } else { //kapag pantay
                $length = ($leftNodeLength - ($leftNodeLength%3))/3;
                $income =  $length * 500;
            }

            if ($parent_node[0]->income < $income ){
                $amount = $income - intval($parent_node[0]->income);

                $transaction = new ClientTransaction([
                    'email' => $parent_node[0]->email,
                    'transaction_type' => 'income',
                    'bank' => $parent_node[0]->bank,
                    'bank_account_name' => $parent_node[0]->bank_account_name,
                    'bank_account_number' => $parent_node[0]->bank_account_number,
                    'bank_account_type' => $parent_node[0]->bank_account_type,
                    'gcash' => $parent_node[0]->gcash,
                    'amount' => $amount,
                ]);
        
                $transaction->save();
            }

            $parent_node[0]->income = $income;
            $parent_node[0]->save();

            if($parent_node[0]->parent_node != 'head'){
                addGoldIncome($parent_node[0]->parent_node);
            } else {
                return;
            }
        }

        addGoldMember([$parent_node[0]->id], $request);

        //update serial number as used code
        $serial_number = SerialNumber::where('input_code',$request->get('input_code'))->get();
        $serial_number[0]->status = '1';
        $serial_number[0]->save();

        return redirect('/use-refcode/member')->with('success','Data is Successfully Added');
    }

    public function addhead(Request $request){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $check_code = SerialNumber::where('input_code',$request->get('code'))->get();

        if ( $check_code->count() == 0 ){
            return redirect('/use-refcode/head')->with('error','Invalid Serial Number');
        }

        if($check_code[0]->status == '1'){
            return redirect('/use-refcode/head')->with('error','This code has already been used');
        }else {
            $input_code = $request->code;
        }

        $title = "Add Head";
        return view('addhead',compact('title','input_code'));
    }

    public function storehead(Request $request){

        $this->validate($request, [
            'full_name' => "required",
            'email' => "required",
            'contact_number' => "required",
        ]);
            
        //Checking if Serial number is valid
        $check_code = SerialNumber::where('input_code',$request->get('input_code'))->get();

        if($check_code[0]->status == '1' || $check_code[0]->code_type != 'member'){
            return redirect('/use-refcode/head')->with('error','This code has already been used');
        }

        if($check_code[0]->code_type != 'member'){
            return redirect('/use-refcode/head')->with('error','This code is not for adding member');
        }

        //Checking if Email is already exist
        $check_email = count(Member::where('email',$request->get('email'))->get());
        $check_email_admin = count(CompanyMember::where('email',$request->get('email'))->get());
        
        if ($check_email != 0 || $check_email_admin != 0){ //isnotempty
            return redirect('/member/add-head?code='.$request->get('input_code'))->with('error','An account with Email '.$request->get('email').' is already exist');
        }

        $batch = DB::table('members')->select('batch')->distinct()->get();
        $batch_no = count($batch) + 1;

        //FOR Manual Binary
        $node = new Member([
            'full_name' => $request->get('full_name'),
            'email' => $request->get('email'),
            'password' => '123123123',
            'account_type' => 'member',
            'contact_number' => $request->get('contact_number'),
            'bank_account' => $request->get('back_account'),
            'gcash' => $request->get('gcash'),
            'serial_number' => $request->get('input_code'),
            'referred_by' => '',
            'income' => 0,
            'batch' => $batch_no,
            'parent_node' => 'head',
            'left_node' => '',
            'right_node' => ''
        ]);

        $node->save();

        //FOR Universal Binary
        $parent_node = GoldMember::where('parent_node','head')->get();

        function addGoldHeadMember($givenNode, $request){
            $arrNode = [];

            foreach ($givenNode as &$parentNode) {
                $node =  GoldMember::findOrFail($parentNode);

                if ($node->left_node == '' ){
                    $left_node = new GoldMember([
                        'full_name' => $request->get('full_name'),
                        'email' => $request->get('email'),
                        'contact_number' => $request->get('contact_number'),
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => '',
                        'income' => 0,
                        'parent_node' => $node->id,
                        'left_node' => '',
                        'right_node' => '' 
                    ]);
            
                    $left_node->save();

                    $left_node = GoldMember::where('email',$request->get('email'))->get();

                    $node->left_node = $left_node[0]->id;
                    $node->save();
                    
                    addHeadGoldIncome($node->id);
                    return;

                } else if($node->right_node == '' ){
                    $right_node = new GoldMember([
                        'full_name' => $request->get('full_name'),
                        'email' => $request->get('email'),
                        'contact_number' => $request->get('contact_number'),
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => '',
                        'income' => 0,
                        'parent_node' => $node->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $right_node->save();

                    $right_node = GoldMember::where('email',$request->get('email'))->get();

                    $node->right_node = $right_node[0]->id;
                    $node->save();
                    
                    addHeadGoldIncome($node->id);
                    return;
                } else {
                    array_push($arrNode, intval($node->left_node),  intval($node->right_node));
                }
            }

            return addGoldHeadMember($arrNode, $request);
        }

        function countHeadGoldNodes($givenNode, $length){
            echo 'countHeadGoldNodes <br />';
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  GoldMember::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countHeadGoldNodes($arrNode, 0);
            }

            return $length;  
        }

        function addHeadGoldIncome($node){
            echo 'income<br />';
            $income = 0;
            $leftNodeLength = 0;
            $rightNodeLength = 0;
            
            $parent_node = GoldMember::where('id', $node)->get();

            if ($parent_node[0]->left_node != '' && $parent_node[0]->right_node != '' ){ 
                $leftNodeLength = countHeadGoldNodes([$parent_node[0]->left_node], $leftNodeLength);
                $rightNodeLength = countHeadGoldNodes([$parent_node[0]->right_node], $rightNodeLength);
            }

            if($leftNodeLength  < $rightNodeLength){
                $length = ($leftNodeLength - ($leftNodeLength%3))/3;
                $income =  $length * 1000;
            } else if($leftNodeLength  > $rightNodeLength){
                $length = ($rightNodeLength - ($rightNodeLength%3))/3;
                $income =  $length * 1000;
            } else { //kapag pantay
                $length = ($leftNodeLength - ($leftNodeLength%3))/3;
                $income =  $length * 1000;
            }

            if ($parent_node[0]->income < $income ){
                $amount = $income - intval($parent_node[0]->income);

                $transaction = new ClientTransaction([
                    'email' => $parent_node[0]->email,
                    'transaction_type' => 'income',
                    'amount' => $amount,
                ]);
        
                $transaction->save();
            }

            $parent_node[0]->income = $income;
            $parent_node[0]->save();

            if($parent_node[0]->parent_node != 'head'){
                addHeadGoldIncome($parent_node[0]->parent_node);
            } else {
                return;
            }
        }
        
        addGoldHeadMember([$parent_node[0]->id], $request);

        $serial_number = SerialNumber::where('input_code',$request->get('input_code'))->get();
        $serial_number[0]->status = '1';
        $serial_number[0]->save();
       
        //return redirect('/form/add')->with('success','Data is Successfully Added');
        return redirect('/use-refcode/head')->with('success','Data is Successfully Added');
    }

    public function addaccount(Request $request){

        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $member = Member::findOrFail(session('data')['id']);

        $check_code = SerialNumber::where('input_code',$request->get('code'))->get();

        if ( $check_code->count() == 0 ){
            return redirect('/use-refcode/account')->with('error','Invalid Serial Number');
        }

        if($check_code[0]->status == '1'){
            return redirect('/use-refcode/account')->with('error','This code has already been used');
        }else if($check_code[0]->code_type == 'member'){
            return redirect('/use-refcode/account')->with('error','This code is not for adding account');
        }else {
            $input_code = $request->code;
        }
        
        $title = "Add account";
        return view('addaccount',compact('member','title','input_code'));
    }

    public function storeaccount(Request $request){
        $this->validate($request, [
            'upline_to' => "required",
            'node' => "required"
        ]);

        $check_code = SerialNumber::where('input_code',$request->get('input_code'))->get();

        if($check_code[0]->status == '1'){
            return redirect('/use-refcode/account')->with('error','This code has already been used');
        }

        if($check_code[0]->code_type != 'account'){
            return redirect('/use-refcode/account')->with('error','This code is not for adding account');
        }

        $parent_node = Member::where('id',$request->get('upline_to'))->get();

        if ($parent_node->count() == 0 ){
            return redirect('/account/add?code='.$request->get('input_code'))->with('error','Invalid Upline ID');
        }else{
            $batch = $parent_node[0]->batch;
        }

        //manual
        $count_account = Member::where('email',session('data')['email'])->count();
        $count_account += 1;

        $account_no = $count_account;

        function addAccount($givenNode, $request, $account_no){
            $arrNode = [];

            foreach ($givenNode as & $id) {
                $parentNode =  Member::findOrFail($id);

                if ($parentNode->left_node == '' ){
                    $left_node = new Member([
                        'first_name' => session('data')['first_name'],
                        'last_name' => session('data')['last_name'],
                        'email' => session('data')['email'],
                        'password' => 'notapplicable',
                        'account_type' => $account_no,
                        'contact_number' => session('data')['contact_number'],
                        'bank' => session('data')['bank'],
                        'bank_account_name' => session('data')['bank_account_name'],
                        'bank_account_number' => session('data')['bank_account_number'],
                        'bank_account_type' => session('data')['bank_account_type'],
                        'gcash' => session('data')['gcash'],
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => '',
                        'income' => 0,
                        'batch' => $parentNode->batch,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $left_node->save();

                    $left_node = Member::where('serial_number',$request->get('input_code'))->get();
                    
                    $parentNode->left_node = $left_node[0]->id;
                    $parentNode->save();

                    addIncome($parentNode->id); // for income
                    return;

                } else if($parentNode->right_node == '' ){
                    $right_node = new Member([
                        'first_name' => session('data')['first_name'],
                        'last_name' => session('data')['last_name'],
                        'email' => session('data')['email'],
                        'password' => '123123123',
                        'account_type' => $account_no,
                        'contact_number' => session('data')['contact_number'],
                        'bank' => session('data')['bank'],
                        'bank_account_name' => session('data')['bank_account_name'],
                        'bank_account_number' => session('data')['bank_account_number'],
                        'bank_account_type' => session('data')['bank_account_type'],
                        'gcash' => session('data')['gcash'],
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => '',
                        'income' => 0,
                        'batch' => $parentNode->batch,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $right_node->save();

                    $right_node = Member::where('serial_number',$request->get('input_code'))->get();
                    
                    $parentNode->right_node = $right_node[0]->id;
                    $parentNode->save();

                    addIncome($parentNode->id); // for income
                    return;
                } else {
                    array_push($arrNode, intval($parentNode->left_node),  intval($parentNode->right_node));
                }
            }

            
            return addMember($arrNode, $request, $account_no);
        }

        function countNodes($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  Member::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countNodes($arrNode, 0);
            }

            return $length;  
        }

        function addIncome($node){
            $income = 0;
            $leftNodeLength = 0;
            $rightNodeLength = 0;
            
            $parent_node = Member::where('id', $node)->get();

            if ($parent_node[0]->left_node != '' && $parent_node[0]->right_node != '' ){ 
                $leftNodeLength = countNodes([$parent_node[0]->left_node], $leftNodeLength);
                $rightNodeLength = countNodes([$parent_node[0]->right_node], $rightNodeLength);
            }

            if($leftNodeLength  < $rightNodeLength){
                $income = 100 * $leftNodeLength;
            } else if($leftNodeLength  > $rightNodeLength){
                $income = 100 * $rightNodeLength;
            } else { //kapag pantay
                $income = 100 * $leftNodeLength;
            }

            //count referral only for main accounts
            if($parent_node[0]->account_type == 'main'){
                $count_referral = Member::where('referred_by', session('data')['email'])->count();
                $income = $income + (50 * $count_referral );
            }

            if ($parent_node[0]->income < $income ){
                $amount = $income - intval($parent_node[0]->income);

                $transaction = new ClientTransaction([
                    'email' => $parent_node[0]->email,
                    'transaction_type' => 'income',
                    'bank' => $parent_node[0]->bank,
                    'bank_account_name' => $parent_node[0]->bank_account_name,
                    'bank_account_number' => $parent_node[0]->bank_account_number,
                    'bank_account_type' => $parent_node[0]->bank_account_type,
                    'gcash' => $parent_node[0]->gcash,
                    'amount' => $amount,
                ]);
        
                $transaction->save();
            }
            $parent_node[0]->income = $income;
            
            $parent_node[0]->save();

            if($parent_node[0]->parent_node != 'head'){
                addIncome($parent_node[0]->parent_node);
            } else {
                return;
            }
        }

        if($request->get('node') == "right"){
            if ($parent_node[0]->right_node == ''){
                $right_node = new Member([
                    'first_name' => session('data')['first_name'],
                    'last_name' => session('data')['last_name'],
                    'email' => session('data')['email'],
                    'password' => 'notapplicable',
                    'account_type' => $account_no,
                    'contact_number' => session('data')['contact_number'],
                    'bank' => session('data')['bank'],
                    'bank_account_name' => session('data')['bank_account_name'],
                    'bank_account_number' => session('data')['bank_account_number'],
                    'bank_account_type' => session('data')['bank_account_type'],
                    'gcash' => session('data')['gcash'],
                    'serial_number' => $request->get('input_code'),
                    'referred_by' => '',
                    'income' => 0,
                    'batch' => $batch,
                    'parent_node' => $parent_node[0]->id,
                    'left_node' => '',
                    'right_node' => ''
                ]);
        
                $right_node->save();

                $right_node = Member::where('serial_number',$request->get('input_code'))->get();

                $parent_node[0]->right_node = $right_node[0]->id;
                $parent_node[0]->save();
                addIncome($parent_node[0]->id);
            }else{
                $parent_node = Member::where('id',$parent_node[0]->right_node)->get();
                addAccount([$parent_node[0]->id], $request, $account_no);
            }
        }else{
            if ($parent_node[0]->left_node == ''){
                $left_node = new Member([
                    'first_name' => session('data')['first_name'],
                    'last_name' => session('data')['last_name'],
                    'email' => session('data')['email'],
                    'password' => 'notapplicable',
                    'account_type' => $account_no,
                    'contact_number' => session('data')['contact_number'],
                    'bank' => session('data')['bank'],
                    'bank_account_name' => session('data')['bank_account_name'],
                    'bank_account_number' => session('data')['bank_account_number'],
                    'bank_account_type' => session('data')['bank_account_type'],
                    'gcash' => session('data')['gcash'],
                    'serial_number' => $request->get('input_code'),
                    'referred_by' => '',
                    'income' => 0,
                    'batch' => $batch,
                    'parent_node' => $parent_node[0]->id,
                    'left_node' => '',
                    'right_node' => ''
                ]);
        
                $left_node->save();

                $left_node = Member::where('serial_number',$request->get('input_code'))->get();
                
                $parent_node[0]->left_node = $left_node[0]->id;
                $parent_node[0]->save();
                addIncome($parent_node[0]->id); //add income
            }else{
                $parent_node = Member::where('id',$parent_node[0]->left_node)->get();
                addAccount([$parent_node[0]->id], $request, $account_no);
            }
        }

        //universal

        $parent_node = GoldMember::where('parent_node','head')->get();

        function addGoldMember($givenNode, $request, $account_no){
            $arrNode = [];

            foreach ($givenNode as & $id) {
                $parentNode =  GoldMember::findOrFail($id);

                if ($parentNode->left_node == '' ){
                    $left_node = new GoldMember([
                        'first_name' => session('data')['first_name'],
                        'last_name' => session('data')['last_name'],
                        'email' => session('data')['email'],
                        'contact_number' => session('data')['contact_number'],
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => '',
                        'income' => 0,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => '' 
                    ]);
            
                    $left_node->save();

                    $left_node = GoldMember::where('serial_number',$request->get('input_code'))->get();

                    $parentNode->left_node = $left_node[0]->id;
                    $parentNode->save();
                    
                    addGoldIncome($parentNode->id); //add gold income
                    return;

                } else if($parentNode->right_node == '' ){
                    $right_node = new GoldMember([
                        'first_name' => session('data')['first_name'],
                        'last_name' => session('data')['last_name'],
                        'email' => session('data')['email'],
                        'contact_number' => session('data')['contact_number'],
                        'serial_number' => $request->get('input_code'),
                        'referred_by' => '',
                        'income' => 0,
                        'parent_node' => $parentNode->id,
                        'left_node' => '',
                        'right_node' => ''
                    ]);
            
                    $right_node->save();

                    $right_node = GoldMember::where('serial_number',$request->get('input_code'))->get();

                    $parentNode->right_node = $right_node[0]->id;
                    $parentNode->save();
                    
                    addGoldIncome($parentNode->id); //add gold income
                    return;
                } else {
                    array_push($arrNode, intval($parentNode->left_node),  intval($parentNode->right_node));
                }
            }

            return addGoldMember($arrNode, $request, $account_no);
        }

        function countGoldNodes($givenNode, $length){
            $arrNode = []; 
            $length += count($givenNode);   

            foreach($givenNode as $id){
                $parentNode =  GoldMember::findOrFail($id);
                
                if ($parentNode->left_node != ''){
                    array_push($arrNode, intval($parentNode->left_node));
                }

                if ($parentNode->right_node != ''){
                    array_push($arrNode, intval($parentNode->right_node));
                }
            }

            if (count($arrNode) != 0){
                $length += countGoldNodes($arrNode, 0);
            }

            return $length;  
        }

        function addGoldIncome($node){
            $income = 0;
            $leftNodeLength = 0;
            $rightNodeLength = 0;
            
            $parent_node = GoldMember::where('id', $node)->get();

            if ($parent_node[0]->left_node != '' && $parent_node[0]->right_node != '' ){ 
                $leftNodeLength = countGoldNodes([$parent_node[0]->left_node], $leftNodeLength);
                $rightNodeLength = countGoldNodes([$parent_node[0]->right_node], $rightNodeLength);
            }

            if($leftNodeLength  < $rightNodeLength){
                $length = ($leftNodeLength - ($leftNodeLength%3))/3;
                $income =  $length * 500;
            } else if($leftNodeLength  > $rightNodeLength){
                $length = ($rightNodeLength - ($rightNodeLength%3))/3;
                $income =  $length * 500;
            } else { //kapag pantay
                $length = ($leftNodeLength - ($leftNodeLength%3))/3;
                $income =  $length * 500;
            }

            if ($parent_node[0]->income < $income ){
                $amount = $income - intval($parent_node[0]->income);

                $transaction = new ClientTransaction([
                    'email' => $parent_node[0]->email,
                    'transaction_type' => 'income',
                    'bank' => $parent_node[0]->bank,
                    'bank_account_name' => $parent_node[0]->bank_account_name,
                    'bank_account_number' => $parent_node[0]->bank_account_number,
                    'bank_account_type' => $parent_node[0]->bank_account_type,
                    'gcash' => $parent_node[0]->gcash,
                    'amount' => $amount,
                ]);
        
                $transaction->save();
            }

            $parent_node[0]->income = $income;
            $parent_node[0]->save();

            if($parent_node[0]->parent_node != 'head'){
                addGoldIncome($parent_node[0]->parent_node);
            } else {
                return;
            }
        }

        addGoldMember([$parent_node[0]->id], $request, $account_no);

        //update serial number as used code
        $serial_number = SerialNumber::where('input_code',$request->get('input_code'))->get();
        $serial_number[0]->status = '1';
        $serial_number[0]->save();

        return redirect('/use-refcode/account')->with('success','Data is Successfully Added');
    }

    public function profile(){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $member = Member::findOrFail(session('data')['id']);

        return view('profile',compact('member'));
    }

    public function edit(){
        if(!session()->has('data')){
            return redirect('login');
        }
        
        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $title="Update Profile";
        $member = Member::findOrFail(session('data')['id']);

        return view('updateMember',compact('title','member'));
    }

    public function update(Request $request){
        $this->validate($request, [
            'first_name' => "required",
            'last_name' => "required",
            'contact_number' => "required"
        ]);
        
        $accounts = Member::where('email', session('data')['email'])->get();

        //Manual Binary
        foreach($accounts as $account){
            if ($account->account_type == 'main' && $request->hasFile('image')){
                $filename = $request->image->getClientOriginalName();
                $request->image->storeAs('images', $filename, 'public');

                $account->image_file = $filename;
                $account->first_name = $request->get('first_name');
                $account->last_name = $request->get('last_name');
                $account->contact_number = $request->get('contact_number');
                $account->bank = $request->get('bank');
                $account->bank_account_name = $request->get('bank_account_name');
                $account->bank_account_number = $request->get('bank_account_number');
                $account->bank_account_type = $request->get('bank_account_type');
                $account->gcash = $request->get('gcash');
                $account->save();

            } else {
                $account->first_name = $request->get('first_name');
                $account->last_name = $request->get('last_name');
                $account->contact_number = $request->get('contact_number');
                $account->bank = $request->get('bank');
                $account->bank_account_name = $request->get('bank_account_name');
                $account->bank_account_number = $request->get('bank_account_number');
                $account->bank_account_type = $request->get('bank_account_type');
                $account->gcash = $request->get('gcash');
                $account->save();
            }
        }

        //Universal Binary
        $accounts = GoldMember::where('email', session('data')['email'])->get();

        foreach($accounts as $account){
            $account->first_name = $request->get('first_name');
            $account->last_name = $request->get('last_name');
            $account->contact_number = $request->get('contact_number');
            $account->save();
        }

        //Client Transaction
        $accounts = ClientTransaction::where('email', session('data')['email'])->get();

        foreach($accounts as $account){
            $account->bank = $request->get('bank');
            $account->bank_account_name = $request->get('bank_account_name');
            $account->bank_account_number = $request->get('bank_account_number');
            $account->bank_account_type = $request->get('bank_account_type');
            $account->gcash = $request->get('gcash');
            $account->save();
        }

        return redirect()->route('profile')->with('success','Profile Information changed successfully');
  
    }

    public function login(Request $request) {
        $request->validate([
            'password' =>'min:9'
        ]);

        $admin = CompanyMember::where('email',$request->get('email'))->get();

        if (count($admin) != 0){
            if( $admin[0]->password == $request->get('password')){
                $request->session()->put('data', $admin[0]);
                return redirect('/admin-manual?batch=1');
            }
        }

        $account = Member::where('email',$request->get('email'))
            ->where('account_type', 'main')
            ->get();
           
        if (count($account) == 0){
            return redirect()->route('login')->with('error','Invalid Email or Password');
        }else{
            if( $account[0]->password == $request->get('password')){
                $request->session()->put('data', $account[0]);
                return redirect('/client-dashboard');
            } else {
                return redirect()->route('login')->with('error','Invalid Email or Password');
            }
        }

        
    }

    public function logout(Request $request) {
        session()->forget('data');
        return redirect('/login');
    }

    public function editPassword(){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] != 'admin'){
            $member = Member::findOrFail(session('data')['id']);
        }

        $title = "Change Password";

        if(session('data')['role'] != 'admin'){
            return view('changePassword',compact('member','title'));
        } else {
            return view('changePassword',compact('title'));
        }
        
    }

    public function updatePassword(Request $request){
        $this->validate($request, [
            'current_password' => "required",
            'new_password' => "required",
            'retype_new_password' => "required"
        ]);

        if ( $request->get('current_password') != session('data')['password'] ){
            return redirect()->route('changePassword')->with('error','Your current password is incorrect. It\'s required to change password');
        }

        if ( $request->get('new_password') != $request->get('retype_new_password')){
            return redirect()->route('changePassword')->with('error','The password and its confirmation do not match');
        }


        if(session('data')['role'] == 'admin'){
            $account = CompanyMember::where('email',session('data')['email'])->get();  

            $account[0]->password = $request->get('new_password');
            $account[0]->save();

            return redirect()->route('changePassword')->with('success','Password changed successfully');
        
        } else{
            $account = Member::where('email',session('data')['email'])->get();

            $account[0]->password = $request->get('new_password');
            $account[0]->save();

            return redirect()->route('changePassword')->with('success','Password changed successfully');
        }

        

    }

    public function cashout(Request $request){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] != 'admin'){
            return redirect('/');
        }

        $cashout_list = ClientTransaction::where('transaction_type','withdraw')->get();
        
        return view('cashout',compact('cashout_list',));
    }
     
    public function transactionClientRecord(){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            return redirect('/');
        }

        $client_transaction = ClientTransaction::where('email',session('data')['email'])->get();
        $member = Member::findOrFail(session('data')['id']);

        return view('transactionClientRecord',compact('member','client_transaction',));
    }

    public function test(Request $request){

        return view('test');
    }
    
}

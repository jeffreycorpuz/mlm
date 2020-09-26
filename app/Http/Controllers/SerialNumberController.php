<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SerialNumber;
use Illuminate\Auth\EloquentUserProvider;

//pagination of collection data
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SerialNumberController extends Controller
{

    public function addCode(){
        if(!session()->has('data')){
            return redirect('login');
        }

        if(session('data')['role'] == 'admin'){
            $title = "Add Referral Code";
            return view('addReferralCode',compact('title'));
        }else{
            return redirect('/');
        }

        
    }

    public function storeCode(Request $request){
        if(!session()->has('data')){
            return redirect('login');
        }

        $this->validate($request, [
            'input_code' => "required",
            'code_type' => "required"
        ]);

        if( count(SerialNumber::where('input_code',$request->get('input_code'))->get()) != 0 ){
            return redirect()->route('addcode')->with('error','This Code is Already Added');
        }

        $serial_number = new SerialNumber([
            'input_code' => $request->get('input_code'),
            'code_type' => $request->get('code_type'),
            'status' => '0'
        ]);

        $serial_number->save();

        return redirect()->route('addcode')->with('success','Data is Successfully Added');
    }

    public function useCode($member){
        if(!session()->has('data')){
            return redirect('login');
        }

        $member_type = $member;

        $title = "Use Referral Code";
        return view('checkReferralCode',compact('title', 'member_type'));
    }

    public function checkCode(Request $request, $member){
        if(!session()->has('data')){
            return redirect('login');
        }

        $this->validate($request, [
            'input_code' => "required",
        ]);

        $serial_number = SerialNumber::where('input_code',$request->get('input_code'))->get();

        if( count($serial_number) != 0){
            if($serial_number[0]->status == 0){
                if($member == "member"){
                    if($serial_number[0]->code_type == 'member'){
                        return redirect('/member/add?code='.$request->get('input_code'));
                    }else{
                        return redirect('/use-refcode/member')->with('error','This code is not for adding member');
                    }
                    
                }else if($member == "account"){
                    if($serial_number[0]->code_type == 'account'){
                        return redirect('/account/add?code='.$request->get('input_code'));
                    }else{
                        return redirect('/use-refcode/account')->with('error','This code is not for adding account');
                    }
                } else {
                    return redirect('/member/add-head?code='.$request->get('input_code'));
                }
            }else{
                if($member == "member"){
                    return redirect('/use-refcode/member')->with('error','This code has already been used');
                } else if($member == "account"){
                    return redirect('/use-refcode/account')->with('error','This code has already been used');
                }else {
                    return redirect('/use-refcode/head')->with('error','This code has already been used');
                }
            }
            
        }else{
            if($member == "member"){
                return redirect('/use-refcode/member')->with('error','Invalid Serial Number');
            } else {
                return redirect('/use-refcode/head')->with('error','Invalid Serial Number');
            }
            
        }
    }

    public function showCode(Request $request){
        if(!session()->has('data')){
            return redirect('login');
        }
        
        if(session('data')['role'] == 'admin'){
            $select_by = $request->select_by;
            

            if($select_by == 'all'){
                $serial_number_all = SerialNumber::all();
            } else if($select_by == 'member'){
                $serial_number_all = SerialNumber::where('code_type', 'member')->get();
            } else if($select_by == 'account') {
                $serial_number_all = SerialNumber::where('code_type', 'account')->get();
            } else{
                return redirect('/view-refcode?select_by=all&sort=1&page=1');
            }

            $serial_number_all_data = collect($serial_number_all)->sortBy('id');
            $serial_number_all_data = $this->paginate($serial_number_all_data);
            $serial_number_all_pagination_length = $serial_number_all->count();

            if($serial_number_all_pagination_length%10 == 0 ){
                $serial_number_all_pagination_length = $serial_number_all_pagination_length / 10;
            }else{
                $serial_number_all_pagination_length = (($serial_number_all_pagination_length - $serial_number_all_pagination_length%10)/10)+1;
            }

            if($select_by == 'all'){
                $serial_number_available = SerialNumber::where('status', '0')->get();
            } else if($select_by == 'member'){
                $serial_number_available = SerialNumber::where('status', '0')
                    ->where('code_type', 'member')
                    ->get();
            } else if($select_by == 'account') {
                $serial_number_available = SerialNumber::where('status', '0')
                    ->where('code_type', 'account')
                    ->get();
            }
            $serial_number_available_data = collect($serial_number_available)->sortBy('id');
            $serial_number_available_data = $this->paginate($serial_number_available_data);
            $serial_number_pagination_length = $serial_number_available->count();
            $serial_number_available_pagination_length = $serial_number_available->count();

            if($serial_number_available_pagination_length%10 == 0 ){
                $serial_number_available_pagination_length = $serial_number_available_pagination_length / 10;
            }else{
                $serial_number_available_pagination_length = (($serial_number_available_pagination_length - $serial_number_available_pagination_length%10)/10)+1;
            }

            $page_no = $request->page;
            $sort = $request->sort;
            
            if($serial_number_available->count() == 0){
                
                //do nothing because this means there are no available codes
            }else if($request->sort == 0){
                //error handler for not existing page
                
                if($request->page > $serial_number_available_pagination_length || $request->page < 1 ){
                    return redirect('/view-refcode?select_by='.$select_by.'&sort=0&page=1');
                }
            } else if ($request->sort == 1 ){
                //error handler for not existing page
                if($request->page > $serial_number_all_pagination_length || $request->page < 1 ){
                    return redirect('/view-refcode?select_by='.$select_by.'&sort=1&page=1');
                }
            }

            

            return view('viewReferralCode',compact(
                'serial_number_all_data',
                'serial_number_all_pagination_length',
                'serial_number_available_data',
                'serial_number_available_pagination_length',
                'page_no',
                'sort',
                'select_by'
            ));
        }else{
            return redirect('/');
        }
    }

    public function paginate($items, $perPage = 10, $page = null, $options = []){
        //paginate collection or json file
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

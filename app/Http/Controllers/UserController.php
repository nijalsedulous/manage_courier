<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\User_profile;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Validator;


class UserController extends Controller
{
    //
    public function admin_dashboard(){

        $data=[];
        return view('admin.dashboard',$data);
    }

    public function agent_dashboard(){

        $data=[];
        return view('agent.dashboard',$data);
    }

    public function store_dashboard(){

        $data=[];
        return view('store.dashboard',$data);
    }

    public function profile($id){

        $profile = User::find($id);
        $countries = Country::get();
        $data['countries']=$countries;
        $data['states']=State::where('country_id',$profile->profile->country_id)->get();
        $data['cities']=City::where('state_id',$profile->profile->state_id)->get();
        $data['profile']=$profile;
        return view('users.profile',$data);
    }

    public function update_profile(Request $request, $id)
    {

        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect(\Auth::user()->user_type.'/profile/'.$id)
                ->withErrors($validator)
                ->withInput();
        }

        $input = $request->all();
        $user = User::find($id);
        if($user != null){
            $user->email = $input['email'];
            $user->save();
        }
        $user_profile = User_profile::where('user_id',$id)->first();
        if($user_profile != null){
            $user_profile->company_name = $input['company_name'];
            $user_profile->first_name = $input['first_name'];
            $user_profile->last_name = $input['last_name'];
            $user_profile->phone = $input['phone'];
            $user_profile->address = $input['address'];
            $user_profile->gender = $input['gender'];
            $user_profile->city_id = $input['city_id'];
            $user_profile->state_id = $input['state_id'];
            $user_profile->country_id = $input['country_id'];
            $user_profile->save();
        }
        $request->session()->flash('message', 'Profile has been updated successfully!');
        return redirect(\Auth::user()->user_type.'/profile/'.$id);

    }

    public function change_password(){

        $id = \Auth::user()->id;
        $user = User::find($id);
        return view('users.change_password')->with('user',$user);
    }

    public function updatePassword(Request $request, $id){

        $rules = [
            'password' => 'required|confirmed',
            'password_confirmation'=>'required'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails()) {

            return redirect(\Auth::user()->user_type.'/change_password')
                ->withErrors($validator)
                ->withInput();
        }
        $user = User::find($id);
        $user->password = \Hash::make($request->password);
        $user->save();

        $request->session()->flash('message', 'Password has been changed successfully!');

        return redirect('/'.\Auth::user()->user_type.'/change_password');

    }

    public function storeCity(){
        $states = State::where('country_id',2)->get();
        foreach ($states as $state){
            $state_code = $state->state_code;
            $usa_cities = \DB::table('usa_cities')->where('state_code',$state_code)->get();

            foreach ($usa_cities as $u_city){
                $city = new City();
                $city->city_name = $u_city->city;
                $city->state_id = $state->id;
                $city->state_name = $state->state_name;

                $city->save();
            }
        }
    }
}
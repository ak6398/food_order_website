<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('frontend.index');
    }
    // end method

    public function ProfileStore(Request $request)
    {
        $id=Auth::User()->id;
        $data=User::find($id);

        $data->name=$request->name;
        $data->email=$request->email;
        $data->photo=$request->photo;
        $data->phone=$request->phone;
        $data->address=$request->address;

        $oldPhotoPath=$data->photo;

        if($request->hasFile('photo')){
            $file=$request->file('photo');
            $filename=time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'),$filename);
            $data->photo=$filename;

            if($oldPhotoPath && $oldPhotoPath !==$filename){
                $this->deleteOldImage($oldPhotoPath);
            }

        }
        $data->save();
        $notification=array('message'=>'Profile updated successfully',
                    'alert-type'=>'success'
    );
        return redirect()->back();
    }
    // ends here
    private function deleteOldImage(string $oldPhotoPath):void{
        $fullPath=public_path('upload/user_images/'.$oldPhotoPath);
        if(file_exists($fullPath)){
            unlink($fullPath);
        }
    }
    // end private method
    public function UserLogout(){
        Auth::guard('web')->logout();
        return redirect()->route('login')->with('success','logout successfully');
    }
    // ends here

    public function ChangePassword(){
        return view('frontend.dashboard.change_password');
    }
    // end here

    public function password_update(Request $request){
        $user=Auth::guard('web')->user();
        $request->validate([
            'old_password'=>'required',
            'new_password'=>'required|confirmed',
        ]);

        if(!Hash::check($request->old_password,$user->password)){
            $notification=array(
                'message'=>'Old password does not match',
                'alert-type'=>'error'
            );
            return back()->with($notification);
        }
        // update the new password
        User::whereId($user->id)->update([
            'password'=>Hash::make($request->new_password)
        ]);
        $notification=array(
            'message'=>'password change successfuly',
            'alert-type'=>'success'
        );
        return back()->with($notification);
    }
    // end function
}
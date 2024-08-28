<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\GeneralResource;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function profile(){
        return apiResponse(true, "Profile Data", 200, new ProfileResource(auth()->user()));
    }

    public function edit_profile(UpdateUserRequest $request){
        $user = $this->user->find(auth()->id());
        if ($request->image) {
            $avatar = uploadFile($request->image);
            $request->merge(['avatar' => $avatar]);
        }
        $user->update($request->all());
        return apiResponse(true, "Profile updated successfully.", 200, $user);
    }

    public function change_password(UpdatePasswordRequest $request){
        $user = $this->user->find(auth()->id());
        if(Hash::check($request->old_password, $user->password)){ 
            $user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();
        
            return apiResponse(true, "Password changed successfully.");
        }else{
            return apiResponse(false, "Password don't match.", 422);
        }
    }

    // public function change_avatar(UpdateAvatarRequest $request){
    //     $user = $this->user->find(auth()->id());
    //     $avatar = uploadFile($request->profile);
    //     if($avatar){
    //         if($user->avatar !== Constant::DEFAULT_AVATAR){
    //             if(file_exists(public_path()."/".$user->avatar)){
    //                 unlink(public_path()."/".$user->avatar);
    //             }
    //         }

    //         $user->update(['avatar' => $avatar]);
    //         return apiResponse(true, "Avatar uploaded successfully.", 200, $avatar);
    //     }
    //     return apiResponse(false, "Image couldn't upload.", 422);
    // }

    public function notifications(Request $request){
        $user = $this->user->find(auth()->id());
        if($request->type == "read"){
            $notifications = $user->readNotifications()->paginate(10);

        }else if($request->type == "unread"){
            $notifications = $user->unreadNotifications()->paginate(10);
            
        }else{
            $notifications = $user->notifications()->paginate(10);
        }
        return apiResponse(true, "Notifications", 200, GeneralResource::collection($notifications)->response()->getData(true));
    }

    public function mark_as_read($id){
        $notification = auth()->user()->unreadNotifications->where('id', $id)->first();
        if($notification) {
            $notification->markAsRead();
        }
        return apiResponse(true, "marked as read");
    }
}

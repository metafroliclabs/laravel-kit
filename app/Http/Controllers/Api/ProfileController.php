<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\MainController;
use App\Http\Requests\Common\UpdatePasswordRequest;
use App\Http\Requests\Common\UpdateUserRequest;
use App\Http\Resources\DefaultResource;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileController extends MainController
{
    public $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function profile()
    {
        return $this->response->success(new ProfileResource(auth()->user()));
    }

    public function edit_profile(UpdateUserRequest $request)
    {
        $user = $this->user->find(auth()->id());
        if ($request->image) {
            $data = uploadFile($request->image);
            $request->merge(['avatar' => $data['data']]);
            deleteFile($user->getAttributes()['avatar']);
        }
        $user->update($request->all());
        return $this->response->success($user);
    }

    public function change_password(UpdatePasswordRequest $request)
    {
        $user = $this->user->find(auth()->id());
        if (Hash::check($request->old_password, $user->password)) {
            $user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();

            return $this->response->successMessage("Password changed successfully.");
        }
        throw new BadRequestException("Current password is invalid.");
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

    public function all_notifications()
    {
        $user = $this->user->find(auth()->id());
        $notifications = $user->notifications()->paginate(10);

        return $this->response->success(
            DefaultResource::collection($notifications)->response()->getData(true)
        );
    }

    public function read_notifications()
    {
        $user = $this->user->find(auth()->id());
        $notifications = $user->readNotifications()->paginate(10);

        return $this->response->success(
            DefaultResource::collection($notifications)->response()->getData(true)
        );
    }

    public function unread_notifications()
    {
        $user = $this->user->find(auth()->id());
        $notifications = $user->unreadNotifications()->paginate(10);

        return $this->response->success(
            DefaultResource::collection($notifications)->response()->getData(true)
        );
    }

    public function unread_notifications_count()
    {
        $user  = $this->user->find(auth()->id());
        $count = $user->notifications()->whereNull('read_at')->count();
        return $this->response->success(['count' => $count]);
    }

    public function mark_notification($id)
    {
        $user = $this->user->find(auth()->id());
        $notification = $user->notifications()->find($id);

        if ($notification) {
            if ($notification->read_at) {
                $notification->update(['read_at' => null]);
                return $this->response->successMessage("Notification marked as unread");
            } else {
                $notification->markAsRead();
                return $this->response->successMessage("Notification marked as read");
            }
        }
        throw new BadRequestException("Notification not found");
    }

    public function mark_all_as_read()
    {
        $user = $this->user->find(auth()->id());
        $user->unreadNotifications()->update(['read_at' => now()]);
        return $this->response->successMessage("All notifications marked as read.");
    }
}
